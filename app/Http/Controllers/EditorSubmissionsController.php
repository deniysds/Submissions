<?php

namespace Modules\Submissions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Submissions\Models\ReviewRound;
use Modules\Submissions\Models\ReviewerAssignment;
use Modules\Submissions\Models\Submission;
use Modules\Submissions\Services\SubmissionService;
use Modules\Usermanagement\Models\User;

class EditorSubmissionsController extends Controller
{
    public function __construct(
        protected SubmissionService $submissionService
    ) {}

    /**
     * Display a listing of submissions submitted for Editorial Review.
     */
    public function index(Request $request)
    {
        $submissions = $this->submissionService->getEditorSubmissions($request);

        return view('submissions::editor.index', compact('submissions'));
    }

    /**
     * Display the specified manuscript details for Editorial Evaluation.
     */
    public function show($id)
    {
        $submission = Submission::with(['journal', 'files', 'authors', 'author', 'editor', 'histories.actor', 'reviewRounds.assignments.reviewer'])
            ->where('status', '!=', 'draft')
            ->findOrFail($id);

        $allowedTransitions = $submission->allowedTransitions();
        $availableReviewers = User::where('id', '!=', $submission->corresponding_author_id)->get();
        $availableIssues = \Modules\Issues\Models\Issue::where('journal_id', $submission->journal_id)->get();
        $article = \Modules\Issues\Models\Article::where('submission_id', $submission->id)->first();

        return view('submissions::editor.show', compact('submission', 'allowedTransitions', 'availableReviewers', 'availableIssues', 'article'));
    }

    /**
     * Display the official DOI and Publication Certificate summary for editor.
     */
    public function doiCertificate($id)
    {
        $submission = Submission::with(['journal', 'files', 'authors', 'author'])
            ->where('status', '!=', 'draft')
            ->findOrFail($id);

        return view('submissions::doi.certificate', compact('submission'));
    }

    /**
     * Make Editorial Decision (State transition with history logging).
     */
    public function makeDecision(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::where('status', '!=', 'draft')->findOrFail($id);

        $request->validate([
            'status' => 'required|string',
            'notes'  => 'nullable|string|max:2000',
        ], [
            'status.required' => 'Keputusan dewan redaksi wajib dipilih.',
        ]);

        try {
            $this->submissionService->transitionStatus(
                $submission,
                $request->status,
                $request->notes,
                $user
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('editor.submissions.show', $submission->id)
                ->withErrors(['status' => $e->getMessage()]);
        }

        $statusLabels = [
            'under_editor_review' => 'Peninjauan Editor (Under Editor Review)',
            'under_review'        => 'Penelaahan Mitra Bestari (Under Review)',
            'revision_required'   => 'Revisi Diperlukan (Revision Required)',
            'resubmitted'         => 'Naskah Revisi Masuk (Resubmitted)',
            'accepted'            => 'Diterima (Accepted)',
            'published'           => 'Diterbitkan (Published)',
            'rejected'            => 'Ditolak (Rejected)',
        ];

        $label = $statusLabels[$request->status] ?? $request->status;

        return redirect()->route('editor.submissions.show', $submission->id)
            ->with('success', "Keputusan redaksi berhasil diperbarui menjadi: {$label}");
    }

    /**
     * Publish / Schedule an accepted manuscript into an Issue.
     */
    public function publishToIssue(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::with(['journal', 'files'])->where('status', '!=', 'draft')->findOrFail($id);

        $request->validate([
            'issue_id' => 'required|exists:issues,id',
            'pages'    => 'nullable|string|max:50',
            'doi'      => 'nullable|string|max:100',
        ], [
            'issue_id.required' => 'Issue terbitan wajib dipilih.',
            'issue_id.exists'   => 'Issue terbitan tidak valid.',
        ]);

        $issue = \Modules\Issues\Models\Issue::findOrFail($request->issue_id);

        // Generate default DOI if not provided
        $doi = $request->doi ?: ('10.1234/' . ($submission->journal?->slug ?: 'ignite') . '.' . $issue->publication_year . '.' . $issue->volume . '.' . $issue->number . '.' . str_pad((string)$submission->id, 4, '0', STR_PAD_LEFT));

        $mainFile = $submission->files->where('file_role', 'naskah_utama')->first();

        // Create or update Article record
        $article = \Modules\Issues\Models\Article::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'issue_id'        => $issue->id,
                'title'           => $submission->title,
                'slug'            => $submission->slug,
                'abstract'        => $submission->abstract,
                'keywords'        => $submission->keywords,
                'pages'           => $request->pages ?: '1-10',
                'doi'             => $doi,
                'file_path'       => $mainFile?->path,
                'published_at'    => now(),
            ]
        );

        // If status was 'accepted', transition to 'published'
        if ($submission->status === 'accepted') {
            $this->submissionService->transitionStatus(
                $submission,
                'published',
                "Naskah resmi diterbitkan ke {$issue->title} (Vol. {$issue->volume} No. {$issue->number}) dengan DOI: {$doi}",
                $user
            );
        } else {
            $submission->update(['status' => 'published', 'decision_at' => now()]);
        }

        return redirect()->route('editor.submissions.show', $submission->id)
            ->with('success', "Naskah berhasil diterbitkan ke {$issue->title} (Vol. {$issue->volume} No. {$issue->number}) dengan DOI: {$doi}!");
    }

    /**
     * Assign an editor to manuscript.
     */
    public function assignEditor(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::where('status', '!=', 'draft')->findOrFail($id);

        $request->validate([
            'editor_id' => 'required|exists:users,id',
        ]);

        $this->submissionService->assignEditor($submission, (int) $request->editor_id, $user);

        return redirect()->route('editor.submissions.show', $submission->id)
            ->with('success', 'Editor penanggung jawab berhasil ditugaskan.');
    }

    /**
     * Assign a peer reviewer to the manuscript.
     */
    public function assignReviewer(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::where('status', '!=', 'draft')->findOrFail($id);

        $request->validate([
            'reviewer_id' => 'required|exists:users,id',
            'due_date'    => 'nullable|date',
        ], [
            'reviewer_id.required' => 'Mitra bestari (reviewer) wajib dipilih.',
            'reviewer_id.exists'   => 'Akun mitra bestari tidak ditemukan.',
        ]);

        if ((int) $request->reviewer_id === (int) $submission->corresponding_author_id) {
            return redirect()->route('editor.submissions.show', $submission->id)
                ->withErrors(['reviewer_id' => 'Penulis utama tidak dapat ditugaskan sebagai reviewer naskahnya sendiri.']);
        }

        // Get or create active review round
        $round = $submission->reviewRounds()->where('status', 'in_progress')->latest('round_number')->first();
        if (!$round) {
            $latestRoundNumber = $submission->reviewRounds()->max('round_number') ?? 0;
            $round = ReviewRound::create([
                'submission_id' => $submission->id,
                'round_number'  => $latestRoundNumber + 1,
                'status'        => 'in_progress',
                'started_at'    => now(),
                'created_by'    => $user->id,
            ]);
        }

        // Prevent assigning the same reviewer twice in the same round
        $existingAssignment = ReviewerAssignment::where('review_round_id', $round->id)
            ->where('reviewer_id', $request->reviewer_id)
            ->first();

        if ($existingAssignment) {
            return redirect()->route('editor.submissions.show', $submission->id)
                ->withErrors(['reviewer_id' => 'Mitra bestari ini sudah ditugaskan pada putaran telaah ini.']);
        }

        ReviewerAssignment::create([
            'review_round_id'  => $round->id,
            'reviewer_id'      => (int) $request->reviewer_id,
            'invitation_token' => Str::random(40),
            'response_status'  => 'pending',
            'due_date'         => $request->due_date ?: now()->addDays(14)->toDateString(),
            'created_by'       => $user->id,
        ]);

        if (in_array($submission->status, ['submitted', 'under_editor_review', 'resubmitted'], true)) {
            $this->submissionService->transitionStatus(
                $submission,
                'under_review',
                'Editor menugaskan mitra bestari untuk penelaahan sejawat.',
                $user
            );
        }

        return redirect()->route('editor.submissions.show', $submission->id)
            ->with('success', 'Mitra bestari berhasil ditugaskan untuk telaah naskah ini.');
    }

    /**
     * Export submissions report to CSV format (Excel compatible).
     */
    public function exportCsv(Request $request)
    {
        $query = Submission::with(['journal', 'author', 'editor', 'authors'])
            ->where('status', '!=', 'draft');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('journal_id')) {
            $query->where('journal_id', $request->journal_id);
        }

        $submissions = $query->latest('submitted_at')->get();

        $fileName = 'submissions_report_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($submissions) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($file, [
                'ID Naskah',
                'Judul Naskah',
                'Jurnal Publikasi',
                'Penulis Korespondensi',
                'Email Penulis',
                'Status Naskah',
                'Editor Bertugas',
                'Tanggal Penyerahan',
                'Tanggal Keputusan',
                'DOI Permanen',
            ]);

            foreach ($submissions as $s) {
                $doi = ($s->status === 'accepted' || $s->status === 'published')
                    ? "https://doi.org/10.1234/ignite.{$s->journal?->slug}.{$s->id}"
                    : '-';

                fputcsv($file, [
                    '#IGN-' . str_pad($s->id, 5, '0', STR_PAD_LEFT),
                    $s->title,
                    $s->journal?->name ?? '-',
                    $s->author?->name ?? '-',
                    $s->author?->email ?? '-',
                    strtoupper(str_replace('_', ' ', $s->status)),
                    $s->editor?->name ?? 'Belum Ditugaskan',
                    $s->submitted_at ? $s->submitted_at->format('Y-m-d H:i') : '-',
                    $s->decision_at ? $s->decision_at->format('Y-m-d H:i') : '-',
                    $doi,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

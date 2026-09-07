<?php

namespace Modules\Submissions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Submissions\Models\Submission;
use Modules\Submissions\Services\SubmissionService;

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
        $submission = Submission::with(['journal', 'files', 'authors', 'author', 'editor', 'histories.actor'])
            ->where('status', '!=', 'draft')
            ->findOrFail($id);

        $allowedTransitions = $submission->allowedTransitions();

        return view('submissions::editor.show', compact('submission', 'allowedTransitions'));
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

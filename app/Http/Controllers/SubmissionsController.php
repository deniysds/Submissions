<?php

namespace Modules\Submissions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Journals\Models\Journal;
use Modules\Submissions\Models\Submission;
use Modules\Submissions\Services\SubmissionService;

class SubmissionsController extends Controller
{
    public function __construct(
        protected SubmissionService $submissionService
    ) {}

    /**
     * Display a listing of the user's submissions.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submissions = $this->submissionService->getAuthorSubmissions($user, $request);

        return view('submissions::index', compact('submissions'));
    }

    /**
     * Show detail of submission.
     */
    public function show($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::with([
            'journal',
            'files',
            'authors',
            'histories.actor',
            'reviewRounds.assignments' => function ($q) {
                $q->whereNotNull('comments_for_author')
                  ->where('response_status', 'accepted');
            },
        ])
            ->where('corresponding_author_id', $user->id)
            ->findOrFail($id);

        return view('submissions::show', compact('submission'));
    }

    /**
     * Display the official DOI and Publication Certificate summary for author.
     */
    public function doiCertificate($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::with(['journal', 'files', 'authors', 'author'])
            ->where('corresponding_author_id', $user->id)
            ->findOrFail($id);

        return view('submissions::doi.certificate', compact('submission'));
    }

    /**
     * Step 1: Show form for creating a new submission (Select Journal & Author Checklist).
     */
    public function create()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $journals = Journal::where('is_active', true)->get();

        return view('submissions::create', compact('journals'));
    }

    /**
     * Store Step 1: Create a Draft Submission
     */
    public function storeStep1(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'journal_id' => 'required|exists:journals,id',
            'checklist'  => 'required|array|min:3',
        ], [
            'checklist.min' => 'Anda wajib menyetujui seluruh kriteria checklist persyaratan penulis sebelum melanjutkan.',
        ]);

        $submission = $this->submissionService->createDraft($user, (int) $request->journal_id);

        return redirect()->route('submissions.create.step2', $submission->id)
            ->with('success', 'Persyaratan Step 1 berhasil disetujui. Lanjutkan dengan mengunggah naskah.');
    }

    /**
     * Step 2: Show Upload Form
     */
    public function createStep2($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::with('files')
            ->where('corresponding_author_id', $user->id)
            ->findOrFail($id);

        return view('submissions::step2', compact('submission'));
    }

    /**
     * Store Step 2: Upload Files
     */
    public function storeStep2(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::where('corresponding_author_id', $user->id)->findOrFail($id);

        if ($request->hasFile('file')) {
            $request->validate([
                'file'      => 'required|file|mimes:docx,doc,pdf|max:10240',
                'file_role' => 'required|in:naskah_utama,supplementary,title_page',
            ]);

            $this->submissionService->uploadFile($submission, $request->file('file'), $request->file_role, $user);

            return redirect()->route('submissions.create.step2', $submission->id)
                ->with('success', 'Berkas berhasil diunggah.');
        }

        $hasMainFile = $submission->files()->where('file_role', 'naskah_utama')->exists();

        if (!$hasMainFile) {
            return redirect()->route('submissions.create.step2', $submission->id)
                ->withErrors(['file' => 'Anda wajib mengunggah minimal 1 berkas dengan jenis "Naskah Utama" sebelum melanjutkan.']);
        }

        return redirect()->route('submissions.create.step3', $submission->id)
            ->with('success', 'Unggah berkas berhasil disimpan. Silakan lengkapi metadata naskah.');
    }

    /**
     * Delete uploaded file (Step 2)
     */
    public function deleteFileStep2($id, $fileId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::where('corresponding_author_id', $user->id)->findOrFail($id);
        $this->submissionService->deleteFile($submission, (int) $fileId);

        return redirect()->route('submissions.create.step2', $submission->id)
            ->with('success', 'Berkas berhasil dihapus.');
    }

    /**
     * Step 3: Show Metadata & Authors Form
     */
    public function createStep3($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::with('authors')
            ->where('corresponding_author_id', $user->id)
            ->findOrFail($id);

        return view('submissions::step3', compact('submission'));
    }

    /**
     * Add Co-Author (Step 3)
     */
    public function addAuthorStep3(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::where('corresponding_author_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'affiliation' => 'nullable|string|max:255',
        ]);

        $this->submissionService->addAuthor($submission, $validated, $user);

        return redirect()->route('submissions.create.step3', $submission->id)
            ->with('success', 'Penulis anggota berhasil ditambahkan.');
    }

    /**
     * Delete Co-Author (Step 3)
     */
    public function deleteAuthorStep3($id, $authorId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::where('corresponding_author_id', $user->id)->findOrFail($id);

        try {
            $this->submissionService->deleteAuthor($submission, (int) $authorId);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('submissions.create.step3', $submission->id)
                ->withErrors(['author' => $e->getMessage()]);
        }

        return redirect()->route('submissions.create.step3', $submission->id)
            ->with('success', 'Penulis anggota berhasil dihapus.');
    }

    /**
     * Store Step 3: Metadata Naskah
     */
    public function storeStep3(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::where('corresponding_author_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'title'    => 'required|string|max:500',
            'abstract' => 'required|string|min:50',
            'keywords' => 'required|string',
        ], [
            'title.required'    => 'Judul naskah wajib diisi.',
            'abstract.min'      => 'Abstrak naskah minimal memuat 50 karakter.',
            'keywords.required' => 'Kata kunci (keywords) wajib diisi.',
        ]);

        $this->submissionService->updateMetadata($submission, $validated, $user);

        return redirect()->route('submissions.create.step4', $submission->id)
            ->with('success', 'Metadata naskah berhasil disimpan. Tinjau kembali seluruh data sebelum penyerahan final.');
    }

    /**
     * Step 4: Confirmation & Summary Page
     */
    public function createStep4($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::with(['journal', 'files', 'authors'])
            ->where('corresponding_author_id', $user->id)
            ->findOrFail($id);

        return view('submissions::step4', compact('submission'));
    }

    /**
     * Finish Step 4: Submit Final Manuscript
     */
    public function finishStep4(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::where('corresponding_author_id', $user->id)->findOrFail($id);

        try {
            $this->submissionService->submitFinal($submission, $user);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('submissions.create.step4', $submission->id)
                ->withErrors(['submission' => $e->getMessage()]);
        }

        return redirect()->route('submissions.index')
            ->with('success', 'Selamat! Naskah ilmiah Anda berhasil diserahkan (submitted) dan saat ini sedang menunggu proses pemeriksaan dewan redaksi.');
    }

    /**
     * Resubmit Revised Manuscript by Author
     */
    public function resubmit(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::where('corresponding_author_id', $user->id)->findOrFail($id);

        if ($submission->status !== 'revision_required') {
            return redirect()->route('submissions.show', $submission->id)
                ->withErrors(['status' => 'Naskah ini tidak dalam status memerlukan revisi.']);
        }

        $request->validate([
            'file'           => 'required|file|mimes:docx,doc,pdf|max:20480',
            'response_notes' => 'required|string|min:10',
        ], [
            'file.required'           => 'Berkas naskah perbaikan wajib diunggah.',
            'file.mimes'              => 'Format berkas naskah perbaikan harus berupa DOC, DOCX, atau PDF.',
            'file.max'                => 'Ukuran berkas maksimal 20 MB.',
            'response_notes.required' => 'Catatan tanggapan perbaikan penulis wajib diisi.',
            'response_notes.min'      => 'Catatan tanggapan perbaikan minimal 10 karakter.',
        ]);

        $this->submissionService->uploadFile($submission, $request->file('file'), 'naskah_revisi', $user);

        try {
            $this->submissionService->transitionStatus(
                $submission,
                'resubmitted',
                $request->response_notes,
                $user
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('submissions.show', $submission->id)
                ->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('submissions.show', $submission->id)
            ->with('success', 'Naskah revisi dan catatan tanggapan Anda berhasil dikirim ke Dewan Redaksi.');
    }
}

<?php

namespace Modules\Submissions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Journals\Models\Journal;
use Modules\Submissions\Models\Submission;
use Modules\Submissions\Models\SubmissionAuthor;
use Modules\Submissions\Models\SubmissionFile;

class SubmissionsController extends Controller
{
    /**
     * Display a listing of the user's submissions.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submissions = Submission::with('journal')
            ->where('corresponding_author_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('submissions::index', compact('submissions'));
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

        $submission = Submission::create([
            'journal_id'              => $request->journal_id,
            'title'                   => 'Draf Naskah Tanpa Judul - ' . Str::random(5),
            'slug'                    => 'draft-' . Str::uuid(),
            'status'                  => 'draft',
            'corresponding_author_id' => $user->id,
            'created_by'              => $user->id,
        ]);

        // Secara otomatis daftarkan user login sebagai Penulis Utama (Author #1)
        SubmissionAuthor::create([
            'submission_id'           => $submission->id,
            'name'                    => $user->name,
            'email'                   => $user->email,
            'affiliation'             => 'Yayasan Satriabudi Dharma Setia',
            'is_corresponding_author' => true,
            'sort_order'              => 1,
            'created_by'              => $user->id,
        ]);

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

        // Jika mengunggah berkas baru
        if ($request->hasFile('file')) {
            $request->validate([
                'file'      => 'required|file|mimes:docx,doc,pdf|max:10240',
                'file_role' => 'required|in:naskah_utama,supplementary,title_page',
            ]);

            $uploadedFile = $request->file('file');
            $originalName = $uploadedFile->getClientOriginalName();
            
            $path = $uploadedFile->store('submissions/' . $submission->id, 'public');

            SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_role'     => $request->file_role,
                'disk'          => 'public',
                'path'          => $path,
                'original_name' => $originalName,
                'uploaded_by'   => $user->id,
                'created_by'    => $user->id,
            ]);

            return redirect()->route('submissions.create.step2', $submission->id)
                ->with('success', 'Berkas berhasil diunggah.');
        }

        // Cek kelayakan naskah utama
        $hasMainFile = SubmissionFile::where('submission_id', $submission->id)
            ->where('file_role', 'naskah_utama')
            ->exists();

        if (!$hasMainFile) {
            return redirect()->route('submissions.create.step2', $submission->id)
                ->withErrors(['file' => 'Anda wajib mengunggah minimal 1 berkas dengan jenis "Naskah Utama" sebelum melanjutkan.']);
        }

        // Lanjut ke Step 3 Metadata
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
        $file = SubmissionFile::where('submission_id', $submission->id)->findOrFail($fileId);

        if (Storage::disk($file->disk)->exists($file->path)) {
            Storage::disk($file->disk)->delete($file->path);
        }

        $file->delete();

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

        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'affiliation' => 'nullable|string|max:255',
        ]);

        $maxOrder = SubmissionAuthor::where('submission_id', $submission->id)->max('sort_order') ?? 0;

        SubmissionAuthor::create([
            'submission_id'           => $submission->id,
            'name'                    => $request->name,
            'email'                   => $request->email,
            'affiliation'             => $request->affiliation,
            'is_corresponding_author' => false,
            'sort_order'              => $maxOrder + 1,
            'created_by'              => $user->id,
        ]);

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
        $author = SubmissionAuthor::where('submission_id', $submission->id)->findOrFail($authorId);

        if ($author->is_corresponding_author) {
            return redirect()->route('submissions.create.step3', $submission->id)
                ->withErrors(['author' => 'Penulis utama (corresponding author) tidak dapat dihapus.']);
        }

        $author->delete();

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

        $request->validate([
            'title'    => 'required|string|max:500',
            'abstract' => 'required|string|min:50',
            'keywords' => 'required|string',
        ], [
            'title.required'    => 'Judul naskah wajib diisi.',
            'abstract.min'      => 'Abstrak naskah minimal memuat 50 karakter.',
            'keywords.required' => 'Kata kunci (keywords) wajib diisi.',
        ]);

        // Split keywords by comma
        $keywordsArray = array_map('trim', explode(',', $request->keywords));

        $submission->update([
            'title'      => $request->title,
            'slug'       => Str::slug(Str::limit($request->title, 100)) . '-' . Str::random(5),
            'abstract'   => $request->abstract,
            'keywords'   => $keywordsArray,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('submissions.index')
            ->with('success', 'Metadata naskah berhasil disimpan dan draf naskah telah diperbarui.');
    }
}

<?php

namespace Modules\Submissions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Journals\Models\Journal;
use Modules\Submissions\Models\Submission;
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

        // Tampilkan semua naskah milik user login
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

        // Buat draf naskah kosong
        $submission = Submission::create([
            'journal_id'              => $request->journal_id,
            'title'                   => 'Draf Naskah Tanpa Judul - ' . Str::random(5),
            'slug'                    => 'draft-' . Str::uuid(),
            'status'                  => 'draft',
            'corresponding_author_id' => $user->id,
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
            
            // Simpan ke storage local
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

        // Jika mengklik tombol Simpan & Lanjutkan
        // Pastikan minimal ada 1 naskah utama yang diunggah
        $hasMainFile = SubmissionFile::where('submission_id', $submission->id)
            ->where('file_role', 'naskah_utama')
            ->exists();

        if (!$hasMainFile) {
            return redirect()->route('submissions.create.step2', $submission->id)
                ->withErrors(['file' => 'Anda wajib mengunggah minimal 1 berkas dengan jenis "Naskah Utama" sebelum melanjutkan.']);
        }

        // Di sini kita bisa langsung redirect ke index submissions untuk sementara, 
        // atau lanjut ke langkah berikutnya jika sudah dikembangkan.
        return redirect()->route('submissions.index')
            ->with('success', 'Unggah berkas berhasil disimpan. Draf naskah telah diperbarui.');
    }

    /**
     * Delete uploaded file
     */
    public function deleteFileStep2($id, $fileId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $submission = Submission::where('corresponding_author_id', $user->id)->findOrFail($id);
        $file = SubmissionFile::where('submission_id', $submission->id)->findOrFail($fileId);

        // Hapus fisik berkas dari disk storage
        if (Storage::disk($file->disk)->exists($file->path)) {
            Storage::disk($file->disk)->delete($file->path);
        }

        $file->delete();

        return redirect()->route('submissions.create.step2', $submission->id)
            ->with('success', 'Berkas berhasil dihapus.');
    }
}

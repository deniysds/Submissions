<?php

namespace Modules\Submissions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Submissions\Models\ReviewerAssignment;
use Modules\Submissions\Models\SubmissionFile;

class ReviewerWorkspaceController extends Controller
{
    /**
     * Display a listing of review assignments for the logged-in reviewer.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = ReviewerAssignment::with(['round.submission.journal', 'round.submission.files'])
            ->where('reviewer_id', $user->id);

        if ($request->filled('status')) {
            $query->where('response_status', $request->status);
        }

        $assignments = $query->latest('created_at')->paginate(10);

        return view('submissions::reviewer.index', compact('assignments'));
    }

    /**
     * Display the manuscript evaluation workspace for a specific assignment.
     * Note: Double-blind peer review principle: author identity is omitted.
     */
    public function show($id)
    {
        $user = Auth::user();

        $assignment = ReviewerAssignment::with(['round.submission.journal', 'round.submission.files'])
            ->where('reviewer_id', $user->id)
            ->findOrFail($id);

        $submission = $assignment->round->submission;

        return view('submissions::reviewer.show', compact('assignment', 'submission'));
    }

    /**
     * Accept or decline the reviewer invitation.
     */
    public function respond(Request $request, $id)
    {
        $user = Auth::user();

        $assignment = ReviewerAssignment::where('reviewer_id', $user->id)->findOrFail($id);

        $request->validate([
            'response_status' => 'required|in:accepted,declined',
        ]);

        if ($assignment->response_status !== 'pending') {
            return redirect()->route('reviewer.assignments.show', $assignment->id)
                ->with('warning', 'Undangan telaah ini sudah direspons sebelumnya.');
        }

        $assignment->update([
            'response_status' => $request->response_status,
            'updated_by'      => $user->id,
        ]);

        $message = $request->response_status === 'accepted'
            ? 'Terima kasih atas kesediaan Anda menelaah naskah ini. Silakan unduh naskah dan berikan evaluasi.'
            : 'Penolakan undangan telaah telah dicatat. Dewan redaksi akan diberitahu.';

        return redirect()->route('reviewer.assignments.show', $assignment->id)
            ->with('success', $message);
    }

    /**
     * Submit peer review evaluation and recommendation.
     */
    public function submitReview(Request $request, $id)
    {
        $user = Auth::user();

        $assignment = ReviewerAssignment::where('reviewer_id', $user->id)->findOrFail($id);

        if ($assignment->response_status !== 'accepted') {
            return redirect()->route('reviewer.assignments.show', $assignment->id)
                ->withErrors(['recommendation' => 'Anda harus menerima undangan telaah terlebih dahulu sebelum mengirim hasil evaluasi.']);
        }

        $request->validate([
            'recommendation'                  => 'required|in:accept,minor_revision,major_revision,reject',
            'comments_for_author'             => 'required|string|min:15',
            'confidential_comments_to_editor' => 'nullable|string',
        ], [
            'recommendation.required'      => 'Rekomendasi telaah wajib dipilih.',
            'comments_for_author.required' => 'Komentar untuk penulis wajib diisi.',
            'comments_for_author.min'      => 'Komentar untuk penulis minimal 15 karakter.',
        ]);

        $assignment->update([
            'recommendation'                  => $request->recommendation,
            'comments_for_author'             => $request->comments_for_author,
            'confidential_comments_to_editor' => $request->confidential_comments_to_editor,
            'response_status'                 => 'completed',
            'submitted_at'                    => now(),
            'updated_by'                      => $user->id,
        ]);

        return redirect()->route('reviewer.assignments.show', $assignment->id)
            ->with('success', 'Hasil penelaahan sejawat berhasil dikirimkan ke dewan redaksi.');
    }

    /**
     * Download manuscript file safely for review.
     */
    public function downloadFile($id, $fileId)
    {
        $user = Auth::user();

        $assignment = ReviewerAssignment::where('reviewer_id', $user->id)->findOrFail($id);
        $submission = $assignment->round->submission;

        $file = SubmissionFile::where('submission_id', $submission->id)->findOrFail($fileId);

        $disk = $file->disk ?: 'public';
        if (!Storage::disk($disk)->exists($file->path)) {
            abort(404, 'Berkas naskah tidak ditemukan di server.');
        }

        return Storage::disk($disk)->download($file->path, $file->original_name);
    }
}

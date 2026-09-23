<?php

namespace Modules\Submissions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Modules\Submissions\Mail\ReviewCompletedMail;
use Modules\Submissions\Models\ReviewerAssignment;
use Modules\Submissions\Models\SubmissionFile;
use Modules\Usermanagement\Models\User;

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

        $submission = $assignment->round?->submission;
        $editor = $submission?->editor;
        $editorEmail = $editor?->email;

        if (!$editorEmail) {
            $editorUser = User::whereHas('roles', function ($q) {
                $q->where('name', 'editor');
            })->first();
            $editorEmail = $editorUser?->email;
        }

        if ($editorEmail && $submission) {
            try {
                Mail::to($editorEmail)->send(
                    new ReviewCompletedMail($submission, $assignment, $editor)
                );
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim email review selesai ke editor: ' . $e->getMessage());
            }
        }

        return redirect()->route('reviewer.assignments.show', $assignment->id)
            ->with('success', 'Hasil penelaahan sejawat berhasil dikirimkan ke dewan redaksi.');
    }

    /**
     * Handle direct invitation token link from email.
     */
    public function handleInvitationToken(string $token)
    {
        $assignment = ReviewerAssignment::where('invitation_token', $token)->firstOrFail();

        $user = Auth::user();
        if (!$user) {
            session(['url.intended' => route('reviewer.assignments.show', $assignment->id)]);
            return redirect()->route('login');
        }

        if ((int) $user->id !== (int) $assignment->reviewer_id) {
            return redirect()->route('dashboard')
                ->with('error', 'Tautan undangan ini ditujukan untuk akun reviewer lain.');
        }

        return redirect()->route('reviewer.assignments.show', $assignment->id);
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

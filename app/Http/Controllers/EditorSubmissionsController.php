<?php

namespace Modules\Submissions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Submissions\Models\Submission;

class EditorSubmissionsController extends Controller
{
    /**
     * Display a listing of submissions submitted for Editorial Review.
     */
    public function index(Request $request)
    {
        $query = Submission::with(['journal', 'author'])
            ->where('status', '!=', 'draft');

        // Filter pencarian berdasarkan kata kunci / judul
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->latest('submitted_at')->paginate(10);

        return view('submissions::editor.index', compact('submissions'));
    }

    /**
     * Display the specified manuscript details for Editorial Evaluation.
     */
    public function show($id)
    {
        $submission = Submission::with(['journal', 'files', 'authors', 'author'])
            ->where('status', '!=', 'draft')
            ->findOrFail($id);

        return view('submissions::editor.show', compact('submission'));
    }

    /**
     * Make Editorial Decision (Accept, Reject, Revision, In-Review).
     */
    public function makeDecision(Request $request, $id)
    {
        $user = Auth::user();
        $submission = Submission::where('status', '!=', 'draft')->findOrFail($id);

        $request->validate([
            'status' => 'required|in:in_review,accepted,rejected,revision_required',
            'notes'  => 'nullable|string',
        ], [
            'status.required' => 'Keputusan dewan redaksi wajib dipilih.',
            'status.in'       => 'Status keputusan tidak valid.',
        ]);

        $submission->update([
            'status'             => $request->status,
            'assigned_editor_id' => $user->id,
            'decision_at'        => now(),
            'updated_by'         => $user->id,
        ]);

        $statusLabels = [
            'in_review'         => 'Penelaahan (In-Review)',
            'accepted'          => 'Diterima (Accepted)',
            'rejected'          => 'Ditolak (Rejected)',
            'revision_required' => 'Revisi Diperlukan (Revision Required)',
        ];

        return redirect()->route('editor.submissions.show', $submission->id)
            ->with('success', 'Keputusan redaksi berhasil diperbarui menjadi: ' . ($statusLabels[$request->status] ?? $request->status));
    }
}

<?php

namespace Modules\Submissions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Journals\Models\Journal;

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

        return view('submissions::index');
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
     * Store Step 1 submission choices into session or database draft.
     */
    public function store(Request $request)
    {
        $request->validate([
            'journal_id' => 'required|exists:journals,id',
            'checklist'  => 'required|array|min:3',
        ], [
            'checklist.min' => 'Anda wajib menyetujui seluruh kriteria checklist persyaratan penulis sebelum melanjutkan.',
        ]);

        return redirect()->route('submissions.index')
            ->with('success', 'Persyaratan Step 1 berhasil disetujui. Naskah draf dibuat.');
    }
}

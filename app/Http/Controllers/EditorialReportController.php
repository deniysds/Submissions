<?php

namespace Modules\Submissions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Journals\Models\Journal;
use Modules\Submissions\Models\ReviewerAssignment;
use Modules\Submissions\Models\Submission;

class EditorialReportController extends Controller
{
    /**
     * Display the editorial and reviewer performance report dashboard.
     */
    public function index(Request $request)
    {
        $data = $this->gatherReportData($request);

        return view('submissions::editor.reports.index', $data);
    }

    /**
     * Export the editorial & reviewer performance metrics into CSV format.
     */
    public function exportCsv(Request $request)
    {
        $data = $this->gatherReportData($request);

        $fileName = 'editorial_performance_report_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            // Header Dokumen Laporan
            fputcsv($file, ['LAPORAN STATISTIK KINERJA REDAKSI & REVIEWER - IGNITE']);
            fputcsv($file, ['Tanggal Unduh', now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []);

            // Ringkasan Metrik
            fputcsv($file, ['RINGKASAN METRIK EDITORIAL']);
            fputcsv($file, ['Metrik', 'Nilai']);
            fputcsv($file, ['Total Naskah Terdaftar', $data['totalSubmissions']]);
            fputcsv($file, ['Naskah Diterima (Accepted & Published)', $data['acceptedCount']]);
            fputcsv($file, ['Naskah Ditolak (Rejected)', $data['rejectedCount']]);
            fputcsv($file, ['Naskah Dalam Proses Telaah', $data['inProgressCount']]);
            fputcsv($file, ['Tingkat Penerimaan (Acceptance Rate)', $data['acceptanceRate'] . '%']);
            fputcsv($file, ['Tingkat Penolakan (Rejection Rate)', $data['rejectionRate'] . '%']);
            fputcsv($file, ['Rata-rata Waktu Proses Keputusan Redaksi (hari)', $data['avgTurnaroundDays'] . ' hari']);
            fputcsv($file, ['Rata-rata Waktu Telaah Mitra Bestari (hari)', $data['overallAvgReviewDays'] . ' hari']);
            fputcsv($file, []);

            // Distribusi Status Naskah
            fputcsv($file, ['DISTRIBUSI STATUS NASKAH']);
            fputcsv($file, ['Status', 'Jumlah Naskah']);
            foreach ($data['statusCounts'] as $status => $count) {
                fputcsv($file, [ucwords(str_replace('_', ' ', $status)), $count]);
            }
            fputcsv($file, []);

            // Tabel Kinerja Mitra Bestari
            fputcsv($file, ['KINERJA MITRA BESTARI (PEER REVIEWERS)']);
            fputcsv($file, [
                'Nama Reviewer',
                'Email Reviewer',
                'Total Penugasan',
                'Selesai Ditelaah',
                'Sedang Berjalan',
                'Menolak Tugas',
                'Terlambat (Overdue)',
                'Rata-rata Waktu Telaah (hari)',
                'Rekomendasi (Accept / Revisions / Reject)',
            ]);

            foreach ($data['reviewerStats'] as $rev) {
                $recomSummary = sprintf(
                    'Accept: %d | Revisions: %d | Reject: %d',
                    $rev['rec_accept'],
                    $rev['rec_revision'],
                    $rev['rec_reject']
                );

                fputcsv($file, [
                    $rev['name'],
                    $rev['email'],
                    $rev['total'],
                    $rev['completed'],
                    $rev['pending'],
                    $rev['declined'],
                    $rev['overdue'],
                    $rev['avg_days'] !== null ? $rev['avg_days'] . ' hari' : '-',
                    $recomSummary,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Gather and aggregate all performance metrics.
     */
    protected function gatherReportData(Request $request): array
    {
        $journalId = $request->input('journal_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Query Naskah
        $submissionQuery = Submission::with(['journal', 'author', 'editor'])
            ->where('status', '!=', 'draft');

        if ($journalId) {
            $submissionQuery->where('journal_id', $journalId);
        }
        if ($dateFrom) {
            $submissionQuery->whereDate('submitted_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $submissionQuery->whereDate('submitted_at', '<=', $dateTo);
        }

        $submissions = $submissionQuery->get();
        $totalSubmissions = $submissions->count();

        // Penghitungan status
        $statusCounts = [
            'submitted'           => 0,
            'under_editor_review' => 0,
            'under_review'        => 0,
            'revision_required'   => 0,
            'resubmitted'         => 0,
            'accepted'            => 0,
            'published'           => 0,
            'rejected'            => 0,
        ];

        foreach ($submissions as $s) {
            if (isset($statusCounts[$s->status])) {
                $statusCounts[$s->status]++;
            } else {
                $statusCounts[$s->status] = 1;
            }
        }

        $acceptedCount = ($statusCounts['accepted'] ?? 0) + ($statusCounts['published'] ?? 0);
        $rejectedCount = $statusCounts['rejected'] ?? 0;
        $inProgressCount = $totalSubmissions - $acceptedCount - $rejectedCount;
        $decidedCount = $acceptedCount + $rejectedCount;

        $acceptanceRate = $decidedCount > 0 ? round(($acceptedCount / $decidedCount) * 100, 1) : 0;
        $rejectionRate = $decidedCount > 0 ? round(($rejectedCount / $decidedCount) * 100, 1) : 0;

        // Penghitungan rata-rata waktu keputusan redaksi (turnaround days)
        $decidedSubmissions = $submissions->filter(fn($s) => $s->submitted_at && $s->decision_at);
        $avgTurnaroundDays = $decidedSubmissions->count() > 0
            ? round($decidedSubmissions->avg(fn($s) => max(0, $s->submitted_at->diffInDays($s->decision_at))), 1)
            : 0;

        // Query Penugasan Mitra Bestari
        $assignmentQuery = ReviewerAssignment::with(['reviewer', 'round.submission'])
            ->whereHas('round.submission', function ($q) use ($journalId, $dateFrom, $dateTo) {
                $q->where('status', '!=', 'draft');
                if ($journalId) {
                    $q->where('journal_id', $journalId);
                }
                if ($dateFrom) {
                    $q->whereDate('submitted_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $q->whereDate('submitted_at', '<=', $dateTo);
                }
            });

        $assignments = $assignmentQuery->get();

        $completedAssignments = $assignments->filter(fn($a) => $a->submitted_at !== null || $a->response_status === 'completed');
        $overallAvgReviewDays = $completedAssignments->count() > 0
            ? round($completedAssignments->avg(function ($a) {
                if ($a->submitted_at && $a->created_at) {
                    return max(0, $a->created_at->diffInDays($a->submitted_at));
                }
                return 0;
            }), 1)
            : 0;

        // Pengelompokan data per mitra bestari
        $grouped = $assignments->groupBy('reviewer_id');
        $reviewerStats = [];

        foreach ($grouped as $reviewerId => $items) {
            $reviewer = $items->first()->reviewer;
            $total = $items->count();
            $completed = $items->filter(fn($a) => $a->submitted_at !== null || $a->response_status === 'completed')->count();
            $declined = $items->where('response_status', 'declined')->count();
            $pending = $items->filter(fn($a) => in_array($a->response_status, ['pending', 'accepted']) && $a->submitted_at === null)->count();
            $overdue = $items->filter(fn($a) => $a->submitted_at === null && $a->due_date && $a->due_date->isPast())->count();

            $completedList = $items->filter(fn($a) => $a->submitted_at !== null && $a->created_at !== null);
            $avgDays = $completedList->count() > 0
                ? round($completedList->avg(fn($a) => max(0, $a->created_at->diffInDays($a->submitted_at))), 1)
                : null;

            $recAccept = $items->where('recommendation', 'accept')->count();
            $recRevision = $items->filter(fn($a) => in_array($a->recommendation, ['minor_revision', 'major_revision']))->count();
            $recReject = $items->where('recommendation', 'reject')->count();

            $reviewerStats[] = [
                'reviewer_id'  => $reviewerId,
                'name'         => $reviewer?->name ?? 'Reviewer #' . $reviewerId,
                'email'        => $reviewer?->email ?? '-',
                'total'        => $total,
                'completed'    => $completed,
                'pending'      => $pending,
                'declined'     => $declined,
                'overdue'      => $overdue,
                'avg_days'     => $avgDays,
                'rec_accept'   => $recAccept,
                'rec_revision' => $recRevision,
                'rec_reject'   => $recReject,
            ];
        }

        // Urutkan berdasarkan total penugasan terbanyak
        usort($reviewerStats, fn($a, $b) => $b['total'] <=> $a['total']);

        $journals = Journal::where('is_active', true)->orderBy('name')->get();

        return [
            'totalSubmissions'     => $totalSubmissions,
            'acceptedCount'        => $acceptedCount,
            'rejectedCount'        => $rejectedCount,
            'inProgressCount'      => $inProgressCount,
            'acceptanceRate'       => $acceptanceRate,
            'rejectionRate'        => $rejectionRate,
            'avgTurnaroundDays'    => $avgTurnaroundDays,
            'overallAvgReviewDays' => $overallAvgReviewDays,
            'statusCounts'         => $statusCounts,
            'reviewerStats'        => $reviewerStats,
            'journals'             => $journals,
            'selectedJournalId'    => $journalId,
            'dateFrom'             => $dateFrom,
            'dateTo'               => $dateTo,
        ];
    }
}

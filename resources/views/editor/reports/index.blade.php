@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('editor.reports.index') }}
@endsection

@section('content')
    <div class="grid w-full space-y-6">
        <!-- Header & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Laporan Statistik Kinerja Redaksi & Reviewer</h1>
                <p class="text-xs text-gray-500 mt-1">Pantau analitik kecepatan proses editorial, rasio penerimaan/penolakan naskah, dan performa penelaahan mitra bestari.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('editor.submissions.index') }}" class="kt-btn kt-btn-outline text-xs px-3 py-2">
                    <i class="ki-filled ki-arrow-left mr-1"></i> Kembali ke Naskah
                </a>
                <a href="{{ route('editor.reports.export', request()->query()) }}" class="kt-btn kt-btn-primary text-white text-xs font-semibold px-4 py-2.5">
                    <i class="ki-filled ki-file-down text-white mr-1"></i> Ekspor Laporan CSV
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="kt-card p-4">
            <form action="{{ route('editor.reports.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Filter Jurnal</label>
                    <select name="journal_id" class="kt-select w-full text-xs">
                        <option value="">-- Semua Jurnal --</option>
                        @foreach($journals as $j)
                            <option value="{{ $j->id }}" {{ (string)$selectedJournalId === (string)$j->id ? 'selected' : '' }}>
                                {{ $j->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Mulai Penyerahan</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="kt-input w-full text-xs" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Akhir Penyerahan</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="kt-input w-full text-xs" />
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="kt-btn kt-btn-primary text-white text-xs px-4 py-2.5 grow">
                        <i class="ki-filled ki-filter mr-1"></i> Terapkan Filter
                    </button>
                    @if($selectedJournalId || $dateFrom || $dateTo)
                        <a href="{{ route('editor.reports.index') }}" class="kt-btn kt-btn-outline text-xs px-3 py-2.5">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Naskah -->
            <div class="kt-card p-5 border border-blue-100 bg-gradient-to-br from-white to-blue-50/40">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Total Naskah Masuk</span>
                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <i class="ki-filled ki-document text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-gray-900">{{ number_format($totalSubmissions) }}</span>
                    <span class="text-xs text-gray-500 font-medium">naskah aktif</span>
                </div>
                <p class="text-[11px] text-gray-500 mt-2">Naskah yang melewati tahap penyerahan (bukan draf).</p>
            </div>

            <!-- Rata-rata Durasi Keputusan Redaksi -->
            <div class="kt-card p-5 border border-emerald-100 bg-gradient-to-br from-white to-emerald-50/40">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Kecepatan Putusan Redaksi</span>
                    <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <i class="ki-filled ki-time text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-gray-900">{{ $avgTurnaroundDays }}</span>
                    <span class="text-xs text-gray-600 font-semibold">hari rata-rata</span>
                </div>
                <p class="text-[11px] text-gray-500 mt-2">Dihitung sejak naskah diserahkan hingga keputusan akhir.</p>
            </div>

            <!-- Acceptance Rate -->
            <div class="kt-card p-5 border border-green-100 bg-gradient-to-br from-white to-green-50/40">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-green-600 uppercase tracking-wider">Rasio Penerimaan Naskah</span>
                    <div class="w-9 h-9 rounded-lg bg-green-100 text-green-600 flex items-center justify-center">
                        <i class="ki-filled ki-check-circle text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-green-700">{{ $acceptanceRate }}%</span>
                    <span class="text-xs text-gray-500 font-medium">({{ $acceptedCount }} diterima)</span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-gray-500 mt-2">
                    <span>Penolakan: <strong class="text-red-600">{{ $rejectionRate }}%</strong> ({{ $rejectedCount }})</span>
                    <span>Dalam telaah: <strong>{{ $inProgressCount }}</strong></span>
                </div>
            </div>

            <!-- Rata-rata Waktu Reviewer -->
            <div class="kt-card p-5 border border-purple-100 bg-gradient-to-br from-white to-purple-50/40">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-purple-600 uppercase tracking-wider">Kecepatan Telaah Reviewer</span>
                    <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                        <i class="ki-filled ki-user-tick text-lg"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-gray-900">{{ $overallAvgReviewDays }}</span>
                    <span class="text-xs text-gray-600 font-semibold">hari rata-rata</span>
                </div>
                <p class="text-[11px] text-gray-500 mt-2">Durasi mitra bestari menyelesaikan evaluasi penugasan.</p>
            </div>
        </div>

        <!-- Distribusi Status Naskah -->
        <div class="kt-card p-6">
            <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="ki-filled ki-chart-simple text-primary"></i> Distribusi Status Naskah Ilmiah
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                <div class="p-3 rounded-xl border border-gray-200 bg-gray-50 text-center">
                    <span class="text-[11px] font-semibold text-gray-500 uppercase block">Diserahkan</span>
                    <span class="text-xl font-bold text-gray-800">{{ $statusCounts['submitted'] ?? 0 }}</span>
                </div>
                <div class="p-3 rounded-xl border border-blue-200 bg-blue-50/60 text-center">
                    <span class="text-[11px] font-semibold text-blue-700 uppercase block">Review Editor</span>
                    <span class="text-xl font-bold text-blue-800">{{ $statusCounts['under_editor_review'] ?? 0 }}</span>
                </div>
                <div class="p-3 rounded-xl border border-indigo-200 bg-indigo-50/60 text-center">
                    <span class="text-[11px] font-semibold text-indigo-700 uppercase block">Mitra Bestari</span>
                    <span class="text-xl font-bold text-indigo-800">{{ $statusCounts['under_review'] ?? 0 }}</span>
                </div>
                <div class="p-3 rounded-xl border border-amber-200 bg-amber-50/60 text-center">
                    <span class="text-[11px] font-semibold text-amber-700 uppercase block">Butuh Revisi</span>
                    <span class="text-xl font-bold text-amber-800">{{ $statusCounts['revision_required'] ?? 0 }}</span>
                </div>
                <div class="p-3 rounded-xl border border-teal-200 bg-teal-50/60 text-center">
                    <span class="text-[11px] font-semibold text-teal-700 uppercase block">Revisi Masuk</span>
                    <span class="text-xl font-bold text-teal-800">{{ $statusCounts['resubmitted'] ?? 0 }}</span>
                </div>
                <div class="p-3 rounded-xl border border-emerald-200 bg-emerald-50/60 text-center">
                    <span class="text-[11px] font-semibold text-emerald-700 uppercase block">Diterima</span>
                    <span class="text-xl font-bold text-emerald-800">{{ $statusCounts['accepted'] ?? 0 }}</span>
                </div>
                <div class="p-3 rounded-xl border border-green-200 bg-green-50/60 text-center">
                    <span class="text-[11px] font-semibold text-green-700 uppercase block">Terbit</span>
                    <span class="text-xl font-bold text-green-800">{{ $statusCounts['published'] ?? 0 }}</span>
                </div>
                <div class="p-3 rounded-xl border border-red-200 bg-red-50/60 text-center">
                    <span class="text-[11px] font-semibold text-red-700 uppercase block">Ditolak</span>
                    <span class="text-xl font-bold text-red-800">{{ $statusCounts['rejected'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Tabel Performa Mitra Bestari -->
        <div class="kt-card">
            <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Performa & Produktivitas Mitra Bestari</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Analisis kuantitatif penugasan review, tingkat penyelesaian tepat waktu, dan rata-rata durasi telaah.</p>
                </div>
                <span class="kt-badge kt-badge-sm kt-badge-light-primary font-bold">
                    {{ count($reviewerStats) }} Reviewer Terdaftar
                </span>
            </div>
            <div class="p-5">
                <div class="table-responsive">
                    <table class="table w-full text-xs text-left align-middle">
                        <thead>
                            <tr class="text-gray-500 font-semibold border-b border-gray-200 uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-3">Nama Mitra Bestari</th>
                                <th class="py-3 px-3 text-center">Total Ditugaskan</th>
                                <th class="py-3 px-3 text-center">Selesai</th>
                                <th class="py-3 px-3 text-center">Sedang Berjalan</th>
                                <th class="py-3 px-3 text-center">Menolak</th>
                                <th class="py-3 px-3 text-center">Terlambat</th>
                                <th class="py-3 px-3 text-center">Rata-rata Telaah</th>
                                <th class="py-3 px-3 text-center">Distribusi Rekomendasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($reviewerStats as $rev)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="py-3.5 px-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">
                                                {{ strtoupper(substr($rev['name'], 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="font-bold text-gray-900 block">{{ $rev['name'] }}</span>
                                                <span class="text-[11px] text-gray-500">{{ $rev['email'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-bold text-gray-900">
                                        <span class="kt-badge kt-badge-sm kt-badge-light-dark font-bold px-2">
                                            {{ $rev['total'] }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="kt-badge kt-badge-sm kt-badge-light-success font-semibold px-2">
                                            {{ $rev['completed'] }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        @if($rev['pending'] > 0)
                                            <span class="kt-badge kt-badge-sm kt-badge-light-primary font-semibold px-2">
                                                {{ $rev['pending'] }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        @if($rev['declined'] > 0)
                                            <span class="kt-badge kt-badge-sm kt-badge-light-warning font-semibold px-2">
                                                {{ $rev['declined'] }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        @if($rev['overdue'] > 0)
                                            <span class="kt-badge kt-badge-sm kt-badge-light-danger font-bold px-2">
                                                {{ $rev['overdue'] }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">0</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-medium">
                                        @if($rev['avg_days'] !== null)
                                            <span class="text-emerald-700 font-bold">{{ $rev['avg_days'] }} hari</span>
                                        @else
                                            <span class="text-gray-400 italic">Belum ada</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span class="kt-badge kt-badge-xs kt-badge-light-success" title="Terima (Accept)">
                                                A: {{ $rev['rec_accept'] }}
                                            </span>
                                            <span class="kt-badge kt-badge-xs kt-badge-light-warning" title="Revisi">
                                                R: {{ $rev['rec_revision'] }}
                                            </span>
                                            <span class="kt-badge kt-badge-xs kt-badge-light-danger" title="Tolak (Reject)">
                                                X: {{ $rev['rec_reject'] }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-10 text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <i class="ki-filled ki-folder-missing text-4xl mb-2 text-gray-300"></i>
                                            <p class="font-medium">Belum ada penugasan telaah mitra bestari pada filter ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

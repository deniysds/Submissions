@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('reviewer.assignments.index') }}
@endsection

@section('content')
    <div class="grid w-full space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Ruang Kerja Mitra Bestari (Peer Reviewer)</h1>
                <p class="text-xs text-gray-500 mt-1">Daftar manuskrip ilmiah yang ditugaskan kepada Anda untuk dievaluasi secara double-blind oleh dewan redaksi.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-green-50 text-green-700 border border-green-200 text-sm flex items-center gap-2">
                <i class="ki-filled ki-check-circle text-green-600 text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('warning'))
            <div class="p-4 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 text-sm flex items-center gap-2">
                <i class="ki-filled ki-information-2 text-amber-600 text-lg"></i>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        <!-- Filter & Search -->
        <div class="kt-card p-4">
            <form action="{{ route('reviewer.assignments.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="w-full sm:w-56">
                    <select name="status" class="kt-select w-full text-xs">
                        <option value="">-- Semua Status Respon --</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Konfirmasi (Pending)</option>
                        <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Disetujui / Sedang Ditelaah (Accepted)</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai Dievaluasi (Completed)</option>
                        <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>Ditolak (Declined)</option>
                    </select>
                </div>
                <button type="submit" class="kt-btn kt-btn-primary text-white text-xs px-4">
                    Filter
                </button>
                @if(request()->has('status'))
                    <a href="{{ route('reviewer.assignments.index') }}" class="kt-btn kt-btn-outline text-xs px-3">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabel Daftar Penugasan Reviewer -->
        <div class="kt-card">
            <div class="p-6">
                <div class="table-responsive">
                    <table class="table w-full text-xs text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-3">Jurnal</th>
                                <th class="px-4 py-3">Judul Manuskrip</th>
                                <th class="px-4 py-3 text-center">Putaran</th>
                                <th class="px-4 py-3">Batas Waktu</th>
                                <th class="px-4 py-3">Status Undangan</th>
                                <th class="px-4 py-3">Rekomendasi</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-150">
                            @forelse($assignments as $assignment)
                                @php
                                    $submission = $assignment->round?->submission;
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-gray-800">
                                        {{ $submission?->journal?->short_name ?? $submission?->journal?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 max-w-sm">
                                        <a href="{{ route('reviewer.assignments.show', $assignment->id) }}" class="font-bold text-gray-900 hover:text-red-600 transition block truncate">
                                            {{ $submission?->title ?? 'Naskah Tidak Ditemukan' }}
                                        </a>
                                        <span class="text-[10px] text-gray-400">Kode: #IGN-{{ str_pad($submission?->id ?? 0, 5, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700">
                                            Putaran {{ $assignment->round?->round_number ?? 1 }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $assignment->due_date ? $assignment->due_date->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($assignment->response_status === 'pending')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                Menunggu Konfirmasi
                                            </span>
                                        @elseif($assignment->response_status === 'accepted')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                Sedang Ditelaah
                                            </span>
                                        @elseif($assignment->response_status === 'completed')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-green-50 text-green-700 border border-green-200">
                                                Selesai Ditelaah
                                            </span>
                                        @elseif($assignment->response_status === 'declined')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-700 border border-red-200">
                                                Undangan Ditolak
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                                {{ $assignment->response_status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($assignment->recommendation === 'accept')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-green-50 text-green-700 border border-green-200">Terima (Accept)</span>
                                        @elseif($assignment->recommendation === 'minor_revision')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-cyan-50 text-cyan-700 border border-cyan-200">Revisi Minor</span>
                                        @elseif($assignment->recommendation === 'major_revision')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Revisi Mayor</span>
                                        @elseif($assignment->recommendation === 'reject')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-700 border border-red-200">Tolak (Reject)</span>
                                        @else
                                            <span class="text-gray-400 text-[11px]">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('reviewer.assignments.show', $assignment->id) }}" class="kt-btn kt-btn-xs kt-btn-primary text-white">
                                            Buka Lembar Telaah
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-gray-500">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <i class="ki-filled ki-document-filter text-4xl text-gray-300"></i>
                                            <p class="text-xs font-medium">Belum ada penugasan telaah manuskrip untuk akun Anda saat ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($assignments->hasPages())
                    <div class="pt-4 border-t border-gray-100">
                        {{ $assignments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

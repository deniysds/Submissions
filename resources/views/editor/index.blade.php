@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('editor.submissions.index') }}
@endsection

@section('content')
    <div class="grid w-full space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Penelaahan Redaksi (Editorial Review)</h1>
                <p class="text-xs text-gray-500 mt-1">Daftar naskah ilmiah yang telah diserahkan oleh penulis untuk dievaluasi oleh Dewan Redaksi IGNITE.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('editor.reports.index') }}" class="kt-btn kt-btn-outline-primary text-xs font-semibold px-4 py-2.5">
                    <i class="ki-filled ki-chart-line-up mr-1"></i> Laporan Statistik Kinerja
                </a>
                <a href="{{ route('editor.submissions.export', request()->query()) }}" class="kt-btn kt-btn-primary text-white text-xs font-semibold px-4 py-2.5">
                    <i class="ki-filled ki-file-down text-white mr-1"></i> Ekspor Laporan CSV
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-green-50 text-green-700 border border-green-200 text-sm flex items-center gap-2">
                <i class="ki-filled ki-check-circle text-green-600 text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter & Search Bar -->
        <div class="kt-card p-4">
            <form action="{{ route('editor.submissions.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="grow relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul naskah..." class="kt-input w-full text-xs pl-9" />
                    <i class="ki-filled ki-magnifier absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                </div>
                <div class="w-full sm:w-48">
                    <select name="status" class="kt-select w-full text-xs">
                        <option value="">-- Semua Status --</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Diserahkan (Submitted)</option>
                        <option value="in_review" {{ request('status') === 'in_review' ? 'selected' : '' }}>Penelaahan (In-Review)</option>
                        <option value="revision_required" {{ request('status') === 'revision_required' ? 'selected' : '' }}>Butuh Revisi</option>
                        <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Diterima (Accepted)</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak (Rejected)</option>
                    </select>
                </div>
                <button type="submit" class="kt-btn kt-btn-primary text-white text-xs px-4">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('editor.submissions.index') }}" class="kt-btn kt-btn-outline text-xs px-3">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabel Daftar Naskah Redaksi -->
        <div class="kt-card">
            <div class="p-6">
                <div class="table-responsive">
                    <table class="table w-full text-xs text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-3">Jurnal Tujuan</th>
                                <th class="px-4 py-3">Judul Naskah</th>
                                <th class="px-4 py-3">Penulis Utama</th>
                                <th class="px-4 py-3">Status Redaksi</th>
                                <th class="px-4 py-3">Tgl Diserahkan</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-150">
                            @forelse($submissions as $sub)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $sub->journal?->short_name ?? $sub->journal?->name }}</td>
                                    <td class="px-4 py-3 max-w-xs truncate text-gray-900 font-bold">
                                        <a href="{{ route('editor.submissions.show', $sub->id) }}" class="hover:text-red-600 transition">{{ $sub->title }}</a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 font-medium">{{ $sub->author?->name ?? 'Penulis' }}</td>
                                    <td class="px-4 py-3">
                                        @if($sub->status === 'submitted')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">Baru Diserahkan</span>
                                        @elseif($sub->status === 'in_review')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-100">Penelaahan (In-Review)</span>
                                        @elseif($sub->status === 'revision_required')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-100">Revisi Diperlukan</span>
                                        @elseif($sub->status === 'accepted')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-green-50 text-green-700 border border-green-100">Diterima (Accepted)</span>
                                        @elseif($sub->status === 'rejected')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-700 border border-red-100">Ditolak (Rejected)</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700 border border-gray-200">{{ $sub->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">{{ $sub->submitted_at ? $sub->submitted_at->format('d M Y') : '-' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('editor.submissions.show', $sub->id) }}" class="kt-btn kt-btn-xs kt-btn-ghost text-primary">
                                            <i class="ki-filled ki-eye"></i> Evaluasi Naskah
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada naskah ilmiah yang perlu ditelaah.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $submissions->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

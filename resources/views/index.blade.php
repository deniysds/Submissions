@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('submissions') }}
@endsection

@section('content')
    <div class="grid w-full space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Naskah Saya (Submissions)</h1>
                <p class="text-xs text-gray-500 mt-1">Daftar pengajuan naskah ilmiah Anda yang sedang dalam alur penelaahan dan publikasi.</p>
            </div>
            <a href="{{ route('submissions.create.step1') }}" class="kt-btn kt-btn-primary text-white">
                <i class="ki-filled ki-plus text-white mr-1"></i> Ajukan Naskah Baru
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-green-50 text-green-700 border border-green-200 text-sm flex items-center gap-2">
                <i class="ki-filled ki-check-circle text-green-600 text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($submissions->isNotEmpty())
            <div class="kt-card">
                <div class="p-6">
                    <div class="table-responsive">
                        <table class="table w-full text-xs text-left">
                            <thead class="bg-gray-50 text-gray-600 uppercase font-semibold">
                                <tr>
                                    <th class="px-4 py-3">Jurnal</th>
                                    <th class="px-4 py-3">Judul Naskah</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Tanggal Dibuat</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-150">
                                @foreach($submissions as $sub)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $sub->journal?->short_name ?? $sub->journal?->name }}</td>
                                        <td class="px-4 py-3 max-w-sm truncate text-gray-700">{{ $sub->title }}</td>
                                        <td class="px-4 py-3">
                                            @if($sub->status === 'draft')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600 border border-gray-200">Draf</span>
                                            @elseif($sub->status === 'submitted')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">Diserahkan</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-green-50 text-green-700 border border-green-100">{{ $sub->status }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-500">{{ $sub->created_at->format('d M Y') }}</td>
                                        <td class="px-4 py-3 text-right">
                                            @if($sub->status === 'draft')
                                                <a href="{{ route('submissions.create.step2', $sub->id) }}" class="kt-btn kt-btn-xs kt-btn-ghost text-warning">
                                                    <i class="ki-filled ki-pencil"></i> Lanjutkan
                                                </a>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $submissions->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="kt-card p-6 text-center py-12 space-y-4">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center text-2xl">
                    <i class="ki-filled ki-document"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="font-bold text-gray-800 text-base">Belum Ada Naskah Diajukan</h3>
                    <p class="text-xs text-gray-500 max-w-md mx-auto">Anda belum memiliki draf atau riwayat pengajuan naskah. Klik tombol di bawah untuk memulai pengajuan naskah ilmiah baru.</p>
                </div>
                <div>
                    <a href="{{ route('submissions.create.step1') }}" class="kt-btn kt-btn-primary text-white text-xs">
                        <i class="ki-filled ki-cloud-change text-white mr-1"></i> Mulai Pengajuan Naskah (Step 1)
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection

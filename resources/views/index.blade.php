@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('submissions') }}
@endsection

@section('content')
    <div class="grid w-full space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Naskah Saya (Submissions)</h1>
                <p class="text-xs text-gray-500 mt-1">Daftar pengajuan naskah ilmiah Anda yang sedang dalam alur penelahaan dan publikasi.</p>
            </div>
            <a href="{{ route('submissions.create') }}" class="kt-btn kt-btn-primary text-white">
                <i class="ki-filled ki-plus text-white mr-1"></i> Ajukan Naskah Baru
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-green-50 text-green-700 border border-green-200 text-sm flex items-center gap-2">
                <i class="ki-filled ki-check-circle text-green-600 text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="kt-card p-6 text-center py-12 space-y-4">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center text-2xl">
                <i class="ki-filled ki-document"></i>
            </div>
            <div class="space-y-1">
                <h3 class="font-bold text-gray-800 text-base">Belum Ada Naskah Diajukan</h3>
                <p class="text-xs text-gray-500 max-w-md mx-auto">Anda belum memiliki draf atau riwayat pengajuan naskah. Klik tombol di bawah untuk memulai pengajuan naskah ilmiah baru.</p>
            </div>
            <div>
                <a href="{{ route('submissions.create') }}" class="kt-btn kt-btn-primary text-white text-xs">
                    <i class="ki-filled ki-cloud-change text-white mr-1"></i> Mulai Pengajuan Naskah (Step 1)
                </a>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('submissions.show', $submission) }}
@endsection

@section('content')
    <div class="grid w-full space-y-6 max-w-5xl mx-auto">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Pengajuan Naskah</h1>
                <p class="text-xs text-gray-500 mt-1">Status dan berkas terlampir dari pengajuan naskah ilmiah Anda.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('submissions.doi_certificate', $submission->id) }}" target="_blank" class="kt-btn kt-btn-primary text-white text-xs font-bold px-4 py-2 flex items-center gap-1.5 shadow-xs">
                    <i class="ki-filled ki-document text-white"></i> Sertifikat DOI & Ringkasan
                </a>
                <a href="{{ route('submissions.index') }}" class="kt-btn kt-btn-outline text-xs">
                    &larr; Kembali ke Daftar Naskah
                </a>
            </div>
        </div>

        <!-- Status Card -->
        <div class="kt-card p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Status Penelaahan Naskah</span>
                @if($submission->status === 'draft')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200 inline-block">Draf (Belum Diserahkan)</span>
                @elseif($submission->status === 'submitted')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 inline-block"><i class="ki-filled ki-check-circle mr-1"></i> Diserahkan (Submitted)</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200 inline-block">{{ $submission->status }}</span>
                @endif
            </div>

            <div class="text-xs text-gray-500 space-y-1 text-left sm:text-right">
                <div>Tanggal Pengajuan: <strong class="text-gray-800">{{ $submission->submitted_at ? $submission->submitted_at->format('d M Y, H:i') : '-' }}</strong></div>
                <div>Jurnal: <strong class="text-red-600">{{ $submission->journal?->name }}</strong></div>
            </div>
        </div>

        <!-- Detail Metadata Card -->
        <div class="kt-card">
            <div class="kt-card-header min-h-14 py-4 border-b border-gray-200">
                <h3 class="kt-card-title text-base font-bold">Metadata Naskah</h3>
            </div>
            <div class="kt-card-body p-6 space-y-4 text-xs">
                <div>
                    <span class="text-gray-500 font-semibold uppercase tracking-wider text-[10px] block">Judul Naskah</span>
                    <h2 class="text-base font-extrabold text-gray-900 leading-snug mt-0.5">{{ $submission->title }}</h2>
                </div>

                <div>
                    <span class="text-gray-500 font-semibold uppercase tracking-wider text-[10px] block mb-1">Abstrak</span>
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-gray-700 leading-relaxed">
                        {{ $submission->abstract }}
                    </div>
                </div>

                <div>
                    <span class="text-gray-500 font-semibold uppercase tracking-wider text-[10px] block mb-1">Kata Kunci</span>
                    <div class="flex flex-wrap gap-1.5">
                        @if(is_array($submission->keywords))
                            @foreach($submission->keywords as $kw)
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-red-50 text-red-700 border border-red-100">{{ $kw }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid Berkas & Penulis -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Berkas -->
            <div class="lg:col-span-6">
                <div class="kt-card h-full">
                    <div class="kt-card-header min-h-14 py-4 border-b border-gray-200">
                        <h3 class="kt-card-title text-base font-bold">Berkas Terlampir</h3>
                    </div>
                    <div class="kt-card-body p-6 text-xs">
                        <ul class="space-y-2">
                            @foreach($submission->files as $file)
                                <li class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 border border-gray-200">
                                    <div class="flex items-center gap-2 truncate">
                                        <i class="ki-filled ki-file text-red-600"></i>
                                        <span class="font-medium text-gray-800 truncate">{{ $file->original_name }}</span>
                                    </div>
                                    <span class="text-[10px] font-bold uppercase text-gray-500 shrink-0">{{ $file->file_role }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Penulis -->
            <div class="lg:col-span-6">
                <div class="kt-card h-full">
                    <div class="kt-card-header min-h-14 py-4 border-b border-gray-200">
                        <h3 class="kt-card-title text-base font-bold">Tim Penulis</h3>
                    </div>
                    <div class="kt-card-body p-6 text-xs">
                        <ul class="space-y-2">
                            @foreach($submission->authors as $author)
                                <li class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 border border-gray-200">
                                    <div>
                                        <span class="font-bold text-gray-900 block">{{ $author->name }}</span>
                                        <span class="text-[10px] text-gray-500 block">{{ $author->email }} | {{ $author->affiliation }}</span>
                                    </div>
                                    @if($author->is_corresponding_author)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-700 border border-red-100">Utama</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

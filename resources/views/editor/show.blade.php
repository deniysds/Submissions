@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('editor.submissions.show', $submission) }}
@endsection

@section('content')
    <div class="grid w-full space-y-6 max-w-5xl mx-auto">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Evaluasi Naskah Ilmiah</h1>
                <p class="text-xs text-gray-500 mt-1">Penelaahan dewan redaksi untuk membuat keputusan publikasi (Accept / Reject / Revision / In-Review).</p>
            </div>
            <a href="{{ route('editor.submissions.index') }}" class="kt-btn kt-btn-outline text-xs">
                &larr; Kembali ke Daftar Redaksi
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-green-50 text-green-700 border border-green-200 text-sm flex items-center gap-2">
                <i class="ki-filled ki-check-circle text-green-600 text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Card Form Keputusan Redaksi -->
        <div class="kt-card p-6 bg-red-50/40 border border-red-100 space-y-4">
            <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                <i class="ki-filled ki-verify text-red-600 text-lg"></i> Lembar Keputusan Dewan Redaksi (Editorial Decision)
            </h3>
            
            <form action="{{ route('editor.submissions.decision', $submission->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="status" class="kt-label font-semibold text-gray-700 text-xs">
                            Pilih Status Keputusan Redaksi <span class="text-danger-600">*</span>
                        </label>
                        <select name="status" id="status" class="kt-select w-full text-xs" required>
                            <option value="in_review" {{ $submission->status === 'in_review' ? 'selected' : '' }}>Penelaahan Mitra Bestari (In-Review)</option>
                            <option value="revision_required" {{ $submission->status === 'revision_required' ? 'selected' : '' }}>Revisi Diperlukan (Revision Required)</option>
                            <option value="accepted" {{ $submission->status === 'accepted' ? 'selected' : '' }}>Diterima Publikasi (Accepted)</option>
                            <option value="rejected" {{ $submission->status === 'rejected' ? 'selected' : '' }}>Ditolak (Rejected)</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="notes" class="kt-label font-semibold text-gray-700 text-xs">
                            Catatan Internal / Catatan Penelaah
                        </label>
                        <input type="text" name="notes" id="notes" placeholder="Catatan singkat keputusan editor..." class="kt-input w-full text-xs" />
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="kt-btn kt-btn-primary text-white text-xs px-6 py-2">
                        Simpan Keputusan Redaksi
                    </button>
                </div>
            </form>
        </div>

        <!-- Detail Metadata Card -->
        <div class="kt-card">
            <div class="kt-card-header min-h-14 py-4 border-b border-gray-200">
                <h3 class="kt-card-title text-base font-bold">Metadata Naskah</h3>
            </div>
            <div class="kt-card-body p-6 space-y-4 text-xs">
                <div>
                    <span class="text-gray-500 font-semibold uppercase tracking-wider text-[10px] block">Jurnal Tujuan</span>
                    <span class="text-sm font-bold text-red-600">{{ $submission->journal?->name }}</span>
                </div>

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
                                <span class="px-2.5 py-1 rounded-md text-[11px] font-medium bg-red-50 text-red-700 border border-red-100">{{ $kw }}</span>
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

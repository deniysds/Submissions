@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('submissions.show', $submission) }}
@endsection

@section('content')
    <div class="grid w-full space-y-6 max-w-5xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Pengajuan Naskah</h1>
                <p class="text-xs text-gray-500 mt-1">Status, evaluasi telaah, dan berkas terlampir dari pengajuan naskah ilmiah Anda.</p>
            </div>
            <div class="flex items-center gap-3">
                @if(in_array($submission->status, ['accepted', 'published']))
                    <a href="{{ route('submissions.doi_certificate', $submission->id) }}" target="_blank" class="kt-btn kt-btn-primary text-white text-xs font-bold px-4 py-2 flex items-center gap-1.5 shadow-xs">
                        <i class="ki-filled ki-document text-white"></i> Sertifikat DOI & Ringkasan
                    </a>
                @endif
                <a href="{{ route('submissions.index') }}" class="kt-btn kt-btn-outline text-xs">
                    &larr; Kembali ke Daftar Naskah
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-green-50 text-green-700 border border-green-200 text-sm flex items-center gap-2">
                <i class="ki-filled ki-check-circle text-green-600 text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-red-50 text-red-700 border border-red-200 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <p class="flex items-center gap-1.5"><i class="ki-filled ki-information text-red-600"></i> {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Status Card -->
        <div class="kt-card p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Status Penelaahan Naskah</span>
                @if($submission->status === 'draft')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200 inline-block">Draf (Belum Diserahkan)</span>
                @elseif($submission->status === 'submitted')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 inline-block"><i class="ki-filled ki-check-circle mr-1"></i> Diserahkan (Menunggu Antrean Redaksi)</span>
                @elseif($submission->status === 'under_editor_review')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 inline-block"><i class="ki-filled ki-time mr-1"></i> Peninjauan Dewan Redaksi (Desk Review)</span>
                @elseif($submission->status === 'under_review')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 inline-block"><i class="ki-filled ki-eye mr-1"></i> Dalam Penelaahan Mitra Bestari (Peer Review)</span>
                @elseif($submission->status === 'revision_required')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-900 border border-orange-300 inline-block animate-pulse"><i class="ki-filled ki-information mr-1"></i> Perlu Revisi (Revision Required)</span>
                @elseif($submission->status === 'resubmitted')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200 inline-block"><i class="ki-filled ki-send mr-1"></i> Naskah Revisi Terkirim (Menunggu Evaluasi Redaksi)</span>
                @elseif($submission->status === 'accepted')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 inline-block"><i class="ki-filled ki-check-circle mr-1"></i> Diterima (Accepted)</span>
                @elseif($submission->status === 'published')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-300 inline-block"><i class="ki-filled ki-crown mr-1"></i> Resmi Diterbitkan (Published)</span>
                @elseif($submission->status === 'rejected')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-800 border border-red-200 inline-block"><i class="ki-filled ki-cross-circle mr-1"></i> Ditolak (Rejected)</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-50 text-gray-700 border border-gray-200 inline-block">{{ $submission->status }}</span>
                @endif
            </div>

            <div class="text-xs text-gray-500 space-y-1 text-left sm:text-right">
                <div>Tanggal Pengajuan: <strong class="text-gray-800">{{ $submission->submitted_at ? $submission->submitted_at->format('d M Y, H:i') : '-' }}</strong></div>
                <div>Jurnal Tujuan: <strong class="text-red-600">{{ $submission->journal?->name }}</strong></div>
            </div>
        </div>

        @if($submission->status === 'revision_required')
            <!-- Revision Request & Upload Box -->
            <div class="kt-card border-2 border-orange-300 shadow-sm overflow-hidden">
                <div class="bg-orange-500 text-white px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="ki-filled ki-information text-xl"></i>
                        <h2 class="text-base font-bold">Instruksi &amp; Pengunggahan Naskah Revisi</h2>
                    </div>
                    <span class="text-xs bg-white/20 px-2.5 py-1 rounded-full font-semibold">Tindakan Diperlukan Penulis</span>
                </div>

                <div class="p-6 space-y-6">
                    @php
                        $latestRevisionHistory = $submission->histories->where('to_status', 'revision_required')->first();
                    @endphp

                    @if($latestRevisionHistory && $latestRevisionHistory->notes)
                        <div class="rounded-xl bg-amber-50/80 border border-amber-200 p-4">
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-900 block mb-1">
                                <i class="ki-filled ki-notepad-edit mr-1"></i> Catatan Resmi dari Dewan Redaksi:
                            </span>
                            <div class="text-xs text-amber-950 leading-relaxed whitespace-pre-line font-medium">
                                {{ $latestRevisionHistory->notes }}
                            </div>
                        </div>
                    @endif

                    @php
                        $reviewerComments = collect();
                        foreach ($submission->reviewRounds as $round) {
                            foreach ($round->assignments as $assignment) {
                                if ($assignment->comments_for_author) {
                                    $reviewerComments->push([
                                        'round' => $round->round_number,
                                        'comments' => $assignment->comments_for_author,
                                        'submitted_at' => $assignment->submitted_at,
                                    ]);
                                }
                            }
                        }
                    @endphp

                    @if($reviewerComments->isNotEmpty())
                        <div class="space-y-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700">
                                <i class="ki-filled ki-eye mr-1"></i> Masukan &amp; Evaluasi Mitra Bestari (Double-Blind Peer Review):
                            </h3>
                            <div class="grid grid-cols-1 gap-3">
                                @foreach($reviewerComments as $idx => $review)
                                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-xs text-gray-800 space-y-1.5">
                                        <div class="flex items-center justify-between text-gray-500 font-semibold text-[11px] pb-1 border-b border-gray-200">
                                            <span>Penelaah / Mitra Bestari #{{ $idx + 1 }} (Putaran {{ $review['round'] }})</span>
                                            <span>{{ $review['submitted_at'] ? \Carbon\Carbon::parse($review['submitted_at'])->format('d M Y') : '-' }}</span>
                                        </div>
                                        <div class="leading-relaxed whitespace-pre-line text-gray-700 pt-1">
                                            {{ $review['comments'] }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Revision Form -->
                    <form action="{{ route('submissions.resubmit', $submission->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 pt-2 border-t border-gray-200">
                        @csrf
                        <div>
                            <label for="response_notes" class="block text-xs font-bold text-gray-800 mb-1">
                                Tanggapan &amp; Rangkuman Perbaikan Penulis (Author Response / Rebuttal Notes) <span class="text-red-600">*</span>
                            </label>
                            <p class="text-[11px] text-gray-500 mb-2">Tuliskan penjelasan rinci poin demi poin atas perbaikan yang telah Anda lakukan sesuai masukan redaksi dan mitra bestari.</p>
                            <textarea name="response_notes" id="response_notes" rows="5" class="kt-textarea w-full text-xs" placeholder="Contoh: Kami telah memperbaiki persamaan matematika pada Bab 3, menambahkan 5 referensi mutakhir tahun 2025-2026, dan memperjelas metodologi pengujian..." required>{{ old('response_notes') }}</textarea>
                        </div>

                        <div>
                            <label for="file" class="block text-xs font-bold text-gray-800 mb-1">
                                Unggah Berkas Naskah Hasil Revisi (.docx, .doc, .pdf) <span class="text-red-600">*</span>
                            </label>
                            <p class="text-[11px] text-gray-500 mb-2">Pastikan naskah baru telah disesuaikan dengan template penulisan jurnal dan memuat seluruh perbaikan (maks. 20 MB).</p>
                            <input type="file" name="file" id="file" accept=".docx,.doc,.pdf" class="kt-input w-full text-xs" required />
                        </div>

                        <div class="pt-2 flex items-center justify-end gap-3">
                            <button type="submit" class="kt-btn kt-btn-primary text-white text-xs font-bold px-5 py-2.5 shadow-sm">
                                <i class="ki-filled ki-send text-white mr-1.5"></i> Kirim Naskah Revisi ke Dewan Redaksi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @elseif($submission->status === 'resubmitted')
            <div class="kt-card p-5 bg-sky-50 border border-sky-200 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-sky-600 text-white flex items-center justify-center text-xl shrink-0 font-bold">
                    <i class="ki-filled ki-check"></i>
                </div>
                <div class="space-y-0.5">
                    <h3 class="text-sm font-bold text-sky-950">Naskah Revisi Telah Berhasil Dikirimkan</h3>
                    <p class="text-xs text-sky-800">Dewan redaksi saat ini sedang memeriksa berkas perbaikan dan tanggapan yang Anda serahkan. Anda akan menerima notifikasi berkala begitu keputusan final ditetapkan.</p>
                </div>
            </div>
        @endif

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
                                <li class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 border border-gray-200 gap-2">
                                    <div class="flex items-center gap-2 truncate">
                                        <i class="ki-filled ki-file {{ $file->file_role === 'naskah_revisi' ? 'text-orange-600' : 'text-red-600' }}"></i>
                                        <span class="font-medium text-gray-800 truncate">{{ $file->original_name }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        @if($file->file_role === 'naskah_revisi')
                                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-orange-100 text-orange-800 border border-orange-200">Revisi</span>
                                        @elseif($file->file_role === 'naskah_utama')
                                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-100">Utama</span>
                                        @else
                                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-gray-100 text-gray-600">{{ $file->file_role }}</span>
                                        @endif
                                        <a href="{{ asset('storage/' . $file->path) }}" target="_blank" class="kt-btn kt-btn-xs kt-btn-icon kt-btn-ghost text-gray-500 hover:text-red-600" title="Unduh Berkas">
                                            <i class="ki-filled ki-file-down"></i>
                                        </a>
                                    </div>
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

        <!-- Submission History Timeline -->
        @if($submission->histories->isNotEmpty())
            <div class="kt-card">
                <div class="kt-card-header min-h-14 py-4 border-b border-gray-200">
                    <h3 class="kt-card-title text-base font-bold">Riwayat &amp; Alur Penelaahan Naskah</h3>
                </div>
                <div class="kt-card-body p-6">
                    <div class="relative border-s-2 border-gray-200 ms-3 space-y-6">
                        @foreach($submission->histories as $history)
                            <div class="ms-6 relative">
                                <span class="absolute -start-[31px] top-0 flex h-6 w-6 items-center justify-center rounded-full bg-white border-2 border-red-500 text-[10px] text-red-600 font-bold">
                                    <i class="ki-filled ki-check text-[10px]"></i>
                                </span>
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                    <h4 class="text-xs font-bold text-gray-900 capitalize">
                                        {{ str_replace('_', ' ', $history->to_status) }}
                                    </h4>
                                    <time class="text-[10px] font-normal text-gray-400">
                                        {{ $history->happened_at ? $history->happened_at->format('d M Y, H:i') : $history->created_at->format('d M Y, H:i') }}
                                    </time>
                                </div>
                                @if($history->notes)
                                    <p class="mt-1 text-xs text-gray-600 bg-gray-50 p-2.5 rounded-lg border border-gray-150 whitespace-pre-line leading-relaxed">
                                        {{ $history->notes }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

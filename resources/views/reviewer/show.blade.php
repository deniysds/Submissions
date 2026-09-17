@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('reviewer.assignments.show', $assignment) }}
@endsection

@section('content')
    <div class="grid w-full space-y-6 max-w-5xl mx-auto">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Lembar Telaah Manuskrip Ilmiah</h1>
                <p class="text-xs text-gray-500 mt-1">Evaluasi telaah sejawat (Double-blind Peer Review). Identitas penulis disamarkan demi objektivitas akademik.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('reviewer.assignments.index') }}" class="kt-btn kt-btn-outline text-xs">
                    &larr; Kembali ke Daftar Tugas
                </a>
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

        @if($errors->any())
            <div class="p-4 rounded-xl bg-red-50 text-red-700 border border-red-200 text-sm">
                <ul class="list-disc pl-5 space-y-1 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Card Respon Undangan / Form Evaluasi -->
        @if($assignment->response_status === 'pending')
            <div class="kt-card p-6 bg-amber-50/50 border border-amber-200 space-y-4">
                <div class="flex items-start gap-3">
                    <i class="ki-filled ki-information-4 text-amber-600 text-2xl mt-0.5"></i>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Undangan Penelaahan Sejawat</h3>
                        <p class="text-xs text-gray-600 mt-1">
                            Dewan Redaksi mengundang Anda untuk menelaah manuskrip di bawah ini sebelum tanggal 
                            <strong>{{ $assignment->due_date ? $assignment->due_date->format('d F Y') : '14 hari ke depan' }}</strong>. 
                            Mohon konfirmasikan kesediaan Anda.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <form action="{{ route('reviewer.assignments.respond', $assignment->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="response_status" value="accepted">
                        <button type="submit" class="kt-btn kt-btn-primary text-white text-xs px-5 py-2">
                            <i class="ki-filled ki-check mr-1"></i> Saya Bersedia Menelaah (Terima)
                        </button>
                    </form>

                    <form action="{{ route('reviewer.assignments.respond', $assignment->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="response_status" value="declined">
                        <button type="submit" class="kt-btn kt-btn-outline text-danger-600 border-danger-200 hover:bg-danger-50 text-xs px-5 py-2">
                            <i class="ki-filled ki-cross mr-1"></i> Maaf, Saya Tidak Bersedia (Tolak)
                        </button>
                    </form>
                </div>
            </div>
        @elseif($assignment->response_status === 'accepted')
            <div class="kt-card p-6 bg-blue-50/40 border border-blue-200 space-y-4">
                <div class="flex items-center justify-between border-b border-blue-100 pb-3">
                    <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                        <i class="ki-filled ki-note-2 text-blue-600 text-lg"></i> Lembar Penilaian & Rekomendasi Mitra Bestari
                    </h3>
                    <span class="text-xs text-gray-500">
                        Batas Akhir Telaah: <strong>{{ $assignment->due_date ? $assignment->due_date->format('d M Y') : '-' }}</strong>
                    </span>
                </div>

                <form action="{{ route('reviewer.assignments.submit', $assignment->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-1">
                        <label for="recommendation" class="kt-label font-semibold text-gray-800 text-xs">
                            Rekomendasi Hasil Penelaahan <span class="text-danger-600">*</span>
                        </label>
                        <select name="recommendation" id="recommendation" class="kt-select w-full text-xs" required>
                            <option value="">-- Pilih Rekomendasi Ilmiah --</option>
                            <option value="accept" {{ old('recommendation', $assignment->recommendation) === 'accept' ? 'selected' : '' }}>
                                Terima Naskah (Accept Submission) - Tanpa revisi signifikan
                            </option>
                            <option value="minor_revision" {{ old('recommendation', $assignment->recommendation) === 'minor_revision' ? 'selected' : '' }}>
                                Revisi Minor (Minor Revision) - Perbaikan redaksional / data kecil
                            </option>
                            <option value="major_revision" {{ old('recommendation', $assignment->recommendation) === 'major_revision' ? 'selected' : '' }}>
                                Revisi Mayor (Major Revision) - Diperlukan eksperimen / kajian ulang substansi
                            </option>
                            <option value="reject" {{ old('recommendation', $assignment->recommendation) === 'reject' ? 'selected' : '' }}>
                                Tolak Naskah (Decline / Reject) - Tidak memenuhi standar ilmiah
                            </option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="comments_for_author" class="kt-label font-semibold text-gray-800 text-xs">
                            Komentar dan Catatan Telaah untuk Penulis <span class="text-danger-600">*</span>
                        </label>
                        <p class="text-[11px] text-gray-500">Komentar ini akan disampaikan kepada penulis untuk panduan perbaikan (tanpa mencantumkan nama Anda).</p>
                        <textarea name="comments_for_author" id="comments_for_author" rows="6" class="kt-textarea w-full text-xs" placeholder="Tuliskan evaluasi metodologi, kebaruan, analisis data, dan saran perbaikan..." required>{{ old('comments_for_author', $assignment->comments_for_author) }}</textarea>
                    </div>

                    <div class="space-y-1">
                        <label for="confidential_comments_to_editor" class="kt-label font-semibold text-gray-800 text-xs">
                            Catatan Rahasia untuk Dewan Redaksi (Opsional)
                        </label>
                        <p class="text-[11px] text-gray-500">Hanya dapat dibaca oleh editor jurnal, tidak akan dikirimkan kepada penulis.</p>
                        <textarea name="confidential_comments_to_editor" id="confidential_comments_to_editor" rows="3" class="kt-textarea w-full text-xs" placeholder="Catatan internal yang ingin Anda sampaikan secara rahasia kepada editor...">{{ old('confidential_comments_to_editor', $assignment->confidential_comments_to_editor) }}</textarea>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="kt-btn kt-btn-primary text-white text-xs px-6 py-2.5">
                            <i class="ki-filled ki-send mr-1.5"></i> Kirimkan Hasil Evaluasi ke Redaksi
                        </button>
                    </div>
                </form>
            </div>
        @elseif($assignment->response_status === 'completed')
            <div class="kt-card p-6 bg-green-50/40 border border-green-200 space-y-4">
                <div class="flex items-center justify-between border-b border-green-100 pb-3">
                    <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                        <i class="ki-filled ki-verify text-green-600 text-lg"></i> Hasil Penelaahan Telah Terkirim
                    </h3>
                    <span class="text-xs text-gray-500">
                        Dikirim pada: <strong>{{ $assignment->submitted_at ? $assignment->submitted_at->format('d M Y H:i') : '-' }}</strong>
                    </span>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-gray-500 font-semibold block uppercase tracking-wider text-[10px]">Rekomendasi Anda</span>
                        <div class="mt-1">
                            @if($assignment->recommendation === 'accept')
                                <span class="px-2.5 py-1 rounded text-xs font-bold bg-green-100 text-green-800">Diterima (Accept)</span>
                            @elseif($assignment->recommendation === 'minor_revision')
                                <span class="px-2.5 py-1 rounded text-xs font-bold bg-cyan-100 text-cyan-800">Revisi Minor</span>
                            @elseif($assignment->recommendation === 'major_revision')
                                <span class="px-2.5 py-1 rounded text-xs font-bold bg-amber-100 text-amber-800">Revisi Mayor</span>
                            @elseif($assignment->recommendation === 'reject')
                                <span class="px-2.5 py-1 rounded text-xs font-bold bg-red-100 text-red-800">Ditolak (Reject)</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <span class="text-gray-500 font-semibold block uppercase tracking-wider text-[10px]">Komentar untuk Penulis</span>
                        <div class="mt-1 p-3.5 rounded-lg bg-white border border-gray-200 text-gray-800 whitespace-pre-line leading-relaxed">
                            {{ $assignment->comments_for_author }}
                        </div>
                    </div>

                    @if($assignment->confidential_comments_to_editor)
                        <div>
                            <span class="text-gray-500 font-semibold block uppercase tracking-wider text-[10px]">Catatan Rahasia untuk Dewan Redaksi</span>
                            <div class="mt-1 p-3.5 rounded-lg bg-white border border-gray-200 text-gray-800 whitespace-pre-line leading-relaxed">
                                {{ $assignment->confidential_comments_to_editor }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @elseif($assignment->response_status === 'declined')
            <div class="kt-card p-6 bg-red-50/40 border border-red-200 text-xs text-red-800 flex items-center gap-3">
                <i class="ki-filled ki-cross-circle text-red-600 text-xl"></i>
                <div>
                    <strong class="font-bold block text-sm">Undangan Telaah Ditolak</strong>
                    <span>Anda telah menyatakan tidak bersedia menelaah naskah ini. Dewan redaksi telah diberitahu.</span>
                </div>
            </div>
        @endif

        <!-- Metadata Naskah Ilmiah (Double Blind - Omit Author Info) -->
        <div class="kt-card">
            <div class="kt-card-header min-h-14 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="kt-card-title text-base font-bold">Informasi Manuskrip</h3>
                <span class="px-2.5 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-700">
                    Putaran Ke-{{ $assignment->round?->round_number ?? 1 }}
                </span>
            </div>
            <div class="kt-card-body p-6 space-y-4 text-xs">
                <div>
                    <span class="text-gray-500 font-semibold uppercase tracking-wider text-[10px] block">Jurnal Ilmiah</span>
                    <span class="text-sm font-bold text-red-600">{{ $submission->journal?->name }}</span>
                </div>

                <div>
                    <span class="text-gray-500 font-semibold uppercase tracking-wider text-[10px] block">Judul Manuskrip</span>
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

        <!-- Berkas Manuskrip untuk Ditelaah -->
        <div class="kt-card">
            <div class="kt-card-header min-h-14 py-4 border-b border-gray-200">
                <h3 class="kt-card-title text-base font-bold">Berkas Naskah untuk Ditelaah</h3>
            </div>
            <div class="kt-card-body p-6 text-xs">
                <ul class="space-y-2.5">
                    @forelse($submission->files as $file)
                        <li class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-200">
                            <div class="flex items-center gap-2.5 truncate">
                                <i class="ki-filled ki-file text-red-600 text-lg"></i>
                                <div>
                                    <span class="font-bold text-gray-900 block truncate">{{ $file->original_name }}</span>
                                    <span class="text-[10px] uppercase font-semibold text-gray-500">{{ $file->file_role }}</span>
                                </div>
                            </div>
                            <a href="{{ route('reviewer.assignments.download', [$assignment->id, $file->id]) }}" class="kt-btn kt-btn-xs kt-btn-primary text-white shrink-0">
                                <i class="ki-filled ki-file-down mr-1"></i> Unduh Berkas
                            </a>
                        </li>
                    @empty
                        <li class="text-gray-500 italic">Belum ada berkas naskah terlampir.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection

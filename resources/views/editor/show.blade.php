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
            <div class="flex items-center gap-3">
                <a href="{{ route('editor.submissions.doi_certificate', $submission->id) }}" target="_blank" class="kt-btn kt-btn-primary text-white text-xs font-bold px-4 py-2 flex items-center gap-1.5 shadow-xs">
                    <i class="ki-filled ki-document text-white"></i> Sertifikat DOI & Ringkasan
                </a>
                <a href="{{ route('editor.submissions.index') }}" class="kt-btn kt-btn-outline text-xs">
                    &larr; Kembali ke Daftar Redaksi
                </a>
            </div>
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
                        @php
                            $statusLabels = [
                                'under_editor_review' => 'Peninjauan Editor (Under Editor Review)',
                                'under_review'        => 'Penelaahan Mitra Bestari (Under Review)',
                                'revision_required'   => 'Revisi Diperlukan (Revision Required)',
                                'resubmitted'         => 'Naskah Revisi Masuk (Resubmitted)',
                                'accepted'            => 'Diterima Publikasi (Accepted)',
                                'published'           => 'Diterbitkan (Published)',
                                'rejected'            => 'Ditolak (Rejected)',
                            ];
                        @endphp
                        <select name="status" id="status" class="kt-select w-full text-xs" required>
                            @forelse($allowedTransitions as $transition)
                                <option value="{{ $transition }}">
                                    {{ $statusLabels[$transition] ?? strtoupper(str_replace('_', ' ', $transition)) }}
                                </option>
                            @empty
                                <option value="" disabled selected>Tidak ada transisi keputusan lanjutan (Status Final: {{ strtoupper($submission->status) }})</option>
                            @endforelse
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
                    <button type="submit" class="kt-btn kt-btn-primary text-white text-xs px-6 py-2" {{ empty($allowedTransitions) ? 'disabled' : '' }}>
                        Simpan Keputusan Redaksi
                    </button>
                </div>
            </form>
        </div>

        @if($submission->status === 'accepted' || $submission->status === 'published' || $article)
            <!-- Panel Penerbitan ke Terbitan / Issue (Publishing Desk) -->
            <div class="kt-card p-6 bg-gradient-to-r from-emerald-50/70 to-teal-50/40 border border-emerald-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-emerald-100 pb-3">
                    <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                        <i class="ki-filled ki-rocket text-emerald-600 text-lg"></i> 
                        {{ $article ? 'Status Publikasi di Issue Jurnal' : 'Jadwalkan & Terbitkan Naskah ke Issue' }}
                    </h3>
                    @if($article)
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                            <i class="ki-filled ki-check-circle text-xs"></i> Sudah Terbit Publik
                        </span>
                    @endif
                </div>

                @if($article)
                    <div class="p-4 rounded-xl bg-white border border-emerald-200/80 space-y-3 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <span class="text-gray-500 font-semibold uppercase text-[10px] block">Terbit di Issue:</span>
                                <strong class="text-gray-900 font-bold text-sm">Vol. {{ $article->issue?->volume }} No. {{ $article->issue?->number }} ({{ $article->issue?->publication_year }})</strong>
                                <span class="text-gray-500 block text-[11px]">{{ $article->issue?->title }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 font-semibold uppercase text-[10px] block">Halaman & DOI:</span>
                                <span class="font-mono text-gray-700 block">Halaman: {{ $article->pages ?: '-' }}</span>
                                <span class="font-mono text-emerald-700 block font-bold">DOI: {{ $article->doi ?: '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 font-semibold uppercase text-[10px] block">Statistik Pembaca:</span>
                                <span class="text-gray-600 block"><i class="ki-filled ki-eye text-xs"></i> {{ $article->views_count }} Kali Dilihat</span>
                                <span class="text-gray-600 block"><i class="ki-filled ki-file-down text-xs"></i> {{ $article->downloads_count }} Kali Diunduh</span>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
                            <a href="{{ route('website.articles.show', $article->slug) }}" target="_blank" class="kt-btn kt-btn-primary text-white text-xs px-4 py-1.5 flex items-center gap-1.5 shadow-xs">
                                <i class="ki-filled ki-eye text-white"></i> Lihat Artikel di Website Publik &rarr;
                            </a>
                            <span class="text-[11px] text-gray-400">Terbit: {{ $article->published_at ? $article->published_at->format('d M Y') : '-' }}</span>
                        </div>
                    </div>
                @else
                    <form action="{{ route('editor.submissions.publish_to_issue', $submission->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-1">
                                <label for="issue_id" class="kt-label font-semibold text-gray-700 text-xs">
                                    Pilih Issue Jurnal Target <span class="text-danger-600">*</span>
                                </label>
                                <select name="issue_id" id="issue_id" class="kt-select w-full text-xs" required>
                                    <option value="" disabled selected>-- Pilih Volume & Nomor Issue --</option>
                                    @foreach($availableIssues as $iss)
                                        <option value="{{ $iss->id }}">
                                            Vol. {{ $iss->volume }} No. {{ $iss->number }} ({{ $iss->publication_year }}) - {{ $iss->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label for="pages" class="kt-label font-semibold text-gray-700 text-xs">
                                    Rentang Halaman (Pages)
                                </label>
                                <input type="text" name="pages" id="pages" placeholder="Contoh: 1-14" value="{{ old('pages', '1-10') }}" class="kt-input w-full text-xs" />
                            </div>

                            <div class="space-y-1">
                                <label for="doi" class="kt-label font-semibold text-gray-700 text-xs">
                                    Nomor DOI (Opsional / Otomatis)
                                </label>
                                <input type="text" name="doi" id="doi" placeholder="Kosongkan untuk otomatis" value="{{ old('doi') }}" class="kt-input w-full text-xs font-mono" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <p class="text-[11px] text-gray-500">
                                Menerbitkan naskah akan membuat halaman artikel publik dan mengunci naskah sebagai terbitan resmi.
                            </p>
                            <button type="submit" class="kt-btn kt-btn-success text-white text-xs px-6 py-2 flex items-center gap-1.5 shadow-xs">
                                <i class="ki-filled ki-rocket text-white"></i> Terbitkan ke Issue Ini (Publish Now)
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        @endif

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
                                        <a href="{{ asset('storage/' . $file->path) }}" target="_blank" class="kt-btn kt-btn-xs kt-btn-icon kt-btn-ghost text-gray-500 hover:text-red-600" title="Unduh">
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

        <!-- Panel Manajemen Mitra Bestari & Round Telaah -->
        <div class="kt-card">
            <div class="kt-card-header min-h-14 py-4 border-b border-gray-200">
                <div>
                    <h3 class="kt-card-title text-base font-bold flex items-center gap-2">
                        <i class="ki-filled ki-document-filter text-red-600"></i> Dewan Mitra Bestari (Peer Reviewers)
                    </h3>
                    <p class="text-[11px] text-gray-500 mt-0.5">Penugasan reviewer, pemantauan batas waktu, dan kompilasi rekomendasi hasil telaah sejawat.</p>
                </div>
            </div>
            
            <div class="kt-card-body p-6 space-y-6">
                <!-- Form Penugasan Reviewer Baru -->
                <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                    <h4 class="font-bold text-gray-800 text-xs mb-3 flex items-center gap-1.5">
                        <i class="ki-filled ki-user-edit text-gray-600"></i> Tugaskan Mitra Bestari Baru
                    </h4>
                    <form action="{{ route('editor.submissions.assign_reviewer', $submission->id) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        @csrf
                        <div class="sm:col-span-6 space-y-1">
                            <label class="kt-label text-xs font-semibold text-gray-700">Pilih Reviewer <span class="text-danger-600">*</span></label>
                            <select name="reviewer_id" class="kt-select w-full text-xs" required>
                                <option value="">-- Pilih Akun Mitra Bestari --</option>
                                @foreach($availableReviewers as $rev)
                                    <option value="{{ $rev->id }}">{{ $rev->name }} ({{ $rev->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-4 space-y-1">
                            <label class="kt-label text-xs font-semibold text-gray-700">Batas Waktu Telaah</label>
                            <input type="date" name="due_date" value="{{ now()->addDays(14)->format('Y-m-d') }}" class="kt-input w-full text-xs" />
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit" class="kt-btn kt-btn-primary text-white text-xs w-full py-2">
                                <i class="ki-filled ki-plus mr-1"></i> Tugaskan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Daftar Putaran Telaah (Review Rounds) -->
                @forelse($submission->reviewRounds as $round)
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <div class="bg-gray-100/70 px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900 text-xs">Putaran #{{ $round->round_number }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $round->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $round->status === 'completed' ? 'Selesai' : 'Sedang Berjalan' }}
                                </span>
                            </div>
                            <span class="text-[11px] text-gray-500">
                                Dimulai: {{ $round->started_at ? $round->started_at->format('d M Y') : '-' }}
                            </span>
                        </div>

                        <div class="p-4 space-y-3">
                            @forelse($round->assignments as $assignment)
                                <div class="p-3.5 rounded-lg border border-gray-150 bg-white space-y-2">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div>
                                            <span class="font-bold text-gray-900 text-xs block">{{ $assignment->reviewer?->name ?? 'Reviewer' }}</span>
                                            <span class="text-[11px] text-gray-500 block">{{ $assignment->reviewer?->email }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($assignment->response_status === 'pending')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Menunggu Respon</span>
                                            @elseif($assignment->response_status === 'accepted')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Sedang Menelaah</span>
                                            @elseif($assignment->response_status === 'completed')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-green-50 text-green-700 border border-green-200">Selesai Menelaah</span>
                                            @elseif($assignment->response_status === 'declined')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-700 border border-red-200">Undangan Ditolak</span>
                                            @endif

                                            @if($assignment->recommendation)
                                                @if($assignment->recommendation === 'accept')
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">Rekomendasi: Accept</span>
                                                @elseif($assignment->recommendation === 'minor_revision')
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-100 text-cyan-800">Rekomendasi: Minor Rev</span>
                                                @elseif($assignment->recommendation === 'major_revision')
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Rekomendasi: Major Rev</span>
                                                @elseif($assignment->recommendation === 'reject')
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">Rekomendasi: Reject</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>

                                    @if($assignment->response_status === 'completed')
                                        <div class="pt-2 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                            <div class="p-2.5 rounded bg-gray-50">
                                                <strong class="text-[10px] uppercase text-gray-500 block mb-1">Catatan untuk Penulis:</strong>
                                                <p class="text-gray-700 text-[11px] whitespace-pre-line">{{ $assignment->comments_for_author }}</p>
                                            </div>
                                            <div class="p-2.5 rounded bg-red-50/50">
                                                <strong class="text-[10px] uppercase text-red-600 block mb-1">Catatan Rahasia untuk Editor:</strong>
                                                <p class="text-gray-700 text-[11px] whitespace-pre-line">{{ $assignment->confidential_comments_to_editor ?: '-' }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 italic">Belum ada mitra bestari yang ditugaskan pada putaran ini.</p>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-400 text-xs">
                        <i class="ki-filled ki-document-filter text-3xl text-gray-300 block mb-2"></i>
                        Belum ada putaran telaah mitra bestari untuk naskah ini. Gunakan formulir di atas untuk menugaskan reviewer pertama.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

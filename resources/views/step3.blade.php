@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('submissions.create.step3', $submission->id) }}
@endsection

@section('content')
    <div class="grid w-full space-y-6 max-w-5xl mx-auto">
        <!-- Step Wizard Progress Bar -->
        <div class="kt-card p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-600 text-white font-bold flex items-center justify-center text-sm shadow">3</div>
                    <div>
                        <div class="font-bold text-sm text-gray-900">Step 3: Metadata Naskah & Penulis</div>
                        <div class="text-xs text-gray-500">Lengkapi judul, abstrak, kata kunci, dan tim penulis naskah</div>
                    </div>
                </div>
                <div class="hidden sm:flex items-center gap-2 text-xs font-semibold text-gray-400">
                    <span class="text-gray-500">1. Persyaratan</span> &rarr; 
                    <span class="text-gray-500">2. Upload File</span> &rarr; 
                    <span class="text-red-600">3. Metadata</span> &rarr; 
                    <span>4. Konfirmasi</span>
                </div>
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

        <!-- Form Metadata Utama -->
        <div class="kt-card">
            <div class="kt-card-header min-h-14 py-4 border-b border-gray-200">
                <h3 class="kt-card-title text-base font-bold">Informasi Naskah (Metadata)</h3>
            </div>
            <div class="kt-card-body p-6" x-data="{ activeMetaTab: 'id' }">
                <form id="step3-form" action="{{ route('submissions.store.step3', $submission->id) }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Language Switcher Tabs -->
                    <div class="flex items-center justify-between border-b border-gray-150 pb-3">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="activeMetaTab = 'id'" :class="activeMetaTab === 'id' ? 'bg-red-600 text-white font-bold shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 font-semibold'" class="px-3.5 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5">
                                <span>🇮🇩</span> Bahasa Indonesia <span class="text-[10px] opacity-80">(Wajib *)</span>
                            </button>
                            <button type="button" @click="activeMetaTab = 'en'" :class="activeMetaTab === 'en' ? 'bg-red-600 text-white font-bold shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 font-semibold'" class="px-3.5 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5">
                                <span>🇬🇧</span> English Version <span class="text-[10px] opacity-80">(Bilingual)</span>
                            </button>
                        </div>
                        <span class="text-[11px] text-gray-400 hidden sm:inline">Lengkapi metadata naskah dalam dua bahasa</span>
                    </div>

                    <!-- Tab ID -->
                    <div x-show="activeMetaTab === 'id'" class="space-y-5">
                        <!-- Judul Naskah (ID) -->
                        <div class="space-y-2">
                            <label for="title" class="kt-label font-semibold text-gray-700 text-xs">
                                Judul Naskah (Bahasa Indonesia) <span class="text-danger-600">*</span>
                            </label>
                            <input type="text" name="title" id="title" value="{{ old('title', Str::startsWith($submission->title, 'Draf Naskah Tanpa Judul') ? '' : $submission->title) }}" placeholder="Masukkan judul lengkap naskah dalam bahasa Indonesia..." class="kt-input w-full text-xs" required />
                        </div>

                        <!-- Abstrak Naskah (ID) -->
                        <div class="space-y-2">
                            <label for="abstract" class="kt-label font-semibold text-gray-700 text-xs">
                                Abstrak Naskah (Bahasa Indonesia) <span class="text-danger-600">*</span>
                            </label>
                            <textarea name="abstract" id="abstract" rows="6" placeholder="Tuliskan ringkasan/abstrak naskah ilmiah minimal 50 karakter..." class="kt-input w-full text-xs p-3 leading-relaxed" required>{{ old('abstract', $submission->abstract) }}</textarea>
                        </div>

                        <!-- Kata Kunci (ID) -->
                        <div class="space-y-2">
                            <label for="keywords" class="kt-label font-semibold text-gray-700 text-xs">
                                Kata Kunci (Bahasa Indonesia) <span class="text-danger-600">*</span>
                            </label>
                            @php
                                $keywordsString = is_array($submission->keywords) ? implode(', ', $submission->keywords) : '';
                            @endphp
                            <input type="text" name="keywords" id="keywords" value="{{ old('keywords', $keywordsString) }}" placeholder="Contoh: Genomik, Bioinformatika, Kesehatan Molekuler (Pisahkan dengan koma)" class="kt-input w-full text-xs" required />
                            <p class="text-[10px] text-gray-400">Gunakan tanda koma (,) untuk memisahkan setiap kata kunci.</p>
                        </div>
                    </div>

                    <!-- Tab EN -->
                    <div x-show="activeMetaTab === 'en'" style="display: none;" class="space-y-5">
                        <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs flex items-start gap-2">
                            <i class="ki-filled ki-information text-base text-blue-600 shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-bold block">International Indexing Metadata (English)</span>
                                <span>Sangat disarankan untuk pengindeksan jurnal bereputasi (Crossref DOI, Google Scholar, DOAJ). Jika terisi, metadata ini akan ditampilkan saat pembaca memilih bahasa English.</span>
                            </div>
                        </div>

                        <!-- Article Title (EN) -->
                        <div class="space-y-2">
                            <label for="title_en" class="kt-label font-semibold text-gray-700 text-xs">
                                Manuscript Title (English)
                            </label>
                            <input type="text" name="title_en" id="title_en" value="{{ old('title_en', $submission->title_en) }}" placeholder="Enter full manuscript title in English..." class="kt-input w-full text-xs" />
                        </div>

                        <!-- Abstract (EN) -->
                        <div class="space-y-2">
                            <label for="abstract_en" class="kt-label font-semibold text-gray-700 text-xs">
                                Manuscript Abstract (English)
                            </label>
                            <textarea name="abstract_en" id="abstract_en" rows="6" placeholder="Write manuscript abstract in English..." class="kt-input w-full text-xs p-3 leading-relaxed">{{ old('abstract_en', $submission->abstract_en) }}</textarea>
                        </div>

                        <!-- Keywords (EN) -->
                        <div class="space-y-2">
                            <label for="keywords_en" class="kt-label font-semibold text-gray-700 text-xs">
                                Keywords (English)
                            </label>
                            @php
                                $keywordsEnString = is_array($submission->keywords_en) ? implode(', ', $submission->keywords_en) : '';
                            @endphp
                            <input type="text" name="keywords_en" id="keywords_en" value="{{ old('keywords_en', $keywordsEnString) }}" placeholder="e.g. Genomics, Bioinformatics, Molecular Health (Comma separated)" class="kt-input w-full text-xs" />
                            <p class="text-[10px] text-gray-400">Separate keywords with commas (,).</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Daftar Penulis / Tim Kontributor (Submission Authors) -->
        <div class="kt-card">
            <div class="kt-card-header min-h-14 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="kt-card-title text-base font-bold">Daftar Penulis & Kontributor (Authors)</h3>
            </div>
            <div class="kt-card-body p-6 space-y-6">
                <!-- Tabel Penulis -->
                <div class="table-responsive">
                    <table class="table w-full text-xs text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Nama Penulis</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Afiliasi / Institusi</th>
                                <th class="px-4 py-3">Peran</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-150">
                            @foreach($submission->authors as $index => $author)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-bold text-gray-900">{{ $author->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $author->email }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $author->affiliation ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if($author->is_corresponding_author)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-700 border border-red-100">Penulis Utama</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-50 text-slate-700 border border-slate-200">Anggota</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if(!$author->is_corresponding_author)
                                            <form action="{{ route('submissions.delete_author.step3', [$submission->id, $author->id]) }}" method="POST" onsubmit="return confirm('Hapus penulis anggota ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="kt-btn kt-btn-xs kt-btn-ghost text-danger">
                                                    <i class="ki-filled ki-trash"></i> Hapus
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 text-[10px]">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Form Tambah Penulis Anggota -->
                <div class="pt-4 border-t border-gray-100">
                    <h4 class="font-bold text-xs text-gray-800 mb-3">+ Tambah Penulis Anggota (Co-Author)</h4>
                    <form action="{{ route('submissions.add_author.step3', $submission->id) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @csrf
                        <div>
                            <input type="text" name="name" placeholder="Nama Lengkap Penulis" class="kt-input w-full text-xs" required />
                        </div>
                        <div>
                            <input type="email" name="email" placeholder="Alamat Email Penulis" class="kt-input w-full text-xs" required />
                        </div>
                        <div class="flex gap-2">
                            <input type="text" name="affiliation" placeholder="Afiliasi / Universitas" class="kt-input w-full text-xs" />
                            <button type="submit" class="kt-btn kt-btn-outline text-xs whitespace-nowrap">
                                Tambah
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tombol Navigasi -->
        <div class="kt-card p-4 flex items-center justify-between">
            <a href="{{ route('submissions.create.step2', $submission->id) }}" class="kt-btn kt-btn-outline text-xs">
                &larr; Kembali ke Step 2 (Unggah Berkas)
            </a>
            <button type="submit" form="step3-form" class="kt-btn kt-btn-primary text-white text-xs">
                Simpan & Selesaikan Metadata <i class="ki-filled ki-check-circle text-white ml-1"></i>
            </button>
        </div>
    </div>
@endsection

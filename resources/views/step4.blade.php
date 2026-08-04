@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('submissions.create.step4', $submission->id) }}
@endsection

@section('content')
    <div class="grid w-full space-y-6 max-w-5xl mx-auto">
        <!-- Step Wizard Progress Bar -->
        <div class="kt-card p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-600 text-white font-bold flex items-center justify-center text-sm shadow">4</div>
                    <div>
                        <div class="font-bold text-sm text-gray-900">Step 4: Konfirmasi & Finalisasi Penyerahan</div>
                        <div class="text-xs text-gray-500">Tinjau ringkasan naskah Anda dan konfirmasi penyerahan final ke dewan redaksi</div>
                    </div>
                </div>
                <div class="hidden sm:flex items-center gap-2 text-xs font-semibold text-gray-400">
                    <span class="text-gray-500">1. Persyaratan</span> &rarr; 
                    <span class="text-gray-500">2. Upload File</span> &rarr; 
                    <span class="text-gray-500">3. Metadata</span> &rarr; 
                    <span class="text-red-600">4. Konfirmasi</span>
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

        <!-- Ringkasan Kartu Metadata -->
        <div class="kt-card">
            <div class="kt-card-header min-h-14 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="kt-card-title text-base font-bold">Ringkasan Data Naskah</h3>
                <a href="{{ route('submissions.create.step3', $submission->id) }}" class="kt-btn kt-btn-xs kt-btn-outline">
                    <i class="ki-filled ki-pencil mr-1"></i> Edit Metadata
                </a>
            </div>
            <div class="kt-card-body p-6 space-y-4 text-xs">
                <div>
                    <span class="text-gray-500 font-semibold uppercase tracking-wider text-[10px] block">Jurnal Publikasi Tujuan</span>
                    <span class="text-sm font-bold text-gray-900">{{ $submission->journal?->name }} ({{ $submission->journal?->short_name }})</span>
                </div>

                <div>
                    <span class="text-gray-500 font-semibold uppercase tracking-wider text-[10px] block">Judul Naskah</span>
                    <span class="text-sm font-extrabold text-gray-900 leading-snug">{{ $submission->title }}</span>
                </div>

                <div>
                    <span class="text-gray-500 font-semibold uppercase tracking-wider text-[10px] block mb-1">Abstrak</span>
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-gray-700 leading-relaxed">
                        {{ $submission->abstract }}
                    </div>
                </div>

                <div>
                    <span class="text-gray-500 font-semibold uppercase tracking-wider text-[10px] block mb-1">Kata Kunci (Keywords)</span>
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

        <!-- Ringkasan Kartu Berkas Terunggah & Penulis -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Berkas -->
            <div class="lg:col-span-6">
                <div class="kt-card h-full">
                    <div class="kt-card-header min-h-14 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="kt-card-title text-base font-bold">Berkas Terunggah</h3>
                        <a href="{{ route('submissions.create.step2', $submission->id) }}" class="kt-btn kt-btn-xs kt-btn-outline">
                            <i class="ki-filled ki-pencil mr-1"></i> Ubah Berkas
                        </a>
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

            <!-- Tim Penulis -->
            <div class="lg:col-span-6">
                <div class="kt-card h-full">
                    <div class="kt-card-header min-h-14 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="kt-card-title text-base font-bold">Tim Penulis</h3>
                        <a href="{{ route('submissions.create.step3', $submission->id) }}" class="kt-btn kt-btn-xs kt-btn-outline">
                            <i class="ki-filled ki-pencil mr-1"></i> Ubah Tim
                        </a>
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

        <!-- Form Finalisasi Submit -->
        <div class="kt-card p-6 bg-red-50/50 border border-red-100 text-xs space-y-4">
            <div class="flex items-start gap-3">
                <i class="ki-filled ki-information text-red-600 text-xl mt-0.5"></i>
                <div class="space-y-1">
                    <h4 class="font-bold text-slate-900 text-sm">Pernyataan Konfirmasi Final</h4>
                    <p class="text-slate-600 leading-relaxed">
                        Dengan menekan tombol **"Kirim / Penyerahan Final Naskah"** di bawah ini, Anda menyatakan bahwa seluruh data di atas sudah benar dan siap diajukan ke proses penelaahan dewan redaksi IGNITE. Status naskah Anda akan berubah menjadi **Submitted** dan tidak dapat diubah kembali secara bebas tanpa permintaan dari editor.
                    </p>
                </div>
            </div>

            <div class="pt-4 border-t border-red-200/60 flex items-center justify-between">
                <a href="{{ route('submissions.create.step3', $submission->id) }}" class="kt-btn kt-btn-outline text-xs">
                    &larr; Kembali ke Step 3 (Metadata)
                </a>
                <form action="{{ route('submissions.finish.step4', $submission->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan penyerahan naskah ini ke dewan redaksi?');">
                    @csrf
                    <button type="submit" class="kt-btn kt-btn-primary text-white text-xs font-bold px-6 py-2.5">
                        <i class="ki-filled ki-send text-white mr-1.5"></i> Kirim / Penyerahan Final Naskah
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

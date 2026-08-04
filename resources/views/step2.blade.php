@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('submissions.create.step2', $submission->id) }}
@endsection

@section('content')
    <div class="grid w-full space-y-6 max-w-5xl mx-auto">
        <!-- Step Wizard Progress Bar -->
        <div class="kt-card p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-600 text-white font-bold flex items-center justify-center text-sm shadow">2</div>
                    <div>
                        <div class="font-bold text-sm text-gray-900">Step 2: Unggah Berkas Naskah</div>
                        <div class="text-xs text-gray-500">Unggah naskah utama & berkas pendukung penelitian Anda</div>
                    </div>
                </div>
                <div class="hidden sm:flex items-center gap-2 text-xs font-semibold text-gray-400">
                    <span class="text-gray-500">1. Persyaratan</span> &rarr; 
                    <span class="text-red-600">2. Upload File</span> &rarr; 
                    <span>3. Metadata</span> &rarr; 
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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Bagian Kiri: Form Upload Berkas -->
            <div class="lg:col-span-5 space-y-6">
                <div class="kt-card">
                    <div class="kt-card-header min-h-14 py-4 border-b border-gray-200">
                        <h3 class="kt-card-title text-base font-bold">Unggah Berkas Baru</h3>
                    </div>
                    <div class="kt-card-body p-6">
                        <form action="{{ route('submissions.store.step2', $submission->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                            @csrf

                            <div class="space-y-2">
                                <label for="file_role" class="kt-label font-semibold text-gray-700 text-xs">
                                    Jenis Berkas <span class="text-danger-600">*</span>
                                </label>
                                <select name="file_role" id="file_role" class="kt-select w-full text-xs" required>
                                    <option value="naskah_utama">Naskah Utama (Tanpa Identitas Penulis)</option>
                                    <option value="title_page">Halaman Judul (Title Page & Kontak Penulis)</option>
                                    <option value="supplementary">Berkas Pendukung / Instrumen Tambahan</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label for="file" class="kt-label font-semibold text-gray-700 text-xs">
                                    Pilih Berkas <span class="text-danger-600">*</span>
                                </label>
                                <input type="file" name="file" id="file" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100" required />
                                <p class="text-[10px] text-gray-400">Format yang didukung: <strong>.docx, .doc, .pdf</strong> (Maks. 10MB).</p>
                            </div>

                            <button type="submit" class="w-full kt-btn kt-btn-primary text-white text-xs py-2">
                                <i class="ki-filled ki-upload text-white mr-1"></i> Unggah Berkas
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Bagian Kanan: Daftar Berkas Terunggah -->
            <div class="lg:col-span-7 space-y-6">
                <div class="kt-card">
                    <div class="kt-card-header min-h-14 py-4 border-b border-gray-200">
                        <h3 class="kt-card-title text-base font-bold">Berkas yang Telah Diunggah</h3>
                    </div>
                    <div class="kt-card-body p-6">
                        <div class="table-responsive">
                            <table class="table w-full text-xs text-left">
                                <thead class="bg-gray-50 text-gray-600 uppercase font-semibold">
                                    <tr>
                                        <th class="px-4 py-3">Nama Berkas</th>
                                        <th class="px-4 py-3">Jenis Berkas</th>
                                        <th class="px-4 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-150">
                                    @forelse($submission->files as $file)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-900 max-w-xs truncate">
                                                <i class="ki-filled ki-file text-slate-400 mr-1.5"></i> {{ $file->original_name }}
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($file->file_role === 'naskah_utama')
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-700 border border-red-100">Naskah Utama</span>
                                                @elseif($file->file_role === 'title_page')
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">Halaman Judul</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-50 text-slate-700 border border-slate-200">Pendukung</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <form action="{{ route('submissions.delete_file.step2', [$submission->id, $file->id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="kt-btn kt-btn-xs kt-btn-ghost text-danger">
                                                        <i class="ki-filled ki-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-6 text-center text-gray-500">Belum ada berkas yang diunggah.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Navigasi Wizard -->
        <div class="kt-card p-4 flex items-center justify-between">
            <a href="{{ route('submissions.index') }}" class="kt-btn kt-btn-outline text-xs">
                Batal
            </a>
            <form action="{{ route('submissions.store.step2', $submission->id) }}" method="POST">
                @csrf
                <button type="submit" class="kt-btn kt-btn-primary text-white text-xs">
                    Simpan & Lanjutkan ke Step 3 (Metadata) <i class="ki-filled ki-arrow-right text-white ml-1"></i>
                </button>
            </form>
        </div>
    </div>
@endsection

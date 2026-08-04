@extends('layouts.main')

@section('breadcrumbs')
    {{ Breadcrumbs::render('submissions.create') }}
@endsection

@section('content')
    <div class="grid w-full space-y-6 max-w-5xl mx-auto">
        <!-- Step Wizard Progress Bar -->
        <div class="kt-card p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-600 text-white font-bold flex items-center justify-center text-sm shadow">1</div>
                    <div>
                        <div class="font-bold text-sm text-gray-900">Step 1: Pilihan Jurnal & Persyaratan Penulis</div>
                        <div class="text-xs text-gray-500">Pilih jurnal tujuan & setujui checklist etika penulisan</div>
                    </div>
                </div>
                <div class="hidden sm:flex items-center gap-2 text-xs font-semibold text-gray-400">
                    <span class="text-red-600">1. Persyaratan</span> &rarr; 
                    <span>2. Upload File</span> &rarr; 
                    <span>3. Metadata</span> &rarr; 
                    <span>4. Konfirmasi</span>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="kt-card">
            <div class="kt-card-header min-h-16 py-5 border-b border-gray-200">
                <h3 class="kt-card-title text-xl font-bold">Form Pengajuan Naskah Baru (Submission Step 1)</h3>
            </div>
            <div class="kt-card-body p-6">
                <form action="{{ route('submissions.store') }}" method="POST" class="space-y-8">
                    @csrf

                    <!-- 1. Pilihan Jurnal -->
                    <div class="space-y-3">
                        <label for="journal_id" class="kt-label font-bold text-gray-800 text-base">
                            1. Pilih Jurnal Publikasi Tujuan <span class="text-danger-600">*</span>
                        </label>
                        <select name="journal_id" id="journal_id" class="kt-select w-full" required>
                            <option value="">-- Pilih Jurnal Publikasi --</option>
                            @foreach($journals as $j)
                                <option value="{{ $j->id }}" {{ old('journal_id') == $j->id ? 'selected' : '' }}>
                                    {{ $j->name }} ({{ $j->short_name ?? $j->slug }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500">Pastikan naskah Anda sesuai dengan Focus & Scope jurnal yang dipilih.</p>
                    </div>

                    <!-- 2. Checklist Persyaratan Penulis -->
                    <div class="space-y-4 pt-4 border-t border-gray-100">
                        <label class="font-bold text-gray-800 text-base block">
                            2. Checklist Persyaratan Penyerahan Naskah (Submission Checklist) <span class="text-danger-600">*</span>
                        </label>
                        <p class="text-xs text-gray-600">Sebelum melanjutkan, penulis wajib memeriksa dan menyetujui seluruh kelayakan naskah berikut:</p>

                        <div class="space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-200 text-sm">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="checklist[]" value="original" class="kt-checkbox mt-0.5" required />
                                <span class="text-gray-700">Naskah bersifat <strong>orisinal</strong>, bebas dari plagiarisme, dan belum pernah dipublikasikan di media ilmiah manapun.</span>
                            </label>

                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="checklist[]" value="template" class="kt-checkbox mt-0.5" required />
                                <span class="text-gray-700">File naskah disiapkan menggunakan format dokumen resmi <strong>(.docx / MS Word)</strong> sesuai template panduan penulis.</span>
                            </label>

                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="checklist[]" value="ethics" class="kt-checkbox mt-0.5" required />
                                <span class="text-gray-700">Telah menyetujui seluruh ketentuan <strong>Etika Publikasi</strong>, hak cipta, dan bebas dari konflik kepentingan riset.</span>
                            </label>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-4 border-t border-gray-200 flex items-center justify-between">
                        <a href="{{ route('submissions.index') }}" class="kt-btn kt-btn-outline">
                            Batal
                        </a>
                        <button type="submit" class="kt-btn kt-btn-primary text-white">
                            Simpan & Lanjutkan ke Step 2 (Upload File) <i class="ki-filled ki-arrow-right text-white ml-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

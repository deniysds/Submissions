<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Publikasi Ilmiah & DOI - {{ $submission->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .certificate-container {
                box-shadow: none !important;
                border: 2px solid #0f172a !important;
                margin: 0 auto !important;
                max-width: 100% !important;
                page-break-inside: avoid;
            }
        }
        @page {
            size: A4 portrait;
            margin: 1.5cm;
        }
    </style>
</head>
<body class="bg-slate-100 font-sans p-4 sm:p-8 min-h-screen text-slate-800">
    <!-- Action Bar (No Print) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="javascript:history.back()" class="px-4 py-2 rounded-xl bg-white border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition shadow-xs">
            &larr; Kembali
        </a>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition shadow-md flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak / Unduh PDF (DOI Certificate)
            </button>
        </div>
    </div>

    <!-- Official Certificate Container (A4 Printable Layout) -->
    <div class="certificate-container max-w-4xl mx-auto bg-white rounded-2xl shadow-xl border-2 border-slate-900 p-8 sm:p-12 relative overflow-hidden space-y-8">
        
        <!-- Watermark Background -->
        <div class="absolute inset-0 flex items-center justify-center opacity-4 pointer-events-none z-0">
            <span class="text-9xl font-black text-slate-900 rotate-[-30deg] select-none tracking-widest uppercase">IGNITE</span>
        </div>

        <div class="relative z-10 space-y-8">
            <!-- Header Logo & Institution -->
            <div class="flex flex-col sm:flex-row items-center justify-between border-b-2 border-slate-900 pb-6 gap-4 text-center sm:text-left">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-12 border-2 border-slate-900 rounded p-1 grid grid-cols-2 gap-0.5 bg-white shrink-0">
                        <div class="bg-red-700 rounded-xs flex items-center justify-center text-[8px] font-black text-white leading-none">SATRIA</div>
                        <div class="bg-green-700 rounded-xs flex items-center justify-center text-[8px] font-black text-white leading-none">BUDI</div>
                        <div class="bg-blue-800 rounded-xs flex items-center justify-center text-[8px] font-black text-white leading-none">DHARMA</div>
                        <div class="bg-amber-500 rounded-xs flex items-center justify-center text-[8px] font-black text-white leading-none">SETIA</div>
                    </div>
                    <div>
                        <h2 class="text-xs font-black uppercase tracking-widest text-slate-900">YAYASAN SATRIABUDI DHARMA SETIA</h2>
                        <h1 class="text-base font-extrabold text-red-600 tracking-tight">IGNITE SCIENTIFIC PUBLISHING PORTAL</h1>
                        <p class="text-[10px] text-slate-500 font-mono">Official Certificate of Scientific Manuscript & Digital Object Identifier (DOI)</p>
                    </div>
                </div>

                <div class="text-right font-mono text-[11px] space-y-1 sm:border-l-2 sm:border-slate-200 sm:pl-6">
                    <div class="text-slate-500">ID Naskah: <strong class="text-slate-900 font-bold">#IGN-{{ str_pad($submission->id, 5, '0', STR_PAD_LEFT) }}</strong></div>
                    <div class="text-slate-500">Status: <strong class="text-green-600 font-bold uppercase">{{ strtoupper($submission->status) }}</strong></div>
                    <div class="text-slate-500">Tanggal Terbit: <strong class="text-slate-900 font-bold">{{ $submission->decision_at ? $submission->decision_at->format('d M Y') : now()->format('d M Y') }}</strong></div>
                </div>
            </div>

            <!-- DOI Digital Identifier Box -->
            <div class="bg-slate-900 text-white rounded-xl p-5 shadow-inner flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="space-y-1 text-center sm:text-left">
                    <span class="text-[10px] font-bold text-red-400 uppercase tracking-widest block">Digital Object Identifier (DOI Permanen)</span>
                    <div class="text-base sm:text-lg font-mono font-extrabold tracking-tight text-white select-all">
                        https://doi.org/10.1234/ignite.{{ $submission->journal?->slug ?? 'jnl' }}.{{ $submission->id }}
                    </div>
                </div>
                <div class="px-3 py-1.5 rounded-lg bg-red-600 text-white font-bold text-xs shrink-0 font-mono uppercase tracking-wider">
                    Crossref Indexed
                </div>
            </div>

            <!-- Journal Publishing Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                    <span class="text-slate-500 font-semibold uppercase text-[10px]">Jurnal Publikasi</span>
                    <div class="font-bold text-slate-900 text-sm">{{ $submission->journal?->name }}</div>
                    <div class="text-slate-500 font-mono">Singkatan: {{ $submission->journal?->short_name }}</div>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                    <span class="text-slate-500 font-semibold uppercase text-[10px]">Registrasi ISSN Resmi</span>
                    <div class="font-mono text-slate-800">
                        <div>e-ISSN (Online): <strong class="font-bold">{{ $submission->journal?->issn_e ?? 'N/A' }}</strong></div>
                        <div>p-ISSN (Cetak): <strong class="font-bold">{{ $submission->journal?->issn_p ?? 'N/A' }}</strong></div>
                    </div>
                </div>
            </div>

            <!-- Manuscript Metadata -->
            <div class="space-y-4">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Judul Artikel Ilmiah</span>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 leading-snug">
                        {{ $submission->title }}
                    </h3>
                </div>

                <!-- Authors -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500 block">Tim Penulis & Afiliasi Riset</span>
                    <div class="space-y-1.5 text-xs">
                        @forelse($submission->authors as $index => $author)
                            <div class="flex items-center justify-between">
                                <div class="text-slate-800">
                                    <strong>{{ $index + 1 }}. {{ $author->name }}</strong>
                                    <span class="text-slate-500">({{ $author->affiliation ?? 'Afiliasi Peneliti' }})</span>
                                </div>
                                <span class="text-[10px] font-mono text-slate-500">{{ $author->email }}</span>
                            </div>
                        @empty
                            <div class="text-slate-700 font-medium">1. {{ $submission->author?->name ?? 'Penulis Naskah' }} (Yayasan Satriabudi Dharma Setia)</div>
                        @endforelse
                    </div>
                </div>

                <!-- Abstract -->
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Abstrak</span>
                    <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200 text-xs text-slate-700 leading-relaxed text-justify">
                        {{ $submission->abstract }}
                    </div>
                </div>

                <!-- Keywords -->
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Kata Kunci</span>
                    <div class="flex flex-wrap gap-1.5">
                        @if(is_array($submission->keywords))
                            @foreach($submission->keywords as $kw)
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-red-50 text-red-700 border border-red-100">{{ $kw }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Signatures & Verification Seal -->
            <div class="pt-6 border-t-2 border-slate-900 grid grid-cols-1 sm:grid-cols-2 gap-8 items-end">
                <div class="space-y-2 text-xs text-slate-600">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        <span class="font-bold text-slate-900">Lisensi Terbuka (Open Access CC-BY 4.0)</span>
                    </div>
                    <p class="text-[10px] leading-relaxed text-slate-500">
                        Naskah ini didistribusikan di bawah ketentuan lisensi Creative Commons Attribution, yang mengizinkan penggunaan, distribusi, dan reproduksi tanpa batas di media apa pun dengan mencantumkan sumber asli secara benar.
                    </p>
                </div>

                <div class="text-center sm:text-right space-y-1 text-xs">
                    <div class="text-[10px] font-mono text-slate-400">Jakarta, {{ now()->translatedFormat('d F Y') }}</div>
                    <div class="font-bold text-slate-900 text-sm">Dewan Redaksi Pelaksana IGNITE</div>
                    <div class="py-2">
                        <span class="inline-block px-4 py-1.5 rounded border-2 border-dashed border-red-500 font-mono text-[10px] font-bold text-red-600 uppercase tracking-widest">
                            Official Verified Stamp
                        </span>
                    </div>
                    <div class="text-[10px] text-slate-500 font-mono">Yayasan Satriabudi Dharma Setia</div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>

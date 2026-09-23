<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Tanda Terima Pengajuan Naskah IGNITE</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            width: 100%;
            background-color: #f8fafc;
            padding: 30px 15px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #0f172a;
            color: #ffffff;
            padding: 24px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 4px 0 0 0;
            font-size: 11px;
            color: #10b981;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .content {
            padding: 30px;
            font-size: 14px;
            line-height: 1.6;
        }
        .greeting {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .badge-success {
            display: inline-block;
            background-color: #10b981;
            color: #ffffff;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 6px;
            margin-bottom: 8px;
        }
        .card-receipt {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 16px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .title {
            font-weight: 800;
            color: #0f172a;
            font-size: 15px;
            margin-top: 4px;
            line-height: 1.4;
        }
        .meta-list {
            margin: 15px 0;
            padding: 0;
            list-style: none;
            font-size: 13px;
        }
        .meta-list li {
            margin-bottom: 6px;
        }
        .meta-list strong {
            color: #475569;
        }
        .timeline-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 14px;
            border-radius: 8px;
            margin: 16px 0;
            font-size: 13px;
            color: #475569;
        }
        .timeline-box ol {
            margin: 8px 0 0 0;
            padding-left: 18px;
        }
        .timeline-box li {
            margin-bottom: 4px;
        }
        .btn-wrapper {
            text-align: center;
            margin: 28px 0 10px 0;
        }
        .btn {
            display: inline-block;
            background-color: #0f172a;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 9999px;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.25);
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 30px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header Brand -->
            <div class="header">
                <p>Tanda Terima Resmi Naskah</p>
                <h1>IGNITE PUBLISHING PORTAL</h1>
            </div>

            <!-- Content Body -->
            <div class="content">
                <div class="greeting">
                    Yth. {{ $author->name }},
                </div>
                <p>
                    Terima kasih telah mengajukan naskah penelitian Anda ke <strong>{{ $submission->journal?->name }} ({{ $submission->journal?->short_name }})</strong>. Naskah Anda telah resmi diterima oleh sistem redaksi kami.
                </p>

                <!-- Receipt Card -->
                <div class="card-receipt">
                    <span class="badge-success">Naskah Berhasil Diserahkan</span>
                    <div class="title">{{ $submission->title }}</div>
                </div>

                <!-- Manuscript Info -->
                <ul class="meta-list">
                    <li><strong>Nomor Registrasi Naskah:</strong> #IGN-{{ str_pad($submission->id, 5, '0', STR_PAD_LEFT) }}</li>
                    <li><strong>Jurnal Publikasi:</strong> {{ $submission->journal?->name }}</li>
                    <li><strong>Waktu Penyerahan:</strong> {{ now()->translatedFormat('d F Y, H:i') }} WIB</li>
                    <li><strong>Penulis Korespondensi:</strong> {{ $author->name }} ({{ $author->email }})</li>
                </ul>

                <!-- Next Steps Info -->
                <div class="timeline-box">
                    <strong>Tahapan Selanjutnya:</strong>
                    <ol>
                        <li><strong>Pemeriksaan Awal (Desk Review):</strong> Redaksi memeriksa kesesuaian cakupan naskah, format, dan plagiarisme (3-5 hari kerja).</li>
                        <li><strong>Penelaahan Sejawat (Peer-Review):</strong> Naskah yang lolos akan dikirim ke minimal 2 mitra bestari (*double-blind*).</li>
                        <li><strong>Pemberitahuan Keputusan:</strong> Anda akan menerima notifikasi email mengenai revisi, penerimaan, atau penolakan.</li>
                    </ol>
                </div>

                <div class="btn-wrapper">
                    <a href="{{ route('submissions.show', $submission->id) }}" class="btn">
                        Lihat Status &amp; Detail Naskah &rarr;
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>&copy; {{ date('Y') }} Yayasan Satriabudi Dharma Setia. Seluruh hak cipta dilindungi undang-undang.</p>
                <p>Email ini berfungsi sebagai bukti sah tanda terima pengajuan naskah pada portal IGNITE.</p>
            </div>
        </div>
    </div>
</body>
</html>

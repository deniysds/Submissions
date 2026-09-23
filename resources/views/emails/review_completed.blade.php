<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Telaah Sejawat Selesai IGNITE</title>
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
            color: #6366f1;
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
        .card-review {
            background-color: #f1f5f9;
            border-left: 4px solid #6366f1;
            padding: 16px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .badge-rec {
            display: inline-block;
            background-color: #6366f1;
            color: #ffffff;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 6px;
            margin-bottom: 8px;
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
        .comments-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 14px;
            border-radius: 8px;
            margin: 16px 0;
            font-size: 13px;
            color: #475569;
        }
        .btn-wrapper {
            text-align: center;
            margin: 28px 0 10px 0;
        }
        .btn {
            display: inline-block;
            background-color: #dc2626;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 9999px;
            box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.25);
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
                <p>Notifikasi Dewan Redaksi</p>
                <h1>IGNITE PUBLISHING PORTAL</h1>
            </div>

            <!-- Content Body -->
            <div class="content">
                <div class="greeting">
                    Yth. {{ $editor?->name ?? 'Dewan Redaksi' }},
                </div>
                <p>
                    Kami menginformasikan bahwa mitra bestari (reviewer) telah menyelesaikan formulir penilaian dan mengirimkan hasil evaluasi telaah sejawat untuk naskah berikut:
                </p>

                <!-- Review Card -->
                <div class="card-review">
                    <span class="badge-rec">Telaah Selesai: {{ strtoupper(str_replace('_', ' ', $assignment->recommendation ?: 'completed')) }}</span>
                    <div class="title">{{ $submission->title }}</div>
                </div>

                <!-- Manuscript Info -->
                <ul class="meta-list">
                    <li><strong>Nomor ID Naskah:</strong> #IGN-{{ str_pad($submission->id, 5, '0', STR_PAD_LEFT) }}</li>
                    <li><strong>Jurnal:</strong> {{ $submission->journal?->name }}</li>
                    <li><strong>Rekomendasi Reviewer:</strong> <strong style="color: #6366f1;">{{ strtoupper(str_replace('_', ' ', $assignment->recommendation ?: '-')) }}</strong></li>
                    <li><strong>Waktu Penyerahan Telaah:</strong> {{ now()->translatedFormat('d F Y, H:i') }} WIB</li>
                </ul>

                @if(!empty($assignment->comments_for_author))
                    <p style="margin-bottom: 4px; font-weight: 700; color: #0f172a; font-size: 13px;">Ringkasan Komentar untuk Penulis:</p>
                    <div class="comments-box">
                        {{ \Illuminate\Support\Str::limit($assignment->comments_for_author, 250) }}
                    </div>
                @endif

                <p>
                    Silakan tinjau seluruh catatan evaluasi dan tentukan keputusan redaksi (*Editorial Decision*) pada panel redaksi:
                </p>

                <div class="btn-wrapper">
                    <a href="{{ route('editor.submissions.show', $submission->id) }}" class="btn">
                        Buka Detail Naskah &amp; Evaluasi &rarr;
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>&copy; {{ date('Y') }} Yayasan Satriabudi Dharma Setia. Seluruh hak cipta dilindungi undang-undang.</p>
                <p>Email ini dikirimkan secara otomatis oleh sistem notifikasi IGNITE Publishing System.</p>
            </div>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bukti Reservasi</title>
    <style>
        @page {
            margin: 18px 22px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            background: #ffffff;
            font-size: 11px;
            line-height: 1.4;
        }

        .page { width: 100%; }

        .hero {
            background: #b91c1c;
            color: #ffffff;
            border-radius: 18px;
            padding: 18px 22px;
            margin-bottom: 14px;
        }

        .eyebrow {
            font-size: 9px;
            letter-spacing: 0.24em;
            opacity: 0.9;
            margin-bottom: 6px;
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 5px;
        }

        .subtitle {
            font-size: 10px;
            color: #fee2e2;
            margin: 0;
        }

        .code-pill {
            display: inline-block;
            margin-top: 12px;
            padding: 7px 12px;
            border-radius: 999px;
            background: #7f1d1d;
            border: 1px solid #fecaca;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.14em;
        }

        .layout {
            width: 100%;
            border-collapse: collapse;
        }

        .left-col {
            width: 61%;
            vertical-align: top;
            padding-right: 10px;
        }

        .right-col {
            width: 39%;
            vertical-align: top;
        }

        .card,
        .notice,
        .qr-wrap {
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 10px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
        }

        .notice {
            background: #fef2f2;
            border: 1px solid #fecaca;
        }

        .section-title {
            margin: 0 0 10px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.22em;
            color: #b91c1c;
            font-weight: 700;
        }

        .detail-row { margin-bottom: 9px; }

        .detail-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #6b7280;
            margin-bottom: 3px;
        }

        .detail-value {
            font-size: 11px;
            font-weight: 700;
            color: #111827;
        }

        .detail-text {
            font-size: 10px;
            color: #374151;
            margin-top: 3px;
        }

        .qr-wrap {
            text-align: center;
            background: #ffffff;
            border: 1px solid #e5e7eb;
        }

        .qr-box {
            display: inline-block;
            background: #ffffff;
            padding: 8px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            margin-bottom: 9px;
        }

        .qr-box img {
            display: block;
            width: 145px;
            height: 145px;
        }

        .note-title {
            margin: 0 0 6px;
            font-size: 11px;
            font-weight: 700;
            color: #7f1d1d;
        }

        .footer {
            margin-top: 6px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="hero">
            <div class="eyebrow">TemuLKPP</div>
            <h1 class="title">Bukti Reservasi</h1>
            <p class="subtitle">Dokumen ini menjadi bukti reservasi resmi Anda untuk layanan TemuLKPP.</p>
            <div class="code-pill">{{ $reservation->kode_reservasi }}</div>
        </div>

        <table class="layout">
            <tr>
                <td class="left-col">
                    <div class="card">
                        <h2 class="section-title">Detail Tamu</h2>

                        <div class="detail-row">
                            <div class="detail-label">Nama Lengkap</div>
                            <div class="detail-value">{{ $reservation->nama_lengkap }}</div>
                        </div>

                        <div class="detail-row">
                            <div class="detail-label">Jabatan</div>
                            <div class="detail-value">{{ $reservation->jabatan }}</div>
                        </div>

                        <div class="detail-row">
                            <div class="detail-label">Instansi / Perusahaan</div>
                            <div class="detail-value">{{ $reservation->asal_pt }}</div>
                        </div>

                        <div class="detail-row">
                            <div class="detail-label">Jenis Layanan</div>
                            <div class="detail-value">{{ $reservation->jenis_layanan }}</div>
                        </div>

                        <div class="detail-row" style="margin-bottom: 0;">
                            <div class="detail-label">Jadwal Reservasi</div>
                            <div class="detail-value">{{ $formattedSchedule }} WIB</div>
                        </div>
                    </div>

                    <div class="notice" style="margin-top: 16px;">
                        <h3 class="note-title">Pemberitahuan Kehadiran</h3>
                        <div class="detail-text">
                            Tamu wajib hadir minimal 15 menit sebelum jadwal reservasi yang telah ditetapkan agar proses verifikasi dan antrean dapat berjalan lancar.
                        </div>
                    </div>

                    <div class="card" style="margin-top: 16px;">
                        <h2 class="section-title">Detail Keperluan</h2>
                        <div class="detail-text">{{ $reservation->detail_keperluan }}</div>
                    </div>
                </td>
                <td class="right-col">
                    <div class="qr-wrap">
                        <div class="section-title">QR Verifikasi</div>
                        <div class="qr-box">
                            <img src="{{ $qrCodeDataUri }}" alt="QR Code Reservasi">
                        </div>
                        <div class="detail-value" style="font-size: 10px; letter-spacing: 0.1em;">{{ $reservation->kode_reservasi }}</div>
                        <div class="detail-text" style="margin-top: 8px;">
                            Tunjukkan QR code ini saat hadir di lokasi untuk mempercepat proses verifikasi.
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            Dokumen ini dihasilkan secara otomatis oleh sistem TemuLKPP. Simpan file PDF ini dan tunjukkan saat kedatangan.
        </div>
    </div>
</body>
</html>

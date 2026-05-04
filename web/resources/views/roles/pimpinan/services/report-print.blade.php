<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: A4;
            margin: 1.2cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #1c1917;
            font-size: 10px;
            line-height: 1.4;
        }

        /* Letterhead */
        .letterhead {
            width: 100%;
            border-bottom: 2px solid #1c1917;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .letterhead table {
            width: 100%;
            border-collapse: collapse;
        }
        .letterhead-logo-td {
            width: 60px;
            vertical-align: middle;
        }
        .letterhead-logo {
            height: 50px;
        }
        .letterhead-info-td {
            text-align: right;
            vertical-align: middle;
        }
        .letterhead-info h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .letterhead-info p {
            margin: 2px 0 0;
            font-size: 9px;
            color: #57534e;
        }

        /* Report Header */
        .report-header {
            margin-bottom: 20px;
        }
        .report-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            color: #1c1917;
        }
        .report-header-meta {
            margin-top: 5px;
            color: #78716c;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }

        /* Filter Box */
        .filter-container {
            background: #f8f7f7;
            border: 1px solid #e7e5e4;
            padding: 12px;
            margin-bottom: 20px;
        }
        .filter-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #a8a29e;
            margin-bottom: 8px;
        }
        .filter-table {
            width: 100%;
            border-collapse: collapse;
        }
        .filter-table td {
            width: 33.33%;
            padding: 4px 0;
            vertical-align: top;
            border: none;
        }
        .filter-label {
            font-size: 7px;
            color: #78716c;
            font-weight: bold;
            text-transform: uppercase;
            display: block;
        }
        .filter-value {
            font-size: 10px;
            font-weight: bold;
            color: #1c1917;
            display: block;
        }

        /* Main Table */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .main-table th {
            background: #1c1917;
            color: white;
            text-align: left;
            padding: 8px 6px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .main-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #e7e5e4;
            vertical-align: top;
            font-size: 9px;
        }
        .td-code {
            font-weight: bold;
            color: #b91c1c;
        }
        .td-status {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #57534e;
        }
        .status-completed {
            color: #166534;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 9px;
        }
        .signature-section {
            display: inline-block;
            text-align: center;
            width: 180px;
        }
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #1c1917;
            padding-top: 5px;
            font-weight: bold;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="letterhead">
        <table>
            <tr>
                <td class="letterhead-logo-td">
                    <img src="{{ public_path('img/icon/logo/logoBlackRed.png') }}" class="letterhead-logo">
                </td>
                <td class="letterhead-info-td">
                    <div class="letterhead-info">
                        <h2>Lembaga Kebijakan Pengadaan Barang/Jasa Pemerintah</h2>
                        <p>Gedung LKPP, Kawasan Epicentrum Kuningan, Jakarta Selatan</p>
                        <p>Telp: (021) 2991 2345 | Website: lkpp.go.id</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="report-header">
        <h1>Laporan Rekapitulasi Layanan</h1>
        <div class="report-header-meta">
            Dicetak oleh: {{ auth()->guard('pimpinan')->user()->name ?? auth()->guard('superadmin')->user()->name ?? 'Administrator' }} | Tanggal: {{ $printedAt->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="filter-container">
        <div class="filter-title">Parameter Laporan</div>
        <table class="filter-table">
            <tr>
                <td>
                    <span class="filter-label">Pencarian</span>
                    <span class="filter-value">{{ $filters['q'] ?: 'Semua Data' }}</span>
                </td>
                <td>
                    <span class="filter-label">Agent</span>
                    <span class="filter-value">
                        @if($filters['agent_id'])
                            {{ optional($agents->find($filters['agent_id']))->name ?? 'N/A' }}
                        @else
                            Semua Agent
                        @endif
                    </span>
                </td>
                <td>
                    <span class="filter-label">Status</span>
                    <span class="filter-value">{{ $filters['status'] ? str_replace('_', ' ', strtoupper($filters['status'])) : 'Semua Status' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="filter-label">Periode Mulai</span>
                    <span class="filter-value">{{ $filters['date_from'] ?: '-' }}</span>
                </td>
                <td>
                    <span class="filter-label">Periode Selesai</span>
                    <span class="filter-value">{{ $filters['date_to'] ?: '-' }}</span>
                </td>
                <td>
                    <span class="filter-label">Jenis Layanan</span>
                    <span class="filter-value">{{ $filters['jenis_layanan'] ?: 'Semua Layanan' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 15%">Jadwal</th>
                <th style="width: 15%">No. Tiket</th>
                <th style="width: 25%">Nama Tamu</th>
                <th style="width: 20%">Jenis Layanan</th>
                <th style="width: 15%">Agent</th>
                <th style="width: 10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ optional($row->tanggal_jam)->format('d/m/y H:i') ?? '-' }}</td>
                    <td class="td-code">{{ $row->kode_reservasi }}</td>
                    <td>{{ $row->nama_lengkap }}</td>
                    <td>{{ $row->jenis_layanan }}</td>
                    <td>{{ optional($row->agent)->name ?? '-' }}</td>
                    <td class="td-status {{ $row->status === 'completed' ? 'status-completed' : '' }}">
                        {{ str_replace('_', ' ', strtoupper($row->status)) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #78716c;">
                        Tidak ada data yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-section">
            <p>Jakarta, {{ $printedAt->format('d F Y') }}</p>
            <div class="signature-line">
                {{ auth()->guard('pimpinan')->user()->name ?? auth()->guard('superadmin')->user()->name ?? 'Administrator' }}
            </div>
            <p style="margin-top: 3px; font-size: 8px;">Pimpinan Unit Kerja</p>
        </div>
    </div>
</body>
</html>

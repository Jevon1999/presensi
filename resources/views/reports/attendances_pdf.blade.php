<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0 0 5px 0;
            color: #1f2937;
        }
        .header h2 {
            font-size: 14px;
            margin: 0 0 10px 0;
            color: #374151;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            vertical-align: top;
        }
        .info-box {
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 10px;
            background: #f9fafb;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: left;
        }
        .data-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            color: #4b5563;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .summary-table {
            width: 50%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #d1d5db;
            padding: 4px 8px;
        }
        .summary-table th {
            background-color: #f3f4f6;
            text-align: left;
            font-size: 9px;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>PT. GLOBAL INTERMEDIA LINTAS BATAS</h1>
        <h2>Laporan Rekapitulasi Absensi Peserta Magang</h2>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <div class="info-box">
                    <strong>Periode:</strong> {{ \Carbon\Carbon::parse($params['start_date'])->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($params['end_date'])->translatedFormat('d M Y') }}<br>
                    <strong>Waktu Cetak:</strong> {{ now()->translatedFormat('d M Y, H:i') }} WIB<br>
                    <strong>Total Data:</strong> {{ count($attendances) }} record
                </div>
            </td>
            <td style="width: 50%;">
                <table class="summary-table" style="width: 100%; margin-bottom: 0;">
                    <tr>
                        <th colspan="4" class="text-center" style="background: #e5e7eb;">RINGKASAN KEHADIRAN</th>
                    </tr>
                    <tr>
                        <th>Hadir</th>
                        <td class="text-center">{{ $stats['hadir'] ?? 0 }}</td>
                        <th>WFO</th>
                        <td class="text-center">{{ $stats['wfo'] ?? 0 }}</td>
                    </tr>
                    <tr>
                        <th>Izin</th>
                        <td class="text-center">{{ $stats['izin'] ?? 0 }}</td>
                        <th>WFA</th>
                        <td class="text-center">{{ $stats['wfa'] ?? 0 }}</td>
                    </tr>
                    <tr>
                        <th>Sakit</th>
                        <td class="text-center">{{ $stats['sakit'] ?? 0 }}</td>
                        <th>Alpha</th>
                        <td class="text-center">{{ $stats['alpha'] ?? 0 }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 3%;">No</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 25%;">Nama & Sekolah/Kampus</th>
                <th class="text-center" style="width: 8%;">Check In</th>
                <th class="text-center" style="width: 8%;">Check Out</th>
                <th style="width: 12%;">Kantor</th>
                <th class="text-center" style="width: 5%;">Tipe</th>
                <th class="text-center" style="width: 5%;">Telat</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 16%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $a)
                @php
                    $isLate = ($a['is_late'] ?? false) ? 'Ya' : '-';
                    $status = strtoupper($a['status'] ?? '-');
                    $reason = '-';
                    if (!empty($a['permissions']) && count($a['permissions']) > 0) {
                        $reason = $a['permissions'][0]['keterangan'] ?? $a['permissions'][0]['reason'] ?? '-';
                    } elseif ($status !== 'HADIR') {
                        $reason = 'Tanpa Keterangan';
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($a['tanggal'])->translatedFormat('d M Y') }}</td>
                    <td>
                        <strong>{{ $a['member']['nama_lengkap'] ?? $a['member']['name'] ?? '-' }}</strong><br>
                        <span style="font-size: 8px; color: #6b7280;">{{ $a['member']['asal_sekolah'] ?? '-' }} • {{ $a['member']['jurusan'] ?? '-' }}</span>
                    </td>
                    <td class="text-center">{{ $a['check_in_time'] ?? '-' }}</td>
                    <td class="text-center">{{ $a['check_out_time'] ?? '-' }}</td>
                    <td>{{ $a['member']['office']['name'] ?? '-' }}</td>
                    <td class="text-center">{{ isset($a['work_type']) && $a['work_type'] ? strtoupper($a['work_type']) : '-' }}</td>
                    <td class="text-center">{{ $isLate }}</td>
                    <td><strong>{{ $status }}</strong></td>
                    <td style="font-size: 8px;">{{ $reason }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px;">Tidak ada data absensi untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini di-generate secara otomatis oleh sistem.</p>
    </div>

</body>
</html>

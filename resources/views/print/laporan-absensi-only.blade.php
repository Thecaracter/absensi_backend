<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi - {{ $periode->format('F Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            position: relative;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        
        .logo {
            width: 50px;
            height: 50px;
            margin-right: 15px;
        }
        
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .company-info {
            font-size: 9px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .report-period {
            font-size: 12px;
            color: #666;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 10px;
            padding: 8px;
            background-color: #f8f9fa;
            border-left: 4px solid #10b981;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .stats-row {
            display: table-row;
        }
        
        .stats-cell {
            display: table-cell;
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
            background-color: #f8f9fa;
            width: 20%;
        }
        
        .stats-label {
            font-weight: bold;
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .stats-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
        }
        
        .table th {
            background-color: #f1f5f9;
            border: 1px solid #d1d5db;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
        }
        
        .table td {
            border: 1px solid #d1d5db;
            padding: 4px;
            font-size: 8px;
        }
        
        .table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .progress-bar {
            background-color: #e5e7eb;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            background-color: #10b981;
            border-radius: 4px;
        }
        
        /* Simple Signature Section */
        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
            position: relative;
            min-height: 150px;
        }
        
        .signature-container {
            text-align: right;
            padding-right: 50px;
        }
        
        .signature-header {
            font-size: 11px;
            color: #333;
            margin-bottom: 60px;
        }
        
        .signature-location {
            font-weight: 500;
            color: #333;
            margin-bottom: 2px;
        }
        
        .signature-role {
            color: #333;
            font-weight: 500;
        }
        
        .signature-name {
            font-weight: bold;
            font-size: 12px;
            color: #333;
            text-decoration: underline;
        }
        
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        
        /* Print Styles */
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .signature-section { page-break-inside: avoid; }
        }
        
        /* Print Button */
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000;
        }
        
        .print-btn:hover {
            background: #059669;
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <button class="print-btn no-print" onclick="window.print()">🖨️ Print / Save PDF</button>
    
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRJiCXKRYj8AQ3rdNQUhbMQ5xEf60dSLGf3Lg&s" alt="Company Logo" class="logo">
            <div class="company-name">{{ $company['name'] }}</div>
        </div>
        <div class="company-info">
            {{ $company['address'] }}<br>
            Telp: {{ $company['phone'] }} | Email: {{ $company['email'] }}
        </div>
        <div class="report-title">LAPORAN ABSENSI KARYAWAN</div>
        <div class="report-period">Periode: {{ $periode->format('F Y') }}</div>
    </div>

    <!-- SECTION: LAPORAN ABSENSI -->
    <div class="section">
        <div class="section-title">📊 RINGKASAN ABSENSI KARYAWAN</div>
        
        <!-- Statistik Absensi -->
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell">
                    <div class="stats-label">HARI KERJA</div>
                    <div class="stats-value">{{ $absensi['stats']['total_hari_kerja'] }}</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-label">TOTAL HADIR</div>
                    <div class="stats-value">{{ $absensi['stats']['total_hadir'] }}</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-label">TOTAL TERLAMBAT</div>
                    <div class="stats-value">{{ $absensi['stats']['total_terlambat'] }}</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-label">TIDAK HADIR</div>
                    <div class="stats-value">{{ $absensi['stats']['total_tidak_hadir'] }}</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-label">TOTAL IZIN</div>
                    <div class="stats-value">{{ $absensi['stats']['total_izin'] }}</div>
                </div>
            </div>
        </div>

        <!-- Tabel Absensi -->
        <table class="table">
            <thead>
                <tr>
                    <th width="15%">ID Karyawan</th>
                    <th width="20%">Nama Karyawan</th>
                    <th width="12%">Shift</th>
                    <th width="10%">Hadir</th>
                    <th width="10%">Terlambat</th>
                    <th width="10%">Tidak Hadir</th>
                    <th width="8%">Izin</th>
                    <th width="15%">Tingkat Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absensi['absensi'] as $emp)
                <tr>
                    <td>{{ $emp['id_karyawan'] }}</td>
                    <td>{{ $emp['karyawan'] }}</td>
                    <td>{{ $emp['shift'] }}</td>
                    <td class="text-center">{{ $emp['total_hadir'] }}</td>
                    <td class="text-center">{{ $emp['total_terlambat'] }}</td>
                    <td class="text-center">{{ $emp['total_tidak_hadir'] }}</td>
                    <td class="text-center">{{ $emp['total_izin'] }}</td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <div class="progress-bar" style="width: 60px; margin-right: 5px;">
                                <div class="progress-fill" style="width: {{ $emp['tingkat_kehadiran'] }}%"></div>
                            </div>
                            <span style="font-size: 8px;">{{ $emp['tingkat_kehadiran'] }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data absensi</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Summary Analisis -->
        <div style="margin-top: 20px; padding: 12px; background-color: #f0f9ff; border-radius: 5px; border-left: 4px solid #10b981;">
            <div style="font-weight: bold; font-size: 10px; margin-bottom: 8px;">📈 ANALISIS KEHADIRAN</div>
            <div style="font-size: 9px; color: #4b5563;">
                <strong>Total Hari Kerja:</strong> {{ $absensi['stats']['total_hari_kerja'] }} hari | 
                <strong>Total Kehadiran:</strong> {{ $absensi['stats']['total_hadir'] }} kehadiran | 
                <strong>Tingkat Keterlambatan:</strong> {{ $absensi['stats']['total_terlambat'] > 0 ? round(($absensi['stats']['total_terlambat'] / $absensi['stats']['total_hadir']) * 100, 1) : 0 }}%
            </div>
            <div style="font-size: 8px; color: #6b7280; margin-top: 5px;">
                Laporan ini mencakup data kehadiran semua karyawan yang aktif untuk periode {{ $periode->format('F Y') }}. 
                Data diambil dari sistem absensi digital dan telah diverifikasi keakuratannya.
            </div>
        </div>
    </div>

    <!-- SIMPLE SIGNATURE SECTION -->
    <div class="signature-section">
        <div class="signature-container">
            <div class="signature-header">
                <div class="signature-location">Bogor, {{ now()->format('l, d F Y') }}</div>
                <div class="signature-role">HRD</div>
            </div>
            
            <div class="signature-name">DWI SOLANA</div>
        </div>
        
        <!-- Note -->
        <div style="margin-top: 40px; padding: 10px; background-color: #f9fafb; border-radius: 5px; font-size: 8px; color: #6b7280; border-left: 3px solid #10b981;">
            <strong>Catatan:</strong> Laporan ini khusus memuat data absensi karyawan untuk periode {{ $periode->format('F Y') }}. 
            Data kehadiran, keterlambatan, dan ketidakhadiran telah diverifikasi melalui sistem absensi digital dan dapat dipertanggungjawabkan keakuratannya.
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} | Laporan Absensi - {{ $periode->format('F Y') }}
    </div>
</body>
</html>
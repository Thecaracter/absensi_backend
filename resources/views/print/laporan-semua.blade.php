<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kehadiran & Izin - {{ $periode->format('F Y') }}</title>
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
            border-left: 4px solid #3b82f6;
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
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .status-hadir { background-color: #dcfce7; color: #166534; }
        .status-terlambat { background-color: #fef3c7; color: #92400e; }
        .status-tidak-hadir { background-color: #fee2e2; color: #991b1b; }
        .status-izin { background-color: #dbeafe; color: #1e40af; }
        .status-menunggu { background-color: #fef3c7; color: #92400e; }
        .status-disetujui { background-color: #dcfce7; color: #166534; }
        .status-ditolak { background-color: #fee2e2; color: #991b1b; }
        
        /* Signature Section */
        .signature-section {
            margin-top: 40px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .signature-container {
            display: table;
            width: 100%;
        }
        
        .signature-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 20px;
        }
        
        .signature-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }
        
        .signature-box {
            border: 1px solid #ddd;
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 5px;
            min-height: 120px;
        }
        
        .signature-title {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 5px;
            color: #374151;
        }
        
        .signature-info {
            font-size: 9px;
            color: #6b7280;
            margin-bottom: 15px;
        }
        
        .signature-space {
            height: 60px;
            border-bottom: 1px solid #333;
            margin-bottom: 8px;
        }
        
        .signature-name {
            font-weight: bold;
            font-size: 10px;
            text-align: center;
        }
        
        .signature-position {
            font-size: 9px;
            color: #6b7280;
            text-align: center;
            margin-top: 2px;
        }
        
        .date-info {
            font-size: 9px;
            color: #6b7280;
            margin-bottom: 10px;
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
        
        .page-break {
            page-break-before: always;
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
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000;
        }
        
        .print-btn:hover {
            background: #2563eb;
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
        <div class="report-title">LAPORAN KEHADIRAN & IZIN KARYAWAN</div>
        <div class="report-period">Periode: {{ $periode->format('F Y') }}</div>
    </div>

    <!-- SECTION 1: LAPORAN ABSENSI -->
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
                    <th width="8%">Hadir</th>
                    <th width="8%">Terlambat</th>
                    <th width="8%">Tidak Hadir</th>
                    <th width="8%">Izin</th>
                    <th width="21%">Tingkat Kehadiran</th>
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
    </div>

    <!-- SECTION 2: LAPORAN IZIN -->
    <div class="section page-break">
        <div class="section-title">📋 RINGKASAN IZIN & CUTI KARYAWAN</div>
        
        <!-- Statistik Izin -->
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell">
                    <div class="stats-label">TOTAL PENGAJUAN</div>
                    <div class="stats-value">{{ $izin['stats']['total_pengajuan'] }}</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-label">DISETUJUI</div>
                    <div class="stats-value">{{ $izin['stats']['disetujui'] }}</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-label">MENUNGGU</div>
                    <div class="stats-value">{{ $izin['stats']['menunggu'] }}</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-label">DITOLAK</div>
                    <div class="stats-value">{{ $izin['stats']['ditolak'] }}</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-label">TOTAL HARI</div>
                    <div class="stats-value">{{ $izin['stats']['total_hari_izin'] }}</div>
                </div>
            </div>
        </div>

        <!-- Tabel Izin -->
        <table class="table">
            <thead>
                <tr>
                    <th width="12%">Tanggal Pengajuan</th>
                    <th width="18%">Karyawan</th>
                    <th width="15%">Jenis Izin</th>
                    <th width="20%">Periode Izin</th>
                    <th width="8%">Durasi</th>
                    <th width="12%">Status</th>
                    <th width="15%">Disetujui Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse($izin['izin'] as $leave)
                <tr>
                    <td>{{ $leave->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div>{{ $leave->user->name }}</div>
                        <div style="font-size: 7px; color: #666;">({{ $leave->user->id_karyawan }})</div>
                    </td>
                    <td>{{ $leave->getJenisIzinText() }}</td>
                    <td>{{ $leave->tanggal_mulai->format('d/m/Y') }} - {{ $leave->tanggal_selesai->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $leave->total_hari }} hari</td>
                    <td>
                        @php
                            $statusClass = match($leave->status) {
                                'menunggu' => 'status-menunggu',
                                'disetujui' => 'status-disetujui',
                                'ditolak' => 'status-ditolak',
                                default => 'status-menunggu'
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">
                            {{ $leave->getStatusText() }}
                        </span>
                    </td>
                    <td>{{ $leave->approver ? $leave->approver->name : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data izin</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- SIGNATURE SECTION -->
    <div class="signature-section">
        <div class="signature-container">
            <div class="signature-left">
                <div class="signature-box">
                    <div class="signature-title">Dibuat Oleh:</div>
                    <div class="signature-info">Sistem Absensi Digital</div>
                    <div class="date-info">Tanggal: {{ now()->format('d F Y') }}</div>
                    <div style="font-size: 9px; color: #6b7280; margin-top: 20px;">
                        <strong>Keterangan:</strong><br>
                        Laporan ini dibuat secara otomatis oleh sistem<br>
                        berdasarkan data kehadiran dan izin karyawan.
                    </div>
                </div>
            </div>
            
            <div class="signature-right">
                <div class="signature-box">
                    <div class="signature-title">Mengetahui & Menyetujui:</div>
                    <div class="signature-info">Manager HRD</div>
                    <div class="date-info">Jakarta, {{ now()->format('d F Y') }}</div>
                    
                    <div class="signature-space"></div>
                    
                    <div class="signature-name">_________________________</div>
                    <div class="signature-position">
                        <strong>Nama: ________________________</strong><br>
                        NIK: ________________________<br>
                        Manager Human Resource Development
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Note -->
        <div style="margin-top: 15px; padding: 10px; background-color: #f3f4f6; border-radius: 5px; font-size: 8px; color: #6b7280;">
            <strong>Catatan:</strong> Laporan ini adalah dokumen resmi yang mencakup data kehadiran dan izin seluruh karyawan untuk periode {{ $periode->format('F Y') }}. 
            Data telah diverifikasi melalui sistem absensi digital dan dapat dipertanggungjawabkan keakuratannya.
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} | Laporan Kehadiran & Izin - {{ $periode->format('F Y') }}
    </div>
</body>
</html>
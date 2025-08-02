<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Izin & Cuti - {{ $periode->format('F Y') }}</title>
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
            border-left: 4px solid #f59e0b;
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
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .status-menunggu { background-color: #fef3c7; color: #92400e; }
        .status-disetujui { background-color: #dcfce7; color: #166534; }
        .status-ditolak { background-color: #fee2e2; color: #991b1b; }
        
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
            background: #f59e0b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000;
        }
        
        .print-btn:hover {
            background: #d97706;
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
        <div class="report-title">LAPORAN IZIN & CUTI KARYAWAN</div>
        <div class="report-period">Periode: {{ $periode->format('F Y') }}</div>
    </div>

    <!-- SECTION: LAPORAN IZIN -->
    <div class="section">
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
                    <th width="10%">Tanggal Pengajuan</th>
                    <th width="18%">Karyawan</th>
                    <th width="12%">Jenis Izin</th>
                    <th width="18%">Periode Izin</th>
                    <th width="8%">Durasi</th>
                    <th width="20%">Alasan</th>
                    <th width="10%">Status</th>
                    <th width="4%">Disetujui Oleh</th>
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
                    <td>{{ Str::limit($leave->alasan, 40) }}</td>
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
                    <td>{{ $leave->approver ? Str::limit($leave->approver->name, 15) : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data izin</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Summary Analisis -->
        <div style="margin-top: 20px; padding: 12px; background-color: #fffbeb; border-radius: 5px; border-left: 4px solid #f59e0b;">
            <div style="font-weight: bold; font-size: 10px; margin-bottom: 8px;">📊 ANALISIS IZIN & CUTI</div>
            <div style="font-size: 9px; color: #4b5563;">
                <strong>Total Pengajuan:</strong> {{ $izin['stats']['total_pengajuan'] }} pengajuan | 
                <strong>Tingkat Persetujuan:</strong> {{ $izin['stats']['total_pengajuan'] > 0 ? round(($izin['stats']['disetujui'] / $izin['stats']['total_pengajuan']) * 100, 1) : 0 }}% | 
                <strong>Rata-rata Durasi:</strong> {{ $izin['stats']['disetujui'] > 0 ? round($izin['stats']['total_hari_izin'] / $izin['stats']['disetujui'], 1) : 0 }} hari/pengajuan
            </div>
            <div style="font-size: 8px; color: #6b7280; margin-top: 5px;">
                Laporan ini mencakup semua pengajuan izin dan cuti karyawan untuk periode {{ $periode->format('F Y') }}. 
                Data diambil dari sistem manajemen izin dan telah diverifikasi keakuratannya.
            </div>
        </div>

        <!-- Breakdown per Jenis Izin -->
        @if(isset($izin['stats_per_jenis']) && count($izin['stats_per_jenis']) > 0)
        <div style="margin-top: 20px;">
            <div style="font-weight: bold; font-size: 10px; margin-bottom: 10px; padding: 8px; background-color: #fef3c7; border-left: 4px solid #f59e0b;">
                📈 BREAKDOWN PER JENIS IZIN
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th width="30%">Jenis Izin</th>
                        <th width="15%">Total Pengajuan</th>
                        <th width="15%">Disetujui</th>
                        <th width="15%">Ditolak</th>
                        <th width="15%">Menunggu</th>
                        <th width="10%">Total Hari</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($izin['stats_per_jenis'] as $jenis => $stats)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $jenis)) }}</td>
                        <td class="text-center">{{ $stats->sum('total') }}</td>
                        <td class="text-center">{{ $stats->where('status', 'disetujui')->sum('total') }}</td>
                        <td class="text-center">{{ $stats->where('status', 'ditolak')->sum('total') }}</td>
                        <td class="text-center">{{ $stats->where('status', 'menunggu')->sum('total') }}</td>
                        <td class="text-center">{{ $stats->sum('total_hari') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
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
        <div style="margin-top: 40px; padding: 10px; background-color: #f3f4f6; border-radius: 5px; font-size: 8px; color: #6b7280;">
            <strong>Catatan:</strong> Laporan ini khusus memuat data izin dan cuti karyawan untuk periode {{ $periode->format('F Y') }}. 
            Data pengajuan, persetujuan, dan penolakan telah diverifikasi melalui sistem manajemen izin dan dapat dipertanggungjawabkan keakuratannya.
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} | Laporan Izin & Cuti - {{ $periode->format('F Y') }}
    </div>
</body>
</html>
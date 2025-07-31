<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Individual - {{ $user->name }} - {{ $periode->format('F Y') }}</title>
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
        
        .employee-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: table;
            width: 100%;
        }
        
        .employee-row {
            display: table-row;
        }
        
        .employee-label {
            display: table-cell;
            font-weight: bold;
            padding: 3px 15px 3px 0;
            width: 25%;
        }
        
        .employee-value {
            display: table-cell;
            padding: 3px 0;
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
            background-color: #f1f5f9;
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
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
            background-color: #f8f9fa;
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
        
        .status-hadir { background-color: #dcfce7; color: #166534; }
        .status-terlambat { background-color: #fef3c7; color: #92400e; }
        .status-tidak-hadir { background-color: #fee2e2; color: #991b1b; }
        .status-izin { background-color: #dbeafe; color: #1e40af; }
        .status-menunggu { background-color: #fef3c7; color: #92400e; }
        .status-disetujui { background-color: #dcfce7; color: #166534; }
        .status-ditolak { background-color: #fee2e2; color: #991b1b; }
        
        .rating-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            margin-top: 5px;
            display: inline-block;
        }
        
        .rating-excellent { background-color: #dcfce7; color: #166534; }
        .rating-good { background-color: #dbeafe; color: #1e40af; }
        .rating-average { background-color: #fef3c7; color: #92400e; }
        .rating-poor { background-color: #fee2e2; color: #991b1b; }
        
        .trend-indicator {
            font-size: 8px;
            padding: 2px 4px;
            border-radius: 3px;
            margin-left: 5px;
        }
        
        .trend-up { background-color: #dcfce7; color: #166534; }
        .trend-down { background-color: #fee2e2; color: #991b1b; }
        .trend-same { background-color: #f3f4f6; color: #6b7280; }
        
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
            width: 33%;
            vertical-align: top;
            padding-right: 15px;
        }
        
        .signature-center {
            display: table-cell;
            width: 34%;
            vertical-align: top;
            padding: 0 7px;
        }
        
        .signature-right {
            display: table-cell;
            width: 33%;
            vertical-align: top;
            padding-left: 15px;
        }
        
        .signature-box {
            border: 1px solid #ddd;
            padding: 12px;
            background-color: #f9fafb;
            border-radius: 5px;
            min-height: 120px;
            text-align: center;
        }
        
        .signature-title {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 5px;
            color: #374151;
        }
        
        .signature-info {
            font-size: 8px;
            color: #6b7280;
            margin-bottom: 15px;
        }
        
        .signature-space {
            height: 50px;
            border-bottom: 1px solid #333;
            margin-bottom: 8px;
        }
        
        .signature-name {
            font-weight: bold;
            font-size: 8px;
            text-align: center;
        }
        
        .signature-position {
            font-size: 7px;
            color: #6b7280;
            text-align: center;
            margin-top: 2px;
        }
        
        .date-info {
            font-size: 8px;
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
        <div class="report-title">LAPORAN KINERJA INDIVIDUAL KARYAWAN</div>
        <div class="report-period">Periode: {{ $periode->format('F Y') }}</div>
    </div>

    <!-- Informasi Karyawan -->
    <div class="employee-info">
        <div class="employee-row">
            <div class="employee-label">Nama Karyawan:</div>
            <div class="employee-value">{{ $user->name }}</div>
            <div class="employee-label">ID Karyawan:</div>
            <div class="employee-value">{{ $user->id_karyawan }}</div>
        </div>
        <div class="employee-row">
            <div class="employee-label">Email:</div>
            <div class="employee-value">{{ $user->email }}</div>
            <div class="employee-label">No. HP:</div>
            <div class="employee-value">{{ $user->no_hp ?? '-' }}</div>
        </div>
        <div class="employee-row">
            <div class="employee-label">Shift:</div>
            <div class="employee-value">{{ $user->shift ? $user->shift->nama : 'Tidak ada shift' }}</div>
            <div class="employee-label">Tanggal Masuk:</div>
            <div class="employee-value">{{ $user->tanggal_masuk ? $user->tanggal_masuk->format('d/m/Y') : '-' }}</div>
        </div>
    </div>

    <!-- SECTION 1: RINGKASAN KINERJA -->
    <div class="section">
        <div class="section-title">📊 RINGKASAN KINERJA</div>
        
        <!-- Statistik Kinerja -->
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell">
                    <div class="stats-label">HARI KERJA</div>
                    <div class="stats-value">{{ $kinerja['stats']['total_hari_kerja'] }}</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-label">TINGKAT KEHADIRAN</div>
                    <div class="stats-value">{{ $kinerja['stats']['tingkat_kehadiran'] }}%</div>
                    @if(isset($kinerja['trends']['kehadiran']))
                        @php
                            $trend = $kinerja['trends']['kehadiran'];
                            $trendClass = $trend > 0 ? 'trend-up' : ($trend < 0 ? 'trend-down' : 'trend-same');
                            $trendSymbol = $trend > 0 ? '↑' : ($trend < 0 ? '↓' : '→');
                        @endphp
                        <span class="trend-indicator {{ $trendClass }}">
                            {{ $trendSymbol }} {{ abs($trend) }}%
                        </span>
                    @endif
                </div>
                <div class="stats-cell">
                    <div class="stats-label">TOTAL TERLAMBAT</div>
                    <div class="stats-value">{{ $kinerja['stats']['total_terlambat'] }}</div>
                    @if(isset($kinerja['trends']['keterlambatan']))
                        @php
                            $trend = $kinerja['trends']['keterlambatan'];
                            $trendClass = $trend < 0 ? 'trend-up' : ($trend > 0 ? 'trend-down' : 'trend-same');
                            $trendSymbol = $trend < 0 ? '↓' : ($trend > 0 ? '↑' : '→');
                        @endphp
                        <span class="trend-indicator {{ $trendClass }}">
                            {{ $trendSymbol }} {{ abs($trend) }}
                        </span>
                    @endif
                </div>
                <div class="stats-cell">
                    <div class="stats-label">RATING KINERJA</div>
                    <div class="stats-value" style="font-size: 12px;">{{ $kinerja['stats']['rating'] }}</div>
                    @php
                        $ratingClass = match($kinerja['stats']['rating']) {
                            'Excellent' => 'rating-excellent',
                            'Good' => 'rating-good', 
                            'Average' => 'rating-average',
                            'Poor' => 'rating-poor',
                            default => 'rating-average'
                        };
                    @endphp
                    <div class="rating-badge {{ $ratingClass }}">{{ $kinerja['stats']['rating'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: DETAIL ABSENSI -->
    <div class="section">
        <div class="section-title">📅 DETAIL ABSENSI HARIAN</div>
        
        <!-- Statistik Detail -->
        <div class="stats-grid">
            <div class="stats-row">
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
                <div class="stats-cell">
                    <div class="stats-label">RATA² TERLAMBAT</div>
                    <div class="stats-value">{{ $absensi['stats']['rata_rata_menit_terlambat'] ?? 0 }}</div>
                    <div class="stats-label">menit</div>
                </div>
            </div>
        </div>

        <!-- Tabel Absensi Detail -->
        <table class="table">
            <thead>
                <tr>
                    <th width="12%">Tanggal</th>
                    <th width="12%">Shift</th>
                    <th width="12%">Jam Masuk</th>
                    <th width="12%">Jam Keluar</th>
                    <th width="15%">Status</th>
                    <th width="10%">Terlambat</th>
                    <th width="10%">Lembur</th>
                    <th width="17%">Durasi Kerja</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absensi['attendances'] as $att)
                <tr>
                    <td>{{ $att->tanggal_absen->format('d/m/Y') }}</td>
                    <td>{{ $att->shift ? $att->shift->nama : '-' }}</td>
                    <td class="text-center">{{ $att->jam_masuk ? \Carbon\Carbon::parse($att->jam_masuk)->format('H:i') : '-' }}</td>
                    <td class="text-center">{{ $att->jam_keluar ? \Carbon\Carbon::parse($att->jam_keluar)->format('H:i') : '-' }}</td>
                    <td>
                        @php
                            $statusClass = match($att->status_absen) {
                                'hadir' => 'status-hadir',
                                'terlambat' => 'status-terlambat',
                                'tidak_hadir' => 'status-tidak-hadir',
                                'izin' => 'status-izin',
                                default => 'status-tidak-hadir'
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">
                            {{ $att->getStatusAbsenText() }}
                        </span>
                    </td>
                    <td class="text-center">{{ $att->menit_terlambat > 0 ? $att->menit_terlambat . ' mnt' : '-' }}</td>
                    <td class="text-center">{{ $att->menit_lembur > 0 ? $att->menit_lembur . ' mnt' : '-' }}</td>
                    <td class="text-center">{{ $att->getDurasiKerjaFormatted() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data absensi</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- SECTION 3: RIWAYAT IZIN -->
    <div class="section page-break">
        <div class="section-title">📋 RIWAYAT IZIN & CUTI</div>
        
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
                    <div class="stats-label">TOTAL HARI IZIN</div>
                    <div class="stats-value">{{ $izin['stats']['total_hari_izin'] }}</div>
                </div>
            </div>
        </div>

        <!-- Tabel Izin -->
        <table class="table">
            <thead>
                <tr>
                    <th width="12%">Tanggal Pengajuan</th>
                    <th width="18%">Jenis Izin</th>
                    <th width="20%">Periode Izin</th>
                    <th width="8%">Durasi</th>
                    <th width="25%">Alasan</th>
                    <th width="12%">Status</th>
                    <th width="15%">Disetujui Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse($izin['leaveRequests'] as $leave)
                <tr>
                    <td>{{ $leave->created_at->format('d/m/Y') }}</td>
                    <td>{{ $leave->getJenisIzinText() }}</td>
                    <td>{{ $leave->tanggal_mulai->format('d/m/Y') }} - {{ $leave->tanggal_selesai->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $leave->total_hari }} hari</td>
                    <td>{{ Str::limit($leave->alasan, 50) }}</td>
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
                    <td colspan="7" class="text-center">Tidak ada riwayat izin</td>
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
                    <div class="signature-title">Karyawan Yang Bersangkutan</div>
                    <div class="date-info">{{ now()->format('d F Y') }}</div>
                    
                    <div class="signature-space"></div>
                    
                    <div class="signature-name">{{ $user->name }}</div>
                    <div class="signature-position">
                        NIK: {{ $user->id_karyawan }}<br>
                        {{ $user->jabatan ?? 'Karyawan' }}
                    </div>
                </div>
            </div>
            
            <div class="signature-center">
                <div class="signature-box">
                    <div class="signature-title">Supervisor / Atasan Langsung</div>
                    <div class="date-info">{{ now()->format('d F Y') }}</div>
                    
                    <div class="signature-space"></div>
                    
                    <div class="signature-name">_____________________</div>
                    <div class="signature-position">
                        <strong>Nama: ____________________</strong><br>
                        NIK: ____________________<br>
                        Supervisor
                    </div>
                </div>
            </div>
            
            <div class="signature-right">
                <div class="signature-box">
                    <div class="signature-title">Manager HRD</div>
                    <div class="date-info">{{ now()->format('d F Y') }}</div>
                    
                    <div class="signature-space"></div>
                    
                    <div class="signature-name">_____________________</div>
                    <div class="signature-position">
                        <strong>Nama: ____________________</strong><br>
                        NIK: ____________________<br>
                        Manager Human Resource Development
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Performance Summary -->
        <div style="margin-top: 20px; padding: 12px; background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #3b82f6;">
            <div style="font-weight: bold; font-size: 10px; margin-bottom: 8px;">📈 RINGKASAN EVALUASI KINERJA</div>
            <div style="font-size: 9px; color: #4b5563;">
                <strong>Periode Evaluasi:</strong> {{ $periode->format('F Y') }} | 
                <strong>Rating Keseluruhan:</strong> {{ $kinerja['stats']['rating'] }} | 
                <strong>Tingkat Kehadiran:</strong> {{ $kinerja['stats']['tingkat_kehadiran'] }}%
            </div>
            <div style="font-size: 8px; color: #6b7280; margin-top: 5px;">
                Laporan ini mencakup evaluasi kehadiran, keterlambatan, dan penggunaan izin karyawan selama periode tersebut. 
                Data diambil dari sistem absensi digital dan telah diverifikasi keakuratannya.
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} | Laporan Individual: {{ $user->name }} ({{ $user->id_karyawan }}) - {{ $periode->format('F Y') }}
    </div>
</body>
</html>
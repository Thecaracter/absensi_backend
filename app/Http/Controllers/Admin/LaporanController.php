<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Halaman Laporan Utama
     */
    public function index()
    {
        // Stats ringkasan untuk dashboard laporan
        $currentMonth = now();

        $summaryStats = [
            'total_karyawan' => User::karyawan()->aktif()->count(),
            'absensi_bulan_ini' => Attendance::bulanIni()->count(),
            'izin_bulan_ini' => LeaveRequest::bulanIni()->count(),
            'rata_rata_kehadiran' => $this->getAverageAttendanceRate($currentMonth),
        ];

        // Chart data untuk 6 bulan terakhir
        $chartData = $this->getMonthlyChartData();

        // Data untuk dropdown filter
        $karyawan = User::karyawan()->aktif()->get();
        $shifts = Shift::aktif()->get();

        return view('admin.laporan', compact('summaryStats', 'chartData', 'karyawan', 'shifts'));
    }

    /**
     * Get dashboard stats untuk AJAX request
     */
    public function getDashboardStats(Request $request)
    {
        $currentMonth = now();

        $stats = [
            'total_karyawan' => User::karyawan()->aktif()->count(),
            'absensi_bulan_ini' => Attendance::bulanIni()->count(),
            'izin_bulan_ini' => LeaveRequest::bulanIni()->count(),
            'rata_rata_kehadiran' => $this->getAverageAttendanceRate($currentMonth),
        ];

        $chartData = $this->getMonthlyChartData();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'chartData' => $chartData
        ]);
    }

    /**
     * Laporan Absensi
     */
    public function absensi(Request $request)
    {
        // Default bulan ini
        $bulan = $request->bulan ?? now()->format('Y-m');
        $periode = Carbon::parse($bulan);

        // Filter parameters
        $userId = $request->user_id;
        $shiftId = $request->shift_id;

        // Base query
        $query = Attendance::with(['user', 'shift'])
            ->whereMonth('tanggal_absen', $periode->month)
            ->whereYear('tanggal_absen', $periode->year);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($shiftId) {
            $query->where('shift_id', $shiftId);
        }

        // Statistik
        $stats = [
            'total_hari_kerja' => $this->getWorkingDaysInMonth($periode),
            'total_absensi' => $query->count(),
            'total_hadir' => $query->clone()->hadir()->count(),
            'total_terlambat' => $query->clone()->terlambat()->count(),
            'total_tidak_hadir' => $query->clone()->tidakHadir()->count(),
            'total_izin' => $query->clone()->where('status_absen', 'izin')->count(),
        ];

        // Data untuk filter
        $karyawan = User::karyawan()->aktif()->get();
        $shifts = Shift::aktif()->get();

        // Jika AJAX request
        if ($request->ajax()) {
            $absensi = $query->orderBy('tanggal_absen', 'desc')->take(50)->get();

            // Stats per shift
            $statsPerShift = Attendance::whereMonth('tanggal_absen', $periode->month)
                ->whereYear('tanggal_absen', $periode->year)
                ->when($userId, function ($q) use ($userId) {
                    return $q->where('user_id', $userId);
                })
                ->with('shift')
                ->get()
                ->groupBy('shift.nama')
                ->map(function ($group) {
                    return [
                        'total' => $group->count(),
                        'hadir' => $group->where('status_absen', 'hadir')->count(),
                        'terlambat' => $group->where('status_absen', 'terlambat')->count(),
                        'tidak_hadir' => $group->where('status_absen', 'tidak_hadir')->count(),
                        'izin' => $group->where('status_absen', 'izin')->count(),
                    ];
                });

            // Top terlambat
            $topTerlambat = User::karyawan()
                ->withCount([
                    'attendances as total_terlambat' => function ($q) use ($periode) {
                        $q->whereMonth('tanggal_absen', $periode->month)
                            ->whereYear('tanggal_absen', $periode->year)
                            ->where('status_absen', 'terlambat');
                    }
                ])
                ->having('total_terlambat', '>', 0)
                ->orderBy('total_terlambat', 'desc')
                ->take(10)
                ->get();

            return response()->json([
                'stats' => $stats,
                'statsPerShift' => $statsPerShift,
                'topTerlambat' => $topTerlambat,
                'absensi' => $absensi->map(function ($att) {
                    return [
                        'id' => $att->id,
                        'tanggal' => $att->tanggal_absen->format('d/m/Y'),
                        'karyawan' => $att->user->name,
                        'id_karyawan' => $att->user->id_karyawan,
                        'shift' => $att->shift->nama,
                        'jam_masuk' => $att->jam_masuk ? Carbon::parse($att->jam_masuk)->format('H:i') : null,
                        'jam_keluar' => $att->jam_keluar ? Carbon::parse($att->jam_keluar)->format('H:i') : null,
                        'status' => $att->getStatusAbsenText(),
                        'status_badge_class' => $this->getStatusBadgeClass($att->status_absen),
                        'menit_terlambat' => $att->menit_terlambat,
                        'durasi_kerja' => $att->getDurasiKerjaFormatted(),
                    ];
                }),
            ]);
        }

        // For regular page load
        $absensi = $query->orderBy('tanggal_absen', 'desc')->paginate(20);

        return view('admin.laporan.absensi', compact(
            'absensi',
            'stats',
            'karyawan',
            'shifts',
            'bulan'
        ));
    }

    /**
     * Get detailed attendance data for AJAX modal
     */
    public function getAbsensiDetail(Request $request)
    {
        $request->validate([
            'periode' => 'required|date_format:Y-m',
            'user_id' => 'nullable|exists:users,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'status' => 'nullable|in:hadir,terlambat,tidak_hadir,izin',
        ]);

        $periode = Carbon::parse($request->periode);

        $query = Attendance::with(['user', 'shift'])
            ->whereMonth('tanggal_absen', $periode->month)
            ->whereYear('tanggal_absen', $periode->year);

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->shift_id) {
            $query->where('shift_id', $request->shift_id);
        }

        if ($request->status) {
            $query->where('status_absen', $request->status);
        }

        $absensi = $query->orderBy('tanggal_absen', 'desc')->get()->map(function ($att) {
            return [
                'id' => $att->id,
                'tanggal' => $att->tanggal_absen->format('d/m/Y'),
                'karyawan' => $att->user->name,
                'id_karyawan' => $att->user->id_karyawan,
                'shift' => $att->shift->nama,
                'jam_masuk' => $att->jam_masuk ? Carbon::parse($att->jam_masuk)->format('H:i') : null,
                'jam_keluar' => $att->jam_keluar ? Carbon::parse($att->jam_keluar)->format('H:i') : null,
                'status' => $att->getStatusAbsenText(),
                'status_badge_class' => $this->getStatusBadgeClass($att->status_absen),
                'menit_terlambat' => $att->menit_terlambat,
                'durasi_kerja' => $att->getDurasiKerjaFormatted(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $absensi,
            'periode_text' => $periode->format('F Y'),
        ]);
    }

    /**
     * Laporan Izin/Cuti
     */
    public function izin(Request $request)
    {
        // Default bulan ini
        $bulan = $request->bulan ?? now()->format('Y-m');
        $periode = Carbon::parse($bulan);

        // Filter parameters
        $userId = $request->user_id;
        $jenisIzin = $request->jenis_izin;
        $status = $request->status;

        // Base query
        $query = LeaveRequest::with(['user', 'approver'])
            ->whereMonth('tanggal_mulai', $periode->month)
            ->whereYear('tanggal_mulai', $periode->year);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($jenisIzin) {
            $query->where('jenis_izin', $jenisIzin);
        }

        if ($status) {
            $query->where('status', $status);
        }

        // Statistik
        $stats = [
            'total_pengajuan' => $query->count(),
            'disetujui' => $query->clone()->disetujui()->count(),
            'ditolak' => $query->clone()->ditolak()->count(),
            'menunggu' => $query->clone()->menunggu()->count(),
            'total_hari_izin' => $query->clone()->disetujui()->sum('total_hari'),
        ];

        // Data untuk filter
        $karyawan = User::karyawan()->aktif()->get();

        // Jika AJAX request
        if ($request->ajax()) {
            $izin = $query->orderBy('created_at', 'desc')->take(50)->get();

            // Stats per jenis
            $statsPerJenis = LeaveRequest::whereMonth('tanggal_mulai', $periode->month)
                ->whereYear('tanggal_mulai', $periode->year)
                ->when($userId, function ($q) use ($userId) {
                    return $q->where('user_id', $userId);
                })
                ->selectRaw('jenis_izin, status, COUNT(*) as total, SUM(total_hari) as total_hari')
                ->groupBy('jenis_izin', 'status')
                ->get()
                ->groupBy('jenis_izin');

            // Top izin
            $topIzin = User::karyawan()
                ->withCount([
                    'leaveRequests as total_izin' => function ($q) use ($periode) {
                        $q->whereMonth('tanggal_mulai', $periode->month)
                            ->whereYear('tanggal_mulai', $periode->year)
                            ->where('status', 'disetujui');
                    }
                ])
                ->having('total_izin', '>', 0)
                ->orderBy('total_izin', 'desc')
                ->take(10)
                ->get();

            return response()->json([
                'stats' => $stats,
                'statsPerJenis' => $statsPerJenis,
                'topIzin' => $topIzin,
                'izin' => $izin->map(function ($leave) {
                    return [
                        'id' => $leave->id,
                        'tanggal_pengajuan' => $leave->created_at->format('d/m/Y'),
                        'karyawan' => $leave->user->name,
                        'id_karyawan' => $leave->user->id_karyawan,
                        'jenis_izin' => $leave->getJenisIzinText(),
                        'tanggal_mulai' => $leave->tanggal_mulai->format('d/m/Y'),
                        'tanggal_selesai' => $leave->tanggal_selesai->format('d/m/Y'),
                        'total_hari' => $leave->total_hari,
                        'durasi_text' => $leave->getDurasiText(),
                        'alasan' => $leave->alasan,
                        'status' => $leave->getStatusText(),
                        'status_badge_class' => $this->getStatusBadgeClass($leave->status),
                        'approver' => $leave->approver ? $leave->approver->name : null,
                        'tanggal_persetujuan' => $leave->tanggal_persetujuan ? $leave->tanggal_persetujuan->format('d/m/Y H:i') : null,
                    ];
                }),
            ]);
        }

        // For regular page load
        $izin = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.laporan.izin', compact(
            'izin',
            'stats',
            'karyawan',
            'bulan'
        ));
    }

    /**
     * Get detailed leave data for AJAX modal
     */
    public function getIzinDetail(Request $request)
    {
        $request->validate([
            'periode' => 'required|date_format:Y-m',
            'user_id' => 'nullable|exists:users,id',
            'jenis_izin' => 'nullable|in:sakit,cuti_tahunan,keperluan_pribadi,darurat,lainnya',
            'status' => 'nullable|in:menunggu,disetujui,ditolak',
        ]);

        $periode = Carbon::parse($request->periode);

        $query = LeaveRequest::with(['user', 'approver'])
            ->whereMonth('tanggal_mulai', $periode->month)
            ->whereYear('tanggal_mulai', $periode->year);

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->jenis_izin) {
            $query->where('jenis_izin', $request->jenis_izin);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $izin = $query->orderBy('created_at', 'desc')->get()->map(function ($leave) {
            return [
                'id' => $leave->id,
                'tanggal_pengajuan' => $leave->created_at->format('d/m/Y'),
                'karyawan' => $leave->user->name,
                'id_karyawan' => $leave->user->id_karyawan,
                'jenis_izin' => $leave->getJenisIzinText(),
                'tanggal_mulai' => $leave->tanggal_mulai->format('d/m/Y'),
                'tanggal_selesai' => $leave->tanggal_selesai->format('d/m/Y'),
                'total_hari' => $leave->total_hari,
                'durasi_text' => $leave->getDurasiText(),
                'alasan' => $leave->alasan,
                'status' => $leave->getStatusText(),
                'status_badge_class' => $this->getStatusBadgeClass($leave->status),
                'approver' => $leave->approver ? $leave->approver->name : null,
                'tanggal_persetujuan' => $leave->tanggal_persetujuan ? $leave->tanggal_persetujuan->format('d/m/Y H:i') : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $izin,
            'periode_text' => $periode->format('F Y'),
        ]);
    }

    /**
     * Laporan Kinerja Karyawan
     */
    public function kinerja(Request $request)
    {
        // Default bulan ini
        $bulan = $request->bulan ?? now()->format('Y-m');
        $periode = Carbon::parse($bulan);

        // Get karyawan dengan statistik
        $karyawan = User::karyawan()->aktif()
            ->with('shift')
            ->withCount([
                'attendances as total_hadir' => function ($q) use ($periode) {
                    $q->whereMonth('tanggal_absen', $periode->month)
                        ->whereYear('tanggal_absen', $periode->year)
                        ->hadir();
                },
                'attendances as total_terlambat' => function ($q) use ($periode) {
                    $q->whereMonth('tanggal_absen', $periode->month)
                        ->whereYear('tanggal_absen', $periode->year)
                        ->terlambat();
                },
                'attendances as total_tidak_hadir' => function ($q) use ($periode) {
                    $q->whereMonth('tanggal_absen', $periode->month)
                        ->whereYear('tanggal_absen', $periode->year)
                        ->tidakHadir();
                },
                'leaveRequests as total_izin' => function ($q) use ($periode) {
                    $q->whereMonth('tanggal_mulai', $periode->month)
                        ->whereYear('tanggal_mulai', $periode->year)
                        ->disetujui();
                }
            ])
            ->get()
            ->map(function ($k) use ($periode) {
                $totalHariKerja = $this->getWorkingDaysInMonth($periode);
                $tingkatKehadiran = $totalHariKerja > 0 ?
                    round(($k->total_hadir / $totalHariKerja) * 100, 2) : 0;

                $k->tingkat_kehadiran = $tingkatKehadiran;
                $k->total_hari_kerja = $totalHariKerja;

                // Rating system
                if ($tingkatKehadiran >= 95 && $k->total_terlambat <= 2) {
                    $k->rating = 'Excellent';
                    $k->rating_class = 'success';
                } elseif ($tingkatKehadiran >= 85 && $k->total_terlambat <= 5) {
                    $k->rating = 'Good';
                    $k->rating_class = 'primary';
                } elseif ($tingkatKehadiran >= 75) {
                    $k->rating = 'Average';
                    $k->rating_class = 'warning';
                } else {
                    $k->rating = 'Poor';
                    $k->rating_class = 'danger';
                }

                return $k;
            })
            ->sortByDesc('tingkat_kehadiran');

        // Overall stats
        $overallStats = [
            'rata_rata_kehadiran' => round($karyawan->avg('tingkat_kehadiran'), 2),
            'karyawan_excellent' => $karyawan->where('rating', 'Excellent')->count(),
            'karyawan_good' => $karyawan->where('rating', 'Good')->count(),
            'karyawan_average' => $karyawan->where('rating', 'Average')->count(),
            'karyawan_poor' => $karyawan->where('rating', 'Poor')->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'karyawan' => $karyawan->values(),
                'overallStats' => $overallStats,
                'periode_text' => $periode->format('F Y'),
            ]);
        }

        return view('admin.laporan.kinerja', compact(
            'karyawan',
            'overallStats',
            'bulan'
        ));
    }

    /**
     * Get detailed performance data for AJAX modal
     */
    public function getKinerjaDetail(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'periode' => 'required|date_format:Y-m',
        ]);

        $user = User::findOrFail($request->user_id);
        $periode = Carbon::parse($request->periode);

        // Get detailed attendance for the month
        $absensi = $user->attendances()
            ->with('shift')
            ->whereMonth('tanggal_absen', $periode->month)
            ->whereYear('tanggal_absen', $periode->year)
            ->orderBy('tanggal_absen', 'desc')
            ->get();

        // Calculate detailed stats
        $stats = [
            'total_hari_kerja' => $this->getWorkingDaysInMonth($periode),
            'total_hadir' => $absensi->whereIn('status_absen', ['hadir', 'terlambat'])->count(),
            'total_terlambat' => $absensi->where('status_absen', 'terlambat')->count(),
            'total_tidak_hadir' => $absensi->where('status_absen', 'tidak_hadir')->count(),
            'total_izin' => $absensi->where('status_absen', 'izin')->count(),
            'total_menit_terlambat' => $absensi->sum('menit_terlambat'),
            'total_menit_lembur' => $absensi->sum('menit_lembur'),
        ];

        $stats['tingkat_kehadiran'] = $stats['total_hari_kerja'] > 0 ?
            round(($stats['total_hadir'] / $stats['total_hari_kerja']) * 100, 2) : 0;

        // Get leave requests for the month
        $izin = $user->leaveRequests()
            ->whereMonth('tanggal_mulai', $periode->month)
            ->whereYear('tanggal_mulai', $periode->year)
            ->get();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'id_karyawan' => $user->id_karyawan,
                'shift' => $user->shift ? $user->shift->nama : null,
                'foto_url' => $user->foto_url,
            ],
            'stats' => $stats,
            'absensi' => $absensi->map(function ($att) {
                return [
                    'tanggal' => $att->tanggal_absen->format('d/m/Y'),
                    'jam_masuk' => $att->jam_masuk ? Carbon::parse($att->jam_masuk)->format('H:i') : null,
                    'jam_keluar' => $att->jam_keluar ? Carbon::parse($att->jam_keluar)->format('H:i') : null,
                    'status' => $att->getStatusAbsenText(),
                    'menit_terlambat' => $att->menit_terlambat,
                    'durasi_kerja' => $att->getDurasiKerjaFormatted(),
                ];
            }),
            'izin' => $izin->map(function ($leave) {
                return [
                    'jenis_izin' => $leave->getJenisIzinText(),
                    'tanggal_mulai' => $leave->tanggal_mulai->format('d/m/Y'),
                    'tanggal_selesai' => $leave->tanggal_selesai->format('d/m/Y'),
                    'total_hari' => $leave->total_hari,
                    'status' => $leave->getStatusText(),
                ];
            }),
            'periode_text' => $periode->format('F Y'),
        ]);
    }

    /**
     * Export Laporan
     */
    public function export(Request $request)
    {
        $request->validate([
            'jenis_laporan' => 'required|in:absensi,izin,kinerja',
            'periode' => 'required|date_format:Y-m',
            'format' => 'required|in:csv,excel,pdf',
            'user_id' => 'nullable|exists:users,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'status' => 'nullable|string',
        ]);

        switch ($request->jenis_laporan) {
            case 'absensi':
                return $this->exportAbsensi($request);
            case 'izin':
                return $this->exportIzin($request);
            case 'kinerja':
                return $this->exportKinerja($request);
        }
    }

    /**
     * Export laporan absensi
     */
    public function exportAbsensi(Request $request)
    {
        $periode = Carbon::parse($request->periode ?? now()->format('Y-m'));

        $query = Attendance::with(['user', 'shift'])
            ->whereMonth('tanggal_absen', $periode->month)
            ->whereYear('tanggal_absen', $periode->year);

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->shift_id) {
            $query->where('shift_id', $request->shift_id);
        }

        if ($request->status) {
            $query->where('status_absen', $request->status);
        }

        $absensi = $query->orderBy('tanggal_absen', 'desc')->get();

        return $this->exportToCsv($absensi, 'absensi', $periode);
    }

    /**
     * Export laporan izin
     */
    public function exportIzin(Request $request)
    {
        $periode = Carbon::parse($request->periode ?? now()->format('Y-m'));

        $query = LeaveRequest::with(['user', 'approver'])
            ->whereMonth('tanggal_mulai', $periode->month)
            ->whereYear('tanggal_mulai', $periode->year);

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $izin = $query->orderBy('created_at', 'desc')->get();

        return $this->exportIzinToCsv($izin, $periode);
    }

    /**
     * Export laporan kinerja
     */
    public function exportKinerja(Request $request)
    {
        $periode = Carbon::parse($request->periode ?? now()->format('Y-m'));

        $karyawan = User::karyawan()->aktif()
            ->with('shift')
            ->withCount([
                'attendances as total_hadir' => function ($q) use ($periode) {
                    $q->whereMonth('tanggal_absen', $periode->month)
                        ->whereYear('tanggal_absen', $periode->year)
                        ->hadir();
                },
                'attendances as total_terlambat' => function ($q) use ($periode) {
                    $q->whereMonth('tanggal_absen', $periode->month)
                        ->whereYear('tanggal_absen', $periode->year)
                        ->terlambat();
                },
                'attendances as total_tidak_hadir' => function ($q) use ($periode) {
                    $q->whereMonth('tanggal_absen', $periode->month)
                        ->whereYear('tanggal_absen', $periode->year)
                        ->tidakHadir();
                },
            ])
            ->get();

        return $this->exportKinerjaToCsv($karyawan, $periode);
    }

    /**
     * Export to CSV format
     */
    private function exportToCsv($data, $type, $periode)
    {
        $filename = 'laporan_' . $type . '_' . $periode->format('Y_m') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Header CSV untuk absensi
            fputcsv($file, [
                'Tanggal',
                'ID Karyawan',
                'Nama Karyawan',
                'Shift',
                'Jam Masuk',
                'Jam Keluar',
                'Status Absen',
                'Menit Terlambat',
                'Menit Lembur',
                'Durasi Kerja',
                'Catatan Admin'
            ]);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->tanggal_absen->format('Y-m-d'),
                    $row->user->id_karyawan,
                    $row->user->name,
                    $row->shift->nama,
                    $row->jam_masuk ? Carbon::parse($row->jam_masuk)->format('H:i') : '',
                    $row->jam_keluar ? Carbon::parse($row->jam_keluar)->format('H:i') : '',
                    $row->getStatusAbsenText(),
                    $row->menit_terlambat,
                    $row->menit_lembur,
                    $row->getDurasiKerjaFormatted(),
                    $row->catatan_admin
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export izin to CSV
     */
    private function exportIzinToCsv($data, $periode)
    {
        $filename = 'laporan_izin_' . $periode->format('Y_m') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Tanggal Pengajuan',
                'ID Karyawan',
                'Nama Karyawan',
                'Jenis Izin',
                'Tanggal Mulai',
                'Tanggal Selesai',
                'Total Hari',
                'Alasan',
                'Status',
                'Disetujui Oleh',
                'Tanggal Persetujuan'
            ]);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->created_at->format('Y-m-d H:i:s'),
                    $row->user->id_karyawan,
                    $row->user->name,
                    $row->getJenisIzinText(),
                    $row->tanggal_mulai->format('Y-m-d'),
                    $row->tanggal_selesai->format('Y-m-d'),
                    $row->total_hari,
                    $row->alasan,
                    $row->getStatusText(),
                    $row->approver ? $row->approver->name : '',
                    $row->tanggal_persetujuan ? $row->tanggal_persetujuan->format('Y-m-d H:i:s') : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export kinerja to CSV
     */
    private function exportKinerjaToCsv($data, $periode)
    {
        $filename = 'laporan_kinerja_' . $periode->format('Y_m') . '.csv';
        $totalHariKerja = $this->getWorkingDaysInMonth($periode);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data, $totalHariKerja) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID Karyawan',
                'Nama Karyawan',
                'Shift',
                'Total Hari Kerja',
                'Total Hadir',
                'Total Terlambat',
                'Total Tidak Hadir',
                'Tingkat Kehadiran (%)',
                'Rating'
            ]);

            foreach ($data as $row) {
                $tingkatKehadiran = $totalHariKerja > 0 ?
                    round(($row->total_hadir / $totalHariKerja) * 100, 2) : 0;

                if ($tingkatKehadiran >= 95 && $row->total_terlambat <= 2) {
                    $rating = 'Excellent';
                } elseif ($tingkatKehadiran >= 85 && $row->total_terlambat <= 5) {
                    $rating = 'Good';
                } elseif ($tingkatKehadiran >= 75) {
                    $rating = 'Average';
                } else {
                    $rating = 'Poor';
                }

                fputcsv($file, [
                    $row->id_karyawan,
                    $row->name,
                    $row->shift ? $row->shift->nama : '',
                    $totalHariKerja,
                    $row->total_hadir,
                    $row->total_terlambat,
                    $row->total_tidak_hadir,
                    $tingkatKehadiran,
                    $rating
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get monthly chart data for dashboard
     */
    private function getMonthlyChartData()
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $absensiCount = Attendance::whereMonth('tanggal_absen', $month->month)
                ->whereYear('tanggal_absen', $month->year)
                ->hadir()
                ->count();

            $izinCount = LeaveRequest::whereMonth('tanggal_mulai', $month->month)
                ->whereYear('tanggal_mulai', $month->year)
                ->disetujui()
                ->count();

            $months[] = [
                'month' => $month->format('M Y'),
                'absensi' => $absensiCount,
                'izin' => $izinCount,
            ];
        }

        return $months;
    }

    /**
     * Get average attendance rate for current month
     */
    private function getAverageAttendanceRate($periode)
    {
        $totalHariKerja = $this->getWorkingDaysInMonth($periode);
        $totalKaryawan = User::karyawan()->aktif()->count();

        if ($totalHariKerja === 0 || $totalKaryawan === 0) {
            return 0;
        }

        $totalAbsensi = Attendance::whereMonth('tanggal_absen', $periode->month)
            ->whereYear('tanggal_absen', $periode->year)
            ->hadir()
            ->count();

        $expectedAttendance = $totalHariKerja * $totalKaryawan;

        return round(($totalAbsensi / $expectedAttendance) * 100, 2);
    }

    /**
     * Hitung hari kerja dalam bulan (exclude weekend)
     */
    private function getWorkingDaysInMonth($periode)
    {
        $startOfMonth = $periode->copy()->startOfMonth();
        $endOfMonth = $periode->copy()->endOfMonth();

        $workingDays = 0;
        $currentDate = $startOfMonth->copy();

        while ($currentDate <= $endOfMonth) {
            // Skip weekends (Saturday & Sunday)
            if (!$currentDate->isWeekend()) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        return $workingDays;
    }

    /**
     * Get CSS class for status badge
     */
    private function getStatusBadgeClass($status)
    {
        return match ($status) {
            'hadir' => 'bg-green-100 text-green-800',
            'terlambat' => 'bg-yellow-100 text-yellow-800',
            'tidak_hadir' => 'bg-red-100 text-red-800',
            'izin' => 'bg-blue-100 text-blue-800',
            'menunggu' => 'bg-yellow-100 text-yellow-800',
            'disetujui' => 'bg-green-100 text-green-800',
            'ditolak' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
}
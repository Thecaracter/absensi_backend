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
     * Halaman Laporan Utama - SATU VIEW SAJA
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

        // Initialize empty data for tabs (will be loaded via AJAX)
        $absensi = collect();
        $izin = collect();
        $karyawanKinerja = collect();

        $overallStats = [
            'rata_rata_kehadiran' => 0,
            'karyawan_excellent' => 0,
            'karyawan_good' => 0,
            'karyawan_average' => 0,
            'karyawan_poor' => 0,
        ];

        // Empty stats for tabs
        $stats = [
            'total_hari_kerja' => 0,
            'total_hadir' => 0,
            'total_terlambat' => 0,
            'total_tidak_hadir' => 0,
            'total_izin' => 0,
            'total_pengajuan' => 0,
            'disetujui' => 0,
            'ditolak' => 0,
            'menunggu' => 0,
            'total_hari_izin' => 0,
        ];

        return view('admin.laporan', compact(
            'summaryStats',
            'chartData',
            'karyawan',
            'shifts',
            'absensi',
            'izin',
            'karyawanKinerja',
            'overallStats',
            'stats'
        ));
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
     * Laporan Absensi - HANYA AJAX/JSON (RINGKASAN PER KARYAWAN)
     */
    public function absensi(Request $request)
    {
        // Default bulan ini
        $bulan = $request->bulan ?? now()->format('Y-m');
        $periode = Carbon::parse($bulan);

        // Filter parameters
        $userId = $request->user_id;
        $shiftId = $request->shift_id;

        // Base query untuk karyawan
        $query = User::karyawan()->aktif()->with('shift');

        if ($userId) {
            $query->where('id', $userId);
        }

        if ($shiftId) {
            $query->where('shift_id', $shiftId);
        }

        // RINGKASAN PER KARYAWAN dengan statistik absensi
        $absensi = $query->withCount([
            'attendances as total_hadir' => function ($q) use ($periode) {
                $q->whereMonth('tanggal_absen', $periode->month)
                    ->whereYear('tanggal_absen', $periode->year)
                    ->whereIn('status_absen', ['hadir', 'terlambat']);
            },
            'attendances as total_terlambat' => function ($q) use ($periode) {
                $q->whereMonth('tanggal_absen', $periode->month)
                    ->whereYear('tanggal_absen', $periode->year)
                    ->where('status_absen', 'terlambat');
            },
            'attendances as total_tidak_hadir' => function ($q) use ($periode) {
                $q->whereMonth('tanggal_absen', $periode->month)
                    ->whereYear('tanggal_absen', $periode->year)
                    ->where('status_absen', 'tidak_hadir');
            },
            'attendances as total_izin' => function ($q) use ($periode) {
                $q->whereMonth('tanggal_absen', $periode->month)
                    ->whereYear('tanggal_absen', $periode->year)
                    ->where('status_absen', 'izin');
            },
            'leaveRequests as total_pengajuan_izin' => function ($q) use ($periode) {
                $q->whereMonth('tanggal_mulai', $periode->month)
                    ->whereYear('tanggal_mulai', $periode->year);
            }
        ])->get();

        // Statistik TOTAL
        $totalQuery = Attendance::whereMonth('tanggal_absen', $periode->month)
            ->whereYear('tanggal_absen', $periode->year);

        if ($userId) {
            $totalQuery->where('user_id', $userId);
        }

        if ($shiftId) {
            $totalQuery->where('shift_id', $shiftId);
        }

        $stats = [
            'total_hari_kerja' => $this->getWorkingDaysInMonth($periode),
            'total_absensi' => $totalQuery->count(),
            'total_hadir' => $totalQuery->clone()->hadir()->count(),
            'total_terlambat' => $totalQuery->clone()->terlambat()->count(),
            'total_tidak_hadir' => $totalQuery->clone()->tidakHadir()->count(),
            'total_izin' => $totalQuery->clone()->where('status_absen', 'izin')->count(),
        ];

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

        // HANYA RETURN JSON - RINGKASAN PER KARYAWAN
        return response()->json([
            'stats' => $stats,
            'statsPerShift' => $statsPerShift,
            'topTerlambat' => $topTerlambat,
            'absensi' => $absensi->map(function ($emp) use ($periode) {
                $totalHariKerja = $this->getWorkingDaysInMonth($periode);
                $tingkatKehadiran = $totalHariKerja > 0 ?
                    round(($emp->total_hadir / $totalHariKerja) * 100, 2) : 0;

                return [
                    'id' => $emp->id,
                    'id_karyawan' => $emp->id_karyawan,
                    'karyawan' => $emp->name,
                    'shift' => $emp->shift ? $emp->shift->nama : '-',
                    'total_hari_kerja' => $totalHariKerja,
                    'total_hadir' => $emp->total_hadir,
                    'total_terlambat' => $emp->total_terlambat,
                    'total_tidak_hadir' => $emp->total_tidak_hadir,
                    'total_izin' => $emp->total_izin,
                    'total_pengajuan_izin' => $emp->total_pengajuan_izin,
                    'tingkat_kehadiran' => $tingkatKehadiran,
                    'foto_url' => $emp->foto_url,
                ];
            }),
        ]);
    }

    /**
     * Laporan Izin/Cuti - HANYA AJAX/JSON
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

        // Ambil data izin
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
            ->with([
                'leaveRequests' => function ($q) use ($periode) {
                    $q->whereMonth('tanggal_mulai', $periode->month)
                        ->whereYear('tanggal_mulai', $periode->year)
                        ->where('status', 'disetujui');
                }
            ])
            ->having('total_izin', '>', 0)
            ->orderBy('total_izin', 'desc')
            ->take(10)
            ->get();

        // HANYA RETURN JSON - TIDAK ADA VIEW
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

    /**
     * Laporan Kinerja - AJAX/JSON untuk analisis performa karyawan
     */
    public function kinerja(Request $request)
    {
        // Default bulan ini
        $bulan = $request->bulan ?? now()->format('Y-m');
        $periode = Carbon::parse($bulan);

        // Get all active employees with performance metrics
        $karyawan = User::karyawan()->aktif()->with('shift')
            ->withCount([
                'attendances as total_hadir' => function ($q) use ($periode) {
                    $q->whereMonth('tanggal_absen', $periode->month)
                        ->whereYear('tanggal_absen', $periode->year)
                        ->whereIn('status_absen', ['hadir', 'terlambat']);
                },
                'attendances as total_terlambat' => function ($q) use ($periode) {
                    $q->whereMonth('tanggal_absen', $periode->month)
                        ->whereYear('tanggal_absen', $periode->year)
                        ->where('status_absen', 'terlambat');
                },
                'attendances as total_tidak_hadir' => function ($q) use ($periode) {
                    $q->whereMonth('tanggal_absen', $periode->month)
                        ->whereYear('tanggal_absen', $periode->year)
                        ->where('status_absen', 'tidak_hadir');
                },
                'attendances as total_izin' => function ($q) use ($periode) {
                    $q->whereMonth('tanggal_absen', $periode->month)
                        ->whereYear('tanggal_absen', $periode->year)
                        ->where('status_absen', 'izin');
                }
            ])
            ->get()
            ->map(function ($emp) use ($periode) {
                $totalHariKerja = $this->getWorkingDaysInMonth($periode);
                $tingkatKehadiran = $totalHariKerja > 0 ?
                    round(($emp->total_hadir / $totalHariKerja) * 100, 2) : 0;

                // Rating system
                if ($tingkatKehadiran >= 95 && $emp->total_terlambat <= 2) {
                    $rating = 'Excellent';
                } elseif ($tingkatKehadiran >= 85 && $emp->total_terlambat <= 5) {
                    $rating = 'Good';
                } elseif ($tingkatKehadiran >= 75) {
                    $rating = 'Average';
                } else {
                    $rating = 'Poor';
                }

                return [
                    'id' => $emp->id,
                    'name' => $emp->name,
                    'id_karyawan' => $emp->id_karyawan,
                    'shift' => $emp->shift ? $emp->shift->nama : '-',
                    'total_hari_kerja' => $totalHariKerja,
                    'total_hadir' => $emp->total_hadir,
                    'total_terlambat' => $emp->total_terlambat,
                    'total_tidak_hadir' => $emp->total_tidak_hadir,
                    'total_izin' => $emp->total_izin,
                    'tingkat_kehadiran' => $tingkatKehadiran,
                    'rating' => $rating,
                ];
            });

        // Overall statistics
        $overallStats = [
            'rata_rata_kehadiran' => $karyawan->avg('tingkat_kehadiran'),
            'karyawan_excellent' => $karyawan->where('rating', 'Excellent')->count(),
            'karyawan_good' => $karyawan->where('rating', 'Good')->count(),
            'karyawan_average' => $karyawan->where('rating', 'Average')->count(),
            'karyawan_poor' => $karyawan->where('rating', 'Poor')->count(),
        ];

        return response()->json([
            'overallStats' => $overallStats,
            'karyawan' => $karyawan
        ]);
    }

    /**
     * Export Laporan - DIPERLUAS JADI 4 JENIS
     */
    public function export(Request $request)
    {
        $request->validate([
            'jenis_laporan' => 'required|in:semua,individual,absensi_only,izin_only',
            'periode' => 'required|date_format:Y-m',
            'format' => 'required|in:pdf',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $periode = Carbon::parse($request->periode);

        switch ($request->jenis_laporan) {
            case 'semua':
                return $this->exportSemuaPdf($request, $periode);
            case 'individual':
                return $this->exportIndividualPdf($request, $periode);
            case 'absensi_only':
                return $this->exportAbsensiOnlyPdf($request, $periode);
            case 'izin_only':
                return $this->exportIzinOnlyPdf($request, $periode);
        }
    }

    /**
     * Export Laporan SEMUA (Absensi + Izin) HTML
     */
    public function exportSemuaPdf(Request $request, Carbon $periode)
    {
        // Get Absensi Data
        $absensiData = $this->getAbsensiDataForExport($request, $periode);

        // Get Izin Data  
        $izinData = $this->getIzinDataForExport($request, $periode);

        return view('print.laporan-semua', [
            'periode' => $periode,
            'absensi' => $absensiData,
            'izin' => $izinData,
            'company' => [
                'name' => 'PT. Inna Pharmaceutical Industry',
                'address' => 'Jl. Barokah No.1 1, RT.1/RW.8, Wanaherang, Kec. Gn. Putri, Kabupaten Bogor, Jawa Barat 16965',
                'phone' => '(021) 1234-5678',
                'email' => 'info@company.com'
            ]
        ]);
    }

    /**
     * Export HANYA Laporan Absensi
     */
    public function exportAbsensiOnlyPdf(Request $request, Carbon $periode)
    {
        // Get Absensi Data
        $absensiData = $this->getAbsensiDataForExport($request, $periode);

        return view('print.laporan-absensi-only', [
            'periode' => $periode,
            'absensi' => $absensiData,
            'company' => [
                'name' => 'PT. Inna Pharmaceutical Industry',
                'address' => 'Jl. Barokah No.1 1, RT.1/RW.8, Wanaherang, Kec. Gn. Putri, Kabupaten Bogor, Jawa Barat 16965',
                'phone' => '(021) 1234-5678',
                'email' => 'info@company.com'
            ]
        ]);
    }

    /**
     * Export HANYA Laporan Izin
     */
    public function exportIzinOnlyPdf(Request $request, Carbon $periode)
    {
        // Get Izin Data  
        $izinData = $this->getIzinDataForExport($request, $periode);

        return view('print.laporan-izin-only', [
            'periode' => $periode,
            'izin' => $izinData,
            'company' => [
                'name' => 'PT. Inna Pharmaceutical Industry',
                'address' => 'Jl. Barokah No.1 1, RT.1/RW.8, Wanaherang, Kec. Gn. Putri, Kabupaten Bogor, Jawa Barat 16965',
                'phone' => '(021) 1234-5678',
                'email' => 'info@company.com'
            ]
        ]);
    }

    /**
     * Export Individual Report HTML - GABUNGAN ABSENSI & IZIN & KINERJA
     */
    public function exportIndividualPdf(Request $request, Carbon $periode)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);

        // Get comprehensive data
        $absensiData = $this->getIndividualAbsensiData($user, $periode);
        $izinData = $this->getIndividualIzinData($user, $periode);
        $kinerjData = $this->getIndividualKinerjaData($user, $periode);

        return view('print.laporan-individual', [
            'user' => $user,
            'periode' => $periode,
            'absensi' => $absensiData,
            'izin' => $izinData,
            'kinerja' => $kinerjData,
            'company' => [
                'name' => 'PT. Inna Pharmaceutical Industry',
                'address' => 'Jl. Barokah No.1 1, RT.1/RW.8, Wanaherang, Kec. Gn. Putri, Kabupaten Bogor, Jawa Barat 16965',
                'phone' => '(021) 1234-5678',
                'email' => 'info@company.com'
            ]
        ]);
    }

    /**
     * Get Individual Absensi Data
     */
    private function getIndividualAbsensiData(User $user, Carbon $periode)
    {
        $attendances = Attendance::with(['shift'])
            ->where('user_id', $user->id)
            ->whereMonth('tanggal_absen', $periode->month)
            ->whereYear('tanggal_absen', $periode->year)
            ->orderBy('tanggal_absen', 'asc')
            ->get();

        $stats = $this->calculateUserStats($user, $periode);

        return [
            'attendances' => $attendances,
            'stats' => $stats
        ];
    }

    /**
     * Get Individual Izin Data
     */
    private function getIndividualIzinData(User $user, Carbon $periode)
    {
        $leaveRequests = LeaveRequest::with(['approver'])
            ->where('user_id', $user->id)
            ->whereMonth('tanggal_mulai', $periode->month)
            ->whereYear('tanggal_mulai', $periode->year)
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total_pengajuan' => $leaveRequests->count(),
            'disetujui' => $leaveRequests->where('status', 'disetujui')->count(),
            'ditolak' => $leaveRequests->where('status', 'ditolak')->count(),
            'menunggu' => $leaveRequests->where('status', 'menunggu')->count(),
            'total_hari_izin' => $leaveRequests->where('status', 'disetujui')->sum('total_hari'),
        ];

        return [
            'leaveRequests' => $leaveRequests,
            'stats' => $stats
        ];
    }

    /**
     * Get Individual Kinerja Data
     */
    private function getIndividualKinerjaData(User $user, Carbon $periode)
    {
        $stats = $this->calculateUserStats($user, $periode);

        $totalHariKerja = $this->getWorkingDaysInMonth($periode);
        $tingkatKehadiran = $totalHariKerja > 0 ?
            round(($stats['total_hadir'] / $totalHariKerja) * 100, 2) : 0;

        // Rating system
        if ($tingkatKehadiran >= 95 && $stats['total_terlambat'] <= 2) {
            $rating = 'Excellent';
            $ratingClass = 'success';
        } elseif ($tingkatKehadiran >= 85 && $stats['total_terlambat'] <= 5) {
            $rating = 'Good';
            $ratingClass = 'primary';
        } elseif ($tingkatKehadiran >= 75) {
            $rating = 'Average';
            $ratingClass = 'warning';
        } else {
            $rating = 'Poor';
            $ratingClass = 'danger';
        }

        // Get trends (compare with previous month)
        $previousMonth = $periode->copy()->subMonth();
        $previousStats = $this->calculateUserStats($user, $previousMonth);

        $trends = [
            'kehadiran' => $stats['tingkat_kehadiran'] - ($previousStats['tingkat_kehadiran'] ?? 0),
            'keterlambatan' => $stats['total_terlambat'] - ($previousStats['total_terlambat'] ?? 0),
        ];

        return [
            'stats' => array_merge($stats, [
                'tingkat_kehadiran' => $tingkatKehadiran,
                'rating' => $rating,
                'rating_class' => $ratingClass
            ]),
            'trends' => $trends,
            'previous_stats' => $previousStats
        ];
    }

    /**
     * Get Absensi Data for Export
     */
    private function getAbsensiDataForExport(Request $request, Carbon $periode)
    {
        $query = User::karyawan()->aktif()->with('shift');

        if ($request->user_id) {
            $query->where('id', $request->user_id);
        }

        if ($request->shift_id) {
            $query->where('shift_id', $request->shift_id);
        }

        $absensi = $query->withCount([
            'attendances as total_hadir' => function ($q) use ($periode) {
                $q->whereMonth('tanggal_absen', $periode->month)
                    ->whereYear('tanggal_absen', $periode->year)
                    ->whereIn('status_absen', ['hadir', 'terlambat']);
            },
            'attendances as total_terlambat' => function ($q) use ($periode) {
                $q->whereMonth('tanggal_absen', $periode->month)
                    ->whereYear('tanggal_absen', $periode->year)
                    ->where('status_absen', 'terlambat');
            },
            'attendances as total_tidak_hadir' => function ($q) use ($periode) {
                $q->whereMonth('tanggal_absen', $periode->month)
                    ->whereYear('tanggal_absen', $periode->year)
                    ->where('status_absen', 'tidak_hadir');
            },
            'attendances as total_izin' => function ($q) use ($periode) {
                $q->whereMonth('tanggal_absen', $periode->month)
                    ->whereYear('tanggal_absen', $periode->year)
                    ->where('status_absen', 'izin');
            },
        ])->get();

        $totalQuery = Attendance::whereMonth('tanggal_absen', $periode->month)
            ->whereYear('tanggal_absen', $periode->year);

        if ($request->user_id) {
            $totalQuery->where('user_id', $request->user_id);
        }

        if ($request->shift_id) {
            $totalQuery->where('shift_id', $request->shift_id);
        }

        $stats = [
            'total_hari_kerja' => $this->getWorkingDaysInMonth($periode),
            'total_absensi' => $totalQuery->count(),
            'total_hadir' => $totalQuery->clone()->hadir()->count(),
            'total_terlambat' => $totalQuery->clone()->terlambat()->count(),
            'total_tidak_hadir' => $totalQuery->clone()->tidakHadir()->count(),
            'total_izin' => $totalQuery->clone()->where('status_absen', 'izin')->count(),
        ];

        return [
            'stats' => $stats,
            'absensi' => $absensi->map(function ($emp) use ($periode) {
                $totalHariKerja = $this->getWorkingDaysInMonth($periode);
                $tingkatKehadiran = $totalHariKerja > 0 ?
                    round(($emp->total_hadir / $totalHariKerja) * 100, 2) : 0;

                return [
                    'id' => $emp->id,
                    'id_karyawan' => $emp->id_karyawan,
                    'karyawan' => $emp->name,
                    'shift' => $emp->shift ? $emp->shift->nama : '-',
                    'total_hari_kerja' => $totalHariKerja,
                    'total_hadir' => $emp->total_hadir,
                    'total_terlambat' => $emp->total_terlambat,
                    'total_tidak_hadir' => $emp->total_tidak_hadir,
                    'total_izin' => $emp->total_izin,
                    'tingkat_kehadiran' => $tingkatKehadiran,
                ];
            })
        ];
    }

    /**
     * Get Izin Data for Export
     */
    private function getIzinDataForExport(Request $request, Carbon $periode)
    {
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

        $stats = [
            'total_pengajuan' => $query->count(),
            'disetujui' => $query->clone()->disetujui()->count(),
            'ditolak' => $query->clone()->ditolak()->count(),
            'menunggu' => $query->clone()->menunggu()->count(),
            'total_hari_izin' => $query->clone()->disetujui()->sum('total_hari'),
        ];

        // Stats per jenis izin untuk export
        $statsPerJenis = LeaveRequest::whereMonth('tanggal_mulai', $periode->month)
            ->whereYear('tanggal_mulai', $periode->year)
            ->when($request->user_id, function ($q) use ($request) {
                return $q->where('user_id', $request->user_id);
            })
            ->selectRaw('jenis_izin, status, COUNT(*) as total, SUM(total_hari) as total_hari')
            ->groupBy('jenis_izin', 'status')
            ->get()
            ->groupBy('jenis_izin');

        return [
            'stats' => $stats,
            'izin' => $izin,
            'stats_per_jenis' => $statsPerJenis
        ];
    }

    /**
     * Calculate User Stats for Individual Report
     */
    private function calculateUserStats(User $user, Carbon $periode)
    {
        $totalHariKerja = $this->getWorkingDaysInMonth($periode);

        $attendanceQuery = $user->attendances()
            ->whereMonth('tanggal_absen', $periode->month)
            ->whereYear('tanggal_absen', $periode->year);

        $totalHadir = $attendanceQuery->clone()->whereIn('status_absen', ['hadir', 'terlambat'])->count();
        $totalTerlambat = $attendanceQuery->clone()->where('status_absen', 'terlambat')->count();
        $totalTidakHadir = $attendanceQuery->clone()->where('status_absen', 'tidak_hadir')->count();
        $totalIzin = $attendanceQuery->clone()->where('status_absen', 'izin')->count();

        $tingkatKehadiran = $totalHariKerja > 0 ? round(($totalHadir / $totalHariKerja) * 100, 2) : 0;

        // Calculate late minutes
        $totalMinuteTerlambat = $attendanceQuery->clone()->sum('menit_terlambat');
        $rataRataMinuteTerlambat = $totalTerlambat > 0 ? round($totalMinuteTerlambat / $totalTerlambat, 2) : 0;

        return [
            'total_hari_kerja' => $totalHariKerja,
            'total_hadir' => $totalHadir,
            'total_terlambat' => $totalTerlambat,
            'total_tidak_hadir' => $totalTidakHadir,
            'total_izin' => $totalIzin,
            'tingkat_kehadiran' => $tingkatKehadiran,
            'total_menit_terlambat' => $totalMinuteTerlambat,
            'rata_rata_menit_terlambat' => $rataRataMinuteTerlambat,
        ];
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
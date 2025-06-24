<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ApiScheduleController extends Controller
{
    /**
     * GET JADWAL BULANAN USER
     * Menampilkan jadwal selama 1 bulan (default bulan ini)
     */
    public function monthly(Request $request)
    {
        try {
            $user = Auth::user();


            $year = $request->year ?? now()->year;
            $month = $request->month ?? now()->month;


            $validator = Validator::make([
                'year' => $year,
                'month' => $month
            ], [
                'year' => 'required|integer|min:2020|max:2030',
                'month' => 'required|integer|min:1|max:12'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tidak valid',
                    'errors' => $validator->errors()
                ], 422);
            }


            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();


            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('tanggal_absen', [$startDate, $endDate])
                ->with('shift')
                ->orderBy('tanggal_absen', 'asc')
                ->get()
                ->keyBy(function ($item) {
                    return $item->tanggal_absen->format('Y-m-d');
                });


            $calendar = [];
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeek;
                $isWeekend = in_array($dayOfWeek, [0, 6]);


                $attendance = $attendances->get($dateStr);


                $dayData = [
                    'date' => $dateStr,
                    'day' => $currentDate->day,
                    'day_name' => $currentDate->format('l'),
                    'day_name_short' => $currentDate->format('D'),
                    'day_name_id' => $this->getDayNameIndonesian($currentDate->format('l')),
                    'is_weekend' => $isWeekend,
                    'is_today' => $currentDate->isToday(),
                    'is_past' => $currentDate->isPast(),
                    'is_future' => $currentDate->isFuture(),
                ];

                if ($attendance) {

                    $dayData = array_merge($dayData, [
                        'has_schedule' => true,
                        'attendance_id' => $attendance->id,
                        'shift' => $attendance->shift ? [
                            'id' => $attendance->shift->id,
                            'nama' => $attendance->shift->nama,
                            'jam_masuk' => $attendance->shift->jam_masuk->format('H:i'),
                            'jam_keluar' => $attendance->shift->jam_keluar->format('H:i'),
                            'toleransi_menit' => $attendance->shift->toleransi_menit,
                        ] : null,
                        'jam_masuk_actual' => $attendance->jam_masuk ? Carbon::parse($attendance->jam_masuk)->format('H:i') : null,
                        'jam_keluar_actual' => $attendance->jam_keluar ? Carbon::parse($attendance->jam_keluar)->format('H:i') : null,
                        'status_absen' => $attendance->status_absen,
                        'status_masuk' => $attendance->status_masuk,
                        'status_keluar' => $attendance->status_keluar,
                        'menit_terlambat' => $attendance->menit_terlambat,
                        'menit_lembur' => $attendance->menit_lembur,
                        'sudah_check_in' => !is_null($attendance->jam_masuk),
                        'sudah_check_out' => !is_null($attendance->jam_keluar),
                        'is_complete' => !is_null($attendance->jam_masuk) && !is_null($attendance->jam_keluar),
                        'durasi_kerja' => $this->getDurasiKerjaFormatted($attendance),
                    ]);
                } else {

                    $dayData = array_merge($dayData, [
                        'has_schedule' => false,
                        'attendance_id' => null,
                        'shift' => null,
                        'jam_masuk_actual' => null,
                        'jam_keluar_actual' => null,
                        'status_absen' => null,
                        'status_masuk' => null,
                        'status_keluar' => null,
                        'menit_terlambat' => 0,
                        'menit_lembur' => 0,
                        'sudah_check_in' => false,
                        'sudah_check_out' => false,
                        'is_complete' => false,
                        'durasi_kerja' => null,
                    ]);
                }

                $calendar[] = $dayData;
                $currentDate->addDay();
            }


            $monthlyStats = [
                'total_hari_dalam_bulan' => $endDate->day,
                'total_hari_kerja' => $attendances->count(),
                'total_weekend' => $this->countWeekends($startDate, $endDate),
                'total_hadir' => $attendances->whereIn('status_absen', ['hadir', 'terlambat'])->count(),
                'total_terlambat' => $attendances->where('status_absen', 'terlambat')->count(),
                'total_tidak_hadir' => $attendances->where('status_absen', 'tidak_hadir')->count(),
                'total_izin' => $attendances->where('status_absen', 'izin')->count(),
                'total_menunggu' => $attendances->where('status_absen', 'menunggu')->count(),
                'total_incomplete' => $attendances->filter(function ($att) {
                    return is_null($att->jam_masuk) || is_null($att->jam_keluar);
                })->count(),
            ];


            if ($monthlyStats['total_hari_kerja'] > 0) {
                $monthlyStats['tingkat_kehadiran'] = round(($monthlyStats['total_hadir'] / $monthlyStats['total_hari_kerja']) * 100, 2);
                $monthlyStats['tingkat_ketepatan'] = round((($monthlyStats['total_hadir'] - $monthlyStats['total_terlambat']) / $monthlyStats['total_hari_kerja']) * 100, 2);
            } else {
                $monthlyStats['tingkat_kehadiran'] = 0;
                $monthlyStats['tingkat_ketepatan'] = 0;
            }


            $periodInfo = [
                'year' => $year,
                'month' => $month,
                'month_name' => $startDate->format('F'),
                'month_name_id' => $this->getMonthNameIndonesian($startDate->format('F')),
                'period_text' => $startDate->format('F Y'),
                'period_text_id' => $this->getMonthNameIndonesian($startDate->format('F')) . ' ' . $year,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_days' => $endDate->day,
                'is_current_month' => $startDate->isCurrentMonth(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Jadwal bulanan berhasil diambil',
                'data' => [
                    'period' => $periodInfo,
                    'calendar' => $calendar,
                    'statistics' => $monthlyStats,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'id_karyawan' => $user->id_karyawan,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Monthly Schedule Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * GET JADWAL MINGGUAN USER
     * Menampilkan jadwal selama 1 minggu
     */
    public function weekly(Request $request)
    {
        try {
            $user = Auth::user();


            $startDate = $request->start_date ?
                Carbon::parse($request->start_date)->startOfWeek() :
                now()->startOfWeek();

            $endDate = $startDate->copy()->endOfWeek();


            $attendances = Attendance::where('user_id', $user->id)
                ->whereBetween('tanggal_absen', [$startDate, $endDate])
                ->with('shift')
                ->orderBy('tanggal_absen', 'asc')
                ->get()
                ->keyBy(function ($item) {
                    return $item->tanggal_absen->format('Y-m-d');
                });


            $weekCalendar = [];
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                $dateStr = $currentDate->format('Y-m-d');
                $attendance = $attendances->get($dateStr);
                $isWeekend = in_array($currentDate->dayOfWeek, [0, 6]);

                $dayData = [
                    'date' => $dateStr,
                    'day' => $currentDate->day,
                    'day_name' => $currentDate->format('l'),
                    'day_name_short' => $currentDate->format('D'),
                    'day_name_id' => $this->getDayNameIndonesian($currentDate->format('l')),
                    'is_weekend' => $isWeekend,
                    'is_today' => $currentDate->isToday(),
                    'is_past' => $currentDate->isPast(),
                    'is_future' => $currentDate->isFuture(),
                    'has_schedule' => !is_null($attendance),
                ];

                if ($attendance) {
                    $dayData = array_merge($dayData, [
                        'attendance_id' => $attendance->id,
                        'shift' => $attendance->shift ? [
                            'id' => $attendance->shift->id,
                            'nama' => $attendance->shift->nama,
                            'jam_masuk' => $attendance->shift->jam_masuk->format('H:i'),
                            'jam_keluar' => $attendance->shift->jam_keluar->format('H:i'),
                        ] : null,
                        'status_absen' => $attendance->status_absen,
                        'status_masuk' => $attendance->status_masuk,
                        'status_keluar' => $attendance->status_keluar,
                        'jam_masuk_actual' => $attendance->jam_masuk ? Carbon::parse($attendance->jam_masuk)->format('H:i') : null,
                        'jam_keluar_actual' => $attendance->jam_keluar ? Carbon::parse($attendance->jam_keluar)->format('H:i') : null,
                        'is_complete' => !is_null($attendance->jam_masuk) && !is_null($attendance->jam_keluar),
                        'menit_terlambat' => $attendance->menit_terlambat,
                        'menit_lembur' => $attendance->menit_lembur,
                        'durasi_kerja' => $this->getDurasiKerjaFormatted($attendance),
                    ]);
                } else {
                    $dayData = array_merge($dayData, [
                        'attendance_id' => null,
                        'shift' => null,
                        'status_absen' => null,
                        'status_masuk' => null,
                        'status_keluar' => null,
                        'jam_masuk_actual' => null,
                        'jam_keluar_actual' => null,
                        'is_complete' => false,
                        'menit_terlambat' => 0,
                        'menit_lembur' => 0,
                        'durasi_kerja' => null,
                    ]);
                }

                $weekCalendar[] = $dayData;
                $currentDate->addDay();
            }


            $weeklyStats = [
                'total_hari_kerja' => $attendances->count(),
                'total_hadir' => $attendances->whereIn('status_absen', ['hadir', 'terlambat'])->count(),
                'total_terlambat' => $attendances->where('status_absen', 'terlambat')->count(),
                'total_tidak_hadir' => $attendances->where('status_absen', 'tidak_hadir')->count(),
                'total_izin' => $attendances->where('status_absen', 'izin')->count(),
                'total_menunggu' => $attendances->where('status_absen', 'menunggu')->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Jadwal mingguan berhasil diambil',
                'data' => [
                    'period' => [
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'week_text' => 'Minggu ' . $startDate->format('d M') . ' - ' . $endDate->format('d M Y'),
                        'is_current_week' => $startDate->isCurrentWeek(),
                    ],
                    'calendar' => $weekCalendar,
                    'statistics' => $weeklyStats,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'id_karyawan' => $user->id_karyawan,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Weekly Schedule Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }





    private function getDayNameIndonesian($englishDay)
    {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        return $days[$englishDay] ?? $englishDay;
    }

    private function getMonthNameIndonesian($englishMonth)
    {
        $months = [
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember',
        ];

        return $months[$englishMonth] ?? $englishMonth;
    }

    private function countWeekends($startDate, $endDate)
    {
        $count = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            if (in_array($current->dayOfWeek, [0, 6])) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    private function getDurasiKerjaFormatted($attendance)
    {
        if (is_null($attendance->jam_masuk) || is_null($attendance->jam_keluar)) {
            return null;
        }

        $jamMasuk = Carbon::parse($attendance->jam_masuk);
        $jamKeluar = Carbon::parse($attendance->jam_keluar);
        $totalMenit = $jamMasuk->diffInMinutes($jamKeluar);

        $jam = floor($totalMenit / 60);
        $menit = $totalMenit % 60;

        return "{$jam} jam {$menit} menit";
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ApiAttendanceController extends Controller
{
    /**
     * GET ABSENSI HARI INI
     */
    public function todayAttendance()
    {
        try {
            $user = Auth::user();
            $today = today();

            $attendance = Attendance::where('user_id', $user->id)
                ->where('tanggal_absen', $today)
                ->with('shift')
                ->first();

            if (!$attendance) {
                return response()->json([
                    'success' => true,
                    'message' => 'Belum ada data absensi hari ini',
                    'data' => null
                ]);
            }

            $attendanceData = [
                'id' => $attendance->id,
                'tanggal_absen' => $attendance->tanggal_absen->format('Y-m-d'),
                'shift' => [
                    'id' => $attendance->shift->id,
                    'nama' => $attendance->shift->nama,
                    'jam_masuk' => $attendance->shift->jam_masuk->format('H:i'),
                    'jam_keluar' => $attendance->shift->jam_keluar->format('H:i'),
                    'toleransi_menit' => $attendance->shift->toleransi_menit
                ],
                'jam_masuk' => $attendance->jam_masuk ? Carbon::parse($attendance->jam_masuk)->format('H:i:s') : null,
                'jam_keluar' => $attendance->jam_keluar ? Carbon::parse($attendance->jam_keluar)->format('H:i:s') : null,
                'foto_masuk_url' => $attendance->foto_masuk_url,
                'foto_keluar_url' => $attendance->foto_keluar_url,
                'latitude_masuk' => $attendance->latitude_masuk,
                'longitude_masuk' => $attendance->longitude_masuk,
                'latitude_keluar' => $attendance->latitude_keluar,
                'longitude_keluar' => $attendance->longitude_keluar,
                'status_absen' => $attendance->status_absen,
                'status_masuk' => $attendance->status_masuk,
                'status_keluar' => $attendance->status_keluar,
                'menit_terlambat' => $attendance->menit_terlambat,
                'menit_lembur' => $attendance->menit_lembur,
                'catatan_admin' => $attendance->catatan_admin,
                'sudah_check_in' => !is_null($attendance->jam_masuk),
                'sudah_check_out' => !is_null($attendance->jam_keluar),
                'dapat_check_out' => !is_null($attendance->jam_masuk) && is_null($attendance->jam_keluar)
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data absensi hari ini berhasil diambil',
                'data' => $attendanceData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CHECK IN (ABSEN MASUK) dengan GPS & Foto
     */
    public function checkIn(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'shift_id' => 'required|exists:shifts,id',
                'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $today = today();

            // Cek apakah sudah ada absensi hari ini
            $existingAttendance = Attendance::where('user_id', $user->id)
                ->where('tanggal_absen', $today)
                ->first();

            if ($existingAttendance && $existingAttendance->jam_masuk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah check in hari ini'
                ], 422);
            }

            // Get shift info
            $shift = Shift::find($request->shift_id);
            if (!$shift->aktif) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shift tidak aktif'
                ], 422);
            }

            // Upload foto
            $fotoPath = $this->uploadFoto($request->file('foto'), $user->id, 'masuk');

            // Waktu sekarang
            $jamMasuk = now();

            // Hitung keterlambatan
            $jamMasukShift = Carbon::parse($today->format('Y-m-d') . ' ' . $shift->jam_masuk->format('H:i:s'));
            $toleransiMenit = $shift->toleransi_menit;
            $batasToleransi = $jamMasukShift->addMinutes($toleransiMenit);

            $menitTerlambat = 0;
            $statusAbsen = 'hadir';

            if ($jamMasuk->gt($batasToleransi)) {
                $menitTerlambat = $jamMasuk->diffInMinutes($jamMasukShift);
                $statusAbsen = 'terlambat';
            }

            // Create atau update attendance
            $attendanceData = [
                'user_id' => $user->id,
                'shift_id' => $request->shift_id,
                'tanggal_absen' => $today,
                'jam_masuk' => $jamMasuk,
                'foto_masuk' => $fotoPath,
                'latitude_masuk' => $request->latitude,
                'longitude_masuk' => $request->longitude,
                'status_masuk' => 'menunggu', // Perlu approval admin
                'status_absen' => $statusAbsen,
                'menit_terlambat' => $menitTerlambat,
            ];

            if ($existingAttendance) {
                $existingAttendance->update($attendanceData);
                $attendance = $existingAttendance;
            } else {
                $attendance = Attendance::create($attendanceData);
            }

            // Response data
            $responseData = [
                'id' => $attendance->id,
                'jam_masuk' => $jamMasuk->format('H:i:s'),
                'status_absen' => $statusAbsen,
                'menit_terlambat' => $menitTerlambat,
                'status_masuk' => 'menunggu',
                'foto_masuk_url' => asset($fotoPath),
                'pesan_terlambat' => $menitTerlambat > 0 ? "Anda terlambat {$menitTerlambat} menit" : 'Tepat waktu'
            ];

            return response()->json([
                'success' => true,
                'message' => 'Check in berhasil, menunggu approval admin',
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CHECK OUT (ABSEN KELUAR) dengan GPS & Foto
     */
    public function checkOut(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $today = today();

            // Cek apakah sudah check in hari ini
            $attendance = Attendance::where('user_id', $user->id)
                ->where('tanggal_absen', $today)
                ->first();

            if (!$attendance || !$attendance->jam_masuk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum check in hari ini'
                ], 422);
            }

            if ($attendance->jam_keluar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah check out hari ini'
                ], 422);
            }

            // Upload foto
            $fotoPath = $this->uploadFoto($request->file('foto'), $user->id, 'keluar');

            // Waktu sekarang
            $jamKeluar = now();

            // Hitung lembur (jika lebih dari jam shift keluar)
            $jamKeluarShift = Carbon::parse($today->format('Y-m-d') . ' ' . $attendance->shift->jam_keluar->format('H:i:s'));

            // Handle shift malam (jam keluar < jam masuk)
            if ($attendance->shift->jam_keluar < $attendance->shift->jam_masuk) {
                $jamKeluarShift->addDay();
            }

            $menitLembur = 0;
            if ($jamKeluar->gt($jamKeluarShift)) {
                $menitLembur = $jamKeluar->diffInMinutes($jamKeluarShift);
            }

            // Update attendance
            $attendance->update([
                'jam_keluar' => $jamKeluar,
                'foto_keluar' => $fotoPath,
                'latitude_keluar' => $request->latitude,
                'longitude_keluar' => $request->longitude,
                'status_keluar' => 'menunggu', // Perlu approval admin
                'menit_lembur' => $menitLembur,
            ]);

            // Hitung total jam kerja
            $jamMasuk = Carbon::parse($attendance->jam_masuk);
            $totalJamKerja = $jamMasuk->diffInMinutes($jamKeluar);
            $jamKerjaFormatted = floor($totalJamKerja / 60) . ' jam ' . ($totalJamKerja % 60) . ' menit';

            // Response data
            $responseData = [
                'id' => $attendance->id,
                'jam_keluar' => $jamKeluar->format('H:i:s'),
                'menit_lembur' => $menitLembur,
                'total_jam_kerja' => $jamKerjaFormatted,
                'total_menit_kerja' => $totalJamKerja,
                'status_keluar' => 'menunggu',
                'foto_keluar_url' => asset($fotoPath),
                'pesan_lembur' => $menitLembur > 0 ? "Lembur {$menitLembur} menit" : 'Sesuai jadwal'
            ];

            return response()->json([
                'success' => true,
                'message' => 'Check out berhasil, menunggu approval admin',
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET HISTORY ABSENSI
     */
    public function history(Request $request)
    {
        try {
            $user = Auth::user();

            // Default 30 hari terakhir
            $limit = $request->limit ?? 30;
            $page = $request->page ?? 1;
            $bulan = $request->bulan; // Format: Y-m (contoh: 2024-01)

            $query = Attendance::where('user_id', $user->id)
                ->with('shift')
                ->orderBy('tanggal_absen', 'desc');

            // Filter by month if provided
            if ($bulan) {
                $date = Carbon::parse($bulan);
                $query->whereMonth('tanggal_absen', $date->month)
                    ->whereYear('tanggal_absen', $date->year);
            }

            $attendances = $query->paginate($limit, ['*'], 'page', $page);

            $attendanceData = $attendances->items();
            $formattedData = collect($attendanceData)->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'tanggal_absen' => $attendance->tanggal_absen->format('Y-m-d'),
                    'tanggal_formatted' => $attendance->tanggal_absen->format('d/m/Y'),
                    'hari' => $attendance->tanggal_absen->format('l'),
                    'shift' => [
                        'nama' => $attendance->shift->nama,
                        'jam_masuk' => $attendance->shift->jam_masuk->format('H:i'),
                        'jam_keluar' => $attendance->shift->jam_keluar->format('H:i'),
                    ],
                    'jam_masuk' => $attendance->jam_masuk ? Carbon::parse($attendance->jam_masuk)->format('H:i:s') : null,
                    'jam_keluar' => $attendance->jam_keluar ? Carbon::parse($attendance->jam_keluar)->format('H:i:s') : null,
                    'status_absen' => $attendance->status_absen,
                    'status_absen_text' => $attendance->getStatusAbsenText(),
                    'menit_terlambat' => $attendance->menit_terlambat,
                    'menit_lembur' => $attendance->menit_lembur,
                    'durasi_kerja' => $attendance->getDurasiKerjaFormatted(),
                    'foto_masuk_url' => $attendance->foto_masuk_url,
                    'foto_keluar_url' => $attendance->foto_keluar_url,
                    'status_masuk' => $attendance->status_masuk,
                    'status_keluar' => $attendance->status_keluar,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'History absensi berhasil diambil',
                'data' => $formattedData,
                'pagination' => [
                    'current_page' => $attendances->currentPage(),
                    'last_page' => $attendances->lastPage(),
                    'per_page' => $attendances->perPage(),
                    'total' => $attendances->total(),
                    'from' => $attendances->firstItem(),
                    'to' => $attendances->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET STATISTIK ABSENSI BULAN INI
     */
    public function monthlyStats()
    {
        try {
            $user = Auth::user();
            $currentMonth = now();

            $stats = [
                'total_hari_kerja' => $user->attendances()
                    ->whereMonth('tanggal_absen', $currentMonth->month)
                    ->whereYear('tanggal_absen', $currentMonth->year)
                    ->count(),
                'total_hadir' => $user->attendances()
                    ->whereMonth('tanggal_absen', $currentMonth->month)
                    ->whereYear('tanggal_absen', $currentMonth->year)
                    ->whereIn('status_absen', ['hadir', 'terlambat'])
                    ->count(),
                'total_terlambat' => $user->attendances()
                    ->whereMonth('tanggal_absen', $currentMonth->month)
                    ->whereYear('tanggal_absen', $currentMonth->year)
                    ->where('status_absen', 'terlambat')
                    ->count(),
                'total_tidak_hadir' => $user->attendances()
                    ->whereMonth('tanggal_absen', $currentMonth->month)
                    ->whereYear('tanggal_absen', $currentMonth->year)
                    ->where('status_absen', 'tidak_hadir')
                    ->count(),
                'total_izin' => $user->attendances()
                    ->whereMonth('tanggal_absen', $currentMonth->month)
                    ->whereYear('tanggal_absen', $currentMonth->year)
                    ->where('status_absen', 'izin')
                    ->count(),
            ];

            // Calculate attendance rate
            $stats['tingkat_kehadiran'] = $stats['total_hari_kerja'] > 0 ?
                round(($stats['total_hadir'] / $stats['total_hari_kerja']) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'message' => 'Statistik absensi berhasil diambil',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * UPLOAD FOTO HELPER
     */
    private function uploadFoto($file, $userId, $type)
    {
        $uploadPath = public_path('uploads/attendance');

        if (!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }

        $fileName = date('Y-m-d') . '_' . $userId . '_' . $type . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($uploadPath, $fileName);

        return 'uploads/attendance/' . $fileName;
    }
}
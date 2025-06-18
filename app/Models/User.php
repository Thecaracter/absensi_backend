<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Kolom yang bisa diisi mass assignment
     */
    protected $fillable = [
        'id_karyawan',
        'name',
        'email',
        'no_hp',
        'role',
        'status',
        'shift_id',
        'tanggal_masuk',
        'alamat',
        'foto',
        'password',
    ];

    /**
     * Kolom yang disembunyikan
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'tanggal_masuk' => 'date',
        'password' => 'hashed',
    ];

    /**
     * Relasi ke Shift (karyawan punya 1 shift)
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Relasi ke Attendances (user punya banyak absensi)
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Relasi ke LeaveRequests (user punya banyak izin)
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Relasi ke LeaveRequests yang di-approve oleh user ini (admin)
     */
    public function approvedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'disetujui_oleh');
    }

    /**
     * Scope untuk karyawan
     */
    public function scopeKaryawan($query)
    {
        return $query->where('role', 'karyawan');
    }

    /**
     * Scope untuk admin
     */
    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope untuk user aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope untuk user nonaktif
     */
    public function scopeNonaktif($query)
    {
        return $query->where('status', 'nonaktif');
    }

    /**
     * Cek apakah user adalah admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Cek apakah user adalah karyawan
     */
    public function isKaryawan()
    {
        return $this->role === 'karyawan';
    }

    /**
     * Cek apakah user aktif
     */
    public function isAktif()
    {
        return $this->status === 'aktif';
    }

    /**
     * Get nama lengkap dengan ID karyawan
     */
    public function getNamaLengkapAttribute()
    {
        return $this->name . ' (' . $this->id_karyawan . ')';
    }

    /**
     * Get absensi hari ini
     */
    public function getAbsensiHariIni()
    {
        return $this->attendances()
            ->where('tanggal_absen', today())
            ->first();
    }

    /**
     * Get absensi bulan ini
     */
    public function getAbsensiBulanIni($bulan = null, $tahun = null)
    {
        $bulan = $bulan ?? now()->month;
        $tahun = $tahun ?? now()->year;

        return $this->attendances()
            ->whereMonth('tanggal_absen', $bulan)
            ->whereYear('tanggal_absen', $tahun)
            ->orderBy('tanggal_absen', 'desc')
            ->get();
    }

    /**
     * Hitung total hari kerja bulan ini
     */
    public function getTotalHariKerjaBulanIni()
    {
        return $this->attendances()
            ->whereMonth('tanggal_absen', now()->month)
            ->whereYear('tanggal_absen', now()->year)
            ->whereIn('status_absen', ['hadir', 'terlambat'])
            ->count();
    }

    /**
     * Hitung total hari terlambat bulan ini
     */
    public function getTotalHariTerlambatBulanIni()
    {
        return $this->attendances()
            ->whereMonth('tanggal_absen', now()->month)
            ->whereYear('tanggal_absen', now()->year)
            ->where('status_absen', 'terlambat')
            ->count();
    }

    /**
     * Get foto profile URL - langsung dari public directory
     */
    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return asset($this->foto);
        }
        return asset('images/default-avatar.png');
    }

    // ============================================================================
    // TAMBAHAN METHOD UNTUK SISTEM JADWAL CALENDAR
    // ============================================================================

    /**
     * Alternative untuk isActive() - sesuai dengan method yang dibutuhin controller
     */
    public function isActive()
    {
        return $this->isAktif();
    }

    /**
     * Get today's attendance - alias untuk getAbsensiHariIni()
     */
    public function getTodayAttendance()
    {
        return $this->getAbsensiHariIni();
    }

    /**
     * Check if user has checked in today
     */
    public function hasCheckedInToday()
    {
        $attendance = $this->getTodayAttendance();
        return $attendance && $attendance->jam_masuk;
    }

    /**
     * Check if user has checked out today
     */
    public function hasCheckedOutToday()
    {
        $attendance = $this->getTodayAttendance();
        return $attendance && $attendance->jam_keluar;
    }

    /**
     * Check if user has shift assigned
     */
    public function hasShift()
    {
        return !is_null($this->shift_id);
    }

    /**
     * Get attendance statistics for a period
     */
    public function getAttendanceStats($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $query = $this->attendances()
            ->whereBetween('tanggal_absen', [$startDate, $endDate]);

        return [
            'total' => $query->count(),
            'menunggu' => $query->clone()->where('status_absen', 'menunggu')->count(),
            'hadir' => $query->clone()->where('status_absen', 'hadir')->count(),
            'terlambat' => $query->clone()->where('status_absen', 'terlambat')->count(),
            'tidak_hadir' => $query->clone()->where('status_absen', 'tidak_hadir')->count(),
            'izin' => $query->clone()->where('status_absen', 'izin')->count(),
        ];
    }

    /**
     * Get monthly attendance rate
     */
    public function getMonthlyAttendanceRate($year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $stats = $this->getAttendanceStats($startDate, $endDate);

        if ($stats['total'] === 0) {
            return 0;
        }

        $presentDays = $stats['hadir'] + $stats['terlambat'];
        return round(($presentDays / $stats['total']) * 100, 2);
    }

    /**
     * Get punctuality rate
     */
    public function getPunctualityRate($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $stats = $this->getAttendanceStats($startDate, $endDate);

        if ($stats['total'] === 0) {
            return 0;
        }

        return round(($stats['hadir'] / $stats['total']) * 100, 2);
    }

    /**
     * Get average late minutes
     */
    public function getAverageLateMinutes($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $avgLate = $this->attendances()
            ->whereBetween('tanggal_absen', [$startDate, $endDate])
            ->where('status_absen', 'terlambat')
            ->avg('menit_terlambat');

        return round($avgLate ?? 0, 2);
    }

    /**
     * Get total working hours for a period
     */
    public function getTotalWorkingHours($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $attendances = $this->attendances()
            ->whereBetween('tanggal_absen', [$startDate, $endDate])
            ->whereNotNull('jam_masuk')
            ->whereNotNull('jam_keluar')
            ->get();

        $totalMinutes = 0;

        foreach ($attendances as $attendance) {
            $masuk = Carbon::parse($attendance->jam_masuk);
            $keluar = Carbon::parse($attendance->jam_keluar);
            $totalMinutes += $keluar->diffInMinutes($masuk);
        }

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return [
            'total_minutes' => $totalMinutes,
            'hours' => $hours,
            'minutes' => $minutes,
            'formatted' => $hours . ' jam ' . $minutes . ' menit'
        ];
    }

    /**
     * Get performance score
     */
    public function getPerformanceScore($period = 'month')
    {
        $startDate = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        $endDate = match ($period) {
            'week' => now()->endOfWeek(),
            'month' => now()->endOfMonth(),
            'quarter' => now()->endOfQuarter(),
            'year' => now()->endOfYear(),
            default => now()->endOfMonth()
        };

        $attendanceRate = $this->getMonthlyAttendanceRate($startDate->year, $startDate->month);
        $punctualityRate = $this->getPunctualityRate($startDate, $endDate);

        // Weighted score: 70% attendance, 30% punctuality
        $score = ($attendanceRate * 0.7) + ($punctualityRate * 0.3);

        return [
            'score' => round($score, 2),
            'grade' => $this->getPerformanceGrade($score),
            'attendance_rate' => $attendanceRate,
            'punctuality_rate' => $punctualityRate
        ];
    }

    /**
     * Get performance grade based on score
     */
    private function getPerformanceGrade($score)
    {
        return match (true) {
            $score >= 95 => 'A+',
            $score >= 90 => 'A',
            $score >= 85 => 'B+',
            $score >= 80 => 'B',
            $score >= 75 => 'C+',
            $score >= 70 => 'C',
            $score >= 65 => 'D+',
            $score >= 60 => 'D',
            default => 'F'
        };
    }

    /**
     * Get attendance trends for chart
     */
    public function getAttendanceTrends($months = 6)
    {
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $stats = $this->getAttendanceStats(
                $date->copy()->startOfMonth(),
                $date->copy()->endOfMonth()
            );

            $data[] = [
                'month' => $date->format('M Y'),
                'total' => $stats['total'],
                'hadir' => $stats['hadir'],
                'terlambat' => $stats['terlambat'],
                'tidak_hadir' => $stats['tidak_hadir'],
                'izin' => $stats['izin'],
                'attendance_rate' => $stats['total'] > 0 ? round((($stats['hadir'] + $stats['terlambat']) / $stats['total']) * 100, 2) : 0
            ];
        }

        return $data;
    }

    /**
     * Get upcoming schedule
     */
    public function getUpcomingSchedule($days = 7)
    {
        $startDate = today();
        $endDate = today()->addDays($days);

        return $this->attendances()
            ->whereBetween('tanggal_absen', [$startDate, $endDate])
            ->with('shift')
            ->orderBy('tanggal_absen')
            ->get();
    }

    /**
     * Get leave balance
     */
    public function getLeaveBalance()
    {
        $totalLeaveAllowed = 12; // 12 days per year
        $usedLeave = $this->attendances()
            ->where('status_absen', 'izin')
            ->whereYear('tanggal_absen', now()->year)
            ->count();

        return [
            'total_allowed' => $totalLeaveAllowed,
            'used' => $usedLeave,
            'remaining' => $totalLeaveAllowed - $usedLeave
        ];
    }

    /**
     * Check if user can take leave on a specific date
     */
    public function canTakeLeave($date)
    {
        // Check if already has attendance for that date
        $existingAttendance = $this->attendances()
            ->whereDate('tanggal_absen', $date)
            ->first();

        if ($existingAttendance) {
            return false;
        }

        // Check leave balance
        $leaveBalance = $this->getLeaveBalance();
        if ($leaveBalance['remaining'] <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Create attendance record for user
     */
    public function createAttendance($date, $shiftId = null)
    {
        $shiftId = $shiftId ?? $this->shift_id;

        if (!$shiftId) {
            throw new \Exception('User tidak memiliki shift yang ditentukan.');
        }

        // Check if attendance already exists
        $existing = $this->attendances()
            ->whereDate('tanggal_absen', $date)
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->attendances()->create([
            'shift_id' => $shiftId,
            'tanggal_absen' => $date,
            'status_absen' => 'menunggu',
            'status_masuk' => 'menunggu',
            'status_keluar' => 'menunggu'
        ]);
    }

    /**
     * Activate user
     */
    public function activate()
    {
        $this->update(['status' => 'aktif']);
    }

    /**
     * Deactivate user
     */
    public function deactivate()
    {
        $this->update(['status' => 'nonaktif']);
    }

    /**
     * Assign shift to user
     */
    public function assignShift(Shift $shift)
    {
        $this->update(['shift_id' => $shift->id]);
    }

    /**
     * Remove shift from user
     */
    public function removeShift()
    {
        $this->update(['shift_id' => null]);
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->status === 'aktif'
            ? 'bg-green-100 text-green-800'
            : 'bg-red-100 text-red-800';
    }

    /**
     * Get role badge class for display
     */
    public function getRoleBadgeClassAttribute()
    {
        return $this->role === 'admin'
            ? 'bg-blue-100 text-blue-800'
            : 'bg-gray-100 text-gray-800';
    }

    /**
     * Get work duration from tanggal_masuk
     */
    public function getWorkDurationAttribute()
    {
        if (!$this->tanggal_masuk) {
            return null;
        }

        $years = $this->tanggal_masuk->diffInYears(now());
        $months = $this->tanggal_masuk->diffInMonths(now()) % 12;

        if ($years > 0) {
            return $years . ' tahun ' . $months . ' bulan';
        }

        return $months . ' bulan';
    }

    /**
     * Generate employee report
     */
    public function generateReport($period = 'month')
    {
        $startDate = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        $endDate = match ($period) {
            'week' => now()->endOfWeek(),
            'month' => now()->endOfMonth(),
            'quarter' => now()->endOfQuarter(),
            'year' => now()->endOfYear(),
            default => now()->endOfMonth()
        };

        $stats = $this->getAttendanceStats($startDate, $endDate);
        $performance = $this->getPerformanceScore($period);
        $workingHours = $this->getTotalWorkingHours($startDate, $endDate);
        $avgLate = $this->getAverageLateMinutes($startDate, $endDate);

        return [
            'employee_info' => [
                'name' => $this->name,
                'id_karyawan' => $this->id_karyawan,
                'email' => $this->email,
                'no_hp' => $this->no_hp,
                'shift' => $this->shift ? $this->shift->nama : 'No Shift',
                'alamat' => $this->alamat,
                'tanggal_masuk' => $this->tanggal_masuk ? $this->tanggal_masuk->format('d/m/Y') : null
            ],
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'type' => $period
            ],
            'statistics' => $stats,
            'performance' => $performance,
            'working_hours' => $workingHours,
            'average_late_minutes' => $avgLate,
            'trends' => $this->getAttendanceTrends(),
            'generated_at' => now()->format('Y-m-d H:i:s')
        ];
    }
}
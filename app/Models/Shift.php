<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'jam_masuk',
        'jam_keluar',
        'toleransi_menit',
        'aktif',
        'keterangan',
    ];

    protected $casts = [
        'jam_masuk' => 'datetime',
        'jam_keluar' => 'datetime',
        'toleransi_menit' => 'integer',
        'aktif' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Scopes
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeNonaktif($query)
    {
        return $query->where('aktif', false);
    }

    /**
     * Accessors
     */
    public function getJamMasukFormattedAttribute()
    {
        return $this->jam_masuk ? $this->jam_masuk->format('H:i') : null;
    }

    public function getJamKeluarFormattedAttribute()
    {
        return $this->jam_keluar ? $this->jam_keluar->format('H:i') : null;
    }

    public function getDurasiKerjaAttribute()
    {
        if (!$this->jam_masuk || !$this->jam_keluar) {
            return null;
        }

        $masuk = Carbon::parse($this->jam_masuk);
        $keluar = Carbon::parse($this->jam_keluar);

        // Handle shift that crosses midnight
        if ($keluar < $masuk) {
            $keluar->addDay();
        }

        $duration = $keluar->diff($masuk);

        return $duration->format('%H:%I');
    }

    public function getDurasiKerjaInMinutesAttribute()
    {
        if (!$this->jam_masuk || !$this->jam_keluar) {
            return 0;
        }

        $masuk = Carbon::parse($this->jam_masuk);
        $keluar = Carbon::parse($this->jam_keluar);

        // Handle shift that crosses midnight
        if ($keluar < $masuk) {
            $keluar->addDay();
        }

        return $keluar->diffInMinutes($masuk);
    }

    public function getStatusTextAttribute()
    {
        return $this->aktif ? 'Aktif' : 'Nonaktif';
    }

    public function getStatusBadgeClassAttribute()
    {
        return $this->aktif
            ? 'bg-green-100 text-green-800'
            : 'bg-red-100 text-red-800';
    }

    /**
     * Helper Methods
     */
    public function isActive()
    {
        return $this->aktif;
    }

    public function activate()
    {
        $this->update(['aktif' => true]);
    }

    public function deactivate()
    {
        $this->update(['aktif' => false]);
    }

    public function toggleStatus()
    {
        $this->update(['aktif' => !$this->aktif]);
    }

    /**
     * Check if current time is within shift hours
     */
    public function isCurrentlyActive()
    {
        if (!$this->aktif) {
            return false;
        }

        $now = now()->format('H:i');
        $jamMasuk = $this->jam_masuk->format('H:i');
        $jamKeluar = $this->jam_keluar->format('H:i');

        // Normal shift (same day)
        if ($jamMasuk <= $jamKeluar) {
            return $now >= $jamMasuk && $now <= $jamKeluar;
        }

        // Night shift (crosses midnight)
        return $now >= $jamMasuk || $now <= $jamKeluar;
    }

    /**
     * Check if time is late for this shift
     */
    public function isLate($checkTime)
    {
        $shiftStart = Carbon::parse($this->jam_masuk);
        $checkTime = Carbon::parse($checkTime);

        $lateThreshold = $shiftStart->addMinutes($this->toleransi_menit);

        return $checkTime > $lateThreshold;
    }

    /**
     * Calculate lateness in minutes
     */
    public function calculateLateness($checkTime)
    {
        if (!$this->isLate($checkTime)) {
            return 0;
        }

        $shiftStart = Carbon::parse($this->jam_masuk);
        $checkTime = Carbon::parse($checkTime);
        $lateThreshold = $shiftStart->addMinutes($this->toleransi_menit);

        return $checkTime->diffInMinutes($lateThreshold);
    }

    /**
     * Get shifts that are currently active
     */
    public static function getCurrentlyActiveShifts()
    {
        return self::aktif()
            ->get()
            ->filter(function ($shift) {
                return $shift->isCurrentlyActive();
            });
    }

    /**
     * Get attendance statistics for this shift
     */
    public function getAttendanceStats($startDate = null, $endDate = null)
    {
        $query = $this->attendances();

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal_absen', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('tanggal_absen', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('tanggal_absen', '<=', $endDate);
        }

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
     * Get employees assigned to this shift
     */
    public function getActiveEmployees()
    {
        return $this->users()
            ->where('status', 'aktif')
            ->where('role', 'karyawan')
            ->get();
    }

    /**
     * Count employees in this shift
     */
    public function getEmployeeCountAttribute()
    {
        return $this->users()
            ->where('status', 'aktif')
            ->where('role', 'karyawan')
            ->count();
    }

    /**
     * Get today's attendance for this shift
     */
    public function getTodayAttendance()
    {
        return $this->attendances()
            ->whereDate('tanggal_absen', today())
            ->with('user')
            ->get();
    }

    /**
     * Check if shift can be deleted
     */
    public function canBeDeleted()
    {
        // Cannot delete if there are users assigned to this shift
        if ($this->users()->where('status', 'aktif')->exists()) {
            return false;
        }

        // Cannot delete if there are attendance records
        if ($this->attendances()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Get deletion blocking reason
     */
    public function getDeletionBlockReason()
    {
        $activeUsers = $this->users()->where('status', 'aktif')->count();
        $attendanceCount = $this->attendances()->count();

        if ($activeUsers > 0) {
            return "Shift ini masih digunakan oleh {$activeUsers} karyawan aktif.";
        }

        if ($attendanceCount > 0) {
            return "Shift ini memiliki {$attendanceCount} record absensi.";
        }

        return null;
    }

    /**
     * Get shift performance metrics
     */
    public function getPerformanceMetrics($period = 'month')
    {
        $startDate = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        $endDate = match ($period) {
            'week' => now()->endOfWeek(),
            'month' => now()->endOfMonth(),
            'year' => now()->endOfYear(),
            default => now()->endOfMonth()
        };

        $stats = $this->getAttendanceStats($startDate, $endDate);
        $totalScheduled = $stats['total'];

        if ($totalScheduled === 0) {
            return [
                'attendance_rate' => 0,
                'punctuality_rate' => 0,
                'total_scheduled' => 0,
                'performance_grade' => 'N/A'
            ];
        }

        $attendanceRate = (($stats['hadir'] + $stats['terlambat']) / $totalScheduled) * 100;
        $punctualityRate = ($stats['hadir'] / $totalScheduled) * 100;

        // Determine performance grade
        $performanceGrade = match (true) {
            $attendanceRate >= 95 && $punctualityRate >= 90 => 'A',
            $attendanceRate >= 90 && $punctualityRate >= 85 => 'B',
            $attendanceRate >= 85 && $punctualityRate >= 80 => 'C',
            $attendanceRate >= 80 => 'D',
            default => 'F'
        };

        return [
            'attendance_rate' => round($attendanceRate, 2),
            'punctuality_rate' => round($punctualityRate, 2),
            'total_scheduled' => $totalScheduled,
            'performance_grade' => $performanceGrade,
            'stats' => $stats
        ];
    }

    /**
     * Get shift utilization rate
     */
    public function getUtilizationRate($date = null)
    {
        $date = $date ?? today();
        $totalEmployees = $this->getActiveEmployees()->count();

        if ($totalEmployees === 0) {
            return 0;
        }

        $scheduledToday = $this->attendances()
            ->whereDate('tanggal_absen', $date)
            ->count();

        return round(($scheduledToday / $totalEmployees) * 100, 2);
    }

    /**
     * Get monthly schedule distribution
     */
    public function getMonthlyDistribution($year = null, $month = null)
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        return $this->attendances()
            ->whereYear('tanggal_absen', $year)
            ->whereMonth('tanggal_absen', $month)
            ->selectRaw('
                       DAY(tanggal_absen) as day,
                       COUNT(*) as total_scheduled,
                       SUM(CASE WHEN status_absen = "menunggu" THEN 1 ELSE 0 END) as menunggu,
                       SUM(CASE WHEN status_absen = "hadir" THEN 1 ELSE 0 END) as present,
                       SUM(CASE WHEN status_absen = "terlambat" THEN 1 ELSE 0 END) as late,
                       SUM(CASE WHEN status_absen = "tidak_hadir" THEN 1 ELSE 0 END) as absent
                   ')
            ->groupBy('day')
            ->orderBy('day')
            ->get();
    }

    /**
     * Check if shift overlaps with another shift
     */
    public function overlapsWith(Shift $other)
    {
        $thisStart = Carbon::parse($this->jam_masuk);
        $thisEnd = Carbon::parse($this->jam_keluar);
        $otherStart = Carbon::parse($other->jam_masuk);
        $otherEnd = Carbon::parse($other->jam_keluar);

        // Handle shifts that cross midnight
        if ($thisEnd < $thisStart) {
            $thisEnd->addDay();
        }
        if ($otherEnd < $otherStart) {
            $otherEnd->addDay();
        }

        return $thisStart < $otherEnd && $otherStart < $thisEnd;
    }

    /**
     * Get conflicting shifts
     */
    public function getConflictingShifts()
    {
        return self::where('id', '!=', $this->id)
            ->aktif()
            ->get()
            ->filter(function ($shift) {
                return $this->overlapsWith($shift);
            });
    }

    /**
     * Validate shift times
     */
    public function validateShiftTimes()
    {
        $errors = [];

        // Check if times are set
        if (!$this->jam_masuk || !$this->jam_keluar) {
            $errors[] = 'Jam masuk dan jam keluar harus diisi.';
            return $errors;
        }

        // Check for overlapping shifts
        $conflicts = $this->getConflictingShifts();
        if ($conflicts->isNotEmpty()) {
            $conflictNames = $conflicts->pluck('nama')->join(', ');
            $errors[] = "Shift ini bentrok dengan shift: {$conflictNames}";
        }

        // Check minimum shift duration (e.g., at least 4 hours)
        $duration = $this->durasi_kerja_in_minutes;
        if ($duration < 240) { // 4 hours = 240 minutes
            $errors[] = 'Durasi shift minimal 4 jam.';
        }

        // Check maximum shift duration (e.g., max 12 hours)
        if ($duration > 720) { // 12 hours = 720 minutes
            $errors[] = 'Durasi shift maksimal 12 jam.';
        }

        return $errors;
    }

    /**
     * Get shift recommendations based on attendance patterns
     */
    public function getOptimizationRecommendations()
    {
        $recommendations = [];
        $metrics = $this->getPerformanceMetrics('month');

        if ($metrics['attendance_rate'] < 85) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Tingkat Kehadiran Rendah',
                'message' => 'Tingkat kehadiran shift ini di bawah 85%. Pertimbangkan untuk mengevaluasi jadwal atau memberikan insentif.',
                'action' => 'review_schedule'
            ];
        }

        if ($metrics['punctuality_rate'] < 80) {
            $recommendations[] = [
                'type' => 'info',
                'title' => 'Masalah Ketepatan Waktu',
                'message' => 'Banyak karyawan terlambat di shift ini. Pertimbangkan menambah toleransi atau mengubah jam mulai.',
                'action' => 'adjust_tolerance'
            ];
        }

        $utilizationRate = $this->getUtilizationRate();
        if ($utilizationRate < 60) {
            $recommendations[] = [
                'type' => 'suggestion',
                'title' => 'Utilisasi Rendah',
                'message' => 'Shift ini kurang dimanfaatkan. Pertimbangkan untuk mengoptimalkan penjadwalan.',
                'action' => 'optimize_scheduling'
            ];
        }

        return $recommendations;
    }

    /**
     * Generate shift report
     */
    public function generateReport($period = 'month')
    {
        $metrics = $this->getPerformanceMetrics($period);
        $recommendations = $this->getOptimizationRecommendations();
        $distribution = $this->getMonthlyDistribution();

        return [
            'shift_info' => [
                'id' => $this->id,
                'nama' => $this->nama,
                'jam_kerja' => $this->jam_masuk_formatted . ' - ' . $this->jam_keluar_formatted,
                'durasi' => $this->durasi_kerja,
                'toleransi' => $this->toleransi_menit . ' menit',
                'status' => $this->status_text,
                'employee_count' => $this->employee_count
            ],
            'performance' => $metrics,
            'recommendations' => $recommendations,
            'distribution' => $distribution,
            'utilization_rate' => $this->getUtilizationRate(),
            'generated_at' => now()->format('Y-m-d H:i:s')
        ];
    }
}
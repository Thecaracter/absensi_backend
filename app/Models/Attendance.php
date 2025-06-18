<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    /**
     * Kolom yang bisa diisi mass assignment
     */
    protected $fillable = [
        'user_id',
        'shift_id',
        'tanggal_absen',
        'jam_masuk',
        'jam_keluar',
        'foto_masuk',
        'foto_keluar',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_keluar',
        'longitude_keluar',
        'status_masuk',
        'status_keluar',
        'status_absen',
        'menit_terlambat',
        'menit_lembur',
        'catatan_admin',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'tanggal_absen' => 'date',
        'jam_masuk' => 'datetime:H:i:s',
        'jam_keluar' => 'datetime:H:i:s',
        'latitude_masuk' => 'decimal:8',
        'longitude_masuk' => 'decimal:8',
        'latitude_keluar' => 'decimal:8',
        'longitude_keluar' => 'decimal:8',
        'menit_terlambat' => 'integer',
        'menit_lembur' => 'integer',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Shift
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Scope untuk absensi hari ini
     */
    public function scopeHariIni($query)
    {
        return $query->where('tanggal_absen', today());
    }

    /**
     * Scope untuk absensi bulan ini
     */
    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal_absen', now()->month)
            ->whereYear('tanggal_absen', now()->year);
    }

    /**
     * Scope untuk status hadir (termasuk terlambat)
     */
    public function scopeHadir($query)
    {
        return $query->whereIn('status_absen', ['hadir', 'terlambat']);
    }

    /**
     * Scope untuk status terlambat
     */
    public function scopeTerlambat($query)
    {
        return $query->where('status_absen', 'terlambat');
    }

    /**
     * Scope untuk status tidak hadir
     */
    public function scopeTidakHadir($query)
    {
        return $query->where('status_absen', 'tidak_hadir');
    }

    /**
     * Scope untuk absensi yang menunggu approval
     */
    public function scopeMenungguApproval($query)
    {
        return $query->where(function ($q) {
            $q->where('status_masuk', 'menunggu')
                ->orWhere('status_keluar', 'menunggu');
        });
    }

    /**
     * Get status absen dalam format text yang bisa dibaca
     */
    public function getStatusAbsenText()
    {
        switch ($this->status_absen) {
            case 'hadir':
                return 'Hadir';
            case 'terlambat':
                return 'Terlambat';
            case 'tidak_hadir':
                return 'Tidak Hadir';
            case 'izin':
                return 'Izin';
            case 'sakit':
                return 'Sakit';
            case 'cuti':
                return 'Cuti';
            case 'menunggu':
                return 'Menunggu';
            case 'belum_absen':
                return 'Belum Absen';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get foto masuk URL
     */
    public function getFotoMasukUrlAttribute()
    {
        if ($this->foto_masuk) {
            return asset($this->foto_masuk);
        }
        return null;
    }

    /**
     * Get foto keluar URL
     */
    public function getFotoKeluarUrlAttribute()
    {
        if ($this->foto_keluar) {
            return asset($this->foto_keluar);
        }
        return null;
    }

    /**
     * Cek apakah sudah absen masuk
     */
    public function sudahAbsenMasuk()
    {
        return !is_null($this->jam_masuk);
    }

    /**
     * Cek apakah sudah absen keluar
     */
    public function sudahAbsenKeluar()
    {
        return !is_null($this->jam_keluar);
    }

    /**
     * Cek apakah absensi lengkap (masuk + keluar)
     */
    public function absensiLengkap()
    {
        return $this->sudahAbsenMasuk() && $this->sudahAbsenKeluar();
    }

    /**
     * Hitung durasi kerja dalam menit
     */
    public function getDurasiKerjaMenit()
    {
        if (!$this->absensiLengkap()) {
            return 0;
        }

        $jamMasuk = Carbon::parse($this->jam_masuk);
        $jamKeluar = Carbon::parse($this->jam_keluar);

        return $jamMasuk->diffInMinutes($jamKeluar);
    }

    /**
     * Hitung durasi kerja dalam format jam:menit
     */
    public function getDurasiKerjaFormatted()
    {
        $totalMenit = $this->getDurasiKerjaMenit();
        $jam = floor($totalMenit / 60);
        $menit = $totalMenit % 60;

        if ($totalMenit == 0) {
            return '-';
        }

        return sprintf('%d:%02d', $jam, $menit);
    }

    /**
     * Cek apakah absensi dalam toleransi waktu
     */
    public function isInTolerance()
    {
        if (!$this->shift || !$this->jam_masuk) {
            return true;
        }

        $jamMasukActual = Carbon::parse($this->jam_masuk);
        $jamMasukScheduled = Carbon::parse($this->tanggal_absen->format('Y-m-d') . ' ' . $this->shift->jam_masuk->format('H:i:s'));
        $toleransiMenit = $this->shift->toleransi_menit;

        return $jamMasukActual->lte($jamMasukScheduled->addMinutes($toleransiMenit));
    }

    /**
     * Hitung berapa menit terlambat berdasarkan shift
     */
    public function calculateLateness()
    {
        if (!$this->shift || !$this->jam_masuk) {
            return 0;
        }

        $jamMasukActual = Carbon::parse($this->jam_masuk);
        $jamMasukScheduled = Carbon::parse($this->tanggal_absen->format('Y-m-d') . ' ' . $this->shift->jam_masuk->format('H:i:s'));
        $toleransiMenit = $this->shift->toleransi_menit;

        $batasToleransi = $jamMasukScheduled->addMinutes($toleransiMenit);

        if ($jamMasukActual->gt($batasToleransi)) {
            return $jamMasukActual->diffInMinutes($jamMasukScheduled);
        }

        return 0;
    }

    /**
     * Auto update status based on lateness
     */
    public function updateStatusBasedOnLateness()
    {
        if ($this->status_absen === 'izin' || !$this->jam_masuk || !$this->shift) {
            return false;
        }

        $menitTerlambat = $this->calculateLateness();

        if ($menitTerlambat > 0) {
            $this->update([
                'status_absen' => 'terlambat',
                'menit_terlambat' => $menitTerlambat,
            ]);
            return true;
        } else {
            $this->update([
                'status_absen' => 'hadir',
                'menit_terlambat' => 0,
            ]);
            return true;
        }
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClass()
    {
        switch ($this->status_absen) {
            case 'hadir':
                return 'bg-green-100 text-green-800';
            case 'terlambat':
                return 'bg-yellow-100 text-yellow-800';
            case 'tidak_hadir':
                return 'bg-red-100 text-red-800';
            case 'izin':
                return 'bg-blue-100 text-blue-800';
            case 'sakit':
                return 'bg-purple-100 text-purple-800';
            case 'cuti':
                return 'bg-indigo-100 text-indigo-800';
            case 'menunggu':
                return 'bg-orange-100 text-orange-800';
            case 'belum_absen':
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    /**
     * Get approval status text
     */
    public function getApprovalStatusText()
    {
        $statuses = [];

        if ($this->status_masuk === 'menunggu') {
            $statuses[] = 'Masuk Pending';
        } elseif ($this->status_masuk === 'disetujui') {
            $statuses[] = 'Masuk OK';
        } elseif ($this->status_masuk === 'ditolak') {
            $statuses[] = 'Masuk Ditolak';
        }

        if ($this->jam_keluar) {
            if ($this->status_keluar === 'menunggu') {
                $statuses[] = 'Keluar Pending';
            } elseif ($this->status_keluar === 'disetujui') {
                $statuses[] = 'Keluar OK';
            } elseif ($this->status_keluar === 'ditolak') {
                $statuses[] = 'Keluar Ditolak';
            }
        }

        return implode(', ', $statuses) ?: 'No Status';
    }

    /**
     * Check if needs approval
     */
    public function needsApproval()
    {
        return $this->status_masuk === 'menunggu' || $this->status_keluar === 'menunggu';
    }

    /**
     * Get formatted late info
     */
    public function getLateInfoFormatted()
    {
        if ($this->menit_terlambat > 0) {
            return "Terlambat {$this->menit_terlambat} menit";
        }

        if ($this->status_absen === 'hadir') {
            return 'Tepat waktu';
        }

        return '-';
    }

    /**
     * Get overtime info formatted
     */
    public function getOvertimeInfoFormatted()
    {
        if ($this->menit_lembur > 0) {
            $jam = floor($this->menit_lembur / 60);
            $menit = $this->menit_lembur % 60;

            if ($jam > 0) {
                return sprintf('%d jam %d menit', $jam, $menit);
            } else {
                return sprintf('%d menit', $menit);
            }
        }

        return '-';
    }

    /**
     * Scope for attendances with photos
     */
    public function scopeWithPhotos($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('foto_masuk')
                ->orWhereNotNull('foto_keluar');
        });
    }

    /**
     * Scope for today's pending approvals
     */
    public function scopeTodayPendingApprovals($query)
    {
        return $query->hariIni()->menungguApproval();
    }

    /**
     * Scope for late attendances today
     */
    public function scopeLateToday($query)
    {
        return $query->hariIni()->terlambat();
    }
}
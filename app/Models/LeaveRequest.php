<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use HasFactory;

    /**
     * Kolom yang bisa diisi mass assignment
     */
    protected $fillable = [
        'user_id',
        'jenis_izin',
        'tanggal_mulai',
        'tanggal_selesai',
        'total_hari',
        'alasan',
        'lampiran',
        'status',
        'disetujui_oleh',
        'catatan_admin',
        'tanggal_persetujuan',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_persetujuan' => 'datetime',
    ];



    /**
     * Relasi ke User (yang mengajukan izin)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke User (admin yang approve)
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }



    /**
     * Scope untuk izin yang menunggu
     */
    public function scopeMenunggu($query)
    {
        return $query->where('status', 'menunggu');
    }

    /**
     * Scope untuk izin yang disetujui
     */
    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }

    /**
     * Scope untuk izin yang ditolak
     */
    public function scopeDitolak($query)
    {
        return $query->where('status', 'ditolak');
    }

    /**
     * Scope untuk izin bulan ini
     */
    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal_mulai', now()->month)
            ->whereYear('tanggal_mulai', now()->year);
    }



    /**
     * Cek apakah izin masih menunggu
     */
    public function isMenunggu()
    {
        return $this->status === 'menunggu';
    }

    /**
     * Cek apakah izin sudah disetujui
     */
    public function isDisetujui()
    {
        return $this->status === 'disetujui';
    }

    /**
     * Cek apakah izin ditolak
     */
    public function isDitolak()
    {
        return $this->status === 'ditolak';
    }

    /**
     * Cek apakah izin masih bisa diedit
     */
    public function bisaDiedit()
    {
        return $this->isMenunggu() && $this->tanggal_mulai > today();
    }

    /**
     * Cek apakah izin masih bisa dibatalkan
     */
    public function bisaDibatalkan()
    {
        return $this->isDisetujui() && $this->tanggal_mulai > today();
    }

    /**
     * Get text jenis izin
     */
    public function getJenisIzinText()
    {
        return match ($this->jenis_izin) {
            'sakit' => 'Sakit',
            'cuti_tahunan' => 'Cuti Tahunan',
            'keperluan_pribadi' => 'Keperluan Pribadi',
            'darurat' => 'Darurat',
            'lainnya' => 'Lainnya',
            default => 'Unknown'
        };
    }

    /**
     * Get CSS class untuk badge status
     */
    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            'menunggu' => 'badge-warning',
            'disetujui' => 'badge-success',
            'ditolak' => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    /**
     * Get text status
     */
    public function getStatusText()
    {
        return match ($this->status) {
            'menunggu' => 'Menunggu Persetujuan',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => 'Unknown'
        };
    }

    /**
     * Get text durasi izin
     */
    public function getDurasiText()
    {
        if ($this->total_hari == 1) {
            return '1 hari';
        }
        return $this->total_hari . ' hari';
    }

    /**
     * Get URL lampiran - langsung dari public directory
     */
    public function getLampiranUrlAttribute()
    {
        if ($this->lampiran) {
            return asset($this->lampiran);
        }
        return null;
    }

    /**
     * Otomatis hitung total hari sebelum save
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($leaveRequest) {
            if ($leaveRequest->tanggal_mulai && $leaveRequest->tanggal_selesai) {
                $tanggalMulai = Carbon::parse($leaveRequest->tanggal_mulai);
                $tanggalSelesai = Carbon::parse($leaveRequest->tanggal_selesai);
                $leaveRequest->total_hari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;
            }
        });
    }
}
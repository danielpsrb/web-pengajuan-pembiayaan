<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    use HasFactory;

    /**
     * Batas nominal pengajuan yang masih dapat disetujui (Rp).
     */
    public const MAKS_NOMINAL_DISETUJUI = 200_000_000;

    /**
     * Batas tenor maksimal (bulan).
     */
    public const MAKS_TENOR_BULAN = 24;

    /**
     * Batas minimal pendapatan bulanan nasabah agar dapat mengajukan (Rp).
     */
    public const MIN_PENDAPATAN_BULANAN = 1_000_000;

    /**
     * Batas maksimal jumlah pengajuan yang boleh diajukan oleh nasabah yang sama.
     */
    public const MAKS_PENGAJUAN_PER_NASABAH = 3;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'nama_lengkap',
        'tipe_pengajuan',
        'nominal_pengajuan',
        'tenor',
        'pendapatan_bulanan',
        'catatan',
        'cicilan_per_bulan',
        'tanggal_pengajuan',
        'status',
    ];

    protected $casts = [
        'nominal_pengajuan' => 'decimal:2',
        'pendapatan_bulanan' => 'decimal:2',
        'cicilan_per_bulan' => 'decimal:2',
        'tanggal_pengajuan' => 'date',
    ];

    /**
     * Hitung cicilan per bulan berdasarkan nominal pengajuan dan tenor.
     * Menggunakan skema flat (tanpa bunga) sesuai lingkup coding test.
     */
    public static function hitungCicilanPerBulan(float $nominal, int $tenor): float
    {
        if ($tenor <= 0) {
            return 0;
        }

        return round($nominal / $tenor, 2);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            default => 'Menunggu',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
            self::STATUS_REJECTED => 'bg-rose-100 text-rose-700 ring-rose-600/20',
            default => 'bg-amber-100 text-amber-700 ring-amber-600/20',
        };
    }
}

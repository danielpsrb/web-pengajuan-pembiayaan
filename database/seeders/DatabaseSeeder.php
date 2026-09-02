<?php

namespace Database\Seeders;

use App\Models\Pengajuan;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed contoh data pengajuan agar tampilan aplikasi mudah dicek.
     */
    public function run(): void
    {
        $contoh = [
            [
                'nama_lengkap' => 'Budi Santoso',
                'tipe_pengajuan' => 'Sepeda Motor',
                'nominal_pengajuan' => 18000000,
                'tenor' => 12,
                'pendapatan_bulanan' => 4500000,
                'catatan' => 'Pengajuan untuk motor baru.',
                'tanggal_pengajuan' => now()->subDays(5)->toDateString(),
                'status' => Pengajuan::STATUS_APPROVED,
            ],
            [
                'nama_lengkap' => 'Siti Aminah',
                'tipe_pengajuan' => 'Mobil',
                'nominal_pengajuan' => 150000000,
                'tenor' => 24,
                'pendapatan_bulanan' => 12000000,
                'catatan' => 'Mobil keluarga bekas.',
                'tanggal_pengajuan' => now()->subDays(2)->toDateString(),
                'status' => Pengajuan::STATUS_PENDING,
            ],
            [
                'nama_lengkap' => 'Andi Wijaya',
                'tipe_pengajuan' => 'Multiguna',
                'nominal_pengajuan' => 25000000,
                'tenor' => 18,
                'pendapatan_bulanan' => 3000000,
                'catatan' => null,
                'tanggal_pengajuan' => now()->subDay()->toDateString(),
                'status' => Pengajuan::STATUS_REJECTED,
            ],
            [
                'nama_lengkap' => 'Dewi Lestari',
                'tipe_pengajuan' => 'Mobil',
                'nominal_pengajuan' => 250000000,
                'tenor' => 24,
                'pendapatan_bulanan' => 20000000,
                'catatan' => 'Melebihi batas nominal yang dapat disetujui.',
                'tanggal_pengajuan' => now()->toDateString(),
                'status' => Pengajuan::STATUS_PENDING,
            ],
        ];

        foreach ($contoh as $item) {
            Pengajuan::create([
                ...$item,
                'cicilan_per_bulan' => Pengajuan::hitungCicilanPerBulan($item['nominal_pengajuan'], $item['tenor']),
            ]);
        }
    }
}

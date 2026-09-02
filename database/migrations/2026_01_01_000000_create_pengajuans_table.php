<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->enum('tipe_pengajuan', ['Sepeda Motor', 'Mobil', 'Multiguna']);
            $table->decimal('nominal_pengajuan', 15, 2);
            $table->unsignedInteger('tenor')->comment('Tenor dalam bulan');
            $table->decimal('pendapatan_bulanan', 15, 2);
            $table->decimal('cicilan_per_bulan', 15, 2)->comment('Tagihan nasabah per bulan (nominal / tenor)');
            $table->text('catatan')->nullable();
            $table->date('tanggal_pengajuan');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            $table->index(['nama_lengkap']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};

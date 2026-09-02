<?php

use App\Http\Controllers\PengajuanController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/pengajuan');

Route::prefix('pengajuan')->name('pengajuan.')->group(function () {
    Route::get('/', [PengajuanController::class, 'index'])->name('index');
    Route::post('/', [PengajuanController::class, 'store'])->name('store');
    Route::get('/{pengajuan}', [PengajuanController::class, 'show'])->name('show');
    Route::post('/{pengajuan}/approve', [PengajuanController::class, 'approve'])->name('approve');
    Route::post('/{pengajuan}/reject', [PengajuanController::class, 'reject'])->name('reject');
});

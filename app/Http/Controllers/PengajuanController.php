<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PengajuanController extends Controller
{
    /**
     * Tampilkan daftar pengajuan sekaligus form tambah pengajuan.
     */
    public function index(): \Illuminate\View\View
    {
        $pengajuans = Pengajuan::query()->latest('tanggal_pengajuan')->latest('id')->get();

        return view('pengajuan.index', [
            'pengajuans' => $pengajuans,
            'tipeOptions' => ['Sepeda Motor', 'Mobil', 'Multiguna'],
            'maksTenor' => Pengajuan::MAKS_TENOR_BULAN,
            'maksNominalDisetujui' => Pengajuan::MAKS_NOMINAL_DISETUJUI,
        ]);
    }

    /**
     * Simpan pengajuan kredit baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'tipe_pengajuan' => ['required', 'in:Sepeda Motor,Mobil,Multiguna'],
            'nominal_pengajuan' => ['required', 'numeric', 'min:1'],
            'tenor' => ['required', 'integer', 'min:1', 'max:'.Pengajuan::MAKS_TENOR_BULAN],
            'pendapatan_bulanan' => ['required', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'tenor.max' => 'Tenor pinjaman maksimal '.Pengajuan::MAKS_TENOR_BULAN.' bulan.',
        ]);

        // Behaviour 1: pendapatan bulanan nasabah < Rp 1.000.000
        if ((float) $validated['pendapatan_bulanan'] < Pengajuan::MIN_PENDAPATAN_BULANAN) {
            throw ValidationException::withMessages([
                'pendapatan_bulanan' => 'Nasabah belum dapat mengajukan pinjaman',
            ]);
        }

        // Behaviour 4: maksimal pengajuan nasabah adalah sebanyak 3 kali (dicek per nama nasabah).
        $jumlahPengajuanSebelumnya = Pengajuan::query()
            ->whereRaw('LOWER(nama_lengkap) = ?', [mb_strtolower(trim($validated['nama_lengkap']))])
            ->count();

        if ($jumlahPengajuanSebelumnya >= Pengajuan::MAKS_PENGAJUAN_PER_NASABAH) {
            throw ValidationException::withMessages([
                'nama_lengkap' => 'Nasabah ini sudah mencapai batas maksimal '.Pengajuan::MAKS_PENGAJUAN_PER_NASABAH.' kali pengajuan.',
            ]);
        }

        $cicilanPerBulan = Pengajuan::hitungCicilanPerBulan(
            (float) $validated['nominal_pengajuan'],
            (int) $validated['tenor']
        );

        Pengajuan::create([
            ...$validated,
            'cicilan_per_bulan' => $cicilanPerBulan,
            'tanggal_pengajuan' => now()->toDateString(),
            'status' => Pengajuan::STATUS_PENDING,
        ]);

        return redirect()
            ->route('pengajuan.index')
            ->with('success', 'Pengajuan kredit berhasil dicatat.');
    }

    /**
     * Tampilkan detail pengajuan (dipakai untuk modal detail via fetch AJAX).
     */
    public function show(Pengajuan $pengajuan): JsonResponse
    {
        return response()->json([
            'id' => $pengajuan->id,
            'nama_lengkap' => $pengajuan->nama_lengkap,
            'tipe_pengajuan' => $pengajuan->tipe_pengajuan,
            'nominal_pengajuan' => (float) $pengajuan->nominal_pengajuan,
            'tenor' => $pengajuan->tenor,
            'pendapatan_bulanan' => (float) $pengajuan->pendapatan_bulanan,
            'cicilan_per_bulan' => (float) $pengajuan->cicilan_per_bulan,
            'catatan' => $pengajuan->catatan,
            'tanggal_pengajuan' => $pengajuan->tanggal_pengajuan->format('d-m-Y'),
            'status' => $pengajuan->status,
            'status_label' => $pengajuan->statusLabel(),
        ]);
    }

    /**
     * Setujui pengajuan. Behaviour 2: nominal maksimal yang dapat disetujui adalah 200 juta.
     */
    public function approve(Pengajuan $pengajuan): RedirectResponse
    {
        if ((float) $pengajuan->nominal_pengajuan > Pengajuan::MAKS_NOMINAL_DISETUJUI) {
            return redirect()
                ->route('pengajuan.index')
                ->with('error', 'Pengajuan tidak dapat disetujui karena nominal melebihi batas maksimal Rp 200.000.000.');
        }

        $pengajuan->update(['status' => Pengajuan::STATUS_APPROVED]);

        return redirect()
            ->route('pengajuan.index')
            ->with('success', 'Pengajuan '.$pengajuan->nama_lengkap.' berhasil disetujui.');
    }

    /**
     * Tolak pengajuan.
     */
    public function reject(Pengajuan $pengajuan): RedirectResponse
    {
        $pengajuan->update(['status' => Pengajuan::STATUS_REJECTED]);

        return redirect()
            ->route('pengajuan.index')
            ->with('success', 'Pengajuan '.$pengajuan->nama_lengkap.' telah ditolak.');
    }
}

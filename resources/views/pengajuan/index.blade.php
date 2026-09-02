@extends('layouts.app')

@section('title', 'Daftar Pengajuan Kredit')

@section('content')
<div
    x-data="{
        showAddModal: {{ $errors->any() ? 'true' : 'false' }},
        showDetailModal: false,
        showConfirmModal: false,
        detail: null,
        detailLoading: false,
        confirmType: null,
        confirmId: null,
        confirmNama: null,
        async loadDetail(id) {
            this.detail = null;
            this.detailLoading = true;
            this.showDetailModal = true;
            try {
                const res = await fetch(`/pengajuan/${id}`);
                this.detail = await res.json();
            } finally {
                this.detailLoading = false;
            }
        },
        openConfirm(type, id, nama) {
            this.confirmType = type;
            this.confirmId = id;
            this.confirmNama = nama;
            this.showConfirmModal = true;
        },
        submitConfirm() {
            document.getElementById(`form-${this.confirmType}-${this.confirmId}`).submit();
        },
        formatRupiah(value) {
            if (value === null || value === undefined) return '-';
            return 'Rp ' + Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 });
        },
    }"
    @open-add-modal.window="showAddModal = true"
>
    {{-- Flash messages --}}
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Daftar Pengajuan</h2>
            <p class="text-sm text-slate-500 mt-0.5">Catat, pantau, dan proses pengajuan kredit nasabah.</p>
        </div>
        <button
            type="button"
            @click="showAddModal = true"
            class="inline-flex items-center gap-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium px-4 py-2.5 shadow-sm transition"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
            </svg>
            Tambah Pengajuan
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        <th class="px-4 py-3">Nasabah</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3 text-center">Tenor</th>
                        <th class="px-4 py-3 text-right">Tagihan / Bulan</th>
                        <th class="px-4 py-3">Tgl Pengajuan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pengajuans as $pengajuan)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $pengajuan->nama_lengkap }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $pengajuan->tipe_pengajuan }}</td>
                            <td class="px-4 py-3 text-right text-slate-700">Rp {{ number_format($pengajuan->nominal_pengajuan, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center text-slate-700">{{ $pengajuan->tenor }} bln</td>
                            <td class="px-4 py-3 text-right text-slate-700">Rp {{ number_format($pengajuan->cicilan_per_bulan, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $pengajuan->tanggal_pengajuan->format('d-m-Y') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $pengajuan->statusBadgeClass() }}">
                                    {{ $pengajuan->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button
                                        type="button"
                                        @click="loadDetail({{ $pengajuan->id }})"
                                        class="rounded-md px-2.5 py-1.5 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 transition"
                                    >Detail</button>

                                    @if ($pengajuan->status === 'pending')
                                        <button
                                            type="button"
                                            @click="openConfirm('approve', {{ $pengajuan->id }}, @js($pengajuan->nama_lengkap))"
                                            class="rounded-md px-2.5 py-1.5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 transition"
                                        >Setujui</button>
                                        <button
                                            type="button"
                                            @click="openConfirm('reject', {{ $pengajuan->id }}, @js($pengajuan->nama_lengkap))"
                                            class="rounded-md px-2.5 py-1.5 text-xs font-medium text-white bg-rose-600 hover:bg-rose-700 transition"
                                        >Tolak</button>
                                    @endif
                                </div>

                                {{-- Hidden forms actually submitted after confirmation --}}
                                <form id="form-approve-{{ $pengajuan->id }}" method="POST" action="{{ route('pengajuan.approve', $pengajuan) }}" class="hidden">
                                    @csrf
                                </form>
                                <form id="form-reject-{{ $pengajuan->id }}" method="POST" action="{{ route('pengajuan.reject', $pengajuan) }}" class="hidden">
                                    @csrf
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-slate-400">
                                Belum ada data pengajuan. Klik "Tambah Pengajuan" untuk mulai mencatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== MODAL: TAMBAH PENGAJUAN ===================== --}}
    <div
        x-show="showAddModal"
        x-cloak
        class="fixed inset-0 z-40 flex items-center justify-center p-4"
        style="display: none;"
    >
        <div class="absolute inset-0 bg-slate-900/50" @click="showAddModal = false"></div>

        <div
            x-show="showAddModal"
            x-transition
            class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
        >
            <form method="POST" action="{{ route('pengajuan.store') }}">
                @csrf
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Tambah Pengajuan Kredit</h3>
                    <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap Nasabah</label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required
                            class="w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm"
                            placeholder="cth. Budi Santoso">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipe Pengajuan</label>
                            <select name="tipe_pengajuan" required
                                class="w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm">
                                <option value="">Pilih tipe</option>
                                @foreach ($tipeOptions as $tipe)
                                    <option value="{{ $tipe }}" @selected(old('tipe_pengajuan') === $tipe)>{{ $tipe }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tenor (bulan)</label>
                            <input type="number" name="tenor" min="1" max="{{ $maksTenor }}" value="{{ old('tenor') }}" required
                                class="w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm"
                                placeholder="maks. {{ $maksTenor }} bulan">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nominal Pengajuan (Rp)</label>
                            <input type="number" name="nominal_pengajuan" min="1" step="1" value="{{ old('nominal_pengajuan') }}" required
                                class="w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm"
                                placeholder="cth. 15000000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Pendapatan Bulanan Nasabah (Rp)</label>
                            <input type="number" name="pendapatan_bulanan" min="0" step="1" value="{{ old('pendapatan_bulanan') }}" required
                                class="w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm"
                                placeholder="cth. 4500000">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                        <textarea name="catatan" rows="3"
                            class="w-full rounded-lg border-slate-300 focus:border-brand-500 focus:ring-brand-500 text-sm"
                            placeholder="Catatan tambahan (opsional)">{{ old('catatan') }}</textarea>
                    </div>

                    <p class="text-xs text-slate-400">
                        Ketentuan: pendapatan bulanan minimal Rp 1.000.000, tenor maksimal {{ $maksTenor }} bulan,
                        dan setiap nasabah maksimal mengajukan 3 kali.
                    </p>
                </div>

                <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button type="button" @click="showAddModal = false"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 transition">
                        Simpan Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL: DETAIL PENGAJUAN ===================== --}}
    <div
        x-show="showDetailModal"
        x-cloak
        class="fixed inset-0 z-40 flex items-center justify-center p-4"
        style="display: none;"
    >
        <div class="absolute inset-0 bg-slate-900/50" @click="showDetailModal = false"></div>

        <div x-show="showDetailModal" x-transition class="relative bg-white rounded-xl shadow-xl w-full max-w-lg">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900">Detail Pengajuan</h3>
                <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5">
                <template x-if="detailLoading">
                    <p class="text-sm text-slate-400">Memuat detail...</p>
                </template>

                <template x-if="!detailLoading && detail">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Nama Nasabah</dt>
                            <dd class="font-medium text-slate-900" x-text="detail.nama_lengkap"></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Tipe Pengajuan</dt>
                            <dd class="font-medium text-slate-900" x-text="detail.tipe_pengajuan"></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Nominal Pengajuan</dt>
                            <dd class="font-medium text-slate-900" x-text="formatRupiah(detail.nominal_pengajuan)"></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Tenor</dt>
                            <dd class="font-medium text-slate-900" x-text="detail.tenor + ' bulan'"></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Pendapatan Bulanan</dt>
                            <dd class="font-medium text-slate-900" x-text="formatRupiah(detail.pendapatan_bulanan)"></dd>
                        </div>
                        <div class="flex justify-between border-t border-slate-100 pt-3">
                            <dt class="text-slate-600 font-medium">Tagihan / Cicilan per Bulan</dt>
                            <dd class="font-semibold text-brand-700" x-text="formatRupiah(detail.cicilan_per_bulan)"></dd>
                        </div>
                        <p class="text-xs text-slate-400" x-show="detail.tenor">
                            Kalkulasi: nominal pengajuan &divide; tenor = <span x-text="formatRupiah(detail.nominal_pengajuan)"></span> &divide; <span x-text="detail.tenor"></span> bulan.
                        </p>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Tanggal Pengajuan</dt>
                            <dd class="font-medium text-slate-900" x-text="detail.tanggal_pengajuan"></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Status</dt>
                            <dd class="font-medium text-slate-900" x-text="detail.status_label"></dd>
                        </div>
                        <div>
                            <dt class="text-slate-500 mb-1">Catatan</dt>
                            <dd class="text-slate-700" x-text="detail.catatan || '-'"></dd>
                        </div>
                    </dl>
                </template>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-end">
                <button type="button" @click="showDetailModal = false"
                    class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: KONFIRMASI SETUJUI / TOLAK ===================== --}}
    <div
        x-show="showConfirmModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;"
    >
        <div class="absolute inset-0 bg-slate-900/50" @click="showConfirmModal = false"></div>

        <div x-show="showConfirmModal" x-transition class="relative bg-white rounded-xl shadow-xl w-full max-w-sm">
            <div class="px-6 py-5">
                <h3 class="text-base font-semibold text-slate-900" x-text="confirmType === 'approve' ? 'Setujui Pengajuan?' : 'Tolak Pengajuan?'"></h3>
                <p class="text-sm text-slate-500 mt-2">
                    Anda yakin ingin
                    <span x-text="confirmType === 'approve' ? 'menyetujui' : 'menolak'"></span>
                    pengajuan atas nama <span class="font-medium text-slate-800" x-text="confirmNama"></span>?
                    Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-2">
                <button type="button" @click="showConfirmModal = false"
                    class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button
                    type="button"
                    @click="submitConfirm()"
                    :class="confirmType === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'"
                    class="rounded-lg px-4 py-2 text-sm font-medium text-white transition"
                    x-text="confirmType === 'approve' ? 'Ya, Setujui' : 'Ya, Tolak'"
                ></button>
            </div>
        </div>
    </div>
</div>

@endsection

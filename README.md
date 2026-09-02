# Sistem Pengajuan Kredit — PT XYZ (Coding Test IT Department)

Prototype internal tool berbasis **Laravel 13** + **Tailwind CSS** untuk mencatat, memantau,
dan memproses (approve/reject) pengajuan kredit nasabah (kredit kendaraan & multiguna).

## Fitur

1. **Form Tambah Pengajuan** (modal) dengan field: nama lengkap nasabah, tipe pengajuan
   (Sepeda Motor / Mobil / Multiguna), nominal pengajuan, tenor (bulan), pendapatan bulanan
   nasabah, dan catatan.
2. **Tabel Daftar Pengajuan** menampilkan: nama nasabah, tipe pengajuan, nominal, tenor,
   tagihan/cicilan per bulan, tanggal pengajuan, status (Disetujui/Ditolak/Menunggu), dan
   aksi (Setujui, Tolak, Detail).
3. **Modal Detail Pengajuan** menampilkan kalkulasi cicilan per bulan berdasarkan nominal
   dan tenor.
4. **Dialog konfirmasi** sebelum menyetujui maupun menolak pengajuan.
5. **Validasi & business rules**:
   - Pendapatan bulanan nasabah **< Rp 1.000.000** → pengajuan ditolak sistem dengan pesan
     *"Nasabah belum dapat mengajukan pinjaman"*.
   - Nominal pengajuan yang **dapat disetujui maksimal Rp 200.000.000** (dicek saat tombol
     "Setujui" ditekan).
   - **Tenor maksimal 24 bulan** (divalidasi pada form).
   - Nasabah yang sama **maksimal mengajukan 3 kali** (dicek berdasarkan nama lengkap,
     tidak case-sensitive).

## Tech Stack

- **Laravel 13** (PHP 8.2+)
- **Tailwind CSS** (via CDN — tanpa perlu build step/Node.js agar mudah dijalankan)
- **Alpine.js** (via CDN) untuk interaksi modal & dialog konfirmasi tanpa reload halaman
- **SQLite** sebagai database default (tidak perlu setup MySQL terpisah)

> Catatan: Tailwind & Alpine dimuat lewat CDN (bukan lewat Vite/npm build) supaya proses
> menjalankan project ini seringan mungkin — cukup PHP & Composer, tanpa Node.js. Struktur
> project tetap standar Laravel sehingga bila diperlukan bisa dengan mudah dipindah ke
> pipeline Vite + Tailwind lokal.

## Struktur Kode Penting

```
app/Models/Pengajuan.php                 -> Model + konstanta business rule + kalkulasi cicilan
app/Http/Controllers/PengajuanController.php -> index, store, show (detail JSON), approve, reject
database/migrations/2026_01_01_000000_create_pengajuans_table.php -> skema tabel pengajuan
database/seeders/DatabaseSeeder.php      -> contoh data dummy
resources/views/pengajuan/index.blade.php -> halaman utama (tabel + modal tambah/detail/konfirmasi)
routes/web.php                           -> definisi route
```

## Cara Menjalankan Project

### 1. Prasyarat
- PHP >= 8.2 dengan ekstensi `pdo_sqlite`
- Composer

### 2. Instalasi

```bash
# clone / extract project, lalu masuk ke folder project
cd coding-test-laravel

# install dependency PHP
composer install

# salin file environment
cp .env.example .env

# generate application key
php artisan key:generate

# pastikan file database sqlite tersedia (sudah disertakan kosong di database/database.sqlite,
# jika tidak ada / terhapus, buat manual):
touch database/database.sqlite
```

### 3. Migrasi & Seeder (opsional, untuk contoh data)

```bash
php artisan migrate
php artisan db:seed        # opsional, mengisi beberapa contoh pengajuan
```

### 4. Jalankan Server

```bash
php artisan serve
```

Buka browser ke **http://localhost:8000** (otomatis diarahkan ke `/pengajuan`).

## Alur Penggunaan

1. Klik **"Tambah Pengajuan"** untuk membuka form dan mencatat pengajuan kredit baru.
2. Data pengajuan baru akan muncul di **Tabel Pengajuan** dengan status *Menunggu*.
3. Klik **"Detail"** pada baris pengajuan untuk melihat rincian & kalkulasi cicilan per bulan.
4. Klik **"Setujui"** atau **"Tolak"** untuk memproses pengajuan — sistem akan menampilkan
   dialog konfirmasi sebelum aksi dieksekusi.
5. Jika nominal pengajuan melebihi Rp 200.000.000 dan tombol "Setujui" ditekan, sistem akan
   menolak proses persetujuan dan menampilkan pesan peringatan (nominal tidak berubah,
   status tetap *Menunggu* sehingga pengajuan bisa ditolak manual bila diperlukan).

## Kalkulasi Cicilan

Untuk menjaga kesederhanaan sesuai lingkup coding test, cicilan per bulan dihitung secara
flat (tanpa bunga):

```
cicilan_per_bulan = nominal_pengajuan / tenor
```

Logika ini ada di `Pengajuan::hitungCicilanPerBulan()` sehingga mudah diganti dengan skema
bunga flat/efektif bila dibutuhkan di kemudian hari.

## Testing Cepat Skenario Business Rule

| Skenario                                          | Ekspektasi                                                    |
|----------------------------------------------------|-----------------------------------------------------------------|
| Pendapatan bulanan diisi < 1.000.000               | Muncul error "Nasabah belum dapat mengajukan pinjaman"          |
| Tenor diisi > 24                                    | Muncul error validasi tenor maksimal 24 bulan                   |
| Nasabah yang sama mengajukan untuk ke-4 kalinya     | Muncul error batas maksimal 3 kali pengajuan                    |
| Approve pengajuan dengan nominal > 200.000.000      | Approve ditolak sistem, status tetap Menunggu, muncul pesan error|
| Approve pengajuan dengan nominal <= 200.000.000     | Status berubah menjadi Disetujui                                 |
| Tolak pengajuan                                     | Status berubah menjadi Ditolak                                   |

## Catatan Tambahan

- Aplikasi ini murni internal tool (tanpa autentikasi/login) sesuai lingkup coding test yang
  menyebutkan penggunaan hanya oleh tim internal perusahaan.
- Struktur project mengikuti kerapian standar Laravel (Model - Controller - View, form
  request validation, route naming, dsb.) agar mudah dikembangkan lebih lanjut (mis.
  menambahkan autentikasi, role, atau riwayat perubahan status).

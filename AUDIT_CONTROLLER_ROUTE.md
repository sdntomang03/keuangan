# Audit Controller dan Route

Tanggal pemeriksaan: 15 Oktober 2026

Pemeriksaan mencakup seluruh file controller di `app/Http/Controllers`, action
route Laravel hasil `php artisan route:list`, serta referensi statis di
`app/`, `resources/`, `routes/`, dan `tests/`. "Tidak ditemukan referensi" di
bawah ini berarti tidak ada pemanggilan yang terdeteksi di kode repository;
hal itu tidak membuktikan route tidak dipakai oleh integrasi eksternal atau
diakses langsung.

## Controller dan method yang tidak terhubung

| Status | Controller / method | Temuan |
|---|---|---|
| **TIDAK TERPAKAI** | [`Coba`](app/Http/Controllers/Coba.php) | Tidak mempunyai route dan tidak ditemukan pemanggilan dari kode lain. Semua action publiknya (`index`, `banding`, `rkas`, `anggaran`) tidak terhubung. |
| **TIDAK TERPAKAI** | [`AkbController::updateIdKomponen`](app/Http/Controllers/AkbController.php) | Tidak didaftarkan sebagai action route. Inline edit AKB justru mengirim PATCH ke route `rkas.update.idkomponen`, yang ditangani `RkasController::updateIdKomponen`. |
| **TIDAK TERPAKAI** | [`EkskulController::cetakSemua`](app/Http/Controllers/EkskulController.php) | Tidak didaftarkan sebagai route dan tidak ditemukan pemanggilan dari kode lain. |
| **TIDAK TERPAKAI** | [`SuratController::cetakBundel`](app/Http/Controllers/SuratController.php) | Tidak didaftarkan sebagai route dan tidak ditemukan pemanggilan dari kode lain. Route `belanja.downloadBundel` adalah action berbeda (`downloadBundel`). |

## Kandidat route yang tidak dipakai di kode repository

Route berikut ada di registrasi Laravel tetapi tidak ditemukan pemanggil internal
berupa nama route maupun URL statis. Verifikasi access log, bookmark pengguna,
dan integrasi eksternal sebelum menghapusnya.

| Status | Nama route | Endpoint | Catatan |
|---|---|---|---|
| **KANDIDAT TIDAK TERPAKAI** | `admin.users.create` | `GET /admin/users/create` | Form tambah user tersedia sebagai modal pada halaman index dan mengirim langsung ke route `store`; tidak ditemukan tautan ke halaman `create`. |
| **KANDIDAT TIDAK TERPAKAI** | `api.npd.saldo` | `GET /api/npd/cek-saldo` | Tidak ditemukan pemanggil di view atau JavaScript aplikasi. |
| **KANDIDAT TIDAK TERPAKAI** | `belanja.export_excel` | `GET /belanja/export-excel` | Tidak ditemukan pemanggil di view atau JavaScript aplikasi. |
| **KANDIDAT TIDAK TERPAKAI** | `belanja.downloadBundel` | `GET /belanja/{id}/download-bundel` | Tidak ditemukan pemanggil di view atau JavaScript aplikasi. |
| **KANDIDAT TIDAK TERPAKAI** | `cetak.cover` | `GET /cetak-cover` | Tidak ditemukan pemanggil di view atau JavaScript aplikasi. |
| **KANDIDAT TIDAK TERPAKAI** | `ekskul.edit` | `GET /ekskul/edit/{id}` | Tidak ditemukan pemanggil di view atau JavaScript aplikasi. |
| **KANDIDAT TIDAK TERPAKAI** | `rkas.rincian` | `GET /rkas/rincian` | Tidak ditemukan pemanggil di view atau JavaScript aplikasi. |
| **KANDIDAT TIDAK TERPAKAI** | `surat.cetak_satuan` | `GET /surat/cetak-satuan/{id}/{jenis}` | Tidak ditemukan pemanggil di view atau JavaScript aplikasi. |

Route seperti `api.rkas.data`, `belanja.json`, `penerimaan.edit`, `sts.edit`,
`rkas.update.idkomponen`, `kegiatan.cek_komponen`, dan
`admin.korek.update_jenis_belanja` **tidak** dimasukkan sebagai kandidat:
pemanggilnya menggunakan URL yang dirakit langsung di JavaScript, bukan nama
route Laravel.

## Route resource dengan action controller yang tidak tersedia

Route resource di bawah menghasilkan endpoint yang action-nya belum ada di
controller. Ini bukan sekadar kandidat route tak terpakai: jika endpoint
tersebut diakses, request akan gagal karena method handler tidak tersedia.

| Route resource | Action yang tidak tersedia |
|---|---|
| `admin.sekolah` | `show`, `destroy` pada [`Admin\SekolahController`](app/Http/Controllers/Admin/SekolahController.php) |
| `admin.users` | `show` pada [`Admin\UserController`](app/Http/Controllers/Admin/UserController.php) |
| `setting.rekanan` | `show` pada [`RekananController`](app/Http/Controllers/RekananController.php) |
| `setting.kegiatan` | `show` pada [`KegiatanController`](app/Http/Controllers/KegiatanController.php) |

Pertimbangkan membatasi route resource dengan `only()` atau `except()` sesuai
action yang memang didukung, atau lengkapi method controller jika endpoint
tersebut dibutuhkan.

## Validasi

- `php artisan route:list --json` berhasil dijalankan untuk memetakan route.
- `php artisan test`: 26 lulus, 3 gagal. Kegagalan yang terlihat adalah dua
  test update password menerima 403 dan `BelanjaTest` tidak menemukan
  `Database\Factories\SekolahFactory`; audit ini hanya menambahkan dokumentasi,
  tidak mengubah kode aplikasi.

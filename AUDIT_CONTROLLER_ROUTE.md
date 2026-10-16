# Audit Controller, Route, dan Keamanan

Tanggal pemeriksaan: 15 Oktober 2026

## Pembersihan yang dilakukan

Menghapus controller/action dan view yang tidak memiliki route atau pemanggil
internal:

- `Coba` dan empat view di `resources/views/coba/`.
- `CetakController::cetakCover` dan view `cetak_cover`.
- `AkbController::updateIdKomponen` (edit AKB menggunakan action milik
  `RkasController`).
- `EkskulController::cetakSemua` dan route ekskul yang tidak memiliki action
  (`edit`, `update`), beserta view `ekskul.edit`.
- `Admin\UserController::create` dan view `admin.users.tambah` (form tambah user
  tersedia sebagai modal pada halaman index).
- `RkasController::rincian` dan route tak terpakai `rkas.rincian`. View
  `resources/views/akb/rkas.blade.php` tetap digunakan oleh
  `AkbController::rincian` pada route `akb.rincian` dan dipertahankan.
- `SuratController::cetakSatuan` dan `SuratController::cetakBundel`.
- Route yang tak memiliki pemanggil internal: `api.npd.saldo`,
  `belanja.export_excel`, `belanja.downloadBundel`, `cetak.cover`,
  `ekskul.edit`, `rkas.rincian`, dan `surat.cetak_satuan`.

Resource routes dibatasi agar tidak mengekspos action yang tidak tersedia:
`admin.sekolah` tidak lagi membuat `show`/`destroy`, `admin.users` tidak lagi
membuat `show`/`create`, dan resource `setting.rekanan` serta
`setting.kegiatan` tidak lagi membuat `show`.

Pembersihan didasarkan pada pencarian rute Laravel dan referensi dalam
`app/`, `resources/`, `routes/`, serta `tests/`. Itu tidak mendeteksi pemakaian
oleh klien eksternal atau URL yang disimpan/bookmark. Route yang dihapus perlu
dipulihkan bila masih dipakai integrasi di luar repository.

## Temuan keamanan

| Severity | Lokasi | Temuan | Status |
|---|---|---|---|
| HIGH | `app/Http/Controllers/EkskulController.php:362,385`; `resources/views/ekskul/cetak_absensi.blade.php:373` | Nilai `materi` dapat disimpan oleh user berizin dan dirender sebagai HTML tanpa escape. Stored XSS dapat berjalan saat user lain membuka absensi. | Diperbaiki dengan escaping pada seluruh output materi di view ekskul. |
| HIGH | `app/Http/Controllers/BelanjaController.php:309,359,414` | `show`, `edit`, dan `update` mengambil belanja berdasarkan ID tanpa pembatasan sekolah/anggaran pengguna. User berizin input-belanja berpotensi membaca atau mengubah transaksi sekolah lain. | Diperbaiki dengan scope sekolah untuk semua operasi berbasis ID; rincian update dibatasi ke transaksi induknya. |

Temuan berasal dari review kode dan route statis, bukan exploit pada deployment
hidup. Regression test `BelanjaAuthorizationTest` memverifikasi bahwa user tidak
dapat membaca atau mengubah transaksi sekolah lain dan tetap dapat membaca
transaksi sekolahnya sendiri.

## Hashids

`vinkla/hashids` belum dipasang. Hashids dapat membuat ID URL tidak berurutan
dan lebih sulit ditebak, tetapi bukan perlindungan akses: setiap endpoint tetap
harus memeriksa kepemilikan/sekolah/permission. Temuan akses lintas-sekolah di
atas harus diperbaiki dengan authorization/scoping meskipun ID dienkode.

## Validasi

- `php artisan route:list --json` berhasil.
- Semua action route aplikasi lolos pemeriksaan keberadaan class dan method.
- `php artisan test --filter=BelanjaAuthorizationTest`: lulus (1 test, 5
  assertions).
- `php artisan test`: 27 lulus, 3 gagal. Dua test `PasswordUpdateTest` menerima
  403; `BelanjaTest` gagal karena `Database\Factories\SekolahFactory` tidak
  ditemukan. Kegagalan yang sama sudah ada sebelum pembersihan ini.

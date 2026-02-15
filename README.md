<p align="center">
  <a href="#" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
<img src="https://img.shields.io/badge/Laravel-11.x-red?style=for-the-badge&logo=laravel" alt="Laravel Version">
<img src="https://img.shields.io/badge/Tailwind-CSS-blue?style=for-the-badge&logo=tailwind-css" alt="Tailwind">
<img src="https://img.shields.io/badge/Alpine.js-Enabled-cyan?style=for-the-badge&logo=alpine.js" alt="Alpine.js">
<img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License">
</p>

## 💰 Tentang SIKS (Sistem Informasi Keuangan Sekolah)

**SIKS** adalah platform manajemen keuangan sekolah berbasis web yang dirancang untuk mengelola anggaran secara transparan dan akuntabel. Aplikasi ini memfasilitasi alur kerja dari perencanaan (RKAS), penarikan dana (NPD), hingga pelaporan belanja riil.

Aplikasi ini memastikan bahwa setiap rupiah yang dikeluarkan memiliki dasar perencanaan yang kuat dan terdokumentasi dengan baik dalam Buku Kas Umum (BKU).



---

## 🚀 Fitur Utama

- **Manajemen RKAS**: Penyusunan anggaran per tahun yang dibagi ke dalam 4 Triwulan.
- **Nota Penarikan Dana (NPD)**: 
  - Input pengajuan dana massal per triwulan.
  - Validasi sisa pagu anggaran secara otomatis.
  - Penomoran dokumen otomatis (Format: `000/NPD/TAHUN`).
- **Realisasi Belanja**:
  - Pencatatan nota belanja berdasarkan NPD yang sudah cair.
  - Perhitungan otomatis **Subtotal + PPN** untuk memotong saldo NPD.
- **Monitoring & Reporting**:
  - Pelacakan sisa dana di tangan bendahara secara real-time.
  - Dashboard monitoring triwulanan.
- **Security**: Autentikasi multi-user dengan proteksi data berbasis `sekolah_id`.

---

## 🛠️ Tech Stack

- **Framework:** Laravel 11
- **Frontend:** Tailwind CSS & Alpine.js (Livewire ready)
- **Database:** MySQL 8.0
- **Icons:** Heroicons

---

## 📐 Alur Logika Keuangan

Sistem menggunakan relasi data yang ketat untuk menjaga integritas laporan. Berikut adalah alur integrasi datanya:



1. **Planning (RKAS)**: Menentukan pagu anggaran per kode akun kegiatan.
2. **Request (NPD)**: Mengambil dana dari RKAS untuk dipegang oleh Bendahara.
3. **Actual (Belanja)**: Mengurangi saldo NPD berdasarkan nota fisik.
   - **Rumus Sisa NPD:** `$sisa = $nilaiNpd - ($subtotal + $ppn)`

---

## ⚙️ Instalasi Proyek

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di lingkungan lokal:

### 1. Clone Repositori
```bash
git clone [https://github.com/username/siks-app.git](https://github.com/username/siks-app.git)
cd siks-app

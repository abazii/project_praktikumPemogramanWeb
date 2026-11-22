# 💈 BARBERBOR — Retro Barbershop & Shoescare

![PHP](https://img.shields.io/badge/PHP-Native%208.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla%20ES6-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Theme](https://img.shields.io/badge/Theme-Classic%20Retro%2090s-E09F3E?style=for-the-badge)

> **"GOOD BARBER FOR GOOD PEOPLE — Nyaman, Tampan, Barberbor."**

**BARBERBOR** adalah aplikasi web pemesanan layanan barbershop dan perawatan sepatu (*shoescare*) yang mengusung desain klasik bertema **Retro**. Website ini dirancang untuk memudahkan pelanggan melakukan reservasi kursi secara *online* dengan sistem pembayaran DP QRIS minimal 50%, sekaligus menyediakan panel kontrol antrian *real-time* dan ekspor laporan keuangan bagi pemilik (*Owner*).

---

## 📋 Daftar Isi

1. [Fitur Unggulan](#-fitur-unggulan)
2. [Daftar Layanan & Tarif](#-daftar-layanan--tarif)
3. [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
4. [Struktur Folder & File](#-struktur-folder--file)
5. [Pembagian Tugas Kelompok](#-pembagian-tugas-kelompok)
6. [Cara Instalasi & Menjalankan](#-cara-instalasi--menjalankan)
7. [Akun Pengujian (Testing Credentials)](#-akun-pengujian-testing-credentials)
8. [Panduan Penggunaan Website](#-panduan-penggunaan-website)

---

## ✨ Fitur Unggulan

### 1. Sistem Autentikasi Terpadu (Login & Sign In)
* **Unified Login (`index.php`):** Halaman masuk ringkas yang hanya membutuhkan **Email** dan **Password**. Sistem secara otomatis mengenali peran akun dan mengarahkan **Customer** ke halaman utama (`home.php`) serta **Owner** ke panel admin (`dashboard_owner.php`).
* **Registrasi Customer (`signin.php`):** Pendaftaran akun pelanggan baru dengan input *Nama Lengkap*, *Nomor Handphone*, *Alamat Lengkap*, *Email*, dan *Password*, dilengkapi validasi format email, pencegahan email ganda, serta enkripsi kata sandi (`password_hash`).

### 2. Antarmuka Pelanggan Bertema Retro
* **Halaman Utama Scrollable (`home.php`):** Menampilkan *Hero Banner* bergaya poster retro, serta bagian `/ 01 ABOUT`, `/ 02 SERVICES`, `/ 03 PRICING`, dan `/ 04 BARBERS`.
* **Halaman Profil (`about.php`):** Halaman khusus informasi sejarah dan identitas merek terdaftar **BARBERBOR**.
* **Navigasi Atas (`navbar.php`):** Menu `HOME`, `ABOUT`, `BOOKING`, `GALLERY`, `CONTACT`, dan tombol `KELUAR`. *(Menu `GALLERY` dan `CONTACT` dipertahankan secara visual pada bilah navigasi tanpa mengalihkan halaman).*

### 3. Sistem Booking Multi-Service & Validasi QRIS DP 50%
* **Pilih Beberapa Layanan Sekaligus (`booking.php`):** Menggunakan dropdown retro interaktif yang memungkinkan pelanggan menambahkan lebih dari satu layanan dalam satu kali transaksi.
* **Kalkulasi Otomatis:** Total harga layanan dan nominal wajib **DP minimal 50%** dihitung secara otomatis oleh JavaScript dan divalidasi ulang di sisi server (`proses_booking.php`).
* **Upload Bukti Pembayaran Khusus Foto:** Pelanggan wajib mengunggah *screenshot* bukti transfer QRIS. Sistem menolak pengunggahan file selain foto (`JPG`, `JPEG`, `PNG`, `WEBP`) serta menampilkan form secara ringkas tanpa pratinjau gambar berlebihan.
* **Pemantauan Antrian Real-Time (AJAX):** Status antrian pelanggan diperbarui otomatis setiap beberapa detik. Jika Owner menekan tombol **Antrian Berikutnya**, pelanggan langsung menerima notifikasi peringatan untuk bersiap di tempat. Ketika status diubah menjadi **Selesai**, tiket antrian otomatis hilang dari halaman pelanggan.

### 4. Dashboard Owner & Laporan Excel (`.xlsx`)
* **Manajemen Antrian (`dashboard_owner.php`):** Menampilkan daftar lengkap pesanan pelanggan beserta status pengerjaan.
* **Preview Bukti Transfer Ukuran Layar HP:** Owner dapat menekan tombol **Lihat Foto** pada kolom *Bukti DP* untuk memeriksa *screenshot* pembayaran di dalam modal berbingkai proporsional layar *smartphone*.
* **Tombol Aksi Icon Grid 2x2:** Setiap baris antrian dilengkapi 4 tombol aksi cepat:
  1. **Antrian Berikutnya** (mengirim panggilan notifikasi ke pelanggan)
  2. **Sedang Diproses** (menandai pelanggan sedang dicukur/dirawat)
  3. **Hapus** (menghapus data antrian sekaligus file foto bukti bayar dari server)
  4. **Selesai** (menyelesaikan antrian dan menghapusnya dari daftar aktif pelanggan)
* **Export Rekapan Excel (`export_xlsx.php`):** Mengolah data transaksi menjadi ringkasan total booking, total omzet layanan, total DP QRIS masuk, sisa pelunasan di tempat, serta rekap status antrian ke dalam file Microsoft Excel (`.xlsx`).

---

## ✂️ Daftar Layanan & Tarif

| No. | Nama Layanan (*Service*) | Harga Normal | Wajib Minimal DP (50%) |
| :---: | :--- | :---: | :---: |
| 1 | **Haircuts Pro** | Rp 30.000 | Rp 15.000 |
| 2 | **Haircut Kids** | Rp 25.000 | Rp 12.500 |
| 3 | **Shaves, Beard & Mustache Trim** | Rp 20.000 | Rp 10.000 |
| 4 | **Hair Coloring** | Rp 100.000 | Rp 50.000 |
| 5 | **Hair Coloring Basic** | Rp 45.000 | Rp 22.500 |
| 6 | **Hair Coloring Fashion** | Rp 65.000 | Rp 32.500 |
| 7 | **Haircare Treatment** | Rp 60.000 | Rp 30.000 |

---

## 🛠️ Teknologi yang Digunakan

* **Frontend:** HTML5, CSS3, Bootstrap 5.3.3 (CDN), Google Fonts (*Alfa Slab One*, *Oswald*, *Poppins*), Vanilla JavaScript.
* **Backend:** PHP Native (MySQLi Object-Oriented & Prepared Statements).
* **Database:** MySQL / MariaDB (`db_barberbor`).
* **Server Lokal:** XAMPP (Apache & MySQL).

---

## 📁 Struktur Folder & File

```text
project_praktikumPemogramanWeb-main/
├── .gitattributes              # Konfigurasi line-ending Git
├── README.md                   # Dokumentasi lengkap proyek
├── db_barberbor.sql            # Skema database & data awal (db_barberbor)
├── koneksi.php                 # Konfigurasi koneksi database MySQLi
│
├── index.php                   # Halaman Login (Email & Password)
├── proses_login.php            # Validasi autentikasi & session role
├── signin.php                  # Halaman Sign In / Pendaftaran Customer
├── proses_signin.php           # Validasi input & penyimpanan akun customer
├── logout.php                  # Penghapusan session & keluar akun
│
├── navbar.php                  # Komponen navigasi atas & proteksi halaman
├── footer.php                  # Komponen footer & pemanggilan script
├── home.php                    # Halaman utama scrollable (About, Services, Pricing, Barbers)
├── about.php                   # Halaman About tersendiri
│
├── booking.php                 # Halaman pemesanan layanan, QRIS, upload bukti & live antrian
├── proses_booking.php          # Validasi server-side DP 50%, upload foto & simpan antrian
├── dashboard_owner.php         # Panel kontrol antrian Owner, modal bukti DP & Icon Grid 2x2
├── export_xlsx.php             # Pengolahan data rekapan & unduh laporan Excel (.xlsx)
│
├── style.css                   # Styling tema Retro Barbershop
├── script.js                   # Validasi form, multi-service dropdown, modal & live polling
└── uploads/                    # Direktori penyimpanan foto QRIS & bukti pembayaran customer
    ├── QRIS BARBERBOR.jpeg
    ├── bukti_1795260585_269.jpg
    └── bukti_pembayaran_asep.jpg
```

---

## 🤝 Pembagian Tugas Kelompok

Proyek ini dikerjakan secara kolaboratif dengan pembagian modul yang seimbang dan saling terintegrasi:

| Anggota | Fokus Modul | Tanggung Jawab File & Fitur |
| :--- | :--- | :--- |
| **Nadhir** | **Booking, Transaksi & Dashboard Owner** | `booking.php`, `proses_booking.php`, `dashboard_owner.php`, `export_xlsx.php`, `about.php`, `footer.php`, `script.js`, folder `uploads/`, serta skema tabel `bookings` & `owners` pada `db_barberbor.sql`. |
| **Dzaki** | **Autentikasi, Session & Antarmuka Customer** | `koneksi.php`, `index.php`, `proses_login.php`, `signin.php`, `proses_signin.php`, `logout.php`, `navbar.php`, `home.php`, `style.css`, serta skema database & tabel `customers` pada `db_barberbor.sql`. |

---

## ⚙️ Cara Instalasi & Menjalankan

1. **Siapkan Server Lokal:**
   Pastikan **XAMPP** sudah terinstal di komputer Anda, lalu jalankan modul **Apache** dan **MySQL** melalui *XAMPP Control Panel*.
2. **Pindahkan Folder Proyek:**
   Salin seluruh folder proyek ke dalam direktori `htdocs` XAMPP Anda (misalnya `C:\xampp\htdocs\barberbor\` atau `C:\xampp\htdocs\project_praktikumPemogramanWeb-main\`).
3. **Import Database:**
   * Buka browser dan akses `http://localhost/phpmyadmin`.
   * Buat database baru bernama **`db_barberbor`** (atau langsung klik tab **Import**).
   * Pilih file **`db_barberbor.sql`** dari folder proyek, lalu klik **Go / Import**.
4. **Cek Koneksi Database (`koneksi.php`):**
   Pastikan pengaturan di `koneksi.php` sudah sesuai dengan konfigurasi MySQL lokal Anda:
   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "db_barberbor";
   ```
5. **Jalankan Aplikasi di Browser:**
   Buka alamat berikut di browser Anda:
   ```text
   http://localhost/project_praktikumPemogramanWeb-main/
   ```
   *(Sesuaikan nama folder dengan nama folder yang Anda simpan di dalam `htdocs`).*

---

## 🔐 Akun Pengujian (Testing Credentials)

Gunakan akun berikut pada halaman awal (`index.php`) untuk menguji fitur dari sisi **Owner** maupun **Customer**:

| Peran (*Role*) | Email | Password | Halaman Tujuan |
| :--- | :--- | :--- | :--- |
| **Owner Barbershop** | `owner@barberbor.com` | `barberbor123` | `dashboard_owner.php` |
| **Customer (Testing)** | `asep@gmail.com` | `asep123` | `home.php` |

> **Catatan:** Anda juga dapat membuat akun pelanggan baru secara mandiri melalui tombol **DAFTAR AKUN BARU (SIGN IN)** pada halaman login.

---

## 📖 Panduan Penggunaan Website

### A. Alur Pelanggan (*Customer*)
1. **Login / Sign In:** Masuk menggunakan akun testing `asep@gmail.com` (`asep123`) atau buat akun baru di halaman Sign In.
2. **Eksplorasi Layanan:** Jelajahi informasi layanan dan harga pada halaman `HOME` maupun `ABOUT`.
3. **Melakukan Booking (`BOOKING`):**
   * Data *Nama Lengkap* dan *Nomor Handphone* akan terisi otomatis sesuai akun yang login.
   * Pilih satu atau beberapa layanan melalui dropdown **PILIH SERVICES**. Setiap layanan yang dipilih akan muncul di daftar bawahnya beserta total harga dan rincian **Wajib Minimal DP (50%)**.
   * Lakukan simulasi pembayaran QRIS dan masukkan nominal transfer minimal 50% dari total harga.
   * Unggah file foto *screenshot* bukti pembayaran (`JPG`, `JPEG`, `PNG`, `WEBP`), lalu klik **KONFIRMASI & KIRIM BOOKING**.
4. **Pantau Antrian:** Tiket nomor antrian Anda akan muncul di kolom **ANTRIAN ANDA** di sebelah kanan dan diperbarui secara *real-time*.

### B. Alur Pemilik (*Owner*)
1. **Login Owner:** Masuk melalui `index.php` menggunakan `owner@barberbor.com` dan password `barberbor123`.
2. **Verifikasi Bukti Pembayaran:** Klik tombol **Lihat Foto** pada kolom *Bukti DP* untuk memeriksa *screenshot* transfer QRIS pelanggan.
3. **Kelola Antrian:** Gunakan 4 tombol pada kolom *Aksi Antrian*:
   * Klik **Antrian Berikutnya** untuk mengirim peringatan kepada pelanggan agar bersiap di lokasi.
   * Klik **Sedang Diproses** saat layanan dimulai.
   * Klik **Selesai** setelah layanan rampung (antrian otomatis menghilang dari layar pelanggan).
   * Klik **Hapus** jika ingin membatalkan/menghapus data antrian.
4. **Unduh Laporan Excel:** Klik tombol **EXPORT REKAPAN (.XLSX)** di bagian kanan atas tabel untuk mengunduh laporan transaksi lengkap dalam format Microsoft Excel.
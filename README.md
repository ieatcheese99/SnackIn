# SnackIn E-Commerce Web

<p align="center">
  <img src="images/AHLINYA CEMILAN1.png" alt="SnackIn Presentation" />
</p>


SnackIn adalah sebuah sistem informasi berbasis web yang berfungsi sebagai **Panel Admin dasbor** untuk mengelola operasional toko makanan/snack. Platform ini memberikan kendali penuh kepada pengelola toko untuk memantau pesanan, mengelola inventaris produk, kategori, data pelanggan, hingga melihat histori dan analitik penjualan.

---

## 🛠️ Tech Stack

Platform ini dibangun menggunakan teknologi web standar yang ringan dan efisien:

<p align="center">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5" />
  <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3" />
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript" />
  <img src="https://img.shields.io/badge/Chart.js-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white" alt="Chart.js" />
</p>

- **Backend:** PHP (Native)
- **Database:** MySQL (Relational Database)
- **Frontend:** HTML5, CSS3, JavaScript
- **Data Visualization:** Chart.js (Untuk grafik statistik pesanan)
- **Icons:** Font Awesome

---

## 📦 Fitur Utama

- **📊 Dashboard Analitik:** Ringkasan total pesanan, produk aktif, total pengguna, dan pendapatan. Dilengkapi dengan grafik penjualan 7 hari terakhir.
- **🍔 Manajemen Produk (`data_barang.php`):** Tambah, edit, hapus, dan kelola ketersediaan produk/snack.
- **🏷️ Manajemen Kategori (`kategori.php`):** Pengelompokan jenis snack untuk memudahkan navigasi.
- **🛒 Manajemen Pesanan (`pesanan.php`):** Memantau pesanan masuk, memproses status pesanan (*Pending*, *Processing*, *Completed*, *Cancelled*).
- **👥 Manajemen Pengguna (`user.php`):** Mengelola data pelanggan yang terdaftar di sistem.
- **📜 Riwayat Transaksi (`history.php` / `history_admin.php`):** Pencatatan seluruh aktivitas penjualan untuk keperluan audit dan laporan.

---

## 🔄 Alur Sistem (Project Flow)

Berikut adalah ringkasan alur kerja (flow) dari aplikasi SnackIn:

### 1. Alur Pengguna (Pelanggan)
1. **Registrasi/Login:** Pelanggan membuat akun baru atau masuk ke sistem (`login.php` / `register.php`).
2. **Katalog Produk:** Pelanggan menelusuri daftar kategori dan produk snack yang tersedia.
3. **Pemesanan (Checkout):** Pelanggan memilih produk, memasukkan ke keranjang, dan melakukan *checkout*.
4. **Pembayaran & Status:** Sistem akan membuat pesanan dengan status awal **Pending**. Pelanggan menunggu pesanan diproses oleh toko.

### 2. Alur Admin (Pemilik Toko)
1. **Login Admin:** Admin masuk melalui panel khusus. Sistem memverifikasi kredensial serta mencatat log akses (Secure Auth).
2. **Notifikasi Pesanan Baru:** Jika ada pesanan dengan status *Pending*, Admin akan mendapat *alert* di Dashboard.
3. **Pemrosesan Pesanan (`pesanan.php`):** 
   - Admin melihat detail pesanan (*Items*, Total Harga, Alamat pengiriman).
   - Admin mengubah status dari **Pending** ➡️ **Processing** (Sedang disiapkan) ➡️ **Completed** (Selesai/Dikirim).
   - Jika ada kendala (stok habis/belum bayar), pesanan dapat diberi status **Cancelled**.
4. **Update Katalog (`data_barang.php`):** Secara berkala, Admin menambahkan stok snack baru atau mengubah harga dan gambar produk.
5. **Analitik Keuangan:** Membuka Dasbor untuk melihat tren pemasukan melalui grafik bar/line dari Chart.js.

---

## 🚀 Panduan Instalasi (Development)

Jika Anda ingin menjalankan proyek ini secara lokal:

1. Pastikan Anda telah menginstal web server lokal seperti **XAMPP / Laragon / WAMP**.
2. Clone repository ini ke dalam folder `htdocs` (jika XAMPP) atau `www` (jika Laragon).
   ```bash
   git clone https://github.com/ieatcheese99/SnackIn.git
   ```
3. Buat database baru di MySQL (misal melalui phpMyAdmin) dengan nama `data_produk2` (sesuai konfigurasi).
4. Import file SQL (jika ada file database `.sql` yang disertakan di repositori) ke dalam database `data_produk2`.
5. Buka `config/database.php` dan pastikan konfigurasi kredensial (host, username, password, dbname) sudah sesuai dengan server lokal Anda.
6. Akses proyek melalui browser di `http://localhost/SnackIn`.

---


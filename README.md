# Fastender - Platform E-Commerce Pre-Order (PO) & Dashboard Admin (PHP Native)

Platform web terintegrasi untuk sistem **Pre-Order (PO)** apparel angkatan, jaket varsity, kemeja PDH organisasi, dan merchandise kampus bernama **Fastender**. Dibangun menggunakan arsitektur **PHP Native murni (tanpa framework)**, **HTML murni**, **CSS murni**, dan **Vanilla JavaScript**.

---

## 🎨 1. Sistem Desain & Visual (Zellerfeld Design System / DESIGN.md)

- **Warna Utama & Aksen:**
  - **Electric Cobalt:** `#000aff` (Primary Accent CTA)
  - **Pale Iris:** `#e5e7ff` (Accent Tint / Subtly tinted badges)
  - **Ink Black:** `#111111` (Surface Level 3 / Secondary button / High contrast blocks)
  - **Plaster:** `#ecedee` (Surface Level 2 / Card surfaces & Muted backgrounds)
  - **Fog:** `#d7d7d7` (Border & Divider line)
  - **Slate:** `#a1a4aa` (Secondary borders & Inactive pills)
  - **Ash:** `#737780` (Muted supporting body copy & technical labels)
  - **Pure White:** `#ffffff` (Canvas Surface Level 1)
- **Tipografi:**
  - **Roobert / Inter:** Font antarmuka utama (`--font-roobert`), tracking `-0.04em`.
  - **Space Mono:** Monospace metadata, harga, nomor resi, kuota (`--font-space-mono`).
- **Border Radius:** Strictly `10px` (`--radius-cards`, `--radius-buttons`, `--radius-inputs`) dan `30px` (`--radius-badges`, `--radius-pills`).
- **Surface Elevation:** Tidak menggunakan drop shadow pada card; card bertengger di atas canvas putih dengan warna Plaster `#ecedee`.
- **Branding:** Wordmark (`Fastender`). Iconography dengan outline tipis 1.5px monochrome.

---

## 📁 2. Struktur File Proyek (PHP Native)

```text
web/
├── config/
│   └── database.php           # Konfigurasi koneksi MySQL PDO dengan graceful fallback
├── data/
│   └── products.json          # File penyimpanan cadangan / fallback
├── includes/
│   ├── functions.php          # Helper PHP (formatRupiah, sanitasi, auth, query produk/pesanan)
│   ├── header.php             # HTML head, Google Fonts, meta tags, CSS link
│   ├── navbar.php             # Komponen Navbar sticky dengan deteksi menu aktif & badge keranjang
│   ├── footer.php             # Komponen Footer 3-kolom navy & baris hak cipta
│   ├── admin-header.php       # Top bar admin dengan profil & mobile sidebar toggle
│   └── admin-sidebar.php      # Sidebar vertikal admin & link logout
├── uploads/
│   └── products/              # Direktori penyimpanan file upload multi-foto produk
├── database.sql               # Skema database MySQL lengkap (users, products, product_images, orders)
├── index.php                  # A. Halaman Beranda (Hero Banner PO 2026, Countdown, Alur PO, Produk Terpopuler)
├── katalog.php                # B. Halaman Katalog (2 Kolom: Filter Kategori & Rentang Harga, Grid Produk)
├── detail.php                 # C. Halaman Detail Produk (2 Kolom: Galeri Multi-Foto, Kuota PO, Varian, Stepper Qty)
├── keranjang.php              # D. Halaman Keranjang Belanja (2 Kolom: List Item, Voucher Promo, Ringkasan Belanja)
├── checkout.php               # E. Halaman Form Pemesanan PO (Data Pembeli, Alamat, Skema DP 50%/Lunas, Modal Resi)
├── lacak.php                  # F. Halaman Lacak Pesanan (Form 1 Input Resi, Timeline Status Produksi Real-Time)
├── admin-login.php            # G. Halaman Login Administrator (PHP Session + Demo Quick-Fill)
├── admin-dashboard.php        # H. Halaman Dashboard Admin (Stat Cards, Tabel Pesanan Header Navy #1e3a8a, Modal Update)
├── admin-input-produk.php     # I. Form Input Produk PO (Multi-Image Upload Dropzone, Live Preview, Mode Space Kosong)
├── logout.php                 # Script logout mengakhiri sesi admin
├── css/
│   ├── style.css              # Stylesheet Global (Navbar Sticky, Grid, Product Card, Space Kosongan, Modal)
│   └── admin.css              # Stylesheet Khusus Admin (Sidebar Vertikal, Dropzone Multi-Foto, Data Table)
└── js/
    └── script.js              # Script pendukung interaktivitas client-side (Cart, Toast, Live Stepper)
```

---

## 🚀 3. Fitur Unggulan Sistem

### 🔐 3.1. Autentikasi Admin (Login Wall)
- **Halaman Login (`admin-login.php`):** Seluruh halaman admin (`admin-dashboard.php`, `admin-input-produk.php`) dilindungi oleh proteksi `requireAdminLogin()`.
- Pengguna yang belum terautentikasi otomatis dialihkan ke `admin-login.php`.
- **Kredensial Default Demo:**
  - **Email:** `admin@fastender.id`
  - **Password:** `admin123`
  - Tombol cepat **"⚡ Isi Otomatis Akun Admin"** untuk pengujian langsung.
  - Sesi dikelola dengan `session_start()` PHP dan `$_SESSION['fastender_admin_logged_in']`.

### 📸 3.2. Multi-Image Upload Produk Pre-Order
- Admin dapat mengunggah **lebih dari 1 foto produk** (jumlah fleksibel).
- Mendukung **Drag & Drop** ke dalam area upload serta tombol pilih multi-file (`multiple`).
- **Pratinjau Thumbnail Interaktif:** Kotak pratinjau thumbnail dengan badge **"Utama"** pada foto ke-1 dan tombol hapus per foto `[×]`.
- Gambar disimpan ke folder `uploads/products/` dan tercatat pada database.
- **Galeri Sisi Pengguna:**
  - `index.php` & `katalog.php`: Menampilkan foto sampul utama.
  - `detail.php`: Galeri foto besar di kiri disertai deretan thumbnail yang dapat diklik untuk berganti sudut pandang foto secara langsung.
  - `keranjang.php` & `checkout.php`: Thumbnail produk tampil mendampingi rincian pesanan.

### 📦 3.3. Mode "Space Kosongan" (Placeholder Slots)
- Sesuai rancangan alur proyek:
  - Sebelum admin menginputkan produk, halaman Beranda dan Katalog menampilkan slot **Space Kosongan** (*dashed border*) bertuliskan *"Space Kosong Produk PO - Menunggu Input dari Admin"*.
  - Begitu admin mengisi produk melalui `admin-input-produk.php`, produk tersebut langsung mengisi slot katalog.
  - Tersedia tombol cepat **"Set Mode Space Kosong"** dan **"⚡ Isi Produk Demo Instan"** di halaman admin untuk kemudahan simulasi dan presentasi.

---

## 💻 4. Panduan Menjalankan

### Cara 1: Menggunakan XAMPP / Laragon (Rekomendasi Kuliah/PBL)
1. Salin atau pindahkan folder `web` ke direktori root server:
   - XAMPP: `C:/xampp/htdocs/fastender`
   - Laragon: `C:/laragon/www/fastender`
2. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
3. Buat database baru bernama `fastender_db`.
4. Import file [`database.sql`](file:///d:/KULIAH/SEMESTER%203/PBL/web/database.sql) ke dalam database tersebut.
5. Buka browser dan akses:
   - Toko Pengguna: `http://localhost/fastender/index.php`
   - Login Admin: `http://localhost/fastender/admin-login.php`

### Cara 2: Menggunakan PHP Built-In Server (Tanpa XAMPP)
Jika PHP sudah terpasang di komputer:
```bash
# Jalankan terminal di dalam direktori folder web ini:
php -S localhost:8000
```
Buka `http://localhost:8000` pada browser. Sistem dilengkapi modul *graceful fallback*, sehingga tetap dapat berjalan mulus meskipun MySQL belum aktif.

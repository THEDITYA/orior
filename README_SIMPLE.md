# 🔍 Sistem Validasi Barang dengan QR Code

Sistem validasi keaslian barang menggunakan PHP Native, MySQL, dan TailwindCSS dengan fitur scan QR Code.

## 🚀 Fitur

### 👤 User (Publik)
- **Scan QR Code** dengan kamera (HTML5 QR Code Scanner)
- **Input manual** kode produk
- **Hasil validasi** real-time: ASLI ✅ atau PALSU ❌
- **Responsive design** untuk mobile dan desktop

### 👨‍💼 Admin
- **Login sistem** dengan username/password
- **CRUD produk** (tambah, lihat, hapus)
- **Generate QR Code** otomatis untuk setiap produk
- **Dashboard** dengan tabel produk
- **Management status** ORI/PALSU

## 📁 Struktur File
```
/orior/
├── simple_index.php     # Landing page
├── scan.php            # Halaman scanner QR Code
├── validate.php        # API validasi produk
├── database_simple.sql # Database schema
├── /admin/
│   ├── admin_login.php     # Login admin
│   ├── admin_dashboard.php # Dashboard admin
│   └── add_product.php     # Tambah produk + QR
├── /qrcodes/          # Folder QR code images
└── README_SIMPLE.md   # Dokumentasi ini
```

## ⚡ Setup Cepat

### 1. Database Setup
```sql
-- Buka phpMyAdmin atau MySQL client
-- Import file: database_simple.sql
-- Atau jalankan query yang ada di file tersebut
```

### 2. Konfigurasi Database
Edit kredensial database di setiap file PHP jika perlu:
```php
$host = 'localhost';
$db = 'validasi_barang';
$user = 'root';        // Ganti sesuai setup
$pass = '';            // Ganti sesuai setup
```

### 3. Permissions
```bash
# Pastikan folder qrcodes bisa ditulis
chmod 755 qrcodes/
```

### 4. Test Aplikasi
- **Scanner**: `http://localhost/orior/simple_index.php`
- **Admin**: `http://localhost/orior/admin/admin_login.php`
  - Username: `admin`
  - Password: `admin123`

## 🎯 Cara Menggunakan

### Untuk Admin:
1. Login di `/admin/admin_login.php`
2. Klik "Tambah Produk" 
3. Isi nama produk dan pilih status (ORI/PALSU)
4. QR Code akan di-generate otomatis
5. Download/cetak QR Code untuk ditempel di produk

### Untuk User:
1. Buka halaman scanner
2. Izinkan akses kamera
3. Arahkan kamera ke QR Code
4. Lihat hasil validasi (ASLI/PALSU/TIDAK DITEMUKAN)

## 🔧 Teknologi

- **Backend**: PHP Native (no framework)
- **Database**: MySQL
- **Frontend**: TailwindCSS (via CDN)
- **QR Scanner**: HTML5-QRCode library
- **QR Generator**: Google Charts API (simple alternative)

## 📝 Catatan Teknis

### QR Code Generation
- Menggunakan Google Charts API untuk kemudahan setup
- Alternative: PHP QRCode library jika ingin offline
- QR berisi kode unik produk

### Database Schema
```sql
users: id, username, password_hash, created_at
products: id, nama_barang, kode_unik, status, qrcode_path, created_at
```

### Security Features
- Password hashing dengan PHP `password_hash()`
- Session management untuk admin
- Input sanitization dengan `htmlspecialchars()`
- Prepared statements untuk SQL queries

## 🐛 Troubleshooting

### Kamera tidak berfungsi:
- Pastikan menggunakan HTTPS (untuk production)
- Check browser permissions
- Test di browser yang berbeda

### QR Code tidak ter-generate:
- Check koneksi internet (Google Charts API)
- Pastikan folder `/qrcodes` ada dan writable
- Check error log server

### Database connection error:
- Pastikan MySQL service berjalan
- Check kredensial database di file PHP
- Pastikan database `validasi_barang` sudah dibuat

## 🎨 Customization

### Mengubah tampilan:
- Edit classes TailwindCSS di file HTML
- Atau tambahkan custom CSS

### Menambah field produk:
1. ALTER table `products` di database
2. Update form di `add_product.php`
3. Update tampilan di dashboard

### Ganti QR Generator:
- Install PHP QRCode library dengan Composer
- Update logic di `add_product.php`

---
**Sistem siap digunakan!** 🚀

Default admin: `admin` / `admin123`
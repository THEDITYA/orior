# Orior Product Validation System

Aplikasi web validasi produk menggunakan QR code untuk memverifikasi keaslian produk dan mencegah pemalsuan. Sistem ini menggunakan PHP native, MySQL, Tailwind CSS, dan JavaScript QR scanner.

## 🚀 Fitur Utama

### Admin Panel
- Login sistem dengan session management
- Dashboard dengan statistik real-time
- Manajemen produk (tambah/edit/hapus)
- Generate QR code unik per unit atau per batch
- View history scan dengan detail IP dan timestamp
- CSRF protection untuk semua form

### Halaman Publik
- QR scanner menggunakan kamera (mobile & desktop)
- Input manual untuk kode produk
- Validasi real-time dengan 4 status: **ORI / PALSU / TIDAK DITEMUKAN / DUPLIKAT**
- Responsive design dengan Tailwind CSS
- Rate limiting untuk mencegah abuse

### API Endpoint
- `/api/validate.php` - Validasi QR token
- `/api/product.php` - Informasi produk (opsional)
- Response format JSON
- CORS support
- Rate limiting per IP

### Sistem Keamanan
- **HMAC SHA256 signature** untuk QR token
- **Prepared statements** untuk semua query database
- **CSRF protection** untuk form admin
- **Rate limiting** sederhana per IP
- **Input sanitization** dan validation
- **Session security** dengan secure cookies

## 📋 Requirements

- PHP 7.4+ dengan ekstensi: PDO, MySQL, GD, JSON
- MySQL/MariaDB 5.7+
- Web server: Apache/Nginx
- Composer (untuk QR code library)

## 🛠️ Instalasi

### 1. Clone/Download Project
```bash
git clone <repository-url> orior
cd orior
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Setup Database
```sql
-- Buat database
CREATE DATABASE orior_validation CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import schema
mysql -u root -p orior_validation < database.sql
```

### 4. Konfigurasi Database
Edit file `lib/db.php` sesuai dengan setup MySQL Anda:
```php
private $host = 'localhost';
private $dbname = 'orior_validation'; 
private $username = 'root';
private $password = '';  // sesuaikan password MySQL
```

### 5. Setup Apache/Nginx
Pastikan document root menuju ke folder proyek, atau akses via:
- Admin: `http://localhost/orior/admin/`
- Public Scanner: `http://localhost/orior/public/`
- API: `http://localhost/orior/api/`

### 6. Set Permissions
```bash
chmod 755 logs/
chmod 644 .htaccess
```

## 🔧 Konfigurasi

### Environment Variables (Opsional)
Buat file `.env` untuk konfigurasi production:
```env
APP_ENV=production
DB_HOST=localhost
DB_NAME=orior_validation
DB_USER=your_db_user
DB_PASS=your_db_password
ORIOR_QR_SECRET=your_secret_key_here
APP_URL=https://yourdomain.com
```

### QR Secret Key
Untuk production, ubah secret key di `lib/qr_helper.php`:
```php
private static $secret = 'your_unique_secret_key_here';
```

## 📖 Cara Penggunaan

### Login Admin
1. Akses: `http://localhost/orior/admin/login.php`
2. Default login: `admin` / `admin123`
3. Ubah password setelah login pertama

### Menambah Produk
1. Login ke admin panel
2. Klik menu "Produk"
3. Isi form tambah produk (SKU wajib)
4. QR code akan di-generate otomatis
5. Download/cetak QR code untuk ditempel di produk

### Validasi Produk (Public)
1. Akses: `http://localhost/orior/public/`
2. Pilih "Scan dengan Kamera" atau "Input Manual"
3. Untuk kamera: izinkan akses kamera, arahkan ke QR code
4. Untuk manual: masukkan token dari QR code
5. Lihat hasil validasi: ORI/PALSU/TIDAK DITEMUKAN/DUPLIKAT

## 🏗️ Struktur Proyek

```
/orior
├── /public               # Halaman publik
│   ├── index.php        # QR scanner page
│   ├── scan.js          # Scanner logic
│   └── /css             # Stylesheets
├── /admin               # Admin panel
│   ├── login.php        # Login page
│   ├── dashboard.php    # Dashboard
│   └── products.php     # Product management
├── /api                 # API endpoints
│   ├── validate.php     # Validation API
│   └── product.php      # Product info API
├── /lib                 # Libraries
│   ├── db.php           # Database connection
│   ├── auth.php         # Authentication
│   ├── qr_helper.php    # QR functions
│   └── rate_limit.php   # Rate limiting
├── /logs                # Log files
├── composer.json        # Dependencies
├── database.sql         # Database schema
├── config.php          # Configuration
├── .htaccess           # Apache config
└── README.md           # This file
```

## 🔍 API Documentation

### POST /api/validate.php
Validasi QR token produk

**Request:**
```json
{
    "token": "eyJza3UiOiJTS1UwMDEiLCJzZXJpYWwiOm51bGwsImlhdCI6..."
}
```

**Response:**
```json
{
    "status": "success",
    "result": "ORI",
    "message": "Produk asli dan valid",
    "product": {
        "sku": "SKU001",
        "serial": "SN001001",
        "data": {
            "nama": "Produk A",
            "kategori": "Elektronik"
        }
    },
    "scan_count": 1,
    "request_info": {
        "ip": "192.168.1.1",
        "timestamp": "2025-01-20T10:30:00+07:00"
    }
}
```

**Result Values:**
- `ORI`: Produk asli dan valid
- `DUPLIKAT`: Produk sudah pernah discan (kemungkinan duplikasi)
- `PALSUK`: Token signature tidak valid (kemungkinan palsu)
- `TIDAK_DITEMUKAN`: Token valid tapi produk tidak terdaftar

### GET /api/product.php?sku=SKU001
Mendapatkan informasi produk (tanpa token)

## 🛡️ Sistem Keamanan

### QR Token Format
```
payload.signature
```

**Payload** (base64 encoded JSON):
```json
{
    "sku": "SKU001",
    "serial": "SN001001", 
    "iat": 1642680000,
    "nonce": "random_string",
    "data": {}
}
```

**Signature**: HMAC-SHA256(payload, secret_key)

### Alur Validasi
1. Extract payload dan signature dari token
2. Verify HMAC signature dengan secret key
3. Jika signature invalid → PALSU
4. Jika signature valid, cek token di database
5. Jika tidak ada di DB → TIDAK DITEMUKAN  
6. Jika ada, cek history scan untuk deteksi duplikat
7. Return status: ORI/DUPLIKAT

### Rate Limiting
- API validation: 10 requests/menit per IP
- Public page: 100 requests/jam per IP
- Admin login: 5 attempts per 5 menit per IP

## 🔧 Maintenance

### Cleanup Commands
```sql
-- Hapus log scan lama (>90 hari)
DELETE FROM scans WHERE scanned_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Hapus CSRF token expired
DELETE FROM csrf_tokens WHERE expires_at < NOW();

-- Hapus rate limit records lama
DELETE FROM rate_limits WHERE last_request < DATE_SUB(NOW(), INTERVAL 24 HOUR);
```

### Log Files
- Auth logs: `/logs/auth_YYYY-MM-DD.log`
- Rate limit logs: `/logs/rate_limit_YYYY-MM-DD.log` 
- PHP errors: `/logs/php_errors.log`

## 🚀 Production Deployment

### 1. Security Checklist
- [ ] Ubah default admin password
- [ ] Generate unique QR secret key
- [ ] Set environment ke 'production'
- [ ] Enable HTTPS
- [ ] Setup SSL certificate
- [ ] Configure firewall

### 2. Database Optimization
```sql
-- Index untuk performa
CREATE INDEX idx_scans_date ON scans(scanned_at);
CREATE INDEX idx_products_status ON products(status);
```

### 3. Server Configuration
- Enable opcache untuk PHP
- Set proper file permissions (644 untuk files, 755 untuk folders)
- Configure log rotation
- Setup database backup

## 📊 Monitoring & Analytics

### Query Statistik
```sql
-- Top scanned products
SELECT p.sku, COUNT(s.id) as scan_count
FROM products p LEFT JOIN scans s ON p.id = s.product_id  
GROUP BY p.id ORDER BY scan_count DESC LIMIT 10;

-- Scan results breakdown
SELECT result, COUNT(*) as count 
FROM scans WHERE scanned_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY result;

-- Suspicious IP addresses
SELECT ip, COUNT(*) as requests
FROM scans WHERE scanned_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
GROUP BY ip HAVING requests > 50 ORDER BY requests DESC;
```

## 🐛 Troubleshooting

### Camera Issues
- Pastikan HTTPS untuk production (camera access)
- Check browser permissions
- Test di browser berbeda
- Fallback ke input manual

### Database Connection
- Verify MySQL credentials
- Check database exists
- Test connection dari command line
- Check MySQL service running

### Rate Limiting Issues
```php
// Reset rate limit untuk IP tertentu
DELETE FROM rate_limits WHERE ip = 'IP_ADDRESS';
```

## 📝 License

MIT License - Bebas digunakan untuk proyek komersial dan non-komersial.

## 🤝 Contributing

1. Fork repository
2. Create feature branch
3. Commit changes
4. Push ke branch
5. Create Pull Request

## 📞 Support

Untuk pertanyaan atau issues, silakan buat issue di repository atau hubungi tim development.

---
**Orior Validation System v1.0.0**  
Melindungi produk dari pemalsuan dengan teknologi QR validation.
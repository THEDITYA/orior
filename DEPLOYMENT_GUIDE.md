# 🚀 Deployment Guide - Orior QR Validation System

## Overview
Panduan lengkap untuk deploy Orior QR Validation System ke Vercel dengan cloud database.

## 📋 Prerequisites
- Akun Vercel (gratis)
- Akun penyedia cloud database (PlanetScale/Railway/Aiven)
- Repository GitHub yang sudah di-push

## 🗄️ Step 1: Setup Cloud Database

### Opsi A: PlanetScale (Recommended)
```bash
# 1. Daftar di https://planetscale.com
# 2. Create new database: orior-db
# 3. Copy connection string
# 4. Gunakan branch 'main' untuk production
```

### Opsi B: Railway
```bash
# 1. Daftar di https://railway.app  
# 2. New Project > Provision MySQL
# 3. Copy database credentials dari Variables tab
```

### Opsi C: Aiven
```bash
# 1. Daftar di https://aiven.io
# 2. Create MySQL service
# 3. Copy connection details
```

## 🔧 Step 2: Setup Database Schema

### Via Web Interface (PlanetScale)
```sql
-- Execute di PlanetScale Console atau phpMyAdmin
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    qr_code TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin user (password: admin123)
INSERT INTO users (username, password, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@orior.local', 'admin');

-- Insert sample products
INSERT INTO products (name, code, description, qr_code) VALUES 
('Laptop Dell XPS 13', 'LPT001', 'Ultrabook premium dengan prosesor Intel i7', 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=LPT001'),
('Mouse Wireless Logitech', 'MSE002', 'Mouse nirkabel dengan sensor presisi tinggi', 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=MSE002'),
('Keyboard Mechanical RGB', 'KBD003', 'Keyboard mekanik dengan backlight RGB', 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=KBD003');
```

### Via Setup Script (Local Migration)
```bash
# Jika sudah ada data lokal, export dulu:
mysqldump -u root -p validasi_barang > orior_backup.sql

# Import ke cloud database:
mysql -h YOUR_HOST -u YOUR_USER -p YOUR_DB_NAME < orior_backup.sql
```

## 🌐 Step 3: Deploy to Vercel

### Connect Repository
1. Login ke [Vercel Dashboard](https://vercel.com/dashboard)
2. Click "New Project"
3. Import dari GitHub: `THEDITYA/orior`
4. Configure settings:
   - **Framework Preset**: Other
   - **Root Directory**: `./`
   - **Build Command**: (kosongkan)
   - **Output Directory**: `public`
   - **Install Command**: (kosongkan)

### Environment Variables
Tambahkan di Vercel Dashboard > Project Settings > Environment Variables:

```env
DB_HOST=your-database-host.com
DB_NAME=validasi_barang
DB_USER=your-username  
DB_PASSWORD=your-password
```

**Contoh untuk PlanetScale:**
```env
DB_HOST=aws.connect.psdb.cloud
DB_NAME=orior-db
DB_USER=xxxxxxxx
DB_PASSWORD=pscale_pw_xxxxxxxx
```

## 🔍 Step 4: Verify Deployment

### Test URLs
```bash
# Home page
https://your-app.vercel.app

# Admin login  
https://your-app.vercel.app/admin

# QR Scanner
https://your-app.vercel.app/scan

# API validation
https://your-app.vercel.app/api/validate?code=LPT001
```

### Check Database Connection
1. Akses admin panel: `https://your-app.vercel.app/admin`
2. Login dengan: `admin` / `admin123`
3. Verifikasi data produk muncul
4. Test scan QR code

## 🐛 Troubleshooting

### Database Connection Error
```bash
# Error: SQLSTATE[HY000] [2002]
- Periksa environment variables di Vercel
- Pastikan database server online
- Cek firewall/whitelist IP
```

### PHP Runtime Error
```bash
# Error: Function not found
- Pastikan vercel.json menggunakan vercel-php@0.7.4
- Check PHP version compatibility (8.2+)
```

### QR Scanner Not Working
```bash
# Camera permission denied
- Pastikan menggunakan HTTPS (Vercel default)
- Test di browser berbeda
- Check mobile compatibility
```

## 📊 Performance Optimization

### Database Indexing
```sql
-- Tambahkan indexes untuk performa
CREATE INDEX idx_products_code ON products(code);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_users_username ON users(username);
```

### Vercel Settings
```json
// vercel.json optimization
{
  "functions": {
    "api/*.php": {
      "runtime": "vercel-php@0.7.4",
      "maxDuration": 10
    }
  },
  "headers": [
    {
      "source": "/assets/(.*)",
      "headers": [
        {
          "key": "Cache-Control",
          "value": "public, max-age=31536000, immutable"
        }
      ]
    }
  ]
}
```

## 🔒 Security Checklist

- [ ] Environment variables set di Vercel (jangan commit ke Git)
- [ ] Database credentials aman
- [ ] Admin password sudah diganti
- [ ] HTTPS enabled (default di Vercel)
- [ ] Database whitelist IP jika perlu

## 📞 Support

### Resources
- [Vercel PHP Documentation](https://vercel.com/docs/functions/serverless-functions/runtimes/php)
- [PlanetScale Documentation](https://docs.planetscale.com/)
- [Repository Issues](https://github.com/THEDITYA/orior/issues)

### Common Commands
```bash
# Redeploy
git push origin main

# Check logs
vercel logs

# Environment variables
vercel env ls
vercel env add DB_HOST
```

---

🎉 **Selamat!** Orior QR Validation System sekarang running di production!

Default admin credentials:
- **Username**: `admin`
- **Password**: `admin123` (segera ganti!)
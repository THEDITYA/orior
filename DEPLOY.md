# 🚀 Deploy Orior QR Validation System ke Vercel

Panduan lengkap untuk deploy sistem validasi QR code ke Vercel dengan database cloud.

## 📋 Prerequisites

- [x] Account Vercel (gratis di [vercel.com](https://vercel.com))
- [x] Database MySQL cloud (PlanetScale, Railway, atau Aiven)
- [x] Git repository (GitHub, GitLab, atau Bitbucket)
- [x] PHP 8.3 compatible code (vercel-php@0.7.4)

## 🐘 PHP Runtime Info

- **Runtime Version**: `vercel-php@0.7.4`
- **PHP Version**: 8.3.x
- **Node.js**: 22.x (latest)
- **Memory Limit**: ~90MB
- **Cold Start**: ~250ms
- **Warm Start**: ~5ms
- **Extensions**: mysqli, pdo_mysql, curl, json, mbstring, openssl, dan 50+ lainnya

## 🗄️ Setup Database Cloud

### Option 1: PlanetScale (Recommended)
1. **Daftar** di [planetscale.com](https://planetscale.com) (gratis)
2. **Buat database** baru: `orior-production`
3. **Copy connection string**: 
   ```
   mysql://username:password@host/database_name?sslaccept=strict
   ```
4. **Import schema** dengan PlanetScale CLI atau phpMyAdmin

### Option 2: Railway
1. **Daftar** di [railway.app](https://railway.app)
2. **Deploy MySQL** dari template
3. **Copy connection details**

### Option 3: Aiven
1. **Daftar** di [aiven.io](https://aiven.io) (gratis $300 credit)
2. **Create MySQL service**
3. **Download SSL certificate**

## 🛠️ Setup Database Schema

Import file SQL ini ke database cloud Anda:

```sql
-- Import dari file: database_simple.sql
CREATE DATABASE IF NOT EXISTS your_database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE your_database_name;

-- Tabel users untuk admin
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel products untuk barang
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    kode_unik VARCHAR(32) NOT NULL UNIQUE,
    status ENUM('ori','palsu') NOT NULL DEFAULT 'ori',
    qrcode_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin default (password: admin123)
INSERT INTO users (username, password_hash) VALUES 
('admin', '$2y$10$xSORZoLBRxvrNFt8llQpquE4NYrnZ3bMGAdQrQYf9ri0mv7uC48ta');
```

## 📂 Setup Repository

1. **Push code** ke Git repository:
```bash
git init
git add .
git commit -m "Initial commit - Orior QR Validation System"
git branch -M main
git remote add origin https://github.com/yourusername/orior-qr-system.git
git push -u origin main
```

## ⚙️ Deploy ke Vercel

### Step 1: Connect Repository
1. **Login** ke [vercel.com](https://vercel.com)
2. **Import Git Repository**
3. **Select** repository Anda
4. **Framework**: Pilih "Other"

### Step 2: Configure Environment Variables
Tambahkan environment variables berikut di **Vercel Dashboard > Settings > Environment Variables**:

```env
# Database Configuration
DB_HOST=your-cloud-mysql-host.com
DB_NAME=your_production_database_name
DB_USER=your_database_user
DB_PASSWORD=your_secure_database_password

# Session Security
SESSION_SECRET=your-random-32-character-secret-key

# Application Settings
APP_ENV=production
APP_DEBUG=false
```

### Step 3: Deploy
1. **Klik Deploy**
2. **Tunggu build** selesai (2-3 menit)
3. **Access URL**: https://your-project-name.vercel.app

## ⚙️ API Structure

Aplikasi menggunakan struktur API Vercel yang benar:

### � File Structure
```
/
├── api/
│   ├── index.php          # Main API router (serverless function)
│   └── validate.php       # Legacy validation endpoint
├── admin/                 # Admin panel files  
│   ├── admin_login.php
│   ├── admin_dashboard.php
│   └── add_product.php
├── config/                # Configuration files
│   ├── database.php       # Database connection
│   └── cloud-storage.php  # QR storage helpers
├── index.php             # Main page
├── scan.php              # QR Scanner
├── validate.php          # Legacy validation (redirects to API)
└── vercel.json           # Vercel configuration
```

### 🔗 API Endpoints
- **Main API**: `/api/index.php?action=validate`
- **Legacy**: `/validate.php` (redirects to API)
- **System Info**: `/api/index.php?action=info`
- **Admin Panel**: `/admin/admin_login.php`

### vercel.json
```json
{
  "version": 2,
  "functions": {
    "*.php": {
      "runtime": "vercel-php@0.7.4"
    },
    "admin/*.php": {
      "runtime": "vercel-php@0.7.4"
    },
    "api/*.php": {
      "runtime": "vercel-php@0.7.4"
    }
  },
  "routes": [
    {
      "src": "/(.*)",
      "dest": "/$1"
    }
  ]
}
```

### Database Config (.env)
```env
DB_HOST=your-host.com
DB_NAME=your_db_name
DB_USER=your_username
DB_PASSWORD=your_password
SESSION_SECRET=your-session-secret
APP_ENV=production
```

## 🌍 Custom Domain (Optional)

1. **Vercel Dashboard** > Project > Domains
2. **Add domain**: `yourdomain.com`
3. **Update DNS records** sesuai instruksi Vercel
4. **SSL otomatis** akan aktif

## 📱 Testing Deployment

### 1. Main Page
- URL: `https://your-app.vercel.app/`
- Should show: Landing page dengan tombol scan

### 2. QR Scanner
- URL: `https://your-app.vercel.app/scan.php`
- Should work: Camera access dan QR scanning

### 3. Admin Panel
- URL: `https://your-app.vercel.app/admin/admin_login.php`
- Login: `admin` / `admin123`

### 4. API Validation
- URL: `https://your-app.vercel.app/api/validate.php`
- Test: POST dengan parameter `kode`

## 🐛 Troubleshooting

### Runtime Version Errors
**Error**: `The Runtime "vercel-php@0.x.x" is using "nodejs14.x", which is discontinued`

**Solution**: 
1. Update `vercel.json` dengan versi terbaru:
```json
{
  "functions": {
    "*.php": {
      "runtime": "vercel-php@0.7.4"
    }
  }
}
```

2. **Available Versions**:
- `vercel-php@0.7.4` - PHP 8.3.x + Node.js 22.x ✅ **Recommended**
- `vercel-php@0.6.2` - PHP 8.2.x + Node.js autodetect
- `vercel-php@0.5.5` - PHP 8.1.x + Node.js autodetect

### Database Connection Error
1. **Check environment variables** di Vercel Dashboard
2. **Test connection string** secara manual
3. **Verify SSL settings** untuk database cloud
4. **Check firewall rules** di database provider

### Memory Limits
**Error**: Memory exhausted  
**Solution**: Optimize queries dan gunakan pagination

### Cold Starts
- **Normal**: ~250ms untuk cold start
- **Optimization**: Gunakan connection pooling
- **Caching**: Implement Redis jika diperlukan

### Session Issues
1. **Set SESSION_SECRET** di environment variables
2. **Check PHP session** configuration
3. **Verify secure cookie settings**

## 🔄 Auto Deployment

Setiap push ke `main` branch akan otomatis trigger deployment baru di Vercel.

## 📊 Monitoring

- **Vercel Analytics**: Built-in traffic monitoring
- **Error Logs**: Vercel Dashboard > Functions > Logs
- **Performance**: Speed Insights otomatis aktif

## 🎯 Production Checklist

- [x] Database cloud configured
- [x] Environment variables set
- [x] SSL certificate active
- [x] Custom domain configured (optional)
- [x] Admin password changed
- [x] Error logging enabled
- [x] Performance monitoring active

## 🚀 Deploy Commands

```bash
# Install Vercel CLI
npm i -g vercel

# Login to Vercel
vercel login

# Deploy to production
vercel --prod

# Preview deployment
vercel
```

**Your Orior QR Validation System is now ready for global access! 🌍**
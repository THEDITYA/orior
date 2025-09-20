# 🍃 Deployment Guide - Orior QR Validation System (MongoDB Atlas)

## Overview
Panduan lengkap untuk deploy Orior QR Validation System ke Vercel dengan MongoDB Atlas.

## 📋 Prerequisites
- Akun Vercel (gratis)
- Akun MongoDB Atlas (gratis)
- Repository GitHub yang sudah di-push
- Composer untuk MongoDB PHP library (opsional untuk local development)

## 🍃 Step 1: Setup MongoDB Atlas

### Create MongoDB Atlas Cluster
1. **Daftar di MongoDB Atlas**
   - Kunjungi: https://mongodb.com/cloud/atlas
   - Sign up dengan email atau GitHub

2. **Create New Cluster**
   ```bash
   # 1. Create Organization (jika belum ada)
   # 2. Create Project: "orior-qr-system"  
   # 3. Build Database > Shared (Free)
   # 4. Provider: AWS / Region: us-east-1
   # 5. Cluster Name: Cluster0
   ```

3. **Database Access Setup**
   ```bash
   # 1. Database Access > Add New Database User
   # Username: admin
   # Password: admin123 (atau generate)
   # Database User Privileges: Atlas admin
   ```

4. **Network Access Setup**
   ```bash
   # 1. Network Access > Add IP Address
   # 2. Add: 0.0.0.0/0 (Allow access from anywhere)
   # ⚠️  Untuk production, gunakan IP spesifik
   ```

5. **Get Connection String**
   ```bash
   # 1. Cluster > Connect > Drivers
   # 2. Driver: PHP, Version: 1.15 or later
   # 3. Copy connection string:
   mongodb+srv://admin:<password>@cluster0.xxxxx.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0
   ```

## 🗄️ Step 2: Setup Database Schema

### Opsi A: Menggunakan Setup Script (Recommended)
```bash
# 1. Set environment variable
export MONGODB_URI="mongodb+srv://admin:admin123@cluster0.8azrv7a.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0"
export DB_NAME="validasi_barang"

# 2. Install MongoDB PHP extension (jika lokal)
# composer require mongodb/mongodb

# 3. Run setup script
php cloud_setup.php
```

### Opsi B: Manual Setup via Atlas UI
1. **Create Database**: `validasi_barang`
2. **Create Collections**:
   ```javascript
   // Collection: users
   {
     "_id": ObjectId(),
     "username": "admin",
     "password": "$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi",
     "email": "admin@orior.local", 
     "role": "admin",
     "created_at": ISODate()
   }

   // Collection: products  
   {
     "_id": ObjectId(),
     "name": "Laptop Dell XPS 13",
     "code": "LPT001",
     "description": "Ultrabook premium dengan prosesor Intel i7",
     "qr_code": "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=LPT001",
     "status": "active",
     "created_at": ISODate()
   }
   ```

3. **Create Indexes**:
   ```javascript
   // users collection
   db.users.createIndex({ "username": 1 }, { unique: true })
   
   // products collection  
   db.products.createIndex({ "code": 1 }, { unique: true })
   db.products.createIndex({ "status": 1 })
   db.products.createIndex({ "created_at": -1 })
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
MONGODB_URI=mongodb+srv://admin:admin123@cluster0.8azrv7a.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0
DB_NAME=validasi_barang
```

**⚠️ Important**: Ganti `admin123` dengan password yang Anda buat di Atlas.

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

### MongoDB Connection Error
```bash
# Error: MongoDB connection failed
✅ Fixes:
- Periksa MONGODB_URI di Vercel environment variables
- Pastikan password tidak mengandung karakter khusus (@, /, dll)
- Cek Network Access di Atlas (whitelist 0.0.0.0/0)
- Verifikasi database user permissions
```

### MongoDB Extension Not Found
```bash
# Error: mongodb extension not available
✅ For Vercel: Ini normal, sistem akan fallback ke setup page
✅ For Local: composer require mongodb/mongodb
```

### Authentication Failed
```bash
# Error: Authentication failed
✅ Fixes:
- Periksa username/password di Atlas
- Escape special characters dalam URI
- Gunakan URL encoding untuk password
```

## 📊 Performance Optimization

### MongoDB Indexes
```javascript
// Recommended indexes untuk performa optimal
db.users.createIndex({ "username": 1 }, { unique: true })
db.users.createIndex({ "email": 1 })

db.products.createIndex({ "code": 1 }, { unique: true })
db.products.createIndex({ "status": 1 })
db.products.createIndex({ "name": "text", "description": "text" })
db.products.createIndex({ "created_at": -1 })
```

### Vercel Settings
```json
// vercel.json optimization
{
  "functions": {
    "api/*.php": {
      "runtime": "vercel-php@0.7.4",
      "maxDuration": 15
    }
  },
  "env": {
    "TZ": "Asia/Jakarta"
  }
}
```

### Atlas Performance Tips
```bash
# 1. Gunakan connection pooling
# 2. Enable compression: compressors=zstd,zlib
# 3. Set proper read/write concerns
# 4. Monitor via Atlas Performance Advisor
```

## 🔒 Security Best Practices

### MongoDB Atlas Security
- [ ] IP Whitelist configured (tidak 0.0.0.0/0 untuk production)
- [ ] Database user dengan minimal permissions
- [ ] Connection string tidak di-commit ke Git
- [ ] Enable MongoDB Auditing (paid feature)
- [ ] Regular backup enabled

### Application Security
- [ ] Environment variables set di Vercel
- [ ] Admin password sudah diganti dari default
- [ ] HTTPS enabled (default di Vercel)
- [ ] Input validation enabled
- [ ] Session management secure

## 💡 MongoDB Atlas Benefits

### Free Tier Limits
```bash
✅ 512 MB storage
✅ Shared RAM dan CPU
✅ No credit card required
✅ Up to 100 database connections
✅ Community support
```

### Scaling Options
```bash
📈 Dedicated clusters mulai dari $9/month
📈 Auto-scaling available
📈 Global clusters dengan multi-region
📈 Advanced monitoring & alerting
```

## 📞 Support & Resources

### Documentation Links
- [MongoDB Atlas Documentation](https://docs.atlas.mongodb.com/)
- [MongoDB PHP Library](https://docs.mongodb.com/php-library/current/)
- [Vercel PHP Runtime](https://vercel.com/docs/functions/serverless-functions/runtimes/php)

### Common Commands
```bash
# Redeploy
git push origin main

# Check Vercel logs
vercel logs

# Environment variables
vercel env ls
vercel env add MONGODB_URI

# Local development
composer require mongodb/mongodb
php cloud_setup.php
```

### MongoDB Compass (GUI Tool)
```bash
# Download MongoDB Compass untuk GUI database management
https://mongodb.com/products/compass

# Connection string sama dengan aplikasi
mongodb+srv://admin:password@cluster0.xxxxx.mongodb.net/
```

---

## 🎯 Quick Start Checklist

- [ ] 1. Create MongoDB Atlas account & cluster
- [ ] 2. Setup database user & network access
- [ ] 3. Copy connection string
- [ ] 4. Run `php cloud_setup.php` (opsional)
- [ ] 5. Set MONGODB_URI di Vercel environment variables
- [ ] 6. Deploy: `git push origin main`
- [ ] 7. Test application di Vercel URL
- [ ] 8. Login admin dan verify data

🎉 **Selamat!** Orior QR Validation System sekarang running dengan MongoDB Atlas!

**Default credentials:**
- **Username**: `admin`
- **Password**: `admin123` ⚠️ (segera ganti!)

**MongoDB Atlas Dashboard**: https://cloud.mongodb.com/
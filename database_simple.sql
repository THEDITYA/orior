-- Database setup untuk Sistem Validasi Barang QR Code
-- Jalankan query ini di phpMyAdmin atau MySQL client

-- 1. Buat database
CREATE DATABASE IF NOT EXISTS validasi_barang CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE validasi_barang;

-- 2. Tabel users untuk admin
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabel products untuk barang
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    kode_unik VARCHAR(32) NOT NULL UNIQUE,
    status ENUM('ori','palsu') NOT NULL DEFAULT 'ori',
    qrcode_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Insert admin default
-- Username: admin, Password: admin123
INSERT INTO users (username, password_hash) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 5. Insert sample products untuk testing
INSERT INTO products (nama_barang, kode_unik, status, qrcode_path) VALUES 
('iPhone 15 Pro Max', 'ABC123DEF456', 'ori', 'ABC123DEF456.png'),
('Nike Air Jordan', 'XYZ789GHI012', 'ori', 'XYZ789GHI012.png'),
('Tas Louis Vuitton (Palsu)', 'FAKE12345678', 'palsu', 'FAKE12345678.png');

-- Selesai! Database siap digunakan.
<?php
/**
 * Database Setup Script for Production
 * Run this script to create database and tables
 */

// Database configuration
$host = 'localhost';
$dbname = 'validasi_barang';
$username = 'root';
$password = '';

try {
    // Connect to MySQL (without database)
    $pdo = new PDO("mysql:host={$host}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '{$dbname}' created successfully.\n";
    
    // Connect to the created database
    $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL UNIQUE,
            `password_hash` varchar(255) NOT NULL,
            `email` varchar(100) DEFAULT NULL,
            `role` enum('admin','user') DEFAULT 'admin',
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ Table 'users' created successfully.\n";
    
    // Create products table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `products` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `category` varchar(100) NOT NULL,
            `description` text,
            `qr_data` varchar(255) NOT NULL UNIQUE,
            `qr_code_path` varchar(255) DEFAULT NULL,
            `status` enum('active','inactive') DEFAULT 'active',
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `qr_data` (`qr_data`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ Table 'products' created successfully.\n";
    
    // Insert default admin user
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password_hash, role) VALUES (?, ?, ?)");
    $stmt->execute(['admin', $adminPassword, 'admin']);
    echo "✅ Default admin user created (admin/admin123).\n";
    
    // Insert sample products
    $sampleProducts = [
        ['iPhone 15 Pro', 'Elektronik', 'Smartphone premium dari Apple dengan chip A17 Pro'],
        ['Nike Air Max 270', 'Fashion', 'Sepatu running dengan teknologi Air Max terbaru'],
        ['Samsung Galaxy S24 Ultra', 'Elektronik', 'Flagship Android dengan S Pen dan kamera 200MP'],
        ['Adidas Ultraboost 22', 'Fashion', 'Sepatu lari dengan teknologi Boost untuk kenyamanan maksimal'],
        ['MacBook Pro M3', 'Elektronik', 'Laptop profesional dengan chip M3 untuk performa tinggi']
    ];
    
    $insertProduct = $pdo->prepare("INSERT IGNORE INTO products (name, category, description, qr_data) VALUES (?, ?, ?, ?)");
    
    foreach ($sampleProducts as $product) {
        $qrData = 'ORIOR_' . strtoupper(uniqid()) . '_' . time();
        $insertProduct->execute([
            $product[0], 
            $product[1], 
            $product[2], 
            $qrData
        ]);
        echo "✅ Sample product added: {$product[0]} (QR: {$qrData})\n";
        usleep(100000); // Small delay to ensure unique timestamps
    }
    
    echo "\n🎉 Database setup completed successfully!\n";
    echo "📊 Admin Panel: http://localhost/orior/admin/admin_login.php\n";
    echo "🔐 Login: admin / admin123\n";
    echo "🔍 Scanner: http://localhost/orior/scan\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
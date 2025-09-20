<?php
/**
 * Cloud Database Setup Script
 * Script untuk setup database di cloud provider (PlanetScale, Railway, Aiven, dll)
 * 
 * Usage:
 * 1. Set environment variables untuk koneksi database
 * 2. Run: php cloud_setup.php
 * 
 * Environment Variables Required:
 * - DB_HOST: Database host
 * - DB_NAME: Database name
 * - DB_USER: Database username  
 * - DB_PASSWORD: Database password
 */

echo "🚀 Orior QR System - Cloud Database Setup\n";
echo "========================================\n\n";

// Check if running in CLI
if (php_sapi_name() !== 'cli') {
    die("⚠️  This script must be run from command line\n");
}

// Get database credentials from environment variables
$host = getenv('DB_HOST') ?: readline("DB Host: ");
$dbname = getenv('DB_NAME') ?: readline("DB Name (default: validasi_barang): ") ?: 'validasi_barang';
$username = getenv('DB_USER') ?: readline("DB User: ");
$password = getenv('DB_PASSWORD') ?: readline("DB Password: ");

if (empty($host) || empty($username)) {
    die("❌ Database host and username are required\n");
}

echo "\n📡 Testing database connection...\n";

try {
    $dsn = "mysql:host={$host};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::ATTR_TIMEOUT => 10,
    ]);
    
    echo "✅ Connected to database server\n";
    
    // Create database if not exists
    echo "📦 Creating database if not exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '{$dbname}' ready\n";
    
    // Connect to specific database
    $pdo->exec("USE `{$dbname}`");
    
    echo "\n🏗️  Creating tables...\n";
    
    // Create users table
    $createUsers = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            role VARCHAR(50) DEFAULT 'admin',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_username (username)
        ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ";
    
    $pdo->exec($createUsers);
    echo "✅ Users table created\n";
    
    // Create products table
    $createProducts = "
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(100) UNIQUE NOT NULL,
            description TEXT,
            qr_code TEXT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_code (code),
            INDEX idx_status (status)
        ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ";
    
    $pdo->exec($createProducts);
    echo "✅ Products table created\n";
    
    echo "\n👤 Setting up admin user...\n";
    
    // Check if admin already exists
    $checkAdmin = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $checkAdmin->execute(['admin']);
    
    if ($checkAdmin->fetchColumn() == 0) {
        // Create admin user (password: admin123)
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $insertAdmin = $pdo->prepare("
            INSERT INTO users (username, password, email, role) 
            VALUES (?, ?, ?, ?)
        ");
        $insertAdmin->execute(['admin', $adminPassword, 'admin@orior.local', 'admin']);
        echo "✅ Admin user created (username: admin, password: admin123)\n";
    } else {
        echo "ℹ️  Admin user already exists\n";
    }
    
    echo "\n📦 Adding sample products...\n";
    
    // Sample products data
    $sampleProducts = [
        [
            'name' => 'Laptop Dell XPS 13',
            'code' => 'LPT001',
            'description' => 'Ultrabook premium dengan prosesor Intel i7 dan RAM 16GB',
        ],
        [
            'name' => 'Mouse Wireless Logitech MX Master 3',
            'code' => 'MSE002',
            'description' => 'Mouse nirkabel dengan sensor presisi tinggi dan baterai tahan lama',
        ],
        [
            'name' => 'Keyboard Mechanical RGB',
            'code' => 'KBD003',
            'description' => 'Keyboard mekanik dengan switch Cherry MX dan backlight RGB',
        ],
        [
            'name' => 'Monitor 4K Samsung',
            'code' => 'MON004',
            'description' => 'Monitor 27 inci dengan resolusi 4K dan teknologi HDR',
        ],
        [
            'name' => 'Webcam Logitech C920',
            'code' => 'WBC005',
            'description' => 'Webcam HD dengan autofocus dan mikrofon noise reduction',
        ]
    ];
    
    $insertProduct = $pdo->prepare("
        INSERT IGNORE INTO products (name, code, description, qr_code) 
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($sampleProducts as $product) {
        $qrCode = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($product['code']);
        $insertProduct->execute([
            $product['name'],
            $product['code'],
            $product['description'],
            $qrCode
        ]);
    }
    
    echo "✅ Sample products added\n";
    
    // Verify setup
    echo "\n🔍 Verifying setup...\n";
    
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $productCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    
    echo "✅ Users: {$userCount}\n";
    echo "✅ Products: {$productCount}\n";
    
    echo "\n🎉 Cloud database setup completed successfully!\n\n";
    
    echo "📋 Summary:\n";
    echo "- Database: {$dbname}\n";
    echo "- Host: {$host}\n";
    echo "- Users table: ✅\n";
    echo "- Products table: ✅\n";
    echo "- Admin user: admin / admin123\n";
    echo "- Sample products: {$productCount} items\n\n";
    
    echo "🔗 Next steps:\n";
    echo "1. Set environment variables in Vercel:\n";
    echo "   DB_HOST={$host}\n";
    echo "   DB_NAME={$dbname}\n";
    echo "   DB_USER={$username}\n";
    echo "   DB_PASSWORD=****** (your password)\n\n";
    echo "2. Deploy to Vercel: git push origin main\n";
    echo "3. Test your application at your-app.vercel.app\n\n";
    
    echo "⚠️  Remember to change the default admin password!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    echo "\n🔧 Troubleshooting:\n";
    echo "1. Check your database credentials\n";
    echo "2. Ensure database server is running\n";
    echo "3. Verify network connectivity\n";
    echo "4. Check firewall settings\n\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
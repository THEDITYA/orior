<?php
/**
 * Database Migration Script - Update to New Structure
 */

try {
    $pdo = new PDO('mysql:host=localhost;dbname=validasi_barang', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔄 Migrating database to new structure...\n";
    
    // Add missing columns to users table
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN email varchar(100) DEFAULT NULL");
        echo "✅ Added email column to users table.\n";
    } catch (Exception $e) {
        echo "ℹ️ Email column already exists or error: " . $e->getMessage() . "\n";
    }
    
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN role enum('admin','user') DEFAULT 'admin'");
        echo "✅ Added role column to users table.\n";
    } catch (Exception $e) {
        echo "ℹ️ Role column already exists or error: " . $e->getMessage() . "\n";
    }
    
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "✅ Added updated_at column to users table.\n";
    } catch (Exception $e) {
        echo "ℹ️ Updated_at column already exists or error: " . $e->getMessage() . "\n";
    }
    
    // Update products table structure
    try {
        $pdo->exec("ALTER TABLE products ADD COLUMN name varchar(255)");
        $pdo->exec("ALTER TABLE products ADD COLUMN category varchar(100)");
        $pdo->exec("ALTER TABLE products ADD COLUMN description text");
        $pdo->exec("ALTER TABLE products ADD COLUMN qr_data varchar(255)");
        echo "✅ Added new columns to products table.\n";
    } catch (Exception $e) {
        echo "ℹ️ New columns may already exist: " . $e->getMessage() . "\n";
    }
    
    // Migrate existing data if needed
    try {
        $pdo->exec("UPDATE products SET name = nama_barang WHERE name IS NULL OR name = ''");
        $pdo->exec("UPDATE products SET category = 'Unknown' WHERE category IS NULL OR category = ''");
        $pdo->exec("UPDATE products SET qr_data = kode_unik WHERE qr_data IS NULL OR qr_data = ''");
        echo "✅ Migrated existing product data.\n";
    } catch (Exception $e) {
        echo "ℹ️ Data migration issue: " . $e->getMessage() . "\n";
    }
    
    // Add unique constraint for qr_data
    try {
        $pdo->exec("ALTER TABLE products ADD UNIQUE KEY unique_qr_data (qr_data)");
        echo "✅ Added unique constraint for qr_data.\n";
    } catch (Exception $e) {
        echo "ℹ️ Unique constraint may already exist: " . $e->getMessage() . "\n";
    }
    
    // Insert admin user
    try {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password_hash) VALUES (?, ?)");
        $stmt->execute(['admin', $adminPassword]);
        echo "✅ Admin user created (admin/admin123).\n";
    } catch (Exception $e) {
        echo "ℹ️ Admin user creation: " . $e->getMessage() . "\n";
    }
    
    // Add some sample products with new structure
    $sampleProducts = [
        ['iPhone 15 Pro', 'Elektronik', 'Smartphone premium dari Apple'],
        ['Nike Air Max', 'Fashion', 'Sepatu olahraga premium'],
        ['Samsung Galaxy S24', 'Elektronik', 'Android flagship terbaru']
    ];
    
    $insertProduct = $pdo->prepare("INSERT IGNORE INTO products (name, category, description, qr_data) VALUES (?, ?, ?, ?)");
    
    foreach ($sampleProducts as $product) {
        $qrData = 'ORIOR_' . strtoupper(uniqid()) . '_' . time();
        try {
            $insertProduct->execute([
                $product[0], 
                $product[1], 
                $product[2], 
                $qrData
            ]);
            echo "✅ Added sample product: {$product[0]} (QR: {$qrData})\n";
        } catch (Exception $e) {
            echo "ℹ️ Product insertion: " . $e->getMessage() . "\n";
        }
        usleep(100000);
    }
    
    echo "\n🎉 Database migration completed!\n";
    echo "🔐 Admin Login: admin/admin123\n";
    
} catch (Exception $e) {
    echo "❌ Migration Error: " . $e->getMessage() . "\n";
}
?>
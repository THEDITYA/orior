<?php
/**
 * MongoDB Atlas Setup Script
 * Script untuk setup MongoDB Atlas dan inisialisasi collections
 * 
 * Usage:
 * 1. Set environment variables untuk koneksi MongoDB
 * 2. Run: php cloud_setup.php
 * 
 * Environment Variables Required:
 * - MONGODB_URI: MongoDB connection string
 * - DB_NAME: Database name (default: validasi_barang)
 */

echo "🍃 Orior QR System - MongoDB Atlas Setup\n";
echo "======================================\n\n";

// Check if running in CLI
if (php_sapi_name() !== 'cli') {
    die("⚠️  This script must be run from command line\n");
}

// Check if MongoDB extension is loaded
if (!extension_loaded('mongodb')) {
    die("❌ MongoDB PHP extension is not installed.\n" .
        "Install with: composer require mongodb/mongodb\n" .
        "Or: pecl install mongodb\n");
}

// Get database credentials from environment variables
$mongoUri = getenv('MONGODB_URI') ?: readline("MongoDB URI: ");
$dbname = getenv('DB_NAME') ?: readline("DB Name (default: validasi_barang): ") ?: 'validasi_barang';

if (empty($mongoUri)) {
    die("❌ MongoDB URI is required\n");
}

echo "\n🔗 Testing MongoDB connection...\n";

try {
    $client = new MongoDB\Client($mongoUri, [
        'serverSelectionTimeoutMS' => 5000,
        'connectTimeoutMS' => 10000,
    ]);
    
    // Test connection
    $client->listDatabases();
    echo "✅ Connected to MongoDB Atlas\n";
    
    // Select database
    $db = $client->selectDatabase($dbname);
    echo "✅ Using database: {$dbname}\n";
    
    echo "\n📂 Setting up collections...\n";
    
    // Create users collection with indexes
    echo "👤 Setting up users collection...\n";
    $usersCollection = $db->selectCollection('users');
    
    // Create unique index on username
    $usersCollection->createIndex(['username' => 1], ['unique' => true]);
    echo "✅ Users collection indexed\n";
    
    // Check if admin user exists
    $existingAdmin = $usersCollection->findOne(['username' => 'admin']);
    
    if (!$existingAdmin) {
        // Create admin user
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $adminUser = [
            'username' => 'admin',
            'password' => $adminPassword,
            'email' => 'admin@orior.local',
            'role' => 'admin',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];
        
        $result = $usersCollection->insertOne($adminUser);
        echo "✅ Admin user created (ID: " . $result->getInsertedId() . ")\n";
    } else {
        echo "ℹ️  Admin user already exists\n";
    }
    
    // Create products collection with indexes
    echo "\n📦 Setting up products collection...\n";
    $productsCollection = $db->selectCollection('products');
    
    // Create indexes
    $productsCollection->createIndex(['code' => 1], ['unique' => true]);
    $productsCollection->createIndex(['status' => 1]);
    $productsCollection->createIndex(['created_at' => -1]);
    echo "✅ Products collection indexed\n";
    
    // Sample products data
    $sampleProducts = [
        [
            'name' => 'Laptop Dell XPS 13',
            'code' => 'LPT001',
            'description' => 'Ultrabook premium dengan prosesor Intel i7 dan RAM 16GB',
            'qr_code' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=LPT001',
            'status' => 'active',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Mouse Wireless Logitech MX Master 3',
            'code' => 'MSE002', 
            'description' => 'Mouse nirkabel dengan sensor presisi tinggi dan baterai tahan lama',
            'qr_code' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=MSE002',
            'status' => 'active',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Keyboard Mechanical RGB',
            'code' => 'KBD003',
            'description' => 'Keyboard mekanik dengan switch Cherry MX dan backlight RGB',
            'qr_code' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=KBD003',
            'status' => 'active',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Monitor 4K Samsung',
            'code' => 'MON004',
            'description' => 'Monitor 27 inci dengan resolusi 4K dan teknologi HDR',
            'qr_code' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=MON004',
            'status' => 'active',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Webcam Logitech C920',
            'code' => 'WBC005',
            'description' => 'Webcam HD dengan autofocus dan mikrofon noise reduction',
            'qr_code' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=WBC005',
            'status' => 'active',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]
    ];
    
    echo "📦 Adding sample products...\n";
    $insertedCount = 0;
    
    foreach ($sampleProducts as $product) {
        try {
            // Check if product already exists
            $existing = $productsCollection->findOne(['code' => $product['code']]);
            if (!$existing) {
                $productsCollection->insertOne($product);
                $insertedCount++;
                echo "✅ Added: " . $product['name'] . "\n";
            } else {
                echo "ℹ️  Exists: " . $product['name'] . "\n";
            }
        } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
            echo "⚠️  Skipped: " . $product['name'] . " (duplicate)\n";
        }
    }
    
    echo "✅ {$insertedCount} new products added\n";
    
    // Verify setup
    echo "\n🔍 Verifying setup...\n";
    
    $userCount = $usersCollection->countDocuments([]);
    $productCount = $productsCollection->countDocuments([]);
    
    echo "✅ Users: {$userCount}\n";
    echo "✅ Products: {$productCount}\n";
    
    echo "\n🎉 MongoDB Atlas setup completed successfully!\n\n";
    
    echo "📋 Summary:\n";
    echo "- Database: {$dbname}\n";
    echo "- Collections: users, products\n"; 
    echo "- Users collection: ✅ (with indexes)\n";
    echo "- Products collection: ✅ (with indexes)\n";
    echo "- Admin user: admin / admin123\n";
    echo "- Sample products: {$productCount} items\n\n";
    
    echo "🔗 Next steps for Vercel deployment:\n";
    echo "1. Set environment variables in Vercel:\n";
    echo "   MONGODB_URI={$mongoUri}\n";
    echo "   DB_NAME={$dbname}\n\n";
    echo "2. Deploy to Vercel: git push origin main\n";
    echo "3. Test your application at your-app.vercel.app\n\n";
    
    echo "⚠️  Remember to change the default admin password!\n";
    echo "🔒 Whitelist Vercel IPs in MongoDB Atlas Network Access if needed\n";
    
} catch (MongoDB\Driver\Exception\Exception $e) {
    echo "❌ MongoDB error: " . $e->getMessage() . "\n";
    echo "\n🔧 Troubleshooting:\n";
    echo "1. Check your MongoDB connection string\n";
    echo "2. Ensure network access is configured in Atlas\n";
    echo "3. Verify database user permissions\n";
    echo "4. Check if IP whitelist includes your current IP\n\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n💡 Common issues:\n";
    echo "- MongoDB extension not installed: composer require mongodb/mongodb\n";
    echo "- Connection timeout: check network/firewall\n";
    echo "- Authentication failed: verify username/password\n\n";
    exit(1);
}
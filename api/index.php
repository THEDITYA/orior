<?php
/**
 * Main API Router - Vercel Serverless Function
 * Handles routing untuk semua API endpoints
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Inline Database Configuration for Vercel
function getDatabase() {
    static $pdo = null;
    
    if ($pdo === null) {
        // Environment variables dengan fallback ke nilai lokal
        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'validasi_barang';
        $username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
        $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
        
        $charset = 'utf8mb4';
        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ];
        
        try {
            $pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Fallback to mock data if DB not available
            error_log("Database connection failed: " . $e->getMessage());
            return null; // Will use mock data
        }
    }
    
    return $pdo;
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get the action from URL path or query parameter
$action = $_GET['action'] ?? 'validate';

switch ($action) {
    case 'validate':
        handleValidation();
        break;
    case 'info':
        handleSystemInfo();
        break;
    default:
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'API endpoint not found'
        ]);
}

function handleValidation() {
    // Database connection - inline for Vercel
    try {
        $pdo = getDatabase();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed'
        ]);
        exit;
    }

    // Get code from POST or GET
    $input = json_decode(file_get_contents('php://input'), true);
    $qrData = $input['qr_data'] ?? $_POST['qr_data'] ?? $_GET['code'] ?? '';

    if (empty($qrData)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'QR code data is required'
        ]);
        exit;
    }

    // Mock validation if DB not available
    if ($pdo === null) {
        // Demo QR codes for testing
        $mockProducts = [
            'ORIOR_DEMO1' => ['name' => 'iPhone 15 Pro', 'category' => 'Elektronik'],
            'ORIOR_DEMO2' => ['name' => 'Nike Air Max', 'category' => 'Fashion'],  
            'ORIOR_DEMO3' => ['name' => 'Samsung Galaxy S24', 'category' => 'Elektronik'],
        ];
        
        if (isset($mockProducts[$qrData])) {
            echo json_encode([
                'status' => 'success',
                'valid' => true,
                'message' => 'Product is valid (Demo Mode)',
                'product' => $mockProducts[$qrData]
            ]);
        } else {
            echo json_encode([
                'status' => 'success', 
                'valid' => false,
                'message' => 'QR Code tidak terdaftar (Demo Mode). Try: ORIOR_DEMO1, ORIOR_DEMO2, ORIOR_DEMO3'
            ]);
        }
        exit;
    }

    try {
        // Real database validation
        $stmt = $pdo->prepare('SELECT * FROM products WHERE qr_data = ? LIMIT 1');
        $stmt->execute([$qrData]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            echo json_encode([
                'status' => 'success',
                'valid' => true,
                'message' => 'Product is valid',
                'product' => [
                    'name' => $product['name'],
                    'category' => $product['category'],
                    'description' => $product['description']
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'valid' => false,
                'message' => 'QR Code tidak terdaftar dalam sistem'
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Validation failed: ' . $e->getMessage()
        ]);
    }
}

function handleSystemInfo() {
    echo json_encode([
        'status' => 'success',
        'system' => 'Orior QR Validation System',
        'version' => '1.0.0',
        'runtime' => 'vercel-php@0.7.4',
        'php_version' => PHP_VERSION,
        'timestamp' => date('Y-m-d H:i:s'),
        'endpoints' => [
            '/api/index.php?action=validate' => 'Validate product code',
            '/api/index.php?action=info' => 'System information'
        ]
    ]);
}
?>
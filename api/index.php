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

// MongoDB Configuration for Production
function getDatabase() {
    static $mongodb = null;
    
    if ($mongodb === null) {
        // MongoDB Atlas connection string
        $mongoUri = $_ENV['MONGODB_URI'] ?? getenv('MONGODB_URI') ?: 
            'mongodb+srv://admin:admin123@cluster0.8azrv7a.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0';
        
        // Database name
        $dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'validasi_barang';
        
        try {
            // Check if MongoDB extension is available
            if (!extension_loaded('mongodb')) {
                throw new Exception("MongoDB extension not available");
            }
            
            $client = new MongoDB\Client($mongoUri, [
                'serverSelectionTimeoutMS' => 5000,
                'connectTimeoutMS' => 10000,
            ]);
            
            $mongodb = $client->selectDatabase($dbname);
            
            // Test connection
            $mongodb->command(['ping' => 1]);
            
        } catch (Exception $e) {
            throw new Exception("MongoDB connection failed: " . $e->getMessage());
        }
    }
    
    return $mongodb;
}

// MongoDB Helper Functions
function findDocument($collection, $filter = [], $options = []) {
    $db = getDatabase();
    return $db->selectCollection($collection)->findOne($filter, $options);
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
    // MongoDB connection - Production mode
    try {
        $db = getDatabase();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed: ' . $e->getMessage()
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

    try {
        // MongoDB validation - search by code or qr_code
        $product = findDocument('products', [
            '$or' => [
                ['code' => $qrData],
                ['qr_code' => new MongoDB\BSON\Regex($qrData, 'i')]
            ]
        ]);

        if ($product) {
            echo json_encode([
                'status' => 'success',
                'valid' => true,
                'message' => 'Product is valid',
                'product' => [
                    'id' => (string)$product['_id'],
                    'name' => $product['name'],
                    'code' => $product['code'],
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
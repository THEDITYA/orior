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
    // Database connection
    require_once __DIR__ . '/../config/database.php';

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
    $kode = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $kode = trim($_POST['kode'] ?? '');
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $kode = trim($_GET['kode'] ?? '');
    }
    
    if (empty($kode)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Kode produk tidak boleh kosong'
        ]);
        exit;
    }

    try {
        // Cari produk berdasarkan kode unik
        $stmt = $pdo->prepare('SELECT * FROM products WHERE kode_unik = ? LIMIT 1');
        $stmt->execute([$kode]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            // Produk ditemukan
            echo json_encode([
                'status' => $product['status'], // 'ori' atau 'palsu'
                'nama_barang' => $product['nama_barang'],
                'kode_unik' => $product['kode_unik'],
                'qrcode_path' => $product['qrcode_path'],
                'created_at' => $product['created_at']
            ]);
        } else {
            // Produk tidak ditemukan
            http_response_code(404);
            echo json_encode([
                'status' => 'notfound',
                'message' => 'Kode produk tidak ditemukan dalam database'
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error occurred'
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
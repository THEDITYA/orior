<?php<?php

/**/**

 * API Validation Endpoint - Vercel Serverless Function * API Endpoint untuk Validasi QR Token

 */ * POST/GET /api/validate.php

 * Input: { "token": "..." } atau ?token=...

header('Content-Type: application/json'); * Output: JSON response dengan status validasi

header('Access-Control-Allow-Origin: *'); */

header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

header('Access-Control-Allow-Headers: Content-Type');header('Content-Type: application/json');

header('Access-Control-Allow-Origin: *');

// Handle preflight requestsheader('Access-Control-Allow-Methods: POST, GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {header('Access-Control-Allow-Headers: Content-Type');

    exit(0);

}// Handle preflight OPTIONS request

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

// Database connection    http_response_code(200);

require_once __DIR__ . '/../config/database.php';    exit;

}

try {

    $pdo = getDatabase();require_once __DIR__ . '/../lib/db.php';

} catch (Exception $e) {require_once __DIR__ . '/../lib/qr_helper.php';

    http_response_code(500);require_once __DIR__ . '/../lib/rate_limit.php';

    echo json_encode([

        'status' => 'error',try {

        'message' => 'Database connection failed'    // Get client IP

    ]);    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    exit;    

}    // Apply rate limiting (10 requests per minute)

    RateLimit::handleRateLimit($ip, 10, 60);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kode'])) {    

    $kode = trim($_POST['kode']);    // Get token from request

        $token = null;

    if (empty($kode)) {    

        http_response_code(400);    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        echo json_encode([        $input = json_decode(file_get_contents('php://input'), true);

            'status' => 'error',        $token = $input['token'] ?? $_POST['token'] ?? null;

            'message' => 'Kode produk tidak boleh kosong'    } else {

        ]);        $token = $_GET['token'] ?? null;

        exit;    }

    }    

    // Validate input

    try {    if (empty($token)) {

        // Cari produk berdasarkan kode unik        http_response_code(400);

        $stmt = $pdo->prepare('SELECT * FROM products WHERE kode_unik = ? LIMIT 1');        echo json_encode([

        $stmt->execute([$kode]);            'status' => 'error',

        $product = $stmt->fetch(PDO::FETCH_ASSOC);            'message' => 'Parameter token tidak diberikan',

            'code' => 'MISSING_TOKEN'

        if ($product) {        ]);

            // Produk ditemukan        exit;

            echo json_encode([    }

                'status' => $product['status'], // 'ori' atau 'palsu'    

                'nama_barang' => $product['nama_barang'],    // Validate token length (basic security check)

                'kode_unik' => $product['kode_unik'],    if (strlen($token) > 1000) {

                'qrcode_path' => $product['qrcode_path'],        http_response_code(400);

                'created_at' => $product['created_at']        echo json_encode([

            ]);            'status' => 'error',

        } else {            'message' => 'Token terlalu panjang',

            // Produk tidak ditemukan            'code' => 'INVALID_TOKEN_LENGTH'

            http_response_code(404);        ]);

            echo json_encode([        exit;

                'status' => 'notfound',    }

                'message' => 'Kode produk tidak ditemukan dalam database'    

            ]);    // Validate QR token

        }    $result = QRHelper::validateQRToken($token);

    } catch (PDOException $e) {    

        http_response_code(500);    // Add request info to response

        echo json_encode([    $result['request_info'] = [

            'status' => 'error',        'ip' => $ip,

            'message' => 'Database error occurred'        'timestamp' => date('c'), // ISO 8601 format

        ]);        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'

    }    ];

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['kode'])) {    

    // Support GET method juga    // Set appropriate HTTP status code

    $kode = trim($_GET['kode']);    switch ($result['result']) {

            case 'ORI':

    if (empty($kode)) {            http_response_code(200);

        http_response_code(400);            break;

        echo json_encode([        case 'DUPLIKAT':

            'status' => 'error',            http_response_code(200); // Still success, but duplicate

            'message' => 'Kode produk tidak boleh kosong'            break;

        ]);        case 'PALSUK':

        exit;        case 'TIDAK_DITEMUKAN':

    }            http_response_code(200); // Success response, but product issue

            break;

    try {        default:

        $stmt = $pdo->prepare('SELECT * FROM products WHERE kode_unik = ? LIMIT 1');            http_response_code(500);

        $stmt->execute([$kode]);            $result['status'] = 'error';

        $product = $stmt->fetch(PDO::FETCH_ASSOC);            $result['message'] = 'Terjadi kesalahan dalam validasi';

    }

        if ($product) {    

            echo json_encode([    echo json_encode($result, JSON_PRETTY_PRINT);

                'status' => $product['status'],    

                'nama_barang' => $product['nama_barang'],} catch (PDOException $e) {

                'kode_unik' => $product['kode_unik'],    // Database error

                'qrcode_path' => $product['qrcode_path'],    error_log("API Validate DB Error: " . $e->getMessage());

                'created_at' => $product['created_at']    

            ]);    http_response_code(500);

        } else {    echo json_encode([

            http_response_code(404);        'status' => 'error',

            echo json_encode([        'message' => 'Terjadi kesalahan database',

                'status' => 'notfound',        'code' => 'DATABASE_ERROR'

                'message' => 'Kode produk tidak ditemukan dalam database'    ]);

            ]);    

        }} catch (Exception $e) {

    } catch (PDOException $e) {    // General error

        http_response_code(500);    error_log("API Validate Error: " . $e->getMessage());

        echo json_encode([    

            'status' => 'error',    http_response_code(500);

            'message' => 'Database error occurred'    echo json_encode([

        ]);        'status' => 'error',

    }        'message' => 'Terjadi kesalahan sistem',

} else {        'code' => 'SYSTEM_ERROR'

    http_response_code(405);    ]);

    echo json_encode([}
        'status' => 'error',
        'message' => 'Method not allowed atau parameter tidak lengkap'
    ]);
}
?>
<?php
header('Content-Type: application/json');

// Database connection - Vercel compatible
require_once __DIR__ . '/config/database.php';
$pdo = getDatabase();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kode'])) {
    $kode = trim($_POST['kode']);
    
    if (empty($kode)) {
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
                'qrcode_path' => $product['qrcode_path']
            ]);
        } else {
            // Produk tidak ditemukan
            echo json_encode([
                'status' => 'notfound',
                'message' => 'Kode produk tidak ditemukan dalam database'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error database: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method atau parameter tidak lengkap'
    ]);
}
?>
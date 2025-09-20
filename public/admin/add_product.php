<?php
session_start();

// Check if logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Database connection - Vercel compatible
require_once '../../config/database.php';
$pdo = getDatabase();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $status = $_POST['status'] ?? 'ori';
    
    if (empty($nama_barang)) {
        $error = 'Nama barang harus diisi!';
    } else {
        try {
            // Generate unique code
            $kode_unik = strtoupper(bin2hex(random_bytes(5)));
            
            // Generate QR Code URL untuk cloud storage (Vercel compatible)
            require_once '../../config/cloud-storage.php';
            $qr_filename = $kode_unik . '.png';
            $qr_url = CloudQRStorage::generateQRCode($kode_unik);
            
            // Untuk Vercel, kita simpan URL QR code, bukan file lokal
            // Karena Vercel filesystem read-only
            $qrcode_path = $qr_url; // Simpan URL langsung
            
            // Insert to database
            $stmt = $pdo->prepare('INSERT INTO products (nama_barang, kode_unik, status, qrcode_path) VALUES (?, ?, ?, ?)');
            $result = $stmt->execute([$nama_barang, $kode_unik, $status, $qrcode_path]);
            
            if ($result) {
                $success = "Produk '$nama_barang' berhasil ditambahkan dengan kode: $kode_unik";
            } else {
                $error = 'Gagal menyimpan ke database!';
            }
            
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Validasi Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="admin_dashboard.php" class="text-blue-600 hover:underline mr-4">← Kembali</a>
                    <h1 class="text-xl font-bold text-gray-800">➕ Tambah Produk Baru</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600"><?= htmlspecialchars($_SESSION['admin_username']) ?></span>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-md mx-auto px-4 py-8">
        <!-- Messages -->
        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($success) ?>
            <div class="mt-2">
                <a href="admin_dashboard.php" class="text-green-800 underline">← Kembali ke Dashboard</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-6">Form Tambah Produk</h2>
            
            <form method="POST">
                <div class="mb-4">
                    <label for="nama_barang" class="block text-gray-700 text-sm font-bold mb-2">
                        Nama Produk *
                    </label>
                    <input type="text" id="nama_barang" name="nama_barang" 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                           placeholder="Contoh: Sepatu Nike Air Max"
                           value="<?= htmlspecialchars($_POST['nama_barang'] ?? '') ?>"
                           required autofocus>
                </div>

                <div class="mb-6">
                    <label for="status" class="block text-gray-700 text-sm font-bold mb-2">
                        Status Produk
                    </label>
                    <select id="status" name="status" 
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        <option value="ori" <?= ($_POST['status'] ?? 'ori') === 'ori' ? 'selected' : '' ?>>
                            ✅ ASLI (ORI)
                        </option>
                        <option value="palsu" <?= ($_POST['status'] ?? '') === 'palsu' ? 'selected' : '' ?>>
                            ❌ PALSU
                        </option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        Pilih "PALSU" hanya untuk testing atau demo
                    </p>
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none font-medium">
                    🚀 Simpan & Generate QR Code
                </button>
            </form>

            <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                <h4 class="font-medium text-blue-800 text-sm mb-1">ℹ️ Info:</h4>
                <ul class="text-xs text-blue-700 space-y-1">
                    <li>• Kode unik akan digenerate otomatis</li>
                    <li>• QR Code akan tersimpan di folder /qrcodes</li>
                    <li>• Produk dapat dipindai menggunakan scanner</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
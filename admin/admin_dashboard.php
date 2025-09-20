<?php
session_start();

// Check if logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Database connection - Vercel compatible
require_once '../config/database.php';
$pdo = getDatabase();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

// Handle delete product
if (isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$_POST['delete_id']]);
    $success = 'Produk berhasil dihapus!';
}

// Get all products
$products = $pdo->query('SELECT * FROM products ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Validasi Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">📦 Dashboard Admin</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Halo, <?= htmlspecialchars($_SESSION['admin_username']) ?>!</span>
                    <a href="?logout=1" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Success Message -->
        <?php if (isset($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= $success ?>
        </div>
        <?php endif; ?>

        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Daftar Produk</h2>
            <a href="add_product.php" 
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                ➕ Tambah Produk
            </a>
        </div>

        <!-- Products Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            #
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nama Produk
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kode Unik
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            QR Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            Belum ada produk. <a href="add_product.php" class="text-blue-600 hover:underline">Tambah produk pertama</a>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($products as $index => $product): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?= $index + 1 ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($product['nama_barang']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <code class="bg-gray-100 px-2 py-1 rounded text-sm">
                                <?= htmlspecialchars($product['kode_unik']) ?>
                            </code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($product['status'] === 'ori'): ?>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                                    ✅ ASLI
                                </span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                                    ❌ PALSU
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($product['qrcode_path']): ?>
                                <a href="../qrcodes/<?= htmlspecialchars($product['qrcode_path']) ?>" 
                                   target="_blank" 
                                   class="text-blue-600 hover:underline text-sm">
                                    🔍 Lihat QR
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400 text-sm">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <form method="POST" class="inline" 
                                  onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                <input type="hidden" name="delete_id" value="<?= $product['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    🗑️ Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Navigation Links -->
        <div class="mt-8 text-center">
            <a href="../scan.php" class="text-blue-600 hover:underline">
                📱 Buka Scanner QR Code
            </a>
        </div>
    </div>
</body>
</html>
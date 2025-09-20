<?php
// Ensure this is recognized as a PHP file
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Validasi Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-2xl p-8 max-w-md w-full mx-4">
        <div class="text-center">
            <div class="text-6xl mb-4">🔍</div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Validasi Keaslian Barang</h1>
            <p class="text-gray-600 mb-8">Sistem verifikasi produk dengan QR Code</p>
            
            <div class="space-y-4">
                <a href="scan.php" 
                   class="block w-full bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition duration-300 font-medium">
                    📱 Mulai Scan QR Code
                </a>
                
                <a href="admin/admin_login.php" 
                   class="block w-full bg-gray-100 text-gray-700 py-3 px-6 rounded-lg hover:bg-gray-200 transition duration-300 font-medium">
                    🔐 Login Admin
                </a>
            </div>
            
            <div class="mt-8 text-sm text-gray-500">
                <p class="mb-2">Cara menggunakan:</p>
                <ul class="text-left space-y-1">
                    <li>1. Klik "Mulai Scan QR Code"</li>
                    <li>2. Izinkan akses kamera</li>
                    <li>3. Arahkan ke QR Code produk</li>
                    <li>4. Lihat hasil validasi</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
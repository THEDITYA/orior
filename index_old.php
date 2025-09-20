<?php
require_once 'db.php';
$hasil = null;
if (isset($_GET['kode'])) {
    $kode = $_GET['kode'];
    $stmt = $pdo->prepare('SELECT * FROM products WHERE kode_unik = ? LIMIT 1');
    $stmt->execute([$kode]);
    $barang = $stmt->fetch();
    if ($barang) {
        $hasil = $barang['status'] === 'ori' ? 'ori' : 'palsu';
    } else {
        $hasil = 'notfound';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/js/html5-qrcode.min.js"></script>
    <script src="assets/js/scan.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center">
    <div class="w-full max-w-md bg-white rounded shadow p-6 mt-8">
        <h2 class="text-2xl font-bold mb-4 text-center">Scan QR Code Barang</h2>
        <div id="reader" class="mb-4"></div>
        <form id="manualForm" class="mb-4 flex">
            <input type="text" id="kodeInput" name="kode" placeholder="Atau masukkan kode unik..." class="flex-1 border px-3 py-2 rounded-l" required>
            <button class="bg-blue-600 text-white px-4 py-2 rounded-r">Cek</button>
        </form>
        <?php if ($hasil === 'ori'): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded text-center font-bold">Barang ASLI ✅</div>
        <?php elseif ($hasil === 'palsu'): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded text-center font-bold">Barang PALSU ❌</div>
        <?php elseif ($hasil === 'notfound'): ?>
        <div class="bg-yellow-100 text-yellow-700 p-3 rounded text-center font-bold">Kode tidak ditemukan</div>
        <?php endif; ?>
    </div>
    <script>
    // Inisialisasi scanner
    document.addEventListener('DOMContentLoaded', function() {
        if (window.Html5Qrcode) {
            let scanner = new Html5Qrcode("reader");
            scanner.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: 200 },
                function(decodedText) {
                    window.location = '?kode=' + encodeURIComponent(decodedText);
                },
                function(error) {}
            );
        }
        document.getElementById('manualForm').onsubmit = function(e) {
            if (!document.getElementById('kodeInput').value) e.preventDefault();
        };
    });
    </script>
</body>
</html>
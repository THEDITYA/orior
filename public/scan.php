<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Validasi Barang QR Code</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">🔍 Validasi Keaslian Barang</h1>
            <p class="text-gray-600">Scan QR Code untuk memverifikasi keaslian produk</p>
        </div>

        <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-6">
            <!-- Scanner Area -->
            <div id="scanner-container" class="mb-6">
                <h3 class="text-lg font-semibold mb-3 text-center">📱 Scan QR Code</h3>
                <div id="reader" class="border rounded-lg" style="height: 300px;"></div>
                <p class="text-sm text-gray-500 text-center mt-2">Arahkan kamera ke QR Code produk</p>
            </div>

            <!-- Manual Input -->
            <div class="border-t pt-4">
                <h4 class="font-medium mb-2">💬 Atau masukkan kode manual:</h4>
                <div class="flex gap-2">
                    <input type="text" id="manual-code" placeholder="Masukkan kode produk..." 
                           class="flex-1 px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <button onclick="validateCode()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Cek
                    </button>
                </div>
            </div>

            <!-- Result Area -->
            <div id="result" class="mt-6 hidden">
                <!-- Results will be shown here -->
            </div>
        </div>

        <!-- Admin Link -->
        <div class="text-center mt-8">
            <a href="admin/admin_login.php" class="text-blue-600 hover:underline">🔐 Login Admin</a>
        </div>
    </div>

    <!-- QR Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    
    <script>
        let html5QrCode;

        // Initialize QR Scanner
        document.addEventListener('DOMContentLoaded', function() {
            html5QrCode = new Html5Qrcode("reader");
            startScanner();
        });

        function startScanner() {
            const config = { fps: 10, qrbox: { width: 250, height: 250 } };
            
            html5QrCode.start(
                { facingMode: "environment" }, // Use back camera
                config,
                (decodedText, decodedResult) => {
                    // QR Code detected
                    console.log(`Code matched = ${decodedText}`, decodedResult);
                    validateCode(decodedText);
                },
                (error) => {
                    // QR Code scanning error (can be ignored)
                }
            ).catch(err => {
                console.log("Camera access denied or not available");
                document.getElementById('reader').innerHTML = 
                    '<div class="text-center text-gray-500 p-8">Kamera tidak tersedia.<br>Gunakan input manual di bawah.</div>';
            });
        }

        function validateCode(code = null) {
            const codeToCheck = code || document.getElementById('manual-code').value.trim();
            
            if (!codeToCheck) {
                alert('Masukkan kode produk!');
                return;
            }

            // Show loading
            document.getElementById('result').innerHTML = `
                <div class="text-center p-4">
                    <div class="animate-spin inline-block w-6 h-6 border-[3px] border-current border-t-transparent rounded-full" role="status"></div>
                    <p class="mt-2 text-gray-600">Memvalidasi...</p>
                </div>
            `;
            document.getElementById('result').classList.remove('hidden');

            // Send to validation API
            fetch('validate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'kode=' + encodeURIComponent(codeToCheck)
            })
            .then(response => response.json())
            .then(data => {
                showResult(data);
            })
            .catch(error => {
                console.error('Error:', error);
                showResult({ status: 'error', message: 'Terjadi kesalahan sistem' });
            });
        }

        function showResult(data) {
            let resultHtml = '';
            
            if (data.status === 'ori') {
                resultHtml = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg text-center">
                        <div class="text-2xl mb-2">✅</div>
                        <h3 class="font-bold text-lg">PRODUK ASLI</h3>
                        <p class="text-sm mt-1">${data.nama_barang}</p>
                        <p class="text-xs mt-2 text-green-600">Kode: ${data.kode_unik}</p>
                    </div>
                `;
            } else if (data.status === 'palsu') {
                resultHtml = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-center">
                        <div class="text-2xl mb-2">❌</div>
                        <h3 class="font-bold text-lg">PRODUK PALSU</h3>
                        <p class="text-sm mt-1">${data.nama_barang}</p>
                        <p class="text-xs mt-2 text-red-600">Kode: ${data.kode_unik}</p>
                    </div>
                `;
            } else if (data.status === 'notfound') {
                resultHtml = `
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg text-center">
                        <div class="text-2xl mb-2">⚠️</div>
                        <h3 class="font-bold text-lg">KODE TIDAK DITEMUKAN</h3>
                        <p class="text-sm mt-1">Produk tidak terdaftar dalam sistem</p>
                    </div>
                `;
            } else {
                resultHtml = `
                    <div class="bg-gray-100 border border-gray-400 text-gray-700 px-4 py-3 rounded-lg text-center">
                        <div class="text-2xl mb-2">⚠️</div>
                        <h3 class="font-bold text-lg">KESALAHAN</h3>
                        <p class="text-sm mt-1">${data.message || 'Terjadi kesalahan sistem'}</p>
                    </div>
                `;
            }

            resultHtml += `
                <button onclick="scanAgain()" class="mt-4 w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                    🔍 Scan Lagi
                </button>
            `;

            document.getElementById('result').innerHTML = resultHtml;
        }

        function scanAgain() {
            document.getElementById('result').classList.add('hidden');
            document.getElementById('manual-code').value = '';
        }
    </script>
</body>
</html>
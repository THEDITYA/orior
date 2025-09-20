<?php
// API Route handler for pages
header('Content-Type: text/html; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get page parameter
$page = $_GET['page'] ?? 'index';
$subpage = $_GET['subpage'] ?? '';

// Route to appropriate page
switch ($page) {
    case 'index':
        renderIndexPage();
        break;
    case 'scan':
        renderScanPage();
        break;
    case 'admin':
        renderAdminPage($subpage);
        break;
    default:
        http_response_code(404);
        echo "Page not found";
        break;
}

function renderIndexPage() {
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
                <a href="/scan" 
                   class="block w-full bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition duration-300 font-medium">
                    📱 Mulai Scan QR Code
                </a>
                
                <a href="/admin/admin_login.php" 
                   class="block w-full bg-gray-600 text-white py-3 px-6 rounded-lg hover:bg-gray-700 transition duration-300 font-medium">
                    🔐 Admin Login
                </a>
            </div>
        </div>
        
        <div class="mt-8 pt-6 border-t border-gray-200 text-center text-sm text-gray-500">
            <p>🛡️ Sistem keamanan tingkat tinggi</p>
        </div>
    </div>
</body>
</html>
<?php
}

function renderScanPage() {
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner - Validasi Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<body class="bg-gradient-to-br from-purple-500 to-pink-600 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-6 text-white text-center">
                <h1 class="text-2xl font-bold">QR Code Scanner</h1>
                <p class="mt-2">Arahkan kamera ke QR Code</p>
            </div>
            
            <div class="p-6">
                <div id="qr-reader" class="mb-6"></div>
                
                <div id="qr-reader-results" class="hidden">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <h3 class="font-bold text-green-800 mb-2">QR Code Terdeteksi!</h3>
                        <div id="result-content" class="text-green-700"></div>
                    </div>
                    
                    <button onclick="validateQR()" 
                            class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition duration-300 font-medium">
                        ✅ Validasi Produk
                    </button>
                </div>
                
                <div class="text-center">
                    <a href="/" class="text-purple-600 hover:text-purple-800 font-medium">
                        ← Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
        
        <div id="validation-result" class="max-w-md mx-auto mt-6"></div>
    </div>

    <script>
        let html5QrcodeScanner;
        let currentQrData = null;

        function onScanSuccess(decodedText, decodedResult) {
            currentQrData = decodedText;
            document.getElementById('result-content').innerHTML = `
                <strong>Data:</strong> ${decodedText}
            `;
            document.getElementById('qr-reader-results').classList.remove('hidden');
            
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
            }
        }

        function onScanError(errorMessage) {
            console.log(`QR scan error: ${errorMessage}`);
        }

        // Start QR Scanner
        html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", 
            { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                rememberLastUsedCamera: true
            }
        );
        html5QrcodeScanner.render(onScanSuccess, onScanError);

        // Validate QR function
        async function validateQR() {
            if (!currentQrData) {
                alert('Tidak ada data QR untuk divalidasi');
                return;
            }

            try {
                const response = await fetch('/api?action=validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ qr_data: currentQrData })
                });

                const result = await response.json();
                
                const resultDiv = document.getElementById('validation-result');
                
                if (result.valid) {
                    resultDiv.innerHTML = `
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">✅</span>
                                <div>
                                    <h3 class="font-bold">Produk Valid!</h3>
                                    <p><strong>Nama:</strong> ${result.product?.name || 'N/A'}</p>
                                    <p><strong>Kategori:</strong> ${result.product?.category || 'N/A'}</p>
                                    <p><strong>Divalidasi:</strong> ${new Date().toLocaleString('id-ID')}</p>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">❌</span>
                                <div>
                                    <h3 class="font-bold">Produk Tidak Valid!</h3>
                                    <p>${result.message || 'QR Code tidak terdaftar dalam sistem'}</p>
                                </div>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Validation error:', error);
                document.getElementById('validation-result').innerHTML = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                        <h3 class="font-bold">Error Validasi</h3>
                        <p>Terjadi kesalahan saat memvalidasi QR Code</p>
                    </div>
                `;
            }
        }
    </script>
</body>
</html>
<?php
}

function renderAdminPage($subpage) {
    // Route admin pages
    switch ($subpage) {
        case 'admin_login.php':
        case '':
            renderAdminLogin();
            break;
        case 'admin_dashboard.php':
            renderAdminDashboard();
            break;
        case 'add_product.php':
            renderAddProduct();
            break;
        default:
            http_response_code(404);
            echo "Admin page not found";
            break;
    }
}

function renderAdminLogin() {
    // Include the admin login content from public/admin/admin_login.php
    include '../public/admin/admin_login.php';
}

function renderAdminDashboard() {
    // Include admin dashboard logic
    include '../public/admin/admin_dashboard.php';
}

function renderAddProduct() {
    // Include add product logic
    include '../public/admin/add_product.php';
}
?>
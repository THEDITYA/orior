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

// Inline Database Configuration for Vercel
function getDatabase() {
    static $pdo = null;
    
    if ($pdo === null) {
        // Environment variables dengan fallback ke nilai lokal
        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'validasi_barang';
        $username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
        $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
        
        $charset = 'utf8mb4';
        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ];
        
        try {
            $pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Fallback to mock data if DB not available
            error_log("Database connection failed: " . $e->getMessage());
            return null; // Will use mock data
        }
    }
    
    return $pdo;
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
    <style>
        .scanner-container { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .scan-frame {
            position: relative;
            border: 3px solid #fff;
            border-radius: 20px;
            overflow: hidden;
        }
        .scan-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            border: 2px solid #00ff88;
            border-radius: 10px;
            box-shadow: 0 0 0 9999px rgba(0,0,0,0.5);
            pointer-events: none;
        }
        .scan-corners {
            position: absolute;
            width: 30px;
            height: 30px;
            border: 3px solid #00ff88;
        }
        .corner-tl { top: -3px; left: -3px; border-right: none; border-bottom: none; }
        .corner-tr { top: -3px; right: -3px; border-left: none; border-bottom: none; }
        .corner-bl { bottom: -3px; left: -3px; border-right: none; border-top: none; }
        .corner-br { bottom: -3px; right: -3px; border-left: none; border-top: none; }
        
        .glow-button {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
            transition: all 0.3s ease;
        }
        .glow-button:hover {
            box-shadow: 0 0 30px rgba(59, 130, 246, 0.8);
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="scanner-container min-h-screen">
    <!-- Header -->
    <div class="bg-white/10 backdrop-blur-lg border-b border-white/20">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="text-2xl">🔍</div>
                    <div>
                        <h1 class="text-xl font-bold text-white">QR Scanner</h1>
                        <p class="text-white/80 text-sm">Validasi Keaslian Produk</p>
                    </div>
                </div>
                <a href="/" class="text-white/80 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-6">
        <!-- Scan Methods Toggle -->
        <div class="max-w-md mx-auto mb-6">
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-1">
                <div class="grid grid-cols-2 gap-1">
                    <button id="camera-tab" onclick="switchScanMethod('camera')" 
                            class="scan-method-tab active py-3 px-4 rounded-xl font-medium transition text-white bg-white/20">
                        📷 Kamera
                    </button>
                    <button id="manual-tab" onclick="switchScanMethod('manual')" 
                            class="scan-method-tab py-3 px-4 rounded-xl font-medium transition text-white/70">
                        ⌨️ Manual
                    </button>
                </div>
            </div>
        </div>

        <!-- Camera Scanner -->
        <div id="camera-scanner" class="scan-method max-w-md mx-auto">
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl overflow-hidden shadow-2xl mb-6">
                <div class="p-4">
                    <div class="scan-frame relative mb-4">
                        <div id="qr-reader" class="w-full"></div>
                        <div class="scan-overlay">
                            <div class="scan-corners corner-tl"></div>
                            <div class="scan-corners corner-tr"></div>
                            <div class="scan-corners corner-bl"></div>
                            <div class="scan-corners corner-br"></div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <div class="text-white/80 mb-3">
                            <div class="animate-pulse">📱 Arahkan kamera ke QR Code</div>
                        </div>
                        <div class="flex justify-center space-x-3">
                            <button id="start-scan-btn" onclick="startCamera()" 
                                    class="bg-green-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-green-600 transition glow-button">
                                ▶️ Mulai Scan
                            </button>
                            <button id="stop-scan-btn" onclick="stopCamera()" style="display:none;"
                                    class="bg-red-500 text-white px-6 py-2 rounded-xl font-medium hover:bg-red-600 transition">
                                ⏹️ Stop
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Input Scanner -->
        <div id="manual-scanner" class="scan-method max-w-md mx-auto" style="display:none;">
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 shadow-2xl mb-6">
                <div class="text-center mb-6">
                    <div class="text-4xl mb-3">🔤</div>
                    <h3 class="text-xl font-bold text-white mb-2">Input Manual</h3>
                    <p class="text-white/80">Masukkan kode QR secara manual</p>
                </div>
                
                <form onsubmit="validateManualQR(event)" class="space-y-4">
                    <div>
                        <label class="block text-white font-medium mb-2">Kode QR:</label>
                        <input type="text" id="manual-qr-input" placeholder="Masukkan kode QR..." 
                               class="w-full px-4 py-3 rounded-xl bg-white/20 border border-white/30 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-400 backdrop-blur-sm">
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white py-3 px-6 rounded-xl font-medium hover:bg-blue-700 transition glow-button">
                        ✅ Validasi Sekarang
                    </button>
                </form>
                
                <div class="mt-4 text-center">
                    <button onclick="pasteFromClipboard()" 
                            class="text-white/80 hover:text-white underline text-sm">
                        📋 Paste dari Clipboard
                    </button>
                </div>
            </div>
        </div>

        <!-- QR Detection Result -->
        <div id="qr-reader-results" class="max-w-md mx-auto mb-6" style="display:none;">
            <div class="bg-green-500/20 backdrop-blur-lg border border-green-400/30 rounded-2xl p-4">
                <div class="flex items-center mb-3">
                    <span class="text-2xl mr-3">✅</span>
                    <div>
                        <h3 class="font-bold text-green-100">QR Code Terdeteksi!</h3>
                        <div id="result-content" class="text-green-200 text-sm mt-1"></div>
                    </div>
                </div>
                
                <button onclick="validateQR()" 
                        class="w-full bg-green-600 text-white py-3 px-6 rounded-xl hover:bg-green-700 transition font-medium glow-button">
                    🔍 Validasi Produk
                </button>
            </div>
        </div>

        <!-- Validation Result -->
        <div id="validation-result" class="max-w-md mx-auto"></div>

        <!-- Help Section -->
        <div class="max-w-md mx-auto mt-8">
            <div class="bg-white/5 backdrop-blur-lg rounded-2xl p-4">
                <h4 class="font-bold text-white mb-2">💡 Tips:</h4>
                <ul class="text-white/80 text-sm space-y-1">
                    <li>• Pastikan QR code dalam kondisi yang jelas</li>
                    <li>• Gunakan pencahayaan yang cukup</li>
                    <li>• Input manual jika kamera tidak berfungsi</li>
                    <li>• QR code format: ORIOR_xxxxx</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        let html5QrcodeScanner;
        let currentQrData = null;
        let isScanning = false;

        // Switch between scan methods
        function switchScanMethod(method) {
            // Update tabs
            document.querySelectorAll('.scan-method-tab').forEach(tab => {
                tab.classList.remove('active', 'bg-white/20');
                tab.classList.add('text-white/70');
            });
            
            document.getElementById(method + '-tab').classList.add('active', 'bg-white/20');
            document.getElementById(method + '-tab').classList.remove('text-white/70');
            document.getElementById(method + '-tab').classList.add('text-white');
            
            // Switch content
            document.querySelectorAll('.scan-method').forEach(scanner => {
                scanner.style.display = 'none';
            });
            
            if (method === 'camera') {
                document.getElementById('camera-scanner').style.display = 'block';
            } else {
                document.getElementById('manual-scanner').style.display = 'block';
                stopCamera(); // Stop camera when switching to manual
            }
        }

        // Camera functions
        function startCamera() {
            if (isScanning) return;
            
            html5QrcodeScanner = new Html5QrcodeScanner(
                "qr-reader", 
                { 
                    fps: 10, 
                    qrbox: { width: 200, height: 200 },
                    rememberLastUsedCamera: true,
                    supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
                }
            );
            html5QrcodeScanner.render(onScanSuccess, onScanError);
            
            isScanning = true;
            document.getElementById('start-scan-btn').style.display = 'none';
            document.getElementById('stop-scan-btn').style.display = 'inline-block';
        }

        function stopCamera() {
            if (html5QrcodeScanner && isScanning) {
                html5QrcodeScanner.clear();
                isScanning = false;
                document.getElementById('start-scan-btn').style.display = 'inline-block';
                document.getElementById('stop-scan-btn').style.display = 'none';
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            currentQrData = decodedText;
            document.getElementById('result-content').innerHTML = `
                <strong>Data:</strong> ${decodedText}
            `;
            document.getElementById('qr-reader-results').style.display = 'block';
            
            stopCamera();
            
            // Auto scroll to result
            document.getElementById('qr-reader-results').scrollIntoView({ 
                behavior: 'smooth' 
            });
        }

        function onScanError(errorMessage) {
            // Silent error handling
            console.log(`QR scan error: ${errorMessage}`);
        }

        // Manual input functions
        function validateManualQR(event) {
            event.preventDefault();
            const input = document.getElementById('manual-qr-input');
            currentQrData = input.value.trim();
            
            if (!currentQrData) {
                alert('Mohon masukkan kode QR');
                return;
            }
            
            document.getElementById('result-content').innerHTML = `
                <strong>Data:</strong> ${currentQrData}
            `;
            document.getElementById('qr-reader-results').style.display = 'block';
            
            // Auto scroll and validate
            document.getElementById('qr-reader-results').scrollIntoView({ 
                behavior: 'smooth' 
            });
            
            // Auto validate after 1 second
            setTimeout(() => {
                validateQR();
            }, 1000);
        }

        // Paste from clipboard
        async function pasteFromClipboard() {
            try {
                const text = await navigator.clipboard.readText();
                document.getElementById('manual-qr-input').value = text;
            } catch (err) {
                console.error('Failed to read clipboard:', err);
                alert('Tidak dapat mengakses clipboard. Paste manual menggunakan Ctrl+V');
            }
        }

        // Validate QR function
        async function validateQR() {
            if (!currentQrData) {
                alert('Tidak ada data QR untuk divalidasi');
                return;
            }

            // Show loading
            const resultDiv = document.getElementById('validation-result');
            resultDiv.innerHTML = `
                <div class="bg-blue-500/20 backdrop-blur-lg border border-blue-400/30 rounded-2xl p-4">
                    <div class="flex items-center justify-center">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-white mr-3"></div>
                        <span class="text-white">Memvalidasi...</span>
                    </div>
                </div>
            `;

            try {
                const response = await fetch('/api?action=validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ qr_data: currentQrData })
                });

                const result = await response.json();
                
                if (result.valid) {
                    resultDiv.innerHTML = `
                        <div class="bg-green-500/20 backdrop-blur-lg border border-green-400/30 rounded-2xl p-4">
                            <div class="flex items-start">
                                <span class="text-3xl mr-4">✅</span>
                                <div class="flex-1">
                                    <h3 class="font-bold text-green-100 text-lg mb-2">Produk Valid! 🎉</h3>
                                    <div class="space-y-1 text-green-200">
                                        <p><strong>Nama:</strong> ${result.product?.name || 'N/A'}</p>
                                        <p><strong>Kategori:</strong> ${result.product?.category || 'N/A'}</p>
                                        <p><strong>Status:</strong> <span class="text-green-300">✓ Terdaftar Resmi</span></p>
                                        <p><strong>Waktu:</strong> ${new Date().toLocaleString('id-ID')}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="bg-red-500/20 backdrop-blur-lg border border-red-400/30 rounded-2xl p-4">
                            <div class="flex items-start">
                                <span class="text-3xl mr-4">❌</span>
                                <div class="flex-1">
                                    <h3 class="font-bold text-red-100 text-lg mb-2">Produk Tidak Valid!</h3>
                                    <div class="text-red-200">
                                        <p>${result.message || 'QR Code tidak terdaftar dalam sistem'}</p>
                                        <p class="mt-2 text-sm"><strong>⚠️ Peringatan:</strong> Produk ini mungkin palsu atau tidak resmi</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Validation error:', error);
                resultDiv.innerHTML = `
                    <div class="bg-yellow-500/20 backdrop-blur-lg border border-yellow-400/30 rounded-2xl p-4">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">⚠️</span>
                            <div>
                                <h3 class="font-bold text-yellow-100">Error Validasi</h3>
                                <p class="text-yellow-200 text-sm">Terjadi kesalahan saat memvalidasi QR Code. Coba lagi.</p>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Auto scroll to result
            setTimeout(() => {
                resultDiv.scrollIntoView({ behavior: 'smooth' });
            }, 100);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Default to camera method
            switchScanMethod('camera');
        });
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
    session_start();

    // Database connection - Vercel compatible (inline)
    $pdo = getDatabase();

    // Check if already logged in
    if (isset($_SESSION['admin_id'])) {
        header('Location: /admin/admin_dashboard.php');
        exit;
    }

    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if ($username && $password) {
            // Mock validation if DB not available
            if ($pdo === null) {
                // Demo credentials
                if ($username === 'admin' && $password === 'admin123') {
                    $_SESSION['admin_id'] = 1;
                    $_SESSION['admin_username'] = $username;
                    header('Location: /admin/admin_dashboard.php');
                    exit;
                } else {
                    $error = 'Username atau password salah! (Demo: admin/admin123)';
                }
            } else {
                // Real DB validation
                try {
                    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
                    $stmt->execute([$username]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($user && password_verify($password, $user['password_hash'])) {
                        $_SESSION['admin_id'] = $user['id'];
                        $_SESSION['admin_username'] = $user['username'];
                        header('Location: /admin/admin_dashboard.php');
                        exit;
                    } else {
                        $error = 'Username atau password salah!';
                    }
                } catch (Exception $e) {
                    $error = 'Error database: ' . $e->getMessage();
                }
            }
        } else {
            $error = 'Username dan password harus diisi!';
        }
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Validasi Barang</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">🔐 Login Admin</h1>
            <p class="text-gray-600">Sistem Validasi Barang</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">
                    Username
                </label>
                <input type="text" id="username" name="username" 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" 
                       required autofocus>
            </div>

            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">
                    Password
                </label>
                <input type="password" id="password" name="password" 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" 
                       required>
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none">
                Login
            </button>
        </form>

        <div class="text-center mt-6">
            <a href="/scan" class="text-blue-600 hover:underline">← Kembali ke Scanner</a>
        </div>

        <div class="text-center mt-4 text-sm text-gray-500">
            Default: admin / admin123
        </div>
    </div>
</body>
</html>
<?php
}

function renderAdminDashboard() {
    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /admin/admin_login.php');
        exit;
    }

    // Database connection - Vercel compatible (inline)
    $pdo = getDatabase();

    // Mock data if DB not available
    if ($pdo === null) {
        $productCount = 3; // Mock count
        $recentProducts = [
            [
                'id' => 1,
                'name' => 'iPhone 15 Pro (Demo)',
                'category' => 'Elektronik',
                'qr_data' => 'ORIOR_DEMO1',
                'created_at' => date('Y-m-d H:i:s', time() - 3600)
            ],
            [
                'id' => 2,
                'name' => 'Nike Air Max (Demo)',
                'category' => 'Fashion',
                'qr_data' => 'ORIOR_DEMO2',
                'created_at' => date('Y-m-d H:i:s', time() - 7200)
            ],
            [
                'id' => 3,
                'name' => 'Samsung Galaxy S24 (Demo)',
                'category' => 'Elektronik',
                'qr_data' => 'ORIOR_DEMO3',
                'created_at' => date('Y-m-d H:i:s', time() - 10800)
            ]
        ];
    } else {
        // Real database queries
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM products');
            $stmt->execute();
            $productCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            $stmt = $pdo->prepare('SELECT * FROM products ORDER BY created_at DESC LIMIT 10');
            $stmt->execute();
            $recentProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Fallback to mock data on error
            $productCount = 0;
            $recentProducts = [];
        }
    }
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
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold">📊 Dashboard Admin</h1>
                <?php if ($pdo === null): ?>
                <span class="bg-yellow-500 text-yellow-900 px-2 py-1 rounded text-xs font-bold">DEMO MODE</span>
                <?php endif; ?>
            </div>
            <div class="space-x-4">
                <span>Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?>!</span>
                <a href="?logout=1" class="bg-red-500 px-3 py-1 rounded hover:bg-red-600">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="text-3xl mr-4">📦</div>
                    <div>
                        <h3 class="text-lg font-semibold">Total Produk</h3>
                        <p class="text-2xl font-bold text-blue-600"><?= $productCount ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="text-3xl mr-4">✅</div>
                    <div>
                        <h3 class="text-lg font-semibold">Status</h3>
                        <p class="text-lg font-bold text-green-600">Aktif</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="text-3xl mr-4">🔐</div>
                    <div>
                        <h3 class="text-lg font-semibold">Keamanan</h3>
                        <p class="text-lg font-bold text-green-600">Tinggi</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Kelola Produk</h2>
                <a href="/admin/add_product.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + Tambah Produk
                </a>
            </div>
            
            <?php if (empty($recentProducts)): ?>
                <div class="text-center py-8 text-gray-500">
                    <div class="text-4xl mb-2">📭</div>
                    <p>Belum ada produk yang terdaftar</p>
                    <a href="/admin/add_product.php" class="text-blue-600 hover:underline">Tambah produk pertama</a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">Nama Produk</th>
                                <th class="px-4 py-2 text-left">Kategori</th>
                                <th class="px-4 py-2 text-left">QR Code</th>
                                <th class="px-4 py-2 text-left">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($recentProducts as $product): ?>
                            <tr>
                                <td class="px-4 py-2">#<?= $product['id'] ?></td>
                                <td class="px-4 py-2 font-medium"><?= htmlspecialchars($product['name']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($product['category']) ?></td>
                                <td class="px-4 py-2">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">
                                        ✓ Generated
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-500">
                                    <?= date('d/m/Y H:i', strtotime($product['created_at'])) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center">
            <a href="/" class="text-blue-600 hover:underline">← Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>
<?php
    // Handle logout
    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location: /admin/admin_login.php');
        exit;
    }
}

function renderAddProduct() {
    session_start();

    // Check if user is logged in
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /admin/admin_login.php');
        exit;
    }

    // Database connection - Vercel compatible (inline)
    $pdo = getDatabase();

    $success = '';
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if ($name && $category) {
            // Generate unique QR data
            $qrData = 'ORIOR_' . strtoupper(uniqid()) . '_' . time();
            
            if ($pdo === null) {
                // Demo mode - generate QR code for display
                $qrCodeUrl = "https://qr-server.com/api/v1/create-qr-code/?size=200x200&data=" . urlencode($qrData);
                $success = 'Produk berhasil ditambahkan! (Demo Mode - tidak tersimpan ke database)';
                $generatedQR = [
                    'qr_data' => $qrData,
                    'qr_url' => $qrCodeUrl,
                    'name' => $name,
                    'category' => $category,
                    'description' => $description
                ];
                // Clear form
                $name = $category = $description = '';
            } else {
                // Real database insert
                try {
                    $stmt = $pdo->prepare('INSERT INTO products (name, category, description, qr_data) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$name, $category, $description, $qrData]);
                    
                    // Generate QR code URL
                    $qrCodeUrl = "https://qr-server.com/api/v1/create-qr-code/?size=200x200&data=" . urlencode($qrData);
                    $success = 'Produk berhasil ditambahkan dan tersimpan ke database!';
                    $generatedQR = [
                        'qr_data' => $qrData,
                        'qr_url' => $qrCodeUrl,
                        'name' => $name,
                        'category' => $category,
                        'description' => $description
                    ];
                    
                    // Clear form
                    $name = $category = $description = '';
                } catch (Exception $e) {
                    $error = 'Terjadi kesalahan: ' . $e->getMessage();
                }
            }
        } else {
            $error = 'Nama produk dan kategori harus diisi!';
        }
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <h1 class="text-xl font-bold">📦 Tambah Produk</h1>
                <?php if ($pdo === null): ?>
                <span class="bg-yellow-500 text-yellow-900 px-2 py-1 rounded text-xs font-bold">DEMO MODE</span>
                <?php endif; ?>
            </div>
            <div class="space-x-4">
                <a href="/admin/admin_dashboard.php" class="hover:underline">← Dashboard</a>
                <span><?= htmlspecialchars($_SESSION['admin_username']) ?></span>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6 max-w-2xl">
        <?php if (isset($generatedQR)): ?>
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="text-center">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <h3 class="text-lg font-bold text-green-800 mb-2">🎉 QR Code Berhasil Dibuat!</h3>
                    <div class="grid md:grid-cols-2 gap-4 items-center">
                        <div class="text-left text-sm text-green-700">
                            <p><strong>Nama:</strong> <?= htmlspecialchars($generatedQR['name']) ?></p>
                            <p><strong>Kategori:</strong> <?= htmlspecialchars($generatedQR['category']) ?></p>
                            <p><strong>QR Code:</strong> <span class="font-mono"><?= htmlspecialchars($generatedQR['qr_data']) ?></span></p>
                            <?php if ($pdo === null): ?>
                            <p class="text-yellow-600 font-semibold mt-2">⚠️ Demo Mode: Data tidak tersimpan</p>
                            <?php endif; ?>
                        </div>
                        <div class="text-center">
                            <img src="<?= htmlspecialchars($generatedQR['qr_url']) ?>" 
                                 alt="QR Code" 
                                 class="mx-auto border-2 border-gray-300 rounded-lg shadow-md">
                            <div class="mt-2 space-x-2">
                                <a href="<?= htmlspecialchars($generatedQR['qr_url']) ?>" 
                                   target="_blank" 
                                   class="inline-block bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600">
                                    📥 Download
                                </a>
                                <button onclick="copyQRData('<?= htmlspecialchars($generatedQR['qr_data']) ?>')" 
                                        class="bg-gray-500 text-white px-3 py-1 rounded text-xs hover:bg-gray-600">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-center space-x-4">
                    <a href="/scan" 
                       class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 font-medium">
                        🔍 Test QR Code
                    </a>
                    <button onclick="location.reload()" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-medium">
                        ➕ Tambah Lagi
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow p-6">
            <?php if ($success && !isset($generatedQR)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <div class="flex items-center">
                    <span class="mr-2">✅</span>
                    <?= htmlspecialchars($success) ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">
                        Nama Produk *
                    </label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>"
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" 
                           required>
                </div>

                <div>
                    <label for="category" class="block text-gray-700 text-sm font-bold mb-2">
                        Kategori *
                    </label>
                    <select id="category" name="category" 
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" 
                            required>
                        <option value="">Pilih kategori...</option>
                        <option value="Elektronik" <?= ($category ?? '') === 'Elektronik' ? 'selected' : '' ?>>Elektronik</option>
                        <option value="Fashion" <?= ($category ?? '') === 'Fashion' ? 'selected' : '' ?>>Fashion</option>
                        <option value="Makanan" <?= ($category ?? '') === 'Makanan' ? 'selected' : '' ?>>Makanan</option>
                        <option value="Kesehatan" <?= ($category ?? '') === 'Kesehatan' ? 'selected' : '' ?>>Kesehatan</option>
                        <option value="Lainnya" <?= ($category ?? '') === 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>

                <div>
                    <label for="description" class="block text-gray-700 text-sm font-bold mb-2">
                        Deskripsi
                    </label>
                    <textarea id="description" name="description" rows="4"
                              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500"><?= htmlspecialchars($description ?? '') ?></textarea>
                </div>

                <div class="flex justify-between">
                    <a href="/admin/admin_dashboard.php" 
                       class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        ← Kembali
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        💾 Simpan Produk
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function copyQRData(qrData) {
            navigator.clipboard.writeText(qrData).then(function() {
                // Create temporary notification
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                notification.textContent = '✅ QR Code copied to clipboard!';
                document.body.appendChild(notification);
                
                // Remove notification after 3 seconds
                setTimeout(function() {
                    document.body.removeChild(notification);
                }, 3000);
            }).catch(function(err) {
                alert('Failed to copy QR code: ' + qrData);
                console.error('Could not copy text: ', err);
            });
        }
    </script>
</body>
</html>
<?php
}
?>
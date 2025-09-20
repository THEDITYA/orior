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
    session_start();

    // Database connection - Vercel compatible
    require_once '../config/database.php';
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
            // Check user credentials
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

    // Database connection - Vercel compatible
    require_once '../config/database.php';
    $pdo = getDatabase();

    // Get products count
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM products');
    $stmt->execute();
    $productCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get recent products
    $stmt = $pdo->prepare('SELECT * FROM products ORDER BY created_at DESC LIMIT 10');
    $stmt->execute();
    $recentProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <h1 class="text-xl font-bold">📊 Dashboard Admin</h1>
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

    // Database connection - Vercel compatible
    require_once '../config/database.php';
    $pdo = getDatabase();

    $success = '';
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if ($name && $category) {
            try {
                // Generate unique QR data
                $qrData = 'ORIOR_' . strtoupper(uniqid()) . '_' . time();
                
                // Insert into database
                $stmt = $pdo->prepare('INSERT INTO products (name, category, description, qr_data) VALUES (?, ?, ?, ?)');
                $stmt->execute([$name, $category, $description, $qrData]);
                
                $success = 'Produk berhasil ditambahkan dengan QR Code: ' . $qrData;
                
                // Clear form
                $name = $category = $description = '';
            } catch (Exception $e) {
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
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
            <h1 class="text-xl font-bold">📦 Tambah Produk</h1>
            <div class="space-x-4">
                <a href="/admin/admin_dashboard.php" class="hover:underline">← Dashboard</a>
                <span><?= htmlspecialchars($_SESSION['admin_username']) ?></span>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6 max-w-2xl">
        <div class="bg-white rounded-lg shadow p-6">
            <?php if ($success): ?>
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
</body>
</html>
<?php
}
?>
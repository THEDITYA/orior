<?php
session_start();

// Database connection - Vercel compatible
require_once '../config/database.php';
$pdo = getDatabase();

// Check if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
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
            header('Location: admin_dashboard.php');
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
            <a href="../scan.php" class="text-blue-600 hover:underline">← Kembali ke Scanner</a>
        </div>

        <div class="text-center mt-4 text-sm text-gray-500">
            Default: admin / admin123
        </div>
    </div>
</body>
</html>
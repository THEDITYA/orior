<?php
/**
 * Database Configuration untuk Vercel
 * Mendukung environment variables dan fallback ke lokal
 */

class DatabaseConfig {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect() {
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
            PDO::ATTR_PERSISTENT         => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        
        try {
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Log error untuk debugging di Vercel
            error_log("Database connection failed: " . $e->getMessage());
            throw new PDOException("Database connection failed: " . $e->getMessage(), (int)$e->getCode());
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function testConnection() {
        try {
            $stmt = $this->pdo->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}

// Function untuk mendapatkan koneksi database
function getDatabase() {
    return DatabaseConfig::getInstance()->getConnection();
}

// Legacy support - untuk file yang masih menggunakan $pdo
$pdo = getDatabase();
?>
<?php
/**
 * Database Connection - Compatible dengan Vercel
 */

// Include konfigurasi database
require_once __DIR__ . '/config/database.php';

// Untuk backward compatibility, export $pdo
$pdo = getDatabase();
?>

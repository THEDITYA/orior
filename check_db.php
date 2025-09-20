<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=validasi_barang', 'root', '');
    
    echo "=== USERS TABLE STRUCTURE ===\n";
    $stmt = $pdo->query('DESCRIBE users');
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . $row['Key'] . "\n";
    }
    
    echo "\n=== PRODUCTS TABLE STRUCTURE ===\n";
    $stmt = $pdo->query('DESCRIBE products');
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . $row['Key'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
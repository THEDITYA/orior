<?php
/**
 * Legacy Validation Endpoint - Redirect to API
 * Untuk backward compatibility
 */

// Redirect ke API endpoint yang baru
$kode = $_POST['kode'] ?? $_GET['kode'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Forward POST request
    $postData = http_build_query($_POST);
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postData
        ]
    ]);
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $api_url = "$protocol://$host/api/index.php?action=validate";
    
    $result = file_get_contents($api_url, false, $context);
    
    // Forward response
    header('Content-Type: application/json');
    echo $result;
    
} elseif (!empty($kode)) {
    // Redirect GET request
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $redirect_url = "$protocol://$host/api/index.php?action=validate&kode=" . urlencode($kode);
    
    header('Location: ' . $redirect_url);
    exit;
    
} else {
    // Direct API call via include
    include_once 'api/index.php';
}
?>
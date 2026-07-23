<?php
// Check if running on localhost
$isLocalhost = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']) || ($_SERVER['SERVER_NAME'] ?? '') === 'localhost';

if ($isLocalhost) {
    // Local XAMPP configuration
    $host = '127.0.0.1';
    $db   = 'lifeflow_db';
    $user = 'root'; 
    $pass = '';     
} else {
    // InfinityFree Production configuration
    // IMPORTANT: Update these values after creating your database on InfinityFree!
    $host = 'sqlXXX.infinityfree.com'; // e.g., sql123.infinityfree.com
    $db   = 'epiz_XXXXXXXX_lifeflow';   // e.g., epiz_12345678_lifeflow
    $user = 'epiz_XXXXXXXX';            // e.g., epiz_12345678
    $pass = 'YOUR_VPANEL_PASSWORD';     // Your hosting account/vPanel password
}

$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In production, don't show the exact database error to users for security
    if ($isLocalhost) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    } else {
        die("Database connection failed. Please ensure your database credentials are correct in includes/db.php");
    }
}
?>

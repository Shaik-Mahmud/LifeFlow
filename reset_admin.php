<?php
require_once 'includes/db.php';

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if admin exists
    $stmt = $pdo->query("SELECT * FROM admins WHERE username = 'admin'");
    if ($stmt->rowCount() > 0) {
        $update = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
        $update->execute([$hash]);
        echo "Admin password successfully reset to 'admin123'\n";
    } else {
        $insert = $pdo->prepare("INSERT INTO admins (username, password) VALUES ('admin', ?)");
        $insert->execute([$hash]);
        echo "Admin user created with password 'admin123'\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
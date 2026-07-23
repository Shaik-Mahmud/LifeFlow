<?php
require_once 'includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? 'Guest';
    $email = $_POST['email'] ?? 'no-reply@lifeflow.local';
    $donor = $_POST['donor'] ?? '';
    $message = $_POST['message'] ?? '';

    $subject = "Contact Request for Donor: " . $donor;

    try {
        $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

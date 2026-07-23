<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['donor_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donorId = $_SESSION['donor_id'];
    $requestId = $_POST['request_id'] ?? null;
    $units = $_POST['units'] ?? 1;

    if (!$requestId) {
        echo json_encode(['success' => false, 'message' => 'Missing request ID']);
        exit;
    }

    try {
        // Call the stored procedure
        $stmt = $pdo->prepare("CALL sp_process_donation(?, ?, ?)");
        $stmt->execute([$donorId, $requestId, $units]);
        
        echo json_encode(['success' => true, 'message' => 'Donation processed successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

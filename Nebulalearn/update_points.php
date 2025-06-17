<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$points = (int)$data['points'];
$action = sanitizeInput($data['action']);
$userId = $_SESSION['user_id'];

global $conn;

// Update points
$table = isTeacher() ? 'teachers' : 'students';
$stmt = $conn->prepare("UPDATE $table SET points = points + ? WHERE id = ?");
$stmt->bind_param("ii", $points, $userId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating points']);
}
?>
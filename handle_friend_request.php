
<?php
require_once 'functions.php';

if (!isLoggedIn() || !isStudent()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$requestId = sanitizeInput($data['request_id']);
$action = sanitizeInput($data['action']);
$currentUserId = $_SESSION['user_id'];

global $conn;

// Verify the request exists and is for the current user
$stmt = $conn->prepare("SELECT student1_id, student2_id FROM friends WHERE id = ? AND student2_id = ? AND status = 'pending'");
$stmt->bind_param("ii", $requestId, $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Friend request not found']);
    exit;
}

if ($action === 'accept') {
    $stmt = $conn->prepare("UPDATE friends SET status = 'accepted' WHERE id = ?");
    $stmt->bind_param("i", $requestId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error accepting friend request']);
    }
} elseif ($action === 'decline') {
    $stmt = $conn->prepare("DELETE FROM friends WHERE id = ?");
    $stmt->bind_param("i", $requestId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error declining friend request']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
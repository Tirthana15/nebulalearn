
<?php
require_once 'functions.php';

if (!isLoggedIn() || !isStudent()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$friendId = sanitizeInput($data['friend_id']);
$currentUserId = $_SESSION['user_id'];

global $conn;

// Delete friendship record
$stmt = $conn->prepare("DELETE FROM friends WHERE 
                       (student1_id = ? AND student2_id = ?) OR 
                       (student1_id = ? AND student2_id = ?)");
$stmt->bind_param("iiii", $currentUserId, $friendId, $friendId, $currentUserId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error removing friend']);
}
?>
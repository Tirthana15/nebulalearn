
<?php
require_once 'functions.php';

if (!isLoggedIn() || !isStudent()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$friendUsername = sanitizeInput($data['username']);
$currentUserId = $_SESSION['user_id'];

global $conn;

// Check if friend exists
$stmt = $conn->prepare("SELECT id FROM students WHERE username = ?");
$stmt->bind_param("s", $friendUsername);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$friend = $result->fetch_assoc();
$friendId = $friend['id'];

// Check if already friends or request pending
$stmt = $conn->prepare("SELECT id, status FROM friends WHERE 
                       (student1_id = ? AND student2_id = ?) OR 
                       (student1_id = ? AND student2_id = ?)");
$stmt->bind_param("iiii", $currentUserId, $friendId, $friendId, $currentUserId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $existing = $result->fetch_assoc();
    if ($existing['status'] === 'accepted') {
        echo json_encode(['success' => false, 'message' => 'You are already friends']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Friend request already pending']);
    }
    exit;
}

// Create friend request
$stmt = $conn->prepare("INSERT INTO friends (student1_id, student2_id, status) VALUES (?, ?, 'pending')");
$stmt->bind_param("ii", $currentUserId, $friendId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error sending friend request']);
}
?>
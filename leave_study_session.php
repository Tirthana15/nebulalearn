
<?php
require_once 'functions.php';

if (!isLoggedIn() || !isStudent()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$sessionId = sanitizeInput($data['session_id']);
$studentId = $_SESSION['user_id'];

global $conn;

// Leave session
$stmt = $conn->prepare("DELETE FROM study_groups WHERE session_id = ? AND student_id = ?");
$stmt->bind_param("si", $sessionId, $studentId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error leaving study session']);
}
?>
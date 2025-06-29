
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

// Check if already in a session
$stmt = $conn->prepare("SELECT id FROM study_groups WHERE student_id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You are already in a study session']);
    exit;
}

// Check if session exists
$stmt = $conn->prepare("SELECT id FROM study_groups WHERE session_id = ? LIMIT 1");
$stmt->bind_param("s", $sessionId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Session not found']);
    exit;
}

// Join session
$stmt = $conn->prepare("INSERT INTO study_groups (session_id, student_id) VALUES (?, ?)");
$stmt->bind_param("si", $sessionId, $studentId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'session_id' => $sessionId]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error joining study session']);
}
?>
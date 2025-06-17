
<?php
require_once 'functions.php';

if (!isLoggedIn() || !isTeacher()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$assignmentId = (int)$_GET['id'];
$teacherId = $_SESSION['user_id'];

global $conn;

// Verify the assignment belongs to the teacher
$stmt = $conn->prepare("SELECT id FROM assignments WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $assignmentId, $teacherId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Assignment not found or access denied']);
    exit;
}

// Delete the assignment
$stmt = $conn->prepare("DELETE FROM assignments WHERE id = ?");
$stmt->bind_param("i", $assignmentId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting assignment']);
}
?>
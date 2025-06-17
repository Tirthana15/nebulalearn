<?php
require_once 'functions.php';

if (!isLoggedIn() || !isTeacher()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$studentId = (int)$_GET['id'];

global $conn;

// Delete student
$stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
$stmt->bind_param("i", $studentId);

if ($stmt->execute()) {
    // Also delete related records
    $conn->query("DELETE FROM student_assignments WHERE student_id = $studentId");
    $conn->query("DELETE FROM friends WHERE student1_id = $studentId OR student2_id = $studentId");
    $conn->query("DELETE FROM study_groups WHERE student_id = $studentId");
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error removing student']);
}
?>
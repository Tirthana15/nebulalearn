<?php
require_once 'functions.php';

if (!isLoggedIn() || !isTeacher()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$assignmentId = (int)$_GET['id'];
$title = sanitizeInput($_POST['title']);
$subject = sanitizeInput($_POST['subject']);
$dueDate = sanitizeInput($_POST['due_date']);
$points = (int)$_POST['points'];
$description = sanitizeInput($_POST['description']);
$class = sanitizeInput($_POST['class']);
$form = sanitizeInput($_POST['form']);
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

$stmt = $conn->prepare("UPDATE assignments SET title = ?, subject = ?, due_date = ?, points = ?, 
                        description = ?, class = ?, form = ? WHERE id = ?");
$stmt->bind_param("sssisisi", $title, $subject, $dueDate, $points, $description, $class, $form, $assignmentId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating assignment']);
}
?>
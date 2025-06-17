
<?php
require_once 'functions.php';

if (!isLoggedIn() || !isTeacher()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$title = sanitizeInput($_POST['title']);
$subject = sanitizeInput($_POST['subject']);
$dueDate = sanitizeInput($_POST['due_date']);
$points = (int)$_POST['points'];
$description = sanitizeInput($_POST['description']);
$class = sanitizeInput($_POST['class']);
$form = sanitizeInput($_POST['form']);
$teacherId = $_SESSION['user_id'];

global $conn;

$stmt = $conn->prepare("INSERT INTO assignments (title, subject, due_date, points, description, teacher_id, class, form) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssisiss", $title, $subject, $dueDate, $points, $description, $teacherId, $class, $form);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'assignment_id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error creating assignment']);
}
?>
<?php
require_once 'functions.php';

if (!isLoggedIn() || !isTeacher()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$title = sanitizeInput($_POST['title']);
$subject = sanitizeInput($_POST['subject']);
$class = sanitizeInput($_POST['class']);
$form = sanitizeInput($_POST['form']);
$description = sanitizeInput($_POST['description']);
$teacherId = $_SESSION['user_id'];

// File upload handling would be here in a real application
// For this example, we'll just simulate it

global $conn;

$stmt = $conn->prepare("INSERT INTO learning_materials (title, subject, class, form, description, teacher_id, file_path) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
$filePath = 'uploads/' . basename($_FILES['file']['name']); // This would be handled properly in a real app
$stmt->bind_param("sssssis", $title, $subject, $class, $form, $description, $teacherId, $filePath);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error uploading material']);
}
?>
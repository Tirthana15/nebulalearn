
<?php
require_once 'functions.php';

if (!isLoggedIn() || !isTeacher()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$username = sanitizeInput($_POST['username']);
$email = sanitizeInput($_POST['email']);
$password = sanitizeInput($_POST['password']);
$class = sanitizeInput($_POST['class']);
$form = sanitizeInput($_POST['form']);

global $conn;

// Check if username or email already exists
$stmt = $conn->prepare("SELECT id FROM students WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Create student
$stmt = $conn->prepare("INSERT INTO students (username, email, password, class, form) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $username, $email, $hashedPassword, $class, $form);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error creating student']);
}
?>
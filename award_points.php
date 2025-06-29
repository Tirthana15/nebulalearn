
<?php
require_once 'functions.php';

if (!isLoggedIn() || !isTeacher()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$studentId = (int)$_POST['student_id'];
$points = (int)$_POST['points'];
$reason = sanitizeInput($_POST['reason']);
$teacherId = $_SESSION['user_id'];

global $conn;

// Award points
$stmt = $conn->prepare("UPDATE students SET points = points + ? WHERE id = ?");
$stmt->bind_param("ii", $points, $studentId);

if ($stmt->execute()) {
    // Log the award (this table would need to be created)
    $stmt = $conn->prepare("INSERT INTO point_awards (student_id, teacher_id, points, reason) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $studentId, $teacherId, $points, $reason);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error awarding points']);
}
?>
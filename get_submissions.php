
<?php
require_once 'functions.php';

if (!isLoggedIn() || !isTeacher()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$assignmentId = (int)$_GET['assignment_id'];
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

// Get submissions
$stmt = $conn->prepare("
    SELECT sa.id, sa.submission, sa.grade, sa.submitted_at, 
           s.username AS student_name, s.class, s.form
    FROM student_assignments sa
    JOIN students s ON sa.student_id = s.id
    WHERE sa.assignment_id = ?
");
$stmt->bind_param("i", $assignmentId);
$stmt->execute();
$result = $stmt->get_result();

$submissions = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode(['success' => true, 'submissions' => $submissions]);
?>
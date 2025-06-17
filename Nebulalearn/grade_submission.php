 <?php
require_once 'functions.php';

if (!isLoggedIn() || !isTeacher()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$submissionId = (int)$_GET['id'];
$grade = (int)$_POST['grade'];
$teacherId = $_SESSION['user_id'];

global $conn;

// Verify the submission belongs to an assignment by this teacher
$stmt = $conn->prepare("
    SELECT sa.id 
    FROM student_assignments sa
    JOIN assignments a ON sa.assignment_id = a.id
    WHERE sa.id = ? AND a.teacher_id = ?
");
$stmt->bind_param("ii", $submissionId, $teacherId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Submission not found or access denied']);
    exit;
}

// Update the grade
$stmt = $conn->prepare("UPDATE student_assignments SET grade = ? WHERE id = ?");
$stmt->bind_param("ii", $grade, $submissionId);

if ($stmt->execute()) {
    // Update student's points based on assignment points
    $stmt = $conn->prepare("
        UPDATE students s
        JOIN student_assignments sa ON s.id = sa.student_id
        JOIN assignments a ON sa.assignment_id = a.id
        SET s.points = s.points + (a.points * ? / 100)
        WHERE sa.id = ?
    ");
    $gradePercentage = $grade / 100;
    $stmt->bind_param("di", $gradePercentage, $submissionId);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error saving grade']);
}
?>
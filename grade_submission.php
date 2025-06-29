<?php
require_once 'functions.php';

if (!isLoggedIn() || !isTeacher()) {
    redirect('login.php');
}

$assignmentId = isset($_GET['assignment_id']) ? (int)$_GET['assignment_id'] : 0;
$studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

if (!$assignmentId || !$studentId) {
    redirect('admin_dashboard.php');
}

global $conn;

// Dapatkan maklumat tugasan pelajar dan markah maksimum
$stmt = $conn->prepare("
    SELECT sa.*, s.username AS student_name, s.class, s.form, 
           a.title AS assignment_title, a.points AS max_points
    FROM student_assignments sa
    JOIN students s ON sa.student_id = s.id
    JOIN assignments a ON sa.assignment_id = a.id
    WHERE sa.assignment_id = ? AND sa.student_id = ?
    LIMIT 1
");

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("ii", $assignmentId, $studentId);
$stmt->execute();
$result = $stmt->get_result();
$submission = $result->fetch_assoc();

if (!$submission) {
    redirect('admin_dashboard.php');
}

// Bila guru hantar gred
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade'])) {
    $grade = (int) $_POST['grade'];
    $maxPoints = (int) $submission['max_points'];

    // Pastikan gred dalam julat sah
    if ($grade < 0) $grade = 0;
    if ($grade > $maxPoints) $grade = $maxPoints;

    // Simpan ke database
    $stmt = $conn->prepare("UPDATE student_assignments SET grade = ? WHERE id = ?");
    $stmt->bind_param("ii", $grade, $submission['id']);

    if ($stmt->execute()) {
        redirect("assignment.php?id=$assignmentId");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grade Submission - <?php echo htmlspecialchars($submission['assignment_title']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="grade-container" style="max-width: 800px; margin: auto; padding: 2rem;">
        <h1>Grade Submission</h1>
        <p><strong>Student:</strong> <?php echo htmlspecialchars($submission['student_name']); ?> (Class: <?php echo $submission['class']; ?>, Form: <?php echo $submission['form']; ?>)</p>
        <p><strong>Assignment:</strong> <?php echo htmlspecialchars($submission['assignment_title']); ?></p>
        <p><strong>Submitted At:</strong> <?php echo date('F j, Y g:i a', strtotime($submission['submitted_at'])); ?></p>

        <h3>Submitted Content:</h3>
        <div class="submitted-content" style="border: 1px solid #ccc; padding: 10px; background: #f9f9f9; margin-bottom: 20px;">
            <pre><?php echo htmlspecialchars($submission['submission']); ?></pre>
        </div>

        <form method="POST" class="grade-form">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="grade">Grade (0–<?php echo $submission['max_points']; ?>):</label><br>
                <input 
                    type="number" 
                    name="grade" 
                    id="grade" 
                    min="0" 
                    max="<?php echo $submission['max_points']; ?>" 
                    required 
                    value="<?php echo $submission['grade'] ?? ''; ?>"
                >
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Submit Grade</button>
                <a href="assignment.php?id=<?php echo $assignmentId; ?>" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </main>
</body>
</html>

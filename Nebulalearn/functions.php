
<?php
require_once 'db.php';

session_start();

// Common functions used across the application

function sanitizeInput($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isTeacher() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'teacher';
}

function isStudent() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student';
}

function getUserData() {
    if (!isLoggedIn()) return null;
    
    global $conn;
    $table = isTeacher() ? 'teachers' : 'students';
    $id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function updateStreak($studentId) {
    global $conn;
    
    // Get student data
    $stmt = $conn->prepare("SELECT streak, last_active_date FROM students WHERE id = ?");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    if ($student['last_active_date'] == $yesterday) {
        // Increment streak
        $newStreak = $student['streak'] + 1;
    } elseif ($student['last_active_date'] != $today) {
        // Reset streak
        $newStreak = 1;
    } else {
        // Already updated today
        return;
    }
    
    // Update streak and last active date
    $stmt = $conn->prepare("UPDATE students SET streak = ?, last_active_date = ? WHERE id = ?");
    $stmt->bind_param("isi", $newStreak, $today, $studentId);
    $stmt->execute();
}

function addPoints($studentId, $points) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE students SET points = points + ? WHERE id = ?");
    $stmt->bind_param("ii", $points, $studentId);
    $stmt->execute();
}

function getTopStudents($limit = 10) {
    global $conn;
    
    $result = $conn->query("SELECT username, points, streak FROM students ORDER BY points DESC LIMIT $limit");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getStudentAssignments($studentId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT a.id, a.title, a.description, a.subject, a.due_date, a.points, 
               sa.submission, sa.grade, sa.submitted_at, t.username AS teacher_name
        FROM assignments a
        LEFT JOIN student_assignments sa ON a.id = sa.assignment_id AND sa.student_id = ?
        JOIN teachers t ON a.teacher_id = t.id
        ORDER BY a.due_date ASC
    ");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getTeacherAssignments($teacherId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT a.id, a.title, a.subject, a.due_date, a.points, 
               COUNT(sa.id) AS submissions, 
               COUNT(CASE WHEN sa.grade IS NOT NULL THEN 1 END) AS graded
        FROM assignments a
        LEFT JOIN student_assignments sa ON a.id = sa.assignment_id
        WHERE a.teacher_id = ?
        GROUP BY a.id
        ORDER BY a.due_date ASC
    ");
    $stmt->bind_param("i", $teacherId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getAssignmentDetails($assignmentId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT a.*, t.username AS teacher_name
        FROM assignments a
        JOIN teachers t ON a.teacher_id = t.id
        WHERE a.id = ?
    ");
    $stmt->bind_param("i", $assignmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function getAssignmentSubmissions($assignmentId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT sa.*, s.username AS student_name, s.class, s.form
        FROM student_assignments sa
        JOIN students s ON sa.student_id = s.id
        WHERE sa.assignment_id = ?
    ");
    $stmt->bind_param("i", $assignmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getStudentFriends($studentId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT s.id, s.username, s.points, s.streak, f.status,
               CASE 
                   WHEN f.student1_id = ? THEN f.student2_id
                   ELSE f.student1_id
               END AS friend_id
        FROM friends f
        JOIN students s ON (f.student1_id = s.id OR f.student2_id = s.id) AND s.id != ?
        WHERE (f.student1_id = ? OR f.student2_id = ?) AND f.status = 'accepted'
    ");
    $stmt->bind_param("iiii", $studentId, $studentId, $studentId, $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getPendingFriendRequests($studentId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT s.id, s.username, f.id AS request_id
        FROM friends f
        JOIN students s ON f.student1_id = s.id
        WHERE f.student2_id = ? AND f.status = 'pending'
    ");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getActiveStudyGroups() {
    global $conn;
    
    $result = $conn->query("
        SELECT sg.session_id, s.username, s.class, s.form
        FROM study_groups sg
        JOIN students s ON sg.student_id = s.id
        ORDER BY sg.join_time DESC
    ");
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getStudentsByClass($class, $form) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, username FROM students WHERE class = ? AND form = ?");
    $stmt->bind_param("ss", $class, $form);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>
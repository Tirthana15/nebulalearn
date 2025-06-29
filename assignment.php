
<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getUserData();
$assignmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$assignmentId) {
    redirect(isTeacher() ? 'admin_dashboard.php' : 'user_dashboard.php');
}

// Get assignment details
$assignment = getAssignmentDetails($assignmentId);

if (!$assignment) {
    redirect(isTeacher() ? 'admin_dashboard.php' : 'user_dashboard.php');
}

// Check if the teacher owns this assignment (for teachers)
if (isTeacher() && $assignment['teacher_id'] != $user['id']) {
    redirect('admin_dashboard.php');
}

// Get submission details (for students)
$submission = null;
if (isStudent()) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM student_assignments WHERE assignment_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $assignmentId, $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $submission = $result->fetch_assoc();
}

// Get all submissions (for teachers)
$submissions = [];
if (isTeacher()) {
    $submissions = getAssignmentSubmissions($assignmentId);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isStudent() && isset($_POST['submit_assignment'])) {
        // Student submitting assignment
        $content = sanitizeInput($_POST['content']);
        
        if ($submission) {
            // Update existing submission
            $stmt = $conn->prepare("UPDATE student_assignments SET submission = ?, submitted_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $content, $submission['id']);
        } else {
            // Create new submission
            $stmt = $conn->prepare("INSERT INTO student_assignments (student_id, assignment_id, submission, submitted_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iis", $user['id'], $assignmentId, $content);
        }
        
        if ($stmt->execute()) {
            // Update streak for submitting
            updateStreak($user['id']);
            redirect("assignment.php?id=$assignmentId");
        }
    }
    
    if (isTeacher() && isset($_POST['update_assignment'])) {
        // Teacher updating assignment
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $dueDate = sanitizeInput($_POST['due_date']);
        $points = (int)$_POST['points'];
        
        $stmt = $conn->prepare("UPDATE assignments SET title = ?, description = ?, due_date = ?, points = ? WHERE id = ?");
        $stmt->bind_param("sssii", $title, $description, $dueDate, $points, $assignmentId);
        
        if ($stmt->execute()) {
            redirect("assignment.php?id=$assignmentId");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($assignment['title']); ?> - Nebula Learn</title>
    <link rel="stylesheet" href="style.css">
    <script src="app.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="background"></div>
    
    <nav class="navbar">
        <div class="logo">Nebula Learn</div>
        <div class="nav-links">
            <a href="index.html">Home</a>
            <a href="login.php">Login</a>
            <a href="signup.php">Sign Up</a>
            <a href="<?php echo isTeacher() ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>" class="active">Dashboard</a>
            <button id="theme-toggle">Dark Mode</button>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
        <div class="mobile-menu-btn">☰</div>
    </nav>
    
    <div class="mobile-nav-links">
        <a href="index.html">Home</a>
        <a href="login.php">Login</a>
        <a href="signup.php">Sign Up</a>
        <a href="<?php echo isTeacher() ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>" class="active">Dashboard</a>
        <button id="mobile-theme-toggle">Dark Mode</button>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
    
    <main class="assignment-container">
        <div class="assignment-header">
            <h1><?php echo htmlspecialchars($assignment['title']); ?></h1>
            <div class="assignment-meta">
                <span class="subject-badge"><?php echo htmlspecialchars($assignment['subject']); ?></span>
                <span class="points-badge"><?php echo $assignment['points']; ?> points</span>
                <?php if (isStudent()): ?>
                    <span class="status-badge <?php echo $submission ? 'submitted' : 'pending'; ?>">
                        <?php echo $submission ? 'Submitted' : 'Pending'; ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="assignment-details">
            <div class="detail-row">
                <div class="detail-label"><i class="fas fa-user"></i> Teacher:</div>
                <div class="detail-value"><?php echo htmlspecialchars($assignment['teacher_name']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label"><i class="fas fa-calendar-day"></i> Due Date:</div>
                <div class="detail-value">
                    <?php echo date('F j, Y', strtotime($assignment['due_date'])); ?>
                    <?php if (isStudent()): ?>
                        (<?php 
                            $dueDate = new DateTime($assignment['due_date']);
                            $today = new DateTime();
                            $interval = $today->diff($dueDate);
                            echo $interval->format('%a days ' . ($interval->invert ? 'ago' : 'left'));
                        ?>)
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($assignment['description'])): ?>
                <div class="detail-row">
                    <div class="detail-label"><i class="fas fa-align-left"></i> Description:</div>
                    <div class="detail-value assignment-description">
                        <?php echo nl2br(htmlspecialchars($assignment['description'])); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isTeacher() && (!empty($assignment['class']) || !empty($assignment['form']))): ?>
                <div class="detail-row">
                    <div class="detail-label"><i class="fas fa-users"></i> Assigned To:</div>
                    <div class="detail-value">
                        <?php 
                            echo !empty($assignment['class']) ? 'Class ' . htmlspecialchars($assignment['class']) : '';
                            echo !empty($assignment['class']) && !empty($assignment['form']) ? ', ' : '';
                            echo !empty($assignment['form']) ? 'Form ' . htmlspecialchars($assignment['form']) : '';
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (isStudent()): ?>
            <!-- Student Assignment Submission Form -->
            <div class="assignment-submission">
                <h2>Your Submission</h2>
                
                <?php if ($submission && $submission['grade'] !== null): ?>
                    <div class="grade-display">
                        <h3>Your Grade: <span><?php echo $submission['grade']; ?>/100</span></h3>
                        <?php if ($submission['grade'] >= 80): ?>
                            <p class="feedback excellent">Excellent work! Keep it up!</p>
                        <?php elseif ($submission['grade'] >= 60): ?>
                            <p class="feedback good">Good job! You're on the right track.</p>
                        <?php else: ?>
                            <p class="feedback improve">Review the material and try to improve next time.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="submission-form">
                    <div class="form-group">
                        <label for="content">Your Work:</label>
                        <textarea id="content" name="content" rows="10" required><?php 
                            echo $submission ? htmlspecialchars($submission['submission']) : ''; 
                        ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="submit_assignment" class="btn btn-primary">
                            <?php echo $submission ? 'Update Submission' : 'Submit Assignment'; ?>
                        </button>
                        <a href="user_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                    </div>
                </form>
                
                <?php if ($submission): ?>
                    <div class="submission-meta">
                        <p>
                            <i class="fas fa-clock"></i> 
                            Submitted on: <?php echo date('F j, Y \a\t g:i a', strtotime($submission['submitted_at'])); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Teacher Assignment Management -->
            <div class="assignment-management">
                <div class="teacher-actions">
                    <h2>Assignment Management</h2>
                    
                    <div class="stats-row">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo count($submissions); ?></div>
                            <div class="stat-label">Total Submissions</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">
                                <?php echo count(array_filter($submissions, fn($s) => $s['grade'] !== null)); ?>
                            </div>
                            <div class="stat-label">Graded</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">
                                <?php 
                                    $ungraded = count(array_filter($submissions, fn($s) => $s['submitted_at'] && $s['grade'] === null));
                                    echo $ungraded ?: '0';
                                ?>
                            </div>
                            <div class="stat-label">To Grade</div>
                        </div>
                    </div>
                    
                    <form method="POST" class="assignment-edit-form">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($assignment['title']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="5"><?php 
                                echo htmlspecialchars($assignment['description']); 
                            ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="due_date">Due Date</label>
                                <input type="date" id="due_date" name="due_date" value="<?php echo $assignment['due_date']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="points">Points</label>
                                <input type="number" id="points" name="points" min="1" max="100" value="<?php echo $assignment['points']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_assignment" class="btn btn-primary">Update Assignment</button>
                            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                        </div>
                    </form>
                </div>
                
                <div class="submissions-list">
                    <h3>Student Submissions</h3>
                    
                    <?php if (empty($submissions)): ?>
                        <div class="empty-state">
                            <i class="fas fa-clipboard-list"></i>
                            <p>No submissions yet</p>
                        </div>
                    <?php else: ?>
                        <table class="submissions-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Form</th>
                                    <th>Submitted</th>
                                    <th>Grade</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($submissions as $sub): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sub['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($sub['class']); ?></td>
                                        <td>Form <?php echo htmlspecialchars($sub['form']); ?></td>
                                        <td>
                                            <?php if ($sub['submitted_at']): ?>
                                                <?php echo date('M j, Y', strtotime($sub['submitted_at'])); ?>
                                            <?php else: ?>
                                                <span class="not-submitted">Not submitted</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($sub['grade'] !== null): ?>
                                                <?php echo $sub['grade']; ?>/100
                                            <?php elseif ($sub['submitted_at']): ?>
                                                <span class="ungraded">Ungraded</span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($sub['submitted_at']): ?>
                                                <a href="grade_submission.php?assignment_id=<?php echo $assignmentId; ?>&student_id=<?php echo $sub['student_id']; ?>" class="btn btn-small <?php echo $sub['grade'] !== null ? 'btn-view' : 'btn-grade'; ?>">
                                                    <?php echo $sub['grade'] !== null ? 'View' : 'Grade'; ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="no-action">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
<?php
require_once 'functions.php';

if (isLoggedIn()) {
    redirect(isTeacher() ? 'admin_dashboard.php' : 'user_dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);
    $confirmPassword = sanitizeInput($_POST['confirm_password']);
    $userType = sanitizeInput($_POST['user_type']);
    
    if ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } else {
        global $conn;
        
        // Check if username or email already exists
        $table = $userType === 'teacher' ? 'teachers' : 'students';
        $stmt = $conn->prepare("SELECT id FROM $table WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email or username already exists";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            if ($userType === 'teacher') {
                $school = sanitizeInput($_POST['school']);
                $stmt = $conn->prepare("INSERT INTO teachers (email, username, password, school) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $email, $username, $hashedPassword, $school);
            } else {
                $class = sanitizeInput($_POST['class']);
                $form = sanitizeInput($_POST['form']);
                $stmt = $conn->prepare("INSERT INTO students (email, username, password, class, form) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $email, $username, $hashedPassword, $class, $form);
            }
            
            if ($stmt->execute()) {
                $success = "Account created successfully! You can now <a href='login.php'>login</a>.";
            } else {
                $error = "Error creating account. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Nebula learn</title>
    <link rel="stylesheet" href="style.css">
    <script src="app.js" defer></script>
</head>
<body>
    <div class="background"></div>
    
    <nav class="navbar">
        <div class="logo">Nebula learn</div>
        <div class="nav-links">
            <a href="index.html">Home</a>
            <a href="login.php">Login</a>
            <a href="signup.php" class="active">Sign Up</a>
            <a href="#" id="dashboard-link">Dashboard</a>
            <button id="theme-toggle">Dark Mode</button>
        </div>
        <div class="mobile-menu-btn">☰</div>
    </nav>
    
    <div class="mobile-nav-links">
        <a href="index.html">Home</a>
        <a href="login.php">Login</a>
        <a href="signup.php" class="active">Sign Up</a>
        <a href="#" id="mobile-dashboard-link">Dashboard</a>
        <button id="mobile-theme-toggle">Dark Mode</button>
    </div>
    
    <main class="auth-container">
        <div class="auth-card">
            <h1>Sign Up</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php else: ?>
                <form action="signup.php" method="POST">
                    <div class="form-group">
                        <label for="user_type">I am a:</label>
                        <select id="user_type" name="user_type" required onchange="toggleSignupFields()">
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div id="student-fields">
                        <div class="form-group">
                            <label for="class">Class</label>
                            <input type="text" id="class" name="class">
                        </div>
                        
                        <div class="form-group">
                            <label for="form">Form</label>
                            <select id="form" name="form">
                                <option value="4">Form 4</option>
                                <option value="5">Form 5</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="teacher-fields" style="display: none;">
                        <div class="form-group">
                            <label for="school">School</label>
                            <input type="text" id="school" name="school">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Sign Up</button>
                </form>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        function toggleSignupFields() {
            const userType = document.getElementById('user_type').value;
            const studentFields = document.getElementById('student-fields');
            const teacherFields = document.getElementById('teacher-fields');
            
            if (userType === 'student') {
                studentFields.style.display = 'block';
                teacherFields.style.display = 'none';
            } else {
                studentFields.style.display = 'none';
                teacherFields.style.display = 'block';
            }
        }
    </script>
</body>
</html>
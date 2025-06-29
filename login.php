
<?php
require_once 'functions.php';

if (isLoggedIn()) {
    redirect(isTeacher() ? 'admin_dashboard.php' : 'user_dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);
    $userType = sanitizeInput($_POST['user_type']);
    
    global $conn;
    
    if ($userType === 'teacher') {
        $stmt = $conn->prepare("SELECT id, password FROM teachers WHERE username = ? OR email = ?");
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM students WHERE username = ? OR email = ?");
    }
    
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $userType;
            
            if ($userType === 'student') {
                updateStreak($user['id']);
                redirect('user_dashboard.php');
            } else {
                redirect('admin_dashboard.php');
            }
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nebula Learn</title>
    <link rel="stylesheet" href="style.css">
    <script src="app.js" defer></script>
</head>
<body>
    <div class="background"></div>
    
    <nav class="navbar">
        <div class="logo">Nebula Learn</div>
        <div class="nav-links">
            <a href="index.html">Home</a>
            <a href="login.php" class="active">Login</a>
            <a href="signup.php">Sign Up</a>
            <a href="#" id="dashboard-link">Dashboard</a>
            <button id="theme-toggle">Dark Mode</button>
        </div>
        <div class="mobile-menu-btn">☰</div>
    </nav>
    
    <div class="mobile-nav-links">
        <a href="index.html">Home</a>
        <a href="login.php" class="active">Login</a>
        <a href="signup.php">Sign Up</a>
        <a href="#" id="mobile-dashboard-link">Dashboard</a>
        <button id="mobile-theme-toggle">Dark Mode</button>
    </div>
    
    <main class="auth-container">
        <div class="auth-card">
            <h1>Login</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="user_type">I am a:</label>
                    <select id="user_type" name="user_type" required>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
                <p><a href="#">Forgot password?</a></p>
            </div>
        </div>
    </main>
</body>
</html>
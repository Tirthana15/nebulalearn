<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'online_learning';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create tables if they don't exist
function initializeDatabase($conn) {
    // Students table
    $sql = "CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        class VARCHAR(50) NOT NULL,
        form VARCHAR(10) NOT NULL,
        streak INT DEFAULT 0,
        last_active_date DATE,
        points INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
    
    // Teachers table
    $sql = "CREATE TABLE IF NOT EXISTS teachers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        school VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
    
    // Assignments table
    $sql = "CREATE TABLE IF NOT EXISTS assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        subject VARCHAR(100) NOT NULL,
        due_date DATE NOT NULL,
        points INT NOT NULL,
        teacher_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (teacher_id) REFERENCES teachers(id)
    )";
    $conn->query($sql);
    
    // Student assignments (submissions)
    $sql = "CREATE TABLE IF NOT EXISTS student_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        assignment_id INT NOT NULL,
        submission TEXT,
        grade INT,
        submitted_at TIMESTAMP NULL DEFAULT NULL,
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (assignment_id) REFERENCES assignments(id)
    )";
    $conn->query($sql);
    
    // Study sessions
    $sql = "CREATE TABLE IF NOT EXISTS study_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        end_time TIMESTAMP NULL DEFAULT NULL,
        duration INT,
        session_type ENUM('study', 'break') NOT NULL,
        FOREIGN KEY (student_id) REFERENCES students(id)
    )";
    $conn->query($sql);
    
    // Friends connections
    $sql = "CREATE TABLE IF NOT EXISTS friends (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student1_id INT NOT NULL,
        student2_id INT NOT NULL,
        status ENUM('pending', 'accepted') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student1_id) REFERENCES students(id),
        FOREIGN KEY (student2_id) REFERENCES students(id),
        UNIQUE KEY unique_friendship (student1_id, student2_id)
    )";
    $conn->query($sql);
    
    // Active study groups
    $sql = "CREATE TABLE IF NOT EXISTS study_groups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(50) NOT NULL,
        student_id INT NOT NULL,
        join_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id)
    )";
    $conn->query($sql);
}

initializeDatabase($conn);
?>

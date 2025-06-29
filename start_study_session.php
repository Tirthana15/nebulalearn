
<?php require_once 'functions.php'; if (!isLoggedIn() || !isStudent()) { header('HTTP/1.1 403 Forbidden'); echo json_encode(['success' => false, 'message' => 'Access denied']); exit; } $data = json_decode(file_get_contents('php://input'), true); $sessionType = sanitizeInput($data['session_type']); $studentId = $_SESSION['user_id']; global $conn; // Start new session $stmt = $conn->prepare("INSERT INTO study_sessions (student_id, session_type) VALUES (?, ?)"); $stmt->bind_param("is", $studentId, $sessionType); if ($stmt->execute()) { echo json_encode(['success' => true, 'session_id' => $stmt->insert_id]); } else { echo json_encode(['success' => false, 'message' => 'Error starting study session']); } ?>
[fileds" class="form-group">
                 <label for="class">Class</label>
                 <input type="text" id="class" name="class" required>
             </div>
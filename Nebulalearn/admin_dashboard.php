
<?php
require_once 'functions.php';

if (!isLoggedIn() || !isTeacher()) {
    redirect('login.php');
}

$user = getUserData();
$assignments = getTeacherAssignments($user['id']);
$students = []; // This would be populated from the database in a real app
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Nebula Learn</title>
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
            <a href="admin_dashboard.php" class="active">Dashboard</a>
            <button id="theme-toggle">Dark Mode</button>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
        <div class="mobile-menu-btn">☰</div>
    </nav>
    
    <div class="mobile-nav-links">
        <a href="index.html">Home</a>
        <a href="login.php">Login</a>
        <a href="signup.php">Sign Up</a>
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <button id="mobile-theme-toggle">Dark Mode</button>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
    
    <main class="dashboard-container">
        <div class="dashboard-sidebar">
            <div class="user-profile">
                <div class="avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                <p>Teacher at <?php echo htmlspecialchars($user['school']); ?></p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="#assignments" class="active"><i class="fas fa-tasks"></i> Assignments</a>
                <a href="#students"><i class="fas fa-users"></i> Students</a>
                <a href="#grades"><i class="fas fa-graduation-cap"></i> Grades</a>
                <a href="#materials"><i class="fas fa-book"></i> Learning Materials</a>
                <a href="scoreboard.php"><i class="fas fa-trophy"></i> Scoreboard</a>
            </nav>
        </div>
        
        <div class="dashboard-content">
            <section id="assignments" class="dashboard-section">
                <h2>Manage Assignments</h2>
                
                <button id="create-assignment-btn" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Assignment
                </button>
                
                <div class="assignments-list">
                    <?php if (!empty($assignments)): ?>
                        <table class="assignments-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Subject</th>
                                    <th>Due Date</th>
                                    <th>Points</th>
                                    <th>Submissions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignments as $assignment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['subject']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($assignment['due_date'])); ?></td>
                                        <td><?php echo $assignment['points']; ?></td>
                                        <td>
                                            <?php echo $assignment['submissions']; ?> submitted
                                            (<?php echo $assignment['graded']; ?> graded)
                                        </td>
                                        <td>
                                            <a href="assignment.php?id=<?php echo $assignment['id']; ?>" class="btn btn-small btn-view">View</a>
                                            <button class="btn btn-small btn-edit" data-assignment-id="<?php echo $assignment['id']; ?>">Edit</button>
                                            <button class="btn btn-small btn-delete" data-assignment-id="<?php echo $assignment['id']; ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-clipboard-list"></i>
                            <p>No assignments created yet</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Assignment Modal -->
                <div id="assignment-modal" class="modal">
                    <div class="modal-content">
                        <span class="close-modal">&times;</span>
                        <h3 id="modal-title">Create New Assignment</h3>
                        
                        <form id="assignment-form">
                            <input type="hidden" id="assignment-id" name="id" value="">
                            
                            <div class="form-group">
                                <label for="assignment-title">Title</label>
                                <input type="text" id="assignment-title" name="title" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="assignment-subject">Subject</label>
                                <select id="assignment-subject" name="subject" required>
                                    <option value="Mathematics">Mathematics</option>
                                    <option value="Science">Science</option>
                                    <option value="English">English</option>
                                    <option value="History">History</option>
                                    <option value="Geography">Geography</option>
                                    <option value="Biology">Biology</option>
                                    <option value="Chemistry">Chemistry</option>
                                    <option value="Physics">Physics</option>
                                    <option value="Additional Mathematics">Additional Mathematics</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="assignment-due-date">Due Date</label>
                                <input type="date" id="assignment-due-date" name="due_date" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="assignment-points">Points</label>
                                <input type="number" id="assignment-points" name="points" min="1" max="100" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="assignment-description">Description</label>
                                <textarea id="assignment-description" name="description" rows="5"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="assignment-class">For Class</label>
                                <input type="text" id="assignment-class" name="class" placeholder="e.g., 4A, 5B (leave blank for all)">
                            </div>
                            
                            <div class="form-group">
                                <label for="assignment-form">For Form</label>
                                <select id="assignment-form" name="form">
                                    <option value="">All Forms</option>
                                    <option value="4">Form 4</option>
                                    <option value="5">Form 5</option>
                                </select>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Save Assignment</button>
                                <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
            
            <section id="students" class="dashboard-section" style="display: none;">
                <h2>Manage Students</h2>
                
                <div class="students-management">
                    <div class="students-list">
                        <h3>All Students</h3>
                        
                        <div class="search-filter">
                            <input type="text" id="student-search" placeholder="Search students...">
                            <select id="class-filter">
                                <option value="">All Classes</option>
                                <option value="4A">4A</option>
                                <option value="4B">4B</option>
                                <option value="5A">5A</option>
                                <option value="5B">5B</option>
                            </select>
                            <select id="form-filter">
                                <option value="">All Forms</option>
                                <option value="4">Form 4</option>
                                <option value="5">Form 5</option>
                            </select>
                        </div>
                        
                        <table class="students-table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Class</th>
                                    <th>Form</th>
                                    <th>Points</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['username']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['class']); ?></td>
                                        <td>Form <?php echo htmlspecialchars($student['form']); ?></td>
                                        <td><?php echo $student['points']; ?></td>
                                        <td>
                                            <button class="btn btn-small btn-view-student" data-student-id="<?php echo $student['id']; ?>">View</button>
                                            <button class="btn btn-small btn-remove-student" data-student-id="<?php echo $student['id']; ?>">Remove</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="add-student">
                        <h3>Add New Student</h3>
                        <form id="add-student-form">
                            <div class="form-group">
                                <label for="student-username">Username</label>
                                <input type="text" id="student-username" name="username" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="student-email">Email</label>
                                <input type="email" id="student-email" name="email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="student-password">Password</label>
                                <input type="password" id="student-password" name="password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="student-class">Class</label>
                                <input type="text" id="student-class" name="class" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="student-form">Form</label>
                                <select id="student-form" name="form" required>
                                    <option value="4">Form 4</option>
                                    <option value="5">Form 5</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Add Student</button>
                        </form>
                    </div>
                </div>
            </section>
            
            <section id="grades" class="dashboard-section" style="display: none;">
                <h2>Grades Management</h2>
                
                <div class="grades-filters">
                    <select id="grades-assignment-filter">
                        <option value="">Select Assignment</option>
                        <?php foreach ($assignments as $assignment): ?>
                            <option value="<?php echo $assignment['id']; ?>">
                                <?php echo htmlspecialchars($assignment['title']); ?> (<?php echo htmlspecialchars($assignment['subject']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select id="grades-class-filter">
                        <option value="">All Classes</option>
                        <option value="4A">4A</option>
                        <option value="4B">4B</option>
                        <option value="5A">5A</option>
                        <option value="5B">5B</option>
                    </select>
                    
                    <select id="grades-form-filter">
                        <option value="">All Forms</option>
                        <option value="4">Form 4</option>
                        <option value="5">Form 5</option>
                    </select>
                </div>
                
                <div class="grades-table-container">
                    <table class="grades-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Form</th>
                                <th>Submission Date</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="grades-table-body">
                            <!-- Grades will be loaded via AJAX -->
                            <tr>
                                <td colspan="6" class="empty-state">Select an assignment to view submissions</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Grade Submission Modal -->
                <div id="grade-modal" class="modal">
                    <div class="modal-content">
                        <span class="close-modal">&times;</span>
                        <h3>Grade Submission</h3>
                        
                        <form id="grade-form">
                            <input type="hidden" id="grade-submission-id" name="submission_id">
                            <input type="hidden" id="grade-assignment-id" name="assignment_id">
                            <input type="hidden" id="grade-student-id" name="student_id">
                            
                            <div class="form-group">
                                <label for="student-name">Student</label>
                                <input type="text" id="student-name" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="assignment-name">Assignment</label>
                                <input type="text" id="assignment-name" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="submission-content">Submission Content</label>
                                <textarea id="submission-content" rows="5" readonly></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="grade-value">Grade (0-100)</label>
                                <input type="number" id="grade-value" name="grade" min="0" max="100" required>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Save Grade</button>
                                <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
            
            <section id="materials" class="dashboard-section" style="display: none;">
                <h2>Learning Materials</h2>
                
                <div class="materials-management">
                    <button id="upload-material-btn" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload New Material
                    </button>
                    
                    <div class="materials-list">
                        <table class="materials-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Subject</th>
                                    <th>Class</th>
                                    <th>Form</th>
                                    <th>Upload Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Materials would be loaded from database -->
                                <tr>
                                    <td>Mathematics Chapter 1</td>
                                    <td>Mathematics</td>
                                    <td>4A</td>
                                    <td>4</td>
                                    <td>2025-06-18</td>
                                    <td>
                                        <a href="#" class="btn btn-small btn-view">View</a>
                                        <button class="btn btn-small btn-delete">Delete</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Science Experiment Guide</td>
                                    <td>Science</td>
                                    <td>All</td>
                                    <td>4-5</td>
                                    <td>2025-07-20</td>
                                    <td>
                                        <a href="#" class="btn btn-small btn-view">View</a>
                                        <button class="btn btn-small btn-delete">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Upload Material Modal -->
                <div id="material-modal" class="modal">
                    <div class="modal-content">
                        <span class="close-modal">&times;</span>
                        <h3>Upload Learning Material</h3>
                        
                        <form id="material-form">
                            <div class="form-group">
                                <label for="material-title">Title</label>
                                <input type="text" id="material-title" name="title" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="material-subject">Subject</label>
                                <select id="material-subject" name="subject" required>
                                    <option value="Mathematics">Mathematics</option>
                                    <option value="Science">Science</option>
                                    <option value="English">English</option>
                                    <option value="History">History</option>
                                    <option value="Geography">Geography</option>
                                    <option value="Biology">Biology</option>
                                    <option value="Chemistry">Chemistry</option>
                                    <option value="Physics">Physics</option>
                                    <option value="Additional Mathematics">Additional Mathematics</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="material-file">File</label>
                                <input type="file" id="material-file" name="file" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="material-class">For Class</label>
                                <input type="text" id="material-class" name="class" placeholder="e.g., 4A, 5B (leave blank for all)">
                            </div>
                            
                            <div class="form-group">
                                <label for="material-form">For Form</label>
                                <select id="material-form" name="form">
                                    <option value="">All Forms</option>
                                    <option value="4">Form 4</option>
                                    <option value="5">Form 5</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="material-description">Description</label>
                                <textarea id="material-description" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Upload Material</button>
                                <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </main>
    
    <script>
        // Dashboard navigation
        document.querySelectorAll('.sidebar-nav a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all links
                document.querySelectorAll('.sidebar-nav a').forEach(l => l.classList.remove('active'));
                
                // Add active class to clicked link
                this.classList.add('active');
                
                // Hide all sections
                document.querySelectorAll('.dashboard-section').forEach(section => {
                    section.style.display = 'none';
                });
                
                // Show the selected section
                const sectionId = this.getAttribute('href').substring(1);
                document.getElementById(sectionId).style.display = 'block';
            });
        });
        
        // Show the first section by default
        document.querySelector('.sidebar-nav a').click();
        
        // Assignment Modal
        const assignmentModal = document.getElementById('assignment-modal');
        const createAssignmentBtn = document.getElementById('create-assignment-btn');
        const closeModalBtns = document.querySelectorAll('.close-modal');
        const assignmentForm = document.getElementById('assignment-form');
        
        // Open modal for new assignment
        createAssignmentBtn.addEventListener('click', () => {
            document.getElementById('modal-title').textContent = 'Create New Assignment';
            document.getElementById('assignment-id').value = '';
            assignmentForm.reset();
            assignmentModal.style.display = 'block';
        });
        
        // Close modal
        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                assignmentModal.style.display = 'none';
                document.getElementById('grade-modal').style.display = 'none';
                document.getElementById('material-modal').style.display = 'none';
            });
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === assignmentModal) {
                assignmentModal.style.display = 'none';
            }
            if (e.target === document.getElementById('grade-modal')) {
                document.getElementById('grade-modal').style.display = 'none';
            }
            if (e.target === document.getElementById('material-modal')) {
                document.getElementById('material-modal').style.display = 'none';
            }
        });
        
        // Edit assignment
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const assignmentId = this.getAttribute('data-assignment-id');
                
                // In a real app, you would fetch the assignment data from the server
                // For this example, we'll simulate it
                fetch(`get_assignment.php?id=${assignmentId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('modal-title').textContent = 'Edit Assignment';
                            document.getElementById('assignment-id').value = data.assignment.id;
                            document.getElementById('assignment-title').value = data.assignment.title;
                            document.getElementById('assignment-subject').value = data.assignment.subject;
                            document.getElementById('assignment-due-date').value = data.assignment.due_date;
                            document.getElementById('assignment-points').value = data.assignment.points;
                            document.getElementById('assignment-description').value = data.assignment.description || '';
                            document.getElementById('assignment-class').value = data.assignment.class || '';
                            document.getElementById('assignment-form').value = data.assignment.form || '';
                            
                            assignmentModal.style.display = 'block';
                        } else {
                            alert(data.message || 'Error loading assignment');
                        }
                    });
            });
        });
        
        // Delete assignment
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this assignment?')) {
                    const assignmentId = this.getAttribute('data-assignment-id');
                    
                    fetch(`delete_assignment.php?id=${assignmentId}`, {
                        method: 'DELETE'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message || 'Error deleting assignment');
                        }
                    });
                }
            });
        });
        
        // Save assignment
        assignmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const assignmentId = document.getElementById('assignment-id').value;
            const method = assignmentId ? 'PUT' : 'POST';
            const url = assignmentId ? `update_assignment.php?id=${assignmentId}` : 'create_assignment.php';
            
            fetch(url, {
                method: method,
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error saving assignment');
                }
            });
        });
        
        // Grade assignment submissions
        document.getElementById('grades-assignment-filter').addEventListener('change', function() {
            const assignmentId = this.value;
            
            if (assignmentId) {
                fetch(`get_submissions.php?assignment_id=${assignmentId}`)
                    .then(response => response.json())
                    .then(data => {
                        const tbody = document.getElementById('grades-table-body');
                        tbody.innerHTML = '';
                        
                        if (data.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No submissions for this assignment yet</td></tr>';
                        } else {
                            data.forEach(submission => {
                                const tr = document.createElement('tr');
                                tr.innerHTML = `
                                    <td>${submission.student_name}</td>
                                    <td>${submission.class}</td>
                                    <td>Form ${submission.form}</td>
                                    <td>${submission.submitted_at ? new Date(submission.submitted_at).toLocaleDateString() : 'Not submitted'}</td>
                                    <td>${submission.grade !== null ? submission.grade : 'Not graded'}</td>
                                    <td>
                                        ${submission.submitted_at ? 
                                            `<button class="btn btn-small btn-grade" data-submission-id="${submission.id}" 
                                                data-assignment-id="${submission.assignment_id}" 
                                                data-student-id="${submission.student_id}"
                                                data-student-name="${submission.student_name}"
                                                data-assignment-name="${submission.assignment_title}"
                                                data-submission-content="${submission.submission || ''}">
                                                ${submission.grade !== null ? 'Regrade' : 'Grade'}
                                            </button>` : 
                                            'Not submitted'}
                                    </td>
                                `;
                                tbody.appendChild(tr);
                            });
                            
                            // Add event listeners to grade buttons
                            document.querySelectorAll('.btn-grade').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    const gradeModal = document.getElementById('grade-modal');
                                    document.getElementById('grade-submission-id').value = this.getAttribute('data-submission-id');
                                    document.getElementById('grade-assignment-id').value = this.getAttribute('data-assignment-id');
                                    document.getElementById('grade-student-id').value = this.getAttribute('data-student-id');
                                    document.getElementById('student-name').value = this.getAttribute('data-student-name');
                                    document.getElementById('assignment-name').value = this.getAttribute('data-assignment-name');
                                    document.getElementById('submission-content').value = this.getAttribute('data-submission-content');
                                    
                                    // If regrading, set the existing grade
                                    if (this.textContent === 'Regrade') {
                                        const grade = this.parentElement.previousElementSibling.textContent;
                                        document.getElementById('grade-value').value = grade.split('/')[0];
                                    } else {
                                        document.getElementById('grade-value').value = '';
                                    }
                                    
                                    gradeModal.style.display = 'block';
                                });
                            });
                        }
                    });
            } else {
                document.getElementById('grades-table-body').innerHTML = 
                    '<tr><td colspan="6" class="empty-state">Select an assignment to view submissions</td></tr>';
            }
        });
        
        // Save grade
        document.getElementById('grade-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submissionId = document.getElementById('grade-submission-id').value;
            
            fetch(`grade_submission.php?id=${submissionId}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('grade-modal').style.display = 'none';
                    document.getElementById('grades-assignment-filter').dispatchEvent(new Event('change'));
                } else {
                    alert(data.message || 'Error saving grade');
                }
            });
        });
        
        // Add student
        document.getElementById('add-student-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('add_student.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Student added successfully!');
                    this.reset();
                    // In a real app, you would refresh the students list
                } else {
                    alert(data.message || 'Error adding student');
                }
            });
        });
        
        // Remove student
        document.querySelectorAll('.btn-remove-student').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Are you sure you want to remove this student?')) {
                    const studentId = this.getAttribute('data-student-id');
                    
                    fetch(`remove_student.php?id=${studentId}`, {
                        method: 'DELETE'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message || 'Error removing student');
                        }
                    });
                }
            });
        });
        
        // Upload material
        const uploadMaterialBtn = document.getElementById('upload-material-btn');
        const materialModal = document.getElementById('material-modal');
        
        uploadMaterialBtn.addEventListener('click', () => {
            materialModal.style.display = 'block';
        });
        
        // Save material
        document.getElementById('material-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('upload_material.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Material uploaded successfully!');
                    materialModal.style.display = 'none';
                    this.reset();
                    // In a real app, you would refresh the materials list
                } else {
                    alert(data.message || 'Error uploading material');
                }
            });
        });
    </script>
</body>
</html>
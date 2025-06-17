<?php
require_once 'functions.php';

$topStudents = getTopStudents(20);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scoreboard - Nebula learn</title>
    <link rel="stylesheet" href="style.css">
    <script src="app.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="background"></div>
    
    <nav class="navbar">
        <div class="logo">Nebula learn</div>
        <div class="nav-links">
            <a href="index.html">Home</a>
            <a href="login.php">Login</a>
            <a href="signup.php">Sign Up</a>
            <a href="#" id="dashboard-link">Dashboard</a>
            <button id="theme-toggle">Dark Mode</button>
            <?php if (isLoggedIn()): ?>
                <a href="logout.php" class="btn-logout">Logout</a>
            <?php endif; ?>
        </div>
        <div class="mobile-menu-btn">☰</div>
    </nav>
    
    <div class="mobile-nav-links">
        <a href="index.html">Home</a>
        <a href="login.php">Login</a>
        <a href="signup.php">Sign Up</a>
        <a href="#" id="mobile-dashboard-link">Dashboard</a>
        <button id="mobile-theme-toggle">Dark Mode</button>
        <?php if (isLoggedIn()): ?>
            <a href="logout.php" class="btn-logout">Logout</a>
        <?php endif; ?>
    </div>
    
    <main class="scoreboard-container">
        <h1>Leaderboard</h1>
        <p class="subtitle">Top students based on points earned</p>
        
        <div class="scoreboard">
            <div class="scoreboard-header">
                <div class="rank">Rank</div>
                <div class="student">Student</div>
                <div class="points">Points</div>
                <div class="streak">Streak</div>
            </div>
            
            <div class="scoreboard-list">
                <?php if (!empty($topStudents)): ?>
                    <?php foreach ($topStudents as $index => $student): ?>
                        <div class="scoreboard-item <?php echo $index < 3 ? 'podium-' . ($index + 1) : ''; ?>">
                            <div class="rank">
                                <?php if ($index < 3): ?>
                                    <i class="fas fa-medal"></i>
                                <?php endif; ?>
                                <?php echo $index + 1; ?>
                            </div>
                            <div class="student">
                                <div class="avatar"><?php echo strtoupper(substr($student['username'], 0, 1)); ?></div>
                                <?php echo htmlspecialchars($student['username']); ?>
                            </div>
                            <div class="points"><?php echo $student['points']; ?></div>
                            <div class="streak">
                                <?php echo $student['streak']; ?>
                                <i class="fas fa-fire" style="color: var(--accent);"></i>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-trophy"></i>
                        <p>No students yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (isTeacher()): ?>
            <div class="teacher-actions">
                <h2>Teacher Actions</h2>
                <button id="award-points-btn" class="btn btn-primary">Award Bonus Points</button>
            </div>
            
            <!-- Award Points Modal -->
            <div id="award-points-modal" class="modal">
                <div class="modal-content">
                    <span class="close-modal">&times;</span>
                    <h3>Award Bonus Points</h3>
                    
                    <form id="award-points-form">
                        <div class="form-group">
                            <label for="award-student">Student</label>
                            <select id="award-student" name="student_id" required>
                                <?php foreach ($topStudents as $student): ?>
                                    <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['username']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="award-points">Points</label>
                            <input type="number" id="award-points" name="points" min="1" max="100" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="award-reason">Reason</label>
                            <textarea id="award-reason" name="reason" rows="3" required></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Award Points</button>
                            <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </main>
    
    <script>
        // Award points modal
        const awardPointsBtn = document.getElementById('award-points-btn');
        const awardPointsModal = document.getElementById('award-points-modal');
        const closeModalBtns = document.querySelectorAll('.close-modal');
        
        if (awardPointsBtn) {
            awardPointsBtn.addEventListener('click', () => {
                awardPointsModal.style.display = 'block';
            });
        }
        
        // Close modal
        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                if (awardPointsModal) {
                    awardPointsModal.style.display = 'none';
                }
            });
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (awardPointsModal && e.target === awardPointsModal) {
                awardPointsModal.style.display = 'none';
            }
        });
        
        // Award points form
        if (document.getElementById('award-points-form')) {
            document.getElementById('award-points-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch('award_points.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Points awarded successfully!');
                        awardPointsModal.style.display = 'none';
                        this.reset();
                        location.reload();
                    } else {
                        alert(data.message || 'Error awarding points');
                    }
                });
            });
        }
    </script>
</body>
</html>
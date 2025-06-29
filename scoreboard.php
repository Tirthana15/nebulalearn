
<?php 
require_once 'functions.php'; 
if (!isLoggedIn()) { 
    redirect('login.php'); 
} 

// Define calculateSubmissionSpeed if not already defined in functions.php
if (!function_exists('calculateSubmissionSpeed')) {
    /**
     * Calculate the average submission speed (in seconds) for a student.
     * Replace this with your actual logic as needed.
     */
    function calculateSubmissionSpeed($studentId) {
        // Example: Fetch submission timestamps from DB and calculate average interval
        // Replace this with your actual DB logic
        // For demonstration, return a random value between 60 and 600 seconds
        return rand(60, 600);
    }
}

// Define formatSubmissionSpeed if not already defined in functions.php
if (!function_exists('formatSubmissionSpeed')) {
    /**
     * Format the submission speed (seconds) as mm:ss.
     */
    function formatSubmissionSpeed($seconds) {
        $minutes = floor($seconds / 60);
        $secs = $seconds % 60;
        return sprintf('%02d:%02d', $minutes, $secs);
    }
}

$user = getUserData(); 
$topStudents = getTopStudents(20); 
$friends = isStudent() ? getStudentFriends($user['id']) : []; 

// Calculate ranks based on points and submission speed
foreach ($topStudents as &$student) {
    $student['submission_speed'] = calculateSubmissionSpeed($student['id']);
}

// Sort students by points (descending) and then by submission speed (ascending)
usort($topStudents, function($a, $b) {
    if ($a['points'] == $b['points']) {
        return $a['submission_speed'] - $b['submission_speed'];
    }
    return $b['points'] - $a['points'];
});

// Add rank position
$rank = 1;
foreach ($topStudents as &$student) {
    $student['rank'] = $rank++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scoreboard - Nebula Learn</title>
    <link rel="stylesheet" href="style.css">
    <script src="app.js" defer></script>
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

    <main class="scoreboard-container">
        <h1>Scoreboard</h1>
        
        <div class="scoreboard-tabs">
            <button class="tab-btn active" data-tab="global">Global Leaderboard</button>
            <?php if (isStudent()): ?>
                <button class="tab-btn" data-tab="friends">Friends</button>
                <button class="tab-btn" data-tab="class">Class</button>
            <?php endif; ?>
        </div>
        
        <div class="tab-content active" id="global-tab">
            <table class="scoreboard-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Username</th>
                        <th>Class</th>
                        <th>Points</th>
                        <th>Avg Speed</th>
                        <th>Streak</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topStudents as $student): ?>
                        <tr class="<?php echo $user['id'] == $student['id'] ? 'current-user' : ''; ?>">
                            <td><?php echo $student['rank']; ?></td>
                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                            <td><?php echo htmlspecialchars($student['class']); ?></td>
                            <td><?php echo $student['points']; ?></td>
                            <td><?php echo formatSubmissionSpeed($student['submission_speed']); ?></td>
                            <td><?php echo $student['streak']; ?> days</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (isStudent()): ?>
            <div class="tab-content" id="friends-tab">
                <?php if (!empty($friends)): ?>
                    <table class="scoreboard-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Username</th>
                                <th>Points</th>
                                <th>Avg Speed</th>
                                <th>Streak</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Calculate ranks for friends
                            $friendRank = 1;
                            foreach ($friends as $friend): 
                                $friend['submission_speed'] = calculateSubmissionSpeed($friend['id']);
                            ?>
                                <tr class="<?php echo $user['id'] == $friend['id'] ? 'current-user' : ''; ?>">
                                    <td><?php echo $friendRank++; ?></td>
                                    <td><?php echo htmlspecialchars($friend['username']); ?></td>
                                    <td><?php echo $friend['points']; ?></td>
                                    <td><?php echo formatSubmissionSpeed($friend['submission_speed']); ?></td>
                                    <td><?php echo $friend['streak']; ?> days</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <p>You don't have any friends yet. Add some to see their progress!</p>
                        <a href="user_dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="tab-content" id="class-tab">
                <?php
                    $classStudents = getStudentsByClass($user['class'], $user['form']);
                    foreach ($classStudents as &$classmate) {
                        $classmate['submission_speed'] = calculateSubmissionSpeed($classmate['id']);
                    }
                    
                    // Sort classmates by points and speed
                    usort($classStudents, function($a, $b) {
                        if ($a['points'] == $b['points']) {
                            return $a['submission_speed'] - $b['submission_speed'];
                        }
                        return $b['points'] - $a['points'];
                    });
                    
                    $classRank = 1;
                ?>
                <?php if (!empty($classStudents)): ?>
                    <table class="scoreboard-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Username</th>
                                <th>Points</th>
                                <th>Avg Speed</th>
                                <th>Streak</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classStudents as $classmate): ?>
                                <tr class="<?php echo $user['id'] == $classmate['id'] ? 'current-user' : ''; ?>">
                                    <td><?php echo $classRank++; ?></td>
                                    <td><?php echo htmlspecialchars($classmate['username']); ?></td>
                                    <td><?php echo $classmate['points']; ?></td>
                                    <td><?php echo formatSubmissionSpeed($classmate['submission_speed']); ?></td>
                                    <td><?php echo $classmate['streak']; ?> days</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No classmates found.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons and tabs
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Show corresponding tab
                const tabId = this.getAttribute('data-tab') + '-tab';
                document.getElementById(tabId).classList.add('active');
            });
        });
    </script>
</body>
</html>
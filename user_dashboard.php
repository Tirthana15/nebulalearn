
<?php require_once 'functions.php'; if (!isLoggedIn() || !isStudent()) { redirect('login.php'); } $user = getUserData(); $assignments = getStudentAssignments($user['id']); $friends = getStudentFriends($user['id']); $pendingRequests = getPendingFriendRequests($user['id']); $activeGroups = getActiveStudyGroups(); ?><!DOCTYPE html><html lang="en"> <head> <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Student Dashboard - Nebula Learn</title> <link rel="stylesheet" href="style.css"> <script src="app.js" defer></script> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> </head> <body> <div class="background"></div>
    <nav class="navbar">
    <div class="logo">Nebula Learn</div>
    <div class="nav-links">
        <a href="index.html">Home</a>
        <a href="login.php">Login</a>
        <a href="signup.php">Sign Up</a>
        <a href="user_dashboard.php" class="active">Dashboard</a>
        <button id="theme-toggle">Dark Mode</button>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
    <div class="mobile-menu-btn">☰</div>
</nav>

<div class="mobile-nav-links">
    <a href="index.html">Home</a>
    <a href="login.php">Login</a>
    <a href="signup.php">Sign Up</a>
    <a href="user_dashboard.php" class="active">Dashboard</a>
    <button id="mobile-theme-toggle">Dark Mode</button>
    <a href="logout.php" class="btn-logout">Logout</a>
</div>

<main class="dashboard-container">
    <div class="dashboard-sidebar">
        <div class="user-profile">
            <div class="avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
            <h3><?php echo htmlspecialchars($user['username']); ?></h3>
            <p>Class <?php echo htmlspecialchars($user['class']); ?>, Form <?php echo htmlspecialchars($user['form']); ?></p>
            <div class="user-stats">
                <div class="stat">
                    <span class="stat-value"><?php echo $user['points']; ?></span>
                    <span class="stat-label">Points</span>
                </div>
                <div class="stat">
                    <span class="stat-value"><?php echo $user['streak']; ?></span>
                    <span class="stat-label">Day Streak</span>
                </div>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="#assignments" class="active"><i class="fas fa-tasks"></i> Assignments</a>
            <a href="#study"><i class="fas fa-graduation-cap"></i> Study</a>
            <a href="#friends"><i class="fas fa-users"></i> Friends</a>
            <a href="#scoreboard"><i class="fas fa-trophy"></i> Scoreboard</a>
        </nav>
    </div>
    
    <div class="dashboard-content">
        <section id="assignments" class="dashboard-section">
            <h2>Your Assignments</h2>
            
            <div class="assignments-list">
                <?php if (!empty($assignments)): ?>
                    <table class="assignments-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Subject</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $assignment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['subject']); ?></td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($assignment['due_date'])); ?>
                                        <span class="due-date-badge <?php 
                                            $dueIn = (strtotime($assignment['due_date']) - time()) / (60 * 60 * 24);
                                            if ($dueIn < 0) echo 'overdue';
                                            elseif ($dueIn < 3) echo 'due-soon';
                                        ?>">
                                            <?php 
                                                if ($dueIn < 0) {
                                                    echo abs(floor($dueIn)) . ' days overdue';
                                                } else {
                                                    echo floor($dueIn) . ' days left';
                                                }
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php 
                                            echo $assignment['submitted_at'] ? 'submitted' : 'pending'; 
                                        ?>">
                                            <?php echo $assignment['submitted_at'] ? 'Submitted' : 'Pending'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($assignment['grade'] !== null): ?>
                                            <?php echo $assignment['grade']; ?>/100
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="assignment.php?id=<?php echo $assignment['id']; ?>" class="btn btn-small btn-view">
                                            <?php echo $assignment['submitted_at'] ? 'View' : 'Start'; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <p>No assignments yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <section id="study" class="dashboard-section" style="display: none;">
            <h2>Study Tools</h2>
            
            <div class="study-tools">
                <div class="study-timer">
                    <h3>Pomodoro Timer</h3>
                    <div class="timer-display">25:00</div>
                    <div class="timer-controls">
                        <button id="start-study-btn" class="btn btn-primary">Start Study Session</button>
                        <button id="start-break-btn" class="btn btn-secondary">Start Break</button>
                        <button id="stop-timer-btn" class="btn btn-danger" disabled>Stop</button>
                    </div>
                    <div class="timer-settings">
                        <div class="form-group">
                            <label for="study-duration">Study Duration (minutes)</label>
                            <input type="number" id="study-duration" min="1" value="25">
                        </div>
                        <div class="form-group">
                            <label for="break-duration">Break Duration (minutes)</label>
                            <input type="number" id="break-duration" min="1" value="5">
                        </div>
                    </div>
                </div>
                
                <div class="study-groups">
                    <h3>Study Groups</h3>
                    
                    <div class="group-actions">
                        <button id="create-group-btn" class="btn btn-primary">Create Group</button>
                        <button id="join-group-btn" class="btn btn-secondary">Join Group</button>
                    </div>
                    
                    <?php if (!empty($activeGroups)): ?>
                        <div class="active-groups">
                            <h4>Active Groups</h4>
                            <ul class="groups-list">
                                <?php foreach ($activeGroups as $group): ?>
                                    <li>
                                        <span><?php echo htmlspecialchars($group['username']); ?> (<?php echo htmlspecialchars($group['class']); ?>)</span>
                                        <?php if ($group['username'] === $user['username']): ?>
                                            <span class="group-owner">Your group</span>
                                        <?php else: ?>
                                            <button class="btn btn-small btn-join" data-session-id="<?php echo $group['session_id']; ?>">Join</button>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        
        <section id="friends" class="dashboard-section" style="display: none;">
            <h2>Friends</h2>
            
            <div class="friends-management">
                <div class="friends-list">
                    <h3>Your Friends</h3>
                    
                    <?php if (!empty($friends)): ?>
                        <ul class="friends-grid">
                            <?php foreach ($friends as $friend): ?>
                                <li>
                                    <div class="friend-avatar"><?php echo strtoupper(substr($friend['username'], 0, 1)); ?></div>
                                    <div class="friend-info">
                                        <h4><?php echo htmlspecialchars($friend['username']); ?></h4>
                                        <p><?php echo $friend['points']; ?> points</p>
                                        <p><?php echo $friend['streak']; ?> day streak</p>
                                    </div>
                                    <button class="btn btn-small btn-remove-friend" data-friend-id="<?php echo $friend['id']; ?>">Remove</button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-friends"></i>
                            <p>You don't have any friends yet</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="friend-requests">
                    <h3>Pending Requests</h3>
                    
                    <?php if (!empty($pendingRequests)): ?>
                        <ul class="requests-list">
                            <?php foreach ($pendingRequests as $request): ?>
                                <li>
                                    <div class="request-avatar"><?php echo strtoupper(substr($request['username'], 0, 1)); ?></div>
                                    <div class="request-info">
                                        <h4><?php echo htmlspecialchars($request['username']); ?></h4>
                                    </div>
                                    <div class="request-actions">
                                        <button class="btn btn-small btn-accept" data-request-id="<?php echo $request['request_id']; ?>">Accept</button>
                                        <button class="btn btn-small btn-decline" data-request-id="<?php echo $request['request_id']; ?>">Decline</button>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-envelope"></i>
                            <p>No pending requests</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="add-friend">
                    <h3>Add Friend</h3>
                    <form id="add-friend-form">
                        <div class="form-group">
                            <label for="friend-username">Username</label>
                            <input type="text" id="friend-username" name="username" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Request</button>
                    </form>
                </div>
            </div>
        </section>

        <section id="scoreboard" class="dashboard-section" style="display: none;">
            <h2>Scoreboard</h2>
            <?php
            $topStudents = getTopStudents(10);
            foreach ($topStudents as &$student) {
                $student['submission_speed'] = calculateSubmissionSpeed($student['id']);
            }
            usort($topStudents, function($a, $b) {
                return $b['points'] <=> $a['points'] ?: $a['submission_speed'] <=> $b['submission_speed'];
            });
            $rank = 1;
            ?>
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
                        <tr class="<?php echo $user['id'] == $student['id'] ? 'current-user' : ''; ?>"></tr>
                            <td><?php echo $rank++; ?></td>
                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                            <td><?php echo htmlspecialchars($student['class']); ?></td>
                            <td><?php echo $student['points']; ?></td>
                            <td><?php echo formatSubmissionSpeed($student['submission_speed']); ?></td>
                            <td><?php echo $student['streak']; ?> days</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
    
    // Pomodoro Timer
    let timerInterval;
    let timeLeft = 25 * 60; // 25 minutes in seconds
    let isRunning = false;
    let currentSessionType = 'study';
    
    function updateTimerDisplay() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        document.querySelector('.timer-display').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
    
    function startTimer(duration, sessionType) {
        if (isRunning) return;
        
        currentSessionType = sessionType;
        timeLeft = duration * 60;
        isRunning = true;
        
        document.getElementById('start-study-btn').disabled = true;
        document.getElementById('start-break-btn').disabled = true;
        document.getElementById('stop-timer-btn').disabled = false;
        
        updateTimerDisplay();
        
        timerInterval = setInterval(() => {
            timeLeft--;
            updateTimerDisplay();
            
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                isRunning = false;
                alert(`${sessionType === 'study' ? 'Study' : 'Break'} session completed!`);
                
                // Automatically start break after study or vice versa
                if (sessionType === 'study') {
                    const breakDuration = parseInt(document.getElementById('break-duration').value);
                    startTimer(breakDuration, 'break');
                } else {
                    const studyDuration = parseInt(document.getElementById('study-duration').value);
                    startTimer(studyDuration, 'study');
                }
            }
        }, 1000);
    }
    
    document.getElementById('start-study-btn').addEventListener('click', () => {
        const duration = parseInt(document.getElementById('study-duration').value);
        startTimer(duration, 'study');
        
        // Send to server
        fetch('start_study_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ session_type: 'study' })
        });
    });
    
    document.getElementById('start-break-btn').addEventListener('click', () => {
        const duration = parseInt(document.getElementById('break-duration').value);
        startTimer(duration, 'break');
        
        // Send to server
        fetch('start_study_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ session_type: 'break' })
        });
    });
    
    document.getElementById('stop-timer-btn').addEventListener('click', () => {
        clearInterval(timerInterval);
        isRunning = false;
        document.getElementById('start-study-btn').disabled = false;
        document.getElementById('start-break-btn').disabled = false;
        document.getElementById('stop-timer-btn').disabled = true;
        
        // Send to server
        fetch('stop_study_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ session_id: 'current' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.points_earned > 0) {
                alert(`You earned ${data.points_earned} points for your study session!`);
            }
        });
    });
    
    // Study Groups
    document.getElementById('create-group-btn').addEventListener('click', () => {
        const sessionId = Math.random().toString(36).substring(2, 8);
        
        fetch('create_study_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ session_id: sessionId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Created study group with ID: ${sessionId}`);
                location.reload();
            } else {
                alert(data.message || 'Error creating study group');
            }
        });
    });
    
    document.getElementById('join-group-btn').addEventListener('click', () => {
        const sessionId = prompt('Enter study group ID:');
        if (sessionId) {
            fetch('join_study_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ session_id: sessionId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Joined study group: ${sessionId}`);
                    location.reload();
                } else {
                    alert(data.message || 'Error joining study group');
                }
            });
        }
    });
    
    document.querySelectorAll('.btn-join').forEach(btn => {
        btn.addEventListener('click', function() {
            const sessionId = this.getAttribute('data-session-id');
            
            fetch('join_study_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ session_id: sessionId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Joined study group: ${sessionId}`);
                    location.reload();
                } else {
                    alert(data.message || 'Error joining study group');
                }
            });
        });
    });
    
    // Friends
    document.getElementById('add-friend-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('friend-username').value;
        
        fetch('add_friend.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Friend request sent!');
                this.reset();
            } else {
                alert(data.message || 'Error sending friend request');
            }
        });
    });
    
    document.querySelectorAll('.btn-accept').forEach(btn => {
        btn.addEventListener('click', function() {
            const requestId = this.getAttribute('data-request-id');
            
            fetch('handle_friend_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    request_id: requestId,
                    action: 'accept'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error accepting request');
                }
            });
        });
    });
    
    document.querySelectorAll('.btn-decline').forEach(btn => {
        btn.addEventListener('click', function() {
            const requestId = this.getAttribute('data-request-id');
            
            fetch('handle_friend_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    request_id: requestId,
                    action: 'decline'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error declining request');
                }
            });
        });
    });
    
    document.querySelectorAll('.btn-remove-friend').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Are you sure you want to remove this friend?')) {
                const friendId = this.getAttribute('data-friend-id');
                
                fetch('remove_friend.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ friend_id: friendId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error removing friend');
                    }
                });
            }
        });
    });
</script>
</body>
</html>
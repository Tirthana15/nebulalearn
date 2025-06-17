<?php
require_once 'functions.php';

if (!isLoggedIn() || !isStudent()) {
    redirect('login.php');
}

$user = getUserData();
$assignments = getStudentAssignments($user['id']);
$friends = getStudentFriends($user['id']);
$pendingRequests = getPendingFriendRequests($user['id']);
$studyGroups = getActiveStudyGroups();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Nebula learn</title>
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
                <p>Form <?php echo htmlspecialchars($user['form']); ?> - <?php echo htmlspecialchars($user['class']); ?></p>
                
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
                <a href="#study-timer"><i class="fas fa-clock"></i> Study Timer</a>
                <a href="#friends"><i class="fas fa-users"></i> Friends</a>
                <a href="#calendar"><i class="fas fa-calendar"></i> Calendar</a>
                <a href="scoreboard.php"><i class="fas fa-trophy"></i> Scoreboard</a>
                
            </nav>
        </div>
        
        <div class="dashboard-content">
            <section id="assignments" class="dashboard-section">
                <h2>Your Assignments</h2>
                
                <div class="assignment-filters">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="pending">Pending</button>
                    <button class="filter-btn" data-filter="submitted">Submitted</button>
                    <button class="filter-btn" data-filter="graded">Graded</button>
                </div>
                
                <div class="assignments-grid">
                    <?php foreach ($assignments as $assignment): ?>
                        <div class="assignment-card" data-status="<?php echo $assignment['submitted_at'] ? ($assignment['grade'] !== null ? 'graded' : 'submitted') : 'pending'; ?>">
                            <div class="assignment-header">
                                <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
                                <span class="subject-badge"><?php echo htmlspecialchars($assignment['subject']); ?></span>
                            </div>
                            
                            <div class="assignment-details">
                                <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($assignment['teacher_name']); ?></p>
                                <p><i class="fas fa-calendar-day"></i> Due: <?php echo date('M j, Y', strtotime($assignment['due_date'])); ?></p>
                                <p><i class="fas fa-star"></i> Points: <?php echo $assignment['points']; ?></p>
                            </div>
                            
                            <?php if ($assignment['submitted_at']): ?>
                                <div class="assignment-status submitted">
                                    <p>Submitted on <?php echo date('M j, Y', strtotime($assignment['submitted_at'])); ?></p>
                                    <?php if ($assignment['grade'] !== null): ?>
                                        <p class="grade">Grade: <?php echo $assignment['grade']; ?>/100</p>
                                    <?php else: ?>
                                        <p class="grade">Awaiting grade</p>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="assignment-status pending">
                                    <p>Not submitted</p>
                                    <p class="days-left">
                                        <?php 
                                            $dueDate = new DateTime($assignment['due_date']);
                                            $today = new DateTime();
                                            $interval = $today->diff($dueDate);
                                            echo $interval->format('%a days left');
                                        ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="assignment-actions">
                                <a href="assignment.php?id=<?php echo $assignment['id']; ?>" class="btn btn-view">View</a>
                                <?php if (!$assignment['submitted_at']): ?>
                                    <button class="btn btn-submit" data-assignment-id="<?php echo $assignment['id']; ?>">Submit</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($assignments)): ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p>No assignments yet! Check back later.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <section id="study-timer" class="dashboard-section" style="display: none;">
                <h2>Study Timer</h2>
                
                <div class="timer-container">
                    <div class="timer-display">
                        <div class="timer-circle">
                            <svg class="timer-svg" viewBox="0 0 100 100">
                                <circle class="timer-circle-bg" cx="50" cy="50" r="45"></circle>
                                <circle class="timer-circle-fg" cx="50" cy="50" r="45"></circle>
                            </svg>
                            <div class="timer-text">
                                <span id="timer-minutes">25</span>:<span id="timer-seconds">00</span>
                            </div>
                        </div>
                        <div class="timer-mode">
                            <span id="timer-mode-label">Study</span>
                        </div>
                    </div>
                    
                    <div class="timer-controls">
                        <button id="timer-start" class="btn btn-primary">Start</button>
                        <button id="timer-pause" class="btn btn-secondary" disabled>Pause</button>
                        <button id="timer-reset" class="btn btn-secondary">Reset</button>
                    </div>
                    
                    <div class="timer-options">
                        <div class="option">
                            <input type="radio" id="pomodoro-25" name="timer-option" value="25" checked>
                            <label for="pomodoro-25">25/5</label>
                        </div>
                        <div class="option">
                            <input type="radio" id="pomodoro-50" name="timer-option" value="50">
                            <label for="pomodoro-50">50/10</label>
                        </div>
                        <div class="option">
                            <input type="radio" id="custom-time" name="timer-option" value="custom">
                            <label for="custom-time">Custom</label>
                            <input type="number" id="custom-minutes" min="1" max="120" placeholder="Min" disabled>
                        </div>
                    </div>
                    
                    <div class="surgery-animation">
                        <div class="operation-room">
                            <div class="doctor"></div>
                            <div class="patient"></div>
                            <div class="equipment"></div>
                        </div>
                        <div class="status-message">Ready to operate</div>
                    </div>
                    
                    <div class="co-study-section">
                        <h3>Co-Study with Friends</h3>
                        
                        <div class="co-study-options">
                            <button id="create-session" class="btn btn-primary">Create Session</button>
                            <button id="join-session" class="btn btn-secondary">Join Session</button>
                        </div>
                        
                        <div id="active-sessions" class="active-sessions">
                            <h4>Active Study Groups</h4>
                            <?php if (!empty($studyGroups)): ?>
                                <ul>
                                    <?php foreach ($studyGroups as $group): ?>
                                        <li>
                                            <span><?php echo htmlspecialchars($group['username']); ?> (Form <?php echo htmlspecialchars($group['form']); ?> <?php echo htmlspecialchars($group['class']); ?>)</span>
                                            <button class="btn btn-small join-group" data-session-id="<?php echo htmlspecialchars($group['session_id']); ?>">Join</button>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>No active study groups</p>
                            <?php endif; ?>
                        </div>
                        
                        <div id="session-info" class="session-info" style="display: none;">
                            <h4>Current Session</h4>
                            <p>Session ID: <span id="current-session-id"></span></p>
                            <div class="participants-list">
                                <h5>Participants</h5>
                                <ul id="participants-list"></ul>
                            </div>
                            <button id="leave-session" class="btn btn-secondary">Leave Session</button>
                        </div>
                    </div>
                </div>
            </section>
            
            <section id="friends" class="dashboard-section" style="display: none;">
                <h2>Friends</h2>
                
                <div class="friends-container">
                    <div class="friends-list">
                        <h3>Your Friends</h3>
                        
                        <?php if (!empty($friends)): ?>
                            <ul>
                                <?php foreach ($friends as $friend): ?>
                                    <li>
                                        <div class="friend-info">
                                            <div class="friend-avatar"><?php echo strtoupper(substr($friend['username'], 0, 1)); ?></div>
                                            <div>
                                                <h4><?php echo htmlspecialchars($friend['username']); ?></h4>
                                                <p>Points: <?php echo $friend['points']; ?> | Streak: <?php echo $friend['streak']; ?></p>
                                            </div>
                                        </div>
                                        <div class="friend-actions">
                                            <button class="btn btn-small btn-study" data-friend-id="<?php echo $friend['id']; ?>">Study Together</button>
                                            <button class="btn btn-small btn-remove" data-friend-id="<?php echo $friend['id']; ?>">Remove</button>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-user-friends"></i>
                                <p>You don't have any friends yet. Add some to study together!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="friend-requests">
                        <h3>Pending Requests</h3>
                        
                        <?php if (!empty($pendingRequests)): ?>
                            <ul>
                                <?php foreach ($pendingRequests as $request): ?>
                                    <li>
                                        <div class="friend-info">
                                            <div class="friend-avatar"><?php echo strtoupper(substr($request['username'], 0, 1)); ?></div>
                                            <h4><?php echo htmlspecialchars($request['username']); ?></h4>
                                        </div>
                                        <div class="friend-actions">
                                            <button class="btn btn-small btn-accept" data-request-id="<?php echo $request['request_id']; ?>">Accept</button>
                                            <button class="btn btn-small btn-decline" data-request-id="<?php echo $request['request_id']; ?>">Decline</button>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-envelope"></i>
                                <p>No pending friend requests</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="add-friend">
                        <h3>Add Friend</h3>
                        <form id="add-friend-form">
                            <div class="form-group">
                                <label for="friend-username">Username</label>
                                <input type="text" id="friend-username" name="friend-username" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Request</button>
                        </form>
                    </div>
                </div>
            </section>
            
            <section id="calendar" class="dashboard-section" style="display: none;">
                <h2>Calendar</h2>
                
                <div class="calendar-container">
                    <div class="calendar-header">
                        <button id="prev-month"><i class="fas fa-chevron-left"></i></button>
                        <h3 id="current-month-year">Month Year</h3>
                        <button id="next-month"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    
                    <div class="calendar-grid">
                        <div class="calendar-weekdays">
                            <div>Sun</div>
                            <div>Mon</div>
                            <div>Tue</div>
                            <div>Wed</div>
                            <div>Thu</div>
                            <div>Fri</div>
                            <div>Sat</div>
                        </div>
                        
                        <div class="calendar-days" id="calendar-days">
                            <!-- Days will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="calendar-events">
                        <h3 id="selected-date-events-title">No date selected</h3>
                        <ul id="selected-date-events">
                            <!-- Events will be populated by JavaScript -->
                        </ul>
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
        
        // Assignment filtering
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                document.querySelectorAll('.assignment-card').forEach(card => {
                    if (filter === 'all') {
                        card.style.display = 'block';
                    } else {
                        card.style.display = card.getAttribute('data-status') === filter ? 'block' : 'none';
                    }
                });
            });
        });
        
        // Timer functionality
        let timerInterval;
        let timeLeft = 25 * 60; // 25 minutes in seconds
        let isRunning = false;
        let isStudyMode = true;
        let studyDuration = 25 * 60;
        let breakDuration = 5 * 60;
        
        const timerMinutes = document.getElementById('timer-minutes');
        const timerSeconds = document.getElementById('timer-seconds');
        const timerModeLabel = document.getElementById('timer-mode-label');
        const startBtn = document.getElementById('timer-start');
        const pauseBtn = document.getElementById('timer-pause');
        const resetBtn = document.getElementById('timer-reset');
        const timerOptions = document.querySelectorAll('input[name="timer-option"]');
        const customMinutesInput = document.getElementById('custom-minutes');
        const timerCircleFg = document.querySelector('.timer-circle-fg');
        const statusMessage = document.querySelector('.status-message');
        const doctor = document.querySelector('.doctor');
        const patient = document.querySelector('.patient');
        
        // Update timer display
        function updateTimerDisplay() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            timerMinutes.textContent = minutes.toString().padStart(2, '0');
            timerSeconds.textContent = seconds.toString().padStart(2, '0');
            
            // Update circle progress
            const totalTime = isStudyMode ? studyDuration : breakDuration;
            const percentage = (timeLeft / totalTime) * 100;
            const circumference = 2 * Math.PI * 45;
            const offset = circumference - (percentage / 100) * circumference;
            
            timerCircleFg.style.strokeDashoffset = offset;
        }
        
        // Start timer
        function startTimer() {
            if (!isRunning) {
                isRunning = true;
                startBtn.disabled = true;
                pauseBtn.disabled = false;
                
                // Start surgery animation
                if (isStudyMode) {
                    statusMessage.textContent = "Operation in progress...";
                    doctor.classList.add('operating');
                    patient.classList.add('alive');
                } else {
                    statusMessage.textContent = "Patient resting...";
                }
                
                timerInterval = setInterval(() => {
                    timeLeft--;
                    updateTimerDisplay();
                    
                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        isRunning = false;
                        
                        if (isStudyMode) {
                            // Study session completed
                            statusMessage.textContent = "Operation successful! Patient saved!";
                            
                            // Add points for completing study session
                            fetch('update_points.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    points: 10,
                                    action: 'complete_study_session'
                                })
                            });
                            
                            // Start break
                            isStudyMode = false;
                            timeLeft = breakDuration;
                            timerModeLabel.textContent = "Break";
                            startTimer();
                        } else {
                            // Break completed
                            statusMessage.textContent = "Break over. Ready for next operation!";
                            doctor.classList.remove('operating');
                            patient.classList.remove('alive');
                            
                            // Start next study session
                            isStudyMode = true;
                            timeLeft = studyDuration;
                            timerModeLabel.textContent = "Study";
                        }
                    }
                }, 1000);
            }
        }
        
        // Pause timer
        function pauseTimer() {
            if (isRunning) {
                clearInterval(timerInterval);
                isRunning = false;
                startBtn.disabled = false;
                pauseBtn.disabled = true;
                
                if (isStudyMode) {
                    statusMessage.textContent = "Operation paused! Patient is dying!";
                    patient.classList.add('dying');
                    
                    // Penalty for pausing during study
                    fetch('update_points.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            points: -5,
                            action: 'pause_study_session'
                        })
                    });
                } else {
                    statusMessage.textContent = "Break paused";
                }
            }
        }
        
        // Reset timer
        function resetTimer() {
            clearInterval(timerInterval);
            isRunning = false;
            startBtn.disabled = false;
            pauseBtn.disabled = true;
            
            if (isStudyMode) {
                timeLeft = studyDuration;
                statusMessage.textContent = "Operation aborted! Patient died!";
                doctor.classList.remove('operating');
                patient.classList.add('dead');
                patient.classList.remove('alive', 'dying');
                
                // Penalty for resetting during study
                fetch('update_points.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        points: -10,
                        action: 'reset_study_session'
                    })
                });
            } else {
                timeLeft = breakDuration;
                statusMessage.textContent = "Break reset";
                patient.classList.remove('dying');
            }
            
            updateTimerDisplay();
        }
        
        // Timer option change
        timerOptions.forEach(option => {
            option.addEventListener('change', function() {
                if (this.value === '25') {
                    studyDuration = 25 * 60;
                    breakDuration = 5 * 60;
                    customMinutesInput.disabled = true;
                } else if (this.value === '50') {
                    studyDuration = 50 * 60;
                    breakDuration = 10 * 60;
                    customMinutesInput.disabled = true;
                } else if (this.value === 'custom') {
                    customMinutesInput.disabled = false;
                    studyDuration = (customMinutesInput.value || 25) * 60;
                    breakDuration = Math.floor(studyDuration / 5);
                }
                
                if (!isRunning) {
                    isStudyMode = true;
                    timeLeft = studyDuration;
                    timerModeLabel.textContent = "Study";
                    updateTimerDisplay();
                    
                    // Reset animation state
                    statusMessage.textContent = "Ready to operate";
                    doctor.classList.remove('operating');
                    patient.classList.remove('alive', 'dying', 'dead');
                }
            });
        });
        
        // Custom minutes input
        customMinutesInput.addEventListener('input', function() {
            if (document.getElementById('custom-time').checked) {
                studyDuration = (this.value || 25) * 60;
                breakDuration = Math.floor(studyDuration / 5);
                
                if (!isRunning) {
                    isStudyMode = true;
                    timeLeft = studyDuration;
                    updateTimerDisplay();
                }
            }
        });
        
        // Event listeners
        startBtn.addEventListener('click', startTimer);
        pauseBtn.addEventListener('click', pauseTimer);
        resetBtn.addEventListener('click', resetTimer);
        
        // Initialize timer display
        updateTimerDisplay();
        timerCircleFg.style.strokeDasharray = 2 * Math.PI * 45;
        
        // Calendar functionality
        const currentMonthYear = document.getElementById('current-month-year');
        const calendarDays = document.getElementById('calendar-days');
        const prevMonthBtn = document.getElementById('prev-month');
        const nextMonthBtn = document.getElementById('next-month');
        const selectedDateEventsTitle = document.getElementById('selected-date-events-title');
        const selectedDateEvents = document.getElementById('selected-date-events');
        
        let currentDate = new Date();
        let currentMonth = currentDate.getMonth();
        let currentYear = currentDate.getFullYear();
        
        // Render calendar
        function renderCalendar() {
            // Update month/year display
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                              'July', 'August', 'September', 'October', 'November', 'December'];
            currentMonthYear.textContent = `${monthNames[currentMonth]} ${currentYear}`;
            
            // Get first day of month and total days in month
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            
            // Clear previous days
            calendarDays.innerHTML = '';
            
            // Add empty cells for days before the first day of the month
            for (let i = 0; i < firstDay; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.className = 'calendar-day empty';
                calendarDays.appendChild(emptyCell);
            }
            
            // Add cells for each day of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const dayCell = document.createElement('div');
                dayCell.className = 'calendar-day';
                dayCell.textContent = day;
                
                // Highlight current day
                if (day === currentDate.getDate() && currentMonth === currentDate.getMonth() && currentYear === currentDate.getFullYear()) {
                    dayCell.classList.add('today');
                }
                
                // Check for assignments due on this day
                const dateStr = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                const assignmentsOnDay = <?php echo json_encode($assignments); ?>.filter(a => a.due_date === dateStr);
                
                if (assignmentsOnDay.length > 0) {
                    const assignmentIndicator = document.createElement('div');
                    assignmentIndicator.className = 'assignment-indicator';
                    assignmentIndicator.title = `${assignmentsOnDay.length} assignment(s) due`;
                    dayCell.appendChild(assignmentIndicator);
                }
                
                // Add click event to show assignments for the day
                dayCell.addEventListener('click', () => showDayAssignments(day, assignmentsOnDay));
                
                calendarDays.appendChild(dayCell);
            }
        }
        
        // Show assignments for a specific day
        function showDayAssignments(day, assignments) {
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                              'July', 'August', 'September', 'October', 'November', 'December'];
            
            selectedDateEventsTitle.textContent = `Assignments for ${monthNames[currentMonth]} ${day}, ${currentYear}`;
            selectedDateEvents.innerHTML = '';
            
            if (assignments.length === 0) {
                selectedDateEvents.innerHTML = '<li>No assignments due on this day</li>';
            } else {
                assignments.forEach(assignment => {
                    const li = document.createElement('li');
                    li.className = assignment.submitted_at ? 'submitted' : 'pending';
                    li.innerHTML = `
                        <h4>${assignment.title} (${assignment.subject})</h4>
                        <p>${assignment.points} points</p>
                        ${assignment.submitted_at ? 
                            `<p class="status submitted">Submitted</p>` : 
                            `<p class="status pending">Pending</p>`}
                    `;
                    selectedDateEvents.appendChild(li);
                });
            }
        }
        
        // Change month
        function changeMonth(offset) {
            currentMonth += offset;
            
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            } else if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            
            renderCalendar();
        }
        
        // Event listeners for month navigation
        prevMonthBtn.addEventListener('click', () => changeMonth(-1));
        nextMonthBtn.addEventListener('click', () => changeMonth(1));
        
        // Initialize calendar
        renderCalendar();
        
        // Friends functionality
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
                    document.getElementById('friend-username').value = '';
                } else {
                    alert(data.message || 'Error sending friend request');
                }
            });
        });
        
        // Accept friend request
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
                        alert(data.message || 'Error accepting friend request');
                    }
                });
            });
        });
        
        // Decline friend request
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
                        alert(data.message || 'Error declining friend request');
                    }
                });
            });
        });
        
        // Remove friend
        document.querySelectorAll('.btn-remove').forEach(btn => {
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
        
        // Study with friend
        document.querySelectorAll('.btn-study').forEach(btn => {
            btn.addEventListener('click', function() {
                const friendId = this.getAttribute('data-friend-id');
                // This would be more complex in a real app with WebSockets
                alert(`In a real app, this would initiate a study session with your friend. Friend ID: ${friendId}`);
            });
        });
        
        // Co-Study functionality
        document.getElementById('create-session').addEventListener('click', function() {
            const sessionId = Math.random().toString(36).substring(2, 8).toUpperCase();
            
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
                    document.getElementById('current-session-id').textContent = sessionId;
                    document.getElementById('session-info').style.display = 'block';
                    document.getElementById('active-sessions').style.display = 'none';
                    
                    // Add yourself to participants list
                    const participantsList = document.getElementById('participants-list');
                    participantsList.innerHTML = `<li><?php echo htmlspecialchars($user['username']); ?> (You)</li>`;
                    
                    // In a real app, you'd use WebSockets to update participants in real-time
                } else {
                    alert(data.message || 'Error creating study session');
                }
            });
        });
        
        document.getElementById('join-session').addEventListener('click', function() {
            const sessionId = prompt('Enter the session ID you want to join:');
            
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
                        document.getElementById('current-session-id').textContent = sessionId;
                        document.getElementById('session-info').style.display = 'block';
                        document.getElementById('active-sessions').style.display = 'none';
                        
                        // In a real app, you'd use WebSockets to get the current participants
                        document.getElementById('participants-list').innerHTML = 
                            `<li>Loading participants...</li>`;
                    } else {
                        alert(data.message || 'Error joining study session');
                    }
                });
            }
        });
        
        document.getElementById('leave-session').addEventListener('click', function() {
            const sessionId = document.getElementById('current-session-id').textContent;
            
            fetch('leave_study_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ session_id: sessionId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('session-info').style.display = 'none';
                    document.getElementById('active-sessions').style.display = 'block';
                } else {
                    alert(data.message || 'Error leaving study session');
                }
            });
        });
        
        // Join study group from list
        document.querySelectorAll('.join-group').forEach(btn => {
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
                        document.getElementById('current-session-id').textContent = sessionId;
                        document.getElementById('session-info').style.display = 'block';
                        document.getElementById('active-sessions').style.display = 'none';
                        
                        // In a real app, you'd use WebSockets to get the current participants
                        document.getElementById('participants-list').innerHTML = 
                            `<li>Loading participants...</li>`;
                    } else {
                        alert(data.message || 'Error joining study session');
                    }
                });
            });
        });
    </script>
</body>
</html>


// Theme Toggle
const themeToggle = document.getElementById('theme-toggle');
const mobileThemeToggle = document.getElementById('mobile-theme-toggle');
const body = document.body;

// Check for saved theme preference or use preferred color scheme
const savedTheme = localStorage.getItem('theme') || 
                   (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

// Apply the saved theme
if (savedTheme === 'dark') {
    body.classList.add('dark-mode');
    themeToggle.textContent = 'Light Mode';
    mobileThemeToggle.textContent = 'Light Mode';
} else {
    body.classList.remove('dark-mode');
    themeToggle.textContent = 'Dark Mode';
    mobileThemeToggle.textContent = 'Dark Mode';
}

// Toggle theme function
function toggleTheme() {
    body.classList.toggle('dark-mode');
    const isDarkMode = body.classList.contains('dark-mode');
    
    if (isDarkMode) {
        themeToggle.textContent = 'Light Mode';
        mobileThemeToggle.textContent = 'Light Mode';
        localStorage.setItem('theme', 'dark');
    } else {
        themeToggle.textContent = 'Dark Mode';
        mobileThemeToggle.textContent = 'Dark Mode';
        localStorage.setItem('theme', 'light');
    }
}

// Event listeners for theme toggle
themeToggle.addEventListener('click', toggleTheme);
mobileThemeToggle.addEventListener('click', toggleTheme);

// Mobile menu toggle
const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
const mobileNavLinks = document.querySelector('.mobile-nav-links');

mobileMenuBtn.addEventListener('click', () => {
    mobileNavLinks.classList.toggle('show');
});

// Dynamic welcome message
const welcomeMessages = [
    "Welcome to Nebula Learn!",
    "Ready to learn?",
    "Let's get productive!",
    "Knowledge awaits!",
    "Your learning journey starts here"
];

const welcomeMessage = document.getElementById('welcome-message');
let currentMessageIndex = 0;

function changeWelcomeMessage() {
    welcomeMessage.style.opacity = 0;
    
    setTimeout(() => {
        currentMessageIndex = (currentMessageIndex + 1) % welcomeMessages.length;
        welcomeMessage.textContent = welcomeMessages[currentMessageIndex];
        welcomeMessage.style.opacity = 1;
    }, 500);
}

// Change message every 5 seconds
setInterval(changeWelcomeMessage, 5000);

// Background interaction
const background = document.querySelector('.background');

document.addEventListener('mousemove', (e) => {
    const x = e.clientX / window.innerWidth;
    const y = e.clientY / window.innerHeight;
    
    background.style.transform = `translate(${x * 20}px, ${y * 20}px)`;
});

// Dashboard link - check if user is logged in
const dashboardLink = document.getElementById('dashboard-link');
const mobileDashboardLink = document.getElementById('mobile-dashboard-link');

// Improved login status check
function checkLoginStatus() {
    fetch('check_session.php', {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.logged_in) {
            dashboardLink.href = "user_dashboard.php";
            mobileDashboardLink.href = "user_dashboard.php";
        } else {
            dashboardLink.href = "login.php";
            mobileDashboardLink.href = "login.php";
            dashboardLink.addEventListener('click', (e) => {
                e.preventDefault();
                alert("Please login first");
                window.location.href = "login.php";
            });
            mobileDashboardLink.addEventListener('click', (e) => {
                e.preventDefault();
                alert("Please login first");
                window.location.href = "login.php";
            });
        }
    })
    .catch(error => {
        console.error('Error checking login status:', error);
        // Fallback to default behavior
        dashboardLink.href = "login.php";
        mobileDashboardLink.href = "login.php";
    });
}

// Run check on page load
document.addEventListener('DOMContentLoaded', checkLoginStatus);

// Floating elements animation enhancement
const floatingElements = document.querySelectorAll('.floating-element');

floatingElements.forEach(el => {
    // Randomize initial position and animation
    const randomX = Math.random() * 20 - 10;
    const randomY = Math.random() * 20 - 10;
    const randomDelay = Math.random() * 3;
    const randomDuration = 5 + Math.random() * 5;
    
    el.style.setProperty('--random-x', `${randomX}px`);
    el.style.setProperty('--random-y', `${randomY}px`);
    el.style.animationDelay = `${randomDelay}s`;
    el.style.animationDuration = `${randomDuration}s`;
});

// Add a style tag for the custom properties
const styleTag = document.createElement('style');
styleTag.textContent = `
    @keyframes float {
        0%, 100% {
            transform: translate(0, 0) rotate(0deg);
        }
        50% {
            transform: translate(var(--random-x), var(--random-y)) rotate(5deg);
        }
    }
`;
document.head.appendChild(styleTag);

// Enhanced grade submission function
async function submitGrade(submissionData) {
    try {
        const response = await fetch('grade_submission.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                submission_id: submissionData.submissionId,
                grade: submissionData.grade,
                assignment_id: submissionData.assignmentId,
                student_id: submissionData.studentId
            }),
            credentials: 'same-origin'
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to submit grade');
        }

        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Grade submission failed');
        }

        return data;
    } catch (error) {
        console.error('Grade submission error:', error);
        alert(`Error submitting grade: ${error.message}`);
        throw error;
    }
}

// Example usage:
// submitGrade({
//     submissionId: 1,    // The student_assignment ID
//     grade: 85,          // The grade to assign (0-100)
//     assignmentId: 3,     // The assignment ID
//     studentId: 1        // The student ID
// }).then(response => {
//     console.log('Grade submitted:', response);
//     // Update UI here
// });
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
    "Welcome to Nebula learn",
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

// Check login status (simplified - in a real app, you'd check with the server)
function checkLoginStatus() {
    // This is a placeholder - in a real app, you'd make an AJAX request to check session
    const isLoggedIn = document.cookie.includes('logged_in=true'); // Simplified
    
    if (isLoggedIn) {
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
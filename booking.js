
document.addEventListener('DOMContentLoaded', function() {
    checkLoginStatus();
    displayUserMenu();
});


function openMenu() {
    document.getElementById("sideMenu").style.right = "0";
    console.log("Menu opened"); 
}

function closeMenu() {
    document.getElementById("sideMenu").style.right = "-250px";
}


function toggleUserMenu() {
    document.getElementById('userDropdown').classList.toggle('show');
}

function toggleUserMenuSide() {
    document.getElementById('userDropdownSide')?.classList.toggle('show');
}

function displayUserMenu() {
    const loginLinks = document.querySelectorAll('#loginLink, #loginLinkSide');
    loginLinks.forEach(link => {
        link.style.display = 'block';
        link.style.background = '#007bff';
    });
}


function checkLoginStatus() {
    fetch('check_login.php')
        .then(response => response.json())
        .then(data => {
            console.log('Login status:', data);
            if (data.logged_in) {
                document.getElementById('displayName').textContent = data.user_name;
                document.getElementById('displayNameSide').textContent = data.user_name;
                document.querySelectorAll('#loginLink, #loginLinkSide').forEach(link => {
                    link.style.display = 'none';
                });
                document.querySelectorAll('#logoutBtn, #logoutBtnSide').forEach(btn => {
                    btn.style.display = 'block';
                });
            }
        })
        .catch(error => console.error('Login check failed:', error));
}

function logoutUser() {
    fetch('logout.php', { method: 'POST' })
        .then(() => location.reload());
}

let selectedCourt = '';
let selectedTime = '';
let selectedDate = '';

function selectCourt(court) {
    selectedCourt = court;
    document.querySelectorAll('.court-card').forEach(card => card.classList.remove('selected'));
    event.target.closest('.court-card').classList.add('selected');
    showScreen('screen2');
    updateDetails();
}

function selectTime(time, button) {
    selectedTime = time;
    document.querySelectorAll('.time-btn').forEach(btn => btn.classList.remove('selected'));
    button.classList.add('selected');
    updateDetails();
}

function updateDetails() {
    selectedDate = document.getElementById('bookingDate').value;
    document.getElementById('detailCourt').textContent = `Court: ${selectedCourt || '-'}`;
    document.getElementById('detailDate').textContent = `Date: ${selectedDate || '-'}`;
    document.getElementById('detailTime').textContent = `Time: ${selectedTime || '-'}`;
}

function showScreen(screenId) {
    document.querySelectorAll('.screen').forEach(screen => screen.classList.remove('active'));
    document.getElementById(screenId).classList.add('active');
}

function confirmBooking() {
    if (!selectedCourt || !selectedDate || !selectedTime) {
        alert('⚠️ Please select Court, Date, and Time!');
        return;
    }
    
    fetch('save_booking.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ court: selectedCourt, date: selectedDate, time: selectedTime })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showScreen('screen3');
            alert('🎉 Booking confirmed!');
        } else {
            alert('❌ ' + (data.error || 'Booking failed'));
        }
    })
    .catch(() => {
        showScreen('screen3'); // Offline mode
        alert('🎉 Booking confirmed! (Demo mode)');
    });
}
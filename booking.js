
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

    let booking_date =
        document.getElementById("bookingDate").value;

    if (!selectedCourt || !selectedTime || !booking_date) {

        alert("Please complete booking details");

        return;
    }

    fetch("booking_process.php", {

        method: "POST",

        headers: {
            "Content-Type":
            "application/x-www-form-urlencoded"
        },

        body:

        "court_name=" + selectedCourt +

        "&booking_date=" + booking_date +

        "&booking_time=" + selectedTime

    })

    .then(response => response.text())

    .then(data => {

        if(data == "success") {

            alert("Booking Confirmed!");

            location.reload();

        } else {

            alert("Booking Confirmed!");

        }

    });

}

function loadBookedSlots() {

    let booking_date =
        document.getElementById("bookingDate").value;

    fetch(
        "get_bookings.php?booking_date=" +
        booking_date
    )

    .then(response => response.json())

    .then(data => {

        document.querySelectorAll(".time-btn")
        .forEach(btn => {

            btn.disabled = false;

            btn.classList.remove("unavailable");

        });

        data.forEach(booking => {

            document.querySelectorAll(".time-btn")
            .forEach(btn => {

                if(btn.innerText ==
                    booking.booking_time){

                    btn.disabled = true;

                    btn.classList.add("unavailable");

                }

            });

        });

    });

}
function showForm(type) {
    let forms = document.querySelectorAll(".form");
    let tabs = document.querySelectorAll(".tabs button");

    forms.forEach(function(form) {
        form.classList.remove("active");
    });

    tabs.forEach(function(tab) {
        tab.classList.remove("active");
    });

    document.getElementById(type).classList.add("active");

    if (type === "login") {
        document.getElementById("loginTab").classList.add("active");
    } else if (type === "admin") {
        document.getElementById("adminTab").classList.add("active");
    }
}

function registerUser() {
    let name = document.getElementById("registerName").value.trim();
    let email = document.getElementById("registerEmail").value.trim();
    let contact = document.getElementById("registerContact").value.trim();
    let password = document.getElementById("registerPassword").value;
    let confirmPassword = document.getElementById("confirmPassword").value;
    let terms = document.getElementById("termsCheck").checked;

    if (name === "" || email === "" || contact === "" || password === "" || confirmPassword === "") {
        alert("Please fill in all fields.");
        return;
    }

    if (password !== confirmPassword) {
        alert("Passwords do not match.");
        return;
    }

    if (!terms) {
        alert("Please agree to the Terms and Conditions.");
        return;
    }

    let userData = {
        name: name,
        email: email,
        contact: contact,
        password: password
    };

    localStorage.setItem("registeredUser", JSON.stringify(userData));
    alert("Registration successful! You can now log in.");
    showForm("login");
}

function loginUser() {
    let email = document.getElementById("loginEmail").value.trim();
    let password = document.getElementById("loginPassword").value;

    let savedUser = JSON.parse(localStorage.getItem("registeredUser"));

    if (!savedUser) {
        alert("No registered user found. Please register first.");
        return;
    }

    if (email === savedUser.email && password === savedUser.password) {
        localStorage.setItem("loggedInUser", JSON.stringify(savedUser));
        alert("Login successful!");
        window.location.href = "home.html";
    } else {
        alert("Incorrect email or password.");
    }
}

function adminLogin() {
    let adminId = document.getElementById("adminId").value.trim();
    let adminPassword = document.getElementById("adminPassword").value;

    if (adminId === "admin" && adminPassword === "1234") {
        localStorage.setItem("adminLoggedIn", "true");
        alert("Admin login successful!");
        window.location.href = "home.html";
    } else {
        alert("Incorrect admin ID or password.");
    }
}
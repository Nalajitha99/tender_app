<?php
session_start();
session_unset();
session_destroy();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (isset($_GET['expired']) && $_GET['expired'] == 'true') {
    echo "<script>alert('Session expired. Please log in again.');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Powernet Tenders | Login</title>
<link rel="icon" href="images/logo.ico">

<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(
        rgba(0,0,0,0.55),
        rgba(0,0,0,0.55)
    ),
    url('images/draw1.png') center/cover no-repeat;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Center content */
.login-wrapper {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Glass card */
.login-card {
    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(12px);
    border-radius: 18px;
    padding: 45px 35px;
    max-width: 420px;
    width: 100%;
    box-shadow: 0 20px 45px rgba(0,0,0,0.6);
    color: #fff;
}

.login-card h2 {
    font-weight: 600;
    letter-spacing: 1px;
}

/* Inputs */
.form-control {
    background: rgba(255,255,255,0.18);
    border: 1px solid rgba(255,255,255,0.35);
    color: #fff;
    padding: 12px;
}

.form-control:focus {
    background: rgba(255,255,255,0.25);
    border-color: #ffb703;
    box-shadow: none;
    color: #fff;
}

.form-control::placeholder {
    color: rgba(255,255,255,0.7);
}

.form-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

/* Button */
.btn-login {
    background: linear-gradient(135deg, #ffb703, #fb8500);
    color: #000;
    border: none;
    padding: 12px;
    width: 100%;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(255,183,3,0.6);
}

/* Links */
.forgot-link {
    color: #ffb703;
    text-decoration: none;
    font-size: 0.9rem;
}

.forgot-link:hover {
    text-decoration: underline;
}

/* Footer */
footer {
    background: #1f2d3d;
    color: #fff;
    padding: 15px 30px;
    font-size: 0.9rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>
</head>

<body>

<div class="login-wrapper">
    <div class="login-card">
        <h2 class="text-center mb-4">TenderDesk</h2>

        <form action="loginDone.php" method="POST">
            <div class="mb-3">
                <label class="form-label" for="uname">User Name</label>
                <input type="text" id="uname" name="uname" class="form-control" placeholder="Enter user name" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="pword">Password</label>
                <input type="password" id="pword" name="pword" class="form-control" placeholder="Enter password" required>
            </div>

            <div class="d-flex justify-content-end mb-4">
                <a href="forgotPassword/forgotPassword.php" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>
</div>

<footer>
    <div>© 2026 Powernet (Pvt) Ltd.<br>All rights reserved.</div>
    <div id="footer-datetime"></div>
</footer>

<!-- Footer Date/Time (UNCHANGED LOGIC) -->
<script>
function getFormattedDate() {
    const date = new Date();
    const day = date.getDate();
    const month = date.toLocaleString('default', { month: 'long' });
    const year = date.getFullYear();
    const suffix = (d) => {
        if (d > 3 && d < 21) return 'th';
        return ['th','st','nd','rd'][d % 10] || 'th';
    };
    return `${day}${suffix(day)} ${month} ${year}`;
}

function getFormattedTime() {
    return new Date().toLocaleTimeString();
}

function updateFooterDateTime() {
    document.getElementById('footer-datetime').innerHTML =
        `${getFormattedDate()}<br>${getFormattedTime()}`;
}

updateFooterDateTime();
setInterval(updateFooterDateTime, 1000);
</script>

<!-- Cache prevention (UNCHANGED) -->
<script>
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
window.addEventListener('pageshow', function (event) {
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        window.location.reload();
    }
});
</script>

</body>
</html>

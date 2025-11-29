<?php

session_start();
session_unset();
session_destroy();

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); 

if (isset($_GET['expired']) && $_GET['expired'] == 'true') {
    echo "<script>alert('Session expired. Please log in again.');</script>";
}


?>


<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Powernet Tenders</title>
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='css/login.css'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    <style>
      body {
            background-image: url('images/draw1.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .vh-100 {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Floating login card */
        .login-card {
            background: rgba(0, 0, 0, 0.6); /* semi-transparent black */
            backdrop-filter: blur(10px); /* glass/3D blur effect */
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6); /* soft shadow */
            border-radius: 15px;
            padding: 40px;
            max-width: 400px;
            width: 100%;
            color: #fff;
        }

        /* Transparent input fields */
        .form-control {
            background-color: rgba(255, 255, 255, 0.2); /* transparent input */
            border: 1px solid rgba(255,255,255,0.5);
            color: #fff;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-label {
            color: #fff;
        }

        /* Login button hover effect */
        .button {
            background-color: #320303;
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .button:hover {
            background-color: white;
            color: black;
            cursor: pointer;
        }

    </style>

</head>
<body>
    <section class="vh-100">
  <div class="login-card">
    <h2 class="text-center fw-bold mb-4">Log In</h2>
    <form action="loginDone.php" method="POST">
      <div class="form-outline mb-3">
        <input type="text" id="uname" name="uname" class="form-control" placeholder="Enter User Name" />
        <label class="form-label" for="uname">User Name</label>
      </div>
      <div class="form-outline mb-3">
        <input type="password" id="pword" name="pword" class="form-control" placeholder="Enter Password" />
        <label class="form-label" for="pword">Password</label>
      </div>
      <div class="d-flex justify-content-between mb-4">
        <a href="forgotPassword/forgotPassword.php" class="text-white">Forgot password?</a>
      </div>
      <div class="text-center">
        <button type="submit" class="button">Login</button>
      </div>
    </form>
  </div>

      </section>
            
              <
       <footer style="background-color:#320303" class="mt-auto py-4 px-4 px-xl-5 text-white d-flex justify-content-between align-items-center">
    <div> © 2026 Powernet (pvt) Ltd.<br> All rights reserved.</div>
    <div id="footer-datetime"></div>
</footer>


<script>
  function getFormattedDate() {
    const date = new Date();
    const day = date.getDate();
    const month = date.toLocaleString('default', { month: 'long' });
    const year = date.getFullYear();

    // Get day suffix (st, nd, rd, th)
    const suffix = (d) => {
      if (d > 3 && d < 21) return 'th';
      switch (d % 10) {
        case 1: return 'st';
        case 2: return 'nd';
        case 3: return 'rd';
        default: return 'th';
      }
    };

    return `${day}${suffix(day)} ${month} ${year}`;
  }

  function getFormattedTime() {
    const date = new Date();
    return date.toLocaleTimeString(); // Customize if needed
  }

  function updateFooterDateTime() {
    document.getElementById('footer-datetime').innerHTML =
      `${getFormattedDate()}<br>${getFormattedTime()}`;
  }

  updateFooterDateTime();
  setInterval(updateFooterDateTime, 1000);
</script>

      </section>
    
</body>

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
</html>
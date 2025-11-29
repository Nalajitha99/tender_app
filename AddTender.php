<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); 

$timeout_duration = 600;

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();     
    session_destroy();   
    header("Location: index.php?expired=true");
    exit();
}

include "db.php";


$userQuery = "SELECT id, uname, department FROM users";
$userResult = mysqli_query($conn, $userQuery);
?> 

<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Powernet Tenders</title>
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='css/AddTender.css'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<section class="vh-100 gradient-custom">
    <div class="container py-5 h-100">
        <div class="row justify-content-center align-items-center h-100">
            <div class="col-12 col-lg-10 col-xl-10">

                <div class="card shadow-2-strong card-registration" style="border-radius: 15px;">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="mb-4 pb-2 pb-md-0 mb-md-5">New Tender Form</h3>
                        
                        <form name="salesForm" id="salesForm" action="AddTenderDone.php" method="post">

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <input type="text" id="Organization" name="organization" class="form-control form-control" required />
                                    <label class="form-label" for="organization">Organization</label>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <input type="text" id="tenderNo" name="tenderNo" class="form-control form-control" required />
                                    <label class="form-label" for="tenderNo">Tender No</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4 pb-2">
                                    <textarea class="form-control form-control" name="tenderTitle" rows="2" required></textarea>
                                    <label class="form-label" for="tendertitle">Tender Title</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4 pb-2">
                                    <textarea class="form-control form-control" name="description" rows="3" required></textarea>
                                    <label class="form-label" for="description">Tender Description</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <input type="text" id="location" name="location" class="form-control form-control" required />
                                    <label class="form-label" for="location">Location</label>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <input type="date" id="date" name="closingDate" class="form-control form-control" required />
                                    <label class="form-label" for="date">Tender Closing Date</label>
                                </div>
                            </div>

                            <!-- Bid Security Section -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label d-block">Bid Security Required?</label>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="bidYes" onclick="handleBidSecurity('yes')">
                                        <label class="form-check-label" for="bidYes">Yes</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="bidNo" onclick="handleBidSecurity('no')">
                                        <label class="form-check-label" for="bidNo">No</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Hidden field to submit YES/NO -->
                            <input type="hidden" id="bidSecurity" name="bidSecurity" value="">

                            <!-- Hidden fields when YES is selected -->
                            <div id="bidSecurityFields" style="display: none;">
                                <div class="row">

                                    <div class="col-md-6 mb-4">
                                        <input type="text" id="bidAmount" name="bidAmount" class="form-control form-control" />
                                        <label class="form-label" for="bidAmount">Bid Security Amount</label>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <input type="date" id="bidValidity" name="bidValidity" class="form-control form-control" />
                                        <label class="form-label" for="bidValidity">Bid Validity Date</label>
                                    </div>

                                </div>
                            </div>

                            <br>

                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <select class="select form-control" id="recievedFrom" name="recievedFrom" require>
                                        <option value="1" selected></option>
                                        <option value="Post">Post</option>
                                        <option value="Email">Email</option>
                                        <option value="News Paper">News Paper</option>
                                        <option value="Web Site">Web Site</option>
                                    </select>
                                    
                                    <label class="form-label select-label">Tender Recieved From?</label>
                                </div>
                                <div class="col-md-4 mb-4">
                                        <input type="date" id="recievedDate" name="recievedDate" class="form-control form-control" />
                                        <label class="form-label" for="recievedDate">Tender Recieved Date</label>
                                </div>
                                <div class="col-md-4 mb-4">
                                        <input type="time" id="recievedTime" name="recievedTime" class="form-control form-control" />
                                        <label class="form-label" for="recievedTime">Tender Recieved Time</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <select class="select form-control" id="assignedBy" name="assignedBy" required>
                                        <option value="" disabled selected></option>

                                        <?php
                                        if(mysqli_num_rows($userResult) > 0){
                                            while($row = mysqli_fetch_assoc($userResult)){
                                                if (strtolower($row['uname']) === "admin" || ($user['uname']) === "Prasadini") {
                                                    continue;
                                                }

                                                echo "<option value='".$row['uname']."'>".$row['uname']." - ".$row['department']."</option>";
                                            }
                                        } else {
                                            echo "<option value='' disabled>No users found</option>";
                                        }
                                        ?>
                                    </select>
                                    
                                    <label class="form-label select-label">Tender Assigned Person</label>
                                </div>
                                <div class="col-md-4 mb-4">
                                        <input type="date" id="assignedDate" name="assignedDate" class="form-control form-control" />
                                        <label class="form-label" for="assignedDate">Tender Assigned Date</label>
                                </div>
                                <div class="col-md-4 mb-4">
                                        <input type="time" id="assignedTime" name="assignedTime" class="form-control form-control" />
                                        <label class="form-label" for="assignedTime">Tender Assigned Time</label>
                                </div>
                            </div>

                            <div class="mt-4 pt-2 d-flex" style="gap: 10px; width: fit-content;">
                                <button class="btn btn-success" id="sub" name="sub" style="width: 120px;">Submit</button>
                                <input class="btn btn-outline-danger" type="reset" value="Clear" style="width: 120px;">
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>


<script>
    function handleBidSecurity(option) {
        const yes = document.getElementById("bidYes");
        const no = document.getElementById("bidNo");
        const fields = document.getElementById("bidSecurityFields");
        const bidSecInput = document.getElementById("bidSecurity");

        if (option === "yes") {
            yes.checked = true;
            no.checked = false;
            fields.style.display = "block";

            bidSecInput.value = "Yes";  

            document.getElementById("bidAmount").required = true;
            document.getElementById("bidValidity").required = true;

        } else {
            no.checked = true;
            yes.checked = false;
            fields.style.display = "none";

            bidSecInput.value = "No";   

            document.getElementById("bidAmount").required = false;
            document.getElementById("bidValidity").required = false;
        }
    }
</script>

</body>
</html>

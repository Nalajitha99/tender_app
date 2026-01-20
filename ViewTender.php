<?php

session_start();

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); 

$timeout_duration = 7200;

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

$_SESSION['LAST_ACTIVITY'] = time(); 
$userName = $_SESSION['username'];
// --------------------------------------------------
// DB CONNECTION
// --------------------------------------------------
include "db.php";


$userQuery = "SELECT id, uname, department FROM users";
$userResult = mysqli_query($conn, $userQuery);

// --------------------------------------------------
// 1. LOAD TENDER DETAILS WHEN ROW IS CLICKED
// --------------------------------------------------
if (!isset($_GET['id'])) {
    echo "<script>alert('No tender selected'); window.location='index.php';</script>";
    exit();
}

$id = $_GET['id'];
$sql = "SELECT * FROM tenders WHERE id = '$id'";
$result = $conn->query($sql);

if ($result->num_rows != 1) {
    echo "<script>alert('Tender not found'); window.location='index.php';</script>";
    exit();
}

$tender = $result->fetch_assoc();

$currentAssignedBy = $tender['assignedBy'];


// --------------------------------------------------
// 2. UPDATE WHEN SUBMITTED
// --------------------------------------------------
if (isset($_POST['update'])) {

    $organization   = $_POST['organization'];
    $tenderNo       = $_POST['tenderNo'];
    $tenderTitle    = $_POST['tenderTitle'];
    $description    = $_POST['description'];
    $location       = $_POST['location'];
    $closingDate    = $_POST['closingDate'];
    $bidSecurity    = $_POST['bidSecurity']; 
    $bidAmount      = $_POST['bidAmount'];
    $bidValidity    = $_POST['bidValidity'];
    $recievedFrom   = $_POST['recievedFrom'];
    $recievedDate   = $_POST['recievedDate'];
    $recievedTime   = $_POST['recievedTime'];
    $assignedBy     = $_POST['assignedBy'];
    $assignedDate   = $_POST['assignedDate'];
    $assignedTime   = $_POST['assignedTime'];
    $status         = $_POST['status'];
    $reason         = $_POST['reason'];
    $submittedDate  = $_POST['submittedDate'];
    $handoverby     = $_POST['handoverby'];
    $l1supplier     = $_POST['l1supplier'];
    $l1price        = $_POST['l1price'];
    $ourprice       = $_POST['ourPrice'];
    $readings       = $_POST['readings'];
    $awardStatus    = $_POST['awardStatus'];
    $approveStatus  = $tender['approveStatus'];
    $doubleCheckedBy = $_POST['doubleCheckedBy'];





    $sqlUpdate = "UPDATE tenders SET
            organization  = '$organization',
            tenderNo      = '$tenderNo',
            tenderTitle   = '$tenderTitle',
            description   = '$description',
            location      = '$location',
            closingDate   = '$closingDate',
            bidSecurity   = '$bidSecurity',
            bidAmount     = '$bidAmount',
            bidValidity   = '$bidValidity',
            recievedFrom  = '$recievedFrom',
            recievedDate  = '$recievedDate',
            recievedTime  = '$recievedTime',
            assignedBy    = '$assignedBy',
            assignedDate  = '$assignedDate',
            assignedTime  = '$assignedTime',
            status       = '$status',
            reason       = '$reason',
            submittedDate = '$submittedDate',
            handoverby    = '$handoverby',
            l1supplier    = '$l1supplier',
            l1price       = '$l1price',
            ourprice      = '$ourprice',
            readings      = '$readings',
            awardStatus   = '$awardStatus',
            approveStatus = '$approveStatus',
            doubleCheckedBy = '$doubleCheckedBy'
        WHERE id = '$id'";

    if ($conn->query($sqlUpdate)) {
        echo "<script>alert('Tender updated successfully!'); window.location='OngoingTenderTable.php';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to update tender');</script>";
    }
}

// Double Checked By (system controlled)
if ($userName != "Prasadini" && !empty($_POST['doubleCheckedBy'])) {
    $doubleCheckedBy = $_POST['doubleCheckedBy'];
} else {
    $doubleCheckedBy = $tender['doubleCheckedBy'];
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Powernet Tenders</title>
    <link rel="icon" href="images/logo.ico" type="image/x-icon">
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<section class="vh-100 gradient-custom">
    <div class="container py-5 h-100">
        <div class="row justify-content-center align-items-center h-100">
            <div class="col-12 col-lg-10 col-xl-10">

                <div class="card shadow-2-strong card-registration" style="border-radius: 15px;">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="mb-4 pb-2 pb-md-0 mb-md-3"><?php echo $tender['organization']; ?> Ongoing Tender</h3><hr><br>

                        <form method="post">

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <input type="text" name="organization" class="form-control form-control" 
                                           value="<?php echo $tender['organization']; ?>" required readonly/>
                                    <label>Organization</label>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <input type="text" name="tenderNo" class="form-control form-control" 
                                           value="<?php echo $tender['tenderNo']; ?>" required readonly/>
                                    <label>Tender No</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4 pb-2">
                                    <textarea class="form-control form-control" name="tenderTitle" rows="2" required readonly><?php echo $tender['tenderTitle']; ?></textarea>
                                    <label>Tender Title</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4 pb-2">
                                    <textarea class="form-control form-control" name="description" rows="3" required readonly><?php echo $tender['description']; ?></textarea>
                                    <label>Tender Description</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <input type="text" name="location" class="form-control form-control" 
                                           value="<?php echo $tender['location']; ?>" required readonly/>
                                    <label>Location</label>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <input type="date" name="closingDate" class="form-control form-control" 
                                           value="<?php echo $tender['closingDate']; ?>" required />
                                    <label>Tender Closing Date</label>
                                </div>
                            </div>

                            <!-- Bid Security -->
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <select name="bidSecurity" id="bidSecurity" class="form-select" onchange="showBidSecurity()" readonly>
                                        <option value="">-- Select --</option>
                                        <option <?php if($tender['bidSecurity']=="Yes") echo 'selected'; ?>>Yes</option>
                                        <option <?php if($tender['bidSecurity']=="No") echo 'selected'; ?>>No</option>
                                    </select>
                                    <label>Bid Security</label>
                                </div>
                            </div>

                            <!-- Bid Security Fields -->
                            <div id="bidSecurityFields" style="<?php echo ($tender['bidSecurity']=="Yes" ? '' : 'display:none'); ?>">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label>Bid Security Amount</label>
                                        <input type="text" id="bidAmount" name="bidAmount" class="form-control" value="<?php echo $tender['bidAmount']; ?>">
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label>Bid Validity Date</label>
                                        <input type="date" id="bidValidity" name="bidValidity" class="form-control" value="<?php echo $tender['bidValidity']; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <select class="form-control" name="recievedFrom" readonly>
                                        <option <?php if($tender['recievedFrom']=="Post") echo 'selected'; ?>>Post</option>
                                        <option <?php if($tender['recievedFrom']=="Email") echo 'selected'; ?>>Email</option>
                                        <option <?php if($tender['recievedFrom']=="News Paper") echo 'selected'; ?>>News Paper</option>
                                        <option <?php if($tender['recievedFrom']=="Web Site") echo 'selected'; ?>>Web Site</option>
                                    </select>
                                    <label>Tender Received From?</label>
                                </div>

                                <div class="col-md-4 mb-4">
                                    <input type="date" name="recievedDate" class="form-control form-control" 
                                           value="<?php echo $tender['recievedDate']; ?>" readonly/>
                                    <label>Received Date</label>
                                </div>
                                <div class="col-md-4 mb-4">
                                        <input type="text" id="recievedTime" name="recievedTime" class="form-control form-control" value="<?php echo date("g:i A", strtotime($tender['recievedTime'])); ?>" readonly/>
                                        <label class="form-label" for="recievedTime">Tender Recieved Time</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-4">
                                     <select class="select form-control" id="assignedBy" name="assignedBy" required readonly>
                                        <!-- <option value="" disabled>Select Assigned Person</option> -->

                                    <?php
                                    if(mysqli_num_rows($userResult) > 0){
                                        while($row = mysqli_fetch_assoc($userResult)){

                                            // Check if user equals the existing assigned user
                                            $selected = ($row['uname'] == $currentAssignedBy) ? "selected" : "";

                                            echo "<option value='".$row['uname']."' $selected>"
                                                .$row['uname']." - ".$row['department'].
                                                "</option>";
                                        }
                                    } else {
                                        echo "<option value='' disabled>No users found</option>";
                                    }
                                    ?>
                                    </select>

                                    <label>Assigned Person</label>
                                </div>

                                <div class="col-md-4 mb-4">
                                    <input type="date" name="assignedDate" class="form-control form-control" 
                                           value="<?php echo $tender['assignedDate']; ?>"readonly />
                                    <label>Tender Assigned Date</label>
                                </div>

                                <div class="col-md-4 mb-4">
                                        <input type="text" id="assignedTime" name="assignedTime" class="form-control form-control" value="<?php echo date("g:i A", strtotime($tender['assignedTime'])); ?>" readonly/>
                                        <label class="form-label" for="assignedTime">Tender Assigned Time</label>
                                </div>
                                
                            </div>

                            <!-- APPROVED DATE + TIME (Only visible if ApprovedStatus = yes) -->
                            <div class="row" id="approvedFields">

                                <div class="col-md-4 mb-4">
                                    <input type="text" id="approveStatus" name="approveStatus" class="form-control form-control" 
                                        value="<?php echo $tender['approveStatus']; ?>" readonly />
                                    <label class="form-label">Acknowledged By Assignee</label>
                                </div>

                                <div class="col-md-4 mb-4" style="<?php echo ($tender['approveStatus'] == 'Accepted' ? '' : 'display:none'); ?>">
                                    <input type="date" name="approvedDate" class="form-control form-control" 
                                        value="<?php echo $tender['approvedDate']; ?>" readonly />
                                    <label>Assignee Confirmation Date</label>
                                </div>

                                <div class="col-md-4 mb-4" style="<?php echo ($tender['approveStatus'] == 'Accepted' ? '' : 'display:none'); ?>">
                                    <input type="text" id="approvedTime" name="approvedTime" class="form-control form-control" 
                                        value="<?php echo date("g:i A", strtotime($tender['approvedTime'])); ?>" readonly/>
                                    <label class="form-label">Assignee Confirmation Time</label>
                                </div>

                            </div>

                            <div class="row">

                            
                            </div>

                            
                            <br><br><h3 class="mb-4 pb-2 pb-md-0 mb-md-3">Tender Status</h3><hr><br>

                            <div class="row">
                            <div class="col-md-4 mb-4">
                                <select class="form-control" name="status" id="status" onchange="toggleStatusFields()">
                                    <option value="" <?php if($tender['status']=="") echo 'selected'; ?>></option>
                                    <option value="Completed" <?php if($tender['status']=="Completed") echo 'selected'; ?>>Submitted</option>
                                    <option value="Uncompleted" <?php if($tender['status']=="Uncompleted") echo 'selected'; ?>>Not Submitted</option>
                                    <option value="Ongoing" <?php if($tender['status']=="Ongoing") echo 'selected'; ?>>Ongoing</option>
                                </select>
                                <label>Status</label>
                            </div>
                        </div>

                        <!-- Reason - visible only if Uncompleted -->
                        <div class="row" id="reasonField">
                            <div class="col-md-8 mb-4">
                                <input type="text" name="reason" class="form-control form-control" 
                                    value="<?php echo $tender['reason']; ?>" />
                                <label>Reason</label>
                            </div>
                        </div>

                        <!-- Submitted Date & Hand Over - visible only if Completed -->
                        <div id="completedFields">
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <input type="date" name="submittedDate" class="form-control form-control" 
                                        value="<?php echo $tender['submittedDate']; ?>"/>
                                    <label>Submitted Date</label>
                                </div>

                                <div class="col-md-4 mb-4">
                                    <select name="handoverby" class="form-select" <?php echo $disabled; ?>>
                                        <option value="">-- Select Method --</option>

                                        <?php
                                        $handoverOptions = [
                                            "Hand Delivered",
                                            "Registered Post",
                                            "Speed Post",
                                            "Courier Service",
                                            "Email Submission"
                                        ];

                                        foreach ($handoverOptions as $opt) {
                                            $selected = ($tender['handoverby'] == $opt) ? "selected" : "";
                                            echo "<option value='$opt' $selected>$opt</option>";
                                        }
                                        ?>
                                    </select>

                                    <label>Hand Over Method</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-4 ms-auto">
                                    <label class="form-label">Double Checked By</label>

                                    <?php if ($userName != "Prasadini") { ?>
                                        <select name="doubleCheckedBy" class="form-select">
                                            <option value="">-- Select Person --</option>

                                            <?php
                                            $userResult2 = mysqli_query($conn, "SELECT uname, department FROM users");
                                            while ($row = mysqli_fetch_assoc($userResult2)) {
                                                $selected = ($tender['doubleCheckedBy'] === $row['uname']) ? "selected" : "";
                                                echo "<option value='{$row['uname']}' $selected>
                                                        {$row['uname']} - {$row['department']}
                                                    </option>";
                                            }
                                            ?>
                                        </select>
                                    <?php } ?>
                                </div>
                        </div>

                        <br><br><h3 class="mb-4 pb-2 pb-md-0 mb-md-3">Tender Readings</h3><hr><br>

                        
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <select class="form-control" name="readings" id="readings" onchange="toggleStatusFields()">
                                    <option value="" <?php if($tender['readings']=="") echo 'selected'; ?>></option>
                                    <option value="Collected" <?php if($tender['readings']=="Collected") echo 'selected'; ?>>Collected</option>
                                    <option value="NotCollected" <?php if($tender['readings']=="NotCollected") echo 'selected'; ?>>Not Collected</option>
                                </select>
                                <label>Readings</label>
                            </div>
                        </div>

                        <!-- Readings -->
                        <div class="row" id="l1SupplierField">
                            <div class="col-md-8 mb-4">
                                <input type="text" name="l1supplier" class="form-control form-control" 
                                    value="<?php echo $tender['l1supplier']; ?>" />
                                <label>L1 Supplier</label>
                            </div>
                        </div>

                        <!-- Readings -->
                        <div id="l1PriceFields">
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <input type="text" name="l1price" class="form-control form-control" 
                                        value="<?php echo $tender['l1price']; ?>"/>
                                    <label>L1 Price</label>
                                </div>

                                <div class="col-md-4 mb-4">
                                    <input type="text" name="ourPrice" class="form-control form-control" 
                                        value="<?php echo $tender['ourprice']; ?>"/>
                                    <label>Our Price</label>
                                </div>
                            </div>
                        </div>

                        <br><br><h3 class="mb-4 pb-2 pb-md-0 mb-md-3">Tender Awarding Status</h3><hr><br>

                        <div class="row">
                            <div class="col-md-4 mb-4">
                                

                                <label>
                                    <input type="radio" name="awardStatus" value="Awarded" 
                                        <?php if($tender['awardStatus']=="Awarded") echo 'checked'; ?> >
                                    Awarded
                                </label>
                            </div>

                            <div class="col-md-4 mb-4">

                                <label>
                                    <input type="radio" name="awardStatus" value="Pending"
                                        <?php if($tender['awardStatus']=="Pending") echo 'checked'; ?> >
                                    Pending
                                </label>
                            </div>

                            <div class="col-md-4 mb-4">

                                <label>
                                    <input type="radio" name="awardStatus" value="Not Awarded"
                                        <?php if($tender['awardStatus']=="Not Awarded") echo 'checked'; ?> >
                                    Not Awarded
                                </label>
                            </div>
                        </div>


                            <div class="mt-4 pt-2 d-flex" style="gap: 10px; width: fit-content;">
                                <?php if ($userName != "Prasadini" && $userName != "Wimal" && $userName != "Chanaka") { ?>
                                <button class="btn btn-primary" name="update" style="width: 120px;">Update</button>
                                <?php } ?>
                                <a href="OngoingTenderTable.php" class="btn btn-secondary" style="width: 120px;">Back</a>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
<script>

    function showBidSecurity() {
        let value = document.getElementById("bidSecurity").value;
        let block = document.getElementById("bidSecurityFields");

        if (value === "Yes") {
            block.style.display = "block";
        } else {
            block.style.display = "none";
            document.getElementById("bidAmount").value = "";
            document.getElementById("bidValidity").value = "";
        }
    }

            function toggleStatusFields() {
                const status = document.getElementById('status').value;

                document.getElementById('reasonField').style.display = 
                    (status === "Uncompleted") ? "block" : "none";

                document.getElementById('completedFields').style.display = 
                    (status === "Completed") ? "block" : "none";
            }

            // Run on page load
            toggleStatusFields();
</script>


</body>

</html>

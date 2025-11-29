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

$_SESSION['LAST_ACTIVITY'] = time(); 
// --------------------------------------------------
// DB CONNECTION
// --------------------------------------------------
include "db.php";

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
    $assignedBy     = $_POST['assignedBy'];
    $assignedDate   = $_POST['assignedDate'];
    $status         = $_POST['status'];
    $reason         = $_POST['reason'];
    $submittedDate  = $_POST['submittedDate'];
    $handoverby     = $_POST['handoverby'];
    $l1supplier     = $_POST['l1supplier'];
    $l1price        = $_POST['l1price'];
    $ourprice       = $_POST['ourPrice'];



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
            assignedBy    = '$assignedBy',
            assignedDate  = '$assignedDate',
            status       = '$status',
            reason       = '$reason',
            submittedDate = '$submittedDate',
            handoverby    = '$handoverby',
            l1supplier    = '$l1supplier',
            l1price       = '$l1price',
            ourprice      = '$ourprice'
        WHERE id = '$id'";

    if ($conn->query($sqlUpdate)) {
        echo "<script>alert('Tender updated successfully!'); window.location='SubmittedTenderTable.php';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to update tender');</script>";
    }
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
                        <h3 class="mb-4 pb-2 pb-md-0 mb-md-3">Not Submitted Tender</h3><hr><br>

                        <form method="post">

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <input type="text" name="organization" class="form-control form-control" 
                                           value="<?php echo $tender['organization']; ?>" disabled />
                                    <label>Organization</label>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <input type="text" name="tenderNo" class="form-control form-control" 
                                           value="<?php echo $tender['tenderNo']; ?>" disabled />
                                    <label>Tender No</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4 pb-2">
                                    <textarea class="form-control form-control" name="tenderTitle" rows="2" disabled><?php echo $tender['tenderTitle']; ?></textarea>
                                    <label>Tender Title</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4 pb-2">
                                    <textarea class="form-control form-control" name="description" rows="3" disabled><?php echo $tender['description']; ?></textarea>
                                    <label>Tender Description</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <input type="text" name="location" class="form-control form-control" 
                                           value="<?php echo $tender['location']; ?>" disabled />
                                    <label>Location</label>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <input type="date" name="closingDate" class="form-control form-control" 
                                           value="<?php echo $tender['closingDate']; ?>" disabled />
                                    <label>Tender Closing Date</label>
                                </div>
                            </div>

                            <!-- Bid Security -->
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <select name="bidSecurity" id="bidSecurity" class="form-select" onchange="showBidSecurity()" disabled>
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
                                        <input type="text" id="bidAmount" name="bidAmount" class="form-control" value="<?php echo $tender['bidAmount']; ?>" disabled>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label>Bid Validity Date</label>
                                        <input type="date" id="bidValidity" name="bidValidity" class="form-control" value="<?php echo $tender['bidValidity']; ?>" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <select class="form-control" name="recievedFrom" disabled>
                                        <option <?php if($tender['recievedFrom']=="Post") echo 'selected'; ?>>Post</option>
                                        <option <?php if($tender['recievedFrom']=="Email") echo 'selected'; ?>>Email</option>
                                        <option <?php if($tender['recievedFrom']=="News Paper") echo 'selected'; ?>>News Paper</option>
                                        <option <?php if($tender['recievedFrom']=="Web Site") echo 'selected'; ?>>Web Site</option>
                                    </select>
                                    <label>Tender Received From?</label>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <input type="date" name="recievedDate" class="form-control form-control" 
                                           value="<?php echo $tender['recievedDate']; ?>" disabled/>
                                    <label>Received Date</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <select class="form-control" name="assignedBy" disabled>
                                        <option <?php if($tender['assignedBy']=="Post") echo 'selected'; ?>>Post</option>
                                        <option <?php if($tender['assignedBy']=="Email") echo 'selected'; ?>>Email</option>
                                        <option <?php if($tender['assignedBy']=="News Paper") echo 'selected'; ?>>News Paper</option>
                                    </select>
                                    <label>Assigned Person</label>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <input type="date" name="assignedDate" class="form-control form-control" 
                                           value="<?php echo $tender['assignedDate']; ?>" disabled />
                                    <label>Assigned Date</label>
                                </div>
                            </div>
                            
                            <br><br><h3 class="mb-4 pb-2 pb-md-0 mb-md-3">Tender Status</h3><hr><br>

                            <div class="row">
                            <div class="col-md-4 mb-4">
                                <select class="form-control" name="status" id="status" onchange="toggleStatusFields()" disabled>
                                    <option value="" <?php if($tender['status']=="") echo 'selected'; ?>></option>
                                    <option value="Completed" <?php if($tender['status']=="Completed") echo 'selected'; ?>>Completed</option>
                                    <option value="Uncompleted" <?php if($tender['status']=="Uncompleted") echo 'selected'; ?>>Uncompleted</option>
                                </select>
                                <label>Status</label>
                            </div>
                        </div>

                        <!-- Reason - visible only if Uncompleted -->
                        <div class="row" id="reasonField">
                            <div class="col-md-8 mb-4">
                                <input type="text" name="reason" class="form-control form-control" 
                                    value="<?php echo $tender['reason']; ?>" disabled/>
                                <label>Reason</label>
                            </div>
                        </div>

                        <!-- Submitted Date & Hand Over - visible only if Completed -->
                        <div id="completedFields">
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <input type="date" name="submittedDate" class="form-control form-control" 
                                        value="<?php echo $tender['submittedDate']; ?>" disabled/>
                                    <label>Submitted Date</label>
                                </div>

                                <div class="col-md-4 mb-4">
                                    <input type="text" name="handoverby" class="form-control form-control" 
                                        value="<?php echo $tender['handoverby']; ?>" disabled/>
                                    <label>Hand Over Method</label>
                                </div>
                            </div>
                        </div>

                        <!-- <br><br><h3 class="mb-4 pb-2 pb-md-0 mb-md-3">Tender Readings</h3><hr><br>

                        
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <select class="form-control" name="readings" id="readings" onchange="toggleStatusFields()" disabled>
                                    <option value="" <?php if($tender['readings']=="") echo 'selected'; ?>></option>
                                    <option value="Collected" <?php if($tender['readings']=="Collected") echo 'selected'; ?>>Collected</option>
                                    <option value="NotCollected" <?php if($tender['readings']=="NotCollected") echo 'selected'; ?>>Not Collected</option>
                                </select>
                                <label>Readings</label>
                            </div>
                        </div>

                      
                        <div class="row" id="l1SupplierField">
                            <div class="col-md-8 mb-4">
                                <input type="text" name="l1supplier" class="form-control form-control" 
                                    value="<?php echo $tender['l1supplier']; ?>" disabled/>
                                <label>L1 Supplier</label>
                            </div>
                        </div>

                       
                        <div id="l1PriceFields">
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <input type="text" name="l1price" class="form-control form-control" 
                                        value="<?php echo $tender['l1price']; ?>" disabled/>
                                    <label>L1 Price</label>
                                </div>

                                <div class="col-md-4 mb-4">
                                    <input type="text" name="ourPrice" class="form-control form-control" 
                                        value="<?php echo $tender['ourprice']; ?>" disabled/>
                                    <label>Our Price</label>
                                </div>
                            </div>
                        </div> -->

                            <div class="mt-4 pt-2 d-flex" style="gap: 10px; width: fit-content;">
                                <!-- <button class="btn btn-primary" name="update" style="width: 120px;">Update</button> -->
                                <a href="UnCompletedTenderTable.php" class="btn btn-secondary" style="width: 120px;">Back</a>
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

<?php

session_start();

$timeout_duration = 600;

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['username'])) {
    header("Location: table1.php");
    exit();
}

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();     
    session_destroy();   
    header("Location: index.php?expired=true");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time(); 


include "db.php";

$id = $_GET['id'];  

$sql = "SELECT * FROM sales WHERE id = $id"; 
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "No record found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Powernet Sales</title>
    <link rel="icon" href="logo.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<section class="vh-100 gradient-custom">
    <div class="container py-5 h-100">
        <div class="row justify-content-center align-items-center h-100">
            <div class="col-12 col-lg-10 col-xl-10">
                <div class="card shadow-2-strong card-registration" style="border-radius: 15px;">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="mb-4 pb-2 pb-md-0 mb-md-5">Update Sale</h3>
                        
                        <form name="salesForm" id="salesForm" action="salesUpdate_done.php" method="post">
                            <!-- Hidden ID field -->
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <input type="text" class="form-control form-control-lg" name="cusname" value="<?php echo $row['cusname']; ?>"  />
                                    <label class="form-label">Customer Name</label>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <input type="text" class="form-control form-control-lg" name="location" value="<?php echo $row['location']; ?>" />
                                    <label class="form-label">Location</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4 pb-2">
                                    <textarea class="form-control form-control-lg" name="description" rows="3" required><?php echo $row['description']; ?></textarea>
                                    <label class="form-label">Description</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <input type="text" class="form-control form-control-lg" name="cusType" value="<?php echo $row['cusType']; ?>" />
                                    <br>
                                    <label class="form-label">Customer Type</label>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <input type="text" class="form-control form-control-lg" name="contactPerson" value="<?php echo $row['contactPerson']; ?>" />
                                    <label class="form-label">Contact Person</label>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <input type="text" class="form-control form-control-lg" name="contactPersonEmail" value="<?php echo $row['contactPersonEmail']; ?>"  />
                                    <label class="form-label">Email</label>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <input type="text" class="form-control form-control-lg" name="contactPersonTel" value="<?php echo $row['contactPersonTel']; ?>" />
                                    <label class="form-label">Phone Number</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4 pb-2">
        
                                    <div data-mdb-input-init class="form-outline">
                                        <input type="text" id="expectedCost" name="expectedCost" value="<?php echo $row['expectedCost']; ?>" class="form-control form-control-lg" />
                                        <label class="form-label" for="expectedCost">Expected Cost</label>
                                    </div>
        
                                </div>
                                <div class="col-md-6 mb-4 pb-2">
                                    <div data-mdb-input-init class="form-outline">
                                        <input type="text" id="profit" name="profit" value="<?php echo $row['profit']; ?>" class="form-control form-control-lg" />
                                        <label class="form-label" for="profit">Profit</label>
                                    </div>
        
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4 pb-2">
                                    <div data-mdb-input-init class="form-outline">
                                        <input type="date" id="date" name="estimatedFinishDate" class="form-control form-control-lg" value="<?php echo $row['estimatedFinishDate']?>"/>
                                        <label class="form-label" for="date">Estimated Job Done Date</label>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-12 mb-4">
                                    <select class="form-select form-control-lg" name="sts" required>
                                        <option value="">Select the Status</option>
                                        <option value="Arranged Meeting" <?php if($row['sts'] == 'Arranged Meeting') echo 'selected'; ?>>Arranged Meeting</option>
                                        <option value="Site Visited" <?php if($row['sts'] == 'Site Visited') echo 'selected'; ?>>Site Visited</option>
                                        <option value="Quotation Making" <?php if($row['sts'] == 'Quotation Making') echo 'selected'; ?>>Quotation Making</option>
                                        <option value="Quotation Sent" <?php if($row['sts'] == 'Quotation Sent') echo 'selected'; ?>>Quotation Sent</option>
                                        <option value="Waiting For PO" <?php if($row['sts'] == 'Waiting For PO') echo 'selected'; ?>>Waiting For PO</option>
                                        <option value="Job Won" <?php if($row['sts'] == 'Job Won') echo 'selected'; ?>>Job Won</option>
                                        <option value="Job Lost" <?php if($row['sts'] == 'Job Lost') echo 'selected'; ?>>Job Lost</option>
                                        <option value="Ongoing" <?php if($row['sts'] == 'Ongoing') echo 'selected'; ?>>Ongoing</option>
                                        <option value="Postponed" <?php if($row['sts'] == 'Postponed') echo 'selected'; ?>>Postponed</option>
                                        <option value="Cancled" <?php if($row['sts'] == 'Cancled') echo 'selected'; ?>>Cancled</option>
                                    </select>
                                    <br>
                                    <label class="form-label">Status</label>
                                </div>
                            </div>

                            <div class="mt-4 pt-2 d-flex" style="gap: 10px; width: fit-content;">
                                <button class="btn btn-success" style="width: 120px;" type="submit" name="submit">Update</button>
                            </div>

                        </form>
                        <div style="margin-top:2%;">
                        <form action="jobFinish_done.php" method="post" style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button class="btn btn-secondary" style="width: 120px;" type="submit">Job Finish</button>
                        </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

</body>
</html>

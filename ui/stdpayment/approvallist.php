<?php
$GLOBALS['title'] = "Payment Approval - HMS";
$base_url = "http://localhost/hms/";

require('./../../inc/sessionManager.php');
require('./../../inc/dbPlayer.php');
require('./../../inc/handyCam.php');

$ses = new \sessionManager\sessionManager();
$ses->start();

$loginId = $ses->Get("userIdLoged");
$loginGrp = $ses->Get("userGroupId");

if ($ses->isExpired()) {
    header('Location:' . $base_url . 'login.php');
    exit;
}

$db = new \dbPlayer\dbPlayer();
$msg = $db->open();

if ($msg !== "true") {
    echo '<script>alert("' . htmlspecialchars($msg) . '"); window.location="approvallist.php";</script>';
    exit;
}

$handyCam = new \handyCam\handyCam();

// Handle Status Update (Pending / Approved)
if (isset($_GET['id']) && isset($_GET['status'])) {
    $serialId = intval($_GET['id']);
    $newStatus = $_GET['status'] === 'Yes' ? 'Yes' : 'No';
    $result = $db->updateData("stdpayment", "serial", $serialId, ['isApprove' => $newStatus]);
    $alertMsg = $result === "true" ? "Payment status updated." : $result;
    echo "<script>alert('$alertMsg'); window.location.href='approvallist.php';</script>";
    exit;
}

// Fetch All Payments
$query = "SELECT a.serial, b.name, a.transDate, a.paymentBy, a.transNo, a.amount, a.remark, a.isApprove 
          FROM stdpayment AS a
          JOIN studentinfo AS b ON a.userId = b.userId
          WHERE b.isActive='Y'";
$result = $db->getData($query);
$output = '';

if ($result && $result instanceof mysqli_result && $result->num_rows > 0) {
    $output .= '<div class="table-responsive">
        <table id="paymentList" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Payment Date</th>
                    <th>Paid By</th>
                    <th>Transection/Mobile No</th>
                    <th>Amount</th>
                    <th>Remark</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';
    while ($row = $result->fetch_assoc()) {
        $status = $row['isApprove'] === 'Yes' ? 'Approved' : 'Pending';
        $toggleStatus = $row['isApprove'] === 'Yes' ? 'No' : 'Yes';
        $output .= "<tr>
            <td>" . htmlspecialchars($row['name']) . "</td>
            <td>" . htmlspecialchars($handyCam->getAppDate($row['transDate'])) . "</td>
            <td>" . htmlspecialchars($row['paymentBy']) . "</td>
            <td>" . htmlspecialchars($row['transNo']) . "</td>
            <td>" . htmlspecialchars($row['amount']) . "</td>
            <td>" . htmlspecialchars($row['remark']) . "</td>
            <td>
                <a class='btn btn-" . ($row['isApprove'] === 'Yes' ? 'success' : 'warning') . " btn-sm' 
                   href='approvallist.php?id=" . intval($row['serial']) . "&status=$toggleStatus'>
                   $status
                </a>
            </td>
        </tr>";
    }
    $output .= '</tbody></table></div>';
} else {
    $output = '<h4 class="text-warning">No Payments Found.</h4>';
}

include('./../../master.php');
?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header titlehms"><i class="fa fa-hand-o-right"></i> Payment Approval List</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fa fa-info-circle fa-fw"></i> Student Payment List</div>
                <div class="panel-body"><?php echo $output; ?></div>
            </div>
        </div>
    </div>
</div>

<?php include('./../../footer.php'); ?>

<script type="text/javascript">
$(document).ready(function() {
    $('#paymentList').DataTable({
        "order": [
            [1, "desc"]
        ],
        "pageLength": 10
    });
});
</script>
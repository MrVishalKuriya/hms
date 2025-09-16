<?php
$GLOBALS['title'] = "Billing View - HMS";
$base_url = "http://localhost/hms/";

require('./../../inc/sessionManager.php');
require('./../../inc/dbPlayer.php');

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

$GLOBALS['output'] = '';

if ($msg != "true") {
    echo '<script>alert("Database connection failed: ' . htmlspecialchars($msg) . '");</script>';
    exit;
}

// Ensure $loginId is integer
$loginId = intval($loginId);

// Fetch billing records
if ($loginGrp == "UG001") { // Admin sees all bills
    $query = "SELECT a.billId, b.name, SUM(a.amount) AS amount, 
                     DATE_FORMAT(a.billingDate,'%D %M, %Y') AS date
              FROM billing AS a
              JOIN studentinfo AS b ON a.billTo = b.userId
              WHERE b.isActive='Y'
              GROUP BY a.billId";
} else { // Student sees only their bills
    $query = "SELECT a.billId, b.name, SUM(a.amount) AS amount, 
                     DATE_FORMAT(a.billingDate,'%D %M, %Y') AS date
              FROM billing AS a
              JOIN studentinfo AS b ON a.billTo = b.userId
              WHERE b.isActive='Y' AND a.billTo={$loginId}
              GROUP BY a.billId";
}

$result = $db->getData($query);

if ($result && $result instanceof mysqli_result && mysqli_num_rows($result) > 0) {
    $GLOBALS['output'] .= '<div class="table-responsive">
        <table id="billList" class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>Bill Id</th>
                    <th>Student Name</th>
                    <th>Amount</th>
                    <th>Billing Date</th>';
    if ($loginGrp === "UG001") {
        $GLOBALS['output'] .= '<th>Action</th>';
    }
    $GLOBALS['output'] .= '</tr></thead><tbody>';

    while ($row = mysqli_fetch_assoc($result)) {
        $billId = htmlspecialchars($row['billId']);
        $studentName = htmlspecialchars($row['name']);
        $amount = htmlspecialchars($row['amount']);
        $date = htmlspecialchars($row['date']);

        $GLOBALS['output'] .= "<tr>";
        $GLOBALS['output'] .= "<td><a href='single.php?billId=" . urlencode($billId) . "'>{$billId}</a></td>";
        $GLOBALS['output'] .= "<td>{$studentName}</td>";
        $GLOBALS['output'] .= "<td>{$amount}/-</td>";
        $GLOBALS['output'] .= "<td>{$date}</td>";

        if ($loginGrp === "UG001") {
            $GLOBALS['output'] .= "<td><a class='btn btn-danger btn-circle' href='action.php?id=" . urlencode($billId) . "&wtd=delete'><i class='fa fa-trash'></i></a></td>";
        }

        $GLOBALS['output'] .= "</tr>";
    }

    $GLOBALS['output'] .= '</tbody></table></div>';
} else {
    $GLOBALS['output'] = "<h4 class='text-warning'>No billing records found.</h4>";
}

// Include master layout
if ($loginGrp === "UG004") {
    include('./../../smater.php');
} else {
    include('./../../master.php');
}
?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header titlehms"><i class="fa fa-hand-o-right"></i> Billing View</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-info-circle fa-fw"></i> Hostel Bill List
                </div>
                <div class="panel-body">
                    <?php echo $GLOBALS['output']; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('./../../footer.php'); ?>

<script type="text/javascript">
$(document).ready(function() {
    $('#billList').DataTable();
});
</script>
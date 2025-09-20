<?php

$GLOBALS['title'] = "Payment-HMS";
$base_url = "http://localhost/hms/";

require('./../../inc/sessionManager.php');
require('./../../inc/dbPlayer.php');
require('./../../inc/handyCam.php');
require('./../../inc/fpdf/fpdf.php');

$ses = new \sessionManager\sessionManager();
$ses->start();

$loginId = $ses->Get("userIdLoged");
$loginGrp = $ses->Get("userGroupId");

$display = "";
$displaytable = "none";
$disBtnPrint = "none";
$GLOBALS['isData'] = "0";

$disBtnPrint2 = $loginGrp == "UG001" ? "none" : "";
$GLOBALS['Name'] = "";

$ses->remove("UserIddrp");

if ($ses->isExpired()) {
    header('Location:' . $base_url . 'login.php');
    exit;
}

$db = new \dbPlayer\dbPlayer();
$msg = $db->open();

if (isset($_GET['id']) && $_GET['wtd'] === "delete") {
    if ($msg === "true") {
        $result = $db->delete("DELETE FROM stdpayment WHERE serial='" . $_GET['id'] . "'");
        $alertMsg = $result ? "Payment Deleted Successfully." : $result;
        echo "<script>alert('$alertMsg');window.location.href='view.php';</script>";
    } else {
        echo "<script>alert('$msg');window.location.href='view.php';</script>";
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["btnUpdate"])) {
        $ses->Set("UserIddrp", $_POST['person']);
        getTableData($loginGrp, $_POST['person'], $db);
        $displaytable = "";
        $disBtnPrint = "";
    } elseif (isset($_POST["btnPrint"])) {
        $ses->Set("UserIddrp", $loginGrp === "UG001" ? $_POST['person'] : $loginId);
        printData($db);
    } elseif (isset($_POST["btnUpdatePay"])) {
        if ($msg === "true") {
            $handyCam = new \handyCam\handyCam();
            $serial = $ses->Get("serial");
            $data = [
                'transDate' => $handyCam->parseAppDate($_POST['paydate']),
                'paymentBy' => $_POST['paidby'],
                'transNo' => $_POST['transno'],
                'amount' => floatval($_POST['amount']),
                'remark' => $_POST['remark'],
                'isApprove' => "Yes"
            ];
            $result = $db->updateData("stdpayment", "serial", $serial, $data);
            $alertMsg = $result === "true" ? "Payment Updated Successfully." : $result;
            echo "<script>alert('$alertMsg');window.location.href='view.php';</script>";
            $ses->remove("serial");
        } else {
            echo "<script>alert('$msg');window.location.href='view.php';</script>";
        }
    }
}

if ($loginGrp === "UG004") {
    getTableData($loginGrp, $loginId, $db);
    $display = "none";
    $displaytable = "";
}

$result = $db->getData("SELECT userId,name FROM studentinfo WHERE isActive='Y'");
$GLOBALS['output1'] = '';
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        $GLOBALS['isData1'] = "1";
        $GLOBALS['output1'] .= '<option value="' . $row['userId'] . '">' . $row['name'] . '</option>';
    }
}

function getTableData($logGRP, $userId, $db)
{
    $handyCam = new \handyCam\handyCam();
    $query = $logGRP === "UG004" ?
        "SELECT a.serial,b.name,a.transDate,a.paymentBy,a.transNo,a.amount,a.remark,a.isApprove FROM stdpayment a, studentinfo b WHERE a.userId='$userId' AND a.userId=b.userId AND b.isActive='Y'" :
        "SELECT a.serial,b.name,a.transDate,a.paymentBy,a.transNo,a.amount,a.remark FROM stdpayment a, studentinfo b WHERE a.userId='$userId' AND a.userId=b.userId AND a.isApprove='Yes' AND b.isActive='Y'";

    $result = $db->getData($query);
    $GLOBALS['output'] = '';

    if ($result && $result instanceof mysqli_result && $result->num_rows > 0) {
        $GLOBALS['output'] .= '<div class="table-responsive">
            <table id="paymentList" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Payment Date</th>
                        <th>Paid By</th>
                        <th>Transection/Mobile No</th>
                        <th>Amount</th>
                        <th>Remark</th>';
        $GLOBALS['output'] .= $logGRP !== "UG004" ? '<th>Action</th>' : '<th>Is Approve</th>';
        $GLOBALS['output'] .= '</tr></thead><tbody>';

        while ($row = mysqli_fetch_assoc($result)) {
            $GLOBALS['output'] .= '<tr>';
            $GLOBALS['output'] .= '<td>' . htmlspecialchars($row['name']) . '</td>';
            $GLOBALS['output'] .= '<td>' . htmlspecialchars($row['transDate'] ?? '') . '</td>';
            $GLOBALS['output'] .= '<td>' . htmlspecialchars($row['paymentBy'] ?? '') . '</td>';
            $GLOBALS['output'] .= '<td>' . htmlspecialchars($row['transNo'] ?? '') . '</td>';
            $GLOBALS['output'] .= '<td>' . htmlspecialchars($row['amount'] ?? '') . '</td>';
            $GLOBALS['output'] .= '<td>' . htmlspecialchars($row['remark'] ?? '') . '</td>';    
            if ($logGRP !== "UG004") {
                $GLOBALS['output'] .= '<td>
                                        <a title="Edit" class="btn btn-success btn-circle editBtn" href="view.php?id= '. $row['serial'] . '><i class="fa fa-pencil"></i></a>
                                        <a title="Delete" class="btn btn-danger btn-circle" href="view.php?id=' . $row['serial'] . '><i class="fa fa-trash"></i></a>
                                       </td>';
            } else {
                $GLOBALS['output'] .= '<td>' . htmlspecialchars($row['isApprove'] ?? '') . '</td>';
            }
            $GLOBALS['output'] .= '</tr>';
        }

        $GLOBALS['output'] .= '</tbody></table></div>';
        $GLOBALS['isData'] = "1";
    } else {
        $GLOBALS['output'] = "<h1 class='text-warning'>Payment Data Not Found!!!</h1>";
    }
}

function printData($db)
{
    $ses = new \sessionManager\sessionManager();
    $usId = $ses->Get("UserIddrp");

    class PDFG extends FPDF
    {
        function Header()
        {
            $title = "DIU HOSTEL";
            $subtitle = "4/2, Sobhanbag, Mirpur Road, Dhaka-1207";
            $logoPath = './../../files/photos/logo.png';
            if (file_exists($logoPath)) $this->Image($logoPath, 10, 10, 20);

            $this->SetFont('Helvetica', 'B', 16);
            $this->SetTextColor(0, 122, 195);
            $this->Cell(0, 9, $title, 0, 1, 'C');
            $this->SetFont('Arial', '', 12);
            $this->Cell(0, 9, $subtitle, 0, 1, 'C');
        }

        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial', 'B', 8);
            $this->SetTextColor(0);
            $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb} Print Date:' . date("d/m/Y"), 0, 0, 'C');
        }

        function FancyTable($header, $data)
        {
            $this->SetFillColor(0, 166, 81);
            $this->SetTextColor(255);
            $this->SetFont('', 'B');
            $w = [40, 40, 40, 70];
            foreach ($header as $i => $col) $this->Cell($w[$i], 7, $col, 1, 0, 'C', true);
            $this->Ln();

            $this->SetFillColor(224, 235, 255);
            $this->SetTextColor(0);
            $this->SetFont('');
            $fill = false;
            foreach ($data as $row) {
                $this->SetX(10);
                $this->Cell($w[0], 6, $row[0], 'LR', 0, 'L', $fill);
                $this->Cell($w[1], 6, $row[1], 'LR', 0, 'L', $fill);
                $this->Cell($w[2], 6, number_format($row[2]) . '/-', 'LR', 0, 'R', $fill);
                $this->Cell($w[3], 6, $row[3], 'LR', 0, 'L', $fill);
                $this->Ln();
                $fill = !$fill;
            }
            $this->SetX(10);
            $this->Cell(array_sum($w), 0, '', 'T');
        }

        function SetLeftMargin($margin)
        {
            $this->SetX($margin);
        }

        function GetStringWidth($s)
        {
            return strlen($s) * 6;
        }
    }

    $pdf = new PDFG('I', 'mm', 'A4');
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Times', '', 12);
    $pdf->SetFillColor(200, 220, 255);

    $dataall = LoadData($db, $usId);
    $billhead = "Payment By: " . $GLOBALS["Name"];
    $pdf->SetLeftMargin(50);
    $pdf->Cell($pdf->GetStringWidth($billhead) + 4, 10, $billhead, 0, 1, 'L', true);
    $pdf->Ln(5);

    $header = ['Payment Date', 'Payment By', 'Amount', 'Remark'];
    $pdf->SetFont('Arial', '', 14);
    $pdf->FancyTable($header, $dataall);
    $pdf->Output("payment.pdf");
    echo '<script>window.open("payment.pdf","_blank");</script>';
    exit;
}

function LoadData($db, $userId)
{
    $query = "SELECT a.serial,b.name,a.transDate,a.paymentBy,a.transNo,a.amount,a.remark,a.isApprove FROM stdpayment a, studentinfo b WHERE a.userId='$userId' AND a.userId=b.userId AND b.isActive='Y'";
    $result = $db->execDataTable($query);
    $handyCam = new \handyCam\handyCam();
    $paydata = [];

    while ($row = mysqli_fetch_array($result)) {
        $GLOBALS['Name'] = $row["name"];
        $paydata[] = [
            $handyCam->getAppDate($row["transDate"]),
            $row["paymentBy"],
            $row["amount"],
            $row["remark"]
        ];
    }
    return $paydata;
}

if ($loginGrp === "UG004") {
    include('./../../smater.php');
} elseif ($loginGrp === "UG003") {
    include('./../../emaster.php');
} else {
    include('./../../master.php');
}

?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header titlehms"><i class="fa fa-hand-o-right"></i> Payment View</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-info-circle fa-fw"></i><i class="fa fa-hand-o-right"></i> Student Payment View
                </div>
                <div class="panel-body">
                    <form name="payment" method="post">
                        <button type="submit" class="btn btn-info" style="display:<?php echo $disBtnPrint2; ?>"
                            name="btnPrint"><i class="fa fa-print"></i> Print</button>
                    </form>

                    <form name="payment" method="post">
                        <div class="row" id="divview" style="display:<?php echo $display; ?>">
                            <div class="col-lg-12">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Student Name</label>
                                        <select class="form-control" name="person" required>
                                            <?php echo $GLOBALS['output1']; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-success" name="btnUpdate"><i
                                                    class="fa fa-check-circle-o"></i> View</button>
                                            <button type="submit" class="btn btn-info"
                                                style="display:<?php echo $disBtnPrint; ?>" name="btnPrint"><i
                                                    class="fa fa-print"></i> Print</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div id="editpayment" style="display:none">
                        <form name="payment" method="post">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Payment Date</label>
                                            <div class="input-group date" id="dp1">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                <input id="paydate" type="text" class="form-control datepicker"
                                                    name="paydate" placeholder="Payment Date" required
                                                    data-date-format="dd/mm/yyyy">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Paid By</label>
                                            <select id="payby" class="form-control" name="paidby" required>
                                                <option value="Bank">Bank</option>
                                                <option value="DBBL">DBBL</option>
                                                <option value="Bkash">BKash</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Transection/Mobile No</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i
                                                        class="fa fa-sort-numeric-asc"></i></span>
                                                <input id="transno" type="text" class="form-control" name="transno"
                                                    placeholder="Transection or Mobile no" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-money"></i></span>
                                                <input id="amount" type="text" class="form-control" name="amount"
                                                    placeholder="Amount" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Remark</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-info"></i></span>
                                                <input id="remark" type="text" class="form-control" name="remark"
                                                    placeholder="Additional Info" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div class="input-group">
                                                <button type="submit" class="btn btn-success pull-right"
                                                    name="btnUpdatePay"><i class="fa fa-2x fa-check"></i>
                                                    Update</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="row" style="display:<?php echo $displaytable; ?>">
                        <div class="col-lg-12">
                            <hr />
                            <?php echo $GLOBALS['output']; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('./../../footer.php'); ?>
<script type="text/javascript">
$(document).ready(function() {
    $('#zpaymentList').dataTable();
    $('.editBtn').on('click', function() {
        $('#divview').hide();
        $('#editpayment').show();
        var serial = $(this).attr('href').substring(1);
        $('#paydate').val($(this).closest("tr").find("td").eq(1).text());
        $('#payby').val($(this).closest("tr").find("td").eq(2).text());
        $('#transno').val($(this).closest("tr").find("td").eq(3).text());
        $('#amount').val($(this).closest("tr").find("td").eq(4).text());
        $('#remark').val($(this).closest("tr").find("td").eq(5).text());
        $.ajax({
            type: 'POST',
            url: '/hms/sesboss.php',
            data: {
                'serial': serial
            }
        });
    });

    $("select option").filter(function() {
        return $(this).val() == '<?php echo $ses->Get("UserIddrp"); ?>';
    }).prop('selected', true);
});
</script>
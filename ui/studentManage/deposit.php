<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

$GLOBALS['title']="Deposit-HMS";
$base_url="http://localhost/hms/";

require('./../../inc/sessionManager.php');
require('./../../inc/dbPlayer.php');
require_once('./../../inc/fpdf.php');

class DepositPDF extends PDF
{
    function Header()
    {
        // Logo
        $this->Image('./../../dist/images/logo.png',10,6,30,20);
          $title="HRS HOSTEL";
        $subtitle="Localhost,Mirpur Road,Dhaka-1207";
        $this->Cell(80);
        $this->SetFont('Helvetica','B',16);
        $w = $this->GetStringWidth($title)+6;
        $this->SetX((210-$w)/2);
        $this->SetTextColor(0,122,195);
        $this->Cell($w,9,$title,0,1,'C');
        $this->Cell(80);
        $this->SetFont('Helvetica','',12);
        $w = $this->GetStringWidth($subtitle)+6;
        $this->SetX((210-$w)/2);
        $this->SetTextColor(0,122,195);
        $this->Cell($w,9,$subtitle,0,1,'C');
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Helvetica','B',8);
        $this->SetTextColor(0);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}     Print Date:'.date("d/m/Y"),0,0,'C');
    }

    function FancyTable($header, $data)
    {
        $this->SetFillColor(0,166,81);
        $this->SetTextColor(255);
        $this->SetDrawColor(128,0,0);
        $this->SetFont('','B');
        $w = array(40,40,70);
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
        $this->Ln();
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont('');
        $fill = false;
        foreach($data as $row)
        {
            $this->SetX(30);
            $this->Cell($w[0],6,$row[0],'LR',0,'L',$fill);
            $this->Cell($w[1],6,number_format($row[1]).'/-','LR',0,'R',$fill);
            $this->Cell($w[2],6,$row[2],'LR',0,'L',$fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->SetX(30);
        $this->Cell(array_sum($w),0,'','T');
    }
}

$ses = new \sessionManager\sessionManager();
$ses->start();
$GLOBALS['isData1']="";

if($ses->isExpired())
{
    header( 'Location:'.$base_url.'login.php');
    exit();
}
else
{
    try {
        $name=$ses->Get("loginId");
        $msg="";
        $db = new \dbPlayer\dbPlayer();
        $msg = $db->open();
        if ($msg !== "true") {
            throw new Exception("Database connection failed: " . $msg);
        }

        // Load student list for dropdown
        $student_list_result = $db->getData("SELECT userId,name FROM studentinfo  where isActive='Y'");
        $GLOBALS['output']='';
        if($student_list_result) {
            while ($row = mysqli_fetch_array($student_list_result)) {
                $GLOBALS['isData']="1";
                $GLOBALS['output'] .= '<option value="'.$row['userId'].'">'.$row['name'].'</option>';
            }
        } else {
            throw new Exception("Could not load student list from database.");
        }

        getData();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST["btnSave"])) {
                if (empty($_POST['amount']) || empty($_POST['person']) || $_POST['person'] === "0") {
                    echo '<script type="text/javascript"> alert("Please select a student and enter an amount to save a new deposit.");</script>';
                } else {
                    $data = array(
                        'userId' => $_POST['person'],
                        'amount' => floatval($_POST['amount']),
                        'depositDate' =>date("Y-m-d")
                    );
                    $result = $db->insertData("deposit",$data);
                    if($result>=0) {
                        echo '<script type="text/javascript"> alert("Money Deposit Successful.");</script>';
                        getData(); // Refresh data table
                    } else {
                        throw new Exception("Failed to save deposit.");
                    }
                }
            } elseif (isset($_POST["btnPrint"])) {
                printData($db);
            }
        }
    } catch (Exception $e) {
        echo "<h1>An Error Occurred</h1><p>An unexpected error prevented the page from loading correctly.</p><hr><strong>Error details:</strong> <pre>" . $e->getMessage() . "</pre>";
        exit();
    }
}

function getData()
{
    $db = new \dbPlayer\dbPlayer();
    $msg = $db->open();
    if ($msg == "true") {
        $result = $db->getData("SELECT a.serial,b.name,a.amount,DATE_FORMAT(a.depositDate, '%D %M,%Y') as date from deposit as a, studentinfo as b where a.userId = b.userId and b.isActive='Y'");
        $GLOBALS['output1']='';
        if($result && mysqli_num_rows($result) > 0) {
            $GLOBALS['output1'].='<div class="table-responsive"><table id="depositList" class="table table-striped table-bordered table-hover"><thead><tr><th>Name</th><th>Amount</th><th>Deposit Date</th><th>Action</th></tr></thead><tbody>';
            while ($row = mysqli_fetch_array($result)) {
                $GLOBALS['isData1']="1";
                $GLOBALS['output1'] .= "<tr>";
                $GLOBALS['output1'] .= "<td>" . $row['name'] . "</td>";
                $GLOBALS['output1'] .= "<td>" . $row['amount'] . "</td>";
                $GLOBALS['output1'] .= "<td>" . $row['date'] . "</td>";
                $GLOBALS['output1'] .= "<td><a title='Edit' class='btn btn-success btn-circle' href='depositaction.php?id=" . $row['serial'] ."&wtd=edit'"."><i class='fa fa-pencil'></i></a>&nbsp&nbsp<a title='Delete' class='btn btn-danger btn-circle' href='depositaction.php?id=" . $row['serial'] ."&wtd=delete'"."><i class='fa fa-trash-o'></i></a></td>";
                $GLOBALS['output1'] .= "</tr>";
            }
            $GLOBALS['output1'].=  '</tbody></table></div>';
        }
    }
}

function printData($db)
{
    try {
        $usId = "0";
        if (isset($_POST['person']) && !empty($_POST['person'])) {
            $usId = $_POST['person'];
        }

        $pdf = new DepositPDF('P', 'mm', 'A4');
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Times','',12);
        $pdf->SetFillColor(200,220,255);
        $pdf->SetTextColor(0,0,0);
        $dataall = LoadData($db, $usId);
        $billhead = "Meal Deposit List";
        $filename = "deposit_bill.pdf";

        if ($usId !== "0") {
            $res = $db->getData("SELECT name FROM studentinfo WHERE userId = '" . $usId . "'");
            if ($res && mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_array($res);
                $studentName = $row['name'];
                $billhead = "Deposit List for " . $studentName;
                $filename = str_replace(' ', '_', strtolower($studentName)) . "_deposit_bill.pdf";
            }
        }

        $w = $pdf->GetStringWidth($billhead)+4;
        $pdf->Cell($w,10,$billhead,0,1,'L',true);
        $pdf->Ln(5);
        $pdf->SetX(30);
        $header = array('Name','Amount','Date');
        $pdf->SetFont('Helvetica','',14);
        $pdf->FancyTable($header,$dataall);
        $pdf->Output($filename, "D");
        exit;
    } catch (Exception $e) {
        header('Content-Type: text/html');
        echo "<h1>Error Generating PDF</h1><p>An unexpected error occurred:</p><pre>" . $e->getMessage() . "</pre>";
        exit();
    }
}

function LoadData($db, $userId)
{
    $query = "SELECT a.serial,b.name,a.amount,DATE_FORMAT(a.depositDate, '%D %M,%Y') as date from deposit as a, studentinfo as b where a.userId = b.userId and b.isActive='Y'";
    if ($userId !== "0") {
        $query .= " AND a.userId = '" . $userId . "'";
    }
    $result = $db->execDataTable($query);
    if (!$result) {
        throw new Exception("Database query failed in LoadData function.");
    }
    $paydata = array();
    while ($row = mysqli_fetch_array($result)) {
        $rowd=array();
        array_push($rowd,$row["name"]);
        array_push($rowd,$row["amount"]);
        array_push($rowd,$row["date"]);
        array_push($paydata,$rowd);
    }
    return $paydata;
}
?>
<?php include('./../../master.php'); ?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header titlehms"><i class="fa fa-hand-o-right"></i>Deposit</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-info-circle fa-fw"></i>Meal Money Deposit
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <form name="deposit" action="deposit.php" accept-charset="utf-8" method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Student Name</label>
                                            <select class="form-control" name="person">
                                                <option value="0">All Students</option>
                                                <?php echo $GLOBALS['output'];?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group ">
                                            <label>Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="fa fa-info"></i> </span>
                                                <input type="text" placeholder="Amount for New Deposit" class="form-control" name="amount">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                         <div class="col-lg-2">
                                            <div class="form-group ">
                                                <button type="submit" class="btn btn-success" name="btnSave"><i class="fa fa-check"></i> Save</button>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <div class="form-group ">
                                                 <button type="submit" class="btn btn-info" name="btnPrint"><i class="fa fa-print"></i> Print Report</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <hr />
                            <?php if($GLOBALS['isData1']=="1"){echo $GLOBALS['output1'];}?>
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
    $('#depositList').dataTable();
});
</script>

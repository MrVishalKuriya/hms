<?php
require('./../../inc/sessionManager.php');
require('./../../inc/dbPlayer.php');
require('./../../inc/fpdf.php');

$ses = new \sessionManager\sessionManager();
$ses->start();

if (!$ses->Get("billId")) {
    header("location:view.php");
    exit;
}

$billId = intval($ses->Get("billId"));
$db = new \dbPlayer\dbPlayer();
$msg = $db->open();
if ($msg !== "true") {
    echo '<script>alert("Database connection failed: '.$msg.'"); window.location.href="view.php";</script>';
    exit;
}

// Fetch bill info
$billResult = $db->execDataTable("SELECT a.type, a.amount, b.name, DATE_FORMAT(a.billingDate,'%D %M,%Y') as date 
                                  FROM billing a 
                                  JOIN studentinfo b ON a.billTo = b.userId 
                                  WHERE a.billId='$billId'");

if (!$billResult || mysqli_num_rows($billResult) == 0) {
    echo '<script>alert("Bill not found!"); window.location.href="view.php";</script>';
    exit;
}

$billData = [];
$billTo = ""; $billDate = ""; $total = 0;

while($row = mysqli_fetch_assoc($billResult)){
    $billTo = $row['name'];
    $billDate = $row['date'];
    $total += $row['amount'];
    $billData[] = [$row['type'], $row['amount']];
}

// PDF generation
class PDF extends FPDF {
    function Header() {
        $this->Image('./../../dist/images/logo.png',10,6,30);
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,'HRS HOSTEL',0,1,'C');
        $this->SetFont('Arial','',12);
        $this->Cell(0,10,'Localhost, Mirpur Road, Dhaka-1207',0,1,'C');
        $this->Ln(5);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}     Print Date:'.date("d/m/Y"),0,0,'C');
    }
    function FancyTable($header, $data){
        $this->SetFillColor(0,166,81);
        $this->SetTextColor(255);
        $this->SetFont('', 'B');
        $w = [100,40];
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
        $this->Ln();
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont('');
        $fill=false;
        foreach($data as $row){
            $this->SetX(10);
            $this->Cell($w[0],6,$row[0],'LR',0,'L',$fill);
            $this->Cell($w[1],6,number_format($row[1],2).'/-','LR',0,'R',$fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->SetX(10);
        $this->Cell(array_sum($w),0,'','T');
    }
}

$pdf = new PDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','',12);

$billHead = "Bill Id: $billId     Bill To: $billTo     Bill Date: $billDate";
$pdf->Cell(0,10,$billHead,0,1);
$pdf->Ln(5);

$header = ['Type','Amount'];
$pdf->SetFont('Arial','',14);
$pdf->FancyTable($header,$billData);

$pdf->Ln(5);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'Total Bill: '.number_format($total,2).'/-',0,1,'C');
$pdf->Output("bill.pdf","I");
exit;
?>
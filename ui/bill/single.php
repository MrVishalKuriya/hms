<?php
$GLOBALS['title'] = "Bill-HMS";
require('./../../inc/sessionManager.php');
require('./../../inc/dbPlayer.php');

// Try different FPDF loading methods
$fpdf_paths = [
    './../../inc/fpdf.php',
    './../../inc/fpdf/fpdf.php', 
    './fpdf.php',
    'fpdf.php'
];

$fpdf_loaded = false;
foreach($fpdf_paths as $path) {
    if(file_exists($path)) {
        require_once($path);
        $fpdf_loaded = true;
        break;
    }
}

// If FPDF not found, use our compact version
if(!$fpdf_loaded || !class_exists('FPDF')) {
    class FPDF {
        private $pg=0,$n=2,$buf='',$pgs=[],$st=0,$k=1,$w=210,$h=297;
        private $x=0,$y=0,$lm=10,$tm=10,$rm=10,$bm=10,$cm=1,$lh=0,$lw=0.2;
        private $ff='',$fs='',$fsp=12,$fsz=12,$cf=[],$dc='0 G',$fc='0 g',$tc='0 g';
        private $fts=[],$imgs=[],$lnks=[],$plnks=[],$offs=[],$apb=true,$pbt=267;
        private $zm='default',$lm_mode='default',$ttl='',$subj='',$auth='',$keys='';
        private $alias='';
        
        public function __construct($o='P',$u='mm',$s='A4') {
            $this->k = $u=='pt' ? 1 : ($u=='cm' ? 28.35 : ($u=='in' ? 72 : 2.835));
            $sizes=['A4'=>[210,297],'A3'=>[297,420],'LETTER'=>[216,279]];
            [$this->w,$this->h] = $sizes[strtoupper($s)] ?? [210,297];
            if(strtoupper($o[0])=='L') [$this->w,$this->h] = [$this->h,$this->w];
            $this->pbt = $this->h - 20;
        }
        
        public function AddPage() {
            if($this->st==0) $this->st=1;
            $this->pg++; $this->pgs[$this->pg]=''; $this->st=2;
            $this->x=$this->lm; $this->y=$this->tm; $this->ff='';
            $this->out('2 J'); $this->out(sprintf('%.2F w',$this->lw*$this->k));
        }
        
        public function SetFont($f,$s='',$sz=0) {
            $f=strtolower($f=='arial'?'helvetica':$f);
            $s=strtoupper(str_replace('U','',$s,$u));
            if($sz==0) $sz=$this->fsp;
            if($this->ff==$f && $this->fs==$s && $this->fsp==$sz) return;
            
            $fk=$f.$s;
            if(!isset($this->fts[$fk])) {
                if(!in_array($f,['courier','helvetica','times','symbol','zapfdingbats']))
                    $f='helvetica';
                $this->fts[$fk]=['i'=>count($this->fts)+1,'type'=>'core','name'=>ucfirst($f)];
            }
            
            $this->ff=$f; $this->fs=$s; $this->fsp=$sz; $this->fsz=$sz/$this->k;
            $this->cf=&$this->fts[$fk];
            if($this->pg>0) $this->out(sprintf('BT /F%d %.2F Tf ET',$this->cf['i'],$sz));
        }
        
        public function Cell($w,$h=0,$txt='',$b=0,$ln=0,$a='',$fill=false,$link='') {
            $k=$this->k;
            if($this->y+$h>$this->pbt && $this->apb) {
                $x=$this->x; $this->AddPage(); $this->x=$x;
            }
            if($w==0) $w=$this->w-$this->rm-$this->x;
            
            $s='';
            if($fill||$b==1) {
                $op=$fill?($b==1?'B':'f'):'S';
                $s=sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
            }
            
            if($txt!=='') {
                $dx=$a=='R'?$w-$this->cm-$this->GetStringWidth($txt):($a=='C'?($w-$this->GetStringWidth($txt))/2:$this->cm);
                $txt2=str_replace(['\\','(',')'],['\\\\',"\\(","\\)"],$txt);
                // Apply text color
                if($this->tc != '0 g') $s.='q '.$this->tc.' ';
                $s.=sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->fsz))*$k,$txt2);
                if($this->tc != '0 g') $s.=' Q';
            }
            
            if($s) $this->out($s);
            $this->lh=$h;
            if($ln>0) {$this->y+=$h; if($ln==1) $this->x=$this->lm;} else $this->x+=$w;
        }
        
        public function Ln($h=null) { $this->x=$this->lm; $this->y+=($h??$this->lh); }
        public function GetStringWidth($s) { return strlen($s)*$this->fsz*0.6; }
        public function SetXY($x,$y) {$this->x=$x;$this->y=$y;}
        public function GetX() {return $this->x;}
        public function GetY() {return $this->y;}
        public function SetX($x) {$this->x=$x;}
        public function SetY($y) {$this->x=$this->lm;$this->y=$y;}
        public function PageNo() {return $this->pg;}
        public function AliasNbPages($alias='{nb}') {$this->alias=$alias;}
        public function Image($file,$x=null,$y=null,$w=0,$h=0) {} // Stub
        
        public function SetFillColor($r,$g=null,$b=null) {
            $this->fc=$g===null?sprintf('%.3F g',$r/255):sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
            if($this->pg>0) $this->out($this->fc);
        }
        
        public function SetTextColor($r,$g=null,$b=null) {
            $this->tc=$g===null?sprintf('%.3F g',$r/255):sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
        }
        
        public function Output($dest='I',$name='doc.pdf') {
            if($this->st<3) $this->Close();
            switch(strtoupper($dest)) {
                case 'I': header('Content-Type: application/pdf'); header("Content-Disposition: inline; filename=\"$name\""); echo $this->buf; break;
                case 'D': header('Content-Type: application/octet-stream'); header("Content-Disposition: attachment; filename=\"$name\""); echo $this->buf; break;
                case 'F': file_put_contents($name,$this->buf); break;
                case 'S': return $this->buf;
            }
            return '';
        }
        
        public function Close() { if($this->st==3) return; if($this->pg==0) $this->AddPage(); $this->st=1; $this->endDoc(); }
        
        private function out($s) { if($this->st==2) $this->pgs[$this->pg].=$s."\n"; else $this->buf.=$s."\n"; }
        private function newObj() { $this->n++; $this->offs[$this->n]=strlen($this->buf); $this->out($this->n.' 0 obj'); }
        
        private function endDoc() {
            $this->out('%PDF-1.3'); $this->putPages(); $this->putResources(); $this->putInfo(); $this->putCatalog();
            $o=strlen($this->buf); $this->out('xref'); $this->out('0 '.($this->n+1)); $this->out('0000000000 65535 f ');
            for($i=1;$i<=$this->n;$i++) $this->out(sprintf('%010d 00000 n ',$this->offs[$i]));
            $this->out('trailer'); $this->out('<<'); $this->out('/Size '.($this->n+1)); $this->out('/Root '.$this->n.' 0 R');
            $this->out('/Info '.($this->n-1).' 0 R'); $this->out('>>'); $this->out('startxref'); $this->out($o); $this->out('%%EOF'); $this->st=3;
        }
        
        private function putPages() {
            for($n=1;$n<=$this->pg;$n++) {
                $this->newObj(); $this->out('<</Type /Page /Parent 1 0 R');
                $this->out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->w*$this->k,$this->h*$this->k));
                $this->out('/Resources 2 0 R /Contents '.($this->n+1).' 0 R>>'); $this->out('endobj');
                $this->newObj(); $p=str_replace($this->alias,$this->pg,$this->pgs[$n]);
                $this->out('<</Length '.strlen($p).'>>'); $this->out('stream'); $this->out($p); $this->out('endstream'); $this->out('endobj');
            }
            $this->offs[1]=strlen($this->buf); $this->out('1 0 obj'); $this->out('<</Type /Pages');
            $kids='/Kids ['; for($i=0;$i<$this->pg;$i++) $kids.=(3+2*$i).' 0 R '; $this->out($kids.']');
            $this->out('/Count '.$this->pg); $this->out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->w*$this->k,$this->h*$this->k));
            $this->out('>>'); $this->out('endobj');
        }
        
        private function putResources() {
            $this->putFonts(); $this->offs[2]=strlen($this->buf); $this->out('2 0 obj');
            $this->out('<</ProcSet [/PDF /Text]'); if($this->fts) { $this->out('/Font <<');
                foreach($this->fts as $ft) $this->out('/F'.$ft['i'].' '.$ft['n'].' 0 R'); $this->out('>>'); }
            $this->out('>>'); $this->out('endobj');
        }
        
        private function putFonts() {
            foreach($this->fts as $k=>$ft) { $this->fts[$k]['n']=$this->n+1; $this->newObj();
                $this->out('<</Type /Font /BaseFont /'.$ft['name'].' /Subtype /Type1');
                if(!in_array(strtolower($ft['name']),['symbol','zapfdingbats'])) $this->out('/Encoding /WinAnsiEncoding');
                $this->out('>>'); $this->out('endobj'); }
        }
        
        private function putInfo() { $this->newObj(); $this->out('<<'); $this->out('/Producer (Compact FPDF)');
            if($this->ttl) $this->out('/Title ('.$this->esc($this->ttl).')');
            if($this->auth) $this->out('/Author ('.$this->esc($this->auth).')');
            $this->out('/CreationDate (D:'.date('YmdHis').')'); $this->out('>>'); $this->out('endobj'); }
        
        private function putCatalog() { $this->newObj(); $this->out('<</Type /Catalog /Pages 1 0 R>>'); $this->out('endobj'); }
        private function esc($s) { return str_replace(['\\','(',')'],['\\\\',"\\(","\\)"],$s); }
    }
}

$ses = new \sessionManager\sessionManager();
$ses->start();
$loginGrp = $ses->Get("userGroupId");

if (!isset($_GET['billId'])) {
    header("location: view.php"); exit;
}

$billId = $_GET['billId'];
$db = new \dbPlayer\dbPlayer();
$msg = $db->open();
if ($msg !== "true") {
    die("DB Connection Failed: $msg");
}

// Fetch bill data - using basic sanitization since dbPlayer API is unknown
$billId = preg_replace('/[^a-zA-Z0-9_-]/', '', $billId); // Basic sanitization
$billResult = $db->execDataTable("SELECT a.billId, b.name, a.type, a.amount, DATE_FORMAT(a.billingDate,'%D %M,%Y') as date 
                                  FROM billing a 
                                  JOIN studentinfo b ON a.billTo = b.userId 
                                  WHERE a.billId = '$billId'");

if (!$billResult || mysqli_num_rows($billResult) == 0) {
    echo "<script>alert('Bill Info Not Present'); window.location='view.php';</script>";
    exit;
}

// Prepare data for display & PDF
$billInfo = ["", "", 0.00];
$output = '<table class="table table-bordered"><thead><tr><th>Type</th><th>Amount</th></tr></thead><tbody>';
$dataForPDF = [];

while ($row = mysqli_fetch_assoc($billResult)) {
    $billInfo[0] = $row['name'];
    $billInfo[1] = $row['date'];
    $billInfo[2] += $row['amount'];

    $output .= "<tr><td>".htmlspecialchars($row['type'])."</td><td>".number_format($row['amount'],2)." /-</td></tr>";
    $dataForPDF[] = [$row['type'], $row['amount']];
}
$output .= "</tbody></table>";

// PDF Generation - RENAMED CLASS TO AVOID CONFLICT
if (isset($_POST['btnPrint'])) {
    class BillPDF extends FPDF {
        function Header() {
            $logoPath = realpath('./../../dist/images/logo.png');
            if(file_exists($logoPath)) {
                $this->Image($logoPath, 10, 6, 30);
            }
            $this->SetFont('Arial','B',16);
            $this->Cell(0,10,'HRS HOSTEL',0,1,'C');
            $this->SetFont('Arial','',12);
            $this->Cell(0,10,'Localhost, Mirpur Road, Dhaka-1207',0,1,'C');
            $this->Ln(10);
        }
        
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(0,10,'Page '.$this->PageNo().'/{nb} Print Date: '.date("d/m/Y"),0,0,'C');
        }
        
        function FancyTable($header, $data) {
            // Header
            $this->SetFillColor(0,166,81);
            $this->SetTextColor(255);
            $this->SetFont('Arial', 'B', 12);
            $w = [100, 40];
            
            for($i=0; $i<count($header); $i++) {
                $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
            }
            $this->Ln();
            
            // Data rows
            $this->SetFillColor(224,235,255);
            $this->SetTextColor(0);
            $this->SetFont('Arial', '', 11);
            $fill = false;
            
            foreach($data as $row) {
                $this->SetX(10);
                $this->Cell($w[0], 6, $row[0], 'LR', 0, 'L', $fill);
                $this->Cell($w[1], 6, number_format($row[1], 2) . ' /-', 'LR', 0, 'R', $fill);
                $this->Ln();
                $fill = !$fill;
            }
            
            // Closing line
            $this->SetX(10);
            $this->Cell(array_sum($w), 0, '', 'T');
        }
    }

    $pdf = new BillPDF('P','mm','A4');
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(0,10,"Bill ID: $billId   Bill To: {$billInfo[0]}   Bill Date: {$billInfo[1]}",0,1);
    $pdf->Ln(5);
    $pdf->FancyTable(['Type','Amount'], $dataForPDF);
    $pdf->Ln(10);
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,'Total Bill: '.number_format($billInfo[2],2).' /-',0,1,'C');
    $pdf->Output("I","bill_$billId.pdf");
    exit;
}

// HTML View
include('./../../master.php');
?>
<div id="page-wrapper">
    <h1>Bill Info [<?php echo htmlspecialchars($billId); ?>]</h1>
    <p><strong>Bill To:</strong> <?php echo htmlspecialchars($billInfo[0]); ?></p>
    <p><strong>Bill Date:</strong> <?php echo htmlspecialchars($billInfo[1]); ?></p>
    <?php echo $output; ?>
    <p><strong>Total Amount:</strong> <?php echo number_format($billInfo[2],2); ?> /-</p>
    <form method="post">
        <button name="btnPrint" class="btn btn-info">Print / Download PDF</button>
    </form>
</div>
<?php include('./../../footer.php'); ?>
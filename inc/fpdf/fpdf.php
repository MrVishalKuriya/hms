<?php
class FPDF{
protected $diffs=[],$FontFiles=[],$fonts=[];
function AddPage(){} function SetFont($f,$s='',$z=0){} function Cell($w,$h=0,$t='',$b=0,$l=0,$a='',$fi=false){} function Ln($h=null){} function Output($d='',$n='',$u=false){} function AliasNbPages(){} function SetY($y){} function SetX($x){} function SetFillColor($r,$g,$b){} function SetTextColor($r,$g=null,$b=null){} function Error($m){exit('FPDF error: '.$m);}
function Header(){ $logo=realpath('./../../dist/images/logo.png'); if($logo&&file_exists($logo))$this->Image($logo,10,6,30); $this->SetFont('Arial','B',16); $this->Cell(0,10,'HRS HOSTEL',0,1,'C'); $this->SetFont('Arial','',12); $this->Cell(0,10,'Localhost, Mirpur Road, Dhaka-1207',0,1,'C'); }
function Footer(){ $this->SetY(-15); $this->SetFont('Arial','I',8); $this->Cell(0,10,'Page '.$this->PageNo().'/{nb} Print Date:'.date('d/m/Y'),0,0,'C'); }
function FancyTable($h,$d){ if(!is_array($d))$d=[]; $this->SetFillColor(0,166,81); $this->SetTextColor(255); $this->SetFont('','B'); $w=[100,40]; foreach($h as $i=>$c)$this->Cell($w[$i],7,$c,1,0,'C',true); $this->Ln(); $this->SetFillColor(224,235,255); $this->SetTextColor(0); $this->SetFont(''); $f=false; foreach($d as $r){ $this->SetX(10); $this->Cell($w[0],6,$r[0],'LR',0,'L',$f); $this->Cell($w[1],6,number_format($r[1],2).'/-','LR',0,'R',$f); $this->Ln(); $f=!$f;} $this->SetX(10); $this->Cell(array_sum($w),0,'','T'); }
function Image($file,$x,$y,$w=0,$h=0){ if(!file_exists($file))$this->Error('Image file not found: '.$file); }
function PageNo(){ return 1; }
}
?>
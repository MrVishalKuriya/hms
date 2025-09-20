<?php
/**
 * Ultra-Compact FPDF - Minimal PDF generator
 * Clean, fast, and highly compressed implementation
 */
class PDF {
    // Core properties
    private $pg=0,$n=2,$buf='',$pgs=[],$st=0,$k=1,$w=210,$h=297;
    private $x=0,$y=0,$lm=10,$tm=10,$rm=10,$bm=10,$cm=1,$lh=0,$lw=0.2;
    private $ff='',$fs='',$fsp=12,$fsz=12,$cf=[],$dc='0 G',$fc='0 g',$tc='0 g';
    private $fts=[],$imgs=[],$lnks=[],$plnks=[],$offs=[],$apb=true,$pbt=267;
    private $zm='default',$lm_mode='default',$ttl='',$subj='',$auth='',$keys='';
    
    // Standard page sizes
    private $sizes=['A4'=>[210,297],'A3'=>[297,420],'LETTER'=>[216,279]];
    
    public function __construct($o='P',$u='mm',$s='A4') {
        $this->k = $u=='pt' ? 1 : ($u=='cm' ? 28.35 : ($u=='in' ? 72 : 2.835));
        [$this->w,$this->h] = $this->sizes[strtoupper($s)] ?? [210,297];
        if(strtoupper($o[0])=='L') [$this->w,$this->h] = [$this->h,$this->w];
        $this->pbt = $this->h - 20;
    }
    
    // Core methods
    public function AddPage() {
        if($this->st==0) $this->st=1;
        $this->pg++; $this->pgs[$this->pg]=''; $this->st=2;
        $this->x=$this->lm; $this->y=$this->tm; $this->ff='';
        $this->out('2 J'); $this->out(sprintf('%.2F w',$this->lw*$this->k));
    }
    
    // public function SetFont($f,$s='',$sz=0) {
    //     $f=strtolower($f=='arial'?'helvetica':$f);
    //     $s=strtoupper(str_replace('U','',$s,$u));
    //     	$this->underline=$u>0;
    //     if($sz==0) $sz=$this->fsp;
    //     if($this->ff==$f && $this->fs==$s && $this->fsp==$sz) return;
        
    //     $fk=$f.$s;
    //     if(!isset($this->fts[$fk])) {
    //         if(!in_array($f,['courier','helvetica','times','symbol','zapfdingbats']))
    //             throw new Exception("Undefined font: $f");
    //         $this->fts[$fk]=['i'=>count($this->fts)+1,'type'=>'core','name'=>ucfirst($f)];
    //     }
        
    //     $this->ff=$f; $this->fs=$s; $this->fsp=$sz; $this->fsz=$sz/$this->k;
    //     $this->cf=&$this->fts[$fk];
    //     if($this->pg>0) $this->out(sprintf('BT /F%d %.2F Tf ET',$this->cf['i'],$sz));
    // }
	public function SetFont($family, $style = '', $size = 0) {
    if ($family === '') {
        $family = $this->ff;
    }
    $family = strtolower($family === 'arial' ? 'helvetica' : $family);
    $style = strtoupper(str_replace('U', '', $style, $underlineCount));
    // $this->underline = $underlineCount > 0;
    if ($size == 0) $size = $this->fsp;
    if ($this->ff === $family && $this->fs === $style && $this->fsp === $size) return;
    $fontKey = $family . $style;
    if (!isset($this->fts[$fontKey])) {
        $coreFonts = ['courier', 'helvetica', 'times', 'symbol', 'zapfdingbats'];
        if (!in_array($family, $coreFonts)) throw new Exception("Undefined font: $family");
        $this->fts[$fontKey] = ['i' => count($this->fts) + 1, 'type' => 'core', 'name' => ucfirst($family)];
    }
    $this->ff = $family;
    $this->fs = $style;
    $this->fsp = $size;
    $this->fsz = $size / $this->k;
    $this->cf = &$this->fts[$fontKey];
    if ($this->pg > 0) $this->out(sprintf('BT /F%d %.2F Tf ET', $this->cf['i'], $size));
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
            $s.=sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->fsz))*$k,$txt2);
        }
        
        if($s) $this->out($s);
        $this->lh=$h;
        if($ln>0) {$this->y+=$h; if($ln==1) $this->x=$this->lm;} else $this->x+=$w;
    }
    
    public function Text($x,$y,$txt) {
        $s=sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$txt));
        $this->out($s);
    }
    
    public function Line($x1,$y1,$x2,$y2) {
        $this->out(sprintf('%.2F %.2F m %.2F %.2F l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
    }
    
    public function Rect($x,$y,$w,$h,$style='') {
        $op=$style=='F'?'f':($style=='FD'||$style=='DF'?'B':'S');
        $this->out(sprintf('%.2F %.2F %.2F %.2F re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$op));
    }
    
    public function Ln($h=null) {
        $this->x=$this->lm; $this->y+=($h??$this->lh);
    }
    
    public function GetStringWidth($s) {
        return strlen($s)*$this->fsz*0.6; // Approximation
    }
    
    public function SetXY($x,$y) {$this->x=$x;$this->y=$y;}
    public function GetX() {return $this->x;}
    public function GetY() {return $this->y;}
    public function SetX($x) {$this->x=$x;}
    public function SetY($y) {$this->x=$this->lm;$this->y=$y;}
    
    // Color methods
    public function SetDrawColor($r,$g=null,$b=null) {
        $this->dc=$g===null?sprintf('%.3F G',$r/255):sprintf('%.3F %.3F %.3F RG',$r/255,$g/255,$b/255);
        if($this->pg>0) $this->out($this->dc);
    }
    
    public function SetFillColor($r,$g=null,$b=null) {
        $this->fc=$g===null?sprintf('%.3F g',$r/255):sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
        if($this->pg>0) $this->out($this->fc);
    }
    
    public function SetTextColor($r,$g=null,$b=null) {
        $this->tc=$g===null?sprintf('%.3F g',$r/255):sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
    }
    
    // Document info
    public function SetTitle($title) {$this->ttl=$title;}
    public function SetAuthor($author) {$this->auth=$author;}
    public function SetSubject($subject) {$this->subj=$subject;}
    
    // Output
    public function Output($dest='I',$name='doc.pdf') {
        if($this->st<3) $this->Close();
        
        switch(strtoupper($dest)) {
            case 'I': // Browser
                header('Content-Type: application/pdf');
                header("Content-Disposition: inline; filename=\"$name\"");
                echo $this->buf;
                break;
            case 'D': // Download
                header('Content-Type: application/octet-stream');
                header("Content-Disposition: attachment; filename=\"$name\"");
                echo $this->buf;
                break;
            case 'F': // File
                file_put_contents($name,$this->buf);
                break;
            case 'S': // String
                return $this->buf;
        }
        return '';
    }
    
    public function Close() {
        if($this->st==3) return;
        if($this->pg==0) $this->AddPage();
        $this->st=1; $this->endDoc();
    }
    
    // Internal methods
    private function out($s) {
        if($this->st==2) $this->pgs[$this->pg].=$s."\n";
        else $this->buf.=$s."\n";
    }
    
    private function newObj() {
        $this->n++; $this->offs[$this->n]=strlen($this->buf);
        $this->out($this->n.' 0 obj');
    }
    
    private function endDoc() {
        $this->out('%PDF-1.3');
        $this->putPages();
        $this->putResources();
        $this->putInfo();
        $this->putCatalog();
        
        $o=strlen($this->buf);
        $this->out('xref');
        $this->out('0 '.($this->n+1));
        $this->out('0000000000 65535 f ');
        for($i=1;$i<=$this->n;$i++) $this->out(sprintf('%010d 00000 n ',$this->offs[$i]));
        $this->out('trailer'); $this->out('<<'); 
        $this->out('/Size '.($this->n+1)); $this->out('/Root '.$this->n.' 0 R');
        $this->out('/Info '.($this->n-1).' 0 R'); $this->out('>>');
        $this->out('startxref'); $this->out($o); $this->out('%%EOF');
        $this->st=3;
    }
    
    private function putPages() {
        for($n=1;$n<=$this->pg;$n++) {
            $this->newObj();
            $this->out('<</Type /Page /Parent 1 0 R');
            $this->out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->w*$this->k,$this->h*$this->k));
            $this->out('/Resources 2 0 R /Contents '.($this->n+1).' 0 R>>');
            $this->out('endobj');
            
            $this->newObj();
            $this->out('<</Length '.strlen($this->pgs[$n]).'>>');
            $this->out('stream'); $this->out($this->pgs[$n]); $this->out('endstream');
            $this->out('endobj');
        }
        
        $this->offs[1]=strlen($this->buf);
        $this->out('1 0 obj'); $this->out('<</Type /Pages');
        $kids='/Kids ['; for($i=0;$i<$this->pg;$i++) $kids.=(3+2*$i).' 0 R ';
        $this->out($kids.']'); $this->out('/Count '.$this->pg);
        $this->out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->w*$this->k,$this->h*$this->k));
        $this->out('>>'); $this->out('endobj');
    }
    
    private function putResources() {
        $this->putFonts();
        $this->offs[2]=strlen($this->buf);
        $this->out('2 0 obj'); $this->out('<</ProcSet [/PDF /Text]');
        if($this->fts) {
            $this->out('/Font <<');
            foreach($this->fts as $ft) $this->out('/F'.$ft['i'].' '.$ft['n'].' 0 R');
            $this->out('>>');
        }
        $this->out('>>'); $this->out('endobj');
    }
    
    private function putFonts() {
        foreach($this->fts as $k=>$ft) {
            $this->fts[$k]['n']=$this->n+1;
            $this->newObj();
            $this->out('<</Type /Font /BaseFont /'.$ft['name'].' /Subtype /Type1');
            if(!in_array(strtolower($ft['name']),['symbol','zapfdingbats']))
                $this->out('/Encoding /WinAnsiEncoding');
            $this->out('>>'); $this->out('endobj');
        }
    }
    
    private function putInfo() {
        $this->newObj();
        $this->out('<<'); $this->out('/Producer (Ultra-Compact FPDF 2.0)');
        if($this->ttl) $this->out('/Title ('.$this->esc($this->ttl).')');
        if($this->auth) $this->out('/Author ('.$this->esc($this->auth).')');
        if($this->subj) $this->out('/Subject ('.$this->esc($this->subj).')');
        $this->out('/CreationDate (D:'.date('YmdHis').')');
        $this->out('>>'); $this->out('endobj');
    }
    
    private function putCatalog() {
        $this->newObj();
        $this->out('<</Type /Catalog /Pages 1 0 R>>');
        $this->out('endobj');
    }
    
    private function esc($s) {
        return str_replace(['\\','(',')'],['\\','\(','\)'],$s);
    }

    public function AliasNbPages($alias = '{nb}') {}

    public function PageNo() {
        return $this->pg;
    }

    public function Image($file, $x=null, $y=null, $w=0, $h=0) {}
}

/* Usage Example:
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(40,10,'Hello World!');
$pdf->Output();
*/
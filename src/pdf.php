<?php 
require('./fpdf/fpdf.php');
class PDF extends FPDF {
    var $widths;
    var $aligns;
    var $lineHeight;

    function Header() {
      $this->setFont('Arial', '', 8);
      $this->SetMargins(15, 15);

      $this->Image('../assets/images/cdm-logo.png', 50, 12, -700);

      $this->setFont('Arial', '', 20);
      $this->Cell(0, 20, 'Colegio de Montalban', 0, 1, 'C');
      
      $this->setFont('Arial', '', 6);
      $this->Cell(0, 5, 'Kasiglahan Village, San Jose Rodriguez, Rizal', 0, 1, 'C');
      
      $this->Cell(0, 5, 'Tel No.: (02)2868667/ (02)283959731 Email Address: registrar@pnm.edu.ph', 0, 1, 'C');
    
      $this->setFont('Arial', 'B', 6);
      $this->Cell(0, 5, 'OFFICIAL REGISTRATION FORM', 0, 1, 'C');
    }

//Set the array of column widths
function SetWidths($w){
    $this->widths=$w;
}

//Set the array of column alignments
function SetAligns($a){
    $this->aligns=$a;
}

//Set line height
function SetLineHeight($h){
    $this->lineHeight=$h;
}

function FancyRow($data, $border=array(), $align=array(), $style=array(), $maxline=array())
{
    //Calculate the height of the row
    $nb = 0;
    for($i=0;$i<count($data);$i++) {
        $nb = max($nb, $this->NbLines($this->widths[$i],$data[$i]));
    }
    if (count($maxline)) {
        $_maxline = max($maxline);
        if ($nb > $_maxline) {
            $nb = $_maxline;
        }
    }
    $h = 5*$nb;
    //Issue a page break first if needed
    $this->CheckPageBreak($h);
    //Draw the cells of the row
    for($i=0;$i<count($data);$i++) {
        $w=$this->widths[$i];
        // alignment
        $a = isset($align[$i]) ? $align[$i] : 'L';
        // maxline
        $m = isset($maxline[$i]) ? $maxline[$i] : false;
        //Save the current position
        $x = $this->GetX();
        $y = $this->GetY();
        //Draw the border
        if ($border[$i]==1) {
            $this->Rect($x,$y,$w,$h);
        } else {
            $_border = strtoupper($border[$i]);
            if (strstr($_border, 'L')!==false) {
                $this->Line($x, $y, $x, $y+$h);
            }
            if (strstr($_border, 'R')!==false) {
                $this->Line($x+$w, $y, $x+$w, $y+$h);
            }
            if (strstr($_border, 'T')!==false) {
                $this->Line($x, $y, $x+$w, $y);
            }
            if (strstr($_border, 'B')!==false) {
                $this->Line($x, $y+$h, $x+$w, $y+$h);
            }
        }
        // Setting Style
        if (isset($style[$i])) {
            $this->SetFont('', $style[$i]);
        }
        $this->MultiCell($w, 5, $data[$i], 0, $a, 0, $m);
        //Put the position to the right of the cell
        $this->SetXY($x+$w, $y);
    }
    //Go to the next line
    $this->Ln($h);
}

//Calculate the height of the row
function Row($data)
{
    // number of line
    $nb=0;

    // loop each data to find out greatest line number in a row.
    for($i=0;$i<count($data);$i++){
        // NbLines will calculate how many lines needed to display text wrapped in specified width.
        // then max function will compare the result with current $nb. Returning the greatest one. And reassign the $nb.
        $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
    }
    
    //multiply number of line with line height. This will be the height of current row
    $h=$this->lineHeight * $nb;

    //Issue a page break first if needed
    $this->CheckPageBreak($h);

    //Draw the cells of current row
    for($i=0;$i<count($data);$i++)
    {
        // width of the current col
        $w=$this->widths[$i];
        // alignment of the current col. if unset, make it left.
        $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
        //Save the current position
        $x=$this->GetX();
        $y=$this->GetY();
        //Draw the border
        $this->Rect($x,$y,$w,$h);
        //Print the text
        $this->MultiCell($w,5,$data[$i],0,$a);
        //Put the position to the right of the cell
        $this->SetXY($x+$w,$y);
    }
    //Go to the next line
    $this->Ln($h);
}

function CheckPageBreak($h)
{
    //If the height h would cause an overflow, add a new page immediately
    if($this->GetY()+$h>$this->PageBreakTrigger)
        $this->AddPage($this->CurOrientation);
}

function NbLines($w,$txt)
{
    //calculate the number of lines a MultiCell of width w will take
    $cw=&$this->CurrentFont['cw'];
    if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    $s=str_replace("\r",'',$txt);
    $nb=strlen($s);
    if($nb>0 and $s[$nb-1]=="\n")
        $nb--;
    $sep=-1;
    $i=0;
    $j=0;
    $l=0;
    $nl=1;
    while($i<$nb)
    {
        $c=$s[$i];
        if($c=="\n")
        {
            $i++;
            $sep=-1;
            $j=$i;
            $l=0;
            $nl++;
            continue;
        }
        if($c==' ')
            $sep=$i;
        $l+=$cw[$c];
        if($l>$wmax)
        {
            if($sep==-1)
            {
                if($i==$j)
                    $i++;
            }
            else
                $i=$sep+1;
            $sep=-1;
            $j=$i;
            $l=0;
            $nl++;
        }
        else
            $i++;
    }
    return $nl;
}
}
?>
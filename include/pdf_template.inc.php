<?php 
$from_tolito = 1;
require_once('../include/fpdi/fpdi.php');

/**
 * Клас, който генерира PDF по зададен шаблон
 * @example 
 	$oPDF = new PDFTemplatesClass();
	$oPDF->setPDFTemplatePath('../doc_templates/1.loading.pdf');
	$oPDF->setPrintArray($aPrint);
	$oPDF->preparePDF();
	$oPDF->processBarCode(39, 13.5, 40, 18, zero_padding($aLoading['num']), 1);
	$oPDF->processPDF();
 * @author Boro 27.09.2006
 */
class PDFTemplatesClass extends fpdf  {
	
	public $sOrientation		= "P";				// Ориентация на листа P, L
	public $sFormat				= "A4";				// Формат на листа
	public $sOutput				= "D";				// Изход - на керан или във файл
	public $sOutputFile			= "result.pdf";		// Име на изходния файл
	public $nDefaultFontSize	= 10;				// Големина на шрифт по подразбиране
	public $sPDFTemplate;							// Път до шаблона
	public $bGrid				= false;			// Дали да се появява грид или не setGrid ()
	public $aPrint				= array();			// Масив, с данни които ще се печатат пр. $Print[0] = array( 'x'=>100, 'y'=>200, 'text'=>'Example', 'font_size'=>6)
	public $aMargin 			= array( "left"=>20, "top"=>25,  "right"=>10 ); 
	public $aPageDimensions		= array();
	public $angle;
	public $pdf;
	
	function __construct() {
		$this->pdf = new fpdi();
		$this->setPageDimensions();
	}
	
	function setPDFTemplatePath ($sPDFTemplate) {
		$this->sPDFTemplate = $sPDFTemplate;
	}
	
	function setPageOrientation ($sOrientation) {
		$this->sOrientation = $sOrientation;
	}
	
	function setPageFormat ($sFormat) {
		$this->sFormat = $sFormat;
	}
	
	function setOutput ($sOutput) {
		$this->sOutput = $sOutput;
	}
	
	function setOutputFile ($sOutputFile) {
		$this->sOutputFile = $sOutputFile;
	}
	
	function setPrintArray ($aPrint) {
		$this->aPrint = $aPrint;
	}
	
	function setDefaultFontSize ($nFontSize) {
		$this->nDefaultFontSize = $nFontSize;
	}
	
	function setGrid ($bGrid = true) {
		$this->bGrid = $bGrid;
	}
	
	function drawGrid ($maxX=500, $maxY=500) {

		$this->pdf->SetDrawColor(255,255,255);
		
		$k = 0;
		for ($x = 0; $x<=$maxX; $x+=2){
			$k+=1;
			if ($k==5) {
				$this->pdf->SetLineWidth(0.3); $this->pdf->SetDrawColor(0,0,0);
				$k=0;
			} else {
				 $this->pdf->SetLineWidth(0.01); $this->pdf->SetDrawColor(150,150,150);
			}
			$this->pdf->Line($x, 0, $x, $maxY);
			
			
		}
		$k = 0;
		for ($y = 0; $y<=$maxY; $y+=2){
			$k+=1;
			if ($k==5) {
				$this->pdf->SetLineWidth(0.3); $this->pdf->SetDrawColor(0,0,0);
				$k=0;
			} else {
				 $this->pdf->SetLineWidth(0.01); $this->pdf->SetDrawColor(150,150,150);
			}
			$this->pdf->Line(0, $y, $maxX, $y);
		}
	}
	
	function processBarCode ($x, $y, $w, $h, $mBarCodeNum=0, $nText=1) {
		$sContent 	= sprintf("%s/include/barcodes/barcode.php?code=code128&pcode=%s&text=%s&height=40&resolution=1&img_type=jpg", dirname($_SERVER['HTTP_REFERER']), $mBarCodeNum, $nText );
		$this->pdf->Image($sContent, $x, $y, $w, $h, 'jpg');	
	}
	
	
	function preparePDF ($aLoading, $sPrintMedia = 'default') {
		// Формат на товарителницата
		$this->pdf->fpdf_tpl($this->sOrientation, "mm", $this->sFormat); 	
		
		// празен шаблон - оригинал
		$this->pdf->setSourceFile($this->sPDFTemplate);						
		
		$this->pdf->AddFont('FreeSans', '', 'FreeSans.php');
		$this->pdf->SetTextColor(0,0,0);	
		$this->pdf->SetFont("FreeSans");
		
		for ($i = 1; $i <= ($sPrintMedia == 'default' ? 1 : 2); $i++) {
			
			$oTpl = $this->pdf->ImportPage(1);
			$this->pdf->AddPage($this->sOrientation);		
			$this->pdf->useTemplate($oTpl);
			$this->pdf->SetAutoPageBreak(false,0);
		
			// изцикляне на данните с масива за печат
			foreach ($this->aPrint AS $v) {
				$this->pdf->SetFontSize(isset($v['font_size']) ? $v['font_size'] : $this->nDefaultFontSize);
				$this->pdf->SetXY($v['x'], $v['y']);
				if (isset($v['text'])) {
					if (isset($v['align'])) 
						$this->pdf->Cell(isset($v['cell_width']) ? $v['cell_width'] : 12, isset($v['cell_height']) ? $v['cell_height'] : 6.8, iconv("UTF-8", "CP1251", $v['text'] ), 0, 0, $v['align'] );
					else
						$this->pdf->Cell(0, 0, iconv("UTF-8", "CP1251", stripslashes($v['text'] )) );
				}
			
			}
			
			switch ($sPrintMedia) {
				case 'template':			// Ще се печата на предварително подготвена бланка
					$this->processBarCode(26, 8.5, 40, 18, zero_padding($aLoading['num']), 1);
					$this->processBarCode(26, 159.7, 40, 18, zero_padding($aLoading['num']), 1);
					$this->setLoadingCaption (); $this->setLoadingCaption ();
					break;
					
				case 'double':				// В случай, че трябва да се печатат 2 товарителници на 1 лист
					$this->processBarCode(28, 10.2, 40, 18, zero_padding($aLoading['num']), 1);
					$this->processBarCode(28, 161.7, 40, 18, zero_padding($aLoading['num']), 1);
					$this->setLoadingCaption (0, 2.5); $this->setLoadingCaption (0, 2.5);
					break;
				default:
					$this->processBarCode(28, 10.2, 40, 18, zero_padding($aLoading['num']), 1);		
			}

		}
		
		// показване на грид
		if ($this->bGrid)
			$this->drawGrid();
	}
	
	
	function setPageDimensions () {
		switch (strtolower($this->sFormat)) {
			case 'a3':
				$this->aPageDimensions = array(297, 420); break;
			case 'a5':
				$this->aPageDimensions = array(148, 210); break;
			case 'letter':
				$this->aPageDimensions = array(216, 297); break;
			case 'legal':
				$this->aPageDimensions = array(216, 355); break;
			default:
				$this->aPageDimensions = array(210, 297); break;
        }
	}
	
	
	function setFooter () {
		$this->pdf->SetAuthor('IntelliSys 2013');
		$this->pdf->SetMargins( $this->aMargin['left'], $this->aMargin['top'], $this->aMargin['right'] );
		$nPageW = ($this->sOrientation == 'P') ? $this->aPageDimensions[0] : $this->aPageDimensions[1];
		$nPageH = ($this->sOrientation == 'P') ? $this->aPageDimensions[1] : $this->aPageDimensions[0];
		
		$this->pdf->SetDrawColor( 90 );
		$this->pdf->SetLineWidth(0.2);
		$this->pdf->Line($this->aMargin['left'], $nPageH - 27, $nPageW -  $this->aMargin['right'], $nPageH - 27);
		$this->pdf->SetFont('FreeSans', '', 6);
		$this->pdf->SetY(-27);
		$this->pdf->Cell(0,6,iconv("UTF-8", "CP1251", 'Генериран на ').date("d.m.Y"),0,0,'R');
		$this->pdf->SetFont('FreeSans', '', 4);
		$this->pdf->SetY(-27);
		$this->pdf->Cell(0,6,iconv("UTF-8", "CP1251", 'Документът е разпечатан с продукта "IntelliSys"'),0,0,'L');
	}

	// WRAP на текст в клетка
	function subWrite ($h, $txt, $link='', $FontSize=8, $subOffset=0) {
		
    	$FontSizeold = $this->FontSizePt; // текуща големина на шрифта
    	$this->SetFontSize($FontSize);
    
    	$subOffset = ((($FontSize - $FontSizeold) / $this->k) * 0.3) + ($subOffset / $this->k);
    	$subX        = $this->x;
    	$subY        = $this->y;
    	$this->SetXY($subX, $subY - $subOffset);

    	//Output text
    	$this->Write($h, $txt, $link);

    	// restore y position
    	$subX        = $this->x;
    	$subY        = $this->y;
    	$this->SetXY($subX,   $subY + $subOffset);

    	// restore font size
    	$this->SetFontSize($FontSizeold);
	}


	function Rotate($angle, $x=-1, $y=-1) {
		if($x==-1)
			$x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->angle!=0)
            $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0)
        {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->pdf->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }

    function _endpage() {
        if($this->angle!=0)
        {
            $this->angle=0;
            $this->_out('Q');
        }
        parent::_end;
    }

	// Поставане на надпис ОРИГИНАЛ и пр.
    function setLoadingCaption ($offsetX = 0, $offsetY = 0) {
  
    	static $nPageNumber;
    	
    	if (empty($nPageNumber)) 
    		$nPageNumber = 1;
    	elseif ($nPageNumber > 4)
    		return false;
    			
    	$aCaption 	 = array();
    	$aCaption[1] = array('text'=>'ОРИГИНАЛ', 			'textX'=>150 + $offsetX, 'textY'=>0 + $offsetY, 		'imageX'=>198, 'imageY'=>0);
    	$aCaption[2] = array('text'=>'ОБРАТНА РАЗПИСКА', 	'textX'=>150 + $offsetX, 'textY'=>151.1 + $offsetY, 	'imageX'=>198, 'imageY'=>151.5);
    	$aCaption[3] = array('text'=>'КОПИЕ ЗА ПОЛУЧАТЕЛ', 	'textX'=>150 + $offsetX, 'textY'=>0 + $offsetY, 		'imageX'=>198, 'imageY'=>0);
    	$aCaption[4] = array('text'=>'КОПИЕ ЗА ПОДАТЕЛ', 	'textX'=>150 + $offsetX, 'textY'=>151.1 + $offsetY, 	'imageX'=>198, 'imageY'=>151.5);
    		
    	$this->pdf->SetTextColor(0, 0, 0);	
		$this->pdf->SetFont("FreeSans");
		$this->pdf->SetFontSize(11);
    	$this->pdf->SetXY($aCaption[$nPageNumber]['textX'], $aCaption[$nPageNumber]['textY']);
    	$this->pdf->Cell(0,	6,	iconv("UTF-8", "CP1251", $aCaption[$nPageNumber]['text']),	0,	0,	'L');
    	$this->pdf->Image( $_SESSION['EOL_BASE_DIR'].'/images/c'.$nPageNumber.'.jpg', $aCaption[$nPageNumber]['imageX'], $aCaption[$nPageNumber]['imageY'], 7.5, 7, 'jpg');
    	
    	$nPageNumber++;
    }
    
	
	// Генериране на PDF
	function processPDF () {
		return $this->pdf->Output($this->sOutputFile, $this->sOutput); 
		
	}
}
?>
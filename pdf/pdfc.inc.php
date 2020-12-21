<?php
	require_once('include/ufpdf/fpdf.php');

    if( !FPDF_FONTPATH ) {
        define('FPDF_FONTPATH', $_SESSION['BASE_DIR'] . '/include/ufpdf/font/');
    }
	
	class PDFC extends FPDF	{
        function __construct($orientation='P') {
            parent::__construct($orientation);

			$this->AddFont('FreeSans', '', 'FreeSans.php');
			$this->AddFont('FreeSans', 'B', 'FreeSansB.php');
			$this->SetMargins( $this->aMargin['left'], $this->aMargin['top'], $this->aMargin['right'] );
			$this->_PageWidth  = $orientation == 'P' ? 210 : 297;
			$this->_PageHeight = $orientation == 'P' ? 297 : 210;
			$this->SetAuthor('IntelliSys 2013');
			$this->SetCreator('LG');
			$this->AliasNbPages();
			#$this->AliasCrPages();
		}

		function Cell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='') {
			FPDF::Cell($w,$h,iconv('UTF-8','CP1251',$txt),$border,$ln,$align,$fill,$link);
		}
		

		function PrintTableHeader($aFields, $aWidths) {
			$this->SetFillColor( $this->aTables['Caption']['Background'] );
			$this->SetTextColor( $this->aTables['Caption']['TextColor'] );
			$this->SetDrawColor( $this->aTables['Caption']['BorderColor'] );
			$this->SetLineWidth( $this->aTables['Caption']['BorderWidth']  );
			$this->SetFont('FreeSans', 'B',  $this->aTables['Caption']['FontSize'] );

			foreach( $aFields as $sFieldKey => $aField )
			{
				$nWidth = isset($aWidths[$sFieldKey]) ?  $aWidths[$sFieldKey] : 30;
				if($nWidth>0)
				{
					$this->Cell($nWidth, $this->aTables['Caption']['Height'], $aField->sCaption,1,0,'C',1);
				}
			}
			// Изтрива остатъка от съдържанието на послдната колона за да се получи margin-right
			$this->SetFillColor( 255 );
			$this->Cell($this->_PageWidth, $this->aTables['Rows']['Height'], '',"L",0,'L',1);
			$this->Ln();
		}

		function moveX( $length = 0 ) {
			$this->SetX( $this->GetX() + $length );
		}
		
		function dottedLine ($length = 0 ) {
			$x = $this->GetX();
			$y = $this->GetY();
			
			for( $i = $x ; $i < $x+$length ; $i+=2 ) {
				$this->Line($i,$y,$i+1,$y);
			}
			$this->SetX($i);
		}
		
		function Faces( $aFaces, $size) {
			for( $i = 0; $i < $size ; $i++ ) {
				$this->Ln(3);
				if(!empty($aFaces[$i])) {
					$this->Cell(30,'',$aFaces[$i]['name']);
					$this->Cell(20,'',$aFaces[$i]['post']);
					$this->Cell(40,'',$aFaces[$i]['phone']);
				}
				$this->Ln(2);
				$this->moveX(1);
				$this->dottedLine(90);
			}
		}
		
		function showSMSVest( $aSMSVest , $price) {
			foreach ( $aSMSVest as $value) {
				$this->Ln(4);
				$this->moveX(10);
				$this->Cell(65,'',$value['user_name']." ".$value['user_gsm']);	
				$this->Cell(10,'',$price." лв");	
				$this->Ln(2);
				$this->moveX(71);
				$this->dottedLine(20);
			}
		}
		
		function showEmails( $aEmailVest) {
			foreach ( $aEmailVest as $value) {
				$this->Ln(4);
				$this->moveX(10);
				$this->Cell(65,'',$value['user_email']);		
			}
		}
		
		function showPrihodiSlave( $aTaxes ) {
			foreach ( $aTaxes as $value ) {
				$this->Ln(4);
				$this->moveX(10);
				$this->Cell(65,'',$value['name']);	
				$this->Cell(10,'',$value['price']." лв");	
				$this->Ln(2);
				$this->moveX(71);
				$this->dottedLine(20);
			}
		}
	}
?>
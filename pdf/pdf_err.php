<?php
	#---------------------------------------------------------
	# Използва се за генериране на PDF със съобщение за грешка
	# $aErr  = array();
	# $aErr['errMsg'] = "Съдържание на грешката";
	# $aErr['errNum'] = "Номер на грешката, ако има такъв";
	#---------------------------------------------------------

	include_once('pdf.inc.php');
	
	class errPDF extends PDF
	{
		
		var $aOptions = array(
				'Header'=> array(
					'TitleFontSize'=>16,
					'StatusFontSize'=>10,
					'StatusHeight'=>7
				)
		);


		var $aErr  = array(); 
		

		function __construct($orientation)
		{
            parent::__construct($orientation);
		}

		function Header() 
		{	
			//$sPath =  $_SESSION['EOL_BASE_DIR'].'/images/attention.gif';
			//$this->Image( $sPath, $this->aHeader['left'], $this->aHeader['top'], 0, $this->aMargin['top'] - $this->aHeader['top'],'gif' );
			$this->SetY($this->aHeader['top']);

			// Заглавие на документа
			$sTitle = "Г Р Е Ш К А!";
			$this->SetFont('FreeSans', "B", $this->aOptions['Header']['TitleFontSize'] );
			$this->Cell( 0, $this->aMargin['top'] - $this->aHeader['top'] - $this->aOptions['Header']['StatusHeight'], $sTitle, 0, 0, 'R');
			$this->Ln();
			
			// Съдържание на забележката
			$sErrMsg = isset ($this->aErr['errNum']) ? sprintf ("[%s] ", $this->aErr['errNum']) : "";
			$sErrMsg = isset ($this->aErr['errMsg']) ? sprintf("%s %s", $sErrMsg, $this->aErr['errMsg']) : "";
			$this->Ln(15);
			$this->SetFont('FreeSans', '',  $this->aOptions['Header']['StatusFontSize'], 12);
			$this->Cell( 0, $this->aOptions['Header']['StatusHeight'], $sErrMsg, 0, 0, 'C');

			$this->SetDrawColor( 255,0,0 );
			$this->SetLineWidth(0.2);
			$this->Line($this->aMargin['left'], $this->aMargin['top'] + 2 , 
						$this->_PageWidth -  $this->aMargin['right'],  $this->aMargin['top'] + 2 );
			$this->Ln($this->aMargin['top']-$this->aHeader['top']+2);
		
		}



		function ClearRightMargin() 
		{
			$y=$this->GetY();
			$this->SetXY( $this->PageWidth - $this->aMargin['right']+0.4, 0 );
			$this->SetFillColor( 255 );
			$this->Cell( $this->aMargin['right'],  $y, "",0, 0, "L",1);
		}

		function PrintReport( $oResponse, $sDocumentTitle, $sFileName = 'doc' ) {
			
			$aFE = current($oResponse->oAction->aForms)->aFormElements;
			foreach ( $aFE as $key => $row )
				$this->aErr[$key] = $row->mValue; 

			$this->SetTitle( $sDocumentTitle );
			$this->AddPage();
			$this->ClearRightMargin();
			$this->Output();
		}
	}
?>
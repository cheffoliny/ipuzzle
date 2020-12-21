<?php
	include_once('pdf.inc.php');
	
	class oaPDF extends PDF
	{
        public function __construct($orientation)
        {
            parent::__construct($orientation);
        }

		function PrintReport( DBResponse $oResponse, $sDocumentTitle, $sFileName = 'doc', $sDestination = '') {

			$aResult = array();
			$aResult['fields'] = $oResponse->oResult->aFields;
			$aResult['data'] = $oResponse->oResult->aData;
			$aResult['data_attributes'] = $oResponse->oResult->aDataAttributes;

			$oField = new DBField();
			$oField->sCaption	= '     #     ';
			$oField->aAttributes = array('align' => 'right');
			
			$aResult['fields'] = array_merge(array('#'=>$oField), $aResult['fields']);

			if ( !empty($oResponse->oResult->aData) ) {
				$oObject 		= new DBObjects();

				$nIDObject 			= isset($oResponse->oResult->aData[0]['id_obj']) ? (int) $oResponse->oResult->aData[0]['id_obj'] : 0;
				$aObject 			= $oObject->getForContract($nIDObject);
				$sDocumentClient 	= "Клиент: ".stripslashes($aObject['client_name']);
				$sDocumentObject	= "Обект: ".stripslashes($aObject['name']);
				$sDocumentAddress	= "Адрес: ".stripslashes($aObject['address']);
			}
						
			//$this->SetTitle( "asasa".$sDocumentTitle );
			
			$this->SetFont('FreeSans', '', 8);
			$aWidths = $this->MakeColWidth( $aResult, $this->_PageWidth - $this->aMargin['left'] - $this->aMargin['right']);
			
			$this->AddPage();
			$this->SetFont('FreeSans', 'B', 10);
			$this->SetXY(89, 8);
			$this->Cell(0, 8, $sDocumentClient);
			$this->Ln();
			$this->SetFont('FreeSans', '', 6);
			$this->SetXY(89, 12);
			$this->Cell(0, 12, $sDocumentObject);
			$this->SetXY(89, 14);
			$this->Cell(0, 14, $sDocumentAddress);			
			$this->Ln(3);
			$this->SetXY($this->aMargin['left'], 28);
			
			$this->SetFont('FreeSans', '', 8);
			$this->PrintTableHeader( $aResult['fields'], $aWidths );
			$this->PrintTableData( $aResult, $aWidths );
			$this->PrintTotal( $aResult );

			$this->Output($sFileName, $sDestination);
		}
	}
?>
<?php
	include_once('pdf.inc.php');
	
	class grPDF extends PDF
	{
		function __construct($orientation)
		{
            parent::__construct($orientation);
		}

		function PrintReport( DBResponse $oResponse, $sDocumentTitle, $sFileName = 'doc', $sDestination = '') {

			$aResult = array();
			$aResult['fields'] = $oResponse->oResult->aFields;
			$aResult['data'] = $oResponse->oResult->aData;
			$aResult['data_attributes'] = $oResponse->oResult->aDataAttributes;
			$aResult['totals'] = $oResponse->oResult->aTotal;


			$oField = new DBField();
			$oField->sCaption	= '     #     ';
			$oField->aAttributes = array('align' => 'right');
			
			$aResult['fields'] = array_merge(array('#'=>$oField), $aResult['fields']);

			$this->SetTitle( $sDocumentTitle );
			$this->SetFont('FreeSans', '', 8);

			$aWidths = $this->MakeColWidth( $aResult, $this->_PageWidth - $this->aMargin['left'] - $this->aMargin['right']);
			
			$this->AddPage();
			$this->PrintTableHeader( $aResult['fields'], $aWidths );
			$this->PrintTableData( $aResult, $aWidths );
			$this->PrintTotal( $aResult );

			$this->Output($sFileName, "I");
		}
	}
?>
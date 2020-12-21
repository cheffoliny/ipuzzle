<?php
	include_once('pdfc.inc.php');
	include_once('pdf.inc.php');
	
	class apPDF extends PDFC 	{
		
		private $nID;
		
		private $nPPPsCounter;
		
		private $nCreatedDay;
		private $nCreatedMonth;
		private $nCreatedYear;	
		
		private $sPPPType;	
		
		private $sSourceType;
		private $sDestType;
		private $sSourceName;
		private $sDestName;
		private $sSourcePerson;
		private $sDestPerson;
		
		function apPDF($orientation) {
			PDFC::PDFC($orientation);
		}

		function PrintReport(DBResponse $oResponse, $nID) {
				
			
			$oDBAssetsPPP = new DBAssetsPPPs();
			
			$this->nID = $nID;
			$aAssetPPP = $oDBAssetsPPP->getRecord($nID);
			
			$this->nCreatedDay = substr($aAssetPPP['created_time'],8,2);
			$this->nCreatedMonth = substr($aAssetPPP['created_time'],5,2);
			$this->nCreatedYear = substr($aAssetPPP['created_time'],0,4);			
			
			switch ($aAssetPPP['ppp_type']) {
				case 'enter': $this->sPPPType	= 'Придобиване';break;
				case 'attach': $this->sPPPType	= 'Въвеждане';break;
				case 'waste': $this->sPPPType	= 'Бракуване';break;
			}
			
			$aResult = array();
			$aResult['fields'] = $oResponse->oResult->aFields;
			$aResult['data'] = $oResponse->oResult->aData;
			$aResult['data_attributes'] = $oResponse->oResult->aDataAttributes;

			$this->sSourceType = '';
			$this->sDestType = '';
			$this->sSourceName = '';
			$this->sDestName = '';
			$this->nPPPsCounter = 1;
			
			foreach ($aResult['data'] as $key => &$value) {

				switch ($value['source_type']) {
					case 'client': $this->sSourceType = 'Доставчик';break;
					case 'asset': $this->sSourceType = 'Актив';break;
					case 'person': $this->sSourceType = 'Служител';break;
					case 'storagehouse': $this->sSourceType = 'Склад';break;
					default:$this->sSourceType = '';
				}
				
				switch ($value['dest_type']) {
					case 'asset': $this->sDestType = 'Актив';break;
					case 'person': $this->sDestType = 'Служител';break;
					case 'storagehouse': $this->sDestType = 'Склад';break;
					default: $this->sDestType = '';
				}
				
				if($value['source'] =='Доставчик') $value['source'] = '';
				
				if($this->sSourceName != $value['source'] || $this->sDestName != $value['dest']) {
					
					$this->sSourceName = $value['source'];
					$this->sDestName = $value['dest'];
					
					if(!empty($key))$this->printPPPFooter();
					$this->printPPPHeader();
					$this->Ln( 10 );
					$this->SetFont( 'FreeSans', '', 8 );
				}
				
				
				$this->sSourceName = $value['source'];
				$this->sDestName = $value['dest'];
				$this->sSourcePerson = $value['source_person'];
				$this->sDestPerson = $value['dest_person'];
				$this->nPPPsCounter++;
				
				$this->moveX( 15 );
				$this->Cell(20,'',$value['num']);
				$this->Cell(60,'',$value['name']);
				if($value['enter_date_'] != '00.00.0000 00:00:00') {
					$this->Cell(30,'',$value['enter_date_']);
				} else {
					$this->Cell(30,'','---');
				}
				$this->Cell(30,'',$value['amortization_months_left'],0,0,'R');
				$this->Cell(35,'',round($value['price_left'],2).' лв.',0,0,'R');
				if($this->GetY()>270) {
					$this->AddPage();
					$this->SetY(20);
				} else {
					$this->Ln(4);
				}

			}
			
			$this->printPPPFooter();

			$this->Output('assets_ppp.pdf','I' );
		}
		
		private function printPPPHeader() {
			
			$this->AddPage();
			
			$this->Image( $_SESSION['BASE_DIR'] . '/images/title.png', '', '', 200 );
			
			$this->SetFont('FreeSans', '', 12);

			$this->SetFont( 'FreeSans', '', 18 );
			$this->SetXY( 45, 35 );
			$this->Cell( '', '', "ПРИЕМО-ПРЕДАВАТЕЛЕН ПРОТОКОЛ" );
			
			$this->Ln( 12 );
			$this->moveX(70);
			$this->Cell('','','Активи - '.$this->sPPPType);
			
			
			$this->Ln( 12 );
			
			$this->SetFont( 'FreeSans', '', 12 );
			$this->SetX( 75 );
			$this->Cell( '20', '',	zero_padding($this->nID,6).'_'.$this->nPPPsCounter );
			$this->Cell( '6', '', 'на' );
			$this->Cell( '6', '', 	$this->nCreatedDay );
			$this->Cell( '3', '', '/' );
			$this->Cell( '6', '', 	$this->nCreatedMonth );
			$this->Cell( '3', '', '/' );
			$this->Cell( '12', '', 	$this->nCreatedYear );
			
			$this->Ln( 2 );
			
			$this->moveX( 80 );
			$this->dottedLine( 15 );
			$this->moveX( 6 );
			$this->dottedLine( 6 );
			$this->moveX( 3 );
			$this->dottedLine( 6 );
			$this->moveX( 3 );
			$this->dottedLine( 12 );
			
			$this->Ln( 2 );
			
			$this->SetFont( 'FreeSans', '', 7 );
			$this->SetX( 83 );
			$this->Cell( '18', '', 'номер' );
			$this->Cell( '8', '', 'ден' );
			$this->Cell( '11', '', 'месец' );
			$this->Cell( '10', '', 'година' );
			
			
			// --- ПРЕДАВАЩ ---
			$this->SetY( 62 );
			$this->Ln( 10 );
			$this->moveX( 40 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( 15, '', "ПРЕДАВАЩ" );
			
			$this->Ln( 7 );
			$this->SetFont( 'FreeSans', '', 8 );	
			
			$this->moveX( 10 );
			$this->Cell( 10, '', 'Тип:' );
			$this->Cell( 80, '', $this->sSourceType );
			
			$this->Ln( 2 );
			$this->moveX( 20 );
			$this->dottedLine( 30 );
			
			$this->Ln( 4 );
			
			$this->moveX( 10 );
			$this->Cell( 10, '', 'Име:' );
			$this->Cell( 80, '', $this->sSourceName );
			
			$this->Ln( 2 );
			$this->moveX( 20 );
			$this->dottedLine( 80 );

			// --- ПРИЕМАЩ ---
			$this->SetLeftMargin( 100 );
			
			$this->SetY( 62 );
			$this->Ln( 10 );
			$this->moveX( 40 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( 15, '', "ПРИЕМАЩ" );
			
			$this->Ln( 7 );
			$this->SetFont( 'FreeSans', '', 8 );
			
			$this->moveX( 10 );
			$this->Cell( 10, '', 'Тип:' );
			$this->Cell( 80, '', $this->sDestType );
			
			$this->Ln( 2 );
			$this->moveX( 20 );
			$this->dottedLine( 30 );
			
			$this->Ln( 4 );
			
			$this->moveX( 10 );
			$this->Cell( 10, '', 'Име:' );
			$this->Cell( 80, '', $this->sDestName);
			
			$this->Ln( 2 );
			$this->moveX( 20 );
			$this->dottedLine( 80 );
			
			
			$this->SetLeftMargin( 0 );
			$this->Ln( 10 );
			$this->moveX( 90 );
			$this->SetFont( 'FreeSans', '', 12 );
			$this->Cell( 15, '', "АКТИВИ" );
			
			$this->Line( 10, 102, 200, 102 );
			
			$this->Ln( 10 );
			$this->moveX( 15 );
	
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( 20, '', "Номер" );
			$this->Cell( 60, '', "Име" );
			$this->Cell( 30, '', "Придобит" );
			$this->Cell( 30, '', "Остатъчен срок" );
			$this->Cell( 35, '', "Остатъчна стойност" );
			
			$this->Line( 10, 112, 200, 112 );
		}
		
		private function printPPPFooter() {
			
			$this->SetY( 250 );
			$this->Ln( 10 );
			$this->moveX( 40 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( 15, '', "ПРЕДАЛ" );
			
			$this->Ln( 7 );
			$this->SetFont( 'FreeSans', '', 8 );	
			
			$this->moveX( 10 );
			$this->Cell( 10, '', 'Име:' );
			$this->Cell( 80, '', $this->sSourcePerson);
			
			$this->Ln( 2 );
			$this->moveX( 20 );
			$this->dottedLine(60);
			
			$this->SetLeftMargin( 110 );
		
			$this->SetY( 250 );
			$this->Ln( 10 );
			$this->moveX( 40 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( 15, '', "ПОЛУЧИЛ" );
			
			$this->Ln( 7 );
			$this->SetFont( 'FreeSans', '', 8 );
			
			$this->moveX( 10 );
			$this->Cell( 10, '', 'Име:' );
			$this->Cell( 80, '', $this->sDestPerson );
			
			$this->Ln( 2 );
			$this->moveX( 20 );
			$this->dottedLine( 60 );
			
			$this->SetLeftMargin( 0 );
		}
	}
?>

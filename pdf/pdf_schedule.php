<?php
	include_once('pdf.inc.php');
	
	class SchedulePDF extends PDF
	{
		
		var $aTables = array(
				'Caption'=> array(
									'TextColor'=>0,
									'Background'=>255,
									'BorderColor'=>0,
									'BorderWidth'=>0.2,
									'FontSize'=>8.5,
									'Height'=>8
				),
				'Rows'   => array(
									'Background'=>array( 0=>255, 1 =>255),
									'TextColor'=>0,
									'BorderColor'=>0,
									'BorderWidth'=>0.2,
									'FontSize'=>8,
									'Height'=>6
				),
				'Total'   => array(
									'Background'=>255,
									'TextColor'=>0,
									'BorderColor'=>0,
									'BorderWidth'=>0.2,
									'FontSize'=>8.5,
									'Height'=>8,
									'Date'=>1
				)
			);
			
			//Pavel - 24.09.2008 г.
			// $aTables['Total']['Date'] - 1 ИЗКЛЮЧВА датата на генериране, 0 - ВКЛЮЧВА!!!

        public function __construct($orientation) {
            parent::__construct($orientation);
        }

		function PrintReport( $oResponse, $nPrintetType, $aLegend = array() )
		{
			$aResult = array();
			$aResult['fields'] = $oResponse->oResult->aFields;
			$aResult['data'] = $oResponse->oResult->aData;
			$aResult['data_attributes'] = $oResponse->oResult->aDataAttributes;
			$sDocumentTitle = current($oResponse->oAction->aForms)->aFormElements['divTitle']->mValue;
			$nMonth = current($oResponse->oAction->aForms)->aFormElements['nResultMonth']->aAttributes['value'];
			$nYear = current($oResponse->oAction->aForms)->aFormElements['nResultYear']->aAttributes['value'];
			
			$aDays = array();
			$aWeek = array();
			
			$aWeek[0] = "Н";
			$aWeek[1] = "П";
			$aWeek[2] = "В";
			$aWeek[3] = "С";
			$aWeek[4] = "Ч";
			$aWeek[5] = "П";
			$aWeek[6] = "С";
			
			$nDays = date("t", mktime(12, 12, 12, $nMonth, 12, $nYear)); 
			
			for($i=1; $i<=$nDays; $i++)
			{
				$sWeekDay = $aWeek[date("w", mktime(12, 12, 12, $nMonth, $i, $nYear) )];
				$aDays[0][$i] = $sWeekDay;
			}
			$aResult['data'] = array_merge( $aDays, $aResult['data'] );
			
//			array_pop($aResult['data']);
			unset($aResult['fields']['personName2']);
			unset($aResult['fields']['personName3']);
//			unset($aResult['fields']['planDuration']);
//			unset($aResult['fields']['planMoney']);
			
			if( $nPrintetType == 1 )
			{
				unset($aResult['fields']['planDuration']);
				unset($aResult['fields']['planMoney']);
				unset($aResult['fields']['realMoney']);
				unset($aResult['fields']['realDuration']);
				
				unset( $aResult['fields']['shift_hours'] );
				
				// Пламен
				unset($aResult['fields']['durationTotal']);
				unset($aResult['fields']['duration']);
				unset($aResult['fields']['shifts_count']);
				unset($aResult['fields']['money']);
				
				//Съкващаване на Имената
				foreach( $aResult['data'] as $nKey => &$aValue )
				{
					$aBlah = array();
					$aBlah = explode( " ", $aValue['personName'] );
					
					if( isset( $aBlah[0] ) && isset( $aBlah[2] ) )
					{
						$aValue['personName'] = $aBlah[0]." ".$aBlah[2];
					}
				}
				//Край на Съкващаване на Имената
			}
							
			$this->nTitleFontSize = 12;
			$this->SetTitle( $sDocumentTitle );
			$this->SetFont('FreeSans', '', 8);

			$this->AddPage();
			
			foreach ($aResult['fields'] as $key=>&$val) {
				if (is_numeric($key)) {	
					$val->sTitle = substr($val->sTitle,0,2);
					$val->sCaption = substr($val->sTitle,0,2);
				} else {
					switch ($key) {
						case 'durationTotal':
							$val->sTitle = 'Общо';
							$val->sCaption = 'Общо';
							break;
						case 'duration':
							$val->sTitle = 'Деж';
							$val->sCaption = 'Деж';
							break;
						case 'shifts_count':
							$val->sTitle = 'Смени';
							$val->sCaption = 'Смени';
							break;
						case 'money':
							$val->sTitle = 'Нар';
							$val->sCaption = 'Нар';
							break;
					}
				}
			}
			$aWidths = $this->MakeColWidth( $aResult, $this->_PageWidth - $this->aMargin['left'] - $this->aMargin['right']);
			
			$this->PrintTableHeader( $aResult['fields'], $aWidths );
			$this->PrintTableData( $aResult, $aWidths );
			
			if( $nPrintetType == 1 )
			{
				//Legend
				$this->SetFont( 'FreeSans', '', 10 );
				$this->Ln();
				$this->Cell( $nWidth, 10, "Видове Смени:" );
				
				if( !empty( $aLegend ) )
				{
					$this->SetFont( 'FreeSans', '', 8 );
					foreach( $aLegend as $nKey => $aItem )
					{
						$this->Ln();
						//$this->Cell( $nWidth, 4, sprintf( "%s \t - %s \t ; Начало : %s \t ; Продължителност : %d ч.", $aItem['code'], $aItem['name'], $aItem['shiftFrom'], $aItem['duration'] ) );
						$this->Cell( 200, 5, sprintf( "%s", $aItem['code'] ) );
						$this->moveX( -180 );
						$this->Cell( 200, 5, sprintf( "- %s", $aItem['name'] ) );
						$this->moveX( -170 );
						$this->Cell( 200, 5, sprintf( "Начало: %s", $aItem['shiftFrom'] ) );
						$this->moveX( -165 );
						$this->Cell( 200, 5, sprintf( "Реално отработено време: %d ч.", $aItem['duration'] ) );
					}
				}
				
				$this->Ln();
				//End Legend
			}
			
			//if( $nPrintetType == 1 )
			//{
				$this->Ln();
				
				$this->SetTextColor( $this->aTables['Rows']['TextColor'] );
				$this->SetDrawColor( 255 );
				$this->SetLineWidth( $this->aTables['Rows']['BorderWidth']  );
				$this->SetFont('FreeSans', '', 10);
				$nWidth = $this->_PageWidth - $this->aMargin['right'] - $this->aMargin['left'];
				$this->Cell( $nWidth, 10, 'Утвърдил:____________________/___________________/                           Изготвил:____________________/___________________/', 1, 0, "L", 0 );
			
		//	}
			

			$this->Output($sFileName, $sDestination);
		}
	}
?>
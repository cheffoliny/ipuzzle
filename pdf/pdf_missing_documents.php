<?php
	include_once('pdf.inc.php');
	
	class myPDF extends PDF
	{
        public function __construct($orientation) {
            parent::__construct($orientation);
        }

		function PrintReport( DBResponse $oResponse, $sDocumentTitle, $sFileName = 'doc', $sDestination = '') {

			$sDestination = 'I';
			$aResult = array();
			$aResult['fields'] = $oResponse->oResult->aFields;
			$aResult['data'] = $oResponse->oResult->aData;
			$aResult['data_attributes'] = $oResponse->oResult->aDataAttributes;

			$oField = new DBField();
			$oField->sCaption	= '     #     ';
			$oField->aAttributes = array('align' => 'right');
			
			$aResult['fields'] = array_merge(array('#'=>$oField), $aResult['fields']);

			$this->SetTitle( $sDocumentTitle );
			$this->SetFont('FreeSans', '', 12);

			$aWidths = $this->MakeColWidth( $aResult, $this->_PageWidth - $this->aMargin['left'] - $this->aMargin['right']);
			
			$aWidths['name'] = 55;
			$aWidths['office_name'] = 50;
			$aWidths['documents'] = 157;
			
			$this->AddPage();
			$this->PrintTableHeaderMissingDocuments( $aResult['fields'], $aWidths );
			$this->PrintTableDataMissingDocuments( $aResult, $aWidths );

			$this->Output($sFileName, $sDestination);
		}
		
		function PrintTableHeaderMissingDocuments($aFields, $aWidths) {
			$this->SetFillColor( $this->aTables['Caption']['Background'] );
			$this->SetTextColor( $this->aTables['Caption']['TextColor'] );
			$this->SetDrawColor( $this->aTables['Caption']['BorderColor'] );
			$this->SetLineWidth( $this->aTables['Caption']['BorderWidth']  );
			$this->SetFont('FreeSans', 'B', 10 );

			foreach( $aFields as $sFieldKey => $aField )
			{
				$nWidth = isset($aWidths[$sFieldKey]) ?  $aWidths[$sFieldKey] : 30;
				if($nWidth>0)
				{
					$this->Cell($nWidth, 6, $aField->sCaption,1,0,'C',1);
				}
			}
			// Изтрива остатъка от съдържанието на послдната колона за да се получи margin-right
			$this->SetFillColor( 255 );
			$this->Cell($this->_PageWidth, 6, '',"L",0,'L',1);
			$this->Ln();
		}
		
		function PrintTableDataMissingDocuments($aData, $aWidths) {
			$this->SetTextColor( $this->aTables['Rows']['TextColor'] );
			$this->SetDrawColor( $this->aTables['Rows']['BorderColor'] );
			$this->SetLineWidth( $this->aTables['Rows']['BorderWidth']  );
			$this->SetFont('FreeSans', '',9);
			
			$aTotalFields = array();
			$nColorIndex=0;
			$nRowNum = 0;
			foreach( $aData['data'] as $nKeyRow => $aRow )
			{
				$this->SetFillColor( $this->aTables['Rows']['Background'][$nColorIndex] );
				$nRowNum++;
				
				foreach( $aData['fields'] as $sFieldKey => $aField )
				{
					if( $aData['fields'][$sFieldKey]->sCaption != '...' ) 
					{
						$nWidth = isset($aWidths[$sFieldKey]) ?  $aWidths[$sFieldKey] : 30;
						if($nWidth>0)
						{
							$sCell = isset($aRow[$sFieldKey]) ? $aRow[$sFieldKey] : '';
							if( ! empty($aField -> sImg) ) 
							{
								if( $aRow[$sFieldKey] == '0' || $aRow[$sFieldKey] == '' ) 
								{
									$aRow[$sFieldKey] = '';
								}
								elseif ( $aRow[$sFieldKey] == '1' )
								{
									$aRow[$sFieldKey] = 'да';
								} 
							}
							$sAllign = 'L';
							$sContent 		= $aRow[$sFieldKey];

							// Полето трябва ли да е тотал
							if( isset( $aField->aAttributes['DATA_TOTAL'] ) && $aField->aAttributes['DATA_TOTAL'] )
							{
								if( empty($aTotalFields[$sFieldKey]) )
									$aTotalFields[$sFieldKey] = 0;
								$aTotalFields[$sFieldKey] += $sContent;
							}

							if( !empty( $aField->aAttributes['DATA_FORMAT']) )
							{									
								$this->FormatCell( $aField->aAttributes['DATA_FORMAT'], $sContent, $sAllign );
							}
							
							if( isset($aField->aAttributes['align']) )
								switch( $aField->aAttributes['align'] )
								{
									case 'right'  : $sAllign = 'R'; break;
									case 'center' : $sAllign = 'C'; break; 
								}

							$sBold = '';
							if( isset($aField->aAttributes['style']) )
								if( $aField->aAttributes['style'] == 'font-weight:bold' )
									$sBold = 'B';
				
							if( $sFieldKey == '#' )
								$sContent = $nRowNum;	
							
							if($sFieldKey == "documents") {
								$aDocuments = array();
								$aDocuments = explode(',',$sContent);
								$sContent = $aDocuments[0];
								$i = 1;
								while(isset($aDocuments[$i]) && !empty($aDocuments[$i])) {
									
									$tmp = $sContent.",".$aDocuments[$i];
									$tmp_width = $this->GetStringWidth($tmp);
									if( $tmp_width < $aWidths['documents']*2 ) {
										$sContent = $tmp;
										$i++;
									} else {
										$sContent .= ",";
										break;
									}
								}
								
								$this->Cell($nWidth, 5, $sContent, 1, 0, $sAllign, 1);
								
								
								while(isset($aDocuments[$i]) && !empty($aDocuments[$i])) {
									
									$sContent = $aDocuments[$i++];
									
									
									/*
									while(isset($aDocuments[$i]) && !empty($aDocuments[$i])) {
										$sContent .= ",".$aDocuments[$i++];
									}
									*/
									while(isset($aDocuments[$i]) && !empty($aDocuments[$i])) {
										$tmp = $sContent.",".$aDocuments[$i];
										$tmp_width = $this->GetStringWidth($tmp);
										if( $tmp_width < $aWidths['documents']*2 ) {
											$sContent = $tmp;
											$i++;
										} else {
											$sContent .= ",";
											break;
										}
									}
									
									
									
									$this->SetFillColor( 255 );
									$this->Cell($this->_PageWidth, 5, '',"L",0,'L',1);
									$this->Ln();
									$nColorIndex = ($nColorIndex+1) % count( $this->aTables['Rows']['Background'] );
									
									$this->SetFillColor( $this->aTables['Rows']['Background'][$nColorIndex] );
									
									$this->Cell($aWidths['#'], 5, '', 1, 0, $sAllign, 1);
									$this->Cell($aWidths['name'], 5, '', 1, 0, $sAllign, 1);
									$this->Cell($aWidths['office_name'], 5, '', 1, 0, $sAllign, 1);
									$this->Cell($nWidth, 5, $sContent, 1, 0, $sAllign, 1);
								}
								
							} else {
								
								//$this->SetFont('FreeSans', $sBold,$this->aTables['Rows']['FontSize']);
								$this->Cell($nWidth, 5, $sContent, 1, 0, $sAllign, 1);
							}
						}
					}
				}
				// Изтрива остатъка от съдържанието на послдната колона за да се получи margin-right
				$this->SetFillColor( 255 );
				$this->Cell($this->_PageWidth, 5, '',"L",0,'L',1);
				$this->Ln();
				$nColorIndex = ($nColorIndex+1) % count( $this->aTables['Rows']['Background'] );
			}
			
		}
	}
?>
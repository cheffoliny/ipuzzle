<?php
    require_once('include/ufpdf/fpdf.php');

    if ( !defined("FPDF_FONTPATH") ) {
        define('FPDF_FONTPATH', $_SESSION['BASE_DIR'].'/include/ufpdf/font/');
    }

	
	class PDF extends FPDF
	{
		var $aMargin = array( "left"=>20, "top"=>25,  "right"=>10, "bottom"=>18 );
		var $aHeader = array( "left"=>20, "top"=>10,  "right"=>10 );
		var $aTables = array(
				'Caption'=> array(
									'TextColor'=>0,
									'Background'=>210,
									'BorderColor'=>255,
									'BorderWidth'=>0.2,
									'FontSize'=>5.5,
									'Height'=>5
				),
				'Rows'   => array(
									'Background'=>array( 0=>240, 1 =>255),
									'TextColor'=>0,
									'BorderColor'=>255,
									'BorderWidth'=>0.2,
									'FontSize'=>5.5,
									'Height'=>3.7
				),
				'Total'   => array(
									'Background'=>210,
									'TextColor'=>0,
									'BorderColor'=>255,
									'BorderWidth'=>0.2,
									'FontSize'=>6,
									'Height'=>5,
									'Date'=>0
				)
			);
		var $nTitleFontSize = 18;
		var $_PageWidth;
		var $_PageHeight;

		function __construct($orientation='P')
		{
            parent::__construct($orientation);

			$this->AddFont('FreeSans', '', 'FreeSans.php');
			$this->AddFont('FreeSans', 'B', 'FreeSansB.php');
			$this->SetMargins( $this->aMargin['left'], $this->aMargin['top'], $this->aMargin['right'] );
			$this->_PageWidth  = $orientation == 'P' ? 210 : 297;
			$this->_PageHeight = $orientation == 'P' ? 297 : 210;
			$this->SetAuthor('Telepol Net 2006');
			$this->SetCreator('');
			$this->AliasNbPages();
			#$this->AliasCrPages();
		}

		function Cell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='')
		{
			FPDF::Cell($w,$h,iconv('UTF-8','CP1251',$txt),$border,$ln,$align,$fill,$link);
		}

		function Header() 
		{

			$this->SetFont('FreeSans', 'B', $this->nTitleFontSize);
			$this->Image( $_SESSION['BASE_DIR'].'/images/logo.jpg',$this->aHeader['left'], 9, 0, $this->aMargin['top'] - $this->aHeader['top'] );
			$this->SetY($this->aHeader['top']);
			$this->SetFont('FreeSans', '', 12);
			$this->Cell( 0, $this->aMargin['top'] - $this->aHeader['top'], $this->title, 0, 0, 'R');
			$this->SetDrawColor( 0 );
			$this->SetLineWidth(0.1);
			$this->Line($this->aMargin['left'], $this->aMargin['top'] - 1 , 
						$this->_PageWidth -  $this->aMargin['right'],  $this->aMargin['top'] - 1 );
			$this->Ln($this->aMargin['top']-$this->aHeader['top']+2);
		}

		function Footer() 
		{
			$this->SetFont('FreeSans', '', 6);
			$this->SetDrawColor( 90 );
			$this->SetLineWidth(0.1);
			$this->Line($this->aMargin['left'], $this->_PageHeight - $this->aMargin['bottom'], 
						$this->_PageWidth -  $this->aMargin['right'], $this->_PageHeight  - $this->aMargin['bottom']);
			$this->SetY( - $this->aMargin['bottom']);
			if ( isset($this->aTables['Total']['Date']) && ($this->aTables['Total']['Date'] == 0) ) {
				$this->Cell(0,6,"Разпечатан на ".date("d.m.Y г."),0,0,'R');
			}
			$this->SetY( - $this->aMargin['bottom']);
			$this->Cell(0,6,'Стр. '.$this->PageNo().' / {nb} ',0,0,'C');
			$this->SetFont('FreeSans', '', 4);
			$this->SetY( - $this->aMargin['bottom']);
			$this->Cell(0,6,'Документът е генериран и разпечатан през система "iPuzzle"',0,0,'L');
		}
		
		function MakeColWidth( $aData, $nTableWidth ) 
		{
			$aWidths = array();
			$unfixed_width = 0;
			$fixed_width = 0;
			foreach( $aData['fields'] as $sFieldKey => $aField )
			{
				
				if( $aData['fields'][$sFieldKey]->sCaption != '...' ) 
				{
					if( !empty( $aField->aAttributes['PDF_WIDTH']) )
					// фиксирана ширина
					{
						$aWidths[$sFieldKey] = $aField->aAttributes['PDF_WIDTH'];
						$fixed_width += $aWidths[$sFieldKey];
					}
					else 
					{
						$width = $this->GetStringWidth( $aData['fields'][$sFieldKey]->sCaption );
						$aWidths[$sFieldKey] = isset( $aWidths[$sFieldKey] ) ? ( $width > $aWidths[$sFieldKey] ? $width : $aWidths[$sFieldKey] ) : $width;
						foreach( $aData['data'] as $aRow )
						{
							if( isset($aRow[$sFieldKey]) ) 
							{
								$width = $this->GetStringWidth( $aRow[$sFieldKey] );
								$aWidths[$sFieldKey] = isset( $aWidths[$sFieldKey] ) ? ( $width > $aWidths[$sFieldKey] ? $width : $aWidths[$sFieldKey] ) : $width;
							}
						}
						$unfixed_width += $aWidths[$sFieldKey];
					}
				}
			}

			// За прекалено широки колони се отнемат по 50 % от ширината им
			foreach( $aWidths as $key => $value )
			{
				if( count($aWidths)>3 && ( $value > ($unfixed_width * 0.7) ) )
				{
					$new_value=$value*0.5;
					$aWidths[$key]= $new_value;
					$unfixed_width -= $value - $new_value;
				}
			}

			$delta =  ($nTableWidth-$fixed_width) / $unfixed_width ;

			foreach( $aData['fields'] as $sFieldKey => $aField )
			{
				if( empty( $aField->aAttributes['PDF_WIDTH']) )
					$aWidths[$sFieldKey] = $delta * $aWidths[$sFieldKey];
			}

			return $aWidths;
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

		function FormatCell( $nDateFormat, &$sContent, &$sAllign )
		{
			$sAllign = 'L';

			switch( $nDateFormat )
			{

				case DF_DIGIT : 
								$sContent = sprintf("%01.2f", $sContent);
								$sAllign = 'R';
								break;
				case DF_FLOAT : 
								$sContent = sprintf("%01.3f", $sContent);
								$sAllign = 'R';
								break;
				case DF_NUMBER : 
								$sContent = sprintf("%01.0f", $sContent);
								$sAllign = 'R';
								break;
				case DF_CURRENCY : 
								$sContent = sprintf("%01.2f лв.", $sContent);
								$sAllign = 'R';
								break;
				case DF_DATE : 
								$nTime = mysqlDateToTimestamp( $sContent ); 
								$sContent = !empty($nTime) ? date("d.m.Y", $nTime) : "";
								$sAllign = 'C';
								break;
				case DF_TIME : 
								$nTime = mysqlDateToTimestamp( $sContent ); 
								$sContent = !empty($nTime) ? date("h:i:s", $nTime) : "";
								$sAllign = 'C';
								break;
				case DF_DATETIME : 
								$nTime = mysqlDateToTimestamp( $sContent ); 
								$sContent = !empty($nTime) ? date("d.m.Y h:i:s", $nTime) : "";
								$sAllign = 'C';
								break;
			}
			
		}
		
		function PrintTableData($aData, $aWidths) {
			$this->SetTextColor( $this->aTables['Rows']['TextColor'] );
			$this->SetDrawColor( $this->aTables['Rows']['BorderColor'] );
			$this->SetLineWidth( $this->aTables['Rows']['BorderWidth']  );
			$this->SetFont('FreeSans', '', $this->aTables['Rows']['FontSize']);
			
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
							if( isset( $aData['totals'][$sFieldKey] ) && $aData['totals'][$sFieldKey] )
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
							
							$this->SetFont('FreeSans', $sBold, $this->aTables['Rows']['FontSize']);
							$this->Cell($nWidth, $this->aTables['Rows']['Height'], $sContent, 1, 0, $sAllign, 1);
						}
					}
				}
				// Изтрива остатъка от съдържанието на послдната колона за да се получи margin-right
				$this->SetFillColor( 255 );
				$this->Cell($this->_PageWidth, $this->aTables['Rows']['Height'], '',"L",0,'L',1);
				$this->Ln();
				$nColorIndex = ($nColorIndex+1) % count( $this->aTables['Rows']['Background'] );
			}
			
			// Добавяне на тотали
			$this->SetTextColor( $this->aTables['Total']['TextColor'] );
			$this->SetDrawColor( $this->aTables['Total']['BorderColor'] );
			$this->SetLineWidth( $this->aTables['Total']['BorderWidth']  );
			$this->SetFillColor( $this->aTables['Total']['Background'] );
			$this->SetFont('FreeSans', '', $this->aTables['Total']['FontSize']);

			if( !empty($aTotalFields) )
			{
				foreach( $aData['fields'] as $sFieldKey => $aField )
				{
					$sAllign = 'R';
					$sContent = '';
					$nWidth = isset($aWidths[$sFieldKey]) ?  $aWidths[$sFieldKey] : 30;
					$this->SetFillColor( 255 );
	
					if( $aTotalFields[$sFieldKey]) 
					{
						$sContent = $aTotalFields[$sFieldKey];
						$this->SetFillColor( $this->aTables['Total']['Background'] );
	
						if( !empty( $aField->aAttributes['DATA_FORMAT']) )
						{
							$this->FormatCell( $aField->aAttributes['DATA_FORMAT'], $sContent, $sAllign );
						}
					} 
							
					$this->SetFont('FreeSans', $sBold, $this->aTables['Rows']['FontSize']);
					$this->Cell($nWidth, $this->aTables['Rows']['Height'], $sContent, 1, 0, $sAllign, 1);
				}
				// Изтрива остатъка от съдържанието на послдната колона за да се получи margin-right
				$this->SetFillColor( 255 );
				$this->Cell($this->_PageWidth, $this->aTables['Rows']['Height'], '',"L",0,'L',1);
				$this->Ln();
				$nColorIndex = ($nColorIndex+1) % count( $this->aTables['Rows']['Background'] );
			}
			
			$this->Ln();
			
		}

		function PrintTotal( $aData ) {
			$this->SetTextColor( $this->aTables['Total']['TextColor'] );
			$this->SetDrawColor( $this->aTables['Total']['BorderColor'] );
			$this->SetLineWidth( $this->aTables['Total']['BorderWidth']  );
			$this->SetFillColor( $this->aTables['Total']['Background'] );
			$this->SetFont('FreeSans', '', $this->aTables['Total']['FontSize']);
			$this->Cell(0, $this->aTables['Total']['Height'], "Брой редове в справката : ".count($aData['data'])." реда",1,0,'L',1);
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
	}
?>
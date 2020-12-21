<?php
	include_once('pdf.inc.php');
	include_once('include/bg_slovom.inc.php');
	
	class invocePDF extends PDF
	{
		var $aMargin = array( "left"=>32, "top"=>15, "right"=>10, "bottom"=>18 ); 
		var $aOptions = array(
									'TextColor'		=>array('r'=>0,'g'=>0,'b'=>0),
									'Background'	=>array('title'=>233,'body'=>233),
									'BorderColor'	=>255,
									'BorderWidth'	=>0.55,
									'FontSize'		=>7.2,
									'RowHeight'		=>5,
									'Widths'		=>array('title'=>14,'body'=>48),

									'CaptionHeight' 	=> 4,
									'CaptionFontSize' 	=> 8.5,
									'CaptionTextColor' 	=> 255,
									'CaptionBackground'	=> array('r'=>31,'g'=>28,'b'=>119),
									
									'TitleFontSize'	=> 20,
									'TitleWidth'	=> 45,
									
									'BarcodeWidth'	=> 30,
									'BarcodeHeight'	=> 8,
									
									'NumFontSize'	=> 14,
									
									'LogoWidth'		=> 12,
									
									'WaterStampTextSize'=> 40,
									'WaterStampColor' 	=> 200,

									'Fields'=>array(	// колони на таблицата с услугите/стоките
										'num'			=> array('align'=>'R','width'=>8, 'Caption'=>'№'),
										'description'	=> array('align'=>'L','width'=>10,'Caption'=>'Наименование/шифър на стоката (услугата)'),
										'measure'		=> array('align'=>'C','width'=>15,'Caption'=>'мярка'),
										'count'			=> array('align'=>'R','width'=>15,'Caption'=>'кол'),
										'price'			=> array('align'=>'R','width'=>20,'Caption'=>'ед.цена'),
										'sum'			=> array('align'=>'R','width'=>20,'Caption'=>'дан. основа')
									)
		);

		var $sDocumentHeader;	// Тип на документа в заглавната част на разпечатката : ОРИГИНАЛ, КОПИЕ, ТОВА НЕ Е ФАКТУРА ....
		var $sDocument;			// Вид на документа : ФАКТУРА, ПРОФОРМА, КВИТАНЦИЯ
		var $sInvoceType;		// Тип на фактурата 

		function invocePDF($orientation) 
		{
			PDF::PDF($orientation);
			$this->aMargin['right'] = $this->_PageWidth - $this->aMargin['left'] - 2 * ( $this->aOptions['Widths']['title'] + $this->aOptions['Widths']['body']) - $this->aOptions['TitleWidth'];
		}

		function Header() 
		{
		}
	
		// Печат на HEADER на страницата
		function PrintHeader() 
		{
/*
			$this->SetDrawColor( $this->aOptions['BorderColor'] );
			$this->SetFillColor( $this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b'] );
			$this->SetLineWidth( $this->aOptions['BorderWidth'] );

			$this->SetXY( $this->aMargin['left'] - $this->aOptions['LogoWidth'] - $this->aOptions['BorderWidth'], $this->aMargin['top'] + $this->aOptions['BarcodeHeight']);
			$this->Cell( 	$this->aOptions['LogoWidth'], 
							$this->_PageHeight - $this->aMargin['top'] - $this->aMargin['bottom'] - $this->aOptions['RowHeight'] - $this->aOptions['BarcodeHeight'], 
							'', "TB", 0, 'L',1);
*/
			$this->Image( 	'../images/invoice_logo.jpg', 
							$this->aMargin['left'] - $this->aOptions['LogoWidth'] - $this->aOptions['BorderWidth'], 
							$this->aMargin['top'] + $this->aOptions['BarcodeHeight'], 
							$this->aOptions['LogoWidth'],
							0);		
			$this->SetXY( $this->aMargin['left'], $this->aMargin['top'] );
		}

				
		// Печат на Баркод
		function PrintBarceode( $aForm ){
			$document_num	=	!empty($aForm['document_num']->mValue) ? $aForm['document_num']->mValue : 
								( !empty($aForm['document_num']->aAttributes['value']) ? $aForm['document_num']->aAttributes['value'] : '' );

			$path = substr($_SERVER['HTTP_REFERER'], 0, strpos($_SERVER['HTTP_REFERER'], 'page'));
			$path = rtrim($path, '/');
			$sContent = sprintf("%s/include/barcodes/barcode.php?code=code128&pcode=%s&text=0&height=44&resolution=1&img_type=jpg", $path, $document_num );
			$this->Image( 	$sContent, 
							$this->aMargin['left'] + $this->aOptions['TitleWidth'] + ($this->aOptions['Widths']['title'] + $this->aOptions['Widths']['body']) * 2 - $this->aOptions['BarcodeWidth'] - $this->aOptions['BorderWidth'], 
							$this->aMargin['top'], 
							$this->aOptions['BarcodeWidth'], 
							$this->aOptions['BarcodeHeight'], 
							'jpg' );		
			$this->Ln($this->aOptions['BarcodeHeight']);
		}

		// Печат на ред от таблица с 2 колони title->body
		function PrintRow( $nLeft, $sTitle, $sBody, $nTitleWidth, $nBodyWidth, $body_align="L" ){
			$this->SetX( $nLeft );
			$this->SetFillColor( $this->aOptions['Background']['title'] );
			$this->Cell( $nTitleWidth, $this->aOptions['RowHeight'], $sTitle, "LTB", 0, 'L', 1);
			$this->SetFillColor( $this->aOptions['Background']['body'] );
			$this->Cell( $nBodyWidth,  $this->aOptions['RowHeight'], $sBody, "RTB", 0,  $body_align, 1);
			$this->Ln();
		}

		// Печат на панела с информация за клиента
		function PrintClient( $aForm ) {
			//debug($aForm);
			$client_name	=	!empty($aForm['client_name']->mValue) ? $aForm['client_name']->mValue : 
								( !empty($aForm['client_name']->aAttributes['value']) ? $aForm['client_name']->aAttributes['value'] : '' );
			$client_address	=	!empty($aForm['client_address']->mValue) ? $aForm['client_address']->mValue : 
								( !empty($aForm['client_address']->aAttributes['value']) ? $aForm['client_address']->aAttributes['value'] : '' );
			$client_EIN		=	!empty($aForm['client_EIN']->mValue) ? $aForm['client_EIN']->mValue : 
								( !empty($aForm['client_EIN']->aAttributes['value']) ? $aForm['client_EIN']->aAttributes['value'] : '' );
			$client_EINDDS	=	!empty($aForm['client_EINDDS']->mValue) ? $aForm['client_EINDDS']->mValue : 
								( !empty($aForm['client_EINDDS']->aAttributes['value']) ? $aForm['client_EINDDS']->aAttributes['value'] : '' );
			$client_mol		=	!empty($aForm['client_mol']->mValue) ? $aForm['client_mol']->mValue : 
								( !empty($aForm['client_mol']->aAttributes['value']) ? $aForm['client_mol']->aAttributes['value'] : '' );
			$client_key_word	=	!empty($aForm['client_key_word']->mValue) ? $aForm['client_key_word']->mValue : 
								( !empty($aForm['client_key_word']->aAttributes['value']) ? $aForm['client_key_word']->aAttributes['value'] : '' );			
			
			if( !empty($client_key_word) )
				$client_name = sprintf( "%s [%s]", $client_name, $client_key_word );
			
			$x= $this->GetX() ;
			$y= $this->GetY();
			
			// заглавие на панела
			$this->SetTextColor( $this->aOptions['CaptionTextColor'] );
			$this->SetDrawColor( $this->aOptions['BorderColor'] );
			$this->SetFillColor( $this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b'] );
			$this->SetLineWidth( $this->aOptions['BorderWidth'] );
			$this->SetFont('FreeSans', 'B', $this->aOptions['CaptionFontSize']);
			$this->Cell( $this->aOptions['Widths']['title']+$this->aOptions['Widths']['body'],  $this->aOptions['CaptionHeight'],' получател:', 1, 0, 'L', 1);
			
			// съдържания на панела
			$this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
			$this->SetDrawColor( $this->aOptions['BorderColor'] );
			$this->SetLineWidth( $this->aOptions['BorderWidth']  );
			$this->Ln();
			
			$nAdressFontSize = $this->aOptions['FontSize'];
			$this->PorccessText( $client_address, $sAddress1, $sAddress2, $nAdressFontSize );

			$nClientFontSize = $this->aOptions['FontSize'];
			$this->PorccessText( $client_name, $sClient1, $sClient2, $nClientFontSize );
			
			$this->SetFont('FreeSans', '', $nClientFontSize);
			$this->PrintRow( $x, 'Име', $sClient1, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
			$this->PrintRow( $x, '', $sClient2, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
			$this->SetFont('FreeSans', '', $nAdressFontSize);
			$this->PrintRow( $x, 'Адрес', $sAddress1, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
			$this->PrintRow( $x, '', $sAddress2, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
			$this->SetFont('FreeSans', '', $this->aOptions['FontSize']);
			$this->PrintRow( $x, 'ИН ДДС', $client_EINDDS, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
			$this->PrintRow( $x, 'ИН/ЕГН', $client_EIN, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
			$this->PrintRow( $x, 'МОЛ', $client_mol, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );

			$this->SetXY( $x, $y );
		}

		// Печат на панела с информация за доставчика
		function PrintDeliver( $aForm ) {
			//debug($aForm);
			$deliver_name	=	!empty($aForm['deliver_name']->mValue) ? $aForm['deliver_name']->mValue : 
								( !empty($aForm['deliver_name']->aAttributes['value']) ? $aForm['deliver_name']->aAttributes['value'] : '' );
			$deliver_address=	!empty($aForm['deliver_address']->mValue) ? $aForm['deliver_address']->mValue : 
								( !empty($aForm['deliver_address']->aAttributes['value']) ? $aForm['deliver_address']->aAttributes['value'] : '' );
			$deliver_EIN	=	!empty($aForm['deliver_EIN']->mValue) ? $aForm['deliver_EIN']->mValue : 
								( !empty($aForm['deliver_EIN']->aAttributes['value']) ? $aForm['deliver_EIN']->aAttributes['value'] : '' );
			$deliver_EINDDS	=	!empty($aForm['deliver_EINDDS']->mValue) ? $aForm['deliver_EINDDS']->mValue : 
								( !empty($aForm['deliver_EINDDS']->aAttributes['value']) ? $aForm['deliver_EINDDS']->aAttributes['value'] : '' );
			$deliver_mol	=	!empty($aForm['deliver_mol']->mValue) ? $aForm['deliver_mol']->mValue : 
								( !empty($aForm['deliver_mol']->aAttributes['value']) ? $aForm['deliver_mol']->aAttributes['value'] : '' );
			$related_doc	=	!empty($aForm['related_doc']->mValue) ? $aForm['related_doc']->mValue : 
								( !empty($aForm['related_doc']->aAttributes['value']) ? $aForm['related_doc']->aAttributes['value'] : '' );
			$deliver_bank_name =!empty($aForm['deliver_bank_name']->mValue) ? $aForm['deliver_bank_name']->mValue : 
								( !empty($aForm['deliver_bank_name']->aAttributes['value']) ? $aForm['deliver_bank_name']->aAttributes['value'] : '' );
			$deliver_bank_account=	!empty($aForm['deliver_bank_account']->mValue) ? $aForm['deliver_bank_account']->mValue : 
								( !empty($aForm['deliver_bank_account']->aAttributes['value']) ? $aForm['deliver_bank_account']->aAttributes['value'] : '' );
			$deliver_bank_code	=	!empty($aForm['deliver_bank_code']->mValue) ? $aForm['deliver_bank_code']->mValue : 
								( !empty($aForm['deliver_bank_code']->aAttributes['value']) ? $aForm['deliver_bank_code']->aAttributes['value'] : '' );
			$deliver_dds_bank_name =!empty($aForm['deliver_dds_bank_name']->mValue) ? $aForm['deliver_dds_bank_name']->mValue : 
								( !empty($aForm['deliver_dds_bank_name']->aAttributes['value']) ? $aForm['deliver_dds_bank_name']->aAttributes['value'] : '' );
			$deliver_dds_bank_account=	!empty($aForm['deliver_dds_bank_account']->mValue) ? $aForm['deliver_dds_bank_account']->mValue : 
								( !empty($aForm['deliver_dds_bank_account']->aAttributes['value']) ? $aForm['deliver_dds_bank_account']->aAttributes['value'] : '' );
			$deliver_dds_bank_code	=	!empty($aForm['deliver_dds_bank_code']->mValue) ? $aForm['deliver_dds_bank_code']->mValue : 
								( !empty($aForm['deliver_dds_bank_code']->aAttributes['value']) ? $aForm['deliver_dds_bank_code']->aAttributes['value'] : '' );
			
		
			$x = $this->aMargin['left'] + $this->aOptions['Widths']['title'] + $this->aOptions['Widths']['body'] + $this->aOptions['TitleWidth'];
			$y = $this->GetY();
			$this->SetXY( $x, $y );
			
			// заглавие на панела
			$this->SetTextColor( $this->aOptions['CaptionTextColor'] );
			$this->SetDrawColor( $this->aOptions['BorderColor'] );
			$this->SetFillColor( $this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']  );
			$this->SetLineWidth( $this->aOptions['BorderWidth'] );
			$this->SetFont('FreeSans', 'B', $this->aOptions['CaptionFontSize']);
			$this->Cell( $this->aOptions['Widths']['title']+$this->aOptions['Widths']['body'],  $this->aOptions['CaptionHeight'], ' доставчик:', 1, 0, 'L', 1);
			
			// съдържания на панела
			$this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
			$this->SetDrawColor( $this->aOptions['BorderColor'] );
			$this->SetLineWidth( $this->aOptions['BorderWidth']  );
			$this->SetFont('FreeSans', '', $this->aOptions['FontSize']);
			$this->Ln();

			$this->PrintRow( $x, 'Име', $deliver_name, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
			$this->PrintRow( $x, 'Адрес ', $deliver_address, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
			$this->PrintRow( $x, 'IBAN', $deliver_bank_account, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
			$this->PrintRow( $x, 'BIC', $deliver_bank_code." ".$deliver_bank_name, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
			$this->PrintRow( $x, 'ИН ДДС', $deliver_EINDDS, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
			$this->PrintRow( $x, 'ИН', $deliver_EIN, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
			$this->PrintRow( $x, 'МОЛ', $deliver_mol, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );

			if( !empty($related_doc) )
			{
				$this->SetFont('FreeSans', '', $this->aOptions['FontSize'] -1 );
				$this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
				$this->SetFillColor( 255 );
				$this->Cell( 50,  $this->aOptions['CaptionHeight'], $related_doc, 1, 0, 'L', 1);
				$this->Ln();
			}
		}

		// Печат на панела с информация за фактурата (номер, дата, тип ...)
		function PrintInvoceTitle( $aForm ) {
			$invoice_date	=	!empty($aForm['invoice_date']->mValue) ? $aForm['invoice_date']->mValue : 
								( !empty($aForm['invoice_date']->aAttributes['value']) ? $aForm['invoice_date']->aAttributes['value'] : '' );
			$document_num	=	!empty($aForm['document_num']->mValue) ? $aForm['document_num']->mValue : 
								( !empty($aForm['document_num']->aAttributes['value']) ? $aForm['document_num']->aAttributes['value'] : '' );
			$document_city			=	!empty($aForm['document_city']->mValue) ? $aForm['document_city']->mValue : 
								( !empty($aForm['document_city']->aAttributes['value']) ? $aForm['document_city']->aAttributes['value'] : '' );
		
			$doc_len=strlen($document_num);
			$document_num = sprintf("%010s", $document_num);

			$x = $this->aMargin['left'] + $this->aOptions['Widths']['title'] + $this->aOptions['Widths']['body'];
			$y	= $this->GetY();
			$this->SetXY( $x, $y );
			
			// заглавие на панела
			$this->SetTextColor( $this->aOptions['CaptionTextColor'] );
			$this->SetDrawColor( $this->aOptions['BorderColor'] );
			$this->SetFillColor( $this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']  );
			$this->SetLineWidth( $this->aOptions['BorderWidth'] );
			$this->SetFont('FreeSans', 'B', $this->aOptions['TitleFontSize'] );
			$this->Cell( $this->aOptions['TitleWidth'],  $this->aOptions['CaptionHeight'] + $this->aOptions['RowHeight'], $this->sDocument, 1, 0, 'C', 1);
			$this->Ln();
			
			$this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
			$this->SetDrawColor( $this->aOptions['BorderColor'] );
			$this->SetFillColor( $this->aOptions['BorderColor'] );
			$this->SetLineWidth( $this->aOptions['BorderWidth'] );
			
			$this->SetX( $x );
			$this->SetFont('FreeSans', 'B',  $this->aOptions['NumFontSize'] );
			$this->Cell( $this->aOptions['TitleWidth'],  2*$this->aOptions['RowHeight'], "№ ".$document_num, 1, 0, 'C', 1);
			$this->Ln();

			$this->SetX( $x );
			$this->SetFillColor( $this->aOptions['Background']['body'] );
			$this->SetFont('FreeSans', 'B',  $this->aOptions['CaptionFontSize'] );
			$this->Cell( $this->aOptions['TitleWidth'],  $this->aOptions['RowHeight'], "дата: ".$invoice_date, 1, 0, 'C', 1);
			$this->Ln();

			$this->SetX( $x );
			$this->Cell( $this->aOptions['TitleWidth'],  $this->aOptions['RowHeight'], "място: ".$document_city, 1, 0, 'C', 1);
			$this->Ln();
			$this->Ln();

			$this->SetX( $x );
			$this->SetFillColor( $this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b'] );
			$this->SetTextColor( $this->aOptions['CaptionTextColor'] );
			$this->SetFont('FreeSans', 'B',  $this->aOptions['CaptionFontSize'] );
			$this->Cell( $this->aOptions['TitleWidth'],  $this->aOptions['RowHeight'], $this->sInvoceType, 1, 0, 'C', 1);
			$this->SetXY( $x, $y );
		}

		// Печат на заглавния ред на таблицата с цени
		function PrintTableHeader() {
			$this->SetTextColor( $this->aOptions['CaptionTextColor'] );
			$this->SetDrawColor( $this->aOptions['BorderColor'] );
			$this->SetFillColor( $this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']  );
			$this->SetLineWidth( $this->aOptions['BorderWidth']  );
			$this->SetFont('FreeSans', '',  $this->aOptions['CaptionFontSize'] );

			$this->aOptions['Fields']['description']['width']  =	2 * $this->aOptions['Widths']['title'] + 2 * $this->aOptions['Widths']['body'] + $this->aOptions['TitleWidth']
																	- $this->aOptions['Fields']['num']['width']
																	- $this->aOptions['Fields']['measure']['width']
																	- $this->aOptions['Fields']['count']['width']
																	- $this->aOptions['Fields']['price']['width']
																	- $this->aOptions['Fields']['sum']['width'];

			foreach( $this->aOptions['Fields'] as $aField )
			{
				$this->Cell($aField['width'], $this->aOptions['CaptionHeight'], $aField['Caption'],0,0,'C',1);
			}
			$this->Ln();
		}
		
		// Печат на цените по фактурата
		function PrintTableData( $aResult ) {
			$aData = $aResult['data'];
			$aForm = $aResult['form_elements'];
			
			$_currency		=	!empty($aForm['currency']->mValue) ? $aForm['currency']->mValue : 
							   (!empty($aForm['currency']->aAttributes['value']) ? $aForm['currency']->aAttributes['value'] : 'лв');
			
			// Печат на воден знак - ОРИГИНАЛ, КОПИЕ
			$oldY = $this->GetY();
			$this->SetDrawColor( $this->aOptions['BorderColor'] );
			$this->SetLineWidth( $this->aOptions['BorderWidth']  );
			$this->SetFillColor( $this->aOptions['Background']['body'] );
			for ($i=0; $i<10; $i++ )
			{
				// Рисува фона на първите 10 реда
				foreach( $this->aOptions['Fields'] as $sFieldKey => $aField )
				{
					$this->Cell( $aField['width'], $this->aOptions['RowHeight'], '', 1 , 0, 'L', 1 );
				}
				$this->Ln();
			}

			$this->SetY($oldY);
			$this->SetTextColor( $this->aOptions['WaterStampColor'] );
			$this->SetFont('FreeSans', 'B', $this->aOptions['WaterStampTextSize']);
			$this->Cell( 2 * $this->aOptions['Widths']['title'] + 2 * $this->aOptions['Widths']['body'] + $this->aOptions['TitleWidth'], 10 * $this->aOptions['RowHeight'], $this->sDocumentHeader,0,0,'C', 0);
			$this->SetY($oldY);
													
								
			$this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
			$this->SetDrawColor( $this->aOptions['BorderColor'] );
			$this->SetFillColor( $this->aOptions['Background']['body'] );
			$this->SetLineWidth( $this->aOptions['BorderWidth'] );
			$this->SetFont('FreeSans', '', $this->aOptions['FontSize']);
			
			$nRow=0;
			$nCounter=0;
			
			foreach( $aData as $aRow )
			{
				if( !$aRow['is_group'] )
				{
					$aRow['num'] = strval(++$nCounter);

					foreach( $this->aOptions['Fields'] as $sFieldKey => $aField )
					{
						$sCell = $sFieldKey=='sum' ? $aRow[$sFieldKey] : $aRow[$sFieldKey];
						if( $nRow < 10 )
						{
							$this->Cell( $aField['width'], $this->aOptions['RowHeight'], strval($sCell), 0 , 0, $aField['align'], 0 );
						}
						else 
						{
							$this->Cell( $aField['width'], $this->aOptions['RowHeight'], strval($sCell), 1 , 0, $aField['align'], 1 );
						}
					}
				}
				else
				{
					$x = $this->GetX();
					if( $nRow > 9 )
						foreach( $this->aOptions['Fields'] as $sFieldKey => $aField )
							$this->Cell( $aField['width'], $this->aOptions['RowHeight'], '', 1 , 0, $aField['align'], 1 );

					$this->SetX($x+$this->aOptions['Fields']['num']['width']);
					
					$this->Cell( 0, $this->aOptions['RowHeight'], $aRow['description'], 0, 0, $this->aOptions['Fields']['description']['align'], 0);
				}
				$nRow ++;
				$this->Ln();
			}

			for ($i=$nRow; $i<10; $i++ )
			{
				$this->Ln($this->aOptions['RowHeight']);
			}
		}

		// Печат на долния панел с обобщени данни за фактурата
		function PrintTotal($aForm) {

			$invoice_date	=	!empty($aForm['invoice_date']->mValue) ? $aForm['invoice_date']->mValue : 
								( !empty($aForm['invoice_date']->aAttributes['value']) ? $aForm['invoice_date']->aAttributes['value'] : '' );
			$total_sum		=	!empty($aForm['total_sum']->mValue) ? $aForm['total_sum']->mValue :
								( !empty($aForm['total_sum']->aAttributes['value']) ? $aForm['total_sum']->aAttributes['value'] : '0.00' );
			$total_sum_without_dds=	!empty($aForm['total_sum_without_dds']->mValue) ? $aForm['total_sum_without_dds']->mValue : 
								( !empty($aForm['total_sum_without_dds']->aAttributes['value']) ? $aForm['total_sum_without_dds']->aAttributes['value'] : '0.00' );
			$sum_dds		=	!empty($aForm['sum_dds']->mValue) ? $aForm['sum_dds']->mValue :
								( !empty($aForm['sum_dds']->aAttributes['value']) ? $aForm['sum_dds']->aAttributes['value'] : '0.00' );
			$client_p_name	=	!empty($aForm['client_p_name']->mValue) ? $aForm['client_p_name']->mValue : 
								( !empty($aForm['client_p_name']->aAttributes['value']) ? $aForm['client_p_name']->aAttributes['value'] : '' );
			$client_p_lk	=	!empty($aForm['client_p_lk']->mValue) ? $aForm['client_p_lk']->mValue : 
								( !empty($aForm['client_p_lk']->aAttributes['value']) ? $aForm['client_p_lk']->aAttributes['value'] : '' );
			$client_p_year	=	!empty($aForm['client_p_year']->mValue) ? $aForm['client_p_year']->mValue : 
								( !empty($aForm['client_p_year']->aAttributes['value']) ? $aForm['client_p_year']->aAttributes['value'] : '' );
			$client_p_num	=	!empty($aForm['client_p_num']->mValue) ? $aForm['client_p_num']->mValue : 
								( !empty($aForm['client_p_num']->aAttributes['value']) ? $aForm['client_p_num']->aAttributes['value'] : '' );
			$_dds			=	!empty($aForm['sum_dds_percent']->mValue) ? $aForm['sum_dds_percent']->mValue : 
								( !empty($aForm['sum_dds_percent']->aAttributes['value']) ? $aForm['sum_dds_percent']->aAttributes['value'] : '0' );
			$_currency		=	!empty($aForm['currency']->mValue) ? $aForm['currency']->mValue : 
								( !empty($aForm['currency']->aAttributes['value']) ? $aForm['currency']->aAttributes['value'] : 'лв' );
			$is_bank		=	!empty($aForm['is_bank']->mValue) ? $aForm['is_bank']->mValue : 
								( !empty($aForm['is_bank']->aAttributes['value']) ? $aForm['is_bank']->aAttributes['value'] : '0' );
			$created_user	=	!empty($aForm['created_user_name']->mValue) ? $aForm['created_user_name']->mValue : 
								( !empty($aForm['created_user_name']->aAttributes['value']) ? $aForm['created_user_name']->aAttributes['value'] : '' );
			$created_user_code	=	!empty($aForm['created_user_code']->mValue) ? "[ ".$aForm['created_user_code']->mValue." ]" : 
								( !empty($aForm['created_user_code']->aAttributes['value']) ? "[ ". $aForm['created_user_code']->aAttributes['value']." ]"  : '' );
			$deliver_bank_name =!empty($aForm['deliver_bank_name']->mValue) ? $aForm['deliver_bank_name']->mValue : 
								( !empty($aForm['deliver_bank_name']->aAttributes['value']) ? $aForm['deliver_bank_name']->aAttributes['value'] : '' );
			$deliver_bank_account=	!empty($aForm['deliver_bank_account']->mValue) ? $aForm['deliver_bank_account']->mValue : 
								( !empty($aForm['deliver_bank_account']->aAttributes['value']) ? $aForm['deliver_bank_account']->aAttributes['value'] : '' );
			$deliver_bank_code	=	!empty($aForm['deliver_bank_code']->mValue) ? $aForm['deliver_bank_code']->mValue : 
								( !empty($aForm['deliver_bank_code']->aAttributes['value']) ? $aForm['deliver_bank_code']->aAttributes['value'] : '' );
			$deliver_dds_bank_name =!empty($aForm['deliver_dds_bank_name']->mValue) ? $aForm['deliver_dds_bank_name']->mValue : 
								( !empty($aForm['deliver_dds_bank_name']->aAttributes['value']) ? $aForm['deliver_dds_bank_name']->aAttributes['value'] : '' );
			$deliver_dds_bank_account=	!empty($aForm['deliver_dds_bank_account']->mValue) ? $aForm['deliver_dds_bank_account']->mValue : 
								( !empty($aForm['deliver_dds_bank_account']->aAttributes['value']) ? $aForm['deliver_dds_bank_account']->aAttributes['value'] : '' );
			$deliver_dds_bank_code	=	!empty($aForm['deliver_dds_bank_code']->mValue) ? $aForm['deliver_dds_bank_code']->mValue : 
								( !empty($aForm['deliver_dds_bank_code']->aAttributes['value']) ? $aForm['deliver_dds_bank_code']->aAttributes['value'] : '' );
			$_currency_100	=   ($_currency=='лв.' || $_currency=='лв') ? 'ст' : 'цента'; 
			$bank			=	$is_bank == 0 ? "в брой" : "с платежно нареждане" ;
			
			$is_post		=	!empty($aForm['is_post']->mValue) ? $aForm['is_post']->mValue : 
								( !empty($aForm['is_post']->aAttributes['value']) ? $aForm['is_post']->aAttributes['value'] : 0 );

			$width =	  $this->aOptions['Fields']['num']['width']
						+ $this->aOptions['Fields']['description']['width'] 
						+ $this->aOptions['Fields']['measure']['width']
						+ $this->aOptions['Fields']['count']['width'];

			$x = $width + $this->aMargin['left'];

			$this->SetTextColor( $this->aOptions['CaptionTextColor'] );
			$this->SetDrawColor( $this->aOptions['BorderColor'] );
			$this->SetFillColor( $this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']  );
			$this->SetLineWidth( $this->aOptions['BorderWidth'] );
			$this->SetFont('FreeSans', 'B', $this->aOptions['CaptionFontSize'] );
			$this->Cell( 	2 * $this->aOptions['Widths']['title'] + 2 * $this->aOptions['Widths']['body'] + $this->aOptions['TitleWidth'],  
							$this->aOptions['CaptionHeight'], ' обща стойност (словом): ', 1, 0, 'L', 1);
			$this->Ln();

			$this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
			$this->SetDrawColor( $this->aOptions['BorderColor']  );
			$this->SetLineWidth( $this->aOptions['BorderWidth'] );
			$this->SetFont('FreeSans', '', $this->aOptions['FontSize'] );
			$this->SetFillColor( $this->aOptions['Background']['body']  );
			$this->Cell( $width,  $this->aOptions['RowHeight'], slovom ($total_sum, $_currency, $_currency_100), 1, 0, 'L', 1);
			$this->PrintRow( $x, "Дан. основа", $total_sum_without_dds.' '.$_currency, $this->aOptions['Fields']['price']['width'], $this->aOptions['Fields']['sum']['width'],'R' );
			
			$this->Cell( $width,  $this->aOptions['RowHeight'], '', 1, 0, 'L', 1);
			$this->PrintRow( $x, "ДДС ".$_dds." %", $sum_dds.' '.$_currency, $this->aOptions['Fields']['price']['width'], $this->aOptions['Fields']['sum']['width'],'R' );

			$sNote = sprintf("Основание за прилагане на нулева ставка - чл.%s от ЗДДС", 
							$_dds > 0 	? 
							"..." 		:
							(
								$is_post ==1 	?
								"49 ал.2"		: 
								"30" 
							)
			);
			
			$this->Cell( $width,  $this->aOptions['RowHeight'], $sNote, 1, 0, 'L', 1);
			$this->PrintRow( $x, "Обща стойност", $total_sum.' '.$_currency, $this->aOptions['Fields']['price']['width'], $this->aOptions['Fields']['sum']['width'],'R' );

			$this->SetDrawColor( $this->aOptions['BorderColor'] );
			$this->SetFillColor( $this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b'] );
			$this->SetLineWidth( $this->aOptions['BorderWidth'] );
			$this->Cell( $width,  $this->aOptions['RowHeight'], "", 1, 0, 'L', 1);
			
			$width_num = $this->aOptions['Fields']['price']['width'] / 4;
			$width_account = 3*$this->aOptions['Fields']['price']['width'] / 8;
			$width_credit = $this->aOptions['Fields']['sum']['width'] / 4;
			
			$this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
			$this->SetFillColor( $this->aOptions['Background']['body'] );
			$this->SetDrawColor( $this->aOptions['BorderColor']  );

			$this->SetFont('FreeSans', '', $this->aOptions['FontSize'] - 3);
			$this->SetLineWidth( $this->aOptions['BorderWidth'] / 2 );
			$y = $this->GetY();

			$this->SetXY( $this->aMargin['left']+$width, $y );
			$this->Cell( $width_num,  $this->aOptions['RowHeight'], "№", 1, 0, 'C', 1);
			$this->Cell( $width_account*2,  $this->aOptions['RowHeight'] / 2, "ДЕБИТ", 1, 0, 'C', 1);

			$this->SetXY($this->aMargin['left']+$width+$width_num, $y + $this->aOptions['RowHeight'] / 2 );
			$this->Cell( $width_account,  $this->aOptions['RowHeight'] / 2, "c/ka", 1, 0, 'C', 1);
			$this->Cell( $width_account,  $this->aOptions['RowHeight'] / 2, "ан.c/ka", 1, 0, 'C', 1);
			$this->SetXY( $this->aMargin['left']+$width+$this->aOptions['Fields']['price']['width'], $y );
			$this->Cell( $width_credit*2,  $this->aOptions['RowHeight'] / 2, "КРЕДИТ", 1, 0, 'C', 1);
			$this->SetXY( $this->aMargin['left']+$width+$this->aOptions['Fields']['price']['width'], $y+$this->aOptions['RowHeight'] / 2 );
			$this->Cell( $width_credit,  $this->aOptions['RowHeight'] / 2, "c/ka", 1, 0, 'C', 1);
			$this->Cell( $width_credit,  $this->aOptions['RowHeight'] / 2, "ан.c/ka", 1, 0, 'C', 1);
			$this->SetXY( $this->aMargin['left']+$width+$this->aOptions['Fields']['price']['width']+$width_credit*2, $y );
			$this->Cell( $width_credit*2,  $this->aOptions['RowHeight'], "СУМА", 1, 0, 'C', 1);
			$this->Ln();
			
			$this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
			$this->SetFillColor( $this->aOptions['Background']['body'] );
			$this->SetDrawColor( $this->aOptions['BorderColor']  );
			$this->SetLineWidth( $this->aOptions['BorderWidth'] );
			$this->SetFont('FreeSans', '', $this->aOptions['FontSize'] );
			$this->Cell( $width / 2,  $this->aOptions['RowHeight'], "Дата на данъчното събитие : ".$invoice_date, 1, 0, 'L', 1);
			$this->Cell( $width / 2,  $this->aOptions['RowHeight'], "Форма на плащане : ", 1, 0, 'L', 1);

			$this->SetLineWidth( $this->aOptions['BorderWidth'] / 2 );
			$this->SetDrawColor( $this->aOptions['Background']['body'] );
			$this->SetFillColor( 255 );
			$this->Cell( $width_num,  $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
			$this->Cell( $width_account,  $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
			$this->Cell( $width_account,  $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
			$this->Cell( $width_credit,  $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
			$this->Cell( $width_credit,  $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
			$this->Cell( $width_credit*2,  $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
			$this->Ln();
			
			$this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
			$this->SetFillColor( $this->aOptions['Background']['body'] );
			$this->SetDrawColor( $this->aOptions['BorderColor']  );
			$this->SetLineWidth( $this->aOptions['BorderWidth'] );
			$this->SetFont('FreeSans', '', $this->aOptions['FontSize'] );
			$this->Cell( $width / 2,  $this->aOptions['RowHeight'], "Получател (име, фамилия) : ", 1, 0, 'L', 1);
			$this->Cell( $width / 2,  $this->aOptions['RowHeight'], "                        ".$bank, 1, 0, 'L', 1);
			
			$this->SetLineWidth( $this->aOptions['BorderWidth'] / 2 );
			$this->SetDrawColor( $this->aOptions['Background']['body'] );
			$this->SetFillColor( 255 );
			$this->Cell( $width_num,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
			$this->Cell( $width_account,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
			$this->Cell( $width_account,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
			$this->Cell( $width_credit,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
			$this->Cell( $width_credit,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
			$this->Cell( $width_credit*2,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
			$this->Ln();

			$this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
			$this->SetFillColor( $this->aOptions['Background']['body'] );
			$this->SetDrawColor( $this->aOptions['BorderColor']  );
			$this->SetLineWidth( $this->aOptions['BorderWidth'] );
			$this->SetFont('FreeSans', '', $this->aOptions['FontSize'] );
			$this->Cell( $width / 2,  $this->aOptions['RowHeight'], $client_p_name, 1, 0, 'L', 1);
			$this->Cell( $width / 2,  $this->aOptions['RowHeight'], "Съставил : ".$created_user, 1, 0, 'L', 1);

			$this->SetLineWidth( $this->aOptions['BorderWidth'] / 2 );
			$this->SetDrawColor( $this->aOptions['Background']['body'] );
			$this->SetFillColor( 255 );
			$this->Cell( $width_num,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
			$this->Cell( $width_account,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
			$this->Cell( $width_account,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
			$this->Cell( $width_credit,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
			$this->Cell( $width_credit,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
			$this->Cell( $width_credit*2,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
			$this->Ln();
			
			$this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
			$this->SetFillColor( $this->aOptions['Background']['body'] );
			$this->SetDrawColor( $this->aOptions['BorderColor']  );
			$this->SetLineWidth( $this->aOptions['BorderWidth'] );
			$this->SetFont('FreeSans', '', $this->aOptions['FontSize'] );
			$this->Cell( $width / 2,  $this->aOptions['RowHeight'], "                        Подпис : ", 1, 0, 'L', 1);
			$this->Cell( $width / 2,  $this->aOptions['RowHeight'], "                        Подпис : ".$created_user_code, 1, 0, 'L', 1);
			
			$this->SetFont('FreeSans', '', $this->aOptions['FontSize'] - 3);
			$this->SetLineWidth( $this->aOptions['BorderWidth'] / 2 );
			$this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
			$this->SetDrawColor( $this->aOptions['BorderColor'] );
			$width_other = ( $this->aOptions['Fields']['price']['width'] + $this->aOptions['Fields']['sum']['width'] ) / 2;
			$this->Cell( $width_other, $this->aOptions['RowHeight'], "статия", 1, 0, 'L', 1 );
			$this->Cell( $width_other, $this->aOptions['RowHeight'], "сч-л", 1, 0, 'L', 1 );
			$this->Ln();
			$this->Ln();

			$width += $this->aOptions['Fields']['price']['width'] + $this->aOptions['Fields']['sum']['width'];
			$this->SetFont('FreeSans', 'B', $this->aOptions['FontSize'] );
			$this->SetFillColor( $this->aOptions['Background']['body'] );
			$this->SetLineWidth( 0 );
			$this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
			$this->Cell( $width,  $this->aOptions['RowHeight'], "Пълна информация за ползваните от Вас услуги можете да намерите във виртуалния Ви кабинет", 1, 0, 'C', 1);
			$this->Ln();
			$this->Cell( $width,  $this->aOptions['RowHeight'], "на адрес WWW.ECONT.COM срещу предоставени служебно име и парола.", 1, 0, 'C', 1);
		}

		function ClearRightMargin() 
		{
			$y=$this->GetY();
			$this->SetXY( $this->_PageWidth - $this->aMargin['right']+0.4, 0 );
			$this->SetFillColor( 255 );
			$this->Cell( $this->aMargin['right'],  $y, "",0, 0, "L",1);
		}

		function PorccessText( $sText, &$sText1, &$sText2, &$nFontSize )
		{
			do 
			{
				$sText1 = trim($sText);
				$sText2 = '';
				$aText = explode( ' ', $sText1);
				$this->SetFont('FreeSans', '', $nFontSize);
				
				while( ($this->GetStringWidth($sText1) >  1.6 * $this->aOptions['Widths']['body']) )
				{
					
					$sText2 = array_pop($aText)." ".$sText2;
					$sText1 = implode( " ", $aText);
				}
				
				if(
					( $this->GetStringWidth($sText1) <  1.6 * ( $this->aOptions['Widths']['body'] - 2 * $this->aOptions['BorderWidth'] ) ) &&
					( $this->GetStringWidth($sText2) <  1.6 * ( $this->aOptions['Widths']['body'] - 2 * $this->aOptions['BorderWidth'] ) )
				)			
					break;
				else 
					$nFontSize -=0.5;
			} 
			while (1) ;
		}
		
		function PrintReport( $oResponse, $sDocumentTitle, $sFileName = 'doc', $dest='') {
			$aResult = array();

			$aResult['fields'] = $oResponse->oResult->aFields;
			$aResult['data'] = $oResponse->oResult->aData;
			$aResult['form_elements'] = current($oResponse->oAction->aForms)->aFormElements;
			
			$document_type	=	!empty($aResult['form_elements']['document_type']->mValue) ? $aResult['form_elements']['document_type']->mValue : 
								( !empty($aResult['form_elements']['document_type']->aAttributes['value']) ? $aResult['form_elements']['document_type']->aAttributes['value'] : 1 );
			$document_status=	!empty($aResult['form_elements']['document_status']->mValue) ? $aResult['form_elements']['document_status']->mValue :
								( !empty($aResult['form_elements']['document_status']->aAttributes['value']) ? $aResult['form_elements']['document_status']->aAttributes['value'] : '' );
			$print_document =	!empty($aResult['form_elements']['print_document']->mValue) ? $aResult['form_elements']['print_document']->mValue :
								( !empty($aResult['form_elements']['print_document']->aAttributes['value']) ? $aResult['form_elements']['print_document']->aAttributes['value'] : '' );
			$zero			=	!empty($aResult['form_elements']['zero']->mValue) ? $aResult['form_elements']['zero']->mValue :
								( !empty($aResult['form_elements']['zero']->aAttributes['value']) ? $aResult['form_elements']['zero']->aAttributes['value'] : 0 );

			$printing = Array();
			switch( $document_status ) 
			{
				case 'proforma' : 
					$this->sDocument	= 'ПРОФОРМА';  
					$this->sInvoceType	= '';  
					array_push($printing, 'ТОВА НЕ Е ФАКТУРА');
					break;
				case 'final' :

					if( $zero == 1 )
					{
						array_push($printing, ' АНУЛИРАНА ');
					}
					else
						switch( $print_document ) 
						{
							case 0 : 
								array_push($printing, ' ОРИГИНАЛ ');
								break;
							case 1 : 
								array_push($printing, ' ОРИГИНАЛ ');
								array_push($printing, ' ');
								break;
							case 3 : 
								array_push($printing, ' КОПИЕ ');
								break;
							default :
								array_push($printing, ' ');
								break;
	
						}
						
					switch( $document_type ) 
					{
						case 'danachna'  :
								$this->sDocument	= 'ФАКТУРА';  
								$this->sInvoceType	= '';  
								break;
						case 'oprostena' :
								$this->sDocument	= 'ФАКТУРА';  
								$this->sInvoceType	= '';  
								break;
						case 'd_debitno'	 :
								$this->sDocument	= 'ИЗВЕСТИЕ';  
								$this->sInvoceType	= 'дебитно известие';  
								break;
						case 'd_kreditno'  : 
								$this->sDocument	= 'ИЗВЕСТИЕ';  
								$this->sInvoceType	= 'кредитно известие';  
								break;
						case 'o_debitno'	 :
								$this->sDocument	= 'ИЗВЕСТИЕ';  
								$this->sInvoceType	= 'дебитно известие';  
								break;
						case 'o_kreditno'  : 
								$this->sDocument	= 'ИЗВЕСТИЕ';  
								$this->sInvoceType	= 'кредитно известие';  
								break;
						case 'kvitancia'  : 
								$this->sDocument	= 'КВИТАНЦИЯ';  
								$this->sInvoceType	= '';  
								break;
					}
					break;
				default	: 
					$this->sDocument	= '';  
					$this->sInvoceType	= '';  
					break;
			}

			$this->aMargin['top']=15;
			$this->SetMargins( $this->aMargin['left'], $this->aMargin['top'], $this->aMargin['right'] );

			$this->nCopyCount=count($printing);

			foreach ( $printing as $pg ) 
			{
				$this->AddPage();
				$this->sDocumentHeader = $pg;
				$this->PrintHeader();
				$this->PrintBarceode( $aResult['form_elements'] );
				$this->PrintClient( $aResult['form_elements'] );
				$this->PrintInvoceTitle( $aResult['form_elements'] );
				$this->PrintDeliver( $aResult['form_elements'] );

				$this->Ln($this->aOptions['BorderWidth']);
				
				$this->PrintTableHeader();
				$this->PrintTableData($aResult);
				
				$this->Ln($this->aOptions['BorderWidth']);

				$this->PrintTotal($aResult['form_elements']);
				$this->ClearRightMargin();
			}

			$this->Output($sFileName, $dest);
		}
	}
?>
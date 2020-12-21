<?php
	include_once( "pdfc.inc.php" );
	
	class pppPDF extends PDFC
	{

        function __construct($orientation)
        {
            parent::__construct($orientation);
        }
		
		function PrintReport( $nID )
		{
			$oPPP = new DBPPP();
			$aData = $oPPP->getPPPforPDF( $nID );
			switch( $aData['source_type'] )
			{
				case 'object': 			$sSourceType = 'Обект'; break;
				case 'storagehouse': 	$sSourceType = 'Склад'; break;
				case 'person': 			$sSourceType = 'Служител'; break;
				case 'client': 			$sSourceType = 'Доставчик'; break;
				default : 				$sSourceType = '';
			}
			switch( $aData['dest_type'] )
			{
				case 'object': 			$sDestType = 'Обект'; break;
				case 'storagehouse': 	$sDestType = 'Склад'; break;
				case 'person': 			$sDestType = 'Служител'; break;
				case 'client': 			$sDestType = 'Доставчик'; break;
				default : 				$sDestType = '';
			}
			$aElements = $oPPP->getMZforPDF( $nID );
			
			$this->AddPage( 'P' );
			
			$this->Image( $_SESSION['BASE_DIR'] . '/images/title.png', '', '', 200 );
			
			
			$this->SetFont( 'FreeSans', '', 18 );
			$this->SetXY( 45, 35 );
			$this->Cell( '', '', "ПРИЕМО-ПРЕДАВАТЕЛЕН ПРОТОКОЛ" );
			
			$this->Ln( 12 );
			
			$this->SetFont( 'FreeSans', '', 12 );
			$this->SetX( 80 );
			$this->Cell( '15', '',	$nID );
			$this->Cell( '6', '', 'на' );
			$this->Cell( '6', '', 	$aData['source_date_day'] );
			$this->Cell( '3', '', '/' );
			$this->Cell( '6', '', 	$aData['source_date_month'] );
			$this->Cell( '3', '', '/' );
			$this->Cell( '12', '', 	$aData['source_date_year'] );
			
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
			$this->SetY( 50 );
			$this->Ln( 10 );
			$this->moveX( 40 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( 15, '', "ПРЕДАВАЩ" );
			
			$this->Ln( 7 );
			$this->SetFont( 'FreeSans', '', 8 );	
			
			$this->moveX( 10 );
			$this->Cell( 10, '', 'Тип:' );
			$this->Cell( 80, '', $sSourceType );
			
			$this->Ln( 2 );
			$this->moveX( 20 );
			$this->dottedLine( 30 );
			
			$this->Ln( 4 );
			
			$this->moveX( 10 );
			$this->Cell( 10, '', 'Име:' );
			$this->Cell( 80, '', $aData['source_name'] );
			
			$this->Ln( 2 );
			$this->moveX( 20 );
			$this->dottedLine( 80 );

			// --- ПРИЕМАЩ ---
			$this->SetLeftMargin( 100 );
			
			$this->SetY( 50 );
			$this->Ln( 10 );
			$this->moveX( 40 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( 15, '', "ПРИЕМАЩ" );
			
			$this->Ln( 7 );
			$this->SetFont( 'FreeSans', '', 8 );
			
			$this->moveX( 10 );
			$this->Cell( 10, '', 'Тип:' );
			$this->Cell( 80, '', $sDestType );
			
			$this->Ln( 2 );
			$this->moveX( 20 );
			$this->dottedLine( 30 );
			
			$this->Ln( 4 );
			
			$this->moveX( 10 );
			$this->Cell( 10, '', 'Име:' );
			$this->Cell( 80, '', $aData['dest_name'] );
			
			$this->Ln( 2 );
			$this->moveX( 20 );
			$this->dottedLine( 80 );
			
			
			$this->SetLeftMargin( 0 );
			$this->Ln( 10 );
			$this->moveX( 80 );
			$this->SetFont( 'FreeSans', '', 12 );
			$this->Cell( 15, '', "МАТЕРИАЛНИ ЗАПАСИ" );
			
			$this->Line( 10, 90, 200, 90 );
			
			$this->Ln( 10 );
			$this->moveX( 25 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( 90, '', "Номенклатура" );
			$this->Cell( 30, '', "Количество" );
			$this->Cell( 50, '', "Собственост На Клиента" );
			
			$this->Line( 10, 100, 200, 100 );
			
			$this->Ln( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			foreach( $aElements as $aElement )
			{
				$this->moveX( 25 );
				$this->Cell( 90, '', $aElement['name'] );
				$this->Cell( 30, '', $aElement['count'] );
				if( $aElement['client_own'] )
					$this->Cell( 50, '', 'Да' );
				else
					$this->Cell( 50, '', 'Не' );
				
				$this->Ln( 4 );
			}
			
			$this->Ln( 10 );
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( 10, '', 'ЗАБЕЛЕЖКА:' );
			$this->Ln( 7 );
			$this->moveX( 15 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->MultiCell( 390, 4, $aData['description'] );
			
			$this->Ln( 10 );
			
			$this->SetY( 250 );
			$this->Ln( 10 );
			$this->moveX( 40 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( 15, '', "ПРЕДАЛ" );
			
			$this->Ln( 7 );
			$this->SetFont( 'FreeSans', '', 8 );	
			
			$this->moveX( 10 );
			$this->Cell( 10, '', 'Име:' );
			$this->Cell( 80, '', $aData['source_user'] );
			
			$this->Ln( 2 );
			$this->moveX( 20 );
			$this->dottedLine( 60 );
			
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
			$this->Cell( 80, '', $aData['dest_user'] );
			
			$this->Ln( 2 );
			$this->moveX( 20 );
			$this->dottedLine( 60 );
			
			$this->SetDisplayMode( 'real' );
			$this->Output();
		}
	}
?>
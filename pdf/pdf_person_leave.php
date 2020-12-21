<?php
	include_once( "pdfc.inc.php" );
	
	class personLeavePDF extends PDFC
	{
		function personLeavePDF( $orientation )
		{
			PDFC::PDFC( $orientation );
		}
		
		function PrintReport( $aParams )
		{
			$this->AddPage( 'P' );
			
			$this->SetFont( 'FreeSans', 'B', 10 );
			$this->SetXY( 150, 25 );
			$this->Cell( '', '', "вх. № {$aParams['PDFData']['leave_number']} / {$aParams['PDFData']['leave_date']} г." );
			
			//Row
			$this->Ln( 6 );
			
			$this->moveX( -122 );
			$this->SetFont( 'FreeSans', 'U', 12 );
			$this->Cell( '', '', "ИНФРА ЕООД" );
			
			/*****************
			 * --  МОЛБА  -- *
			 *****************/
			
			//Row
			$this->Ln( 15 );
			
			$this->moveX( -117 );
			$this->SetFont( 'FreeSans', 'B', 13 );
			$this->Cell( '', '', "МОЛБА" );
			
			//Row
			$this->Ln( 10 );
			
			$this->moveX( 60 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "От" );
			$this->moveX( -140 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['PDFData']['person_name'] );
			$this->Line( 67, 58, 145, 58 );
			
			//Row
			$this->Ln( 6 );
			
			$this->moveX( 30 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Длъжност" );
			$this->moveX( -160 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['PDFData']['person_position'] );
			$this->Line( 48, 64, 110, 64 );
			
			$this->moveX( -90 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Отдел" );
			$this->moveX( -75 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['PDFData']['person_department'] );
			$this->Line( 133, 64, 195, 64 );
			
			//Row
			$this->Ln( 6 );
			
			$this->moveX( 35 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Моля за разрешение да ползвам" );
			$this->moveX( -128 );
			$this->SetFont( 'FreeSans', '', 9 );
			$this->Cell( '', '', $aParams['PDFData']['leave_days'] );
			$this->Line( 82, 70, 93, 70 );
			$this->moveX( -113 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "работни дни" );
			$this->moveX( -90 );
			$this->SetFont( 'FreeSans', '', 9 );
			$this->Cell( '', '', $aParams['PDFData']['leave_type'] );
			$this->Line( 117, 70, 173, 70 );
			$this->moveX( -35 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "годишен" );
			
			//Row
			$this->Ln( 6 );
			
			$this->moveX( 25 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "отпуск, по" );
			$this->moveX( -168 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['PDFData']['code_leave_name'] );
			$this->Line( 41, 76, 130, 76 );
			$this->moveX( -80 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "от КТ, считано от" );
			$this->moveX( -54 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['PDFData']['leave_from'] );
			$this->Line( 156, 76, 175, 76 );
			$this->moveX( -35 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "г. по време на" );
			
			//Row
			$this->Ln( 6 );
			
			$this->moveX( 25 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "който ще бъда заместван от" );
			$this->moveX( -143 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['PDFData']['person_substitute'] );
			$this->Line( 65, 82, 143, 82 );
			$this->moveX( -66 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', ", за което имам писменото съгласие" );
			
			//Row
			$this->Ln( 6 );
			
			$this->moveX( 25 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "на прекия ръководител и посочения заместник." );
			
			//Row
			$this->Ln( 10 );
			
			$this->moveX( 35 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', addSpaces( "Подпис:" ) );
			$this->Line( 53, 98, 78, 98 );
			$this->moveX( -132 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "(" );
			$this->moveX( -130 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['PDFData']['person_name_short'] );
			$this->Line( 80, 98, 110, 98 );
			$this->moveX( -100 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', ")" );
			
			//Row
			$this->Ln( 6 );
			
			$this->moveX( 35 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "гр. / с." );
			$this->moveX( -165 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['PDFData']['person_office'] );
			$this->Line( 46, 104, 88, 104 );
			$this->moveX( -122 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "," );
			$this->moveX( -120 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['PDFData']['leave_date'] );
			$this->Line( 90, 104, 110, 104 );
			$this->moveX( -100 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "г." );
			
			//Row
			$this->Ln( 8 );
			
			$this->moveX( 25 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Пряк ръководител :" );
			$this->Line( 53, 112, 78, 112 );
			$this->moveX( -132 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "(" );
			$this->Line( 80, 112, 110, 112 );
			$this->moveX( -100 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', ")" );
			
			$this->moveX( -90 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Заместник :" );
			$this->Line( 138, 112, 158, 112 );
			$this->moveX( -52 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "(" );
			$this->moveX( -50 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['PDFData']['person_substitute_short'] );
			$this->Line( 160, 112, 190, 112 );
			$this->moveX( -20 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', ")" );
			
			//Row
			$this->Ln( 8 );
			
			$this->moveX( 25 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Обработил :" );
			$this->Line( 44, 120, 78, 120 );
			$this->moveX( -132 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "(" );
			$this->Line( 80, 120, 110, 120 );
			$this->moveX( -100 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', ")" );
			
			$this->moveX( -74 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['PDFData']['leave_resolution'] );
			$this->Line( 120, 120, 158, 120 );
			$this->moveX( -52 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "(" );
			$this->moveX( -50 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['PDFData']['person_head_short'] );
			$this->Line( 160, 120, 190, 120 );
			$this->moveX( -20 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', ")" );
			
			//Row
			$this->Ln( 5 );
			
			$this->moveX( 130 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "( Резолюция на Управителя : Да / Не )" );
			
			$this->Ln( 5 );
			
			$this->moveX( 120 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', $aParams['PDFData']['remain_days'] );
			/*****************
			 * -- ЗАПОВЕД -- *
			 *****************/
			
			//Row
			$this->Ln( 50 );
			
			$this->moveX( -120 );
			$this->SetFont( 'FreeSans', 'B', 13 );
			$this->Cell( '', '', "ЗАПОВЕД" );
			
			//Row
			$this->Ln( 6 );
			
			$sAddConfirmDate 	= !empty( $aParams['PDFData']['is_confirm'] ) ? " / {$aParams['PDFData']['confirm_date']} г." : "";
			$this->moveX( -126 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', "№ {$aParams['PDFData']['leave_number']}" . $sAddConfirmDate );
			$this->Line( 89, 186, 118, 186 );
			
			//Row
			$this->Ln( 8 );
			
			$this->moveX( 40 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', "На основание чл. 155, ал. 1 от КТ ( чл. 156, т. 1 или т. 2 от КТ, чл. 160, ал. 1 от КТ ) и молба" );
			
			//Row
			$this->Ln( 5 );
			
			$this->moveX( 35 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', "вх. № {$aParams['PDFData']['leave_number']} / {$aParams['PDFData']['leave_date']} г." );
			$this->Line( 44, 199, 67, 199 );
			
			//Row
			$this->Ln( 8 );
			
			if( empty( $aParams['PDFData']['is_confirm'] ) || $aParams['PDFData']['leave_is_allowed'] == "1" )
			{
				$this->moveX( -125 );
				$this->SetFont( 'FreeSans', '', 10 );
				$this->Cell( '', '', addSpaces( "РАЗРЕШАВАМ :" ) );
			}
			else
			{
				$this->moveX( -130 );
				$this->SetFont( 'FreeSans', '', 10 );
				$this->Cell( '', '', addSpaces( "НЕ РАЗРЕШАВАМ :" ) );
			}
			
			//Row
			$this->Ln( 8 );
			
			$this->moveX( 35 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "На" );
			$this->moveX( -170 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['PDFData']['person_name'] );
			$this->Line( 41, 215, 100, 215 );
			$this->moveX( -108 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "на длъжност" );
			$this->moveX( -88 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['PDFData']['person_position'] );
			$this->Line( 122, 215, 173, 215 );
			$this->moveX( -35 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "в отдел" );
			
			//Row
			$this->Ln( 6 );
			
			$this->moveX( 25 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['PDFData']['person_department'] );
			$this->Line( 26, 221, 75, 221 );
			$this->moveX( -133 );
			$this->SetFont( 'FreeSans', '', 8 );
			if( $aParams['PDFData']['leave_is_due'] )
			{
				$this->Cell( '', '', "да ползва основен платен годишен отпуск и / или допълнителен" );
			}
			else
			{
				$this->Cell( '', '', "да ползва неплатен отпуск и / или допълнителен" );
			}
			$this->Line( 26, 221, 75, 221 );
			
			//Row
			$this->Ln( 6 );
			
			$this->moveX( 25 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "платен отпуск за " . substr( $aParams['PDFData']['leave_from'], 6, 4 ) . " година, в размер на" );
			$this->moveX( -125 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', empty( $aParams['PDFData']['leave_res_days'] ) ? "" : $aParams['PDFData']['leave_res_days'] );
			$this->Line( 84, 227, 95, 227 );
			$this->moveX( -114 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "работни дни, считано от" );
			if( $aParams['PDFData']['leave_is_allowed'] && !empty( $aParams['PDFData']['is_confirm'] ) )
			{
				$this->moveX( -79 );
				$this->SetFont( 'FreeSans', 'B', 9 );
				$this->Cell( '', '', $aParams['PDFData']['leave_res_from'] );
			}
			$this->Line( 131, 227, 150, 227 );
			$this->moveX( -60 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "г." );
			
			//Row
			$this->Ln( 10 );
			
			$this->moveX( 35 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "Настоящата заповед се състави в три еднообразни екземпляра, по един за всяка от страните и един за касата за сведение." );
			
			//Row
			$this->Ln( 4 );
			
			$this->moveX( -185 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "Преписи от настоящата заповед да се връчат на лицето срешу подпис и дата, а на съответните длъжностни лица - за сведение и изпълнение." );
			
			//Row
			$this->Ln( 10 );
			
			$this->moveX( 25 );
			$this->SetFont( 'FreeSans', 'B', 10 );
			$this->Cell( '', '', "Дата: " . ( !empty( $aParams['PDFData']['is_confirm'] ) ? "{$aParams['PDFData']['confirm_date']} г." : "" ) );
			$this->Line( 37, 251, 55, 251 );
			$this->moveX( -90 );
			$this->SetFont( 'FreeSans', 'B', 10 );
			$this->Cell( '', '', "УПРАВИТЕЛ НА {$aParams['PDFData']['firm_jur_name']}" );
			
			//Row
			$this->Ln( 5 );
			
			$this->moveX( 25 );
			$this->SetFont( 'FreeSans', 'B', 10 );
			$this->Cell( '', '', "гр. Димитровград" );
			$this->moveX( -90 );
			$this->SetFont( 'FreeSans', 'B', 10 );
			$this->Cell( '', '', "{$aParams['PDFData']['person_head']}" );
			$this->Line( 170, 256, 185, 256 );
			
			$this->SetDisplayMode( 'real' );
			$this->Output();
		}
	}
?>

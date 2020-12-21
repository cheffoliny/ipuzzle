<?php
	include_once( "pdfc.inc.php" );
	
	class personContractOrderPDF extends PDFC
	{
		
		function personContractOrderPDF( $orientation )
		{
			PDFC::PDFC( $orientation );
		}
		
		function reformatDate( $sDate )
		{
			$nDay = (int) substr( $sDate, 0, 2 );
			$nMonth = (int) substr( $sDate, 3, 2 );
			$nYear = (int) substr( $sDate, 6, 4 );
			
			return $nYear . "-" . $nMonth . "-" . $nDay;
		}
		
		function saveContract( $aParams )
		{
			$oWorkContracts = new DBWorkContractsEnd();
			$oContractPrint = new DBContractPrint();
			
			$aData = array();
			
			$aData['id_person'] = 			$aParams['nID'];
			$aData['num'] = 				$aParams['nNum'];
			$aData['date'] = 				$this->reformatDate( $aParams['sDate'] );
			$aData['date_from'] = 			$this->reformatDate( $aParams['sStartDate'] );
			$aData['clause_paragraph'] = 	$aParams['sClause'] . "," . $aParams['sParagraph'] . "," . $aParams['sLine']. "," . $aParams['sClause2'] . "," . $aParams['sParagraph2'] . "," . $aParams['sLine2'];
			$aData['relation_addition'] = 	$aParams['sInRel'];
			$aData['work_place'] = 			$aParams['sPersonWorkPlace'];
			$aData['address'] = 			$aParams['sPersonAddress'];
			$aData['position'] = 			$aParams['sPosition'];
			$aData['position_code'] = 		$aParams['nCode'];
			$aData['reasons'] = 			$aParams['sReason'] . "||" . $aParams['sReason2'];
			$aData['compensations'] = 		$aParams['sCompA'] . "||" . $aParams['sCompB'] . "||" . $aParams['sCompC'];
			
			$oWorkContracts->update( $aData );
			
			$oContractPrint->updateHead( $aParams['nFirmID'], $aParams['sLeaderName'], $aParams['sLeaderPosition'] );
			$oContractPrint->updateHeadTRZ( $aParams['sLeaderTRZ'] );
			$oContractPrint->updateHeadAccountant( $aParams['sAccountant'] );
		}
		
		function PrintReport( $aParams )
		{
			//Collect Information
			if( empty( $aParams['nIDContract'] ) )
			{
				$this->saveContract( $aParams );
			}
			else
			{
				$oWorkContracts = new DBWorkContractsEnd();
				
				$aData = $oWorkContracts->getWorkContractData( $aParams['nIDContract'] );
				
				$aParams['nID'] = 				$aData['id_person'];
				$aParams['nNum'] = 				$aData['num'];
				$aParams['sDate'] = 			$aData['date'];
				$aParams['sStartDate'] = 		$aData['date_from'];
				$aClauseParagraph = 			explode( ",", $aData['clause_paragraph'] );
				$aParams['sInRel'] =			$aData['relation_addition'];
				$aParams['sPersonWorkPlace'] = 	$aData['work_place'];
				$aParams['sPersonAddress'] = 	$aData['address'];
				$aParams['sPosition'] = 		$aData['position'];
				$aParams['nCode'] = 			$aData['position_code'];
				$aReasons =						explode( "||", $aData['reasons'] );
				$aComps =						explode( "||", $aData['compensations'] );
				
				//Edit PDF Data
				if( !empty( $aClauseParagraph ) )
				{
					$aParams['sClause'] 	= $aClauseParagraph[0];
					$aParams['sParagraph'] 	= $aClauseParagraph[1];
					$aParams['sLine'] 		= $aClauseParagraph[2];
					$aParams['sClause2'] 	= $aClauseParagraph[3];
					$aParams['sParagraph2'] = $aClauseParagraph[4];
					$aParams['sLine2'] 		= $aClauseParagraph[5];
				}
				
				if( !empty( $aComps ) )
				{
					$aParams['sCompA'] = $aComps[0];
					$aParams['sCompB'] = $aComps[1];
					$aParams['sCompC'] = $aComps[2];
				}
				
				if( !empty( $aReasons ) )
				{
					$aParams['sReason'] = $aReasons[0];
					$aParams['sReason2'] = $aReasons[1];
				}
				//End Edit PDF Data
			}
			//End Collect Information
			
			$this->AddPage( 'P' );
			
			$this->SetFont( 'FreeSans', '', 12 );
			$this->SetXY( 55, 15 );
			$this->Cell( '', '', addSpaces( $aParams['sFirmName'] ) );
			
			$this->moveX( -40 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', $aParams['nBulstat'] );
			
			$this->Line( 10, 17, 155, 17 );
			$this->Line( 160, 17, 200, 17 );
			
			$this->Ln( 4 );
			
			$this->moveX( 75 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "(предприятие)" );
			$this->moveX( -37 );
			$this->Cell( '', '', "БУЛСТАТ" );
			
			//Row
			
			$this->Ln( 6 );
			
			$this->movex( 167 );
			$this->SetFont( 'FreeSans', '', 9 );
			$this->Cell( '', '', "ЕГН" );
			$this->movex( -32 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['nPersonEGN'] );
			$this->Line( 176, 27, 200, 27 );
			
			//Row
			
			$this->Ln( 6 );
			
			$this->movex( 70 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', "З А П О В Е Д" );
			
			$this->moveX( -50 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', "№ " . $aParams['nNum'] );
			$this->moveX( -30 );
			$this->Cell( '', '', $aParams['sDate'] );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->moveX( -10 );
			$this->Cell( '', '', "г." );
			
			$this->Line( 160, 33, 200, 33 );
			$this->Ln( 4 );
			$this->moveX( 162 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "номер" );
			$this->moveX( -24 );
			$this->Cell( '', '', "дата" );
			
			//Row
			
			$this->Ln( 6 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "на основание чл." );
			$this->moveX( -175 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', $aParams['sClause'] );
			$this->moveX( -160 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "ал." );
			$this->moveX( -152 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', $aParams['sParagraph'] );
			$this->moveX( -145 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "т." );
			$this->moveX( -139 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', $aParams['sLine'] );
			$this->moveX( -130 );
			$this->Cell( '', '', "от Кодекса на труда" );
			$this->Line( 35, 43, 48, 43 );
			$this->Line( 57, 43, 65, 43 );
			$this->Line( 70, 43, 78, 43 );
			
			//Row
			
			$this->Ln( 6 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "във връзка с чл." );
			$this->moveX( -175 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', $aParams['sClause2'] );
			$this->moveX( -160 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "ал." );
			$this->moveX( -152 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', $aParams['sParagraph2'] );
			$this->moveX( -145 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "т." );
			$this->moveX( -139 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', $aParams['sLine2'] );
			
			$this->Ln( 7 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 9 );
			$this->Cell( '', '', $aParams['sInRel'] );
			
			$this->Line( 35, 49, 48, 49 );
			$this->Line( 57, 49, 65, 49 );
			$this->Line( 70, 49, 78, 49 );
			
			$this->Line( 10, 56, 165, 56 );
			
			//Row
			
			$this->Ln( 9 );
			
			$this->moveX( 47 );
			$this->SetFont( 'FreeSans', '', 9 );
			$this->Cell( '', '', "ПРЕКРАТЯВАМ ТРУДОВОТО ПРАВООТНОШЕНИЕ" );
			
			//Row
			
			$this->Ln( 8 );
			
			$this->moveX( 20 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sPersonName'] );
			$this->Line( 14, 73, 145, 73 );
			
			$this->moveX( -64 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Считано от:" );
			$this->moveX( -35 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sStartDate'] );
			$this->Line( 168, 73, 200, 73 );
			
			$this->Ln( 4 );
			
			$this->moveX( -30 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "дата" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "от" );
			$this->moveX( -145 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', addSpaces( $aParams['sFirmName'] ) );
			$this->Line( 15, 82, 165, 82 );
			
			$this->Ln( 4 );
			
			$this->moveX( 65 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "предприятие, организация, учреждение" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "с място на работа:" );
			$this->moveX( -170 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sPersonWorkPlace'] );
			$this->Line( 38, 91, 165, 91 );
			
			$this->Ln( 4 );
			
			$this->moveX( 75 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "цех, участък, бригада, дейност, отдел" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "в" );
			$this->moveX( -190 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', stripslashes( $aParams['sFirmAddress'] ) );
			$this->Line( 15, 100, 165, 100 );
			
			$this->Ln( 4 );
			
			$this->moveX( 70 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "област, община, нас. място, ул., №" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "на длъжност:" );
			$this->moveX( -175 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sPosition'] );
			$this->Line( 31, 109, 165, 109 );
			
			$this->Ln( 4 );
			
			$this->moveX( 64 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "професия, специалност, квалификационна степен" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "с шифър по ТУ-ЕТМ ( ЩУ-ЕЩТ ):" );
			$this->moveX( -150 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['nCode'] );
			$this->Line( 55, 118, 165, 118 );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Причини за прекратяване на трудовия договор:" );
			
			$this->Ln( 6 );
			
			$this->moveX( 15 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sReason'] );
			
			$this->Ln( 7 );
			
			$this->moveX( 15 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sReason2'] );
			
			$this->Line( 10, 129, 200, 129 );
			$this->Line( 10, 136, 200, 136 );
			$this->Line( 10, 141, 200, 141 );
			
			//Row
			
			$this->Ln( 11 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', "На лицето се изплащат следните обезщетения съгласно:" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 20 );
			$this->Cell( '', '', "а)  " . $aParams['sCompA'] );
			$this->Line( 25, 152, 165, 152 );
			$this->Line( 168, 152, 200, 152 );
			
			$this->Ln( 5 );
			
			$this->moveX( 20 );
			$this->Cell( '', '', "б)  " . $aParams['sCompB'] );
			$this->Line( 25, 157, 165, 157 );
			$this->Line( 168, 157, 200, 157 );
			
			$this->Ln( 5 );
			
			$this->moveX( 20 );
			$this->Cell( '', '', "в)  " . $aParams['sCompC'] );
			$this->Line( 25, 162, 165, 162 );
			$this->Line( 168, 162, 200, 162 );
			
			//Row
			
			$this->Line( 10, 176, 200, 176 );
			$this->Line( 10, 179, 200, 179 );
			
			//Row
			
			$this->Ln( 23 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Копие от заповедта да се връчи на лицето и на главния счетоводител." );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 40 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sLeaderName'] . "   -   " . $aParams['sLeaderPosition'] );
			
			$this->Ln( 4 );
			
			$this->moveX( 55 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "собствено, бащино и фамилно име на ръководителя" );
			$this->moveX( -31 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "подпис" );
			
			$this->Line( 10, 190, 165, 190 );
			$this->Line( 168, 190, 200, 190 );
			
			//Row
			
			$this->Ln( 6 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Съгласувано с:  Гл. Счетоводител:" );
			
			$this->moveX( -150 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sAccountant'] );
			
			$this->Line( 60, 200, 150, 200 );
			$this->Line( 168, 200, 200, 200 );
			
			$this->Ln( 4 );
			
			$this->moveX( 94 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "фамилно име" );
			$this->moveX( -31 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "подпис" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 31 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Гл. юристконсулт:" );
			$this->Line( 60, 209, 150, 209 );
			$this->Line( 168, 209, 200, 209 );
			
			$this->Ln( 4 );
			
			$this->moveX( 94 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "фамилно име" );
			$this->moveX( -31 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "подпис" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 31 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Ръководител ТРЗ:" );
			
			$this->moveX( -150 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sLeaderTRZ'] );
			
			$this->Line( 60, 218, 150, 218 );
			$this->Line( 168, 218, 200, 218 );
			
			$this->Ln( 4 );
			
			$this->moveX( 94 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "фамилно име" );
			$this->moveX( -31 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "подпис" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Синдикат, протокол №                        /                      г." );
			$this->Line( 43, 227, 78, 227 );
			
			//Row
			
			$this->Ln( 7 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Представител на сидникална организация:" );
			
			$this->Line( 70, 234, 150, 234 );
			$this->Line( 168, 234, 200, 234 );
			
			$this->Ln( 4 );
			
			$this->moveX( 99 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "фамилно име" );
			$this->moveX( -31 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "подпис" );
			
			//Row
			
			$this->Ln( 7 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', "Дата на връчване на заповедта:" );          // Value ( removed 2009-01-16 )
			
			$this->Line( 168, 245, 200, 245 );
			
			$this->Ln( 4 );
			
			$this->moveX( 173 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "подпис на лицето" );
			
			//Row
			
			
			$this->SetDisplayMode( 'real' );
			$this->Output( "personContractOrder.pdf" );
		}
	}
?>
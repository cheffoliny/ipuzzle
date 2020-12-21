<?php
	include_once( "pdfc.inc.php" );
	
	class personContractAdditionPDF extends PDFC
	{
		
		function personContractAdditionPDF( $orientation )
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
			$oWorkContracts = new DBWorkContractsExtend();
			$oContractPrint = new DBContractPrint();
			
			$aData = array();
			
			$aData['id_person'] = 			$aParams['nID'];
			$aData['num'] = 				$aParams['nNum'];
			$aData['date'] = 				$this->reformatDate( $aParams['sDate'] );
			$aData['date_today'] = 			$this->reformatDate( $aParams['sToday'] );
			$aData['clause_paragraph'] = 	$aParams['sClause'] . "," . $aParams['sParagraph'] . $aParams['sLine'];
			$aData['head_family'] = 		$aParams['sLeaderName'];
			$aData['head_position'] = 		$aParams['sLeaderPosition'];
			$aData['work_place'] = 			$aParams['sPersonWorkPlace'];
			$aData['address'] = 			$aParams['sPersonAddress'];
			$aData['position'] = 			$aParams['sPosition'];
			$aData['position_code'] = 		$aParams['nCode'];
			$aData['position_to'] = 		$aParams['sPositionTo'];
			$aData['position_to_code'] = 	$aParams['nCodeTo'];
			$aData['work_time_hours'] = 	$aParams['nFullDayHours'];
			$aData['salary_basic'] = 		$aParams['nBasicSalary'];
			$aData['salary_increase'] = 	$aParams['nSalary'];
			$aData['date_start'] = 			$this->reformatDate( $aParams['sStartDate'] );
			$aData['extra_rewards'] = 		$aParams['sExtraReward1'] . "||" . $aParams['sExtraReward2'] . "||" . $aParams['sExtraReward3'] . "||" . $aParams['sExtraReward4'];
			$aData['work_period_type'] = 	$aParams['sWorkPeriodType'];
			$aData['work_period_time'] = 	$aParams['nWorkPeriodTime'];
			
			$oWorkContracts->update( $aData );
			
			$oContractPrint->updateHead( $aParams['nFirmID'], $aParams['sLeaderName'], $aParams['sLeaderPosition'] );
		}
		
		function PrintReport( $aParams )
		{
			//Collect Information
			if( empty( $aParams['nIDContract'] ) )
			{
				//Edit PDF Data
				if( $aParams['nFullDayHours'] >= 8 )$aParams['FullOrNot'] = "пълно";
				else $aParams['FullOrNot'] = "непълно";
				//End Edit PDF Data
				
				$this->saveContract( $aParams );
			}
			else
			{
				$oWorkContracts = new DBWorkContractsExtend();
				
				$aData = $oWorkContracts->getWorkContractData( $aParams['nIDContract'] );
				
				$aParams['nID'] = 				$aData['id_person'];
				$aParams['nNum'] = 				$aData['num'];
				$aParams['sDate'] = 			$aData['date'];
				$aParams['sToday'] = 			$aData['date_today'];
				$aClauseParagraph = 			explode( ",", $aData['clause_paragraph'] );
				$aParams['sLeaderName'] = 		$aData['head_family'];
				$aParams['sLeaderPosition'] = 	$aData['head_position'];
				$aParams['sPersonWorkPlace'] = 	$aData['work_place'];
				$aParams['sPersonAddress'] = 	$aData['address'];
				$aParams['sPosition'] = 		$aData['position'];
				$aParams['nCode'] = 			$aData['position_code'];
				$aParams['sPositionTo'] = 		$aData['position_to'];
				$aParams['nCodeTo'] = 			$aData['position_to_code'];
				$aParams['nFullDayHours'] = 	$aData['work_time_hours'];
				$aParams['nBasicSalary'] = 		$aData['salary_basic'];
				$aParams['nSalary'] = 			$aData['salary_increase'];
				$aParams['sStartDate'] = 		$aData['date_start'];
				$aExtraRewards =				explode( "||", $aData['extra_rewards'] );
				$aParams['sWorkPeriodType'] =	$aData['work_period_type'];
				$aParams['nWorkPeriodTime'] = 	$aData['work_period_time'];
				
				//Edit PDF Data
				if( $aParams['nFullDayHours'] >= 8 )$aParams['FullOrNot'] = "пълно";
				else $aParams['FullOrNot'] = "непълно";
				
				if( !empty( $aClauseParagraph ) )
				{
					$aParams['sClause'] = $aClauseParagraph[0];
					$aParams['sParagraph'] = $aClauseParagraph[1];
					$aParams['sLine'] = $aClauseParagraph[2];
				}
				
				if( !empty( $aExtraRewards ) )
				{
					$aParams['sExtraReward1'] = $aExtraRewards[0];
					$aParams['sExtraReward2'] = $aExtraRewards[1];
					$aParams['sExtraReward3'] = $aExtraRewards[2];
					$aParams['sExtraReward4'] = $aExtraRewards[3];
				}
				//End Edit PDF Data
			}
			
			//Fix Null Values
			if( $aParams['nCodeTo'] == 0 )$aParams['nCodeTo'] = "";
			//End Fix Null Values
			
			//End Collect Information
			
			$this->AddPage( 'P' );
			
			$this->SetFont( 'FreeSans', '', 12 );
			$this->SetXY( 45, 15 );
			$this->Cell( '', '', addSpaces( $aParams['sFirmName'] ) );
			
			$this->moveX( -60 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', "БУЛСТАТ   " . $aParams['nBulstat'] );
			
			$this->Line( 10, 17, 200, 17 );
			
			$this->Ln( 4 );
			
			$this->movex( 62 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "(предприятие)" );
			
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
			
			$this->Ln( 4 );
			
			$this->movex( 45 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', "ДОПЪЛНИТЕЛНО СПОРАЗУМЕНИЕ КЪМ" );
			
			$this->Ln( 5 );
			
			$this->movex( 58 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', "Т Р У Д О В   Д О Г О В О Р" );
			$this->moveX( -50 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', "№ " . $aParams['nNum'] );
			$this->moveX( -30 );
			$this->Cell( '', '', $aParams['sDate'] );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->moveX( -10 );
			$this->Cell( '', '', "г." );
			
			$this->Line( 160, 36, 200, 36 );
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
			$this->Cell( '', '', "Днес" );
			$this->moveX( -190 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sToday'] );					//Value
			$this->Line( 20, 46, 38, 46 );
			
			$this->moveX( -171 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "г. в гр. (с.)" );
			
			$this->moveX( -145 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', "Шумен" );
			$this->Line( 55, 46, 90, 46 );
			
			$this->moveX( -119 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "област" );
			$this->moveX( -93 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', "Шумен" );
			$this->Line( 105, 46, 145, 46 );
			
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
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "от Кодекса на труда" );
			$this->Line( 35, 52, 48, 52 );
			$this->Line( 57, 52, 65, 52 );
			$this->Line( 70, 52, 78, 52 );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "предприятието, представлявано от ръководителя му" );
			
			//Row
			
			$this->Ln( 7 );
			
			$this->moveX( 13 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', $aParams['sLeaderName'] );
			$this->Line( 10, 64, 80, 64 );
			
			$this->moveX( -130 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', ", на длъжност" );
			$this->moveX( -103 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', $aParams['sLeaderPosition'] );
			$this->Line( 105, 64, 165, 64 );
			
			$this->Ln( 4 );
			
			$this->moveX( 29 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "име на ръководителя" );
			$this->moveX( -93 );
			$this->Cell( '', '', "длъжност на ръководителя" );
			
			//Row
			
			$this->Ln( 7 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "и" );
			$this->moveX( -190 );
			$this->setFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', $aParams['sPersonName'] );
			$this->Line( 15, 75, 120, 75 );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "с адрес:" );
			$this->moveX( -180 );
			$this->setFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', stripslashes( $aParams['sPersonAddress'] ) );
			$this->Line( 24, 80, 165, 80 );
			
			$this->Ln( 4 );
			
			$this->moveX( 80 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "обл., гр.(с.), ул., №" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "с образование:" );
			$this->moveX( -175 );
			$this->setFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', $aParams['sPersonEducation'] );
			$this->Line( 33, 89, 75, 89 );
			
			$this->moveX( -130 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "и специалност:" );
			$this->moveX( -100 );
			$this->setFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', $aParams['sPersonSpeciality'] );
			$this->Line( 105, 89, 165, 89 );
			
			//Row
			
			$this->Ln( 6 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "с друга специалност (професия):" );
			$this->moveX( -150 );
			$this->setFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', $aParams['sPersonSpecialityOther'] );
			$this->Line( 57, 95, 165, 95 );
			
			//Row
			
			$this->Ln( 6 );
			
			$this->moveX( 140 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "с трудов стаж общ:" );
			
			$this->Line( 168, 101, 200, 101 );
			
			$this->moveX( -39 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', $aParams['LOS_Y'] );
			$this->moveX( -28 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', $aParams['LOS_M'] );
			$this->moveX( -18 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', $aParams['LOS_D'] );
			
			$this->Ln( 4 );
			
			$this->moveX( 170 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "год." );
			$this->moveX( -29 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "мес." );
			$this->moveX( -19 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "дни" );
			
			//Row
			
			$this->Ln( 4 );
			
			$this->moveX( 133 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "в т.ч. по специалността:" );
			
			$this->Line( 168, 109, 200, 109 );
			
			$this->Ln( 4 );
			
			$this->moveX( 170 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "год." );
			$this->moveX( -29 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "мес." );
			$this->moveX( -19 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "дни" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "( не е ) пенсиониран" );
			$this->Line( 40, 118, 130, 118 );
			
			$this->Ln( 4 );
			
			$this->moveX( 53 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "за изслужено време и старост, по инвалидност и др." );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "се споразумяха за следните изменения на трудовия договор, считано от :" );
			$this->moveX( -35 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sStartDate'] );
			$this->Line( 168, 127, 200, 127 );
			
			$this->Ln( 4 );
			
			$this->moveX( -30 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "дата" );
			
			//Row
			
			$this->Ln( 4 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "1. За мястото на работа в :" );
			$this->moveX( -125 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', addSpaces( $aParams['sFirmName'] ) );
			$this->Line( 50, 135, 165, 135 );
			
			$this->Ln( 4 );
			
			$this->moveX( 80 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "предприятие, организация, учреждение" );
			
			//Row
			
			$this->Ln( 4 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "в" );
			$this->moveX( -190 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sPersonWorkPlace'] );
			$this->Line( 15, 143, 165, 143 );
			
			$this->Ln( 4 );
			
			$this->moveX( 65 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "цех, участък, бригада, дейност, отдел" );
			
			//Row
			
			$this->Ln( 4 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "в" );
			$this->moveX( -190 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', stripslashes( $aParams['sFirmAddress'] ) );
			$this->Line( 15, 151, 165, 151 );
			
			$this->Ln( 4 );
			
			$this->moveX( 68 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "област, община, нас. място, ул., №" );
			
			//Row
			
			$this->Ln( 4 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "2. От длъжност" );
			$this->moveX( -170 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sPosition'] );
			$this->Line( 35, 159, 165, 159 );
			
			//Row
			
			$this->Ln( 6 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "на длъжност:" );
			$this->moveX( -170 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sPositionTo'] );
			$this->Line( 35, 165, 165, 165 );
			
			//Row
			
			$this->Ln( 6 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "с шифър по ТУ-ЕТМ ( ЩУ-ЕЩТ )" );
			$this->moveX( -150 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['nCodeTo'] );
			$this->Line( 55, 171, 90, 171 );
			
			$this->moveX( -120 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', ", категория персонал :" );
			$this->Line( 123, 171, 165, 171 );
			
			//Row
			
			$this->Ln( 6 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "3. за:" );
			$this->moveX( -185 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			if( empty( $aParams['sWorkPeriodType'] ) )
			{
				$this->Cell( '', '', "" );
			}
			else
			{
				$sWorkPeriodTime = !empty( $aParams['nWorkPeriodTime'] ) ? " - " . $aParams['nWorkPeriodTime'] . " мес." : "";
				$this->Cell( '', '', $aParams['sWorkPeriodType'] . $sWorkPeriodTime );
			}
			$this->Line( 20, 177, 165, 177 );
			
			$this->Ln( 4 );
			
			$this->moveX( -173 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "непред. време, опр. срок, извършване на опр. работа, заместване, със срок на изпитване" );
			
			//Row
			
			$this->Ln( 4 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "4. за:" );
			$this->moveX( -185 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['FullOrNot'] . " работно време - " . $aParams['nFullDayHours'] . " часа" );
			$this->Line( 20, 185, 165, 185 );
			
			$this->Ln( 4 );
			
			$this->moveX( -150 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "пълно, непълно - ч. или в опр. дни работно време" );
			
			//Row
			
			$this->Ln( 4 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "5. За основно месечно (дневно) трудово възнаграждение: " );
			$this->moveX( -115 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			if( !empty( $aParams['nBasicSalary'] ) )
			{
				$this->Cell( '', '', convertDigitToText( (string) round( $aParams['nBasicSalary'], 0 ) ) . " лева" );
			}
			$this->moveX( -31 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			if( !empty( $aParams['nBasicSalary'] ) )
			{
				$this->Cell( '', '', $aParams['nBasicSalary'] . " лв." );
			}
			
			$this->Line( 93, 193, 165, 193 );
			$this->Line( 168, 193, 200, 193 );
			
			//Row
			
			$this->Ln( 6 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "6. За увеличението на основното трудово възнаграждение: " );
			$this->moveX( -115 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			if( !empty( $aParams['nSalary'] ) )
			{
				$this->Cell( '', '', convertDigitToText( (string) round( $aParams['nSalary'], 0 ) ) . " лева" );
			}
			$this->moveX( -31 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			if( !empty( $aParams['nSalary'] ) )
			{
				$this->Cell( '', '', $aParams['nSalary'] . " лв." );
			}
			
			$this->Line( 93, 199, 165, 199 );
			$this->Line( 168, 199, 200, 199 );
			
			//Row
			
			$this->Ln( 6 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "7. За допълнителни възнаграждения:" );
			$this->moveX( -147 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', $aParams['sExtraReward1'] );
			$this->Line( 63, 205, 165, 205 );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 30 );
			$this->Cell( '', '', $aParams['sExtraReward2'] );
			$this->Line( 30, 210, 165, 210 );
			$this->Line( 168, 210, 200, 210 );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 30 );
			$this->Cell( '', '', $aParams['sExtraReward3'] );
			$this->Line( 30, 215, 165, 215 );
			$this->Line( 168, 215, 200, 215 );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 30 );
			$this->Cell( '', '', $aParams['sExtraReward4'] );
			$this->Line( 30, 220, 165, 220 );
			$this->Line( 168, 220, 200, 220 );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 26 );
			$this->SetFont( 'FreeSans', '', 8 );
			//$this->Cell( '', '', "г)" );
			$this->Line( 30, 225, 165, 225 );
			$this->Line( 168, 225, 200, 225 );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "8. За другите условия на трудовия договор: " );
			$this->moveX( -137 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', "Запазват се всички условия и допълнителни споразумения към него." );
			$this->Line( 10, 230, 200, 230 );
			
			//Row
			
			$this->Ln( 4 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Настоящото допълнително споразумение се състави в два екземпляра, по един за всяка една от страните и е неделима част от" );
			
			$this->Ln( 4 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "трудов договор №" );
			$this->moveX( -170 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', $aParams['nNum'] . "  /  " . $aParams['sDate'] . " г." );
			$this->Line( 37, 238, 70, 238 );
			
			//Row
			
			$this->Ln( 6 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->moveX( 10 );
			$this->Cell( '', '', "Работник:" );
			$this->moveX( -185 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sPersonWorkName'] );					//Value
			$this->moveX( -87 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Ръководител:" );
			
			$this->Ln( 2 );
			$this->moveX( 25 );
			$this->dottedLine( 50 );
			$this->moveX( 70 );
			$this->dottedLine( 50 );
			$this->Line( 10, 246, 200, 246 );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->Cell( '', '', "Изготвил проекта на допълнителното споразумение," );
			$this->moveX( -120 );
			$this->Cell( '', '', "Завеждащ \"Личен състав\":" );
			$this->Line( 128, 251, 200, 251 );
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->Cell( '', '', "Проектът за трудовия договор е съгласуван с:" );
			$this->moveX( -120 );
			$this->Cell( '', '', "Гл. счетоводител:" );
			$this->Line( 128, 256, 200, 256 );
			
			$this->Ln( 5 );
			
			$this->moveX( 90 );
			$this->Cell( '', '', "Гл. юристконсулт:" );
			$this->Line( 128, 261, 200, 261 );
			
			$this->Ln( 5 );
			
			$this->moveX( 90 );
			$this->Cell( '', '', "Ръководител ТРЗ:" );
			$this->Line( 128, 266, 200, 266 );
			
			$this->Line( 10, 268, 200, 268 );
			
			//Row
			
			$this->Ln( 7 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "Подписан от двете страни екземпляр от настоящото споразумение е връчен на работника на:" );
			$this->moveX( -30 );
			$this->Cell( '', '', "г." );
			$this->Line( 138, 273, 180, 273 );
			$this->Line( 184, 273, 200, 273 );
			
			$this->Ln( 4 );
			
			$this->moveX( 187 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "Подпис" );
			
			//Row
			
			$this->SetDisplayMode( 'real' );
			$this->Output( "personContractAddition.pdf" );
		}
	}
?>
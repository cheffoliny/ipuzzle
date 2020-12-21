<?php
	include_once( "pdfc.inc.php" );
	
	class personContractPDF extends PDFC
	{
		
		function personContractPDF( $orientation )
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
			$oWorkContracts = new DBWorkContracts();
			$oContractPrint = new DBContractPrint();
			
			$aData = array();
			
			$aData['id_person'] = 				$aParams['nID'];
			$aData['num'] = 					$aParams['nNum'];
			$aData['date_contract'] = 			$this->reformatDate( $aParams['sDate'] );
			$aData['date_today'] = 				$this->reformatDate( $aParams['sToday'] );
			$aData['clause_paragraph'] = 		$aParams['sClause'] . "," . $aParams['sParagraph'] . "," . $aParams['sClause2'] . "," . $aParams['sParagraph2'] . "," . $aParams['sLine'];
			$aData['head_famaly'] = 			$aParams['sLeaderName'];
			$aData['head_position'] = 			$aParams['sLeaderPosition'];
			$aData['work_place'] = 				$aParams['sPersonWorkPlace'];
			$aData['address'] = 				$aParams['sPersonAddress'];
			$aData['position'] = 				$aParams['sPosition'];
			$aData['position_code'] = 			$aParams['nCode'];
			$aData['is_fulltime_work'] = 		$aParams['nIsFulltimeWork'];
			$aData['is_on_night_schedule'] = 	$aParams['nIsOnNightSchedule'];
			$aData['work_time_hours'] = 		$aParams['nFullDayHours'];
			$aData['salary'] = 					$aParams['nSalary'];
			$aData['date_from'] = 				$this->reformatDate( $aParams['sStartDate'] );
			$aData['year_leave_days'] = 		$aParams['nLeave'];
			$aData['work_period_type'] = 		$aParams['sWorkPeriodType'];
			$aData['test_period'] = 			$aParams['nTestPeriodMonths'];
			$aData['reward_optional'] = 		$aParams['sRewardOptional1'] . "||" . $aParams['sRewardOptional2'] . "||" . $aParams['sRewardOptional3'];
			$aData['term_optional']	=			$aParams['sTermOptional1'] . "||" . $aParams['sTermOptional2'];
			
			$oWorkContracts->update( $aData );
			
			$oContractPrint->updateHead( $aParams['nFirmID'], $aParams['sLeaderName'], $aParams['sLeaderPosition'] );
		}
		
		function PrintReport( $aParams )
		{
			//Collect Information
			if( empty( $aParams['nIDContract'] ) )
			{
				//Edit PDF Data
				switch( $aParams['nIsFulltimeWork'] )
				{
					case 0: $aParams['FullOrNot'] = "непълно работно време"; break;
					case 1: $aParams['FullOrNot'] = "пълно работно време"; break;
					case 2: $aParams['FullOrNot'] = "непълно работно време по график с нощен труд"; break;
					case 3: $aParams['FullOrNot'] = "пълно работно време по график с нощен труд"; break;
				}
				//End Edit PDF Data
				
				$this->saveContract( $aParams );
			}
			else
			{
				$oWorkContracts = new DBWorkContracts();
				
				$aData = $oWorkContracts->getWorkContractData( $aParams['nIDContract'] );
				
				$aParams['nID'] = 					$aData['id_person'];
				$aParams['nNum'] = 					$aData['num'];
				$aParams['sDate'] = 				$aData['date_contract'];
				$aParams['sToday'] = 				$aData['date_today'];
				$aClauseParagraph = 				explode( ",", $aData['clause_paragraph'] );
				$aParams['sLeaderName'] = 			$aData['head_famaly'];
				$aParams['sLeaderPosition'] = 		$aData['head_position'];
				$aParams['sPersonWorkPlace'] = 		$aData['work_place'];
				$aParams['sPersonAddress'] = 		$aData['address'];
				$aParams['sPosition'] = 			$aData['position'];
				$aParams['nCode'] = 				$aData['position_code'];
				$aParams['nIsFulltimeWork'] =		$aData['is_fulltime_work'];
				$aParams['nIsOnNightSchedule'] = 	$aData['is_on_night_schedule'];
				$aParams['nFullDayHours'] = 		$aData['work_time_hours'];
				$aParams['nSalary'] = 				$aData['salary'];
				$aParams['sStartDate'] = 			$aData['date_from'];
				$aParams['nLeave'] = 				$aData['year_leave_days'];
				$aParams['sWorkPeriodType'] = 		$aData['work_period_type'];
				$aParams['nTestPeriodMonths'] = 	$aData['test_period'];
				$aRewardOptionals =					explode( "||", $aData['reward_optional'] );
				$aTermOptional = 					explode( "||", $aData['term_optional'] );
				
				//Edit PDF Data
				switch( $aParams['nIsFulltimeWork'] )
				{
					case 0: $aParams['FullOrNot'] = "непълно работно време"; break;
					case 1: $aParams['FullOrNot'] = "пълно работно време"; break;
					case 2: $aParams['FullOrNot'] = "непълно работно време по график с нощен труд"; break;
					case 3: $aParams['FullOrNot'] = "пълно работно време по график с нощен труд"; break;
				}
				
				if( !empty( $aClauseParagraph ) )
				{
					$aParams['sClause'] 	= $aClauseParagraph[0];
					$aParams['sParagraph'] 	= $aClauseParagraph[1];
					$aParams['sClause2'] 	= $aClauseParagraph[2];
					$aParams['sParagraph2'] = $aClauseParagraph[3];
					$aParams['sLine'] 		= $aClauseParagraph[4];
				}
				
				if( !empty( $aRewardOptionals ) )
				{
					$aParams['sRewardOptional1'] = $aRewardOptionals[0];
					$aParams['sRewardOptional2'] = $aRewardOptionals[1];
					$aParams['sRewardOptional3'] = $aRewardOptionals[2];
				}
				
				if( !empty( $aTermOptional ) )
				{
					$aParams['sTermOptional1'] = $aTermOptional[0];
					$aParams['sTermOptional2'] = $aTermOptional[1];
				}
				//End Edit PDF Data
			}
			
			//Fix Null Values
			if( $aParams['LOS_Y'] == 0 )$aParams['LOS_Y'] = "";
			if( $aParams['LOS_M'] == 0 )$aParams['LOS_M'] = "";
			if( $aParams['LOS_D'] == 0 )$aParams['LOS_D'] = "";
			
			if( $aParams['nYears'] == 0 )$aParams['nYears'] = "";
			if( $aParams['nMonths'] == 0 )$aParams['nMonths'] = "";
			if( $aParams['nDays'] == 0 )$aParams['nDays'] = "";
			if( empty( $aParams['nCode'] ) )$aParams['nCode'] = "";
			//End Fix Null Values
			
			//End Collect Information
			
			$this->AddPage( 'P' );
			
			$this->SetFont( 'FreeSans', '', 12 );
			$this->SetXY( 55, 15 );
			$this->Cell( '', '', addSpaces( $aParams["sFirmName"] ) );
			
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
			
			$this->Ln( 7 );
			
			$this->moveX( 67 );
			$this->SetFont( 'FreeSans', 'B', 10 );
			$this->Cell( '', '', "ТРУДОВ ДОГОВОР" );
			
			$this->moveX( -50 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', "№ " . $aParams['nNum'] );						//Value
			$this->moveX( -30 );
			$this->Cell( '', '', $aParams['sDate'] );							//Value
			$this->SetFont( 'FreeSans', '', 8 );
			$this->moveX( -10 );
			$this->Cell( '', '', "г." );
			
			$this->Line( 160, 28, 200, 28 );
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
			$this->Line( 20, 38, 38, 38 );
			
			$this->moveX( -171 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "г. в гр. (с.)" );
			
			$this->moveX( -145 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', "Шумен" );
			$this->Line( 55, 38, 90, 38 );
			
			$this->moveX( -119 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "област" );
			$this->moveX( -93 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', "Шумен" );
			$this->Line( 105, 38, 145, 38 );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 105 );
			$this->Cell( '', '', "л.к. № " . $aParams['sPersonIDNum'] . " / " . $aParams['sPersonIDDate'] . " " . $aParams['sPersonIDCreated'] );			//Values
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "на основание чл." );
			$this->moveX( -174 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sClause2'] );
			$this->Line( 35, 48, 43, 48 );
			
			$this->moveX( -165 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "ал." );
			$this->moveX( -156 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sParagraph2'] );
			$this->Line( 52, 48, 60, 48 );
			
			$this->moveX( -147 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "т." );
			$this->moveX( -138 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			$this->Cell( '', '', $aParams['sLine'] );
			$this->Line( 70, 48, 78, 48 );
			
			$this->moveX( -130 );
			$this->SetFont( 'FreeSans', '', 8 );
			if( !empty( $aParams['sClause'] ) || !empty( $aParams['sParagraph'] ) )
			{
				$sInRelation = ", във връзка с чл. " . $aParams['sClause'] . ", ал. " . $aParams['sParagraph'];
			}
			else $sInRelation = "";
			$this->Cell( '', '', "{$sInRelation} от Кодекса на труда се сключи настоящия" );							//Values
			
//			$this->moveX( -150 );
//			$this->SetFont( 'FreeSans', '', 8 );
//			$this->Cell( '', '', "от Кодекса на труда се сключи настоящия" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "трудов договор между предприятието, представлявано от ръководителя му" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 13 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', $aParams['sLeaderName'] );								//Value
			$this->Line( 10, 58, 80, 58 );
			
			$this->moveX( -130 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', ", на длъжност" );
			$this->moveX( -103 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', $aParams['sLeaderPosition'] );								//Value
			$this->Line( 105, 58, 175, 58 );
			
			$this->Ln( 4 );
			
			$this->moveX( 29 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "име на ръководителя" );
			$this->moveX( -88 );
			$this->Cell( '', '', "длъжност на ръководителя" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "и" );
			$this->moveX( -190 );
			$this->setFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', $aParams['sPersonName'] );		//Value
			$this->Line( 15, 67, 120, 67 );
			
			$this->moveX( -32 );
			$this->setFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', $aParams['nPersonEGN'] );					//Value
			$this->Line( 177, 67, 200, 67 );
			
			$this->Ln( 4 );
			
			$this->moveX( 186 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "ЕГН" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "с адрес:" );
			$this->moveX( -180 );
			$this->setFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', stripslashes( $aParams['sPersonAddress'] ) );		//Value
			$this->Line( 24, 76, 170, 76 );
			
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
			$this->Cell( '', '', $aParams['sPersonEducation'] );					//Value
			$this->Line( 33, 85, 75, 85 );
			
			$this->moveX( -130 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "и специалност:" );
			$this->moveX( -100 );
			$this->setFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', $aParams['sPersonSpeciality'] );					//Value
			$this->Line( 105, 85, 200, 85 );
			
			//Row
			
			$this->Ln( 6 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "с друга специалност (професия):" );
			$this->moveX( -150 );
			$this->setFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', $aParams['sPersonSpecialityOther'] );					//Value
			$this->Line( 57, 91, 200, 91 );
			
			//Row
			
			$this->Ln( 6 );
			
			$this->moveX( 140 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "с трудов стаж общ:" );
			
			$this->Line( 168, 93, 200, 93 );
			$this->Line( 168, 97, 200, 97 );
			$this->Line( 168, 93, 168, 97 );
			$this->Line( 179, 93, 179, 97 );
			$this->Line( 189, 93, 189, 97 );
			$this->Line( 200, 93, 200, 97 );
			
			$this->moveX( -39 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', $aParams['LOS_Y'] );							//Value
			$this->moveX( -28 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', $aParams['LOS_M'] );							//Value
			$this->moveX( -18 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', $aParams['LOS_D'] );							//Value
			
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
			
			$this->moveX( 145 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "в т.ч. за ТСПО:" );
			$this->moveX( -39 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', $aParams['nYears'] );							//Value
			$this->moveX( -28 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', $aParams['nMonths'] );							//Value
			$this->moveX( -18 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', $aParams['nDays'] );							//Value
			
			$this->Line( 168, 105, 200, 105 );
			
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
			$this->Line( 40, 114, 130, 114 );
			
			$this->moveX( -70 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "за следното:" );
			$this->moveX( -50 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', "от " . $aParams['sStartDate'] . " г." );			//Value
			$this->Line( 160, 114, 183, 114 );
			
			$this->Ln( 4 );
			
			$this->moveX( 53 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "за изслужено време и старост, по инвалидност и др." );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "1. Предприятието възлага и работникът приема да изпълнява в:" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 81 );
			$this->SetFont( 'FreeSans', '', 10 );
			$this->Cell( '', '', addSpaces( $aParams["sFirmName"] ) );
			$this->Line( 10, 128, 200, 128 );
			
			$this->Ln( 4 );
			
			$this->moveX( 78 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "предприятие, организация, учреждение" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 12 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "с място на работа:" );
			$this->moveX( -165 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', $aParams['sPersonWorkPlace'] . " - " . $aParams['sObject'] );			//Value
			$this->Line( 10, 137, 200, 137 );
			
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
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', $aParams['sFirmAddress'] );
			$this->Line( 14, 146, 160, 146 );
			
			$this->Ln( 4 );
			
			$this->moveX( 64 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "област, община, нас. място, ул., №" );
			
			//Row
			
			$this->Ln( 4 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "длъжността:" );
			$this->moveX( -177 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', $aParams['sPosition'] );
			$this->Line( 30, 154, 160, 154 );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "с шифър по ТУ-ЕТ М (ЩУ-ЕЩТ)" );
			$this->moveX( -150 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', $aParams['nCode'] );
			$this->moveX( -130 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', ", категория персонал:" );
			$this->Line( 55, 159, 200, 159 );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "за:" );
			$this->moveX( -140 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			if( !empty( $aParams['sWorkPeriodType'] ) )
			{
				if( $aParams['sWorkPeriodType'] != "Неопределено време" && $aParams['sWorkPeriodType'] != "Заместване" )
				{
					$nInterval = "";
					if( !empty( $aParams['nTestPeriodMonths'] ) )
					{
						$nInterval = " - " . $aParams['nTestPeriodMonths'] . " месеца";
					}
					$this->Cell( '', '', $aParams['sWorkPeriodType'] . $nInterval );			//Value
				}
				else
				{
					$this->Cell( '', '', $aParams['sWorkPeriodType'] );			//Value
				}
			}
			$this->Line( 16, 164, 200, 164 );
			
			$this->Ln( 4 );
			
			$this->moveX( 50 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "непред. време, опр. срок, извършване на опр. работа, заместване, със срок на изпитване" );
			
			//Row
			
			$this->Ln( 4 );
			$this->moveX( 20 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			if( !empty( $aParams['sTermOptional1'] ) )
			{
				$this->Cell( '', '', $aParams['sTermOptional1'] );
			}
			$this->Line( 10, 172, 200, 172 );
			
			$this->Ln( 4 );
			$this->moveX( 20 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			if( !empty( $aParams['sTermOptional2'] ) )
			{
				$this->Cell( '', '', $aParams['sTermOptional2'] );
			}
			$this->Line( 10, 176, 200, 176 );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "за:" );
			$this->moveX( -140 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			if( !empty( $aParams['nIsOnNightSchedule'] ) )$sIsOnNightSchedule = " ( по график с нощен труд )";
			else $sIsOnNightSchedule = "";
			$this->Cell( '', '', $aParams['FullOrNot'] . " - " . $aParams['nFullDayHours'] . " часа" . $sIsOnNightSchedule );				//Value
			$this->Line( 10, 181, 200, 181 );
			
			$this->Ln( 4 );
			
			$this->moveX( 75 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->Cell( '', '', "пълно, непълно-ч. или опр. дни работно време" );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "с основно месечно (дневно) трудово възнаграждение:" );
			$this->moveX( -120 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			if( !empty( $aParams['nSalary'] ) )
			{
				$this->Cell( '', '', convertDigitToText( (string) round( $aParams['nSalary'], 0 ) ) . " лева" );	//Value
			}
			$this->Line( 85, 190, 170, 190 );
			
			$this->moveX( -30 );
			$this->SetFont( 'FreeSans', 'B', 9 );
			if( !empty( $aParams['nSalary'] ) )
			{
				$this->Cell( '', '', $aParams['nSalary'] . " лв." );														//Value
			}
			$this->Line( 175, 190, 200, 190 );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 20 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			if( !empty( $aParams['LOS_Y'] ) )
			{
				$this->Cell( '', '', "а)  трудов стаж и професионален опит - години " . $aParams['LOS_Y'] . "x 0.6 = " . ( ( (int) $aParams['LOS_Y'] ) * 0.6 ) . "%" );		//Value
			}
			$this->Line( 10, 195, 200, 195 );
			$this->Ln( 4 );
			$this->moveX( 20 );
			$this->SetFont( 'FreeSans', '', 8 );
			if( !empty( $aParams['sRewardOptional1'] ) )
			{
				if( !empty( $aParams['LOS_Y'] ) )
				{
					$this->Cell( '', '', "б)  " . $aParams['sRewardOptional1'] );
				}
				else
				{
					$this->Cell( '', '', "a)  " . $aParams['sRewardOptional1'] );
				}
			}
			$this->Line( 10, 199, 200, 199 );
			$this->Ln( 4 );
			$this->moveX( 20 );
			if( !empty( $aParams['sRewardOptional2'] ) )
			{
				if( !empty( $aParams['LOS_Y'] ) )
				{
					$this->Cell( '', '', "в)  " . $aParams['sRewardOptional2'] );
				}
				else
				{
					$this->Cell( '', '', "б)  " . $aParams['sRewardOptional2'] );
				}
			}
			$this->Line( 10, 203, 200, 203 );
			$this->Ln( 4 );
			$this->moveX( 20 );
			$this->SetFont( 'FreeSans', '', 8 );
			if( !empty( $aParams['sRewardOptional3'] ) )
			{
				if( !empty( $aParams['LOS_Y'] ) )
				{
					$this->Cell( '', '', "г)  " . $aParams['sRewardOptional3'] );
				}
				else
				{
					$this->Cell( '', '', "в)  " . $aParams['sRewardOptional3'] );
				}
			}
			$this->Line( 10, 207, 200, 207 );
			
			//Row
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->Cell( '', '', "2. Работникът се задължава да постъпи на работа на " . $aParams['sStartDate'] . "г. или в двуседмичен срок от връчването на екземпляр от този договор." );		//Value
			$this->Ln( 3 );
			$this->moveX( 10 );
			$this->Cell( '', '', "3. Период на изплащане на трудовото възнаграждение - до края на следващият месец." );
			$this->Ln( 3 );
			$this->moveX( 10 );
			$this->Cell( '', '', "4. Срокът на предизвестието при прекраряване на трудовия договор се определя на " . "30" . " дни." );
			$this->Ln( 3 );
			$this->moveX( 10 );
			$this->Cell( '', '', "5. Други условия на трудовия договор: " );
			$this->moveX( -145 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', "Договорът се сключва в полза на работодателя." );
			
			$this->Line( 10, 221, 200, 221 );
			$this->Ln( 4 );
			$this->SetFont( 'FreeSans', '', 8 );
			$this->moveX( 10 );
			$this->Cell( '', '', "6. Полагащ се годишен платен отпуск - " );
			$this->moveX( -145 );
			$this->SetFont( 'FreeSans', 'B', 8 );
			$this->Cell( '', '', $aParams['nLeave'] . " дни." );										//Value
			$this->Line( 10, 225, 200, 225 );
			
			//Row
			
			$this->Ln( 4 );
			$this->SetFont( 'FreeSans', '', 7 );
			$this->moveX( 10 );
			$this->Cell( '', '', "7. За неуредиците в настоящия трудов договор се прилагат разпоредбите на КТ, нормативните актове по прилагането му, колективния трудов договор," );
			$this->Ln( 3 );
			$this->moveX( 10 );
			$this->Cell( '', '', "правилника за вътрешния трудов ред на предприятието, длъжностната характеристика и вътрешните правила за работна заплата." );
			$this->Ln( 3 );
			$this->moveX( 10 );
			$this->Cell( '', '', "Настоящият трудов договор се състави в два екземпляра, по един за всяка страна." );
			
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
			$this->Line( 10, 242, 200, 242 );
			
			//Row
			
			$this->Ln( 4 );
			
			$this->moveX( 10 );
			$this->Cell( '', '', "Изготвил проекта на договора," );
			$this->moveX( -120 );
			$this->Cell( '', '', "Завеждащ \"Личен състав\":" );
			$this->Line( 128, 247, 200, 247 );
			
			$this->Ln( 5 );
			
			$this->moveX( 10 );
			$this->Cell( '', '', "Проектът за трудовия договор е съгласуван с:" );
			$this->moveX( -120 );
			$this->Cell( '', '', "Гл. счетоводител:" );
			$this->Line( 128, 252, 200, 252 );
			
			$this->Line( 10, 254, 200, 254 );
			
			//Row
			
			$this->Ln( 7 );
			
			$this->moveX( 10 );
			$this->Cell( '', '', "Подписан от двете страни трудов договор и копие от длъжностната характеристика, както и заверено от ТД" );				//Value ( removed 2009-01-15 )
			$this->Ln( 4 );
			$this->moveX( 10 );
			$this->Cell( '', '', "на НАП уведомление получих на :" );
			
			$this->Ln( 7 );
			
			$this->moveX( 90 );
			$this->Cell( '', '', "Подпис на работника:" );
			$this->Line( 128, 269, 200, 269 );
			
			$this->Ln( 4 );
			
			$this->moveX( 10 );
			$this->Cell( '', '', "Работникът е постъпил на работа на " . $aParams['sStartDate'] . " г." );			//Value
			$this->moveX( -120 );
			$this->Cell( '', '', "Подпис на работника:" );
			$this->Line( 128, 273, 200, 273 );
			
			$this->Ln( 4 );
			
			$this->moveX( 90 );
			$this->Cell( '', '', "Завеждащ \"Личен състав\":" );
			$this->Line( 128, 277, 200, 277 );
			
			
			$this->SetDisplayMode( 'real' );
			$this->Output( "personContract.pdf" );
		}
	}
?>
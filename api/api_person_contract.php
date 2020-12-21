<?php
	$oHospital = new DBLeaves();
	$oPerson = new DBPersonnel();

	$right_level = 'none';
	if (!empty($_SESSION['userdata']['access_right_levels'])) {
		if (in_array('edit_personnel', $_SESSION['userdata']['access_right_levels'])) {
			$right_level = 'edit';
		} else $right_level = 'none';
	}

	switch ($aParams['api_action']) {
		case "save":
			if ( empty($aParams['type_salary']) ) {
				$aParams['type_salary'] = "fix";
			}

			if( empty( $aParams['id'] ) ) {
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Служитела не е вкаран в системата!", __FILE__, __LINE__ );
				print( $oResponse->toXML() );
				return DBAPI_ERR_INVALID_PARAM;
			}
			
//			$aParams['fix_cost'] = (float) $aParams['fix_cost'];
//			if( ($aParams['type_salary'] == 'fix') && empty( $aParams['fix_cost'] ) ) {
//				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Не е въведена фиксирана заплата!", __FILE__, __LINE__ );
//				print( $oResponse->toXML() );
//				//return DBAPI_ERR_INVALID_PARAM;
//			}
//			
//			$aParams['min_cost'] = (float) $aParams['min_cost'];
//			if( ($aParams['type_salary'] == 'min') && empty( $aParams['min_cost'] ) ) {
//				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Не е въведена минимална заплата!", __FILE__, __LINE__ );
//				print( $oResponse->toXML() );
//				//return DBAPI_ERR_INVALID_PARAM;
//			}
			
			if ( empty($aParams['factor']) ) $aParams['factor'] = 1;
			
			$aApplication = array();
//			$aApplication['id']					= $aParams['idc'];
			$aApplication['id_person']			= $aParams['id'];
//			$aApplication['type_salary'] 		= $aParams['type_salary'];
			$aApplication['fix_cost']			= $aParams['fix_cost'];
			$aApplication['min_cost']			= $aParams['min_cost'];
			$aApplication['insurance']			= $aParams['insurance'];
			$aApplication['tech_support_factor']= $aParams['factor'];
			$aApplication['shifts_factor']		= $aParams['shifts_factor'];
			$aApplication['trial_from']			= jsDateToTimestamp( $aParams['trial_from'] );
			$aApplication['trial_to'] 			= jsDateToTimestamp( $aParams['trial_to'] );
			$aApplication['serve'] 				= $aParams['serve'];
			$aApplication['rate_reward'] 		= $aParams['rate_reward'];
			$aApplication['class']				= $aParams['nClass'];
		
			global $db_personnel;
			
			$nID = $aParams['id'];
			$rs = $db_personnel->Execute("SELECT * FROM person_contract WHERE 1 AND to_arc = 0 AND id_person = {$nID}");
			
			$aData = array();
			$aData['to_arc']		= 1;
			
			$db_personnel->Execute( $db_personnel->GetUpdateSQL($rs, $aData) ) ;
			
			if( $nResult = $oHospital->update( $aApplication ) != DBAPI_ERR_SUCCESS ) {
				$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
				return $nResult;
			}
			
			$aApplication = array();
			$aApplication['id'] = $aParams['id'];
			$aApplication['tech_support_factor'] = $aParams['factor'];
			$aApplication['shifts_factor'] = $aParams['shifts_factor'];
			$aApplication['education'] = $aParams['sEducation'];
			$aApplication['speciality'] = $aParams['sSpeciality'];
			$aApplication['speciality_other'] = $aParams['sSpecialityOther'];
			$aApplication['length_of_service'] = $aParams['nLOSYears'] . "," . $aParams['nLOSMonths'] . "," . $aParams['nLOSDays'];
			//APILog::log(0, $aApplication);
			$oPerson->update($aApplication);
			
			$oResponse->setAlert("Данните бяха запазени успешно!");
			print( $oResponse->toXML() );
		break;
		
		default :
			if ( empty($aParams['sfield']) ) {
				$aParams['sfield'] = "year";
			}
				
			if ( empty($aParams['stype']) ) {
				$aParams['stype'] = DBAPI_SORT_DESC; 
			}

			if ( empty($aParams['current_page']) ) {
				$aParams['current_page'] = "1";
			}

			if ( ($aParams['api_action'] == "export_to_xls") || ($aParams['api_action'] == "export_to_pdf") ) {
				$aParams['current_page'] = "0";
			}
			
			$aPerson = $oPerson->getRecord( $aParams['id'] );
			$oDBPersonContract = new DBPersonContract();
			$sTitleMinSalary = '';
			$sTitleFixSalary = '';
			$sTitleInsurance = '';
			$sTitleFactor	 = '';
			$sTitleShiftFactor = '';
			
			$old_min_cost = -1;
			$br = 0;
			$aInfo = $oDBPersonContract->getMinCostByIDPerson($aParams['id']);
			foreach ($aInfo as $key => $val) {
				if ($old_min_cost != $val['min_cost']) {
//					if (empty($br) && !empty($val['min_cost'])) {
//							$old_min_cost	= $val['min_cost'];
//							if($aInfo[$key+1]['min_cost']!=$val['min_cost'] ) {
//								$sTitleMinSalary.= "\nЗаплата - ".$val['min_cost']." лв.\nДата: ".$val['updated_time_']."\nПроменил: ".$val['updated_user']."\n";
//								$br++;
//							}
//							continue;
//						}
						
					if(isset($aInfo[$key+1]) && $aInfo[$key+1]['min_cost']!=$val['min_cost']) {
						$sTitleMinSalary.= "\nЗаплата - ".$val['min_cost']." лв.\nДата: ".$val['updated_time_']."\nПроменил: ".$val['updated_user']."\n";
						$br++;
						$old_min_cost	= $val['min_cost'];
						if ($br == 5) break;
					}
				} else {
						if(isset($aInfo[$key+1]) && $aInfo[$key+1]['min_cost']!=$val['min_cost'] && $aInfo[0]['min_cost']!=$val['min_cost']) {
						$sTitleMinSalary.= "\nЗаплата - ".$val['min_cost']." лв.\nДата: ".$val['updated_time_']."\nПроменил: ".$val['updated_user']."\n";
						$br++;
						$old_min_cost	= $val['min_cost'];
						if ($br == 5) break;
					}
				}
			}
			
			$old_fix_cost	= -1;
			$br		= 0;
			$aInfo	= $oDBPersonContract->getFixCostByIDPerson($aParams['id']);
			foreach ($aInfo as $key => $val) {
				if ($old_fix_cost != $val['fix_cost']) {
//					if (empty($br) && !empty($val['fix_cost'])) {
//							$old_fix_cost	= $val['fix_cost'];
//							if($aInfo[$key+1]['fix_cost']!=$val['fix_cost']) {
//								$sTitleFixSalary.= "\nЗаплата - ".$val['fix_cost']." лв.\nДата: ".$val['updated_time_']."\nПроменил: ".$val['updated_user']."\n";
//								$br++;
//							}
//							continue;
//					}
					if(isset($aInfo[$key+1]) && $aInfo[$key+1]['fix_cost']!=$val['fix_cost']) {
						$sTitleFixSalary.= "\nЗаплата - ".$val['fix_cost']." лв.\nДата: ".$val['updated_time_']."\nПроменил: ".$val['updated_user']."\n";
						$br++;
						$old_fix_cost	= $val['fix_cost'];
						if ($br == 5) break;
					}
				} else {
						if(isset($aInfo[$key+1]) && $aInfo[$key+1]['fix_cost']!=$val['fix_cost'] && $aInfo[0]['fix_cost']!=$val['fix_cost']) {
							$sTitleFixSalary.= "\nЗаплата - ".$val['fix_cost']." лв.\nДата: ".$val['updated_time_']."\nПроменил: ".$val['updated_user']."\n";
							$br++;
							$old_fix_cost	= $val['fix_cost'];
							if ($br == 5) break;
					}
				}
			}
			
			$old_insurance	= -1;
			$br		= 0;
			$aInfo	 = $oDBPersonContract->getInsuranceByIDPerson($aParams['id']);
			foreach ($aInfo as $key => $val) {
				if ($old_insurance != $val['insurance']) {
//					if (empty($br)  && !empty($val['insurance'])) {
//							$old_insurance	= $val['insurance'];
//							if($aInfo[$key+1]['insurance']!=$val['insurance']) {
//								$sTitleInsurance.= "\nМин.осиг.праг - ".$val['insurance']." лв.\nДата: ".$val['updated_time_']."\nПроменил: ".$val['updated_user']."\n";
//								$br++;
//							}
//							continue;
//					}
					if (isset($aInfo[$key+1]) && $aInfo[$key+1]['insurance']!=$val['insurance']){
						$sTitleInsurance.= "\nМин.осиг.праг - ".$val['insurance']." лв.\nДата: ".$val['updated_time_']."\nПроменил: ".$val['updated_user']."\n";
						$br++;
						$old_insurance	= $val['insurance'];
						if ($br == 5) break;
					}
				} else {
					if (isset($aInfo[$key+1]) && $aInfo[$key+1]['insurance']!=$val['insurance'] && $aInfo[0]['insurance']!=$val['insurance']){
						$sTitleInsurance.= "\nМин.осиг.праг - ".$val['insurance']." лв.\nДата: ".$val['updated_time_']."\nПроменил: ".$val['updated_user']."\n";
						$br++;
						$old_insurance	= $val['insurance'];
						if ($br == 5) break;
					}
				}
			}
			
			$old_factor	= -1;
			$br		= 0;
			$aInfo	 = $oDBPersonContract->getTechSupportFactorByIDPerson($aParams['id']);
			foreach ($aInfo as $key => $val) {
				if ($old_factor != $val['tech_support_factor']) {
//						if (empty($br)  && !empty($val['tech_support_factor'])) {
//							$old_factor = $val['tech_support_factor'];
//							if($aInfo[$key+1]['tech_support_factor']!=$val['tech_support_factor']) {
//								$sTitleFactor.= "\nФактор - ".$val['tech_support_factor']."\nДата: ".$val['updated_time_']."\nПроменил: ".$val['updated_user']."\n";
//								$br++;
//							}
//							continue;
//						}
						if (isset($aInfo[$key+1]) && $aInfo[$key+1]['tech_support_factor']!=$val['tech_support_factor']) {					
							$sTitleFactor.= "\nФактор - ".$val['tech_support_factor']."\nДата: ".$val['updated_time_']."\nПроменил: ".$val['updated_user']."\n";
							$br++;
							$old_factor = $val['tech_support_factor'];
							if ($br == 5) break;
						}
				} else {
					if (isset($aInfo[$key+1]) && $aInfo[$key+1]['tech_support_factor']!=$val['tech_support_factor'] && $aInfo[0]['tech_support_factor']!=$val['tech_support_factor']) {					
							$sTitleFactor.= "\nФактор - ".$val['tech_support_factor']."\nДата: ".$val['updated_time_']."\nПроменил: ".$val['updated_user']."\n";
							$br++;
							$old_factor = $val['tech_support_factor'];
							if ($br == 5) break;
						}
				}
			}
			
			$old_shift_factor	= -1;
			$br		= 0;
			$aInfo	 = $oDBPersonContract->getShiftsFactorByIDPerson($aParams['id']);
			foreach ($aInfo as $key => $val) {
				if ($old_shift_factor != $val['shifts_factor']) {
						if (isset($aInfo[$key+1]) && $aInfo[$key+1]['shifts_factor']!=$val['shifts_factor']) {					
							$sTitleShiftFactor.= "\nФактор - ".$val['shifts_factor']."\nДата: ".$val['updated_time_']."\nПроменил: ".$val['updated_user']."\n";
							$br++;
							$old_factor = $val['shifts_factor'];
							if ($br == 5) break;
						}
				} else {
					if (isset($aInfo[$key+1]) && $aInfo[$key+1]['shifts_factor']!=$val['shifts_factor'] && $aInfo[0]['shifts_factor']!=$val['shift_factor']) {					
							$sTitleShiftFactor.= "\nФактор - ".$val['shifts_factor']."\nДата: ".$val['updated_time_']."\nПроменил: ".$val['updated_user']."\n";
							$br++;
							$old_factor = $val['shifts_factor'];
							if ($br == 5) break;
						}
				}
			}
			
			$oResponse->setFormElement( 'form1', 'sEducation', array( "value" => $aPerson['education'] ), $aPerson['education'] );
			$oResponse->setFormElement( 'form1', 'sSpeciality', array( "value" => $aPerson['speciality'] ), $aPerson['speciality'] );
			$oResponse->setFormElement( 'form1', 'sSpecialityOther', array( "value" => $aPerson['speciality_other'] ), $aPerson['speciality_other'] );
			
			$aLOS = split( ",", $aPerson['length_of_service'] );

			if (!empty($aLOS[0]))
				$oResponse->setFormElement( 'form1', 'nLOSYears', array( "value" => $aLOS[0] ), $aLOS[0] );
			if (!empty($aLOS[1]))
				$oResponse->setFormElement( 'form1', 'nLOSMonths', array( "value" => $aLOS[1] ), $aLOS[1] );
			if (!empty($aLOS[2]))
				$oResponse->setFormElement( 'form1', 'nLOSDays', array( "value" => $aLOS[2] ), $aLOS[2] );
			
			$oLeave = array();
			if( ($nResultOnce = $oHospital->getResultOnce($aParams['id'], $oLeave)) == DBAPI_ERR_SUCCESS ) {
				if ( !empty($oLeave) ) {
					if ( !empty($oLeave['fix_cost']) && $oLeave['fix_cost'] != "0.00" ) {
						$oResponse->setFormElement('form1', 'fix_cost',		array(), $oLeave['fix_cost']);
						$oResponse->setFormElement('form1', 'fix_salary',	array('checked' => 'checked'), '' );						
					} else {
						$oResponse->setFormElement('form1', 'fix_cost',		array('disabled' => 'disabled'), '0.00' );
					}

					if ( !empty($oLeave['min_cost']) && $oLeave['min_cost'] != "0.00" ) {
						$oResponse->setFormElement('form1', 'min_cost',		array(), $oLeave['min_cost']);
						$oResponse->setFormElement('form1', 'min_salary',	array('checked' => 'checked'), '' );
					} else {
						$oResponse->setFormElement('form1', 'min_cost',		array('disabled' => 'disabled'), '0.00' );
					}
					
//					$oResponse->setFormElement('form1', 'idc',				array(), $oLeave['_id']);
//					$oResponse->setFormElement('form1', 'type_salary',		array(), $oLeave['type_salary']);
					$oResponse->setFormElement('form1', 'insurance',		array(), $oLeave['insurance']);
					$oResponse->setFormElement('form1', 'trial_from',		array(), $oLeave['trial_from']);
					$oResponse->setFormElement('form1', 'trial_to',			array(), $oLeave['trial_to']);
					$oResponse->setFormElement('form1', 'factor',			array(), $oLeave['factor']);
					$oResponse->setFormElement('form1',	'shifts_factor',		array(), $oLeave['shifts_factor']);
					$oResponse->setFormElement('form1', 'serve',			array(), 'Няма данни!');
					$oResponse->setFormElement('form1', 'rate_reward',		array(), $oLeave['rate_reward']);
					$oResponse->setFormElement('form1', 'sMinSalary',		array('title' => "{$sTitleMinSalary}"));
					$oResponse->setFormElement('form1', 'sFixSalary',		array('title' => "{$sTitleFixSalary}"));
					$oResponse->setFormElement('form1', 'sInsurance',		array('title' => "{$sTitleInsurance}"));
					$oResponse->setFormElement('form1', 'sFactor',		array('title' => "{$sTitleFactor}"));
					$oResponse->setFormElement('form1', 'sShiftsFactor',		array('title' => "{$sTitleShiftFactor}"));
					$oResponse->setFormElement( "form1", "nClass",			array( "value" => $oLeave['class'] ));
				} else {
					$oResponse->setFormElement('form1', 'fix_salary',	array('checked' => 'checked'), '' );	
				}
			} else $oResponse->setError( $nResultOnce );

//			if ( ($nResult = $oHospital->getResult( 
//				$aParams['id'], 
//				$aParams['sfield'],
//				$aParams['stype'], 
//				$aParams['current_page'],
//				$oResponse)) != DBAPI_ERR_SUCCESS ) {
//				$oResponse->setError( $nResult );
//			} else {
//				$oResponse->setField( 'year', 			'година', 					'Сортирай по година' );
//				$oResponse->setField( 'due_days', 		'полагаеми дни', 			'Сортирай по дни' );
//				$oResponse->setField( 'used_days',		'използвани дни',			'Сортирай по дни' );
//				$oResponse->setField( 'remain',			'оставащи дни', 			'Сортирай по дни' );
//				$oResponse->setField( 'hospital',		'болнични', 				'Сортирай по болнични' );
//				$oResponse->setField( 'unpaid',			'неплатени', 				'Сортирай по неплатени' );
//				$oResponse->setField( 'student',		'пл. полагаем', 			'Сортирай по полагаеми' );								
//				$oResponse->setField( 'updated_user', 	'...', 						'Сортиране по последно редактирал', 'images/dots.gif' );
//				$oResponse->setField( '', 				'',  						'Изтрий', "images/cancel.gif", "delLeave", '');
//				
//				$oResponse->setFIeldLink( 'year',		'openLeave' );
//				$oResponse->setFIeldLink( 'due_days',	'openLeave' );
//			}
			print( $oResponse->toXML() );
		break;
	}	
?>
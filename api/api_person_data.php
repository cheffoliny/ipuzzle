<?php
	$oPersonData = New DBBase( $db_personnel, 'personnel' );

	class MyHandler 
		extends APIHandler {
			
			function setFields($aParams) {
				global $oResponse;
			}			
			
			function Handler( $aParams ) {
				global $oResponse, $db_personnel, $db_name_personnel;
				$aData = array();
				//APILog::log(0, $aParams);

				switch( $aParams['api_action']) {
					case "save" :
						$this->SaveData( $aParams );
						print( $oResponse->toXML() );
					break;
					
					case "salary" :
						$this->Salary( $aParams );
						print( $oResponse->toXML() );
					break;
										
					default :			
						if ( $nResult = $this->loadData( $aParams['id'], $aData ) != DBAPI_ERR_SUCCESS ) {
							$oResponse->setError( $nResult, "Проблем при извличане на данните!", __FILE__, __LINE__ );
							print( $oResponse->toXML() );
							return $nResult;
						}
						
						$aData = current($aData);
						//!empty($aData['code']) ? $aParams['code'] = $aData['code'] : $aParams['code'] = 0;
						//$aParams['history'] = isset($aData['history']) && !empty($aData['history']) ? $aData['history'] : 0;
						//APILog::log(0, $aData);
						
						if ( $nResult = $this->loadHistory( $aParams ) != DBAPI_ERR_SUCCESS ) {
							$oResponse->setError( $nResult, "Проблем при извличане на назначенията!", __FILE__, __LINE__ );
							print( $oResponse->toXML() );
							return $nResult;
						}
						
						$oFirms			= new DBFirms();
						$oOffices		= new DBOffices();
						$oPositionsNC	= new DBPositionsNC();
					
						$aFirms		= array();
						$aOffices	= array();
						$aPositions = array();
						
						$nIDFirm		= isset($aParams['nIDFirm']) 		? $aParams['nIDFirm'] 		: 0;
						$nIDOffice		= isset($aParams['nIDOffice']) 		? $aParams['nIDOffice'] 	: 0;
						$nPositionNKID	= isset($aParams['nPositionNKID']) 	? $aParams['nPositionNKID'] : 0;

						$aFirm		= isset($aData['id_firm']) 			? $aData['id_firm'] 		: 0;
						$aOffs		= isset($aData['id_office']) 		? $aData['id_office'] 		: 0;
						$aPosition 	= isset($aData['id_position_nc']) 	? $aData['id_position_nc'] 	: 0;
						$person 	= isset($aParams['id']) 			? $aParams['id'] 			: 0;
												
						$in 	= $aData['date_in'];
						$out 	= $aData['date_out'];
						
						if ( !empty($aData['date_in']) ) {
							if ( $aData['date_in'] > 315525600 ) {
								$aData['date_in'] = date("d.m.Y", $aData['date_in']);
							} else $aData['date_in'] = "";
						} else $aData['date_in'] = "";

						if ( !empty($aData['date_out']) ) {
							if ( $aData['date_out'] > 315525600 ) {
								$aData['date_out'] = date("d.m.Y", $aData['date_out']);
							} else $aData['date_out'] = "";
						} else $aData['date_out'] = "";

						if ( ($in > 315525600) && ($out > $in) ) {
							$length_service = (int) ( ($out - $in) / 2592000 );    //Месеци
						} elseif ($in > 315525600) {
							$length_service = (int) ( (time() - $in) / 2592000 );    //Месеци
						} else {
							$length_service = "";
						}
						
						//APILog::log(0, $oResponse);

						foreach ($oResponse->oResult->aData as $key => &$value) {
							$value['days'] 		= $value['days']." м.";
							$value['region'] 	= $value['firm']." / ".$value['region'];
							
							$oResponse->setDataAttributes( $key, "days", array("style" => "text-align: right;") );
						}						
						
						$aDataLeaves  = array();
						
						$sQueryLeaves = "
							( 
								SELECT SUM(due_days) AS days 
									FROM {$db_name_personnel}.person_leaves
								WHERE id_person = {$person}
									AND type = 'leave'
									AND to_arc = 0
							) UNION ( 
								SELECT SUM(application_days) AS days
									FROM {$db_name_personnel}.person_leaves
								WHERE id_person = {$person}
									AND type = 'application'
									AND to_arc = 0
							)
						";
								
						$aDataLeaves = $db_personnel->getArray($sQueryLeaves);
						
						$days1 = isset($aDataLeaves[0]['days']) && !empty($aDataLeaves[0]['days']) ? $aDataLeaves[0]['days'] : 0;
						$days2 = isset($aDataLeaves[1]['days']) && !empty($aDataLeaves[1]['days']) ? $aDataLeaves[1]['days'] : 0;
						
						if ( ($days1 - $days2) > 0 ) {
							$oResponse->setFormElement("form1", "countDays", 		array(), ($days1 - $days2));
							$oResponse->setFormElement("form1", "count", 			array(), ($days1 - $days2));	
						} else {
							$oResponse->setFormElement("form1", "countDays", 		array(), 0);
							$oResponse->setFormElement("form1", "count", 			array(), 1);								
						}
						
						$oResponse->setFormElement('form1', 'nIDFirm',			array(), '');
						$oResponse->setFormElement('form1', 'nIDOffice',		array(), '');
						
						$oResponse->setFormElement('form1', 'nIDFirm2',			array(), '');
						$oResponse->setFormElement('form1', 'nIDOffice2',		array(), '');
												
						$oResponse->setFormElement('form1', 'nPositionNKID',	array(), '');
						
						$oResponse->setFormElement('form1', 'code',				array(), '');
											
						$oResponse->setFormElement('form1', 'idc',				array(), $aData['_id']);
						//$oResponse->setFormElement('form1', 'id_firm',		array(), $aData['id_firm']);
						//$oResponse->setFormElement('form1', 'nIDOffice',		array(), $aData['id_office']);
//						$oResponse->setFormElement('form1', 'nPositionNKID',		array(), $aData['position_nc']);
						$oResponse->setFormElement('form1', 'id_object',		array(), $aData['id_object']);
						$oResponse->setFormElement('form1', 'id_position',		array(), $aData['id_position']);
						$oResponse->setFormElement('form1', 'position',			array(), $aData['position']);
						$oResponse->setFormElement('form1', 'date_in',			array(), $aData['date_in']);
						$oResponse->setFormElement('form1', 'date_out',			array(), $aData['date_out']);
						$oResponse->setFormElement('form1', 'length_service',	array(), $length_service);
						$oResponse->setFormElement('form1', 'status',			array(), $aData['status']);
						
						$oResponse->setFormElement('form1', 'firm',				array(), $aData['firm']);
						$oResponse->setFormElement('form1', 'region',			array(), $aData['region']);
						$oResponse->setFormElement('form1', 'obj',				array(), $aData['object']);
						
						$oResponse->setFormElement('form1', 'description',		array(), "Домашен отпуск");
						
						//$oResponse->setField( 'code', 		'код', 				'Сортирай по код' );
						$oResponse->setField( 'position', 	'длъжност', 		'Сортирай по длъжност' );
						$oResponse->setField( 'region', 	'регион', 			'Сортирай по регион' );
						$oResponse->setField( 'object', 	'обект', 			'Сортирай по обект' );
						$oResponse->setField( 'date_in', 	'от', 				'Сортирай по дата' );
						$oResponse->setField( 'date_out', 	'до', 				'Сортирай по дата' );
						$oResponse->setField( 'days', 		'месеца', 			'Сортирай по месеци' );
						
						$oResponse->setFormElementChild('form1', 'nIDFirm',			array('value' => 0), 'Изберете');
						$oResponse->setFormElementChild('form1', 'nIDOffice',		array('value' => 0), 'Изберете');
						$oResponse->setFormElementChild('form1', 'nPositionNKID',	array('value' => 0), 'Изберете');
						
						$aFirms = $oFirms->getFirms();
						$aPositions = $oPositionsNC->getPositionsNC();
											
						$nIDFirm = !empty($nIDFirm) ? $nIDFirm : $aFirm;
						$nIDOffice = !empty($nIDOffice) ? $nIDOffice : $aOffs;
						$nPositionNKID = !empty($nPositionNKID) ? $nPositionNKID :$aPosition;
						

						$aSalaryCode	= array();
						$oSalaryCode 	= new DBSalaryEarning();
						$aSalaryCode 	= $oSalaryCode->getEarningCode();
						$dCode 			= "+ДОМ";	
						
						
						foreach ( $aSalaryCode as $keyCode => $valCode ) {
							$tmpArr 	= array();
							$tmpArr 	= explode('$$$', $valCode);
							$code 		= isset($tmpArr[0]) 		? $tmpArr[0] 		: "";
							$name 		= isset($tmpArr[1]) 		? $tmpArr[1] 		: "";
							
							if ( trim($code) == $dCode ) {
								$oResponse->setFormElementChild('form1', 'code', array('value' => $keyCode, 'id' => $name, 'selected' => 'selected'), $code);
							} else {
								$oResponse->setFormElementChild('form1', 'code', array('value' => $keyCode, 'id' => $name), $code);
							}
						}							
						
						foreach ( $aFirms as $key => $val ) {
							if ( $nIDFirm == $key ) {
								$oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => $key, 'selected' => 'selected'), $val);
							} else $oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => $key), $val);
						}	
									
						unset($key); unset($val);
						
						foreach ( $aFirms as $key => $val ) {
							if ( $nIDFirm == $key ) {
								$oResponse->setFormElementChild('form1', 'nIDFirm2', array('value' => $key, 'selected' => 'selected'), $val);
							} else $oResponse->setFormElementChild('form1', 'nIDFirm2', array('value' => $key), $val);
						}	
									
						unset($key); unset($val);						
									
						if ( $nIDFirm > 0 ) {
							$aOffices = $oOffices->getFirmOfficesAssoc( $nIDFirm );
							foreach ( $aOffices as $key => $val ) {
								if ( $nIDOffice == $key ) {
									$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => $key, 'selected' => 'selected'), $val['name']);
								} else $oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => $key), $val['name']);
							}	
							
							foreach ( $aOffices as $key => $val ) {
								if ( $nIDOffice == $key ) {
									$oResponse->setFormElementChild('form1', 'nIDOffice2', array('value' => $key, 'selected' => 'selected'), $val['name']);
								} else $oResponse->setFormElementChild('form1', 'nIDOffice2', array('value' => $key), $val['name']);
							}								
						}
					
						unset($key); unset($val);					
						
						
						foreach ( $aPositions as $key => $val ) {
							if ( $nPositionNKID == $key ) {
								$oResponse->setFormElementChild('form1', 'nPositionNKID', array('value' => $key, 'selected' => 'selected'), $val);
							} else $oResponse->setFormElementChild('form1', 'nPositionNKID', array('value' => $key), $val);
						}
						
						print( $oResponse->toXML() );
					break;
				}
			}
			
			
			function SaveData( $aParams )
			{
				global $db_name_personnel, $db_personnel, $oPersonData, $oResponse;
				
				$db->debug = true;
				
				$oPositionsNC 	 	= new DBPositionsNC();
				$oPersonContract 	= new DBPersonContract();
				$oDBPersonLeaves 	= new DBPersonLeaves();
				
				$confirm 			= isset($aParams['confirm']) && !empty($aParams['confirm']) ? 1 : 0;
				$person 			= $aParams['id'];

				if ( empty($aParams['id']) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Проблем с идентификация на служитела!", __FILE__, __LINE__ );
					//print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}

				if ( empty($aParams['nIDFirm']) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето фирма!", __FILE__, __LINE__ );
					//print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}

				if ( empty($aParams['nIDOffice']) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето регион!", __FILE__, __LINE__ );
					//print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}

				if ( empty($aParams['id_position']) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето длъжност!", __FILE__, __LINE__ );
					//print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}
				
				if ( empty($aParams['nPositionNKID']) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето длъжност по НКИД!", __FILE__, __LINE__ );
					//print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				} 

				if ( empty($aParams['date_in']) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете дата на постъпване!", __FILE__, __LINE__ );
					//print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}

				if ( ($aParams['status'] == "vacate") ) {
					if ( empty($aParams['date_out']) || empty($aParams['date_in']) ) {
						$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете дати на назначение и напускане!", __FILE__, __LINE__ );
						//print( $oResponse->toXML() );
						return DBAPI_ERR_INVALID_PARAM;
					}
					
//					if ( !$confirm ) {
//						$aDataLeaves  = array();
//						
//						$sQueryLeaves = "
//							( 
//								SELECT SUM(due_days) AS days 
//									FROM {$db_name_personnel}.person_leaves
//								WHERE id_person = {$person}
//									AND type = 'leave'
//									AND to_arc = 0
//							) UNION ( 
//								SELECT SUM(application_days) AS days
//									FROM {$db_name_personnel}.person_leaves
//								WHERE id_person = {$person}
//									AND type = 'application'
//									AND to_arc = 0
//							)
//						";
//								
//						$aDataLeaves = $db_personnel->getArray($sQueryLeaves);
//									
//						$days1 = isset($aDataLeaves[0]['days']) && !empty($aDataLeaves[0]['days']) ? $aDataLeaves[0]['days'] : 0;
//						$days2 = isset($aDataLeaves[1]['days']) && !empty($aDataLeaves[1]['days']) ? $aDataLeaves[1]['days'] : 0;
//						
//						if ( ($days1 - $days2) > 0 ) {
//							$oResponse->setFormElement("form1", "confirm", array(), 1);
//							$oResponse->setFormElement("form1", "count", array(), ($days1 - $days2));
//							return 0;
//						}					
//					}
										
					$oSalary = New DBBase( $db_personnel, 'salary' );
					
					$aDuplicate = array();
					$aWhere = array();
					
					$aWhere[] = " id_person = {$aParams['id']} ";
					$aWhere[] = " is_earning = 0 ";
					$aWhere[] = " to_arc = 0 ";
							
					if( $nResult = $oSalary->getResult( $aDuplicate, NULL, $aWhere ) != DBAPI_ERR_SUCCESS ) {
						$oResponse->setError( DBAPI_ERR_SQL_QUERY, "Проблем при комуникацията!", __FILE__, __LINE__ );
						return DBAPI_ERR_SQL_QUERY;
					}
					
					$yemon = date("Y").date( "m", mktime( 0, 0, 0, date("m")-1, date("d"), date("Y") ) );
					
					$sql = "
						SELECT SUM(total_sum) as sm 
						FROM salary 
						WHERE id_person = {$aParams['id']} 
							AND month = '{$yemon}' 
					";
					
					$db_personnel->StartTrans();
					try {
						$err = $db_personnel->getArray( $sql );
						
						if ( isset($err[0]['sm']) && $err[0]['sm'] < 0 ) {
							$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Служителя има удръжки!!!", __FILE__, __LINE__ );
							return DBAPI_ERR_INVALID_PARAM;
						}
												
						$db_personnel->CompleteTrans();
					} catch(Execution $err) {
						$db_personnel->FailTrans();
					}
				
//					if( !empty( $aDuplicate ) ) {
//						$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Служитела има удръжки!!!", __FILE__, __LINE__ );
//						return DBAPI_ERR_INVALID_PARAM;
//					}	
						
					$oActive = New DBBase( $db_personnel, 'person_actives' );
					
					$aDuplicate = array();
					$aWhere = array();
					
					$aWhere[] = " id_person = {$aParams['id']} ";
					$aWhere[] = " to_arc = 0 ";
							
					if( $nResult = $oActive->getResult( $aDuplicate, NULL, $aWhere ) != DBAPI_ERR_SUCCESS ) {
						$oResponse->setError( DBAPI_ERR_SQL_QUERY, "Проблем при комуникацията!", __FILE__, __LINE__ );
						return DBAPI_ERR_SQL_QUERY;
					}
					
					if( !empty( $aDuplicate ) ) {
						$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Служитела има активи!!!", __FILE__, __LINE__ );
						return DBAPI_ERR_INVALID_PARAM;
					}		
					
					$nLeaveDays = $oDBPersonLeaves->getHowManyDaysToAdd( $aParams['id'] );
					
					//$dayLeaves = 0.0548;
					$dayLeaves = round( ( $nLeaveDays / 365 ), 4 );
					$dateIn = jsDateToTimestamp( $aParams['date_in'] );
					$dateOut = jsDateToTimestamp( $aParams['date_out'] );
					$yearIn = date("Y", $dateIn);
					$yearOut = date("Y", $dateOut);
					if ( $yearIn < $yearOut ) {
						$dateFrom = mktime(0, 0, 0, 1, 0, $yearOut);
					} else $dateFrom = $dateIn;
					$days = ( ($dateOut) - $dateFrom) / 86400;
					$person = $aParams['id'];
					
					$leaveDays = round($dayLeaves * $days);  // Полагаема отпуска до момента на напускането - база 20/365 
					
					if( $yearOut >= date( "Y" ) )
					{
						$sQuery = "
							UPDATE person_leaves
							SET due_days = '{$leaveDays}'
							WHERE type = 'leave'
								AND id_person = '{$person}'
								AND year = '{$yearOut}'
						";
						
						$db_personnel->execute( $sQuery );
					}
				}
				
				if ( $aParams['status'] == "moved" ) {
					if ( empty($aParams['date_out']) || empty($aParams['date_in']) ) {
						$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете дати на назначение и напускане!", __FILE__, __LINE__ );
						return DBAPI_ERR_INVALID_PARAM;
					}
					
//					if ( !$confirm ) {
//						$aDataLeaves  = array();
//						
//						$sQueryLeaves = "
//							( 
//								SELECT SUM(due_days) AS days 
//									FROM {$db_name_personnel}.person_leaves
//								WHERE id_person = {$person}
//									AND type = 'leave'
//									AND to_arc = 0
//							) UNION ( 
//								SELECT SUM(application_days) AS days
//									FROM {$db_name_personnel}.person_leaves
//								WHERE id_person = {$person}
//									AND type = 'application'
//									AND to_arc = 0
//							)
//						";
//								
//						$aDataLeaves = $db_personnel->getArray($sQueryLeaves);
//						
//						$days1 = isset($aDataLeaves[0]['days']) && !empty($aDataLeaves[0]['days']) ? $aDataLeaves[0]['days'] : 0;
//						$days2 = isset($aDataLeaves[1]['days']) && !empty($aDataLeaves[1]['days']) ? $aDataLeaves[1]['days'] : 0;
//						
//						if ( ($days1 - $days2) > 0 ) {
//							$oResponse->setFormElement("form1", "confirm", array(), 1);
//							$oResponse->setFormElement("form1", "count", array(), ($days1 - $days2));
//							return 0;
//						}												
//					}
					
					$nID = $aParams['id'];
					$cUser = $_SESSION['userdata']['id_person'];
					$cTimeIn = date("Y-m-d", jsDateToTimestamp( $aParams['date_in'] ));
					$cTimeOut = date("Y-m-d", jsDateToTimestamp( $aParams['date_out'] ));
					
					$sql = "
						INSERT INTO `personnel` 
							(
  							`code`,
  							`id_history`,
  							`id_office`,
  							`id_region_object`,
  							`id_position`,
  							`id_position_nc`,
  							`fname`,
  							`mname`,
  							`lname`,
  							`phone`,
  							`business_phone`,
  							`mobile`,
  							`mphones`,
  							`iban`,
  							`email`,
  							`icq`,
  							`skype`,
  							`image_file`,
  							`character_file`,
  							`date_from`,
  							`status`,
  							`vacate_date`,
  							`length_service`,
  							`note`,
  							`address`,
  							`addr_city`,
  							`addr_street`,
  							`addr_num`,
  							`addr_floor`,
  							`addr_app`,
  							`egn`,
  							`lk_num`,
  							`lk_date`,
  							`lk_izdatel`,
  							`sk_num`,
  							`family_status`,
  							`tech_support_factor`,
  							`updated_time`,
  							`updated_user`,
  							`to_arc`
						)
						SELECT
  							`code`,
  							`id`,
  							`id_office`,
  							`id_region_object`,
  							`id_position`,
  							`id_position_nc`,
  							`fname`,
  							`mname`,
  							`lname`,
  							`phone`,
  							`business_phone`,
  							`mobile`,
  							`mphones`,
  							`iban`,
  							`email`,
  							`icq`,
  							`skype`,
  							`image_file`,
  							`character_file`,
							'{$cTimeIn}',
  							'moved',
  							'{$cTimeOut}',
  							`length_service`,
  							`note`,
  							`address`,
  							`addr_city`,
  							`addr_street`,
  							`addr_num`,
  							`addr_floor`,
  							`addr_app`,
  							`egn`,
  							`lk_num`,
  							`lk_date`,
  							`lk_izdatel`,
  							`sk_num`,
  							`family_status`,
  							`tech_support_factor`,
  							NOW(),
							{$cUser},
  							0
						FROM personnel
						WHERE id = {$nID}
					";
					
					$aPerson = array();
					$aPerson['id'] 					= $aParams['id'];
					$aPerson['id_office'] 			= 0;
					$aPerson['id_region_object'] 	= 0;
					$aPerson['id_position'] 		= 0;
					$aPerson['id_position_nc'] 		= 0;
					$aPerson['status'] 				= 'active';
					$aPerson['date_from'] 			= date(jsDateToTimestamp( $aParams['date_out'] ));
					$aPerson['vacate_date'] 		= '0000-00-00';
					$aPerson['updated_time']		= time();
					$aPerson['updated_user']		= $_SESSION['userdata']['id_person'];
					
					$db_personnel->StartTrans();
					
					$newID = 0;
					
					try {
						$err = $db_personnel->Execute( $sql );
						$newID = $db_personnel->Insert_ID();
						
						$oPersonData->update( $aPerson );
												
						$db_personnel->CompleteTrans();
					} catch(Execution $err) {
						$db_personnel->FailTrans();
					}
						
				} else {
				
					$aPersonData = array();
					$aPersonData['id'] 					= $aParams['idc'];
					$aPersonData['id_person'] 			= $aParams['id'];
					$aPersonData['id_office'] 			= $aParams['nIDOffice'];
					$aPersonData['id_region_object'] 	= $aParams['id_object'];
					$aPersonData['id_position'] 		= $aParams['id_position'];
					$aPersonData['id_position_nc'] 		= $aParams['nPositionNKID'];
					$aPersonData['status'] 				= $aParams['status'];
					$aPersonData['date_from'] 			= date(jsDateToTimestamp( $aParams['date_in'] ));
					$aPersonData['vacate_date'] 		= date(jsDateToTimestamp( $aParams['date_out'] ));
					$aPersonData['length_service'] 		= $aParams['length_service'];
					$aPersonData['updated_time']		= time();
					$aPersonData['updated_user']		= $_SESSION['userdata']['id_person'];
					
					$db_personnel->StartTrans();
					
					//APILog::log(0, $data);
					
					if( $nResult = $oPersonData->update( $aPersonData ) != DBAPI_ERR_SUCCESS ) {
						$db_personnel->FailTrans();
						$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
						print( $oResponse->toXML() );
						return $nResult;
					}
					
					$db_personnel->CompleteTrans();
					
					if (!empty($aParams['nPositionNKID'])) {
						$nInsurance=$oPositionsNC->getPersonMinSalary($aParams['idc']);

//						$sQuery = " 
//							UPDATE person_contract
//								SET insurance = {$nInsurance}
//							WHERE 1
//								AND to_arc = 0
//								AND id_person = {$aParams['idc']}
//						";
//						$db_personnel->Execute ( $sQuery );

						$rs = $db_personnel->Execute("SELECT * FROM person_contract WHERE 1 AND to_arc = 0 AND id_person = {$aParams['idc']}");
						$aResult = $oPersonContract->getAllByIDPerson($aParams['idc']);
//						APILog::Log(0,$rs);
//						APILog::Log(0,$aResult);

						$aApplication = array();
		
						if (!count($aResult))
						{
							$aApplication['id_person']			= $aParams['idc'];
							$aApplication['insurance']			= $nInsurance;
						}
						else
						{
							$aApplication['id_person']			= $aResult['id_person'];
							$aApplication['type_salary'] 		= $aResult['type_salary'];
							$aApplication['fix_cost']			= $aResult['fix_cost'];
							$aApplication['min_cost']			= $aResult['min_cost'];
							if (empty($aResult['insurance']) || $aResult['insurance']==0 ) {
								$aApplication['insurance']		= $nInsurance;
							} else { 
								$aApplication['insurance']		= $aResult['insurance'];
							}
							$aApplication['trial_from']			= $aResult['trial_from'];
							$aApplication['trial_to'] 			= $aResult['trial_to'];
							$aApplication['serve'] 				= $aResult['serve'];
							$aApplication['rate_reward'] 		= $aResult['rate_reward'];
							$aApplication['tech_support_factor']= $aResult['tech_support_factor'];
							$aApplication['to_arc']				= 0;
						}
						
						$aData = array();
						$aData['updated_user']	= $_SESSION['userdata']['id_person'];	
						$aData['updated_time']	= time();
						$aData['to_arc']		= 1;
			
						$db_personnel->Execute( $db_personnel->GetUpdateSQL($rs, $aData) ) ;

						if( $nResult = $oPersonContract->update( $aApplication ) != DBAPI_ERR_SUCCESS ) {
							$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
							return $nResult;
						}
					}
				
					$oResponse->setAlert("Данните бяха запазени успешно!");
					return DBAPI_ERR_SUCCESS;
				}
			}
				
			function Salary( $aParams ) {
				global $db_personnel, $oSalary, $oResponse;
				
				//var_dump($aParams);
				
				$nCode = !empty( $aParams['nCodeSalary'] ) ? $aParams['nCodeSalary'] : "";
				$aCode = "";
				
					
				if ( empty( $nCode ) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Некоректна стойност на полето код!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}

				if( empty( $aParams['nSumSalary'] ) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето sum!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}

				if( empty( $aParams['nCountSalary'] ) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето count!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}
				
				$oSalaryType 	= new DBSalaryEarning();
				$oSalary		= new DBSalary();
				
				$aCode = $oSalaryType->getCodeById( $nCode );
					
				if ( empty($aCode) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Некоректна стойност на полето код!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}				
				
				$aSalary = array();
				$aSalary['id'] 				= 0;
				$aSalary['code'] 			= $aCode;
				$aSalary['id_person']		= $aParams['id'];
				$aSalary['id_office']		= $aParams['nIDOfficeSalary'];
				$aSalary['id_object']		= $aParams['id_object2'];
				$aSalary['description']		= $aParams['sDescription'];
				$aSalary['sum'] 			= $aParams['nSumSalary'];
				$aSalary['auto'] 			= 0;
				$aSalary['count'] 			= $aParams['nCountSalary'];
				$aSalary['total_sum'] 		= $aParams['nTotalSumSalary'];
				$aSalary['month'] 			= $aParams['year'].$aParams['month'];
				$aSalary['is_earning'] 		= 1;
				
				$oSalary->update($aSalary);
				
				return DBAPI_ERR_SUCCESS;
			}
			
			function loadData( $nID, &$aData ) {
				global $oResponse, $db_name_sod;
				
				$id = (int) $nID;
				
				if ( $id > 0 ) {
					$aData = array();
					$aWhere = array();
					
					$aWhere[] = sprintf(" p.id = '%d' ", $id );
					$aWhere[] = sprintf(" p.to_arc = 0 " );

					$sQuery = sprintf(" 
						SELECT 
							p.id,
							p.id as _id, 
							p.code,
							p.id as id_person,
							p.id_history,
							p.id_office,
							o.name as region,
							p.id_region_object AS id_object,
							obj.name as object,
							o.id_firm AS id_firm,
							f.name AS firm,
							p.id_position,
							p.id_position_nc,
							pp.name AS position,
							pnc.name AS position_nc,
							UNIX_TIMESTAMP(p.date_from) AS date_in,
							UNIX_TIMESTAMP(p.vacate_date) AS date_out,
							IF ( p.vacate_date >= '1980-01-01', DATE_FORMAT(p.vacate_date, '%%d.%%m.%%Y'), '') AS date_outa,
							IF ( p.length_service > 0, p.length_service, '') AS length_service,
							p.status AS status
						FROM 
							personnel p
						LEFT JOIN personnel as up on up.id = p.updated_user
						LEFT JOIN {$db_name_sod}.offices as o on o.id = p.id_office
						LEFT JOIN {$db_name_sod}.firms as f on f.id = o.id_firm
						LEFT JOIN {$db_name_sod}.objects as obj on obj.id = p.id_region_object
						LEFT JOIN {$db_name_sod}.region_objects as r on r.id = p.id_region_object
						LEFT JOIN positions as pp on pp.id = p.id_position
						LEFT JOIN positions_nc as pnc ON pnc.id = p.id_position_nc
					", 
					$this->_oBase->_sTableName
					);
					
					return $this->_oBase->getResult( $aData, $sQuery, $aWhere );
				}
			}


			function loadHistory( $aParams ) {
				global $oResponse, $db_name_sod;
				
				$nID 		= $aParams['id'];
				//$history 	= $aParams['history'];
				//$code 	= $aParams['code'];

				if ( !is_numeric($nID) ) {
					return DBAPI_ERR_INVALID_PARAM;
				}

//				if ( !is_numeric($history) ) {
//					return DBAPI_ERR_INVALID_PARAM;
//				}
				
				
				if ( $nID > 0 ) {
					$aData = array();
					$aWhere = array();
					//$aWhere[] = sprintf(" p.code = '%s' ", $code );
					$aWhere[] = sprintf(" p.id_history = '%s' AND p.id_history > 0 ", $nID );
					$aWhere[] = sprintf(" p.status = 'moved' " );
					$aWhere[] = sprintf(" p.to_arc = 0 " );

					$sQuery = sprintf(" 
						SELECT 
							p.id,
							p.id as _id, 
							p.id as id_person,
							p.id_history,
							p.id_office,
							p.code AS code,
							o.name as region,
							p.id_region_object AS id_object,
							r.name as object,
							o.id_firm AS id_firm,
							f.name AS firm,
							p.id_position,
							p.id_position_nc,
							pp.name AS position,
							pnc.name AS position_nc,
							IF ( TIMESTAMPDIFF(MONTH, p.date_from, p.vacate_date) > 0, TIMESTAMPDIFF(MONTH, p.date_from, p.vacate_date), 0) AS days,
							IF ( p.date_from >= '1980-01-01', DATE_FORMAT(p.date_from, '%%d.%%m.%%Y'), '') AS date_in,
							IF ( p.vacate_date >= '1980-01-01', DATE_FORMAT(p.vacate_date, '%%d.%%m.%%Y'), '') AS date_out,
							p.status AS status
						FROM 
							personnel p
						LEFT JOIN personnel as up on up.id = p.updated_user
						LEFT JOIN {$db_name_sod}.offices as o on o.id = p.id_office
						LEFT JOIN {$db_name_sod}.firms as f on f.id = o.id_firm
						LEFT JOIN {$db_name_sod}.objects as r on r.id = p.id_region_object
						LEFT JOIN positions as pp on pp.id = p.id_position
						LEFT JOIN positions_nc as pnc ON pnc.id = p.id_position_nc
					", 
					$this->_oBase->_sTableName
					);
					//APILog::Log(0, $sQuery);
					return $this->_oBase->getReport( $aParams, $sQuery, $aWhere );
				}
			}
		}

	$oHandler = new MyHandler( $oPersonData, 'id', 'person_data', 'Служебни данни' );
	$oHandler->Handler( $aParams );	
?>
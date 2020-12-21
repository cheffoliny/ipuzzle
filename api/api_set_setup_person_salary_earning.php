<?php
	$oSalary = New DBBase( $db_personnel, 'salary' );

	class MyHandler 
		extends APIHandler {
			
			function setFields($aParams) {
				global $oResponse;
								
			}			
			
			function Handler( $aParams ) {
				global $oResponse;
				$aData = array();
				
				switch( $aParams['api_action']) {
					case "save" :
						$this->SaveSalary( $aParams );
					break;
					default :	
						$oFirms		= new DBFirms();
						$oOffices	= new DBOffices();
						
						$aPerson = array();
						$aSalaryCode = array();
					
						$nID 		= isset($aParams['id']) 		&& is_numeric($aParams['id'])			? $aParams['id']		: 0;
						$nIDPerson 	= isset($aParams['id_person']) 	&& is_numeric($aParams['id_person'])	? $aParams['id_person']	: 0;
						
						$firm	 	= isset($aParams['firm']) 		&& is_numeric($aParams['firm'])			? $aParams['firm']		: 0;
						$office 	= isset($aParams['office']) 	&& is_numeric($aParams['office'])		? $aParams['office']	: 0;
						$codeto 	= isset($aParams['codeto']) 	&& !empty($aParams['codeto'])			? "+".trim($aParams['codeto']) : "";
						
						if ( $nResult = $this->loadSalary( $nID, $aData ) != DBAPI_ERR_SUCCESS ) {
							$oResponse->setError( $nResult, "Проблем при извличане на данните!", __FILE__, __LINE__ );
							print( $oResponse->toXML() );
							return $nResult;
						}
						
						$aData = current($aData);
						//APILog::Log(0, $aData);
						if ( empty($nID) ) {
							$aPerson = $oOffices->getInfoByID( $nIDPerson );
							
							if ( !isset($aPerson['id_firm']) ) $aPerson['id_firm'] = 0;
							if ( !isset($aPerson['id_office']) ) $aPerson['id_office'] = 0;
							
							$aData['id_object'] 	= isset($aPerson['id_region_object']) ? $aPerson['id_region_object'] : 0;
							$aData['object'] 		= isset($aPerson['obj_name']) ? $aPerson['obj_name'] : '';
							$aData['description'] 	= $codeto == "+ДОМ" ? "Домашен отпуск" : $codeto;
							$aData['sum'] 			= "";
							$aData['count'] 		= 1;
							$aData['total_sum'] 	= "";
							$aData['auto'] 			= 0;
							$aData['code'] 			= $codeto;
							
							if ( empty($firm) ) {
								$nIDFirm	= isset($aParams['nIDFirm']) && !empty($aParams['nIDFirm']) ? (int) $aParams['nIDFirm'] : $aPerson['id_firm'];
							} else {
								$nIDFirm 	= $firm;
							}
							
							if ( empty($office) ) {
								$nIDOffice	= isset($aParams['nIDOffice']) && !empty($aParams['nIDOffice']) ? (int) $aParams['nIDOffice'] : $aPerson['id_office'];		
							} else {
								$nIDOffice 	= $office;
							}				
							
						} else {
							if ( !isset($aData['nIDFirm']) ) $aData['nIDFirm'] = 0;
							if ( !isset($aData['nIDOffice']) ) $aData['nIDOffice'] = 0;
							
							$nIDFirm	= isset($aParams['nIDFirm']) && !empty($aParams['nIDFirm']) ? (int) $aParams['nIDFirm'] : $aData['nIDFirm'];
							$nIDOffice	= isset($aParams['nIDOffice']) && !empty($aParams['nIDOffice']) ? (int) $aParams['nIDOffice'] : $aData['nIDOffice'];						
						}	
												
						$aFirms		= array();
						$aOffices	= array();

						$sAct		= $aParams['sAct'];
						
						//Check Sums
						$aData['sum'] = ( float ) $aData['sum'];
						$aData['total_sum'] = ( float ) $aData['total_sum'];
						
						if( empty( $aData['sum'] ) && $aData['total_sum'] != 0 && $aData['count'] > 0 )
						{
							$aData['sum'] = round( ( $aData['total_sum'] / $aData['count'] ), 2 );
						}
						elseif( empty( $aData['total_sum'] ) && $aData['sum'] != 0 && $aData['count'] > 0 )
						{
							$aData['total_sum'] = $aData['sum'] * $aData['count'];
						}
						//End Check Sums
						
						$oResponse->setFormElement('form1', 'nIDFirm',			array(), '');
						$oResponse->setFormElement('form1', 'nIDOffice',		array(), '');
						$oResponse->setFormElement('form1', 'code',				array(), '');
						$oResponse->setFormElement('form1', 'region_object',	array(), $aData['object']);
						$oResponse->setFormElement('form1', 'id_object',		array(), $aData['id_object']);
						$oResponse->setFormElement('form1', 'description',		array(), $aData['description']);
						$oResponse->setFormElement('form1', 'sum',				array(), $aData['sum']);
						$oResponse->setFormElement('form1', 'count',			array(), $aData['count']);
						$oResponse->setFormElement('form1', 'sum_total',		array(), $aData['total_sum']);
						$oResponse->setFormElement('form1', 'auto',				array(), '');
						
						if ( $aData['auto'] == 1 ) {
							$oResponse->setFormElement('form1', 'auto',		array("checked" => "checked") );
						}

						$oResponse->setFormElementChild('form1', 'nIDFirm',		array('value' => 0), 'Изберете');
						$oResponse->setFormElementChild('form1', 'nIDOffice',	array('value' => 0), 'Изберете');
						$oResponse->setFormElementChild('form1', 'code',		array('value' => 0), 'Изберете');
						
						$aFirms = $oFirms->getFirms();

						if ( $aParams['is_earning'] == 1 ) {
							$oSalaryCode = new DBSalaryEarning();
							$aSalaryCode = $oSalaryCode->getEarningCode();
						} else {
							$oSalaryCode = new DBSalaryExpense();
							$aSalaryCode = $oSalaryCode->getExpenseCode();
						}
						
						
						foreach ( $aSalaryCode as $key => $val ) {
							$tmpArr = array();
							$tmpArr = explode('$$$', $val);
							$code = isset($tmpArr[0]) ? $tmpArr[0] : "";
							$name = isset($tmpArr[1]) ? $tmpArr[1] : "";
							$dCode = isset($aParams['code']) ? $aParams['code'] : "";
							
							if ( $aData['code'] == $code || $key == $dCode ) {
								$oResponse->setFormElementChild('form1', 'code', array('value' => $key, 'id' => $name, 'selected' => 'selected'), $code);
							} else $oResponse->setFormElementChild('form1', 'code', array('value' => $key, 'id' => $name), $code);
						}	
						
						//$oResponse->setFormElement('form1', 'code',	array(), $aData['code']);
						//var_dump($aData);
						
						foreach ( $aFirms as $key => $val ) {
							if ( $nIDFirm == $key ) {
								$oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => $key, 'selected' => 'selected'), $val);
							} else $oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => $key), $val);
						}	
						
						unset($key); unset($val);
						
						if ( $nIDFirm > 0 ) {
							$aOffices = $oOffices->getFirmOfficesAssoc( $nIDFirm );
							foreach ( $aOffices as $key => $val ) {
								if ( $nIDOffice == $key ) {
									$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => $key, 'selected' => 'selected'), $val['name']);
								} else $oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => $key), $val['name']);
							}	
						}			
									
						print( $oResponse->toXML() );
					break;
				}
			}
			
			
			function SaveSalary( $aParams ) {
				global $oSalary, $oResponse;
				
				//var_dump($aParams);
				
				$nCode = !empty( $aParams['code'] ) ? $aParams['code'] : "";
				$aCode = "";
				
					
				if ( empty( $nCode ) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Некоректна стойност на полето код!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}

				if( empty( $aParams['sum'] ) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето sum!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}

				if( empty( $aParams['count'] ) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето count!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}
				
				if ( $aParams['is_earning'] == 1 ) {
					$oSalaryType = new DBSalaryEarning();
					$aCode = $oSalaryType->getCodeById( $nCode );
					
					if ( empty($aCode) ) {
						$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Некоректна стойност на полето код!", __FILE__, __LINE__ );
						print( $oResponse->toXML() );
						return DBAPI_ERR_INVALID_PARAM;
					}
				} else {
					$oSalaryType = new DBSalaryExpense();
					$aCode = $oSalaryType->getCodeById( $nCode );
					
					if ( empty($aCode) ) {
						$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Некоректна стойност на полето код!", __FILE__, __LINE__ );
						print( $oResponse->toXML() );
						return DBAPI_ERR_INVALID_PARAM;
					}
				}					
				
				$aSalary = array();
				$aSalary['id'] 				= $aParams['id'];
				$aSalary['code'] 			= $aCode;
				$aSalary['id_person']		= $aParams['id_person'];
				$aSalary['id_office']		= $aParams['nIDOffice'];
				$aSalary['id_object']		= $aParams['id_object'];
				$aSalary['description']		= $aParams['description'];
				$aSalary['sum'] 			= $aParams['sum'];
				$aSalary['auto'] 			= $aParams['auto'];
				$aSalary['count'] 			= $aParams['count'];
				$aSalary['total_sum'] 		= $aParams['sum_total'];
				$aSalary['month'] 			= $aParams['month'];
				$aSalary['is_earning'] 		= $aParams['is_earning'];
				
				if( $nResult = $oSalary->update( $aSalary ) != DBAPI_ERR_SUCCESS ) {
					$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return $nResult;
				}
				
				return DBAPI_ERR_SUCCESS;
			}
			
			function loadSalary( $nID, &$aData ) {
				global $oResponse, $db_name_sod;
				
				$id = (int) $nID;
				
				if ( $id > 0 ) {
					$aData = array();
					$aWhere = array();
					
					$aWhere[] = sprintf(" t.id = '%d' ", $id );

					$sQuery = sprintf(" 
						SELECT 
							t.id, 
							t.code as code, 
							t.month,
							t.auto,
							r.id as nIDOffice,
							r.name as region,
							f.id as nIDFirm,
							f.name as firm,
							o.id as id_object,
							o.name as object,
							is_earning as type,
							t.sum,
							t.count,
							t.total_sum,
							t.description as description
						FROM 
							%s t 
							LEFT JOIN {$db_name_sod}.offices r ON r.id = t.id_office
							LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
							LEFT JOIN {$db_name_sod}.objects o ON o.id = t.id_object
							LEFT JOIN personnel as up on up.id = t. updated_user
						", 
						$this->_oBase->_sTableName
					);
				
					return $this->_oBase->getResult( $aData, $sQuery, $aWhere );
				}
				
				return DBAPI_ERR_SUCCESS;
			}

			
		}

	$oHandler = new MyHandler( $oSalary, 'id', 'salary', 'Заплати' );
	$oHandler->Handler( $aParams );	
?>

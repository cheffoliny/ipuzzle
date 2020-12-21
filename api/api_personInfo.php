<?php
	$oPerson = New DBBase( $db_personnel, 'personnel' );
	$oValidate = new Validate();

	switch( $aParams['api_action']) {
		case "save" :
			SavePerson( $aParams );
		break;
		default :
			//APILog::Log(0, $_SESSION);
			loadPerson( $aParams['id'] );
		break;
	}
	
	function SavePerson( $aParams ) {
		global $oPerson, $oValidate, $oResponse;
		
		$nCode = !empty($aParams['EIC']) || !is_numeric($aParams['EIC']) ? $aParams['EIC'] : 0;
		 
		if ( !is_numeric($nCode) ) {
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Некоректна стойност на полето код!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		//APILog::log(0, $aParams);
		$aDuplicate = array();
		$aWhere = array();
		$aWhere[] = " id != {$aParams['id']} ";
		$aWhere[] = " code = '{$nCode}' ";
		$aWhere[] = " to_arc = 0 ";
		//$aWhere[] = " status = 'active' ";
				
		if( $nResult = $oPerson->getResult( $aDuplicate, NULL, $aWhere ) != DBAPI_ERR_SUCCESS ) {
			$oResponse->setError( DBAPI_ERR_SQL_QUERY, "Проблем при комуникацията!", __FILE__, __LINE__ );
			return DBAPI_ERR_SQL_QUERY;
		}
		
//		if( !empty( $aDuplicate ) ) {
//			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Вече съществува запис с този код!", __FILE__, __LINE__ );
//			return DBAPI_ERR_INVALID_PARAM;
//		}		

		if( empty( $aParams['fname'] ) ) {
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето име!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}

		if ( !empty($aParams['email']) ) {
			$oValidate->variable = $aParams['email'];
			$oValidate->checkEMAIL();
			if ( !$oValidate->result ) {
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "E-mail адресът не е коректен!\n", __FILE__, __LINE__ );
				$oResponse->setFormElement('form1', 'email', array('focused'=>'true', 'selected'=>'true'));
				return DBAPI_ERR_INVALID_PARAM;
			}	
		}
		
		if ( !empty($aParams['iban']) ) {
			$oValidate->variable = $aParams['iban'];
			$oValidate->checkIBAN();
			if ( !$oValidate->result ) {
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "IBAN сметката не е коректна!\n", __FILE__, __LINE__ );
				$oResponse->setFormElement('form1', 'iban', array('focused'=>'true', 'selected'=>'true'));
				return DBAPI_ERR_INVALID_PARAM;
			}	
		}

		if ( !empty($aParams['lkn']) ) {
			$oValidate->variable = $aParams['lkn'];
			$oValidate->checkIDCARD();
			if ( !$oValidate->result ) {
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Номера на личната карта не е коректен!\n", __FILE__, __LINE__ );
				$oResponse->setFormElement('form1', 'lkn', array('focused'=>'true', 'selected'=>'true'));
				return DBAPI_ERR_INVALID_PARAM;
			}	
		}

		if ( !empty($aParams['egn']) ) {
			$oValidate->variable = $aParams['egn'];
			$oValidate->checkEGN();
			if ( !$oValidate->result ) {
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Номера на ЕГН не е коректен!\n", __FILE__, __LINE__ );
				$oResponse->setFormElement('form1', 'egn', array('focused'=>'true', 'selected'=>'true'));
				return DBAPI_ERR_INVALID_PARAM;
			}	
		}

		if ( !empty($aParams['mobile_phone']) ) {
			$oValidate->variable = $aParams['mobile_phone'];
			$oValidate->checkGSM();
			if ( !$oValidate->result ) {
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "GSM номера не е коректен!\n", __FILE__, __LINE__ );
				$oResponse->setFormElement('form1', 'mobile_phone', array('focused'=>'true', 'selected'=>'true'));
				return DBAPI_ERR_INVALID_PARAM;
			}	
		}

		if ( !empty($aParams['mphones']) ) {
			$phones = array();
			$phones = explode(",", $aParams['mphones']);
			foreach ( $phones as $val ) {
				$val = trim($val);
				//APILog::log(0, $val);
				$oValidate->variable = $val;
				$oValidate->checkGSM();
				if ( !$oValidate->result ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Има некоректни GSM номера!\n", __FILE__, __LINE__ );
					$oResponse->setFormElement('form1', 'mphones', array('focused'=>'true', 'selected'=>'true'));
					return DBAPI_ERR_INVALID_PARAM;
				}	
			}
			//APILog::log(0, $phones);
		}

		$aPerson = array();
		$aPerson['id'] 				= $aParams['id'];
		$aPerson['code'] 			= $nCode;
		$aPerson['fname'] 			= $aParams['fname'];
		$aPerson['mname'] 			= $aParams['mname'];
		$aPerson['lname'] 			= $aParams['lname'];
		$aPerson['addr_city'] 		= $aParams['addr_city'];
		$aPerson['address'] 		= $aParams['addr_street'];
		$aPerson['addr_num'] 		= $aParams['addr_num'];
		$aPerson['addr_floor'] 		= $aParams['addr_floor'];
		$aPerson['addr_app'] 		= $aParams['addr_app'];
		$aPerson['family_status'] 	= $aParams['family_status'];
		$aPerson['lk_num'] 			= $aParams['lkn'];
		$aPerson['sk_num'] 			= $aParams['skn'];
		$aPerson['lk_date'] 		= jsDateToTimestamp( $aParams['lk_date'] );
		$aPerson['lk_izdatel'] 		= $aParams['lk_pub'];
		$aPerson['egn'] 			= $aParams['egn'];
		$aPerson['iban'] 			= $aParams['iban'];
		$aPerson['phone']	 		= $aParams['home_phone'];
		$aPerson['business_phone'] 	= $aParams['business_phone'];
		$aPerson['mobile'] 			= $aParams['mobile_phone'];
		$aPerson['mphones'] 		= $aParams['mphones'];
		$aPerson['email'] 			= $aParams['email'];
		$aPerson['note'] 			= $aParams['note'];
		
		if( $nResult = $oPerson->update( $aPerson ) != DBAPI_ERR_SUCCESS ) {
			$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
			return $nResult;
		}
		
		if ( empty($aParams['id']) && !empty($aPerson['id']) ) {
			$nID 					= $aPerson['id'];
			
			$aPerson 				= array();
			$aPerson['id'] 			= $nID;
			$aPerson['id_history'] 	= $nID;
			
			
			if( $nResult = $oPerson->update( $aPerson ) != DBAPI_ERR_SUCCESS ) {
				$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
				return $nResult;
			}			
			
			$oResponse->setFormElement('form1', 'id', array(), $nID);
		}

		$oResponse->setAlert("Данните бяха запазени успешно!");
		return DBAPI_ERR_SUCCESS;
	}
	
	function loadPerson( $nID ) {
		global $oPerson, $oResponse;
		
		$id = (int) $nID;
		
		if ( $id > 0 ) {
			$aData = array();
			
			if( $nResult = $oPerson->getRecord( $id, $oData ) != DBAPI_ERR_SUCCESS ) {
				$oResponse->setError( $nResult, "Проблем при комуникацията!", __FILE__, __LINE__ );
				return $nResult;
			}
			
			if ( isset($oData['lk_date']) && !empty($oData['lk_date']) ) {
				$ye = explode("-", $oData['lk_date']);
				if ( $ye[0] < '1980' ) {
					$lk_date = "";
				} else $lk_date = mysqlDateToJsDate ( $oData['lk_date'] );
			} else $lk_date = "";
			//APILog::log(0, $ye);
			
			$oResponse->setFormElement('form1', 'id', array(), $oData['id']);
			$oResponse->setFormElement('form1', 'EIC', array(), $oData['code']);
			$oResponse->setFormElement('form1', 'fname', array(), $oData['fname']);
			$oResponse->setFormElement('form1', 'mname', array(), $oData['mname']);
			$oResponse->setFormElement('form1', 'lname', array(), $oData['lname']);

			$oResponse->setFormElement('form1', 'family_status', array(), $oData['family_status']);
			$oResponse->setFormElement('form1', 'addr_city', array(), $oData['addr_city']);
			$oResponse->setFormElement('form1', 'addr_street', array(), $oData['address']);
			$oResponse->setFormElement('form1', 'addr_num', array(), $oData['addr_num']);
			$oResponse->setFormElement('form1', 'addr_floor', array(), $oData['addr_floor']);
			$oResponse->setFormElement('form1', 'addr_app', array(), $oData['addr_app']);
			$oResponse->setFormElement('form1', 'egn', array(), $oData['egn']);
			$oResponse->setFormElement('form1', 'lkn', array(), $oData['lk_num']);
			$oResponse->setFormElement('form1', 'skn', array(), $oData['sk_num']);
			$oResponse->setFormElement('form1', 'lk_date', array(), $lk_date);
			$oResponse->setFormElement('form1', 'lk_pub', array(), $oData['lk_izdatel']);
			$oResponse->setFormElement('form1', 'iban', array(), $oData['iban']);
			$oResponse->setFormElement('form1', 'home_phone', array(), $oData['phone']);
			$oResponse->setFormElement('form1', 'business_phone', array(), $oData['business_phone']);
			$oResponse->setFormElement('form1', 'mobile_phone', array(), $oData['mobile']);
			$oResponse->setFormElement('form1', 'mphones', array(), $oData['mphones']);
			$oResponse->setFormElement('form1', 'email', array(), $oData['email']);
			$oResponse->setFormElement('form1', 'note', array(), $oData['note']);
			//debug($oData);
		}
			
		return DBAPI_ERR_SUCCESS;
	}

	print( $oResponse->toXML() );	
?>

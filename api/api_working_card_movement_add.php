<?php
	class ApiWorkingCardMovementAdd {
		
		public function load( DBResponse $oResponse ) {
			global $db_sod, $db_name_sod, $db_name_personnel, $db_name_auto;
			
			$aData			= array();
			$aFirms			= array();
			$aOffices		= array();
			
			$aWorkCard 		= array();
			$aSignals		= array();
			
			$nID			= Params::get("nID", 0);
			$nIDFirm		= Params::get("nIDFirm", 0);
			$nIDOffice		= Params::get("nIDOffice", 0);
			//$sAct			= Params::get("sAct", 'load');
			$nIDCard		= Params::get("nIDCard", 0);
			$nIDPatrul		= Params::get("sPatrul", 0);
			$nIDSignal		= Params::get("sSignal", 0);
			
			$oFirms			= new DBFirms();
			$oOffices		= new DBOffices();
			$oSignals		= new DBSignals();
			
			// БЛАХ!!!
			$oWorkCard 		= new DBWorkCard();
			$aWorkCard		= $oWorkCard->getWorkCardInfo( $nIDCard );
			$aFirms 		= $oFirms->getFirms();
			
			$nWorkCardStartTime = $aWorkCard['sttime'];
			$nWorkCardEndTime 	= $aWorkCard['locked'];

			$sQuery = "
				SELECT
					rl.id, 
					pat.num_patrul AS code,
					pat.id as id_patrul,
					CONCAT('[',fm.name,'] ',o.name) AS office,
					CONCAT(m.model, ' [', a.reg_num, ']') AS auto,
					GROUP_CONCAT(CONCAT_WS(' ', p.fname, p.lname) SEPARATOR ', ' ) AS patruls,
					rl.start_km
				FROM {$db_name_auto}.road_lists rl 
				LEFT JOIN {$db_name_personnel}.personnel p ON ( FIND_IN_SET(p.id, rl.persons) AND p.id IN (rl.persons) )
				LEFT JOIN {$db_name_sod}.offices o ON o.id = rl.id_office
				LEFT JOIN {$db_name_sod}.firms fm ON fm.id = o.id_firm
				LEFT JOIN {$db_name_auto}.auto a ON a.id = rl.id_auto
				LEFT JOIN {$db_name_auto}.auto_models m ON a.id_model = m.id
				LEFT JOIN {$db_name_auto}.functions f ON f.id = rl.id_function
				LEFT JOIN {$db_name_sod}.patruls pat ON pat.id = rl.id_patrul
				WHERE 1
					AND f.function_type = 'patrul'
			";
			
			if ( empty($nWorkCardEndTime) ) {
				$sQuery .= " AND UNIX_TIMESTAMP(rl.end_time) = 0\n";
			} else {
				$sQuery .= " 
					AND (	
							( (UNIX_TIMESTAMP(rl.start_time) > {$nWorkCardStartTime}) 	AND (UNIX_TIMESTAMP(rl.start_time) < {$nWorkCardEndTime}) )	
						OR
							( (UNIX_TIMESTAMP(rl.end_time) > {$nWorkCardStartTime}) 	AND (UNIX_TIMESTAMP(rl.end_time) < {$nWorkCardEndTime}) )
						OR
							( (UNIX_TIMESTAMP(rl.start_time) < {$nWorkCardStartTime}) 	AND (UNIX_TIMESTAMP(rl.end_time) > {$nWorkCardEndTime}) )
					 )	
				";
			}
			
			$sQuery .= " GROUP BY rl.id	\n";
			
			$aData = $db_sod->getArray($sQuery);

			//APILog::Log(0, $aData);
					
			$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			$oResponse->setFormElement('form1', 'sPatrul', array(), '');
			$oResponse->setFormElement('form1', 'sSignal', array(), '');
			
			$oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => 0), 'Избери');
			$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => 0), 'Избери');
			$oResponse->setFormElementChild('form1', 'sPatrul', array('value' => 0), 'Избери');
			$oResponse->setFormElementChild('form1', 'sSignal', array('value' => 0), 'Избери');
			
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
			
			unset($key); unset($val);
			
			foreach ( $aData as $val ) {
				if ( $nIDPatrul == $val['id_patrul'] ) {
					$oResponse->setFormElementChild('form1', 'sPatrul', array('value' => $val['id_patrul'], 'selected' => 'selected'), $val['code']." [".$val['patruls']."]");
				} else $oResponse->setFormElementChild('form1', 'sPatrul', array('value' => $val['id_patrul']), $val['code']." [".$val['patruls']."]");
			}			
			
			unset($key); unset($val);
			
			$aSignals = $oSignals->getAlarmSignals();
			//APILog::Log(0, $aSignals);
			
			foreach ( $aSignals as $val ) {
				if ( $nIDSignal == $val['id'] ) {
					$oResponse->setFormElementChild('form1', 'sSignal', array('value' => $val['id'], 'selected' => 'selected'), $val['msg_al']);
				} else $oResponse->setFormElementChild('form1', 'sSignal', array('value' => $val['id']), $val['msg_al']);
			}			
			
			$date = date("d.m.Y");
			$time = date("H:i");
			
			$oResponse->setFormElement('form1', 'sAlarmH', array(), $time);
			$oResponse->setFormElement('form1', 'sAlarmD', array(), $date);
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse ) {
			$nObject	= Params::get( "nObject", 0 );
			$nIDFirm	= Params::get( "nIDFirm", 0 );

			$nIDOffice	= Params::get("nIDOffice", 0);
			$nIDCard	= Params::get("nIDCard", 0);
			$nIDPatrul	= Params::get("sPatrul", 0);
			$nIDSignal	= Params::get("sSignal", 0);			

			$sAlarmD	= Params::get( "sAlarmD", "" );
			$sAlarmH 	= Params::get( "sAlarmH", "" );
			
			$oWorkCard	= new DBWorkCardMovement();
			$oObject	= new DBObjects();
			$aMatches 	= array();
			$aObject	= array();
			
			$sStart		= "0000-00-00";
			
			if ( !empty($sAlarmD) && !empty($sAlarmH) )	{
				if ( !preg_match("/^(\d{2})\.(\d{2})\.(\d{4})$/", $sAlarmD, $aMatches ) ) {
					throw new Exception("Невалидна дата!", DBAPI_ERR_INVALID_PARAM);
				} 
				
				if ( !preg_match("/^(\d{2})\:(\d{2})$/", $sAlarmH, $aMatches ) ) {
					throw new Exception("Невалиден час!", DBAPI_ERR_INVALID_PARAM);		
				}		
				
				$sTmp 	= jsDateToTimestamp( $sAlarmD );
				$sStart = date("Y-m-d", $sTmp)." ".$sAlarmH.":00";
			
			} else {
				throw new Exception("Моля въведете дата/час на алармата!", DBAPI_ERR_INVALID_PARAM);
			} 
			
			if ( empty($nObject) ) {
				throw new Exception("Въведете обект!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($nIDOffice) ) {
				throw new Exception("Изберете регион!", DBAPI_ERR_INVALID_PARAM);
			}	
			
			if ( empty($nIDPatrul) ) {
				throw new Exception("Изберете позивна!", DBAPI_ERR_INVALID_PARAM);
			}	
			
			if ( empty($nIDSignal) ) {
				throw new Exception("Изберете сигнал!", DBAPI_ERR_INVALID_PARAM);
			}				
			
			$aObject = $oObject->getByID($nObject);
			
			if ( isset($aObject['name']) && isset($aObject['num']) ) {
				$sObject = $aObject['num']." - ".$aObject['name'];
			} else {
				throw new Exception("Въведете обект!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$aData 						= array();
			$aData['id']				= 0;
			$aData['id_patrul'] 		= $nIDPatrul;
			$aData['id_office'] 		= $nIDOffice;
			$aData['type']				= "object";
			$aData['id_object'] 		= $nObject;
			$aData['obj_name']			= $sObject;
			$aData['id_parking'] 		= 0;
			$aData['alarm_type'] 		= $nIDSignal;
			$aData['alarm_time'] 		= $sStart;
			$aData['id_alarm_reason'] 	= 0;
			$aData['start_time'] 		= $sStart;
			$aData['end_time'] 			= "0000-00-00 00:00:00";
			$aData['reason_time'] 		= "0000-00-00 00:00:00";
			$aData['note'] 				= "";
			
			$oWorkCard->update( $aData );
			
			$oResponse->printResponse();
		}
		
		public function getunprocessed( DBResponse $oResponse ) {
			$oObjects 	= new DBObjects();
			$oOffices 	= new DBOffices();
			
			$nIDObject 	= Params::get( "nObject", 0 );

			$aObject 	= $oObjects->getRecord( $nIDObject );
			
			if ( !empty($aObject) ) {
				$aOffice = $oOffices->getRecord( $aObject['id_tech_office'] );

				if ( !empty($aOffice) ) {
					$oResponse->setFormElement( "form1", "nTempIDOffice", array( "value" => $aOffice['id'] ) );
					$oResponse->setFormElement( "form1", "nTempIDFirm", array( "value" => $aOffice['id_firm'] ) );
				}
			}
			
			$oResponse->printResponse();
		}
		
	}
?>
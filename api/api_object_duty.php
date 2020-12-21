<?php
	class ApiObjectDuty {
		
		public function result( DBResponse $oResponse ) {
			global $db_sod;
			$db_sod->debug=true;
			$Time = array();
			
			$nID = Params::get("nID", 0);
			$sAct = Params::get("sAct", 'cur');
			$sIDShift = Params::get("sIDShift", 0);
			$nStep = Params::get("nStep", 0);
			//$sShift = empty( $sShift ) ? time() : jsDateToTimestamp( $sShift );
			//$nTime = Params::get("nTime", 0);
			
			$param = array();
			$param['nID'] = $nID;
			$param['sAct'] = $sAct;
			$param['sIDShift'] = $sIDShift;
			$param['nStep'] = $nStep;
			
			$oDuty = new DBObjectDuty();
			$Time = $oDuty->getShift( $param );
			$nTime = isset($Time['nTime']) ? (int) $Time['nTime'] : time();
			
			$param['nTime'] = $nTime;
			
			if ( $nStep == 1 ) {
				$pTime = $oDuty->getPrevShiftOnce( $param );
				$nTime = isset($pTime['pTime']) ? (int) $pTime['pTime'] : time();
				$param['nTime'] = $nTime;
			}

			if( !empty( $nID ) ) {
				//debug(date('d.m.Y H:i', $Time['nTime'])); 
				
				$oResponse->setFormElement( 'form1', 'sDuty', array(), date('d.m.Y H:i', $nTime) );
				$oResponse->setFormElement( 'form1', 'sShift', array(), date("d.m.Y", $nTime) );
				$oResponse->setFormElement( 'form1', 'sShiftT', array(), date("H:i", $nTime) );
				$oResponse->setFormElement( 'form1', 'nTime', array(), $nTime );
				
				$aDuty = $oDuty->getReport( $param, $oResponse );
								
			}
			
			$oResponse->printResponse("График за обект", "object_duty");
		}
		
		public function duty( DBResponse $oResponse ) {
			global $db_sod;

			$nIDObj = Params::get('nID');
			$sShift = Params::get("sShift", '');
			$sShiftT = Params::get("sShiftT", '');
			
			$chk = Params::get('chk', array());
			$chk2 = Params::get('chk2', array());
			$note = Params::get('note', array());
			
			$aData = array();
			$aData['nIDObj'] = $nIDObj;			
			
			foreach ( $chk as $key => $val ) {
				if ( ($key > 0) && ($val > 0) ) {
					!isset($aData['chk']) ? $aData['chk'] = $key : $aData['chk'] .= ",".$key;
				}
			}

			foreach ( $chk2 as $key => $val ) {
				if ( ($key > 0) && ($val > 0) ) {
					!isset($aData['chk2']) ? $aData['chk2'] = $key : $aData['chk2'] .= ",".$key;
				}
			}

//			foreach ( $note as $key => $val ) {
//				if ( ($key > 0) && ($val > 0) ) {
//					!isset($aData['note']) ? $aData['note'] = $key : $aData['note'] .= ",".$key;
//				}
//			}
			
			if ( empty( $sShift ) || empty( $sShiftT ) ) {
				throw new Exception("Зададеното време за валидиране не е коректно!", DBAPI_ERR_INVALID_PARAM);
				$oResponse->printResponse();
			} else {
				@list($d, $m, $y) = explode(".", $sShift);
				@list($h, $i) = explode(":", $sShiftT);
			
				if( @checkdate($m, $d, $y) ) {
					$aData['nTime'] = mktime($h, $i, 0, $m, $d, $y);
					$oDuty = new DBObjectDuty();
					$oDuty->validate( $aData );
					
				} else {
					throw new Exception("Зададеното време за валидиране не е коректно!", DBAPI_ERR_INVALID_PARAM);
					$oResponse->printResponse();
				}
			}
			
			$oResponse->printResponse();
		}		

		public function erase( DBResponse $oResponse ) {
			global $db_sod;
			$aParams = Params::getAll();
			$nObj = (int) $aParams['nID'];
			
			//APILog::Log(0, $aParams);
			$sStart = "";
			$sStop = "";
			
			foreach ( $aParams['chk'] as $key => $val ) {
				if ($val == 1) {
					$sStop .= empty($sStop) ? $key : ",".$key;	
				}
			}

			foreach ( $aParams['chk2'] as $key => $val ) {
				if ($val == 1) {
					$sStart = empty($sStart) ? $key : ",".$key;	
				}
			}
			
			$data = array();
			$data['nIDObj'] = $nObj;
			$data['nIDStart'] = $sStart;
			$data['nIDStop'] = $sStop;
			
//			APILog::Log(0, $sStart);
			$oDuty = new DBObjectDuty();
			$oDuty->erase( $data );
			
			$oResponse->printResponse();
		}	
		
		
		public function autoValidate( DBResponse $oResponse ) {
			global $db_sod, $db_name_sod, $db_name_personnel;
			
			$day = date("Y-m-d");
			$user = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
			$nIDs = "";
			
			$db_sod->startTrans();
			
			$sQueryID = "
				SELECT 
					GROUP_CONCAT(od.id) as id 
				FROM object_duty od
				LEFT JOIN object_shifts os on os.id = od.id_shift
				WHERE od.id_shift > 0
					AND os.automatic = 1
					
					AND UNIX_TIMESTAMP(od.endRealShift) = 0	
					AND od.endShift <= '".$day." 23:59:00'				
			";
			//AND UNIX_TIMESTAMP(od.startRealShift) = 0
			$res = $db_sod->Execute( $sQueryID );
			
			if ( !$res->EOF ) {
				$nIDs = $res->fields['id'];
			}
			
			if ( empty($nIDs) ) {
				$nIDs = "-1";
			}
			
			$sQuery = "
				UPDATE object_duty od, object_shifts os
				SET od.startRealShift = od.startShift,
					od.endRealShift = od.endShift,
					od.note = 'Автоматична смяна', 
					od.stake = os.stake,
					od.updated_user = {$user},
					od.updated_time = NOW()
				WHERE od.id_shift = os.id
					AND od.id_shift > 0
					
					AND UNIX_TIMESTAMP(od.endRealShift) = 0	
					AND od.endShift <= '".$day." 23:59:00'				
					AND od.id IN ({$nIDs})
			
			";
			//AND UNIX_TIMESTAMP(od.startRealShift) = 0
			$db_sod->Execute( $sQuery );
			
			$sQuery2 = "
				INSERT INTO {$db_name_personnel}.salary (id_person, id_office, id_object, id_object_duty, month, code, is_earning, sum, description, count, total_sum, created_user, created_time, updated_user, updated_time, to_arc )
				SELECT 
					od.id_person, 
					o.id_office, 
					od.id_obj, 
					od.id, 
					CONCAT( 
						DATE_FORMAT( od.startRealShift, '%Y' ), 
						DATE_FORMAT( od.startRealShift, '%m' ) 
						), 
					se.code, 
					1, 
					IF (od.stake > 0, IF (pc.rate_reward, ((od.stake*pc.rate_reward)/100), od.stake), IF (pc.rate_reward, ((os.stake*pc.rate_reward)/100), os.stake)) AS stake, 
					CONCAT('Автоматична - [', os.code, '] ', DATE_FORMAT( od.startRealShift, '%d.%m.%Y %H:%i' ) ) as name,
					CONCAT(( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) div 3600, '.', (( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) mod 3600) / 60),
					ROUND( IF (od.stake > 0, IF (pc.rate_reward, ((od.stake*pc.rate_reward)/100), od.stake), IF (pc.rate_reward, ((os.stake*pc.rate_reward)/100), os.stake)) * ( ( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) / 3600 ), 2 ),
					{$user},
					NOW(),
					{$user},
					NOW(),
					0
				FROM {$db_name_sod}.object_duty od
				LEFT JOIN {$db_name_sod}.objects o ON od.id_obj = o.id
				LEFT JOIN {$db_name_personnel}.salary_earning_types se ON se.source = 'schedule'
				LEFT JOIN {$db_name_sod}.object_shifts os ON od.id_shift = os.id
				LEFT JOIN {$db_name_personnel}.person_contract pc ON (pc.to_arc = 0 AND pc.id_person = od.id_person AND UNIX_TIMESTAMP((INTERVAL 1 DAY + trial_from)) <= UNIX_TIMESTAMP(od.startRealShift) AND UNIX_TIMESTAMP((INTERVAL 1 DAY + trial_to)) >= UNIX_TIMESTAMP(od.endRealShift) )
				WHERE 1
					AND od.stake > 0
					AND od.startRealShift > 0
					AND od.endRealShift   > 0
					AND od.endShift > od.startShift
					AND od.id IN ({$nIDs})

			";
			//APILog::Log(0, $sQuery2);
			$db_sod->Execute( $sQuery2 );
			
			$db_sod->completeTrans();
			
			$oResponse->printResponse();
		}			
	}
?>
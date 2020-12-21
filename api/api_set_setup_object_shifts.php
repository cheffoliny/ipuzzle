<?php
	class ApiSetSetupObjectShifts {
		public function load( DBResponse $oResponse ) {
			$nID = Params::get("nID", 0);
			$aShiftTypes = array();
			$oShift = new DBObjectShifts();
			
			if( !empty( $nID ) ) {
				$aShift = $oShift->getRecord( $nID );
				$mode = empty($aShift['mode']) ? "none" : $aShift['mode'];
				
				//$oResponse->setFormElement('form1', 'nIDObject', array('value' => $aShift['id_obj']));
				$oResponse->setFormElement('form1', 'sCode', array('value' => $aShift['code']));
				$oResponse->setFormElement('form1', 'sName', array('value' => $aShift['name']));
				$oResponse->setFormElement('form1', 'sStake', array('value' => $aShift['stake']));
				$oResponse->setFormElement('form1', 'sDescription', array('value' => $aShift['description']));
				$oResponse->setFormElement('form1', 'sMode', array('value' => $mode));
				$oResponse->setFormElement('form1', 'sShiftFrom', array('value' => $aShift['shiftFrom']));
				$oResponse->setFormElement('form1', 'sShiftTo', array('value' => $aShift['shiftTo']));
				$oResponse->setFormElement('form1', 'sDuration', array('value' => $aShift['duration']));
				
				$f = explode(":", $aShift['shiftFrom']);
				$t = explode(":", $aShift['shiftTo']);
				$d = explode(":", $aShift['duration']);
				
				$hours = "00:00:00";
				$stake = $aShift['stake'];
				
				if ( isset($f[0]) && isset($f[1]) && isset($t[0]) && isset($t[1]) ) {
					$f_time 	= $f[0] * 3600 + $f[1] * 60;
					$t_time 	= $t[0] * 3600 + $t[1] * 60;
					$d_time 	= $d[0] * 3600 + $d[1] * 60;
					$duration 	= $d_time / 3600;
					
					if ( $t_time < $f_time ) {
						// Смяната преминава на другия ден
						$day = 24 * 60 * 60;
						$time_stamp = ($day - $f_time) + $t_time;
					} elseif ( ($t_time == $f_time) && ($t_time != "00:00:00") ) {
						$time_stamp = 24 * 60 * 60;
					} else {
						$time_stamp = $t_time - $f_time;
					}
					
					$h = floor($time_stamp / 3600);
					$m = floor(($time_stamp - ($h * 3600)) / 60);
					
					if ( strlen($h) == 1 ) {
						$h = "0".$h;
					}
					
					if ( strlen($m) == 1 ) {
						$m = "0".$m;
					}		
					
					$hours = $h.":".$m.":00";	
					
					$stake = ($duration / ($time_stamp / 3600)) * $aShift['stake'];	
				}
				
				$oResponse->setFormElement('form1', 'sRealTime', array('value' => $hours));
				$oResponse->setFormElement('form1', 'sStakeDuty', array('value' => sprintf("%01.3f", $stake)));
				
				
				if ( $aShift['automatic'] ) {
					$oResponse->setFormElement('form1', 'nAuto', array('checked' => 'checked'));
				}
			}
			
			$aShiftTypes = $oShift->getShiftTypes();

			$oResponse->setFormElement('form1',			'nType', array(), '');
			$oResponse->setFormElementChild('form1',	'nType', array('value' => 0), 'Изберете шаблон');
			
			foreach ( $aShiftTypes as $key => $val ) {
				$id = $val['start'].','.$val['end'].','.$val['code'].','.$val['name'];
				$oResponse->setFormElementChild('form1',	'nType', array('value' => $key, 'id' => $id), '['.$val['code'].'] '.$val['name']);
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse ) {
			$nID			= Params::get('nID', 0);
			$nIDObj			= Params::get('nIDObject', 0);
			$sName			= Params::get("sName");
			$nAutomatic		= Params::get("nAuto");
			$sStake			= Params::get("sStake");
			$sMode			= Params::get("sMode");
			$sCode			= Params::get("sCode");
			$sShiftFrom 	= Params::get("sShiftFrom");
			$sShiftTo		= Params::get("sShiftTo");
			$sDuration		= Params::get("sDuration");
			$sDescription	= Params::get("sDescription");
			
			$f = explode(":", $sShiftFrom);
			$t = explode(":", $sShiftTo);	
			$d = explode(":", $sDuration);		
				
			$stake = $sStake;
				
			if ( isset($f[0]) && isset($f[1]) && isset($t[0]) && isset($t[1]) ) {
				$f_time 	= $f[0] * 3600 + $f[1] * 60;
				$t_time 	= $t[0] * 3600 + $t[1] * 60;
				$d_time 	= $d[0] * 3600 + $d[1] * 60;
				$duration 	= $d_time / 3600;
								
				if ( $t_time < $f_time ) {
					// Смяната преминава на другия ден
					$day = 24 * 60 * 60;
					$time_stamp = ($day - $f_time) + $t_time;
				} elseif ( ($t_time == $f_time) && ($t_time != "00:00:00") ) {
					$time_stamp = 24 * 60 * 60;
				} else {
					$time_stamp = $t_time - $f_time;
				}
					
				//$stake = $sDuration / (($time_stamp / 3600) *$sStake);	
				$stake = ($duration / ($time_stamp / 3600)) * $sStake;	
			}			

			$now = time();
			
			if ( empty($sCode) ) {
				throw new Exception("Въведете код на смяна!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($sName) ) {
				throw new Exception("Въведете наименование на смяна!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($sShiftFrom) ) {
				throw new Exception("Въведете начално време на смяна!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($sShiftTo) ) {
				throw new Exception("Въведете крайно време на смяна!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty( $sDuration ) )
			{
				throw new Exception( "Въведете продължителност на смяна!", DBAPI_ERR_INVALID_PARAM );
			}
			
//			if ( empty($sStake) ) {
//				throw new Exception("Въведете ставка за смяната!", DBAPI_ERR_INVALID_PARAM);
//			}
			
			if ( empty($sMode) || ($sMode == 'none') ) {
				throw new Exception("Изберете вид на смяната!!!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$oShift = new DBObjectShifts();
			$aShift = array();
			
			$sQuery = "
				SELECT id, code
				FROM object_shifts
				WHERE id != {$nID}
					AND code = '{$sCode}' 
					AND id_obj = '{$nIDObj}'
					AND to_arc = 0
			";
			
			$aShift = $oShift->selectOne( $sQuery );
			
			if ( !empty($aShift) ) {
				throw new Exception("Вече съществува запис с този код!", DBAPI_ERR_INVALID_PARAM);
				$oResponse->printResponse();
			}
			
			if ( empty($nID) ) {
				$aData = array();
				$aData['id'] = $nID;
				$aData['id_obj'] = $nIDObj;
				$aData['id_archiv'] = 0;
				$aData['code'] = $sCode;
				$aData['name'] = $sName;
				$aData['automatic'] = $nAutomatic;
				$aData['stake'] = $sStake;
				$aData['stake_duty'] = $stake;
				$aData['shiftFrom'] = $sShiftFrom;
				$aData['shiftTo'] = $sShiftTo;
				$aData['duration'] = $sDuration;
				$aData['validFrom'] = $now;
				$aData['description'] = $sDescription;
				$aData['mode'] = $sMode;
				
				$oShift->update( $aData );
			} else {
				$nNewID = $oShift->makeArchivFromShift( $nID );
				
				$oDBObjectDuty = new DBObjectDuty();
				$oDBObjectDuty->changeIDShift($nID,$nNewID);
				
//				$aData = array();
//				$aData['id'] = $nID;
//				$aData['validTo'] = $now;
//				$aData['to_arc'] = 1;
				
				//$oShift->update( $aData );
				
				$aData['id'] = $nID;
				$aData['id_obj'] = $nIDObj;
				$aData['id_archiv'] = 0;
				$aData['code'] = $sCode;
				$aData['name'] = $sName;
				$aData['automatic'] = $nAutomatic;
				$aData['stake'] = $sStake;
				$aData['stake_duty'] = $stake;
				$aData['shiftFrom'] = $sShiftFrom;
				$aData['shiftTo'] = $sShiftTo;
				$aData['duration'] = $sDuration;
				$aData['description'] = $sDescription;	
				$aData['mode'] = $sMode;			
				$aData['validFrom'] = $now;
				$aData['validTo'] = '0000-00-00';
				$aData['to_arc'] = 0;
				
				$oShift->update( $aData );
			}
			
			$oResponse->printResponse();
		}
			
	}
	
?>
<?php
	class ApiSetSetupSignalMessage {
		public function load( DBResponse $oResponse ) {
			$nID		= Params::get("nID", 0);
			
			$aSignals	= array();
			$aSignal	= array();
			$oSignals	= new DBSignals();
			
			$aSignals	= $oSignals->getSignals();
			$aSignal	= $oSignals->getSignalById($nID);

			$oResponse->setFormElement('form1',		 'nIDSignal', array(), '');	
			$oResponse->setFormElementChild('form1', 'nIDSignal', array('value' => 0), 'Изберете сигнал');	
			
			foreach ( $aSignals as $key => $val ) {
				$id = $val['test_flag'].",".$val['msg_al'].",".$val['msg_rest'];
				$oResponse->setFormElementChild('form1', 'nIDSignal', array('value' => $val['id'], 'id' => $id), $val['msg_al']);
			}
			
			if ( !empty( $nID ) ) {
				$aSignal['code_al'] 	= $aSignal['channel'] == "phone" && $aSignal['is_cid'] == 0 ? dechex($aSignal['code_al']) 	: $aSignal['code_al'];
				$aSignal['code_rest']	= $aSignal['channel'] == "phone" && $aSignal['is_cid'] == 0 ? dechex($aSignal['code_rest']) : $aSignal['code_rest'];
				
				$oResponse->setFormElement( 'form1', 'sIDChannel',		array('value' => $aSignal['channel']) );	
				$oResponse->setFormElement( 'form1', 'nIDSignal',		array('value' => $aSignal['id_sig']) );	
				$oResponse->setFormElement( 'form1', 'sAlarmName',		array('value' => $aSignal['msg_al']) );	
				$oResponse->setFormElement( 'form1', 'sRestoreName',	array('value' => $aSignal['msg_rest']) );	
				$oResponse->setFormElement( 'form1', 'nIDSignalAlarm', 	array('value' => $aSignal['code_al']) );	
				$oResponse->setFormElement( 'form1', 'nIDSignalRest',	array('value' => $aSignal['code_rest']) );	
				$oResponse->setFormElement( 'form1', 'sIDAlarmRadio',	array('value' => $aSignal['code_al']) );	
				$oResponse->setFormElement( 'form1', 'sIDRestoreRadio', array('value' => $aSignal['code_rest']) );	

                $oResponse->setFormElement( 'form1', 'nIDTest', 		array('value' => $aSignal['test']) );
				if ( $aSignal['flag'] == 1 ) {
					$oResponse->setFormElement( 'form1', 'active', 			array("checked" => "checked") );
				}

                $oResponse->setFormElement( 'form1', 'sName', 		array('value' => $aSignal['sName']) );
                if ( $aSignal['is_zone'] == 1 ) {
                    $oResponse->setFormElement( 'form1', 'is_zone', 	array("checked" => "checked") );
                }
                $oResponse->setFormElement( 'form1', 'zName', 		array('value' => $aSignal['zName']) );
                if ( $aSignal['is_sector'] == 1 ) {
                    $oResponse->setFormElement( 'form1', 'is_sector', 	array("checked" => "checked") );
                }
			}

			$oResponse->printResponse();
		}
			
		public function save( DBResponse $oResponse ) {
			global $db_name_sod, $db_sod;
			
			$nID			= Params::get("nID", 			0);
			$nIDObject		= Params::get("nIDObject", 		0);  	// ИД на обект в ТЕЛЕНЕТ!
			$nIDSignal		= Params::get("nIDSignal", 		0);
			$sIDChannel		= Params::get("sIDChannel", 	"");
			$sAlarmName		= Params::get("sAlarmName", 	"");
			$sRestoreName	= Params::get("sRestoreName", 	"");
			$nIDSignalRest	= Params::get("sIDSignalRest", 	0);
			$nIDSignalAlarm = Params::get("sIDSignalAlarm", 0);
			$sIDAlarmRadio	= Params::get("sIDAlarmRadio", 	0);
			$sIDRestRadio	= Params::get("sIDRestoreRadio", 0);
			$flag			= Params::get("flag", 			0);
			$alarm			= Params::get("active", 		"");
			$isTest 		= Params::get("is_test", 		0);
            $isZone 		= Params::get("is_zone", 		0);
            $isSector 		= Params::get("is_sector", 		0);
			$nIDTest		= Params::get("nIDTest", 		0);
			$user			= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			
			$now 			= time();
			$aMessage		= array();
			
			if ( empty($nIDSignal) ) {
				throw new Exception("Въведете сигнал!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( $sIDChannel == "radio" ) {
				$nCAlarm 	= hexdec($nIDSignalAlarm);
				$nCRestore 	= hexdec($nIDSignalRest);
			} elseif ( $sIDChannel == "phone" ) {
				$nCAlarm 	= $sIDAlarmRadio;
				$nCRestore	= $sIDRestRadio;
			} else {
				$nCAlarm 	= $sIDAlarmRadio;
				$nCRestore 	= $sIDRestRadio;
			}
							
			$oMessagesOld 	= new DBMessages();
			$oObjectSignals	= new DBObjectSignals();
			$oSignals		= new DBSignals();
			$oObjects		= new DBObjects();
			
			if ( $nIDObject > 0 ) {

				$is_cid		= $sIDChannel == "cid" ? 	1 		 : 0;
                $is_zone	= isset( $isZone )     ?    $isZone  : 1;
                $is_sector	= isset( $isSector )   ?    $isSector: 1;
				$id_cid 	= $sIDChannel == "cid" ? 	$nCAlarm : 0;
				$msg_al		= $sAlarmName;
				$msg_rest 	= $sRestoreName;
				$test 		= $isTest		== 1 ? $nIDTest : 0;
				$is_phone 	= $sIDChannel	== "radio" ? 	0 : 1;
				
				$aMessage	= $oSignals->getSignalById($nID);
				//$nIDOldObj	= $oObjects->getIDOldObjByID($nIDObject);
				
				$aData					= array();
				$aData['id'] 			= 0;
				$aData['id_obj'] 		= $nIDObject;
				$aData['id_signal'] 	= $nIDSignal;
				$aData['id_old_signal'] = !empty($nID) && isset($aMessage['id_sig']) 		? $aMessage['id_sig'] 		: 0;
				$aData['code_al'] 		= $nCAlarm;
				$aData['code_old_al'] 	= !empty($nID) && isset($aMessage['code_al']) 		? $aMessage['code_al'] 		: 0;
				$aData['code_rest'] 	= $nCRestore;
				$aData['code_old_rest'] = !empty($nID) && isset($aMessage['code_rest']) 	? $aMessage['code_rest'] 	: 0;
				$aData['msg_al'] 		= $sAlarmName;
				$aData['msg_old_al'] 	= !empty($nID) && isset($aMessage['msg_al']) 		? $aMessage['msg_al'] 		: "";
				$aData['type'] 			= !empty($nID) ? "edit" : "new";
				$aData['to_arc'] 		= 0;

				$oObjectSignals->update($aData);
				
				if ( $nID > 0 ) { // update
					// Telenet
					$sQuery = "
						UPDATE {$db_name_sod}.messages
						SET
							`id_sig` 	= '{$nIDSignal}',
							`is_cid` 	= '{$is_cid}',
							`is_zone` 	= '{$is_zone}',
							`is_sector`	= '{$is_sector}',
							`id_cid` 	= '{$id_cid}',
							`code_al` 	= '{$nCAlarm}',
							`msg_al` 	= '{$sAlarmName}',
							`code_rest` = '{$nCRestore}',
							`msg_rest` 	= '{$sRestoreName}',
							`flag` 		= '{$alarm}',
							`test_flag` = '{$isTest}',
							`test` 		= '{$test}',
							`is_phone` 	= '{$is_phone}',
							`updated_user` 	= '{$user}',
							`updated_time` 	= NOW()
						WHERE id 			= {$nID}
							AND to_arc 		= 0				
					";

					$db_sod->Execute($sQuery);		
						
				} else {
					// Telenet
					$sQuery = "
						INSERT INTO {$db_name_sod}.messages (
							`id_sig`,     `id_obj`,   `is_cid`,	`is_zone`,  `is_sector`,`id_cid`, `code_al`,
							`msg_al`,
							`code_rest`,
							`msg_rest`,
							`time_al`,
							`flag`,
							`test_flag`,
							`test`,
							`is_phone`,
							`updated_user`,
							`updated_time`
						) VALUES ( 
							{$nIDSignal},{$nIDObject},{$is_cid},{$is_zone},{$is_sector},{$id_cid}, {$nCAlarm},
							'{$sAlarmName}',
							{$nCRestore},
							'{$sRestoreName}',
							'0000-00-00 00:00:00',
							{$alarm},
							{$isTest},
							{$test},
							{$is_phone},
							{$user},
							NOW()
						)
					";	

					$db_sod->Execute($sQuery);	
								
				}
			}
			
			$oResponse->printResponse();
		}
			
	}
	
?>
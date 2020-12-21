<?php

	require_once ("../config/session.inc.php");
	if (empty($_SESSION['telenet_valid_session']) || empty($_SESSION['userdata'])) {
		echo "Неоторизиран достъп!";
		die();
	}
	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
	set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../');

	require_once ("../config/function.autoload.php");
	require_once ("../config/config.inc.php");
	require_once ("../include/adodb/adodb-exceptions.inc.php"); 
	$ADODB_EXCEPTION = 'DBException';
	require_once ("../config/connect.inc.php");
	require_once ("../include/general.inc.php");
	
if (get_magic_quotes_gpc()) {
	$_POST = stripslashes_deep($_POST);
}
try {
	$result = null;
	$aRequest = json_decode($_POST['request'], true);	
	if($aRequest) {
		switch($aRequest['type']) {
			case 'command' :
				$oDBMonitoringCommands = new DBBase2($db_sod,'monitoring_commands');
				$aRequest['data']['data']['is_dispatcher'] = true;
				$aCommand = array(
					'session_id' => session_id(),
					'type' => $aRequest['data']['command_type'],
					'data' => json_encode($aRequest['data']['data'])
				);
				
				$oDBMonitoringCommands->update($aCommand);
			break;
			case "carinfo":
				$id_auto = $aRequest['data']['data']['id_auto'];
				$callsign = $aRequest['data']['data']['callsign'];
				$oAuto = new DBBase2($db_sod, 'offices');
			if (!empty($callsign)) {
				$sQuery = "
					SELECT						
						a.id, 	
						a.reg_num,
						a.hash_km,
						a.inventar_num AS phone,
						a.geo_lat,
						a.geo_lan,
						DATE_FORMAT(a.geo_time,'%d.%m.%Y %h:%i:%s') AS geo_time,
						a.client_geo_lat,
						a.client_geo_lan,
						DATE_FORMAT(a.client_geo_time,'%d.%m.%Y %h:%i:%s') AS client_geo_time,
						CONCAT(amk.name,' ',am.model) AS mm,
						amt.type,									
						ro.gsm AS car_phone						
					FROM $db_name_auto_trans.auto a					
					LEFT JOIN $db_name_sod.patruls AS pa ON pa.num_patrul = $callsign
					LEFT JOIN $db_name_auto_trans.relation_offices AS ro ON ro.id_patrul = pa.id
					LEFT JOIN $db_name_auto_trans.auto_models am ON am.id=a.id_model
					LEFT JOIN $db_name_auto_trans.auto_marks amk ON amk.id = am.id_mark
					LEFT JOIN $db_name_auto_trans.auto_types amt ON amt.id = am.id_type					
					LEFT JOIN $db_name_personnel.personnel p	 ON a.id_person=p.id
					WHERE 1 AND a.id=$id_auto
				";				
			} else {
				$sQuery = "
					SELECT						
						a.id, 	
						a.reg_num,
						a.hash_km,
						a.inventar_num AS phone,
						CONCAT(amk.name,' ',am.model) AS mm,
						amt.type,
						CONCAT(p.fname,' ',p.mname,' ',p.lname) AS person_name,
						p.code AS person_code,
						p.mobile AS person_phone,
						p.id AS person_id
					FROM auto_trans.auto a										
					LEFT JOIN $db_name_auto_trans.auto_models am ON am.id=a.id_model
					LEFT JOIN $db_name_auto_trans.auto_marks amk ON amk.id = am.id_mark
					LEFT JOIN $db_name_auto_trans.auto_types amt ON amt.id = am.id_type					
					LEFT JOIN $db_name_personnel.personnel p	ON a.id_person=p.id
					WHERE 1 AND a.id=$id_auto
				";
			}
			
				$result = $oAuto->select($sQuery);		
				$result = $result[0];
				
				$oAutoTrans = new DBBase2($db_auto_trans,'auto');
				
				$aYearMonths = array();
				$nYearFrom = date('Y', time() - 60*60*24);
				$nMonthFrom = date('m', time() - 60*60*24);
				$aRealTables = $oAutoTrans->select("SHOW TABLES LIKE 'road\_lists\_____'");
				
				foreach ($aRealTables as $k => $aRealTableRow) $aRealTables[$k] = reset($aRealTableRow);
				rsort($aRealTables);
				foreach ($aRealTables as $sTable) {
					$sYearMonth = preg_replace("/[^\d]/",'',$sTable);
					$nYear = '20'.substr($sYearMonth,0,2);
					$nMonth = substr($sYearMonth,2,2);
					$aYearMonths[] = substr($sYearMonth,0,2).$nMonth;
					if($nYearFrom > $nYear || $nMonthFrom > $nMonth) break;
				}				
				$sQuery="
					SELECT 
						persons						
					FROM $db_name_auto_trans.road_lists_<yearmonth>					
					WHERE id_auto = $id_auto AND end_time=0
				";
				$aQueries = array();
				foreach ($aYearMonths as $sYearMonths) $aQueries[] = str_replace('<yearmonth>',$sYearMonths,$sQuery);																						
				$drivers = $oAutoTrans->select(implode("\nUNION\n",$aQueries));		
				$result['drivers'] = Array();
				if(!empty($drivers)) { 
					$aDrivers = explode(",", $drivers[0]['persons']);					
					foreach ($aDrivers as $driver) {
						$oPerson = new DBBase2($db_personnel,'personnel');
						$sQuery = "	SELECT 
										id AS person_id,
										code AS person_code,
										mobile AS person_phone,
										CONCAT(fname,' ',mname,' ',lname) AS person_name 
									FROM $db_name_personnel.personnel
									WHERE id=$driver";
						$_resp = $oPerson->select($sQuery);					
						array_push($result['drivers'],$_resp[0]);
					}					
				} else {
					$drivers = array(
						'person_id'		=> $result['person_id'],
						'person_code'	=> $result['person_code'],
						'person_phone'	=> $result['person_phone'],
						'person_name'	=> $result['person_name']
					);
					array_push($result['drivers'],$drivers);							
				}
				
			break;
			case "carinfo_nopatrol":
				
			break;	
			case "objinfo":
				$id_object = $aRequest['data']['data']['id_object'];
				$num_object= $aRequest['data']['data']['num_object'];
				$oObject = new DBBase2($db_sod, 'objects');				
				$sQuery = "
					SELECT
						o.id,
						o.num,
						o.name,
						CONCAT(o.address, IF(o.phone,', тел: ',''),o.phone) AS address,
						o._address_other,
						o.place,
						o.operativ_info,
						GROUP_CONCAT(f.name,IF(f.phone,' - ',''),f.phone SEPARATOR '@') AS mol,
						s.name as status,
						o.id_status,
						o.id_office
					FROM $db_name_sod.objects o
					LEFT JOIN $db_name_sod.faces AS f ON f.id_obj=$id_object AND f.to_arc = 0
					LEFT JOIN {$db_name_sod}.statuses AS s ON o.id_status = s.id
					WHERE o.id = $id_object
					GROUP BY o.id
				";				
				$aObject = $oObject->select($sQuery);	
				
				$nIDAlarmRegister = (int) $aRequest['data']['data']['id_alarm_register'];				
				$month1 = date('Ym',  strtotime("-1 month"));
				$month2 = date('Ym');
				$oDBase = new DBBase2($db_sod, 'messages');	
				$sQuery = "
					(SELECT 
										a.id,
										DATE_FORMAT(a.msg_time, '%d.%m.%Y %H:%i:%s') AS time,
										a.msg,
										IF(a.alarm=1,s.play_alarm,0) AS alarm_status						
										FROM archiv_$month1 AS a
										LEFT JOIN messages AS m ON m.id = a.id_msg
										LEFT JOIN signals AS s ON s.id = m.id_sig
										WHERE 1
										AND m.id_obj = $id_object										
										ORDER BY a.id DESC
										LIMIT 10
					)
					UNION ALL
					(SELECT 
										a.id,
										DATE_FORMAT(a.msg_time, '%d.%m.%Y %H:%i:%s') AS time,
										a.msg,
										IF(a.alarm=1,s.play_alarm,0) AS alarm_status						
										FROM archiv_$month2 AS a
										LEFT JOIN messages AS m ON m.id = a.id_msg
										LEFT JOIN signals AS s ON s.id = m.id_sig
										WHERE 1
										AND m.id_obj = $id_object										
										ORDER BY a.id DESC
										LIMIT 10
					)
					ORDER BY id DESC
					LIMIT 10										
				";				
				
				$aAlarms = $oDBase->select($sQuery);		
				$result=array('info'=>$aObject,'alarms'=>$aAlarms);
			break;
			case "init":
				$oBase = new DBBase2($db_sod, 'monitoring_commands');		
				$aUpd = array(
					'session_id' => $aRequest['data']['data']['session_id'],
					'type' => "init",
					'data' => json_encode($aRequest['data']['data'])
				);	
				$oBase->update($aUpd);
			break;
			case "notifications";
				
				$oDBNotifications = new DBNotifications();
				$oDBAlarmPanics = new DBAlarmPanics();
				
				$result['notifications'] = $oDBNotifications->getTelNotifications();
				$result['alarm_panics'] = $oDBAlarmPanics->getNewPanics();
			break;
			case 'notification_confirmed':
				$oDBNotifications = new DBNotifications();
				$oDBObjectsSingles = new DBObjectsSingles();
				$oDBNomenclaturesServices = new DBNomenclaturesServices();
				$oDBObjects = new DBObjects();
				
				$nIDNotification = $aRequest['data']['id_notification'];
				$sFaceName = $aRequest['data']['face'];
				
				$aNotification = $oDBNotifications->getNotificationRow($nIDNotification);
				$aObject = $oDBObjects->getRecord($aNotification['id_object']);
				$aNomenclatureService = $oDBNomenclaturesServices->getRecord($aNotification['additional_params']->id_service);
				
				$aObjectSingle = array();
				$aObjectSingle['id_object'] = $aNotification['id_object'];
				$aObjectSingle['id_office'] = $aObject['id_office'];
				$aObjectSingle['id_service'] = $aNotification['additional_params']->id_service;
				$aObjectSingle['service_name'] = 'Обаждане по телефон за аларма '.$aNotification['additional_params']->signal_name.' от '.$aNotification['additional_params']->signal_time." отговорил ".$sFaceName;
				$aObjectSingle['single_price'] = $aNomenclatureService['single_price'];
				$aObjectSingle['quantity'] = 1;
				$aObjectSingle['total_sum'] = $aNomenclatureService['single_price'];
				$aObjectSingle['start_date'] = time();
				
				$oDBObjectsSingles->update($aObjectSingle);
				
				$aForUpdate = array();
				$aForUpdate['id'] = $nIDNotification;
				$aForUpdate['status'] = 'sent';
				
				$oDBNotifications->update($aForUpdate);
				
			break;
			case "notification_cancel":
				$nIDNotification = $aRequest['data']['id_notification'];
				$oDBNotifications = new DBNotifications();
				
				$aForUpdate = array();
				$aForUpdate['id'] = $nIDNotification;
				$aForUpdate['status'] = 'failed';
				
				$oDBNotifications->update($aForUpdate);
				
			break;
			case "alarm_panic_cancel":
				$nID = $aRequest['data']['id_alarm_panic'];
				$oDBAlarmPanics = new DBAlarmPanics();
				
				$aForUpdate = array();
				$aForUpdate['id'] = $nID;
				$aForUpdate['status'] = 'fake';
				
				$oDBAlarmPanics->update($aForUpdate);
				
			break;
			case "alarm_panic_confirmed":
				
				$nIDAlarmPanic = $aRequest['data']['id_alarm_panic'];
				$sFaceName = $aRequest['data']['face'];
				
				$oDBAlarmPanics = new DBAlarmPanics();
				$oDBObjectsSingles = new DBObjectsSingles();
				$oDBObjectServices = new DBObjectServices();
				$oDBNomenclaturesServices = new DBNomenclaturesServices();				
				
				$aAlarmPanic = $oDBAlarmPanics->getAlarmPanic($nIDAlarmPanic);
				
				$aPanicService = $oDBObjectServices->getServiceByCode($aAlarmPanic['id_object'], 'PANIK');
				
				if(!empty($aPanicService)) {
					
					$aObjectSingle = array();
					$aObjectSingle['id_object'] = $aAlarmPanic['id_object'];
					$aObjectSingle['id_office'] = $aAlarmPanic['id_office'];
					$aObjectSingle['id_service'] = $aPanicService['id_service'];
					$aObjectSingle['service_name'] = 'Паник функция '.$aAlarmPanic['alarm_time']." от ".$sFaceName;
					$aObjectSingle['single_price'] = $aPanicService['total_sum'];
					$aObjectSingle['quantity'] = 1;
					$aObjectSingle['total_sum'] = $aPanicService['total_sum'];
					$aObjectSingle['start_date'] = time();

					$oDBObjectsSingles->update($aObjectSingle);
					
					/* ако има отдалеченост добавям и нея */
					$oDBIsuSettingsStatistics = new DBIsuSettingsStatistics();		
					$oDBCalculatorCoefficients = new DBCalculatorCoefficients();
					
					$aOfficesIncityKM = $oDBIsuSettingsStatistics->getOfficesInCityKm();	
					$aRentAndRemoteness = $oDBCalculatorCoefficients->getRentAndRemoteness();
					$remoteness_price_per_km = $aRentAndRemoteness['remoteness'];
					
					$in_city_km = 0;
				
					if(isset($aOfficesIncityKM[$aAlarmPanic['id_office']])) {
						$in_city_km = $aOfficesIncityKM[$aAlarmPanic['id_office']];
					} else if(isset($aOfficesIncityKM[0])) {
						$in_city_km = $aOfficesIncityKM[0];
					}
					
					$remoteness_in_km = round($aAlarmPanic['remoteness'] / 1000) - $in_city_km;
					
					if($remoteness_in_km > 0) {
						$remoteness_price = roundToHalf($remoteness_price_per_km * $remoteness_in_km);
				
						$aObjectSingle = array();
						$aObjectSingle['id_object'] = $aAlarmPanic['id_object'];
						$aObjectSingle['id_office'] = $aAlarmPanic['id_office'];
						$aObjectSingle['id_service'] = $aPanicService['id_service'];
						$aObjectSingle['id_limit_card'] = $aLimitCard['id'];
						$aObjectSingle['service_name'] = 'Отдалеченост при посещение за паника';
						$aObjectSingle['single_price'] = $remoteness_price;
						$aObjectSingle['quantity'] = 1;
						$aObjectSingle['total_sum'] = $remoteness_price;
						$aObjectSingle['start_date'] = date('Y-m-d');

						$oDBObjectsSingles->update($aObjectSingle);
					}	
					
				}
				
				$aToUp  = array();
				$aToUp['id'] = $nIDAlarmPanic;
				$aToUp['status'] = 'confirmed';
				
				$oDBAlarmPanics->update($aToUp);
				
			break;
			case "archive":			
				$oBase = new DBBase2($db_sod, 'alarm_register');
				$id_office = $aRequest['data']['data']['idRegion'];
				$id_person = $aRequest['data']['data']['idPerson'];
				$id_object = $aRequest['data']['data']['idObject'];				
				if(empty($id_object)) {
					$sQuery = "	
						SELECT 
							ar.id AS id_alarm_register,
							ar.id_office,
							ar.obj_num,
							ar.obj_name,
							ar.alarm_name,
							ar.alarm_time,
							ar.for_bulletin,
							GROUP_CONCAT(ac.time,'@@',CONCAT(p.fname,' ',p.mname,' ',p.lname),'@@',ac.comment SEPARATOR '::') AS comments
						FROM $db_name_sod.alarm_register AS ar
						LEFT JOIN $db_name_sod.alarm_comments AS ac ON ar.id = ac.id_alarm_register
						LEFT JOIN $db_name_personnel.personnel AS p ON p.id = $id_person
						WHERE ar.id_office = $id_office
						GROUP BY ar.id
						ORDER BY ar.id DESC
						LIMIT 20
					";			
				} else {
					$month1 = date('Ym',  strtotime("-1 month"));
					$month2 = date('Ym');
					$oDBase = new DBBase2($db_sod, 'messages');	
					$sQuery = "
						(SELECT 
							a.id,
							ar.id AS id_alarm_register,
							ar.id_office,
							a.num AS obj_num,
							ar.obj_name,
							ar.for_bulletin,
							DATE_FORMAT(a.msg_time, '%d.%m.%Y %H:%i:%s') AS alarm_time,
							a.msg AS alarm_name,
							IF(a.alarm=1,s.play_alarm,0) AS alarm_status,
							GROUP_CONCAT(ac.time,'@@',CONCAT(p.fname,' ',p.mname,' ',p.lname),'@@',ac.comment SEPARATOR '::') AS comments
							FROM archiv_$month1 AS a
							LEFT JOIN messages AS m ON m.id = a.id_msg
							LEFT JOIN signals AS s ON s.id = m.id_sig
							LEFT JOIN alarm_register AS ar ON ar.id_archiv = a.id
							LEFT JOIN alarm_comments AS ac ON ar.id = ac.id_alarm_register
							LEFT JOIN $db_name_personnel.personnel AS p ON p.id = ac.id_person
							WHERE m.id_obj = $id_object	
							GROUP BY a.id
							ORDER BY a.id DESC
							LIMIT 20
						)
						UNION ALL
						(SELECT 
							a.id,
							ar.id AS id_alarm_register,
							ar.id_office,
							a.num AS obj_num,
							ar.obj_name,
							ar.for_bulletin,
							DATE_FORMAT(a.msg_time, '%d.%m.%Y %H:%i:%s') AS alarm_time,
							a.msg AS alarm_name,
							IF(a.alarm=1,s.play_alarm,0) AS alarm_status,
							GROUP_CONCAT(ac.time,'@@',CONCAT(p.fname,' ',p.mname,' ',p.lname),'@@',ac.comment SEPARATOR '::') AS comments
							FROM archiv_$month2 AS a
							LEFT JOIN messages AS m ON m.id = a.id_msg
							LEFT JOIN signals AS s ON s.id = m.id_sig
							LEFT JOIN alarm_register AS ar ON ar.id_archiv = a.id
							LEFT JOIN alarm_comments AS ac ON ar.id = ac.id_alarm_register
							LEFT JOIN $db_name_personnel.personnel AS p ON p.id = ac.id_person
							WHERE m.id_obj = $id_object										
							GROUP BY a.id
							ORDER BY a.id DESC
							LIMIT 20
						)
						ORDER BY id DESC
						LIMIT 20										
					";																	
				}
				$result = $oBase->select($sQuery);
			break;
			case "get_service_bypass":
				$oBase = new DBBase2($db_sod,'object_bypass');
				$id_office = $aRequest['data']['data']['idRegion'];
				$time_to = date('Y-m-d H:i:s');
				$sQuery = "
					(SELECT 
						ob.id			AS id_bypass,
						ob.id_object	AS obj_id,
						o.name			AS obj_name,
						o.num			AS obj_num,
						m.msg_al		AS alarm_name,
						'bypass'		AS type
					FROM object_bypass AS ob 
					JOIN objects AS o ON o.id = ob.id_object AND o.id_office=$id_office
					LEFT JOIN messages AS m ON m.id = ob.id_message
					WHERE ob.time_to > '$time_to')
					UNION
					(SELECT
						''			AS id_bypass,
						id			AS obj_id, 
						name		AS obj_name,
						num			AS obj_num,
						''			AS alarm_name, 
						'service'	AS type
					FROM objects
					WHERE id_office = $id_office AND service_status = 1 AND id_status = 1)
				";				
				$result = $oBase->select($sQuery);
			break;
			case "stop_service_status":											
				$nIDObject = $aRequest['data']['data']['id_obj'];								
				$oBase = new DBBase2($db_sod,'objects');
				$aUpd['id'] = $nIDObject;
				$aUpd['service_status'] = 0;
				$oBase->update($aUpd);				
				$result = array();
			break;
			case "write_comment":				
				$oBase = new DBBase2($db_sod, 'alarm_comments');
				$oDBAlarmRegister = new DBBase2($db_sod, 'alarm_register');
				$aUpdate['id_alarm_register'] = $aRequest['data']['data']['id_alarm_register'];
				$aUpdate['id_person'] = $aRequest['data']['data']['id_person'];
				$aUpdate['comment'] = $aRequest['data']['data']['comment'];
				$aUpdate['time'] = date("Y-m-d H:i:s");				
				$oBase->update($aUpdate);
				
				$aAlarmRegister = array();
				$aAlarmRegister['id'] = $aRequest['data']['data']['id_alarm_register'];
				$aAlarmRegister['for_bulletin'] = !empty($aRequest['data']['data']['for_bulletin']) ? 1 : 0;
				$oDBAlarmRegister->update($aAlarmRegister);
				
			break;
			case "dispatcher_factor" :
				$oBase = new DBBase2($db_sod,'dispatcher_factor');				
				
				$id_person = $aRequest['data']['id_person'];
				$id_alarm_register = $aRequest['data']['id_alarm_register'];
				$id_region = $aRequest['data']['id_region'];
				$now = date('Y-m-d H:i:s',mktime());
				$sQuery = "
					SELECT
						pn.id AS perosn,
						od.id AS id_object_duty,
						od.startShift,
						od.endShift
					FROM $db_name_personnel.personnel AS pn
					LEFT JOIN $db_name_sod.object_duty AS od ON od.id_person = pn.id
					WHERE od.startShift <= '$now' 
					AND   od.endShift >= '$now' 
					AND   id_person = $id_person
				";				
				$aShifts = $oBase->select($sQuery);
								
				if (!empty($aShifts)) {										
					$id_object_duty = $aShifts['id_object_duty'];														
				//$id_object_duty=1;
					$aUpd = array(
						'id_person'			=> $id_person,
						'id_object_duty'	=> $aShifts[0]['id_object_duty'],
						'id_alarm_register'	=> $id_alarm_register,
						'id_region'			=> $id_region
					);			
					$oBase->update($aUpd);
				}				
				$result = $aRequest;
			break;
			case "suggest_object":
				$oBase = new DBBase2($db_sod,'objects');					
				$idOffice = $aRequest['data']['data']['idRegion'];
				$startsWith = addslashes($aRequest['data']['data']['startsWith']);								
				$sQuery = "
					SELECT 
						o.id AS value,						
						CONCAT('[',o.num,'][',off.name,'] ',o.name,' [',s.name,']') AS name,
						o.num AS num
					FROM objects AS o
					LEFT JOIN statuses AS s ON s.id = o.id_status
					LEFT JOIN offices AS off ON off.id=o.id_office
					WHERE o.id_status!=4
					#AND o.id_office = $idOffice
					AND (o.num LIKE '{$startsWith}%' OR o.name LIKE '$startsWith%')
					ORDER BY o.num ASC
				";		
				$result = $oBase->select($sQuery);				
			break;
			case "getMsg":
				$oBase = new DBBase2($db_sod,'objects');
				$region = $aRequest['data']['data']['region'];
				$objNum = $aRequest['data']['data']['objNum'];
				$objID = $aRequest['data']['data']['objID'];
				$sQuery = "
					(SELECT 	
						m.id,
						1 as alarm,
						m.code_al AS code,
						m.msg_al AS msg
					FROM objects AS o
					JOIN messages AS m ON m.id_obj=o.id AND m.to_arc = 0 
					WHERE o.id=$objID AND o.num=$objNum AND o.id_status!=4)
					UNION
					(SELECT 	
						m.id,
						0 as alarm,
						m.code_rest AS code,
						m.msg_rest AS msg
					FROM objects AS o
					JOIN messages AS m ON m.id_obj=o.id AND m.to_arc = 0
					WHERE o.id=$objID AND o.num=$objNum AND o.id_status!=4 AND m.code_rest>0)
					";				
				$result = $oBase->select($sQuery);		
			break;
			case "fireup_alarm":
				$oBase = new DBBase2($db_sod,'objects');
				$objID		= (int)$aRequest['data']['data']['objID'];
				$objNum		= (int)$aRequest['data']['data']['objNum'];
				$idOffice	= (int)$aRequest['data']['data']['region'];
				$id_msg		= (int)$aRequest['data']['data']['id_msg'];
				$alarm_code = $aRequest['data']['data']['alarm_code'];
				$alarm		= (int)$aRequest['data']['data']['alarm'];
				$msg		= $aRequest['data']['data']['msg'];
				if (empty($objNum)) return;						
				$sQuery = "
					SELECT id, num
					FROM objects
					WHERE id=$objID
					";		
				$aObject = $oBase->selectOnce($sQuery);		
				
				if(empty($aObject)) throw new Exception("Не е намерен обекта.");
				
				$oDBArchive = new DBMonthTable($db_name_sod,'archiv_',$db_sod);
				$aArchiveMsg = array(
					'id_msg'	=> $id_msg,
					'msg_time'	=> date('Y-m-d H:i:s'),
					'channel'	=> 99,
					'num'		=> $aObject['num'],
					'status'	=> $alarm_code,
					'alarm'		=> $alarm,
					'msg'		=> $msg
				);			
				$oDBArchive->update($aArchiveMsg);			
			break;
			case "obj_set_geo":
				$oBase = new DBBase2($db_sod,'objects');		
				$aData = array(
					'id'		=> (int)$aRequest['data']['data']['id'],
					'geo_lat'	=> $aRequest['data']['data']['geo_lat'],
					'geo_lan'	=> $aRequest['data']['data']['geo_lan'],
					'confirmed'	=> 1
				);								
				$result = $oBase->update($aData);
			break;
		}
	}
	$response = array(
	    'type' => 'response',
	    'data' => $result
	);
} catch(Exception $ex) {	
	print_r($ex);
	$response = array(
	    'type' => 'error',
	    'data' => array(
			'type' => get_class($ex),
			'message' => $ex->getMessage(),
			'file' => $ex->getFile(),
			'line' => $ex->getLine(),
	    )
	);
}
die(json_encode($response));

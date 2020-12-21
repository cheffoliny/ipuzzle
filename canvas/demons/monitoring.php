<?php
	DEFINE('REASON_ALARM_AUTO_RESTORE'	, 21				);	// ID на причината за автоматично прекратяване на алармата, ако на обекта не е реагирано до N сек
	DEFINE('REASON_OPEN_AFTER_ALARM'	, 20				);	// ID на причината за отваряне след аларма
	DEFINE('MAX_GPS_REPORT'				, 30*60				);	// Максимално време за което GPS-а трябва да рапортува, ако се надвиши това време колата е в статус, без координати 
	DEFINE('MAX_DISLPAY_REPORT'			, 5*60				);	// Максимално време за което дисплея трябва да рапортува, ако се надвиши това време колата е в статус, без връзка с дисплея 
	DEFINE('MEDIATOR_URL'				, '213.91.252.138'	);
	DEFINE('MEDIATOR_PORT'				, 7000				);
	DEFINE('MONITORING_DEBUG'			, 4					);
	    


	if ( !isset($_SESSION) ) {
		session_start();
	}
	
	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
	set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../../');
	
	require_once ("../../config/function.autoload.php");
    require_once ("../../config/config.inc.php");
	require_once ("../../config/connect.inc.php");
	require_once ("../../include/general.inc.php");

	$oCars		= new DBCars();
	$oObjects	= new DBObjects();
	$oSignals	= new DBSODSignals();
	$oStory		= new DBStory();
	$oRegister	= new DBAlarmRegister();
	$oPatruls	= new DBSODPatruls();
	$oAlPatruls	= new DBAlarmPatruls();
	$oSettings	= new DBFiltersEvents();
	$oStat		= new DBSettingsStat();
	$oPid		= new pidfile("", "monitoring");
    
    
/*    
	$oObjectEvent = array(
    					'id'				=> 36013575,
    					'num'				=> 4992,
    					'name' 				=> 'Магазин 1',
    					'idRegion'			=> 20,
    					'mainSig'			=> 'Обща аларма',
    					'reactionTime'		=> 220,
    					'geo_lan'			=> 26.9267821311951,
    					'geo_lat'			=> 43.2736941397687,
    					'statusAlarm'		=> FALSE,
    					'statusService'		=> FALSE,                                        
    );
    

    MonitoringEvents::addCmdObject( $oObjectEvent );
    MonitoringEvents::send();
    die();
*/


	$aCars		= array();
	$aObjects	= array();
	$aSignals	= array();
	$aStory		= array();
	$aSettings	= array();
	$nArchPos	= 0;

	$aObjStat	= array();
	$aCarStat	= array();

	$aAutoCo	= array();

	set_time_limit(0);

	$aWaitForMove	= array();
	$aWaitForCar	= array();
	
	$nTimer			= 0;
	$nTimerFilter	= 0;
	$sArchTableName	= '';
	$nArchPos		= 0;

	while ( true ) {	
		
		
		// ------------------------------ Таймер - една минута, след което презарежда настройките! ------------------------------
		if ( (time() - $nTimer) >= 60 ) {
			$nTimer		= time();
			$aSettings	= $oSettings->getSettings();

			$sig1		= isset($aSettings[0]['reactions'])				? $aSettings[0]['reactions']			: "";
			$sig2		= isset($aSettings[0]['reactions_down'])		? $aSettings[0]['reactions_down']		: "";
			$al1		= isset($aSettings[0]['alarm_after'])			? $aSettings[0]['alarm_after']			: 0;
			$al2		= isset($aSettings[0]['down_after_alarm'])		? $aSettings[0]['down_after_alarm']		: 0;
			$wait		= isset($aSettings[0]['wait_time'])				? $aSettings[0]['wait_time']			: 0;
			$max_time	= isset($aSettings[0]['max_time_react'])		? $aSettings[0]['max_time_react']		: 0;
			$dis_to_arr	= isset($aSettings[0]['distance_to_arrival'])	? $aSettings[0]['distance_to_arrival']	: 50;

			$aAlStat		= array();
			$aAlStat		= explode(",", $sig1);

			$aOpClose		= array();
			$aOpClose		= explode(",", $sig2);
		}
		// -----------------------------------------------------------------------------------------------------------------------

		
		
		
		// ----------------------------------- САМО ЗА ТЕСТ -----------------------------------
		if ( (time() - $nTimer) >= 40 ) 
			die();
			
		// ------------------------------------------------------------------------------------
			
			

		// ------------------------------ Аларми без реакция!!! ------------------------------
		// Затваряне на всички аларами, за които не е регирано през последнит $max_time сек 
		$aReactions		= array();
		$aReactions		= $oRegister->getAlarmsWithOutReaction($max_time);

		foreach ( $aReactions as $nReactClose ) {
			$nIDReactClose	= isset($nReactClose['id']) ? $nReactClose['id'] : 0;
			$oPatruls->closeAlarm($nIDReactClose, REASON_ALARM_AUTO_RESTORE);
		}
		// -----------------------------------------------------------------------------------
		
		
		
		
		
		// ------------------------------ Сигнали от архива ------------------------------
		$sCuurentTableName = $oSignals->getLastArchTable();

		if( empty($sArchTableName) ) {
			// първо пускане на демона
			$sArchTableName = $sCuurentTableName;
			$aObjects	= $oSignals->getSignalsAfterId($sArchTableName, 9999999999999);
			
		} elseif( $sArchTableName != $sCuurentTableName ) {
			// Месеца е првъртял
			
			// 1. Изчерпваме стартия архив
			$aObjects1	= $oSignals->getSignalsAfterId($sArchTableName, $nArchPos);

			// 2. Вземаме сигналите от новия архив 
			$sArchTableName = $sCuurentTableName;
			$aObjects2	= $oSignals->getSignalsAfterId($sArchTableName, 0);
			
			$aObjects = array_merge( $aObjects1, $aObjects2);
			
		} else{
			// Текущ месец
			$aObjects	= $oSignals->getSignalsAfterId($sArchTableName, $nArchPos);
		}
			
		// -----------------------------------------------------------------------------------

		
		
		
		// ------------------------------ Анонсиране на автомобили към обекти без реакция ------------------------------
		
		// Обекти без реакция 
		$aObjWithOutReaction	= $oObjects->getObjectsWithOutReaction();

		foreach ($aObjWithOutReaction as $nIDObj => $valObject) {
				ReactionProcess( $valObject );
		}

		// -----------------------------------------------------------------------------------

		
		
		
		
		// ------------------------------ Обработка на архива ------------------------------
		foreach ( $aObjects as $key => &$val ) {
			$nArchPos				= $val['id_archiv'];
			
			$nIDObj					= isset($val['id_obj']) && is_numeric($val['id_obj']) ? $val['id_obj'] : 0;
			$nIDRegister			= $oRegister->getCurrentRegisterIDByAlarmObject($nIDObj);

			// Сигнали без регистър
			if ( empty($nIDRegister) ) {

				// Аларма. Нова аларма! 
				if ( in_array($val['id_sig'], $aAlStat) && ($val['alarm'] == 1) && !empty($nIDObj) ) {
					// Регистрираме алармата!
					$aAlarm					= array();
					$aAlarm['id']			= 0;
					$aAlarm['id_object']	= $nIDObj;
					$aAlarm['id_work_card'] = 0;
					$aAlarm['id_office']	= $val['id_office'];
					$aAlarm['obj_num']		= $val['object_num'];
					$aAlarm['obj_name']		= $val['object_name'];
					$aAlarm['obj_address']	= $val['object_address'];
					$aAlarm['obj_geo_lan']	= $val['geo_lan'];
					$aAlarm['obj_geo_lat']	= $val['geo_lat'];
					$aAlarm['obj_time_alarm_reaction'] = $val['time_limit'];
					$aAlarm['id_archiv']	= $val['id_archiv'];
					$aAlarm['alarm_time']	= $val['alarm_time'];
					$aAlarm['alarm_name']	= $val['message'];
					$aAlarm['status']		= "active";

					$oRegister->update($aAlarm);
					$nIDRegister = $aAlarm['id'];

					// Инициализираме архива с първи запис
					$aStory						= array();
					$aStory['id']				= 0;
					$aStory['id_alarm_register']= $nIDRegister;
					$aStory['id_archiv']		= $val['id_archiv'];
					$aStory['id_msg']			= $val['id_msg'];
					$aStory['id_sig']			= $val['id_sig'];
					$aStory['alarm_time']		= $val['alarm_time'];
					$aStory['alarm_name']		= $val['message'];
					$aStory['pictogram']		= "";
					$aStory['alarm_status']		= "alarm";
					$aStory['id_patrul']		= 0;
					$aStory['patrul_num']		= 0;
					$aStory['id_auto']			= 0;
					$aStory['id_reason']		= "";
					$aStory['distance']			= 0;

					$oStory->update($aStory);

					$aUpdate					= array();
					$aUpdate['id']				= $nIDObj;
					$aUpdate['reaction_status'] = 1;
					$aUpdate['reaction_car']	= 0;
					
					$oObjects->update($aUpdate);

					// Генерираме събитие към ПЛАТНОТО -> Object Alarm
					MonitoringEvents::addCmdObject( array(
    					'id'				=> $nIDObj,
    					'idRegion'			=> $val['id_office'],
    					'event_type'		=> 'alarm',
    					'num'				=> $val['object_num'],
    					'name' 				=> $val['object_name'],
    					'mainSig'			=> $val['alarm_message'],
    					'reactionTime'		=> 3*60,						// TODO - да се смята коефициент време за реакция
    					'geo_lan'			=> $val['geo_lan'],
    					'geo_lat'			=> $val['geo_lat'],
    					'statusAlarm'		=> TRUE,
    					'statusServise'		=> FALSE,
					));

					ReactionProcess( $val );
				}
			} else {
				// Сигнали с регистър
				// Правим проверка дали е алармиращ сигнала

				// 1. Сигнала НЕ е алармиращ - вкарваме го в архив!
				if ( !in_array($val['id_sig'], $aAlStat) || (in_array($val['id_sig'], $aAlStat) && ($val['alarm'] == 0)) && !empty($nIDObj) ) {
					$nIDCar			= isset($val['reaction_car']) && is_numeric($val['reaction_car']) ? $val['reaction_car'] : 0;
					$aPatrul		= $oPatruls->getPatrulByAuto($nIDCar);
					$distance		= 0;
					$status			= "update";

					if ( !empty($nIDCar) ) {
						$aTempCar	= array();
						$aTempCar	= $oCars->getCarByID($nIDCar);
						
						$distance	= $oObjects->getDistanceByGeo($val['geo_lat'], $val['geo_lan'], $aTempCar['geo_lat'], $aTempCar['geo_lan']);
						$distance	= $distance * 1000;
					}

					if ( $status != "ccccancel" ) {
						$aTCar	= array();

						if ( $status == "cancel" ) {
							$status = "update";
						}

						if ( !empty($nIDCar) ) {
							$aTCar						= $oCars->getCarByID($nIDCar);
							$distance					= $oObjects->getDistanceByGeo($val['geo_lat'], $val['geo_lan'], $aTCar['geo_lat'], $aTCar['geo_lan']);
							$distance					= $distance * 1000;
						} else {
							$distance					= 0;
						}

						if ( isset($aTCar['geo_lan']) ) {
							$aDistance	= array();
							$nPatNum	= isset($aPatrul['num'])	? $aPatrul['num']	: 0;
							$aDistance	= $oPatruls->getLastDistanceByPatrul($nPatNum);
							$dLan		= isset($aDistance['geo_lan'])	? $aDistance['geo_lan']	: 0;
							$dLat		= isset($aDistance['geo_lat'])	? $aDistance['geo_lat']	: 0;
							$nDs		= $oObjects->getDistanceByGeo($dLat, $dLan, $aTCar['geo_lat'], $aTCar['geo_lan']);
							$nDs		= $nDs * 1000;
						} else {
							$nDs		= 0;
						}

						if ( $nDs > 10000 ) {
							$nDs = 0;
						}

						$aStory						= array();
						$aStory['id']				= 0;
						$aStory['id_alarm_register']= $nIDRegister;
						$aStory['id_archiv']		= $val['id_archiv'];
						$aStory['id_msg']			= $val['id_msg'];
						$aStory['id_sig']			= $val['id_sig'];
						$aStory['alarm_time']		= $val['alarm_time'];
						$aStory['alarm_name']		= $val['message'];
						$aStory['pictogram']		= "";
						$aStory['alarm_status']		= $status;
						$aStory['id_patrul']		= isset($aPatrul['id'])		? $aPatrul['id']	: 0;
						$aStory['patrul_num']		= isset($aPatrul['num'])	? $aPatrul['num']	: 0;
						$aStory['patrul_geo_lan']	= isset($val['geo_lan'])	? $val['geo_lan']	: 0;
						$aStory['patrul_geo_lat']	= isset($val['geo_lat'])	? $val['geo_lat']	: 0;
						$aStory['patrul_trace']		= $nDs;
						$aStory['id_auto']			= $nIDCar;
						$aStory['id_reason']		= "";
						$aStory['distance']			= $distance;

						$oStory->update($aStory);

						// TODO - евентуално да с еинформира платното за нов сигналл
					
					}

					// Проверка за снемане от охрана
					if ( in_array($val['id_sig'], $aOpClose) ) {
						// Имаме снемане, прекратяваме действията!!!
						$nAlTime = $oRegister->getTimeByRegister($nIDRegister);

						if ( !empty($nAlTime) && ((time() - $nAlTime) <= $al2) ) {
							$status	= "cancel";
							$oPatruls->closeAlarm($nIDRegister, REASON_OPEN_AFTER_ALARM);
							
							
							//echo "Srabotvane na filtar! {$nIDRegister}, {$nAlTime}, {$al2}\n";
						}
					}
				}

				// 2. Сигнала е алармиращ
				if ( in_array($val['id_sig'], $aAlStat) && ($val['alarm'] == 1) && !empty($nIDObj) ) {
					// Вкарваме в архива запис за промяната
					$nIDCar			= isset($val['reaction_car']) && is_numeric($val['reaction_car']) ? $val['reaction_car'] : 0;
					$aPatrul		= $oPatruls->getPatrulByAuto($nIDCar);
					$distance		= 0;
					$status			= "update";
					$aTempCar		= array();

					if ( !empty($nIDCar) ) {
						$aTempCar	= $oCars->getCarByID($nIDCar);
						$distance	= $oObjects->getDistanceByGeo($val['geo_lat'], $val['geo_lan'], $aTempCar['geo_lat'], $aTempCar['geo_lan']);
						$distance	= $distance * 1000;
					} else {
						$distance	= 0;
					}

					if ( isset($aTempCar['geo_lan']) ) {
						$aDistance	= array();
						$nPatNum	= isset($aPatrul['num'])	? $aPatrul['num']	: 0;
						$aDistance	= $oPatruls->getLastDistanceByPatrul($nPatNum);
						$dLan		= isset($aDistance['geo_lan'])	? $aDistance['geo_lan']	: 0;
						$dLat		= isset($aDistance['geo_lat'])	? $aDistance['geo_lat']	: 0;
						$nDs		= $oObjects->getDistanceByGeo($dLat, $dLan, $aTempCar['geo_lat'], $aTempCar['geo_lan']);
						$nDs		= $nDs * 1000;
					} else {
						$nDs		= 0;
					}

					if ( $nDs > 10000 ) {
						$nDs = 0;
					}

					$aStory						= array();
					$aStory['id']				= 0;
					$aStory['id_alarm_register']= $nIDRegister;
					$aStory['id_archiv']		= $val['id_archiv'];
					$aStory['id_msg']			= $val['id_msg'];
					$aStory['id_sig']			= $val['id_sig'];
					$aStory['alarm_time']		= $val['alarm_time'];
					$aStory['alarm_name']		= $val['message'];
					$aStory['pictogram']		= "";
					$aStory['alarm_status']		= "update";
					$aStory['id_patrul']		= isset($aPatrul['id'])		? $aPatrul['id']	: 0;
					$aStory['patrul_num']		= isset($aPatrul['num'])	? $aPatrul['num']	: 0;
					$aStory['patrul_geo_lan']	= isset($val['geo_lan'])	? $val['geo_lan']	: 0;
					$aStory['patrul_geo_lat']	= isset($val['geo_lat'])	? $val['geo_lat']	: 0;
					$aStory['patrul_trace']		= $nDs;
					$aStory['id_auto']			= $nIDCar;
					$aStory['id_reason']		= "";
					$aStory['distance']			= $distance;

					$oStory->update($aStory);
					
					
					// TODO - евентуално да с еинформира платното за нов сигналл

				}

			}
		}

		unset($val);

		// -----------------------------------------------------------------------------------

		
		
		
		// ------------------------------ Обработка на колите ------------------------------
		
		$aCars	= $oCars->getCurrentPosition();
		
		foreach ( $aCars as $key => $val ) {
			$aTempAuto = array();
			$aTempAuto['id_auto'] = $val['id_auto'];
			$aTempAuto['geo_lan'] = isset($aAutoCo[$val['id_auto']]['geo_lan']) ? $aAutoCo[$val['id_auto']]['geo_lan'] : $val['geo_lan'];
			$aTempAuto['geo_lat'] = isset($aAutoCo[$val['id_auto']]['geo_lat']) ? $aAutoCo[$val['id_auto']]['geo_lat'] : $val['geo_lat'];
			$aTempAuto['geo_lan2'] = $val['geo_lan'];
			$aTempAuto['geo_lat2'] = $val['geo_lat'];

			$aAutoCo[$val['id_auto']]['geo_lan'] = $val['geo_lan'];
			$aAutoCo[$val['id_auto']]['geo_lat'] = $val['geo_lat'];

			$aCarInfo = $oCars->getCarByID($val['id_auto']);

			$rt = isset($aCarInfo['reaction_time'])		? $aCarInfo['reaction_time']	: 0;
			$rs = isset($aCarInfo['reaction_status'])	? $aCarInfo['reaction_status']	: 0;
			$n1	= 0;

			// Време за реакция
			$nTm  = (time() - $rt);

			if ( ($nTm < 0) || ($nTm > 3600) ) {
				$nTm = 0;	
			}

			$nObj = $oCars->getObjectByAuto($val['id_auto']);
			$nDis = 0;
			$aObj = $oObjects->getObjectsByID($nObj);
			$aPat = $oPatruls->getPatrulByAuto($val['id_auto']);
			$nReg = $oRegister->getCurrentRegisterIDByAlarmObject($nObj);
			$w2	  = ($wait * 15) / 100;

			$aDistance	= array();
			$nPatNum	= isset($aPat['num'])			? $aPat['num']			: 0;
			$aDistance	= $oPatruls->getLastDistanceByPatrul($nPatNum);
			$dLan		= isset($aDistance['geo_lan'])	? $aDistance['geo_lan']	: 0;
			$dLat		= isset($aDistance['geo_lat'])	? $aDistance['geo_lat']	: 0;
			$nDs		= $oObjects->getDistanceByGeo($dLat, $dLan, $val['geo_lat'], $val['geo_lan']);
			$nDs		= $nDs * 1000;

			if ( $nDs > 10000 ) {
				$nDs = 0;
			}

			if ( isset($aObj['geo_lan']) && isset($aObj['geo_lan']) ) {
				$nDis = $oObjects->getDistanceByGeo($val['geo_lat'], $val['geo_lan'], $aObj['geo_lat'], $aObj['geo_lan']);
				$nDis *= 1000;
			}

			// Отказваме кола
			if ( ($rs == 1) && ($nTm >= $wait) ) {
				if ( !empty($val['id_auto']) ) {
					$aUpdate					= array();
					$aUpdate['id']				= $val['id_auto'];
					$aUpdate['reaction_status'] = 3;
					$aUpdate['reaction_time']	= time();

					$oCars->update($aUpdate);

					$rs		= 3;
					$nTm	= 0;
				}

				if ( !empty($nObj) ) {
					$aUpdate					= array();
					$aUpdate['id']				= $nObj;
					$aUpdate['reaction_status'] = 1;
					$aUpdate['reaction_car']	= 0;

					$oObjects->update($aUpdate);
				}
				
				if ( !empty($nReg) ) {
					$oAlPatruls->delByIDRegister($nReg);

					$aStory						= array();
					$aStory['id']				= 0;
					$aStory['id_alarm_register']= $nReg;
					$aStory['id_archiv']		= 0;
					$aStory['id_msg']			= 0;
					$aStory['id_sig']			= 0;
					$aStory['alarm_time']		= time();
					$aStory['alarm_name']		= "";
					$aStory['pictogram']		= "";
					$aStory['alarm_status']		= "car_free";
					$aStory['id_patrul']		= isset($aPat['id'])		? $aPat['id']		: 0;
					$aStory['patrul_num']		= isset($aPat['num'])		? $aPat['num']		: 0;
					$aStory['patrul_geo_lan']	= isset($val['geo_lan'])	? $val['geo_lan']	: 0;
					$aStory['patrul_geo_lat']	= isset($val['geo_lat'])	? $val['geo_lat']	: 0;
					$aStory['patrul_trace']		= 0; //$nDs;
					$aStory['id_auto']			= $val['id_auto'];
					$aStory['id_reason']		= "";
					$aStory['distance']			= $nDis;

					$oStory->update($aStory);
					
					
					$aAlarm						= array();
					$aAlarm['id']				= $nReg;
					$aAlarm['patruls']			= 0;

					$oRegister->update($aAlarm);
					
					$aAuto						= array();
					$aAuto['id']				= $val['id_auto'];
					$aAuto['reaction_object']	= 0;

					$oCars->update($aAuto);

					MonitoringEvents::addCmdCar(array(
						'id'		=> $val['id_auto'],
						'idRegion'	=> $val['id_office'],
						'event_type'=> 'car_free',
						'geo_lan'	=> isset($val['geo_lan'])	? $val['geo_lan']	: 0,
						'geo_lat'	=> isset($val['geo_lat'])	? $val['geo_lat']	: 0
					));
				} 
			} 

			if ( ($rs == 3) && ($nTm >= $w2) ) {
				if ( !empty($val['id_auto']) ) {
					$aUpdate					= array();
					$aUpdate['id']				= $val['id_auto'];
					$aUpdate['reaction_status'] = 0;
					$aUpdate['reaction_object'] = 0;
					$aUpdate['reaction_time']	= "0000-00-00";

					$oCars->update($aUpdate);

					$rs		= 0;
					$nTm	= 0;
				}
			}

			// Колата се мести
			if ( $oCars->carMove($aTempAuto) ) {


				//echo "Mestq kola: ".$val['id_auto']." v ofis: ".$val['id_office']." => ".$val['geo_lan']."/".$val['geo_lat']."\n";

				if ( isset($aObj['geo_lan']) && isset($aObj['geo_lan']) ) {
					$nDis = $oObjects->getDistanceByGeo($val['geo_lat'], $val['geo_lan'], $aObj['geo_lat'], $aObj['geo_lan']);
					$nDis *= 1000;
 				}

				// Колата е свободна
				if ( $rs == 0 ) {
					//continue;
				}

				// Колата тръгва за позицията
				if ( ($rs == 4) ) {  //&& ($nTm >= $wait) // $rs == 1
					if ( !empty($val['id_auto']) ) {
						$aUpdate					= array();
						$aUpdate['id']				= $val['id_auto'];
						$aUpdate['reaction_status'] = 2;
						$aUpdate['reaction_time']	= time();

						$oCars->update($aUpdate);
					}
					
					if ( !empty($nReg) && !empty($nDis) ) {
						$aStory						= array();
						$aStory['id']				= 0;
						$aStory['id_alarm_register']= $nReg;
						$aStory['id_archiv']		= 0;
						$aStory['id_msg']			= 0;
						$aStory['id_sig']			= 0;
						$aStory['alarm_time']		= time();
						$aStory['alarm_name']		= "";
						$aStory['pictogram']		= "";
						$aStory['alarm_status']		= "start";
						$aStory['id_patrul']		= isset($aPat['id'])		? $aPat['id']	: 0;
						$aStory['patrul_num']		= isset($aPat['num'])		? $aPat['num']	: 0;
						$aStory['patrul_geo_lan']	= isset($val['geo_lan'])	? $val['geo_lan']	: 0;
						$aStory['patrul_geo_lat']	= isset($val['geo_lat'])	? $val['geo_lat']	: 0;
						$aStory['patrul_trace']		= $nDs;
						$aStory['id_auto']			= $val['id_auto'];
						$aStory['id_reason']		= "";
						$aStory['distance']			= $nDis;

						$oStory->update($aStory);
					} 

					$fAuto	= $oCars->checkForGPS($val['id_auto']);

					if ( !$fAuto ) {
						// Добавяме запис в историята
						$aStory							= array();
						$aStory['id']					= 0;
						$aStory['id_alarm_register']	= $nReg;
						$aStory['id_archiv']			= 0;
						$aStory['id_msg']				= 0;
						$aStory['id_sig']				= 0;
						$aStory['alarm_time']			= time();
						$aStory['alarm_name']			= "";
						$aStory['pictogram']			= "";
						$aStory['alarm_status']			= "gps_failure";
						$aStory['id_patrul']			= isset($aPat['id'])		? $aPat['id']	: 0;
						$aStory['patrul_num']			= isset($aPat['num'])		? $aPat['num']	: 0;
						$aStory['patrul_geo_lan']		= isset($val['geo_lan'])	? $val['geo_lan']	: 0;
						$aStory['patrul_geo_lat']		= isset($val['geo_lat'])	? $val['geo_lat']	: 0;
						$aStory['patrul_trace']			= 0;
						$aStory['id_auto']				= $val['id_auto'];
						$aStory['id_reason']			= 0;
						$aStory['distance']				= 0;

						$oStory->update($aStory);
					}

					// Генерираме събитие към ПЛАТНОТО -> Car Start
					MonitoringEvents::addCmdCar( array(
						'id' 				=> $val['id_auto'],
						'idRegion'			=> $val['id_office'],
						'event_type'		=> 'start',
						'callsign'			=> isset($aPat['num'])					? $aPat['num']					: 0,
						'regnum'			=> isset($aCarInfo['auto'])				? $aCarInfo['auto']				: '',
						'statusGeo'			=> isset($aCarInfo['statusGeo'])		? $aCarInfo['statusGeo']		: false,
						'statusConnection'	=> isset($aCarInfo['statusConnection'])	? $aCarInfo['statusConnection']	: false,
						'geo_lan'			=> isset($val['geo_lan'])				? $val['geo_lan']				: 0,
						'geo_lat'			=> isset($val['geo_lat'])				? $val['geo_lat']				: 0,
						'distance'			=> $nDis
					));
					
				} else {
					if ( ($rs == 2) && !empty($nReg) && !empty($nDis) ) {
						$move		= 0;
						$move		= $oPatruls->getMovesByRegister($nReg);
						$move		+= $nDs;
						$aStory		= array();

						$aStory['id']				= 0;
						$aStory['id_alarm_register']= $nReg;
						$aStory['id_archiv']		= 0;
						$aStory['id_msg']			= 0;
						$aStory['id_sig']			= 0;
						$aStory['alarm_time']		= time();
						$aStory['alarm_name']		= "";
						$aStory['pictogram']		= "";
						$aStory['alarm_status']		= "position";
						$aStory['id_patrul']		= isset($aPat['id'])	? $aPat['id']	: 0;
						$aStory['patrul_num']		= isset($aPat['num'])	? $aPat['num']	: 0;
						$aStory['patrul_geo_lan']	= isset($val['geo_lan'])	? $val['geo_lan']	: 0;
						$aStory['patrul_geo_lat']	= isset($val['geo_lat'])	? $val['geo_lat']	: 0;
						$aStory['patrul_trace']		= $nDs;
						$aStory['patrul_trace_cascade_sum']		= $move;
						$aStory['id_auto']			= $val['id_auto'];
						$aStory['id_reason']		= "";
						$aStory['distance']			= $nDis;

						$oStory->update($aStory);

						$n1 = $aStory['id'];
					}
				}

				// Генерираме събитие към ПЛАТНОТО -> Car Position
				MonitoringEvents::addCmdCar( array(
					'id' 				=> $val['id_auto'],
					'idRegion'			=> $val['id_office'],
					'event_type'		=> 'position',
					'callsign'			=> isset($aPat['num'])					? $aPat['num']					: 0,
					'regnum'			=> isset($aCarInfo['auto'])				? $aCarInfo['auto']				: '',
					'statusGeo'			=> isset($aCarInfo['statusGeo'])		? $aCarInfo['statusGeo']		: false,
					'statusConnection'	=> isset($aCarInfo['statusConnection'])	? $aCarInfo['statusConnection']	: false,
					'geo_lan'			=> isset($val['geo_lan'])				? $val['geo_lan']				: 0,
					'geo_lat'			=> isset($val['geo_lat'])				? $val['geo_lat']				: 0,
					'distance'			=> $nDis
				));
				

				$fArrival	= $oRegister->checkForArrival($nReg);

				if ( ($rs == 2) && ($nDis <= $dis_to_arr) && !empty($nDis) && !$fArrival ) {
					if ( !empty($nReg) ) {
						$move		= 0;
						$move		= $oPatruls->getMovesByRegister($nReg);
						$nAlPatrul	= $oAlPatruls->getPatrulRegister($nReg);

						$aStory						= array();
						$aStory['id']				= 0;
						$aStory['id_alarm_register']= $nReg;
						$aStory['id_archiv']		= 0;
						$aStory['id_msg']			= 0;
						$aStory['id_sig']			= 0;
						$aStory['alarm_time']		= time();
						$aStory['alarm_name']		= "";
						$aStory['pictogram']		= "";
						$aStory['alarm_status']		= "arrival";
						$aStory['id_patrul']		= isset($aPat['id'])		? $aPat['id']	: 0;
						$aStory['patrul_num']		= isset($aPat['num'])		? $aPat['num']	: 0;
						$aStory['patrul_geo_lan']	= isset($val['geo_lan'])	? $val['geo_lan']	: 0;
						$aStory['patrul_geo_lat']	= isset($val['geo_lat'])	? $val['geo_lat']	: 0;
						$aStory['patrul_trace']		= 0; //$nDs;
						$aStory['patrul_trace_cascade_sum']		= $move;
						$aStory['id_auto']			= $val['id_auto'];
						$aStory['id_reason']		= "";
						$aStory['distance']			= $nDis;

						$oStory->update($aStory);

						// Регистрираме пристигането!
						$aAlarm						= array();
						$aAlarm['id']				= $nReg;
						$aAlarm['arrival_time']	= time();

						$oRegister->update($aAlarm);

						$nStat		= $oStat->Signals($nReg);

						if ( !empty($nAlPatrul) ) {
							$aAlarm					= array();
							$aAlarm['id']			= $nAlPatrul;
							$aAlarm['k_vr']			= $nStat;

							$oAlPatruls->update($aAlarm);
						}

						// Генерираме събитие към ПЛАТНОТО -> Car Arrival
						MonitoringEvents::addCmdCar( array(
							'id' 				=> $val['id_auto'],
							'idRegion'			=> $val['id_office'],
							'event_type'		=> 'position',
							'callsign'			=> isset($aPat['num'])					? $aPat['num']					: 0,
							'regnum'			=> isset($aCarInfo['auto'])				? $aCarInfo['auto']				: '',
							'statusGeo'			=> isset($aCarInfo['statusGeo'])		? $aCarInfo['statusGeo']		: false,
							'statusConnection'	=> isset($aCarInfo['statusConnection'])	? $aCarInfo['statusConnection']	: false,
							'geo_lan'			=> isset($val['geo_lan'])				? $val['geo_lan']				: 0,
							'geo_lat'			=> isset($val['geo_lat'])				? $val['geo_lat']				: 0,
							'distance'			=> $nDis
						));
						
					} 
				}
			} 
		}
	

		MonitoringEvents::send();			

		usleep(300);

		if ( file_exists('stop') ) {
			echo "Manual STOP !!!";
			die();
		}
	}


	// Функция, която Претърсва и анонсира автомобил към алармирал обект 	
	function ReactionProcess( &$valObject ){
		global  $oRegister, $oCars, $oObjects, $oPatruls, $oAlPatruls, $oStory, $aAvalCars, $aWaitForMove, $aWaitForCar; 
		
		$nIDObj = $valObject['id_obj'];
		
		// Търсим свободна кола
		$aCurrentCars	= array();
		$aAvalCars		= array();
		$aCurrentCars	= $oCars->getPatrulCar($valObject['id_office']);
		$distance		= 0;
		$nIDRegister	= $oRegister->getCurrentRegisterIDByAlarmObject($nIDObj);

		foreach ( $aCurrentCars as $kCar => $vCar ) {
			if ( $vCar['reaction_status'] == 0 ) {
				$distance = $oObjects->getDistanceByGeo($valObject['geo_lat'], $valObject['geo_lan'], $vCar['geo_lat'], $vCar['geo_lan']);
				$aAvalCars[$kCar] = array("distance" => $distance, "lan" => $valObject['geo_lan'], "lat" => $valObject['geo_lat']);

				$distance	= $distance * 1000;
			}
		}
		
		// Колата е намерена, подаваме сигнал към патрула за реакция!
		// Маркираме колата като заета!
		if ( !empty($aAvalCars) && !empty($nIDRegister) ) {
			asort($aAvalCars); 
			reset($aAvalCars);

			$dDistance	= current($aAvalCars);
			$nIDCar		= key($aAvalCars);

			if ( !empty($nIDCar) ) {
				$aUpdate					= array();
				$aUpdate['id']				= $nIDCar;
				$aUpdate['reaction_status'] = 1;
				$aUpdate['reaction_object'] = $nIDObj;
				$aUpdate['reaction_time']	= time();

				$oCars->update($aUpdate);
			}
			
			$valObject['reaction_status']		= 1;

			if ( !empty($nIDObj) ) {
				$aUpdate					= array();
				$aUpdate['id']				= $nIDObj;
				$aUpdate['reaction_status'] = 2;				// Статус, че сме привърали кола към обекта
				$aUpdate['reaction_car']	= $nIDCar;
				
				$oObjects->update($aUpdate);
			}

			$aPatrul					= $oPatruls->getPatrulByAuto($nIDCar);
			$aTCar						= $oCars->getCarByID($nIDCar);

			$oAlPatruls->delByIDRegister($nIDRegister);

			$aUpdate					= array();
			$aUpdate['id']				= 0;
			$aUpdate['id_alarm_register'] = $nIDRegister;
			$aUpdate['id_road_list']	= isset($aPatrul['id_road_list'])	? $aPatrul['id_road_list']	: 0; 
			$aUpdate['patrul_num']		= isset($aPatrul['num'])			? $aPatrul['num']			: 0;
			$aUpdate['start_geo_lan']	= isset($aTCar['geo_lan'])			? $aTCar['geo_lan']			: 0;
			$aUpdate['start_geo_lat']	= isset($aTCar['geo_lat'])			? $aTCar['geo_lat']			: 0;
			$aUpdate['start_time']		= time();
			$aUpdate['start_distance']	= $distance;

			$oAlPatruls->update($aUpdate);

			// Вкарваме в архива запис за оповестяването
			if ( !empty($nIDCar) ) {
				$distance					= $oObjects->getDistanceByGeo($valObject['geo_lat'], $valObject['geo_lan'], $aTCar['geo_lat'], $aTCar['geo_lan']);
				$distance					= $distance * 1000;

				$aDistance	= array();
				$nPatNum	= isset($aPatrul['num'])	? $aPatrul['num']	: 0;
				$aDistance	= $oPatruls->getLastDistanceByPatrul($nPatNum);
				$dLan		= isset($aDistance['geo_lan'])	? $aDistance['geo_lan']	: 0;
				$dLat		= isset($aDistance['geo_lat'])	? $aDistance['geo_lat']	: 0;
				$nDs		= $oObjects->getDistanceByGeo($dLat, $dLan, $aTCar['geo_lat'], $aTCar['geo_lan']);
				$nDs		= $nDs * 1000;

				if ( $nDs > 10000 ) {
					$nDs = 0;
				}

				$aStory						= array();
				$aStory['id']				= 0;
				$aStory['id_alarm_register']= $nIDRegister;
				$aStory['id_archiv']		= 0;
				$aStory['id_msg']			= 0;
				$aStory['id_sig']			= 0;
				$aStory['alarm_time']		= time();
				$aStory['alarm_name']		= "";
				$aStory['pictogram']		= "";
				$aStory['alarm_status']		= "announced";
				$aStory['id_patrul']		= isset($aPatrul['id'])			? $aPatrul['id']		: 0;
				$aStory['patrul_num']		= isset($aPatrul['num'])		? $aPatrul['num']		: 0;
				$aStory['patrul_geo_lan']	= isset($aTCar['geo_lan'])		? $aTCar['geo_lan']		: 0;
				$aStory['patrul_geo_lat']	= isset($aTCar['geo_lat'])		? $aTCar['geo_lat']		: 0;
				$aStory['patrul_trace']		= 0;
				$aStory['id_auto']			= $nIDCar;
				$aStory['id_reason']		= "";
				$aStory['distance']			= $distance;

				$oStory->update($aStory);

				
				// Генерираме събитие към ПЛАТНОТО -> Car Annonce
				MonitoringEvents::addCmdCar( array(
					'id' 				=> $nIDCar,
					'idRegion'			=> $valObject['id_office'],
					'event_type'		=> 'announce',
					'callsign'			=> isset($aPatrul['num'])				? $aPatrul['num']				: 0,
					'regnum'			=> isset($aTCar['auto'])				? $aTCar['auto']				: '',
					'statusGeo'			=> isset($aTCar['statusGeo'])			? $aTCar['statusGeo']			: false,
					'statusConnection'	=> isset($aTCar['statusConnection'])	? $aTCar['statusConnection']	: false,
					'geo_lan'			=> isset($aTCar['geo_lan'])				? $aTCar['geo_lan']				: 0,
					'geo_lat'			=> isset($aTCar['geo_lat'])				? $aTCar['geo_lat']				: 0,
					'distance'			=> $distance,
					'idObject'			=> $nIDObj
				));
				
				// Регистрираме колата за следене дали се мести
				$aWaitForMove[$nIDCar] = $distance;
			}

			if ( !empty($nIDRegister) ) {
				$aAlarm						= array();
				$aAlarm['id']				= $nIDRegister;
				$aAlarm['patruls']			= isset($aPatrul['num'])	? $aPatrul['num']	: 0;
				$aAlarm['announce_time']	= time();

				$oRegister->update($aAlarm);
			}
		} else {
			// Не е намерена кола, регитрираме обекта за следене!!!
			$aWaitForCar[$nIDObj] = $nIDObj;			
		}
		
	}


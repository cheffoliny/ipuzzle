<?php
	if ( !isset($_SESSION) ) {
		session_start();
	}
	
	//$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
	//set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../');
	set_include_path( get_include_path().PATH_SEPARATOR.'/www/isu/');	

	require_once ("../config/function.autoload.php");
	require_once ("../config/connect.inc.php");
	require_once ("../include/general.inc.php");
	require_once ("../include/classes.inc.php");

	$oCars		= new DBCars();
	$oObjects	= new DBObjects();
	$oSignals	= new DBSignals();
	$oStory		= new DBStory();
	$oRegister	= new DBAlarmRegister();
	$oPatruls	= new DBPatruls();
	$oAlPatruls	= new DBAlarmPatruls();
	$oSettings	= new DBFiltersEvents();
	$oStat		= new DBSettingsStat();
	$oPid		= new pidfile("", "cls");

	$aCars		= array();
	$aObjects	= array();
	$aSignals	= array();
	$aStory		= array();
	$aSettings	= array();
	$nArchPos	= 0;

	$aObjStat	= array();
	$aCarStat	= array();

	$aSettings	= $oSettings->getSettings();

	$sig1		= isset($aSettings[0]['reactions'])				? $aSettings[0]['reactions']			: "";
	$sig2		= isset($aSettings[0]['reactions_down'])		? $aSettings[0]['reactions_down']		: "";
	$al1		= isset($aSettings[0]['alarm_after'])			? $aSettings[0]['alarm_after']			: 0;
	$al2		= isset($aSettings[0]['down_after_alarm'])		? $aSettings[0]['down_after_alarm']		: 0;
	$wait		= isset($aSettings[0]['wait_time'])				? $aSettings[0]['wait_time']			: 0;
	$max_time	= isset($aSettings[0]['max_time_react'])		? $aSettings[0]['max_time_react']		: 0;
	$dis_to_arr	= isset($aSettings[0]['distance_to_arrival'])	? $aSettings[0]['distance_to_arrival']	: 50;

	$aAutoCo	= array();

	set_time_limit(0);
	//$oSignals->setDebug( true );
	$pos		= $oSignals->getMax();

	$_SESSION['arch_pos'] = $pos;

	$aAlStat		= array();
	$aAlStat		= explode(",", $sig1);

	$aOpClose		= array();
	$aOpClose		= explode(",", $sig2);

	$aWaitForMove	= array();
	$aWaitForCar	= array();
	
	$response		= new WS_Response( 'localhost', 9999, 'cbr99' );
	$nArchPos		= isset($_SESSION['arch_pos']) ? $_SESSION['arch_pos'] : -1;

	$timer			= time();
	$timerFilter	= time();

	while ( true ) {	
		// Таймер - една минута, след което презарежда настройките!
		if ( (time() - $timer) >= 60 ) {
			$timer		= time();
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

		// **************************************************************
		// Синхронизация с реакция през PowerLink!!!
		$aAlarmObjects	= array();
		$aAlarmObjects	= $oObjects->getAlarmObjects();

		foreach ( $aAlarmObjects as $nKey => $aVal ) {
			$nIDRegister	= $oRegister->getCurrentRegisterIDByAlarmObject($nKey);
			$aPatInfo		= $oAlPatruls->getByRegister($nIDRegister);
			$aObjVal		= $oObjects->getObjectsByID($nKey);
			$nIDWCM			= 0;

			if ( isset($aPatInfo['id_work_card_movement']) && !empty($aPatInfo['id_work_card_movement']) ) {
				$aWC	= array();
				$aWC	= $oObjects->getReactionObjectFromPowerLinkByID($aPatInfo['id_work_card_movement']);

				if ( isset($aWC['id_alarm_reasons']) && !empty($aWC['id_alarm_reasons']) ) {
					// Алармата е приключила!!!
					$nIDWCM = $aWC['id_alarm_reasons'];
					$oPatruls->closeAlarm($nIDRegister, $nIDWCM);
				}

				continue;
			}

			$nIDOldObj		= isset($aObjVal['id_oldobj'])		? $aObjVal['id_oldobj']		: 0;
			$nIDOldObjCar	= isset($aObjVal['reaction_car'])	? $aObjVal['reaction_car']	: 0;
			$nIDOldObjOff	= isset($aObjVal['id_office'])		? $aObjVal['id_office']		: 0;

			$aTmp			= array();
			$aTmp			= $oObjects->getReactionObjectFromPowerLink($nIDOldObj);

			$nIDLinkPatrul	= isset($aTmp['id_patrul']) ? $aTmp['id_patrul'] : 0;

			if ( !empty($nIDLinkPatrul) && is_numeric($nIDLinkPatrul) ) {
				$aLinkCar = $oCars->getCarByIDPatrul($nIDLinkPatrul);
				$nLinkCar = isset($aLinkCar['id']) ? $aLinkCar['id'] : 0;

				if ( !empty($nLinkCar) ) {
					if ( !empty($nIDOldObjCar) && ($nLinkCar != $nIDOldObjCar) ) {
						$aUpdate					= array();
						$aUpdate['id']				= $nIDOldObjCar;
						$aUpdate['reaction_status'] = 0;
						$aUpdate['reaction_object'] = 0;
						$aUpdate['reaction_time']	= time();

						$oCars->update($aUpdate);

						$cmd = new Plane_Message_Car_Cancel(1, $nIDOldObjOff, $nLinkCar);
						addCmd($cmd, $byRegion, $nIDOldObjOff);
					}

					// Проверка дали колата е свободна:
					if ( $aLinkCar['reaction_status'] != 0 ) {
						// Освобождаваме колата!!!
						if ( !empty($nLinkCar) ) {
							$aUpdate					= array();
							$aUpdate['id']				= $nLinkCar;
							$aUpdate['reaction_status'] = 3;
							$aUpdate['reaction_object'] = 0;
							$aUpdate['reaction_time']	= time();

							$oCars->update($aUpdate);
						}

						if ( !empty($aLinkCar['reaction_object']) ) {
							$aUpdate					= array();
							$aUpdate['id']				= $aLinkCar['reaction_object'];
							$aUpdate['reaction_status'] = 1;
							$aUpdate['reaction_car']	= 0;

							$oObjects->update($aUpdate);
						}

						if ( $aLinkCar['reaction_status'] == 2 ) {
							$cmd = new Plane_Message_Car_Report(1, $aLinkCar['id_office'], $aLinkCar['id'], 21);
						} else {
							$cmd = new Plane_Message_Car_Cancel(1, $aLinkCar['id_office'], $aLinkCar['id']);
						}

						addCmd($cmd, $byRegion, $aLinkCar['id_office']);
					}


					// Анонсирам колата:
					if ( !empty($nIDRegister) ) {
						if ( !empty($nLinkCar) ) {
							$aUpdate					= array();
							$aUpdate['id']				= $nLinkCar;
							$aUpdate['reaction_status'] = 2;
							$aUpdate['reaction_object'] = $nKey;
							$aUpdate['reaction_time']	= time();

							$oCars->update($aUpdate);
						}

						if ( !empty($nKey) ) {
							$aUpdate					= array();
							$aUpdate['id']				= $nKey;
							$aUpdate['reaction_status'] = 2;
							$aUpdate['reaction_car']	= $nLinkCar;
							
							$oObjects->update($aUpdate);
						}	
						
						$oAlPatruls->delByIDRegister($nIDRegister);

						// Вкарваме в архива запис за оповестяването
						if ( !empty($aLinkCar['id']) ) {
							$distance					= $oObjects->getDistanceByGeo($aObjVal['geo_lan'], $aObjVal['geo_lat'], $aLinkCar['geo_lan'], $aLinkCar['geo_lat']);
							$distance					= $distance * 1000;

							$aUpdate						= array();
							$aUpdate['id']					= 0;
							$aUpdate['id_alarm_register']	= $nIDRegister;
							$aUpdate['id_road_list']		= $aLinkCar['id_road_list'];
							$aUpdate['id_work_card_movement'] = isset($aTmp['id']) ? $aTmp['id'] : 0;
							$aUpdate['patrul_num']			= $aLinkCar['num'];
							$aUpdate['start_geo_lan']		= $aLinkCar['geo_lan'];
							$aUpdate['start_geo_lat']		= $aLinkCar['geo_lat'];
							$aUpdate['start_time']			= time();
							$aUpdate['start_distance']		= $distance;

							$oAlPatruls->update($aUpdate);

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
							$aStory['id_patrul']		= $nIDLinkPatrul;
							$aStory['patrul_num']		= $aLinkCar['num'];
							$aStory['patrul_geo_lan']	= $aLinkCar['geo_lan'];
							$aStory['patrul_geo_lat']	= $aLinkCar['geo_lat'];
							$aStory['patrul_trace']		= 0;
							$aStory['id_auto']			= $aLinkCar['id'];
							$aStory['id_reason']		= 1;
							$aStory['distance']			= $distance;

							$oStory->update($aStory);

							$n1 = $aStory['id'];
							
							$aStory						= array();
							$aStory['id']				= 0;
							$aStory['id_alarm_register']= $nIDRegister;
							$aStory['id_archiv']		= 0;
							$aStory['id_msg']			= 0;
							$aStory['id_sig']			= 0;
							$aStory['alarm_time']		= time();
							$aStory['alarm_name']		= "";
							$aStory['pictogram']		= "";
							$aStory['alarm_status']		= "start";
							$aStory['id_patrul']		= $nIDLinkPatrul;
							$aStory['patrul_num']		= $aLinkCar['num'];
							$aStory['patrul_geo_lan']	= $aLinkCar['geo_lan'];
							$aStory['patrul_geo_lat']	= $aLinkCar['geo_lat'];
							$aStory['patrul_trace']		= 0;
							$aStory['id_auto']			= $aLinkCar['id'];
							$aStory['id_reason']		= 1;
							$aStory['distance']			= $distance;

							$oStory->update($aStory);

							$n2		= $aStory['id'];
							$fAuto	= $oCars->checkForGPS($aLinkCar['id']);

							if ( !$fAuto ) {
								// Добавяме запис в историята
								$aStory							= array();
								$aStory['id']					= 0;
								$aStory['id_alarm_register']	= $nIDRegister;
								$aStory['id_archiv']			= 0;
								$aStory['id_msg']				= 0;
								$aStory['id_sig']				= 0;
								$aStory['alarm_time']			= time();
								$aStory['alarm_name']			= "";
								$aStory['pictogram']			= "";
								$aStory['alarm_status']			= "gps_failure";
								$aStory['id_patrul']			= $nIDLinkPatrul;
								$aStory['patrul_num']			= $aLinkCar['num'];
								$aStory['patrul_geo_lan']		= $aLinkCar['geo_lan'];
								$aStory['patrul_geo_lat']		= $aLinkCar['geo_lat'];
								$aStory['patrul_trace']			= 0;
								$aStory['id_auto']				= $aLinkCar['id'];
								$aStory['id_reason']			= 0;
								$aStory['distance']				= 0;

								$oStory->update($aStory);
							}

							$cmd = new Plane_Message_Car_Notify($n1, $aLinkCar['id_office'], $aLinkCar['id'], $nKey, $distance);
							addCmd( $cmd, $byRegion, $aLinkCar['id_office'] );

							$cmd = new Plane_Message_Car_Action($n2, $aLinkCar['id_office'], $aLinkCar['id']);
							addCmd( $cmd, $byRegion, $aLinkCar['id_office'] );
						}

						if ( !empty($nIDRegister) ) {
							$aAlarm						= array();
							$aAlarm['id']				= $nIDRegister;
							$aAlarm['patruls']			= $aLinkCar['num'];
							$aAlarm['announce_time']	= time();

							$oRegister->update($aAlarm);
						}
					}
					//}
				}
			}
		}
		// **************************************************************

		// Аларми без реакция!!!
		$aReactions		= array();
		$aReactions		= $oRegister->getAlarmsWithOutReaction($max_time);

		foreach ( $aReactions as $nReactClose ) {
			$nIDReactClose	= isset($nReactClose['id']) ? $nReactClose['id'] : 0;
			$oPatruls->closeAlarm($nIDReactClose, 21);
		}

		$aCars			= $oCars->getCurrentPosition();

		if ( $nArchPos >= 0 ) {
			$aObjects	= $oSignals->getSignalsAfterId($nArchPos);
		} else {
			$aObjects	= array(); //$oObjects->getObjectStatuses($sig1, $sig2, 60);
		}

		$byRegion	= array();

		// Търсене на кола за реакция
		$aObjWithOutReaction	= $oObjects->getObjectsWithOutReaction();

		foreach ($aObjWithOutReaction as $nIDObj => $valObject) {
			// Търсим свободна кола
			$aCurrentCars	= array();
			$aAvalCars		= array();
			$aCurrentCars	= $oCars->getPatrulCar($valObject['id_office']);
			$distance		= 0;
			//$nIDObj			= isset($valObject['id']) ? $valObject['id'] : 0;
			$nIDRegister	= $oRegister->getCurrentRegisterIDByAlarmObject($nIDObj);

			foreach ( $aCurrentCars as $kCar => $vCar ) {
				if ( $vCar['reaction_status'] == 0 ) {
					$distance = $oObjects->getDistanceByGeo($valObject['geo_lan'], $valObject['geo_lat'], $vCar['geo_lan'], $vCar['geo_lat']);
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

				if ( !empty($nIDObj) ) {
					$aUpdate					= array();
					$aUpdate['id']				= $nIDObj;
					$aUpdate['reaction_status'] = 2;
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
					$distance					= $oObjects->getDistanceByGeo($valObject['geo_lan'], $valObject['geo_lat'], $aTCar['geo_lan'], $aTCar['geo_lat']);
					$distance					= $distance * 1000;

					$aDistance	= array();
					$nPatNum	= isset($aPatrul['num'])	? $aPatrul['num']	: 0;
					$aDistance	= $oPatruls->getLastDistanceByPatrul($nPatNum);
					$dLan		= isset($aDistance['geo_lan'])	? $aDistance['geo_lan']	: 0;
					$dLat		= isset($aDistance['geo_lat'])	? $aDistance['geo_lat']	: 0;
					$nDs		= $oObjects->getDistanceByGeo($dLan, $dLat, $aTCar['geo_lan'], $aTCar['geo_lat']);
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

					$cmd = new Plane_Message_Car_Notify($aStory['id'], $valObject['id_office'], $nIDCar, $nIDObj, $distance ); //$aStory['id']
					addCmd( $cmd, $byRegion, $valObject['id_office'] );
				}

				if ( !empty($nIDRegister) ) {
					$aAlarm						= array();
					$aAlarm['id']				= $nIDRegister;
					$aAlarm['patruls']			= isset($aPatrul['num'])	? $aPatrul['num']	: 0;
					$aAlarm['announce_time']	= time();

					$oRegister->update($aAlarm);
				}
			}
		}

		foreach ( $aObjects as $key => &$val ) {
			$_SESSION['arch_pos']	= $val['id_archiv'];
			$nArchPos				= $val['id_archiv'];
			$nIDObj					= isset($val['id_obj']) && is_numeric($val['id_obj']) ? $val['id_obj'] : 0;
			$nIDRegister			= $oRegister->getCurrentRegisterIDByAlarmObject($nIDObj);
			$fl						= true;

			// Сигнали без регистър
			if ( empty($nIDRegister) ) {

				// Аларма. Нова аларма! 
				if ( in_array($val['id_sig'], $aAlStat) && ($val['alarm'] == 1) && !empty($nIDObj) ) {
					// Регистрираме алармата!
					$aAlarm					= array();
					$aAlarm['id']			= 0;
					$aAlarm['id_object']	= $nIDObj;
					$aAlarm['id_work_card'] = (int) $oRegister->getWorkCardByOffice($val['id_office']);
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

					$object = new Plane_Item_Object($nIDObj, $val['id_office'], new Plane_Item_Position($val['geo_lan'], $val['geo_lat']) );
					$info	= new Plane_Item_Object_Info($val['object_name'], $val['object_num'],  $val['object_address'], 'Place', $val['time_limit']);
					$object->setInfo( $info );
					$cmd	= new Plane_Message_Object_Alarm($aStory['id'], $val['id_office'], $object, $val['id_sig'], $val['message'], time());

					addCmd( $cmd, $byRegion, $val['id_office'] );
					
					// Търсим свободна кола
					$aCurrentCars	= array();
					$aAvalCars		= array();
					$aCurrentCars	= $oCars->getPatrulCar($val['id_office']);
					$distance		= 0;

					foreach ( $aCurrentCars as $kCar => $vCar ) {
						if ( $vCar['reaction_status'] == 0 ) {
							$distance = $oObjects->getDistanceByGeo($val['geo_lan'], $val['geo_lat'], $vCar['geo_lan'], $vCar['geo_lat']);
							$aAvalCars[$kCar] = array("distance" => $distance, "lan" => $val['geo_lan'], "lat" => $val['geo_lat']);

							$distance	= $distance * 1000;
						}
					}

					// Колата е намерена, подаваме сигнал към патрула за реакция!
					// Маркираме колата като заета!
					if ( !empty($aAvalCars) ) {
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

						$val['reaction_status']		= 1;

						if ( !empty($nIDObj) ) {
							$aUpdate					= array();
							$aUpdate['id']				= $nIDObj;
							$aUpdate['reaction_status'] = 2;
							$aUpdate['reaction_car']	= $nIDCar;
							
							$oObjects->update($aUpdate);
						}

						$aPatrul					= $oPatruls->getPatrulByAuto($nIDCar);
						$aTCar						= $oCars->getCarByID($nIDCar);

						if ( !empty($nIDRegister) ) {
							$aUpdate					= array();
							$aUpdate['id']				= $nIDRegister;
							$aUpdate['patruls']			= isset($aPatrul['num'])	? $aPatrul['num']	: 0;
							$aUpdate['announce_time']	= time();
							
							$oRegister->update($aUpdate);
						}

						$oAlPatruls->delByIDRegister($nIDRegister);

						$aUpdate					= array();
						$aUpdate['id']				= 0;
						$aUpdate['id_alarm_register'] = $nIDRegister;
						$aUpdate['patrul_num']		= isset($aPatrul['num'])			? $aPatrul['num']			: 0;
						$aUpdate['id_road_list']	= isset($aPatrul['id_road_list'])	? $aPatrul['id_road_list']	: 0; 
						$aUpdate['start_geo_lan']	= isset($aTCar['geo_lan'])			? $aTCar['geo_lan']			: 0;
						$aUpdate['start_geo_lat']	= isset($aTCar['geo_lat'])			? $aTCar['geo_lat']			: 0;
						$aUpdate['start_time']		= time();
						$aUpdate['start_distance']	= $distance;

						$oAlPatruls->update($aUpdate);

						// Вкарваме в архива запис за оповестяването
						if ( !empty($nIDCar) ) {
							$aTCar						= $oCars->getCarByID($nIDCar);
							$distance					= $oObjects->getDistanceByGeo($val['geo_lan'], $val['geo_lat'], $aTCar['geo_lan'], $aTCar['geo_lat']);
							$distance					= $distance * 1000;

							$aDistance	= array();
							$nPatNum	= isset($aPatrul['num'])	? $aPatrul['num']	: 0;
							$aDistance	= $oPatruls->getLastDistanceByPatrul($nPatNum);
							$dLan		= isset($aDistance['geo_lan'])	? $aDistance['geo_lan']	: 0;
							$dLat		= isset($aDistance['geo_lat'])	? $aDistance['geo_lat']	: 0;
							$nDs		= $oObjects->getDistanceByGeo($dLan, $dLat, $aTCar['geo_lan'], $aTCar['geo_lat']);
							$nDs		= $nDs * 1000;

							if ( $nDs > 10000 ) {
								$nDs = 0;
							}

							$aStory						= array();
							$aStory['id']				= 0;
							$aStory['id_alarm_register']= $nIDRegister;
							$aStory['id_archiv']		= $val['id_archiv'];
							$aStory['id_msg']			= $val['id_msg'];
							$aStory['id_sig']			= $val['id_sig'];
							$aStory['alarm_time']		= time();
							$aStory['alarm_name']		= $val['message'];
							$aStory['pictogram']		= "";
							$aStory['alarm_status']		= "announced";
							$aStory['id_patrul']		= isset($aPatrul['id'])		? $aPatrul['id']	: 0;
							$aStory['patrul_num']		= isset($aPatrul['num'])	? $aPatrul['num']	: 0;
							$aStory['patrul_geo_lan']	= isset($aTCar['geo_lan'])	? $aTCar['geo_lan']	: 0;
							$aStory['patrul_geo_lat']	= isset($aTCar['geo_lat'])	? $aTCar['geo_lat']	: 0;
							$aStory['patrul_trace']		= 0;
							$aStory['id_auto']			= $nIDCar;
							$aStory['id_reason']		= "";
							$aStory['distance']			= $distance;

							$oStory->update($aStory);

							$cmd = new Plane_Message_Car_Notify($aStory['id'], $val['id_office'], $nIDCar, $nIDObj, $distance ); //$aStory['id']
							addCmd( $cmd, $byRegion, $val['id_office'] );
						}

						//$aAlarm						= array();
						//$aAlarm['id']				= $nIDRegister;
						//$aAlarm['patruls']			= isset($aPatrul['num'])	? $aPatrul['num']	: 0;

						//$oRegister->update($aAlarm);

						// Регистрираме колата за следене дали се мести
						$aWaitForMove[$nIDCar] = $distance;

					} else {
						// Не е намерена кола, регитрираме обекта за следене!!!

						$aWaitForCar[$nIDObj] = $nIDObj;
					}
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
						
						$distance	= $oObjects->getDistanceByGeo($val['geo_lan'], $val['geo_lat'], $aTempCar['geo_lan'], $aTempCar['geo_lat']);
						$distance	= $distance * 1000;
					}

					if ( $status != "ccccancel" ) {
						$aTCar	= array();

						if ( $status == "cancel" ) {
							$status = "update";
						}

						if ( !empty($nIDCar) ) {
							$aTCar						= $oCars->getCarByID($nIDCar);
							$distance					= $oObjects->getDistanceByGeo($val['geo_lan'], $val['geo_lat'], $aTCar['geo_lan'], $aTCar['geo_lat']);
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
							$nDs		= $oObjects->getDistanceByGeo($dLan, $dLat, $aTCar['geo_lan'], $aTCar['geo_lat']);
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
					
						$object = new Plane_Item_Object($nIDObj, $val['id_office'], new Plane_Item_Position($val['geo_lan'], $val['geo_lat']) );
						$info	= new Plane_Item_Object_Info($val['object_name'], $val['object_num'],  $val['object_address'], 'Place', $val['time_limit']);
						$object->setInfo( $info );
						$cmd	= new Plane_Message_Object_Alarm($aStory['id'], $val['id_office'], $object, $val['id_sig'], $val['message'], time());

						addCmd( $cmd, $byRegion, $val['id_office'] );
					}

					// Проверка за снемане от охрана
					if ( in_array($val['id_sig'], $aOpClose) ) {
						// Имаме снемане, прекратяваме действията!!!
						$nAlTime = $oRegister->getTimeByRegister($nIDRegister);

						if ( !empty($nAlTime) && ((time() - $nAlTime) <= $al2) ) {
							$status	= "cancel";
							$oPatruls->closeAlarm($nIDRegister, 20);
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
						$distance	= $oObjects->getDistanceByGeo($val['geo_lan'], $val['geo_lat'], $aTempCar['geo_lan'], $aTempCar['geo_lat']);
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
						$nDs		= $oObjects->getDistanceByGeo($dLan, $dLat, $aTempCar['geo_lan'], $aTempCar['geo_lat']);
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

					$object = new Plane_Item_Object($nIDObj, $val['id_office'], new Plane_Item_Position($val['geo_lan'], $val['geo_lat']) );
					$info	= new Plane_Item_Object_Info($val['object_name'], $val['object_num'],  $val['object_address'], 'Place', $val['time_limit']);
					$object->setInfo( $info );
					$cmd	= new Plane_Message_Object_Alarm($aStory['id'], $val['id_office'], $object, $val['id_sig'], $val['message'], time());

					addCmd( $cmd, $byRegion, $val['id_office'] );

				}

			}
		}

		unset($val);

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
			$nDs		= $oObjects->getDistanceByGeo($dLan, $dLat, $val['geo_lan'], $val['geo_lat']);
			$nDs		= $nDs * 1000;

			if ( $nDs > 10000 ) {
				$nDs = 0;
			}

			if ( isset($aObj['geo_lan']) && isset($aObj['geo_lan']) ) {
				$nDis = $oObjects->getDistanceByGeo($val['geo_lan'], $val['geo_lat'], $aObj['geo_lan'], $aObj['geo_lat']);
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

					//$cmd = new Plane_Message_Car_Report(0, $val['id_office'], $val['id_auto'], "Stop!");
					$cmd = new Plane_Message_Car_Cancel($aStory['id'], $val['id_office'], $val['id_auto']);
					addCmd($cmd, $byRegion, $val['id_office']);
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
					$nDis = $oObjects->getDistanceByGeo($val['geo_lan'], $val['geo_lat'], $aObj['geo_lan'], $aObj['geo_lat']);
					$nDis *= 1000;
 				}

				//$cmd = new Plane_Message_Car_Move(0, $val['id_office'], $val['id_auto'], new Plane_Item_Position($val['geo_lan'], $val['geo_lat']), $nDis);
				//addCmd( $cmd, $byRegion, $val['id_office'] );

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

					$cmd = new Plane_Message_Car_Action($aStory['id'], $val['id_office'], $val['id_auto'] );
					addCmd( $cmd, $byRegion, $val['id_office'] );
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

				$cmd = new Plane_Message_Car_Move($n1, $val['id_office'], $val['id_auto'], new Plane_Item_Position($val['geo_lan'], $val['geo_lat']), $nDis);
				addCmd( $cmd, $byRegion, $val['id_office'] );

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

						$cmd = new Plane_Message_Car_Target($aStory['id'], $val['id_office'], $val['id_auto']);
						addCmd( $cmd, $byRegion, $val['id_office'] );
					} 
				}
			} 
		}
	

		foreach ( $byRegion as $key => $val ) {
			if ( empty($val) ) {
				continue;
			}
			
			//echo "\n".date("d.m.Y H:m:s").ArrayToString($val)."\n";

			$activity = new WS_Cmd_RegionActivity($key);
			$activity->setResult($val);
			
			if ( !$response->send($activity) ) {
/*
				$is = 1;
				$fl	= false;

				while ( $is <=5 ) {
					usleep(100);

					if( $response->send($activity) ) {
						$fl = true;
						break;
					}

					$is++;
				}

				if ( !$fl ) {
					$response   = new WS_Response( 'localhost', 9999, 'cbr99' );

					echo "Nov opit\n";
					$response->send($activity);
				}
*/
				echo "\n".date("d.m.Y H:m:s")." Cant send to server!\n";
				die();
			}
		}

		
		$byRegion = array();
			

		usleep(300);

		if ( file_exists('stop') ) {
			echo "Umirame slavno....";
			die();
		}
	}

	function addCmd( $cmd, &$target, $id_office ) {
		if ( !array_key_exists($id_office, $target) ) {
			$target[ $id_office ] = array();
		}
		
		$target[ $id_office ][] = $cmd;
	}

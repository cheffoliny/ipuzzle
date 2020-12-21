<?php
	class DBSODPatruls
		extends DBBase2 {
			
		public function __construct() {
			global $db_sod;
			//$db_sod->debug=true;
			
			parent::__construct($db_sod, 'patruls');
		}
				
		public function getPatrulByNum( $nIDPatrul ) {
			global $db_name_auto, $db_name_sod, $db_auto;

			if ( empty($nIDPatrul) || !is_numeric($nIDPatrul) ) {
				return array();
			}

			$sQuery = "
				SELECT 
					p.id,
					r.id as id_road_list,
					r.id_office
				FROM {$db_name_auto}.road_lists r
				LEFT JOIN {$db_name_sod}.patruls p ON p.id = r.id_patrul 
				WHERE p.num_patrul =  {$nIDPatrul} 		
					AND UNIX_TIMESTAMP(r.end_time) = 0
				ORDER BY id DESC
				LIMIT 1				
			";
			
			return $this->selectOnce( $sQuery );
		}

		public function getPatrulByAuto( $nIDAuto ) {
			global $db_name_auto, $db_name_sod;

			if ( empty($nIDAuto) || !is_numeric($nIDAuto) ) {
				return array();
			}

			$sQuery = "
				SELECT 
					p.id,
					r.id as id_road_list,
					p.num_patrul as num
				FROM {$db_name_auto}.road_lists r
				LEFT JOIN {$db_name_sod}.patruls p ON p.id = r.id_patrul 
				WHERE r.id_auto = {$nIDAuto} 		
					AND UNIX_TIMESTAMP(r.end_time) = 0
				ORDER BY id DESC
				LIMIT 1
				
			";

			$aPatrul = $this->selectOnce( $sQuery );

			if ( !empty($aPatrul) ) {
				return $aPatrul;
			} else {
				return array();
			}
		}

		public function getPartrul($nIDAuto){
			global $db_name_auto, $db_name_sod;
			
			if ( !is_numeric($nIDAuto) ) {
				return array();
			}
			
			$query = 	"	SELECT 
								p.id,
								r.id_auto,
								r.id as id_road_list,
								p.num_patrul,
								a.geo_lan,
								a.geo_lat,
								a.reg_num,
								r.id_office,
								a.phone,
								a.reaction_status,
								a.reaction_object
								
							FROM {$db_name_auto}.road_lists r
							LEFT JOIN {$db_name_auto}.auto a ON a.id = r.id_auto
							LEFT JOIN {$db_name_sod}.patruls p ON p.id = r.id_patrul 
							WHERE
									
						";
		}

		public function getPatrulsByOffice( $nIDOffice, $busyPatruls = "" ) {
			global $db_name_auto, $db_name_sod;

			if ( empty($nIDOffice) || !is_numeric($nIDOffice) ) {
				return array();
			}

			$sQuery = "
				SELECT 
					p.id,
					r.id_auto,
					r.id as id_road_list,
					p.num_patrul,
					a.geo_lan,
					a.geo_lat,
					a.reg_num,
					r.id_office,
					a.phone,
					a.reaction_status,
					a.reaction_object
					
				FROM {$db_name_auto}.road_lists r
				LEFT JOIN {$db_name_auto}.auto a ON a.id = r.id_auto
				LEFT JOIN {$db_name_sod}.patruls p ON p.id = r.id_patrul 
				WHERE 1
					AND UNIX_TIMESTAMP(r.end_time) = 0
					AND r.id_office = {$nIDOffice}
					AND DATEDIFF( NOW(), a.geo_time ) < 60 	
					AND a.geo_lan > 0
			";
			
			if ( !empty($busyPatruls) ) {
				$sQuery .= "AND p.id NOT IN ( $busyPatruls )"; 
			}
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getNumByIDOffice($nIDOffice) {
			global $db_name_sod;
			
			if ( empty($nIDOffice) || !is_numeric($nIDOffice) ) {
				return array();
			}			
			
			$sQuery = "
				SELECT
					id,
					num_patrul as patrul
				FROM {$db_name_sod}.patruls
				WHERE id_office = {$nIDOffice}
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getAllPatruls()	{
			
			$sQuery = " 
				SELECT 
					group_concat( p.num_patrul) as patruls
				FROM patruls p		
			";
			return $this->selectOnce( $sQuery );
		}
		
		public function getFreePatruls()	{
			
			$sQuery = " 
				SELECT 
					group_concat( p.num_patrul) as patruls
				FROM patruls p
				WHERE p.id_office = 0 				
			";
			return $this->selectOnce( $sQuery );
		}
		
		public function checkFreePatrul( $nNum )
		{
			if( empty( $nNum ) || !is_numeric( $nNum ) )
				return array();
			
			$sQuery = "
				SELECT *
				FROM patruls
				WHERE num_patrul = {$nNum}
				";
				
			$aData = $this->select( $sQuery );
			
			return empty( $aData );
		}
		public function getIDByNumPatrul($nID)
		{
			$sQuery = "
				SELECT 
					id
				FROM 
					patruls
				WHERE  
					num_patrul = {$nID}

					";
				
			return $this->selectOne($sQuery);	
		}
		
		public function getNumByID($nID) {
			$sQuery = "
				SELECT 
					num_patrul
				FROM 
					patruls
				WHERE  
					id = {$nID}
			";
				
			return $this->selectOne($sQuery);	
		}

		public function addCmd( $cmd, &$target, $id_office ){
			if( !array_key_exists( $id_office, $target ) ){
				$target[ $id_office ] = array();
			}
			
			$target[ $id_office ][] = $cmd;
		}

		public function getLastDistanceByPatrul($nPatrul) {
			global $db_name_sod;
			
			if ( empty($nPatrul) || !is_numeric($nPatrul) ) {
				return array();
			}			
			
			$sQuery = "
				SELECT
					ah.patrul_geo_lan as geo_lan,
					ah.patrul_geo_lat as geo_lat,
					ap.start_geo_lan,
					ap.start_geo_lat
				FROM {$db_name_sod}.alarm_patruls ap
				LEFT JOIN {$db_name_sod}.alarm_register ar ON ( ar.id = ap.id_alarm_register )
				LEFT JOIN {$db_name_sod}.alarm_history ah ON ( ah.id_alarm_register = ap.id_alarm_register )
				WHERE ah.patrul_num = {$nPatrul}
					AND ar.status = 'active'
				ORDER BY ah.id DESC
				LIMIT 1
			";
/*
				SELECT
					ah.patrul_geo_lan as geo_lan,
					ah.patrul_geo_lat as geo_lat
				FROM {$db_name_sod}.alarm_register ar
				LEFT JOIN {$db_name_sod}.alarm_history ah ON ( ah.id_alarm_register = ar.id )
				WHERE {$nPatrul} = ar.patruls 
					AND ar.status = 'active'
				ORDER BY ah.id DESC
				LIMIT 1
*/				

			return $this->selectOnce($sQuery);
		}


        public function getRealDistance($olat, $olan, $dlat, $dlan) {
            $origin_lat = str_replace(',', '.', $olat);
            $origin_lan = str_replace(',', '.', $olan);
            $dest_lat 	= str_replace(',', '.', $dlat);
            $dest_lan 	= str_replace(',', '.', $dlan);

            $origin_lat = floatval($origin_lat);
            $origin_lan = floatval($origin_lan);
            $dest_lat	= floatval($dest_lat);
            $dest_lan 	= floatval($dest_lan);

            $url 		= "http://maps.googleapis.com/maps/api/distancematrix/json?origins={$origin_lat}+{$origin_lan}&destinations={$dest_lat}+{$dest_lan}&mode=driving&language=bg-BG&sensor=false";
            $json 		= file_get_contents($url);
            $distance	= 0;
            //$status		= "FAIL";

            if (false !== $json) {
                $result = json_decode($json, true);

                if ( isset($result['status']) && $result['status'] == "OK" ) {
                    //$status		= "OK";
                    $distance 	= intval($result['rows'][0]['elements'][0]['distance']['value']);

                    /*
                    foreach($result->rows[0]->elements as $road) {
                        $time 		+= $road->duration->value;
                        $distance 	+= $road->distance->value;
                    }
                    */
                }
                //var_dump($result);
            }

            return $distance;
        }

		public function getMovesByRegister($nIDRegister) {
			global $db_name_sod;

			if ( empty($nIDRegister) || !is_numeric($nIDRegister) ) {
				return 0;
			}

			$sQuery = "
				SELECT 
					SUM(patrul_trace) as moves
				FROM {$db_name_sod}.alarm_history 
				WHERE id_alarm_register = {$nIDRegister}
					AND alarm_status IN ('start', 'position', 'arrival')
			";

			return $this->selectOne($sQuery);
		}

		public function closeAlarm($nIDRegister, $nIDReason, $sType = "reason") {
			global $db_name_auto, $db_name_sod, $db_sod;
			
			if ( empty($nIDReason) || !is_numeric($nIDReason) ) {
				return;
			}

			if ( empty($nIDRegister) || !is_numeric($nIDRegister) ) {
				return;
			}

			if ( $sType == "refusal" ) {
				$sType = "refusal_car";
			} else {
				$sType = "reason";
			}

			$oCars		= new DBCars();
			$oObjects	= new DBObjects();
			$oRegister	= new DBAlarmRegister();
			$oAlPat		= new DBAlarmPatruls();
			$oStory		= new DBStory();
			$oStat		= new DBSettingsStat();
			$oReasons	= new DBAlarmReasons();

			$nIDAuto	= $oAlPat->getIDPatrulByRegister($nIDRegister);
			$nIDObject	= $oRegister->getIDObjectByRegister($nIDRegister);
			$nIDOffice	= $oRegister->getIDOfficeByRegister($nIDRegister);
			//$nAlPatrul	= $oAlPat->getPatrulRegister($nIDRegister);
			$aAlPatruls	= $oAlPat->getPatrulRegister($nIDRegister);
			$aPatrul	= $this->getPatrulByAuto($nIDAuto);
			$aReason	= $oReasons->getRecord( $nIDReason );

			$n1			= 0;
			$n2			= 0;
			$aTCar		= array();
			$aDistance	= array();
			$nPatNum	= isset($aPatrul['num'])	? $aPatrul['num']	: 0;
			$aDistance	= $this->getLastDistanceByPatrul($nPatNum);
            $dLan		= isset($aDistance['geo_lan'])	? $aDistance['geo_lan']	: 0;
            $dLat		= isset($aDistance['geo_lat'])	? $aDistance['geo_lat']	: 0;

            $start_lat	= isset($aDistance['start_geo_lat'])	? $aDistance['start_geo_lat']	: 0;
            $start_lan	= isset($aDistance['start_geo_lan'])	? $aDistance['start_geo_lan']	: 0;

			$nDs		= 0;
			$nRs		= 0;
			

			// Прекратяваме статуса на колата!
			if ( !empty($nIDAuto) && is_numeric($nIDAuto) ) {
				$aTCar	= $oCars->getCarByID($nIDAuto);
				$nDs	= $oObjects->getDistanceByGeo($dLat, $dLan, $aTCar['geo_lat'], $aTCar['geo_lan']);
				$nRs	= isset($aTCar['reaction_status']) ? $aTCar['reaction_status'] : 0;
				$nDs	= $nDs * 1000;

				if ( $nDs > 10000 ) {
					$nDs = 0;
				}

				$aUpdate					= array();
				$aUpdate['id']				= $nIDAuto;
				$aUpdate['reaction_status'] = 3;
				$aUpdate['reaction_object'] = 0;
				$aUpdate['reaction_time']	= time();

				$oCars->update($aUpdate);
			}

			// Променяме статуса на обекта!
			if ( !empty($nIDObject) ) {
				$aUpdate					= array();
				$aUpdate['id']				= $nIDObject;
				$aUpdate['reaction_status'] = $sType == "refusal_car" ? 1 : 0;
				$aUpdate['reaction_car']	= 0;
				
				$oObjects->update($aUpdate);
			}

			// Принудително пристигане
			$fArrival	= $oRegister->checkForArrival($nIDRegister);
			$fStart		= $oRegister->checkForStart($nIDRegister);
			//ob_toFile("aa: ".$fStart, "/tmp/test.txt");
			if ( !$fArrival && $fStart && ($sType == "reason") ) { //!$fArrival && $fStart !$fArrival && ($sType == "reason")
				$move	= 0;
				$move	= $this->getMovesByRegister($nIDRegister);
				
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
				$aStory['alarm_status']			= "arrival";
				$aStory['id_patrul']			= isset($aPatrul['id'])			? $aPatrul['id']		: 0;
				$aStory['patrul_num']			= isset($aPatrul['num'])		? $aPatrul['num']		: 0;
				$aStory['patrul_geo_lan']		= isset($aTCar['geo_lan'])		? $aTCar['geo_lan']		: 0;
				$aStory['patrul_geo_lat']		= isset($aTCar['geo_lat'])		? $aTCar['geo_lat']		: 0;
				$aStory['patrul_trace']			= $nDs;
				$aStory['patrul_trace_cascade_sum']		= $move;
				$aStory['id_auto']				= $nIDAuto;
				$aStory['id_reason']			= 1;
				$aStory['distance']				= 0;

				$oStory->update($aStory);

				$aAlarm						= array();
				$aAlarm['id']				= $nIDRegister;
				$aAlarm['arrival_time']		= time();

				$oRegister->update($aAlarm);
			}

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
			$aStory['alarm_status']			= $sType;
			$aStory['id_patrul']			= isset($aPatrul['id'])			? $aPatrul['id']		: 0;
			$aStory['patrul_num']			= isset($aPatrul['num'])		? $aPatrul['num']		: 0;
			$aStory['patrul_geo_lan']		= isset($aTCar['geo_lan'])		? $aTCar['geo_lan']		: 0;
			$aStory['patrul_geo_lat']		= isset($aTCar['geo_lat'])		? $aTCar['geo_lat']		: 0;
			$aStory['patrul_trace']			= $nDs;
			$aStory['id_auto']				= $nIDAuto;
			$aStory['id_reason']			= $nIDReason;
			$aStory['distance']				= 0;

			$oStory->update($aStory);

			$n1 = $aStory['id'];

			//if ( $sType == "reason" ) {
				// Затваряме алармата!

				$oRegister->delByStatus($nIDRegister, "cancel");

				$aStory							= array();
				$aStory['id']					= 0;
				$aStory['id_alarm_register']	= $nIDRegister;
				$aStory['id_archiv']			= 0;
				$aStory['id_msg']				= 0;
				$aStory['id_sig']				= 0;
				$aStory['alarm_time']			= time();
				$aStory['alarm_name']			= "";
				$aStory['pictogram']			= "";
				$aStory['alarm_status']			= "cancel";
				$aStory['id_patrul']			= isset($aPatrul['id'])			? $aPatrul['id']		: 0;
				$aStory['patrul_num']			= isset($aPatrul['num'])		? $aPatrul['num']		: 0;
				$aStory['patrul_geo_lan']		= isset($aTCar['geo_lan'])		? $aTCar['geo_lan']		: 0;
				$aStory['patrul_geo_lat']		= isset($aTCar['geo_lat'])		? $aTCar['geo_lat']		: 0;
				$aStory['patrul_trace']			= $nDs;
				$aStory['id_auto']				= $nIDAuto;
				$aStory['id_reason']			= $nIDReason;
				$aStory['distance']				= 0;

				$oStory->update($aStory);

				$n2		= $aStory['id'];
				$nStat	= 0;
				$move	= 0;
				$move	= $this->getMovesByRegister($nIDRegister);

				if ( $oRegister->checkForArrival($nIDRegister) ) {
					$nStat	= $oStat->Signals($nIDRegister);
				}

				if ( !empty($aAlPatruls) ) {
					foreach ( $aAlPatruls as $val ) {
						$nAlPatrul = $val['id'];
/*
                        try {
                            $real_distance  = getRealDistance($start_lat, $start_lan, $aTCar['geo_lat'], $aTCar['geo_lan']);
                        } catch (Exception $ex) {
                            // bla
                            $real_distance = 0;
                        }
*/
						$aAlarm						= array();
						$aAlarm['id']				= $nAlPatrul;
						$aAlarm['end_time']			= time();
						$aAlarm['end_geo_lan']		= isset($aTCar['geo_lan'])	? $aTCar['geo_lan']	: 0;
						$aAlarm['end_geo_lat']		= isset($aTCar['geo_lat'])	? $aTCar['geo_lat']	: 0;
                        $aAlarm['reaction_distance']= $move;
                        //$aAlarm['route_distance']   = $real_distance;
						$aAlarm['k_vr']				= $nStat;
	
						$oAlPat->update($aAlarm);
	
						// TODO: vreme za reakcia
						$sQry = "UPDATE {$db_name_sod}.alarm_patruls SET reaction_time = (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(start_time)) / 60, end_time = NOW() WHERE id = {$nAlPatrul}";
						$db_sod->Execute($sQry);
					}
				}
			//}

			$n2	 = $n1 + 1;

			//if ( $sType == "reason" ) {
				// Прекратяване на алармата!
				$aAlarm						= array();
				$aAlarm['id']				= $nIDRegister;
				$aAlarm['id_reason']		= $nIDReason;
				$aAlarm['status']			= "closed";
				$aAlarm['reaction_time']	= time();

				$oRegister->update($aAlarm);
			//}
			
			if ( !empty($nIDOffice) && !empty($nIDObject) ) { 
				$byRegion	= array();

				if ( $sType == "reason" ) {
					MonitoringEvents::addCmdObject(array(
						'id'		=> $nIDObject,
						'idRegion'	=> $nIDOffice,
						'event_type'=> 'cancel',
						'reason'	=> !empty( $aReason['name'] ) ? $aReason['name'] 	: ''
					));						
				}

				if ( !empty($nIDAuto) ) {
					MonitoringEvents::addCmdCar(array(
						'id'		=> $nIDAuto,
						'idRegion'	=> $nIDOffice,
						'event_type'=> 'car_free',
						'geo_lan'	=> isset($aTCar['geo_lan'])	? $aTCar['geo_lan']		: 0,
						'geo_lat'	=> isset($aTCar['geo_lat'])	? $aTCar['geo_lat']		: 0,
						'reason'	=> !empty( $aReason['name'] ) ? $aReason['name'] 	: ''
					));						
							
				}
			}
		}

	
		public function start($nIDRegister) {
			global $db_name_auto;

			if ( empty($nIDRegister) || !is_numeric($nIDRegister) ) {
				return;
			}

			$oCars		= new DBCars();
			$oObjects	= new DBObjects();
			$oRegister	= new DBAlarmRegister();
			$oAlPat		= new DBAlarmPatruls();
			$oStory		= new DBStory();

			$nIDAuto	= $oAlPat->getIDPatrulByRegister($nIDRegister);
			$nIDObject	= $oRegister->getIDObjectByRegister($nIDRegister);
			$nIDOffice	= $oRegister->getIDOfficeByRegister($nIDRegister);
			//$nAlPatrul	= $oAlPat->getPatrulRegister($nIDRegister);
			$aPatrul	= $this->getPatrulByAuto($nIDAuto);
			$aObject	= $oObjects->getObjectsByID($nIDObject);
			$n1			= 0;
			$n2			= 0;
			$aTCar		= array();
			$aDistance	= array();
			$nPatNum	= isset($aPatrul['num'])		? $aPatrul['num']		: 0;
			$dLan		= isset($aObject['geo_lan'])	? $aObject['geo_lan']	: 0;
			$dLat		= isset($aObject['geo_lat'])	? $aObject['geo_lat']	: 0;
			$nDs		= 0;
			

			// Колата се маркира като анонсирана!
			if ( !empty($nIDAuto) && is_numeric($nIDAuto) ) {
				$aTCar	= $oCars->getCarByID($nIDAuto);
				$nDs	= $oObjects->getDistanceByGeo($dLat, $dLan, $aTCar['geo_lat'], $aTCar['geo_lan']);
				$nDs	= $nDs * 1000;

				$fAuto	= $oCars->checkForGPS($nIDAuto);

				if ( $nDs > 10000 ) {
					$nDs = 0;
				}

				$aUpdate					= array();
				$aUpdate['id']				= $nIDAuto;
				$aUpdate['reaction_status'] = 2;
				$aUpdate['reaction_object'] = $nIDObject;
				$aUpdate['reaction_time']	= time();

				$oCars->update($aUpdate);
			}

			// Oбекта се маркира!
			if ( !empty($nIDObject) ) {
				$aUpdate					= array();
				$aUpdate['id']				= $nIDObject;
				$aUpdate['reaction_status'] = 2;
				$aUpdate['reaction_car']	= $nIDAuto;
				
				$oObjects->update($aUpdate);
			}

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
			$aStory['alarm_status']			= "start";
			$aStory['id_patrul']			= isset($aPatrul['id'])			? $aPatrul['id']		: 0;
			$aStory['patrul_num']			= isset($aPatrul['num'])		? $aPatrul['num']		: 0;
			$aStory['patrul_geo_lan']		= isset($aTCar['geo_lan'])		? $aTCar['geo_lan']		: 0;
			$aStory['patrul_geo_lat']		= isset($aTCar['geo_lat'])		? $aTCar['geo_lat']		: 0;
			$aStory['patrul_trace']			= 0;
			$aStory['id_auto']				= $nIDAuto;
			$aStory['id_reason']			= $nIDReason;
			$aStory['distance']				= $nDs;

			$oStory->update($aStory);

			$n = $aStory['id'];

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
				$aStory['id_patrul']			= isset($aPatrul['id'])			? $aPatrul['id']		: 0;
				$aStory['patrul_num']			= isset($aPatrul['num'])		? $aPatrul['num']		: 0;
				$aStory['patrul_geo_lan']		= isset($aTCar['geo_lan'])		? $aTCar['geo_lan']		: 0;
				$aStory['patrul_geo_lat']		= isset($aTCar['geo_lat'])		? $aTCar['geo_lat']		: 0;
				$aStory['patrul_trace']			= 0;
				$aStory['id_auto']				= $nIDAuto;
				$aStory['id_reason']			= 0;
				$aStory['distance']				= 0;

				$oStory->update($aStory);
			}
			
			if ( !empty($nIDOffice) && !empty($nIDAuto) ) { 
					MonitoringEvents::addCmdCar( array(
						'id' 				=> $nIDAuto,
						'idRegion'			=> $nIDOffice,
						'event_type'		=> 'start',
						'callsign'			=> isset($aPat['num'])					? $aPat['num']					: 0,
						'regnum'			=> isset($aTCar['auto'])				? $aTCar['auto']				: '',
						'statusGeo'			=> isset($aTCar['statusGeo'])			? $aTCar['statusGeo']			: false,
						'statusConnection'	=> isset($aTCar['statusConnection'])	? $aTCar['statusConnection']	: false,
						'geo_lan'			=> isset($aTCar['geo_lan'])				? $aTCar['geo_lan']				: 0,
						'geo_lat'			=> isset($aTCar['geo_lat'])				? $aTCar['geo_lat']				: 0,
						'idObject'			=> $nIDObject,
						'distance'			=> $nDs
					));
			}
		}
	
	}
?>
<?php
	class DBCars extends DBBase2 {
		public function __construct() {
			global $db_auto;
			
			parent::__construct( $db_auto, "auto" );
		}		
		
		/**
		 * Функцията връща актуални данни за автомобилите с назначение "Патрул",
		 * които се водят в движение по пътни листи, с техните координати
		 * 
		 * @author	Павел Петров
		 * @name	getCurrentPosition()
		 * 
		 * @return	array - масив с данните за автомобилите!
		 */
		public function getCurrentPosition() {
			global $db_name_auto, $db_name_personnel, $db_name_sod;
			
			$aData	= array();
			
			$sQuery = "
				SELECT
					rl.id,
					a.id AS id_auto,
					rl.id_office,
					a.geo_lat,
					a.geo_lan,
					pat.num_patrul AS code,
					CONCAT( '[' , fm.name , '] ', o.name ) AS office,
					CONCAT( m.model, ' [', a.reg_num, ']' ) AS auto,
					GROUP_CONCAT( CONCAT_WS( ' ', p.fname, p.lname ) SEPARATOR '\\n' ) AS patruls,
					rl.start_km,
					a.reaction_status,
					a.reaction_object,
					UNIX_TIMESTAMP(a.reaction_time) as reaction_time
				FROM {$db_name_auto}.road_lists rl 
				LEFT JOIN {$db_name_personnel}.personnel p ON ( FIND_IN_SET( p.id, rl.persons ) AND p.id IN ( rl.persons ) )
				LEFT JOIN {$db_name_sod}.offices o ON o.id = rl.id_office
				LEFT JOIN {$db_name_sod}.firms fm ON fm.id = o.id_firm
				LEFT JOIN {$db_name_auto}.auto a ON a.id = rl.id_auto
				LEFT JOIN {$db_name_auto}.auto_models m ON a.id_model = m.id
				LEFT JOIN {$db_name_auto}.functions f ON f.id = rl.id_function
				LEFT JOIN {$db_name_sod}.patruls pat ON pat.id = rl.id_patrul
				WHERE 1
					AND f.function_type = 'patrul'
					AND UNIX_TIMESTAMP( rl.end_time ) = 0
					AND a.geo_lat > 0
					AND a.to_arc = 0
					AND a.id_office != 0
				GROUP BY rl.id
				ORDER BY id_auto
			";
			//a.id_gps != 0
			$aData = $this->selectAssoc( $sQuery );
			
			if ( !empty($aData) ) {
				return $aData;
			} else {
				return array();
			}
		}

		/**
		 * Функцията връща регистрираните автомобили като патрул за даден
		 * регион. Данните се връщат в масив: id:car
		 * 
		 * @author	Павел Петров
		 * @name	getPatrul()
		 *
		 * @param	(numeric) $nIDOffice - ID на региона, за който търсим
		 * 
		 * @return	array - масив с данните за автомобилите!
		 */
		public function getPatrulCar( $nIDOffice ) {
			global $db_name_auto;

			$nIDOffice = is_numeric($nIDOffice) ? $nIDOffice : 0;

			$sQuery = sprintf("
				SELECT 
					a.id,
					CONCAT(m.model, ' [', a.reg_num, ']') AS car,
					a.geo_lat,
					a.geo_lan,
					a.reaction_status,
					a.reaction_object,
					UNIX_TIMESTAMP(a.reaction_time) as reaction_time
				FROM {$db_name_auto}.road_lists rl 
				LEFT JOIN {$db_name_auto}.auto a ON a.id = rl.id_auto
				LEFT JOIN {$db_name_auto}.auto_models m ON a.id_model = m.id
				LEFT JOIN {$db_name_auto}.functions f ON f.id = rl.id_function
				WHERE 1
					AND rl.id_office = %d
					AND f.function_type = 'patrul'
					AND UNIX_TIMESTAMP( rl.end_time ) = 0
					AND a.geo_lat > 0
					AND a.to_arc = 0
					AND a.id_office != 0
			", $nIDOffice );
			
			return $this->selectAssoc( $sQuery );
		}

		/**
		 * Функцията връща модел и номер на автомобила по зададено ID
		 * 
		 * @author	Павел Петров
		 * @name	getCarByID()
		 * 
		 * @param	(numeric) $nID	- ID на автомобила
		 *
		 * @return	(string) - модел и номер на автомобила!
		 */
		public function getCarByID( $nID ) {
			global $db_name_auto;

			$nID = is_numeric($nID) ? $nID : 0;
			
			$nMaxGPSReport = defined('MAX_GPS_REPORT') 		? MAX_GPS_REPORT 			: 30*60;
			$nMaxDisplayReport = defined('MAX_DISLPAY_REPORT') 	? MAX_DISLPAY_REPORT 	: 5*60;
			
			$sQuery = sprintf("
				SELECT 
					a.id,
					CONCAT(m.model, ' [', a.reg_num, ']') AS auto,
					a.geo_lat,
					a.geo_lan,
					a.reaction_status,
					a.reaction_object,
					IF( FROM_UNIXTIME(geo_real_time) + {$nMaxGPSReport} > NOW(),0, 1) as statusGeo,   
					IF( FROM_UNIXTIME(last_info_request) + {$nMaxDisplayReport} > NOW(),0, 1) as statusConnection,   
					UNIX_TIMESTAMP(a.reaction_time) as reaction_time
				FROM {$db_name_auto}.auto a
				LEFT JOIN {$db_name_auto}.auto_models m ON a.id_model = m.id
				WHERE a.id = %d
			", $nID );
			
			return $this->selectOnce( $sQuery );
		}

		public function carMove($aData) {
			$nIDCar		= isset($aData['id_auto']) && is_numeric($aData['id_auto']) ? $aData['id_auto'] : 0;
			$geoLan		= isset($aData['geo_lan']) ? $aData['geo_lan'] : 0;
			$geoLat		= isset($aData['geo_lat']) ? $aData['geo_lat'] : 0;
			$geoLan2	= isset($aData['geo_lan2']) ? $aData['geo_lan2'] : 0;
			$geoLat2	= isset($aData['geo_lat2']) ? $aData['geo_lat2'] : 0;

			if ( empty($nIDCar) || empty($geoLan) || empty($geoLat) ) {
				return false;
			}

			//$aCar		= $this->getCarByID($nIDCar);
			$oObjects	= new DBObjects();

			$distance	= $oObjects->getDistanceByGeo($geoLat, $geoLan, $geoLat2, $geoLan2);

			if ( ($distance * 1000) > 10 ) {
				return true;
			} else {
				return false;
			}
		}

		public function getObjectByAuto( $nIDAuto ) {
			global $db_name_auto;

			if ( empty($nIDAuto) || !is_numeric($nIDAuto) ) {
				return 0;
			}

			$sQuery = sprintf("
				SELECT 
					a.reaction_object
				FROM {$db_name_auto}.auto a
				WHERE a.id = %d
			", $nIDAuto );
			
			return $this->selectOne( $sQuery );
		}

		public function getCarByNumPatrul( $nPatrul ) {
			global $db_name_auto, $db_name_sod;

			if ( empty($nPatrul) || !is_numeric($nPatrul) ) {
				return array();
			}

			$sQuery = sprintf("
				SELECT 
					a.id,
					r.id_office,
					a.geo_lat,
					a.geo_lan,
					a.reaction_status,
					a.reaction_object,
					UNIX_TIMESTAMP(a.reaction_time) as reaction_time
				FROM {$db_name_sod}.patruls p
				LEFT JOIN {$db_name_auto}.road_lists r ON ( r.id_patrul = p.id AND UNIX_TIMESTAMP(r.end_time) = 0 )
				LEFT JOIN {$db_name_auto}.auto a ON ( a.id = r.id_auto )
				WHERE p.num_patrul = %d
			", $nPatrul );
			
			return $this->selectOnce( $sQuery );
		}

		public function getCarByIDPatrul( $nIDPatrul ) {
			global $db_name_auto, $db_name_sod;

			if ( empty($nIDPatrul) || !is_numeric($nIDPatrul) ) {
				return array();
			}

			$sQuery = sprintf("
				SELECT 
					a.id,
					p.num_patrul as num,
					r.id_office,
					r.id as id_road_list,
					a.geo_lat,
					a.geo_lan,
					a.reaction_status,
					a.reaction_object,
					UNIX_TIMESTAMP(a.reaction_time) as reaction_time
				FROM {$db_name_sod}.patruls p
				LEFT JOIN {$db_name_auto}.road_lists r ON ( r.id_patrul = p.id AND UNIX_TIMESTAMP(r.end_time) = 0 )
				LEFT JOIN {$db_name_auto}.auto a ON ( a.id = r.id_auto )
				WHERE p.id = %d
			", $nIDPatrul );
			
			return $this->selectOnce( $sQuery );
		}


		public function checkForGPS( $nIDAuto ) {
			global $db_name_auto;

			if ( empty($nIDAuto) || !is_numeric($nIDAuto) ) {
				return false;
			}

			$nMaxGPSReport = defined('MAX_GPS_REPORT') 		? MAX_GPS_REPORT 			: 30*60;
			$nTime	= time() - $nMaxGPSReport;

			$sQuery = sprintf("
				SELECT 
					1
				FROM {$db_name_auto}.auto a
				WHERE a.id = %d
					AND UNIX_TIMESTAMP(a.geo_real_time) > %d
				LIMIT 1
			", $nIDAuto, $nTime );
			
			$nID = $this->selectOne( $sQuery );

			if ( empty($nID) || !is_numeric($nID) ) {
				return false;
			} else {
				return true;
			}
		}
	
	}
?>
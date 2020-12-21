<?php
	class DBObjects extends DBBase2 {
		public function __construct() {
			global $db_sod;
			
			parent::__construct( $db_sod, "objects" );
		}		
		
		/**
		 * Функцията връща дистанцията м/у две точки в геостационарна 
		 * координатна система в километри
		 * 
		 * @author	Павел Петров
		 * @name	getDistanceByGeo()
		 *
		 * @param	(decimal) $x1, y1 - координатите на първата точка
		 * @param	(decimal) $x2, y2 - координатите на втората точка
		 * 
		 * @return	(int) - Дистанция в километри
		 */
		function getDistanceByGeo( $x1, $y1, $x2, $y2 ) {
			$x1		= deg2rad($x1);
			$x2		= deg2rad($x2);
			$y1		= deg2rad($y1);
			$y2		= deg2rad($y2);

			$R		= 6371;

			$lat	= $x2 - $x1;
			$long	= $y1 - $y2;

			$arc	= ( sin($lat / 2) * sin($lat / 2) ) + cos($x1) * cos($x2) * ( sin($long / 2) * sin($long / 2) );
			$cer	= 2 * atan2( sqrt($arc), sqrt(1 - $arc) );

			return $R * $cer;
		}

		/**
		 * Функцията връща актуални данни за обектите по статуси
		 * 
		 * @author	Павел Петров
		 * @name	getObjectStatuses()
		 *
		 * @param	(string) $sIDAlarm		- числова поредица - ID алармиращ сигнал
		 * @param	(string) $sIDRestore	- числова поредица - ID възстановяващ сигнал
		 * @param	(numeric) $nUTS			- време на последен сигнал
		 * 
		 * @return	(array) - масив с данните обектите по статуси!
		 */
		public function getObjectStatuses( $sIDAlarm, $sIDRestore, $nUTS ) {
			global $db_name_sod;
			
			$aData	= array();

			if ( empty($sIDAlarm) ) {
				$sIDAlarm = "-1";
			}

			if ( empty($sIDRestore) ) {
				$sIDRestore = "-1";
			}
			
			$sQuery = sprintf("
				SELECT 
					o.id AS id,
					m.id as id_msg,
					o.geo_lan,
					o.geo_lat,
					o.name,
					o.num,
					o.address,
					o.id_reaction_office as id_office,
					o.reaction_status,
					IF ( m.flag = 1, m.msg_al, m.msg_rest ) as msg,
					m.time_al as msg_time
				FROM {$db_name_sod}.objects o
				LEFT JOIN {$db_name_sod}.messages m ON m.id_obj = o.id
				WHERE o.id_status != %d
					AND o.geo_lan > 0
					AND o.confirmed = 1
					AND ( (m.flag = 1 AND m.id_sig IN (%s)) OR (m.flag = 0 AND m.id_sig IN (%s)) )
					AND UNIX_TIMESTAMP(m.time_al) > %s
			", 
			4, $sIDAlarm, $sIDRestore, time() - $nUTS);

			$aData = $this->selectAssoc( $sQuery );
			
			if ( !empty($aData) ) {
				return $aData;
			} else {
				return array();
			}
		}

		/**
		 * Функцията връща типовете алармиращи сигнали, като състояниата 
		 * алармираща/възтановяваща се определя с префикс на ID: 1000/100000
		 * 
		 * @author	Павел Петров
		 * @name	getSignals()
		 * 
		 * @return	(array) - масив с данните за сигналите!
		 */
		public function getSignals() {
			global $db_name_sod;

			$sQuery = "
				(
					SELECT 
						(s.id + 1000) as id,
						s.msg_al as name
					FROM {$db_name_sod}.signals s
					WHERE s.play_alarm != 0
						AND LENGTH(s.msg_al) > 2
					ORDER BY s.msg_al
				) UNION (
					SELECT 
						(s.id + 100000) as id,
						s.msg_rest as name
					FROM {$db_name_sod}.signals s
					WHERE s.play_alarm != 0
						AND LENGTH(s.msg_rest) > 2
					ORDER BY s.msg_rest
				)
			";
			
			return $this->selectAssoc( $sQuery );
		}

		/**
		 * Функцията връща обектите по зададен регион
		 * 
		 * @author	Павел Петров
		 * @name	getObjectsByOffice()
		 * 
		 * @param	(numeric) $nIDOffice	- ID на автомобила
		 *
		 * @return	(string) - данните за обектите по регион
		 */
		public function getObjectsByOffice( $nIDOffice ) {
			global $db_name_sod;

			$nIDOffice	= is_numeric($nIDOffice) ? $nIDOffice : 0;

			$sQuery		= sprintf("
				SELECT 
					id,
					id_reaction_office as id_office,
					geo_lan,
					geo_lat,
					name,
					num,
					address,
					reaction_status
				FROM {$db_name_sod}.objects
				WHERE id_reaction_office = %d
					AND id_status != %d
					AND geo_lan > 0
					AND confirmed = 1
			", 
			$nIDOffice, 4);
			
			return $this->selectAssoc( $sQuery );
		}

		public function getObjectsByID( $nIDObject ) {
			global $db_name_sod;

			if ( empty($nIDObject) || !is_numeric($nIDObject) ) {
				return array();
			}

			$sQuery		= sprintf("
				SELECT 
					id,
					id_oldobj,
					id_reaction_office as id_office,
					geo_lan,
					geo_lat,
					name,
					num,
					address,
					reaction_status,
					reaction_car
				FROM {$db_name_sod}.objects
				WHERE id = %d
			", 
			$nIDObject);
			
			return $this->selectOnce( $sQuery );
		}

		public function getObjectsWithOutReaction() {
			global $db_sod, $db_name_sod;

			$sQuery	= sprintf("
				SELECT 
					id,
					id_reaction_office as id_office,
					geo_lan,
					geo_lat,
					name,
					num,
					address,
					reaction_status
				FROM {$db_name_sod}.objects
				WHERE reaction_status = %d
					AND reaction_car = 0
					AND confirmed = 1
					AND geo_lan > 0
			", 
			1);
			
			return $this->selectAssoc( $sQuery );
		}

		public function getObjectsByNum( $nObject ) {
			global $db_name_sod;

			if ( empty($nObject) || !is_numeric($nObject) ) {
				return array();
			}

			$sQuery		= sprintf("
				SELECT 
					id,
					id_reaction_office as id_office,
					geo_lan,
					geo_lat,
					name,
					num,
					address,
					reaction_status,
					reaction_car
				FROM {$db_name_sod}.objects
				WHERE num = %d
					AND confirmed = 1
					AND reaction_status != 0
			", 
			$nObject);
			
			return $this->selectOnce( $sQuery );
		}

		public function getAlarmObjects() {
			global $db_name_sod;

			$sQuery	= "
				SELECT 
					id,
					id_reaction_office as id_office,
					geo_lan,
					geo_lat,
					name,
					num,
					address,
					reaction_status
				FROM {$db_name_sod}.objects
				WHERE reaction_status != 0
					AND confirmed = 1
					AND geo_lan > 0
			";
			
			return $this->selectAssoc( $sQuery );
		}

		public function getReactionObjectFromPowerLink( $nIDObject ) {
			global $db_name_sod;

			if ( empty($nIDObject) || !is_numeric($nIDObject) ) {
				return array();
			}

			$date	= date("Y-m-d");

			$sQuery	= "
				SELECT
					id,
					id_patrul
				FROM {$db_name_sod}.work_card_movement
				WHERE `type` = 'object'
					AND id_object = {$nIDObject}
					AND DATE_FORMAT(start_time, '%Y-%m-%d') = '{$date}'
					AND end_time = '0000-00-00 00:00:00'
				LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}

		public function getReactionObjectFromPowerLinkByID( $nID ) {
			global $db_name_sod;

			if ( empty($nID) || !is_numeric($nID) ) {
				return array();
			}

			$sQuery	= "
				SELECT
					id,
					id_patrul,
					id_alarm_reasons,
					end_time,
					reason_time
				FROM {$db_name_sod}.work_card_movement
				WHERE id = {$nID}
				LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function suggestObject($word,$region=0){
			global $db_name_sod;
			
			if( $region ){
				$qregion = " AND id_reaction_office = $region ";
			}
			
			$query = "SELECT * FROM objects WHERE ( num LIKE '$word%' OR name LIKE '%$word%' ) $qregion AND is_sod = 1 LIMIT 4";
			return $this->select($query);
		}
	}
?>
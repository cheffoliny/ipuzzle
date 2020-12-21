<?php
	class DBAlarmRegister extends DBBase2 {
			
		public function __construct() {
			global $db_sod;
			
			parent::__construct($db_sod, 'alarm_register');
		}	
		

		/**
		 * Функцията връща всички активни работни карти, които са регистрирали 
		 * зададения офис за реакция
		 * 
		 * @author	Павел Петров
		 * @name	getWorkCardByOffice()
		 *
		 * @param	(numeric) $nIDOffice - ID на офиса, за който търсим РК
		 * 
		 * @return	(string) - списък с ID-та на работните карти!
		 */
		public function getWorkCardByOffice( $nIDOffice ) {
			global $db_name_sod;

			$nIDOffice = is_numeric($nIDOffice) ? $nIDOffice : 0;

			$sQuery = sprintf("
				SELECT 
					GROUP_CONCAT(wc.id) as id
				FROM {$db_name_sod}.work_card wc
				LEFT JOIN {$db_name_sod}.work_card_offices wco ON wc.id = wco.id_work_card
				WHERE 1
					AND wco.id_office = %d
					AND wc.end_time < wc.start_time
					AND UNIX_TIMESTAMP(wc.start_time) > (UNIX_TIMESTAMP(NOW()) - 172800)
			", $nIDOffice );
			
			return $this->selectOne( $sQuery );
		}


		/**
		 * Функцията връща ID на регистъра на текущата аларма за зададения обект 
		 * 
		 * @author	Павел Петров
		 * @name	getCurrentRegisterIDByAlarmObject()
		 *
		 * @param	(numeric) $nIDObject - ID на обекта, за който търсим история
		 * 
		 * @return	(int) - ID на регистъра ако е намерен, 0 когато не е открит
		 */
		public function getCurrentRegisterIDByAlarmObject( $nIDObject ) {
			global $db_name_sod;

			if ( empty($nIDObject) || !is_numeric($nIDObject) ) {
				return 0;
			}

			$nID	= 0;

			$sQuery = sprintf("
				SELECT 
					MAX(ar.id) as id
				FROM {$db_name_sod}.alarm_register ar
				WHERE 1
					AND ar.id_object = %d
					AND ar.status = 'active'
			", $nIDObject );
			
			$nID = $this->selectOne( $sQuery );

			if ( empty($nID) || !is_numeric($nID) ) {
				return 0;
			} else {
				return $nID;
			}
		}
		
		public function getIDObjectByRegister( $nIDRegister ) {
			global $db_name_sod;

			if ( empty($nIDRegister) || !is_numeric($nIDRegister) ) {
				return 0;
			}

			$nID	= 0;

			$sQuery = sprintf("
				SELECT 
					ar.id_object as id
				FROM {$db_name_sod}.alarm_register ar
				WHERE 1
					AND ar.id = %d
			", $nIDRegister );
			
			$nID = $this->selectOne( $sQuery );

			if ( empty($nID) || !is_numeric($nID) ) {
				return 0;
			} else {
				return $nID;
			}
		}

		public function checkForArrival( $nIDRegister ) {
			global $db_name_sod;

			if ( empty($nIDRegister) || !is_numeric($nIDRegister) ) {
				return false;
			}

			$sQuery = sprintf("
				SELECT 
					1
				FROM {$db_name_sod}.alarm_history ah
				WHERE ah.id_alarm_register = %d
					AND ah.alarm_status = 'arrival'
				LIMIT 1
			", $nIDRegister );
			
			$nID = $this->selectOne( $sQuery );

			if ( empty($nID) || !is_numeric($nID) ) {
				return false;
			} else {
				return true;
			}
		}

		public function checkForStart( $nIDRegister ) {
			global $db_name_sod;

			if ( empty($nIDRegister) || !is_numeric($nIDRegister) ) {
				return false;
			}

			$sQuery = sprintf("
				SELECT 
					1
				FROM {$db_name_sod}.alarm_history ah
				WHERE ah.id_alarm_register = %d
					AND ah.alarm_status = 'start'
				LIMIT 1
			", $nIDRegister );
			
			$nID = $this->selectOne( $sQuery );

			if ( empty($nID) || !is_numeric($nID) ) {
				return false;
			} else {
				return true;
			}
		}

		public function delByStatus($nIDRegister, $sStatus) {
			global $db_name_sod, $db_sod;

			if ( empty($nIDRegister) || !is_numeric($nIDRegister) ) {
				return false;
			}

			$sQuery = "DELETE FROM {$db_name_sod}.alarm_history WHERE id_alarm_register = {$nIDRegister} AND alarm_status = '{$sStatus}'";
	
			$db_sod->Execute($sQuery);
		}

		public function getAlarmsWithOutReaction( $nTime, $getAll = 0 ) {
			global $db_name_sod;

			if ( empty($nTime) || !is_numeric($nTime) ) {
				return array();
			}

			$aData	= array();

			$sQuery = sprintf("
				SELECT 
					ar.id
				FROM {$db_name_sod}.alarm_register ar
				WHERE 1
					AND (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(ar.alarm_time)) >= %d
					AND ar.status = 'active'
			", $nTime );
			
			$aData = $this->select( $sQuery );

			if ( $getAll == 1 ) {
				return $aData;
			}

			foreach ( $aData as $key => $val ) {
				$id		= isset($val['id']) ? $val['id'] : 0;
				$sQuery = "SELECT id_alarm_register as id FROM {$db_name_sod}.alarm_history WHERE id_alarm_register = {$id} AND alarm_status = 'start'";

				$nID	= $this->selectOne( $sQuery );
				
				if ( $nID == $id ) {
					unset($aData[$key]);
				}
			}

			return $aData;
		}

		public function getIDOfficeByRegister( $nIDRegister ) {
			global $db_name_sod;

			if ( empty($nIDRegister) || !is_numeric($nIDRegister) ) {
				return 0;
			}

			$nID	= 0;

			$sQuery = sprintf("
				SELECT 
					ar.id_office as id
				FROM {$db_name_sod}.alarm_register ar
				WHERE 1
					AND ar.id = %d
			", $nIDRegister );
			
			$nID = $this->selectOne( $sQuery );

			if ( empty($nID) || !is_numeric($nID) ) {
				return 0;
			} else {
				return $nID;
			}
		}

		public function dasdas( $nIDObject ) {
			global $db_name_sod;

			if ( empty($nIDObject) || !is_numeric($nIDObject) ) {
				return 0;
			}

			$nID	= 0;

			$sQuery = sprintf("
				SELECT 
					MAX(ar.id) as id
				FROM {$db_name_sod}.alarm_register ar
				WHERE 1
					AND ar.id_object = %d
					AND ar.status = 'active'
			", $nIDObject );
			
			$nID = $this->selectOne( $sQuery );

			if ( empty($nID) || !is_numeric($nID) ) {
				return 0;
			} else {
				return $nID;
			}
		}

		public function getTimeByRegister( $nIDRegister ) {
			global $db_name_sod;

			if ( empty($nIDRegister) || !is_numeric($nIDRegister) ) {
				return 0;
			}

			$sQuery = "
				SELECT 
					UNIX_TIMESTAMP(ar.alarm_time) as tmp
				FROM {$db_name_sod}.alarm_register ar
				WHERE 1
					AND ar.id = {$nIDRegister}
			";

			return $this->selectOne($sQuery);
		}

	}
?>
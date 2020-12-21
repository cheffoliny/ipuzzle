<?php
	class DBAlarmPatruls extends DBBase2 {
		public function __construct() {
			global $db_sod;
			
			parent::__construct($db_sod, "alarm_patruls");
		}	
		
		public function getPatrulRegister( $nIDRegister ) {
			global $db_name_sod;

			if ( empty($nIDRegister) || !is_numeric($nIDRegister) ) {
				return 0;
			}

			$nID	= 0;

			$sQuery = sprintf("
				SELECT 
					ap.id as id
				FROM {$db_name_sod}.alarm_patruls ap
				WHERE 1
					AND ap.id_alarm_register = %d
			", $nIDRegister );
			
			return $this->select( $sQuery );
/*
			if ( empty($nID) || !is_numeric($nID) ) {
				return 0;
			} else {
				return $nID;
			}
*/			
		}

		public function getByRegister( $nIDRegister ) {
			global $db_name_sod;

			if ( empty($nIDRegister) || !is_numeric($nIDRegister) ) {
				return array();
			}

			$sQuery = sprintf("
				SELECT 
					id,
					id_road_list,
					id_work_card_movement,
					patrul_num
				FROM {$db_name_sod}.alarm_patruls ap
				WHERE 1
					AND ap.id_alarm_register = %d
			", $nIDRegister );
			
			return $this->selectOnce( $sQuery );
		}

		public function getIDPatrulByRegister( $nIDRegister ) {
			global $db_name_sod, $db_name_auto;

			if ( empty($nIDRegister) || !is_numeric($nIDRegister) ) {
				return 0;
			}

			$nID	= 0;

			$sQuery = sprintf("
				SELECT 
					rl.id_auto
				FROM {$db_name_sod}.alarm_patruls ap
				LEFT JOIN {$db_name_sod}.patruls p ON p.num_patrul = ap.patrul_num
				LEFT JOIN {$db_name_auto}.road_lists rl ON (rl.id_patrul = p.id AND UNIX_TIMESTAMP(rl.end_time) = 0 )
				WHERE 1
					AND ap.id_alarm_register = %d
					AND UNIX_TIMESTAMP(rl.end_time) = 0
			", $nIDRegister );
			
			$nID = $this->selectOne( $sQuery );

			if ( empty($nID) || !is_numeric($nID) ) {
				return 0;
			} else {
				return $nID;
			}
		}

		public function delByIDRegister( $nIDRegister ) {
			global $db_name_sod, $db_sod;

			if ( empty($nIDRegister) || !is_numeric($nIDRegister) ) {
				return 0;
			}

			$sQuery = "DELETE FROM {$db_name_sod}.alarm_patruls WHERE id_alarm_register = {$nIDRegister}";

			$db_sod->Execute($sQuery);
		}
		
		public function getLayersVisits($from_date,$to_date) {			
			global $db_name_sod;
			
			$nIDOffice = !empty($aAdditionalOptions['nIDOffice']) ? (int) $aAdditionalOptions['nIDOffice'] : 0;
			$nIDObject = !empty($aAdditionalOptions['nIDObject']) ? (int) $aAdditionalOptions['nIDObject'] : 0;						
			
			$sFromDate = $from_date." 00:00:00";
			$sToDate = $to_date." 23:59:59";
			
			$sQuery = "
				SELECT
					ap.id,
					ap.id_object,
					ap.patrul_num,
					ap.end_time
				FROM {$db_name_sod}.alarm_patruls ap
				JOIN layers_objects lo ON lo.id = ap.id_object
				WHERE 
					ap.id_alarm_register = 0 AND
					end_time >= '{$sFromDate}' AND end_time < '{$sToDate}'
			";
					
			if(!empty($nIDObject)) {
				$sQuery .= " AND ap.id_object = {$nIDObject} \n";
			} else {				
				if(!empty($nIDOffice)) {
					$sQuery .= " AND lo.id_office = {$nIDOffice}\n";
				} else if( $_SESSION['userdata']['access_right_all_regions'] != 1 ) {
					$sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
					$sQuery .= " AND lo.id_office IN ({$sAccessable}) \n";					
				}				
			}

			$aData = $this->select($sQuery);
			
			$aFinalData = array();
			foreach($aData as $key => $value) {
				
				$sDate = substr($value['end_time'],0,10);
				
				$value['visit_hour']  = substr($value['end_time'],11,5);
				$aFinalData[$sDate][$value['id_object']][] = $value;
			}
			
			return $aFinalData;
		}
		
	}
?>
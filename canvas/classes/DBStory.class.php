<?php
	class DBStory extends DBBase2 {
			
		static $ALARM_HISTORY_SELECT = "
										h.*,
										r.id_object,
										r.id_office,
										r.obj_num,
										r.obj_name,
										r.obj_address,
										r.obj_geo_lan,
										r.obj_geo_lat,
										r.obj_time_alarm_reaction,
										r.alarm_name,
										UNIX_TIMESTAMP( r.alarm_time ) as alarm_time_stamp,
										UNIX_TIMESTAMP( h.alarm_time ) as `time`,
										rs.name as reason
										";
		
		public function __construct() {
			global $db_sod;
			//$db_sod->debug = true;
			parent::__construct($db_sod, 'alarm_history');
		}
		
		public function getHistoryAfterLastCarStatus($carId, $status){
			$result= array();
			$query = "	SELECT * FROM alarm_history WHERE 	id_auto = {$carId}
								AND alarm_status = '{$status}' ORDER BY id DESC LIMIT 1";
			$last = $this->selectOnce($query);
			
			if( empty($last) ){
				return $result;
			}
			
			$query = "	SELECT
								" . self::$ALARM_HISTORY_SELECT . "
						FROM 	alarm_history h
								LEFT JOIN alarm_register r ON r.id = h.id_alarm_register
								LEFT JOIN alarm_reasons rs ON rs.id = h.id_reason
						WHERE
								h.id_alarm_register = {$last['id_alarm_register']}
								AND
								h.alarm_status = 'alarm'
								
						ORDER BY h.id ASC LIMIT 1";
						
			$first = $this->selectOnce($query);
			
			if( empty($first) ){
				return $result;
			}
			
			$query = "	SELECT
								" . self::$ALARM_HISTORY_SELECT . "
								
						FROM 	alarm_history h
								LEFT JOIN alarm_register r ON r.id = h.id_alarm_register
								LEFT JOIN alarm_reasons rs ON rs.id = h.id_reason
						WHERE
									h.id >= {$last['id']}
									AND
									h.id_auto = {$carId}
									AND
									h.id_alarm_register = {$last['id_alarm_register']}
																
						ORDER BY h.id ASC";
			$result = $this->select($query);
			array_unshift( $result, $first );
			return $result;
		}
		
		public function getAlarmHistory($id_alarm_register){
			if( !is_numeric($id_alarm_register) ){
				throw new Exception("id_alarm_register param should be numeric");
			}
			
			
			$query = "	SELECT
								" . self::$ALARM_HISTORY_SELECT . "
								
						FROM 	alarm_history h
								LEFT JOIN alarm_register r ON r.id = h.id_alarm_register
								LEFT JOIN alarm_reasons rs ON rs.id = h.id_reason
						WHERE
								h.id_alarm_register = $id_alarm_register
						ORDER BY h.id ASC";
			
			$result = $this->select($query);
			
			if( $result === false ){
				throw new Exception("Error while executing query");	
			}
			
			return $result;
		}
		
		public function getHistoryByObject($idObject,$statuses=array()){
			if(!is_numeric($idObject)){
				throw new Exception("idObject should be numeric");
			}
			
			$statusesWhere = "";
			
			if( !empty($statuses) ){
				$statusesWhere = " AND h.alarm_status IN ( '" . implode("','",$statuses) . "') ";
			}
			
			$query = "	SELECT
								" . self::$ALARM_HISTORY_SELECT  . "
						FROM
								alarm_history h
								LEFT JOIN alarm_register r ON r.id = h.id_alarm_register
								LEFT JOIN alarm_reasons rs ON rs.id = h.id_reason
						WHERE
								r.id_object = $idObject
								AND
								r.status = 'active'
								{$statusesWhere}
								
						ORDER BY h.id ASC
					";
			
			$result = $this->select($query);
			
			if( $result === false ){
				throw new Exception("Error while executing query");	
			}
			
			return $result;
		}
	}
?>
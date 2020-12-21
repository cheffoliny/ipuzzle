<?php
	class DBSODSignals
		extends DBBase2 {
			
		public function __construct() {
			global $db_sod;
			//$db_sod->debug=true;
			
			parent::__construct($db_sod, 'signals');
		}
		
		public function getPic( $nID)	{
			$sQuery = "
				SELECT
					pic
				FROM signals
				WHERE id = {$nID}
			";
			return $this->selectOne($sQuery);
		}

		public function getSignals()	{
			$sQuery = "
				SELECT
					id,
					CONCAT(msg_al,' / ',msg_rest) as name
				FROM signals
				WHERE to_arc = 0
			";
			
			return $this->selectAssoc( $sQuery );
		}
		public function getSignalById( $nID )
		{
			$sQuery = "
				SELECT
					CONCAT(msg_al,' / ',msg_rest) as name
				FROM signals
				WHERE to_arc = 0
				AND id = {$nID}
			";
			
			return $this->select( $sQuery );
		}

//		public function getSignalById( $nID )	{
//			$sQuery = "
//				SELECT
//					id,
//					id_sig,
//					msg_al,
//					code_al,
//					msg_rest,
//					code_rest,
//					test_flag,
//					test,
//					IF (is_phone, IF(is_cid, 'cid', 'phone'), 'radio') as channel,
//					is_cid
//				FROM messages
//				WHERE to_arc = 0
//					AND id = {$nID}
//			";
//
//			return $this->select( $sQuery );
//		}
		
		
		public function getAlarmSignals()	{
			$sQuery = "
				SELECT
					id,
					msg_al
				FROM signals
				WHERE to_arc = 0
					AND play_alarm = 2
			";
			
			return $this->select( $sQuery );
		}	
		
		public function getSignalsAfterId($sTableName, $nID) {
			global $db_name_sod;

			if ( !isset($nID) || !is_numeric($nID) ) {
				return array();
			}

			$sQuery = "
				SELECT
					a.id as id_archiv,
					m.id as id_msg,
					o.id as id_obj,
					s.id as id_sig,
					a.msg as message,
					a.alarm as alarm,
					a.msg_time as alarm_time,
					CONCAT(DATE_FORMAT(a.msg_time, '%h:%n:%s'), ' ', a.msg) as alarm_message,
					IF ( a.alarm = 1, m.msg_al, m.msg_rest ) as signal_message,
					s.play_alarm as signal_type,
					o.name as object_name,
					o.num as object_num,
					o.address as object_address,
					o.id_reaction_office as id_office,
					o.reaction_status,
					o.geo_lan,
					o.geo_lat,
					(o.reaction_time_normal * 60) as time_limit
				FROM {$db_name_sod}.$sTableName a
				LEFT JOIN {$db_name_sod}.messages m ON m.id = a.id_msg
				LEFT JOIN {$db_name_sod}.objects o ON ( o.id = m.id_obj AND m.id_obj > 0 )
				LEFT JOIN {$db_name_sod}.signals s ON ( s.id = m.id_sig AND m.id_sig > 0 )
				WHERE a.id > {$nID}
					AND o.confirmed = 1
					AND o.geo_lan > 0
					AND o.id_status = 1
					AND ( o.service_status = 0 OR ( o.service_status = 1 AND DATE_FORMAT(o.service_status_time, '%Y-%m-%d') != DATE_FORMAT(NOW(), '%Y-%m-%d') ) )
					AND (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(a.msg_time)) <= 60
				LIMIT 100
			";

			return $this->select( $sQuery );
		}

		public function getClosingByObject($nIDObject) {
			global $db_name_sod;

			if ( !isset($nIDObject) || !is_numeric($nIDObject) ) {
				return false;
			}

			$sTableName = "archiv_".date("Ym");

			$sQuery = "
				SELECT
					UNIX_TIMESTAMP(MAX(a.msg_time)) - UNIX_TIMESTAMP(NOW()) as t
				FROM {$db_name_sod}.$sTableName a
				LEFT JOIN {$db_name_sod}.messages m ON m.id = a.id_msg
				WHERE m.id_obj = {$nIDObject}
					AND m.flag = 0
					AND m.id_sig IN (9,23,24,25,26)
			";
			
			$nTime = $this->selectOne( $sQuery );

			if ( is_numeric($nTime) && ($nTime < 300) ) {
				return true;
			} else {
				return false;
			}
		}	


		public function getOpeningByObject($nIDObject) {
			global $db_name_sod;

			if ( !isset($nIDObject) || !is_numeric($nIDObject) ) {
				return false;
			}

			$sTableName = "archiv_".date("Ym");

			$sQuery = "
				SELECT
					UNIX_TIMESTAMP(MAX(a.msg_time)) - UNIX_TIMESTAMP(NOW()) as t
				FROM {$db_name_sod}.$sTableName a
				LEFT JOIN {$db_name_sod}.messages m ON m.id = a.id_msg
				WHERE m.id_obj = {$nIDObject}
					AND m.flag = 1
					AND m.id_sig IN (9,23,24,25,26)
			";
			
			$nTime = $this->selectOne( $sQuery );

			if ( is_numeric($nTime) && ($nTime < 300) ) {
				return true;
			} else {
				return false;
			}
		}
		
		public function getLastArchTable() {
			global $db_name_sod, $db_sod;
			
			$sPrefix = 'archiv_';
			$aTables = $this->oDB->GetCol("SHOW TABLES FROM {$db_name_sod} LIKE '{$sPrefix}______'");
			
			if( $aTables === false )
			{
				APILog::Log(DBAPI_ERR_SQL_QUERY, NULL, __FILE__, __LINE__);
				return DBAPI_ERR_SQL_QUERY;
			}
				
			for($i=0;$i<count( $aTables ); )
			{
				if( !ereg("^{$sPrefix}([0-9]){4}(([0][0-9])|([1][0-2]))$", $aTables[ $i ]) )
					array_splice($aTables, $i, 1);
				else
					$i++;
			}
			
			if( empty( $aTables ) )
				return array();
			
			sort( $aTables );
			
			return end($aTables);
		}

	}
?>
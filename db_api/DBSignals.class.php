<?php
	class DBSignals
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

		public function getSignals() {
			global $db_name_sod;
			
			$sQuery = "
				SELECT
					id,
					msg_al,
					msg_rest,
					pic,
					test_flag,
					play_alarm
				FROM {$db_name_sod}.signals
				WHERE to_arc = 0
			";
			
			return $this->select($sQuery);
		}

		public function getSignalById( $nID )	{
					global $db_name_sod;
			
			if ( empty($nID) || !is_numeric($nID) ) {
				return array();
			}
			
			$sQuery = "
				SELECT
					IF( m.is_zone!= 0,
					    IF( m.id_cid>=400 AND m.id_cid<=500, COALESCE( ou.name, m.zone ), COALESCE( oz.name , m.zone ) ),
					    NULL
                    ) AS zName,
					IF( m.is_sector != 0, COALESCE( os.name, m.part ), NULL) AS sName,
                    os.name     AS 'sName'    ,
                    m.id        AS 'id'       ,
                    m.id_sig    AS 'id_sig'   ,
                    m.id_obj    AS 'id_obj'   ,
                    m.msg_al    AS 'msg_al'   ,
                    m.code_al   AS 'code_al'  ,
                    m.msg_rest  AS 'msg_rest' ,
                    m.code_rest AS 'code_rest',
                    m.test_flag AS 'test_flag',
                    m.test      AS 'test'     ,
                    IF (m.is_phone, IF(m.is_cid, 'cid', 'phone'), 'radio') as channel,
                    m.is_cid    AS 'is_cid'   ,
                    m.id_cid    AS 'id_cid'   ,
                    m.is_zone   AS 'is_zone'  ,
                    m.zone      AS 'zone'     ,
                    m.is_sector AS 'is_sector',
                    m.part      AS 'part'     ,
                    m.flag      AS 'flag'     ,
                    m.is_phone  AS 'is_phone'
                FROM {$db_name_sod}.messages m
                LEFT JOIN {$db_name_sod}.objects_zones oz ON oz.id_object = m.id_obj AND oz.zone = m.zone AND oz.to_arc = 0
                LEFT JOIN {$db_name_sod}.objects_sectors os ON os.id_object = m.id_obj AND os.sector = m.part AND os.to_arc = 0
                LEFT JOIN {$db_name_sod}.objects_users ou ON ou.id_object = m.id_obj AND ou.user = m.zone AND ou.to_arc = 0
                WHERE m.to_arc = 0
                    AND m.id = {$nID}
			";

			return $this->selectOnce($sQuery);
		}
		
		
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
	}
?>
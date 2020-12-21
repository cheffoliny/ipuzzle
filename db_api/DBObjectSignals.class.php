<?php
	class DBObjectSignals extends DBBase2 {
		public function __construct() {
			global $db_sod;
			
			parent::__construct( $db_sod, "object_signals" );
		}
		
		
		public function getOldStatusByIdObjectType($nIDObect, $sType) {
			global $db_name_sod;
			
			if ( empty($nIDObect) || !is_numeric($nIDObect) ) {
				return 0;
			}
			
			if ( empty($sType) ) {
				$sType = "change_status";
			}
			
			$sQuery = "
				SELECT 
					id_old_status as id_status
				FROM {$db_name_sod}.object_changes
				WHERE id_obj = {$nIDObect}
					AND `type` = '{$sType}'
				ORDER BY id DESC 
				LIMIT 1
			";
			
			return $this->selectOne($sQuery);
		}
	}
?>
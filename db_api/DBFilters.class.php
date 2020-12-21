<?php
	
	class DBFilters extends DBBase2 {
		
		function __construct() {
			global $db_system;
			
			parent::__construct($db_system, "filters");
		}
		
		public function getFiltersByReportClass($sReportClass, $nIDPerson) {
			global $db_system, $db_name_system;
			
			$sQuery = "
				SELECT 
					id,
					name,
					is_default
				FROM {$db_name_system}.filters 
				WHERE to_arc = 0
					AND report_class = {$db_system->Quote($sReportClass)}
					AND id_person = {$nIDPerson}
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getFiltersByReportClassForFlexCombo( $sReportClass, $nIDPerson )
		{
			global $db_system;
			
			$sQuery = "
				SELECT
					id,
					name AS label,
					is_default
				FROM
					filters
				WHERE
					to_arc = 0
					AND report_class = {$db_system->Quote($sReportClass)}
					AND id_person = {$nIDPerson}
			";
			
			return $this->select( $sQuery );
		}
		
		public function resetDefaults($sReportClass, $nIDPerson) {
			global $db_system, $db_name_system;
			
			$sQuery = "
				UPDATE {$db_name_system}.filters
				SET is_default = 0
				WHERE report_class = {$db_system->Quote($sReportClass)}
					AND id_person = {$nIDPerson}
			";
			
			$this->oDB->Execute($sQuery);
		}
		
		public function getAutoFilters() {
			
			$nWebServerTime = time();
			
			$sQuery = "
				SELECT
					*
				FROM filters
				WHERE is_auto = 1
					AND auto_start_date != '0000-00-00'
					AND {$nWebServerTime} > UNIX_TIMESTAMP(auto_start_date)
					AND to_arc = 0	
			";
			
			return $this->select($sQuery);
		}
		
		public function getByID($nID) {
			global $db_name_system;
			
			if ( empty($nID) || !is_numeric($nID) ) {
				return array();
			}
			
			$sQuery = "
				SELECT
					*
				FROM {$db_name_system}.filters
				WHERE id = {$nID}
			";
			
			return $this->selectOnce($sQuery);
		}	
	}

?>
<?php
	class DBFiltersEvents extends DBBase2 {
		
		function __construct() {
			global $db_system;
			
			parent::__construct($db_system, "isu_settings_events");
		}
		
		public function getSettings() {
			global $db_name_system;
			
			$sQuery = "
				SELECT
					*
				FROM {$db_name_system}.isu_settings_events
				WHERE to_arc = 0
				LIMIT 1
			";
			
			return $this->select($sQuery);
		}
		
		public function getSettingsAssoc() {
			global $db_name_system;
			
			$sQuery = "
				SELECT
					*
				FROM {$db_name_system}.isu_settings_events
				WHERE to_arc = 0
				LIMIT 1
			";
			
			return $this->selectOnce($sQuery);
		}
	}
?>
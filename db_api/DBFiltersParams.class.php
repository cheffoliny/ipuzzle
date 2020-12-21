<?php
	class DBFiltersParams extends DBBase2 {
		
		function __construct() {
			global $db_system;
			
			parent::__construct($db_system, "filters_params");
		}
		
		public function getParamsByIDFilter($nIDFilter) {
			global $db_name_system;
			
			$sQuery = "
				SELECT
					name,
					value
				FROM {$db_name_system}.filters_params
				WHERE id_filter = {$nIDFilter}
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function delParamsByIDFilter($nIDFilter) {
			global $db_name_system;
			
			$sQuery = "
				DELETE
				FROM {$db_name_system}.filters_params
				WHERE id_filter = {$nIDFilter}
			";
			
			$this->oDB->Execute($sQuery);
		}
	}

?>
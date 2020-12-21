<?php
	class DBTechSupportRequestsFilters
		extends DBBase2 {
			
		public function __construct() {
			global $db_sod;
			//$db_sod->debug=true;
			
			parent::__construct($db_sod, 'tech_support_requests_filters');
		}
		
		public function getFiltersByIDPerson($nIDPerson) {
			
			$sQuery = "
				SELECT 
					id,
					name,
					visible_columns,
					is_default
				FROM tech_support_requests_filters
				WHERE 
					to_arc = 0
					AND id_person = {$nIDPerson}
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function resetDefaults($nIDPerson) {
			
			$sQuery = "
				UPDATE
					tech_support_requests_filters
				SET is_default = 0
				WHERE id_person = {$nIDPerson}
			";
			
			$this->oDB->Execute($sQuery);
		}
	}
?>
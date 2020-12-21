<?php

	class DBFiltersVisibleFields extends DBBase2 {
		
		function __construct() {
			global $db_system;
			
			parent::__construct($db_system,'filters_visible_fields');
		}
		
		public function getFieldsByIDFilter($nIDFilter) {
			
			$sQuery = "
				SELECT
					id,
					field_name
				FROM filters_visible_fields
				WHERE id_filter = {$nIDFilter}
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function delByIDFilter($nIDFilter) {
			
			$sQuery = "
				DELETE
				FROM filters_visible_fields
				WHERE id_filter = {$nIDFilter}
			";
			
			$this->oDB->Execute($sQuery);
		}
	}

?>
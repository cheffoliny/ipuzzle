<?php

	class DBFiltersTotals extends DBBase2 {
		
		function __construct() {
			global $db_system;
			
			parent::__construct($db_system,'filters_totals');
		}
		
		public function getFilterTotalsByIDFilter($nIDFilter) {
			
			$sQuery = "
				SELECT
					id,
					total_name
				FROM filters_totals
				WHERE id_filter = {$nIDFilter}
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function delFilterTotalsByIDFilter($nIDFilter) {
			$sQuery = "
				DELETE
				FROM filters_totals
				WHERE id_filter = {$nIDFilter}
			";
			
			$this->oDB->Execute($sQuery);
		}
		
	}

?>
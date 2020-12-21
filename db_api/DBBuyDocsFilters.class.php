<?php

	class DBBuyDocsFilters extends DBBase2 {
		
		public function __construct() {
			global $db_finance;
			parent::__construct($db_finance,'buy_docs_filters');
		}
		
		public function getFiltersNames($nIDPerson) {
			
			$sQuery = "
				SELECT
					id,
					is_default,
					name
				FROM buy_docs_filters
				WHERE to_arc = 0
					AND id_person = {$nIDPerson}
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function resetDefaults($nIDPerson) {
			
			$sQuery = "
				UPDATE buy_docs_filters
					SET is_default = 0
				WHERE 
					id_person = {$nIDPerson}
			";
			
			$this->oDB->Execute($sQuery);
		}
		
		public function getAutoFilters() {
			
			$sQuery = "
				SELECT
					*
				FROM buy_docs_filters
				WHERE to_arc = 0
					AND is_auto = 1			
			";
			
			return $this->select($sQuery);
		}
	}

?>
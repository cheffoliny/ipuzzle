<?php

	class DBStatesFilters extends DBBase2 {
		public function __construct() {
			
			global $db_storage;
			
			parent::__construct( $db_storage, 'states_filters' );
		}
		
		public function getFiltersByIDPerson($nIDPerson) {
			
			$sQuery = "
			
				SELECT
					*
				FROM states_filters
				WHERE 1
					AND to_arc = 0	
					AND id_person = {$nIDPerson}
			";
			
			return $this->select($sQuery);
		}
		
		public function makeNotDefault($nIDPerson) {
			$sQuery = "
				UPDATE
					states_filters
				SET is_default = 0
				WHERE id_person = {$nIDPerson}
			";
			
			$this->oDB->Execute($sQuery);
		}
		
		public function getAutoFilters() {
			$sQuery = "
				SELECT 
					*
				FROM states_filters
				WHERE 1
					AND to_arc = 0
					AND auto = 1
					AND UNIX_TIMESTAMP(auto_start_date) < UNIX_TIMESTAMP(now())
					AND UNIX_TIMESTAMP(auto_start_date) != 0				
			";
			
			return $this->select($sQuery);
		}
		
		public function getAutoFiltersByIDPerson($nIDPerson) {
			$sQuery = "
				SELECT 
					id,
					name
				FROM states_filters
				WHERE 1
					AND id_person = {$nIDPerson}
					AND auto = 1  
					AND to_arc = 0
					
			";
			
			return $this->selectAssoc($sQuery);
		}
	}
?>
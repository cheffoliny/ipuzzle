<?php

	class DBClientsObjects extends DBBase2 {
		
		public function __construct() {
			global $db_sod;
			parent::__construct($db_sod,'clients_objects');
			
		}
		
		public function getIDClientByIDObject($nIDObject) {
			
			$sQuery = "
				SELECT
					id_client
				FROM clients_objects
				WHERE id_object = {$nIDObject}
					AND to_arc = 0
			";
			
			return $this->selectOne($sQuery);
			
		}
		
	}

?>
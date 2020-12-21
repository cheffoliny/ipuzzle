<?php
	class DBMovementSchemes extends DBBase2 
	{
		public function __construct()
		{
			global $db_sod;
			
			parent::__construct($db_sod, 'movement_schemes');
		}
		
		public function getSchemes() {
			$nID = $_SESSION['userdata']['id_person'];
			
			$sQuery = "
				SELECT
					id,
					name,
					def
				FROM movement_schemes
				WHERE 1
					AND to_arc = 0
				 	AND updated_user = {$nID}
			";
			
			return $this->selectAssoc($sQuery);			
			
		}
		public function eraseDefaults() {
			$nID = $_SESSION['userdata']['id_person'];
			
			$sQuery = "
				UPDATE
					movement_schemes
				SET def = 0
				WHERE 1
					AND to_arc = 0
				 	AND updated_user = {$nID}
			";
			
			$this->oDB->Execute($sQuery);
			
		}
	}
?>
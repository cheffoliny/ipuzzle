<?php
	class DBLastId extends DBBase2 	{
		public function __construct() {
			global $db_telepol;
			parent::__construct($db_telepol, 'lastid');
		}
		
		public function getServicesLastId() {
			
			$sQuery = "
				SELECT
					service
				FROM lastid
			";
			return $this->selectOne($sQuery);
		}
		public function plusplusServicesLastId() {
			
			$sQuery = "
				UPDATE
					lastid
				SET service = service + 1
			";
			
			$this->oDB->Execute($sQuery);
		}
	}
?>
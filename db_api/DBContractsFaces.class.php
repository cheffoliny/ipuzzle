<?php

	class DBContractsFaces extends DBBase2 {
		public function __construct() {
			global $db_finance;
			parent::__construct($db_finance, "contracts_faces");
		}	
				
		public function getFaces( $nID ) {	
			$sQuery = "
				SELECT 
					cf.name,
					cf.phone,
					cf.post
				FROM contracts_faces cf
				WHERE 1 
					AND cf.id_contract = {$nID}
			";

			return $this->select($sQuery);
		}
	}
?>
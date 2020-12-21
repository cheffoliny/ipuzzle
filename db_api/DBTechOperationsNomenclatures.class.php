<?php

	class DBTechOperationsNomenclatures extends DBBase2 {
		
		public function __construct() {
			global $db_sod;
			$db_sod->debug=true;
			parent::__construct($db_sod,'tech_operations_nomenclatures');
			
		}
		
		
		public function getNomenclaturesByIdOperation($nIDOperation) {
			
			$sQuery = "
				SELECT 
					id,
					id_nomenclature
				FROM tech_operations_nomenclatures
				WHERE 1
					AND to_arc = 0
					AND id_operation = {$nIDOperation}
			";
			
			return $this->selectAssoc($sQuery);
		}
	}

?>
<?php
	class DBSchemeElements
		extends DBBase2 {
			
		public function __construct() {
			global $db_storage;
			//$db_sod->debug=true;
			
			parent::__construct($db_storage, 'scheme_elements');
		}	
		public function toArc($id)
		{
			$sQuery = "
				UPDATE scheme_elements
				SET
					to_arc = 1
				WHERE id_scheme = {$id}
			";
			$this->oDB->Execute($sQuery);
		}
		
		public function getSchemeElements($nIDScheme) {
			
			$sQuery = "
				SELECT 
					se.id_nomenclature,
					se.count,
					n.last_price
				FROM scheme_elements se
				LEFT JOIN nomenclatures n ON n.id = se.id_nomenclature 
				WHERE 1
					AND se.to_arc = 0
					AND se.id_scheme = {$nIDScheme}
			";
			
			return $this->select($sQuery);
		}
	}
?>
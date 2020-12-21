<?php

	class DBServices extends DBBase2 {
		public function __construct() {
			global $db_telepol;
			parent::__construct($db_telepol, "services");
		}	
		
		public function getTaxesByIdObject($nID) {
			
			$sQuery = "
				SELECT 
					ps.name,
					s.price
				FROM services s
				LEFT JOIN prihodi_slave ps ON ps.id_slave = s.id_type
				WHERE id_obj = {$nID} AND s.price != 0
			
			";
			
			return $this->select($sQuery);
		}
		
	}
?>
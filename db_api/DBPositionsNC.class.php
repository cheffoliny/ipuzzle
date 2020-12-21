<?php
	class DBPositionsNC 
		extends DBBase2 {
		public function __construct() {
			global $db_personnel;
			//$db_personnel->debug=true;
			parent::__construct($db_personnel, "positions_nc");
		}
		
		public function getPositionsNC (){
			$sQuery = "
				SELECT 
					id,
					name
				FROM positions_nc 
				WHERE 
					to_arc = 0
				ORDER BY name
			";
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getPersonMinSalary ($nIDPerson) {
			$sQuery = "
				SELECT
					pnc.min_salary
				FROM positions_nc pnc
				LEFT JOIN personnel p			ON	p.id_position_nc = pnc.id
				LEFT JOIN person_contract pc	ON	pc.id_person = p.id AND pc.to_arc = 0
				WHERE 1
					AND p.to_arc = 0
					AND p.id	 = {$nIDPerson}
			";
			return  $this->selectOne( $sQuery );
		}
		
		public function getPositions() {
			global $db_name_personnel;
			
			$sQuery = "
				SELECT 
					id,
					name
				FROM {$db_name_personnel}.positions_nc 
				WHERE to_arc = 0
				ORDER BY name
			";
			
			return $this->select( $sQuery );
		}
	}
?>
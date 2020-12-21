<?php
	class DBRegions2 extends DBBase2 	{
		public function __construct() {
			global $db_telepol;
			parent::__construct($db_telepol, 'regions');
		}
		
		public function getIDRegion( $nID ) {
			
			$sQuery = "
				SELECT
					id_region
				FROM
					regions
				WHERE telenet_id_office = {$nID}
			";

			return $this->selectOne($sQuery);
		}
	}
?>
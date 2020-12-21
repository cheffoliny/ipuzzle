<?php
	class DBFuelLists
		extends DBBase2 {
			
		public function __construct() {
			global $db_auto;
			//$db_sod->debug=true;
			
			parent::__construct($db_auto, 'fuel_lists');
		}
				
		public function getFuelList( $nID ) {
			global $db_name_personnel;
			
			$nID = (int) $nID;

			$sQuery = "
				SELECT * FROM fuel_lists
				WHERE 1
					AND id = {$nID}			
			";
			
			return $this->selectOnce( $sQuery );
		}
		
	}
	
?>
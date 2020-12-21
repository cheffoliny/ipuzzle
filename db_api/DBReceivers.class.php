<?php
	class DBReceivers
		extends DBBase2 {
			
		public function __construct() {
			global $db_sod;
			//$db_sod->debug=true;
			
			parent::__construct($db_sod, 'receivers');
		}

		public function getReceives() {
			
			$sQuery = "
				SELECT 
					id,
					name
				FROM receivers 
				WHERE to_arc = 0
			";
			
			return $this->select( $sQuery );
		}

		public function getReceiversByObj( $nIDObj ) {
			
			$sQuery = "
				SELECT 
					r.name
				FROM receivers r
				WHERE 1
				
			";
			
			return $this->select( $sQuery );
		}

		public function resetDefaults() {
			global $db_personnel;
			
			$nID = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
						
			$sQuery = "
				UPDATE visible_tabs
				SET def = 0
				WHERE id_person = {$nID}
					AND to_arc = 0
			";
			
			$db_personnel->Execute( $sQuery );
		}

	}
?>
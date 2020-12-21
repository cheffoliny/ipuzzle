<?php
	class DBVisibleTabs
		extends DBBase2 {
			
		public function __construct() {
			global $db_personnel;
			$db_personnel->debug=true;
			
			parent::__construct($db_personnel, 'visible_tabs');
		}

		public function getTabsByPerson( $nID ) {
			if ( empty($nID) || !is_numeric($nID) ) {
				return 0;
			}
			
			$sQuery = "
				SELECT 
					id,
					name,
					def
				FROM visible_tabs 
				WHERE id_person = {$nID}
					AND to_arc = 0
			";
			
			return $this->selectAssoc( $sQuery );
		}

		public function getTabsByID( $nID ) {
			if ( empty($nID) || !is_numeric($nID) ) {
				return 0;
			}
			
			$sQuery = "
				SELECT 
					id,
					name,
					data,
					id_person,
					def
				FROM visible_tabs 
				WHERE id = {$nID}
					AND to_arc = 0
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
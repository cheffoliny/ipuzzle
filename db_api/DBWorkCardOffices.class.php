<?php
	class DBWorkCardOffices
		extends DBBase2 {
			
		public function __construct() {
			global $db_sod;
			
			parent::__construct($db_sod, 'work_card_offices');
		}
				
		public function getWorkCardOffices( $nID ) {
			global $db_name_personnel;
			
			$nID = (int) $nID;

			$sAccessRegions = implode(',',$_SESSION['userdata']['access_right_regions']);
			
			$sQuery = "
				SELECT 
					wco.id_office AS id,
					CONCAT('[',f.name,'] ',of.name) AS name
				
				FROM work_card_offices wco
				LEFT JOIN offices of ON of.id = wco.id_office
				LEFT JOIN firms f ON of.id_firm = f.id
				WHERE 1 
					AND id_work_card = {$nID}
					AND of.id IN ({$sAccessRegions})
				ORDER BY of.name
			";
			
			return $this->selectAssoc( $sQuery );
		}

		public function getWorkCardOffices2( $nID ) {
			global $db_name_personnel;
			
			$nID = (int) $nID;
			
			$sAccessRegions = implode(',',$_SESSION['userdata']['access_right_regions']);

			$sQuery = "
				SELECT 
					GROUP_CONCAT(id_office)
				FROM work_card_offices
				WHERE 1
					AND id_work_card = {$nID}
					AND id_office IN ({$sAccessRegions})
				GROUP BY id_work_card
			";
			return $this->selectOne( $sQuery );
		}
				
	}
	
?>
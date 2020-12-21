<?php

	class DBStoragehousesMols extends DBBase2
	{
		public function __construct()
		{
			global $db_storage;
			
			parent::__construct( $db_storage, "storagehouses_mols" );
		}
		
		public function getAllPersons( $nID )
		{
			global $db_name_personnel;
			
			if( empty( $nID ) || !is_numeric( $nID ) )
				return array();
			
			$sQuery = "
					SELECT
						p.id AS id,
						CONCAT_WS( ' ', p.fname, p.mname, p.lname ) AS name
					FROM storagehouses_mols s
						LEFT JOIN {$db_name_personnel}.personnel p ON p.id = s.id_person
						LEFT JOIN storagehouses st ON st.id = s.id_storagehouse
					WHERE 1
						AND st.id = {$nID}
						AND s.to_arc = 0
						AND p.to_arc = 0
			";
			
			return $this->select( $sQuery );
		}
		
		public function updateMols( $nIDStoragehouse, $sMOLList )
		{
			if( !empty( $nIDStoragehouse ) && is_numeric( $nIDStoragehouse ) )
			{
				$sQuery = "
						DELETE FROM storagehouses_mols
						WHERE id_storagehouse = {$nIDStoragehouse}
				";
				
				$this->select( $sQuery );
				
				if( !empty( $sMOLList ) )
				{
					$aPersonList = split( ",", $sMOLList );
					foreach( $aPersonList as $nIDMol )
					{
						$aData = array();
						
						$aData['id_storagehouse'] = $nIDStoragehouse;
						$aData['id_person'] = $nIDMol;
						
						$this->update( $aData );
					}
				}
			}
		}
	}

?>
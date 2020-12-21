<?php

	class DBStoragehousesTypes extends DBBase2
	{
		public function __construct()
		{
			global $db_storage;
			
			parent::__construct( $db_storage, "storagehouses_types" );
		}
		
		public function getTypesAssoc()
		{
			$sQuery = "
				SELECT
					id,
					description AS name
				FROM
					storagehouses_types
				WHERE
					to_arc = 0
					AND is_storagehouse = 1
			";
			
			return $this->selectAssoc( $sQuery );
		}
	}

?>
<?php

	class DBSignalSectors extends DBBase2
	{
		public function __construct()
		{
			global $db_sod;
			
			parent::__construct($db_sod, "objects_sectors");
		}	
		
		public function getSectors()
		{
			$sQuery = "
				SELECT 
				id,
				name
				FROM objects_sectors
				WHERE to_arc = 0
				ORDER BY sector ASC
				";
			
			return $this->select( $sQuery );
		}


        public function getSectorsByID( $nID )
        {
            $sQuery = "
				SELECT
				id,
				id_object,
				name,
				sector
				FROM objects_sectors
				WHERE id = {$nID}
				";

            return $this->select( $sQuery );
        }

		public function getSectorsByIDObjects( $nID )
		{
			$sQuery = "
				SELECT 
				id,
				name,
				sector
				FROM objects_sectors
				WHERE id_object = {$nID} AND to_arc = 0
				ORDER BY name
				";
			
			return $this->select( $sQuery );
		}
		
		public function getNamesByIDObjects( $nID )
		{
			$sQuery = "
				SELECT 
				id,
				name
				FROM objects_sectors
				WHERE id_object = {$nID}
				";
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getReport( $nID, DBResponse $oResponse )
		{
            global $db_name_personnel;

            $right_edit = false;

            if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
                if ( in_array('object_messages_edit', $_SESSION['userdata']['access_right_levels']) ) {
                    $right_edit = true;
                }
            }
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						os.id,
						os.name,
						os.sector,
						IF
						(
							p.id,
							CONCAT(
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
								' (',
								DATE_FORMAT( os.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
								),
							''
						) AS updated_user
						FROM objects_sectors os
							LEFT JOIN {$db_name_personnel}.personnel p ON os.updated_user = p.id
						WHERE 1
							AND os.to_arc = 0 AND os.id_object = {$nID}
			";
            //APILog::log(0, $sQuery);
			
			$this->getResult( $sQuery, 'name', DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField( "name", 			"Име", 		"Сортирай по наименование" );
			$oResponse->setField( "sector", 		"Сектор", 		"Сортирай по номер на сектор" );
            $oResponse->setField( "updated_user", 	"Последна редакция", 	"Сортирай по последна редакция" );
			
			if( $right_edit )
			{
				$oResponse->setField( '', '', '', 'images/cancel', 'deleteSector', '' );
				$oResponse->setFieldLink( "name", "editSector" );
			}
		}
	}
?>
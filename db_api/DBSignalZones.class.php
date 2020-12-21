<?php

	class DBSignalZones extends DBBase2
	{
		public function __construct()
		{
			global $db_sod;
			
			parent::__construct($db_sod, "objects_zones");
		}	
		
		public function getZones()
		{
			$sQuery = "
				SELECT 
				id,
				name
				FROM objects_zones
				WHERE to_arc = 0
				ORDER BY zone ASC
				";
			
			return $this->select( $sQuery );
		}


        public function getZonesByID( $nID )
        {
            $sQuery = "
				SELECT
				id,
				id_object,
				name,
				zone
				FROM objects_zones
				WHERE id = {$nID}
				";

            return $this->select( $sQuery );
        }

		public function getZonesByIDObjects( $nID )
		{
			$sQuery = "
				SELECT 
				id,
				name,
				zone
				FROM objects_zones
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
				FROM objects_zones
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
						oz.id,
						oz.name,
						oz.zone AS 'zone',
						IF
						(
							p.id,
							CONCAT(
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
								' (',
								DATE_FORMAT( oz.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
								),
							''
						) AS updated_user
						FROM objects_zones oz
							LEFT JOIN {$db_name_personnel}.personnel p ON oz.updated_user = p.id
						WHERE 1
							AND oz.to_arc = 0 AND oz.id_object = {$nID}
			";
            APILog::log(0, $sQuery);
			
			$this->getResult( $sQuery, 'name', DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField( "name", 			"Име", 		"Сортирай по наименование" );
			$oResponse->setField( "zone", 			"Зона", 		"Сортирай по номер на зона" );
            $oResponse->setField( "updated_user", 	"Последна редакция", 	"Сортирай по последна редакция" );
			
			if( $right_edit )
			{
				$oResponse->setField( '', '', '', 'images/cancel', 'deleteZone', '' );
				$oResponse->setFieldLink( "name", "editZone" );
			}
		}
	}
?>
<?php

	class DBCityAreas
		extends DBBase2 
	{
		public function __construct()
		{
			global $db_sod;
			
			parent::__construct($db_sod, "city_areas");
		}	
		
		public function getAreas()
		{
			$sQuery = "
				SELECT 
				id,
				name
				FROM city_areas 
				WHERE to_arc = 0
				ORDER BY name
				";
			
			return $this->select( $sQuery );
		}
		
		public function getAreasByCity( $nID )
		{
			$sQuery = "
				SELECT 
				id,
				name
				FROM city_areas 
				WHERE id_city = {$nID} AND to_arc = 0
				ORDER BY name
				";
			
			return $this->select( $sQuery );
		}
		
		public function getNamesByIDCity( $nID )
		{
			$sQuery = "
				SELECT 
				id,
				name
				FROM city_areas 
				WHERE id_city = {$nID} 
				";
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
				if( in_array( 'city_areas', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						c.id,
						c.name,
						city.name as city,
						IF
						(
							p.id,
							CONCAT(
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
								' (',
								DATE_FORMAT( c.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
								),
							''
						) AS updated_user
						FROM city_areas c
							LEFT JOIN {$db_name_personnel}.personnel p ON c.updated_user = p.id
							LEFT JOIN cities city ON c.id_city = city.id
						WHERE 1
							AND c.to_arc = 0
							AND city.to_arc = 0
			";
			
			if( isset( $aParams['nIDCity'] ) && !empty( $aParams['nIDCity'] ) )
			{
				$sQuery .= "
							AND c.id_city = {$aParams['nIDCity']}
				";
			}
			
			$this->getResult( $sQuery, 'name', DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField( "name", 			"Наименование", 		"Сортирай по наименование" );
			$oResponse->setField( "city", 			"Населено място", 		"Сортирай по населено място" );
			$oResponse->setField( "updated_user", 	"Последна редакция", 	"Сортирай по последна редакция" );
			
			if( $right_edit )
			{
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'deleteArea', '' );
				$oResponse->setFieldLink( "name", "setupArea" );
			}
		}
	}
?>
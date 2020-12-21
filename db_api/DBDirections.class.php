<?php
	class DBDirections extends DBBase2 
	{
		public function __construct()
		{
			global $db_sod;
			
			parent::__construct( $db_sod, "directions" );
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$right_edit = false;
			if( !empty( $_SESSION["userdata"]["access_right_levels"] ) )
			{
				if( in_array( "setup_directions", $_SESSION["userdata"]["access_right_levels"] ) )
				{
					$right_edit = true;
				}
			}
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					dir.id AS id,
					dir.name AS name,
					IF
					(
						p.id,
						CONCAT(
							CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
							' (',
							DATE_FORMAT( dir.updated_time, '%d.%m.%Y %H:%i:%s' ),
							')'
						),
						''
					) AS updated_user
				FROM
					directions dir
				LEFT JOIN
					{$db_name_personnel}.personnel p ON dir.updated_user = p.id
				WHERE
					dir.to_arc=0
			";
			
			$this->getResult( $sQuery, "name", DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField( "name", 			"Наименование", 		"Сортирай по наименование" );
			$oResponse->setField( "updated_user", 	"Последна редакция", 	"Сортирай по последна редакция" );
			
			if( $right_edit )
			{
				$oResponse->setField( "", "", "", "images/cancel.gif", "deleteDirection", "" );
				$oResponse->setFieldLink( "name", "openDirection" );
			}
		}
		
		public function getDirections()
		{
			$sQuery = "
				SELECT
					dir.id,
					dir.name
				FROM
					directions dir
				WHERE
					dir.to_arc = 0
				ORDER BY
					name
			";
			
			return $this->select( $sQuery );
		}
		
		/**
		 * Функцията връща всички направления със съответното id на офис
		 * 
		 * @author Павел Петров
		 * @name getRegionDirections
		 * 
		 * @return array масив с направленията
		 */			
		public function getRegionDirections() {
			global $db_name_sod;
			
			$sQuery = "
				SELECT 
					od.id_direction as id,
					od.id_office as id_office,
					d.name  as name
				FROM {$db_name_sod}.offices_directions od
				LEFT JOIN {$db_name_sod}.directions d ON ( d.id = od.id_direction AND d.to_arc = 0 )
				
				ORDER BY d.name
			";
			//GROUP BY d.id
			return $this->select( $sQuery );
		}
		
		/**
		 * Функцията връща всички направления (без повторения) със съответното id на офис
		 * 
		 * @author Румен Пенчев
		 * @name getRegionDirectionsGrouped
		 * 
		 * @return array масив с направленията
		 */			
		public function getRegionDirectionsGrouped() {
			global $db_name_sod;
			
			$sQuery = "
				SELECT 
					od.id_direction as id,
					od.id_office as id_office,
					d.name  as name
				FROM {$db_name_sod}.offices_directions od
				LEFT JOIN {$db_name_sod}.directions d ON ( d.id = od.id_direction AND d.to_arc = 0 )
				GROUP BY od.id_direction
				ORDER BY d.name
			";
			return $this->select( $sQuery );
		}
		
		/**
		 * Функцията връща всички направления (без повторения) за избрана фирма и офис със съответното id на офис
		 * 
		 * @author Румен Пенчев
		 * @name getDirectionsByFirmRegionGrouped
		 * 
		 * @return array масив с направленията
		 */	
		public function getDirectionsByFirmRegionGrouped($id_firm, $id_office) {
			global $db_name_sod;
			
			$sQuery = "
				SELECT 
					od.id_direction as id,
					od.id_office as id_office,
					d.name as name,
					off.id_firm
				FROM sod.offices_directions od
				LEFT JOIN {$db_name_sod}.directions d ON ( d.id = od.id_direction AND d.to_arc = 0 )
				LEFT JOIN {$db_name_sod}.offices off ON ( od.id_office = off.id AND off.to_arc = 0)
				WHERE 1 ";
			
			if (!empty($id_office)) {
				$sQuery .= "	and (id_office = {$id_office})";
			} else if (!empty($id_firm)) {
				$sQuery .= "	and (id_firm = {$id_firm})";
			}

			$sQuery .= "
				GROUP BY od.id_direction
				ORDER BY d.name
			";
			return $this->select( $sQuery );
		}
		
	}
?>
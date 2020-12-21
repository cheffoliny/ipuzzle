<?php

	class DBCities
		extends DBBase2 
	{
		public function __construct()
		{
			global $db_sod;
			
			parent::__construct($db_sod, "cities");
		}	
		
		public function getNameByID( $nID )
		{
			$sQuery = "
				SELECT 
				name
				FROM cities 
				WHERE id = {$nID} 
				";
			
			return $this->selectOne( $sQuery );
		}
		
		public function getCities() {
			
			$sQuery = "
				SELECT 
					id,
					name
				FROM cities 
				WHERE 1
				AND to_arc = 0
				AND zone = 1
				ORDER BY name
			";
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getCities2()
		{
			//Accessable Regions
			if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
			{
				$sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
				$sCondition = " AND id_office IN ({$sAccessable}) \n";
			}
			else $sCondition = "";
			//End Accessable Regions
			
			$sQuery = "
					SELECT 
						id,
						name,
						post_code
					FROM cities 
					WHERE to_arc = 0
						AND id_office != 0
						{$sCondition}
					ORDER BY name
				";
			
			return $this->select( $sQuery );
		}
		
		public function getContractCitiesByContractStatus( $sContractStatus) {
			global $db_name_finance,$db_name_sod;
			
			$sQuery = "
				SELECT
					c.id,
					c.name
				FROM {$db_name_sod}.cities c
				LEFT JOIN {$db_name_sod}.offices off ON off.address_city = c.id
				LEFT JOIN {$db_name_finance}.contracts con ON con.id_office = off.id
				WHERE 1 
			";
			
			if (!empty($sContractStatus)) {
				$sQuery .= " AND con.contract_status = '{$sContractStatus}' ";
			}
			
			$sQuery .= "
				GROUP BY c.id
				ORDER BY c.name
			 ";
			
			return $this -> selectAssoc($sQuery);			
		}
		
		public function getCitiesWithIDOffice() {
			
			$sAccessRegions = implode(',',$_SESSION['userdata']['access_right_regions']);
			
			$sQuery = "
				SELECT
					c.id,
					c.name
				FROM cities c
				LEFT JOIN offices off ON off.id = c.id_office
				WHERE 1
					AND c.to_arc = 0
					AND	c.id_office IN ({$sAccessRegions})
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$right_edit = false;
			if( !empty($_SESSION['userdata']['access_right_levels'] ) )
				if( in_array('auto_marks_edit', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}

			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						c.id, 
						c.name, 
						c.post_code, 
						IF( 
						p.id, 
						CONCAT(
							CONCAT_WS(' ', p.fname, p.mname, p.lname),
							' (',
							DATE_FORMAT( c.updated_time, '%d.%m.%Y %H:%i:%s' ),
							')'
							),
							''
							) AS updated_user
						FROM cities c
						LEFT JOIN {$db_name_personnel}.personnel p ON c.updated_user = p.id
						WHERE c.to_arc=0
					";
			
			$this->getResult($sQuery, 'name', DBAPI_SORT_ASC, $oResponse);
			
			$oResponse->setField("post_code", 		"Пощенски Код", 		"Сортирай по Пощенски Код");
			$oResponse->setField("name", 			"Име", 					"Сортирай по Име");
			$oResponse->setField("updated_user", 	"Последна редакция", 	"Сортирай по Последна Редакция");
			
			if( $right_edit )
			{
				$oResponse->setField( '', '', '', 'images/cancel', 'deleteCity', '' );
				$oResponse->setFieldLink( "name", "openCity" );
			}
		}
		
	}
?>
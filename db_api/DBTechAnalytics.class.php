<?php
 	class DBTechAnalytics extends DBBase2 {
		
		public function __construct() {
			global $db_sod;
			
			parent::__construct($db_sod, "objects");
		}	
		
		
		public function getReport( DBResponse $oResponse )
		{	 		

			global $db_name_sod;
			
			$aParams = Params::getAll();
			
			$nIDObject = isset( $aParams['nIDObject'] ) ? $aParams['nIDObject'] : 0;
			$nOfficeID = isset( $aParams['nIDOffice'] ) ? $aParams['nIDOffice'] : 0;
			
			$sQuery = "
				SELECT 
					SQL_CALC_FOUND_ROWS
						o.id		AS id,
						o.num		AS num,
						o.name		AS name,
						o.address	AS address,
						o.geo_lat	AS lat,
						o.geo_lan	AS lon,
						o.confirmed	AS confirmed
						
				FROM
						{$db_name_sod}.objects o
					WHERE
						o.id_office = {$nOfficeID}
					AND
						o.id = {$nIDObject}
					LIMIT 1
			";
		
			$aRes = $this->selectOnce( $sQuery );
			return( $aRes );
		
		}
		

		public function getUnconfirmedObjects( DBResponse $oResponse )
		{
			global $db_name_sod;
			
			$nConfirmed = 0;
			
			$aParams = Params::getAll();
			
			$office_id = isset( $aParams['nIDOffices'] )  ? $aParams['nIDOffices']  : 0; 			
		 
			$oResponse->setField( "num", "Име", "Сортирай по номер" );
			$oResponse->setField( "name", "Име", "Сортирай по име", NULL, 'loadObject' );
			$oResponse->setField( "address", "Адрес" );
		 	
			if ($aParams['nConfirmed'] == 1) {
				$nConfirmed = 1;				
			}
			else 
				$nConfirmed = 0;
			$nStatus = $aParams['nStatus'];
					
			$sQuery = "
			
				SELECT 
					SQL_CALC_FOUND_ROWS
						o.id		AS id,
						o.num		AS num,
						o.name		AS name,
						o.address,
						o.geo_lat 	AS lat,
						o.geo_lan 	AS lon
	
					FROM sod.objects o
					LEFT JOIN statuses s ON s.id = o.id_status
					WHERE 
							o.confirmed = {$nConfirmed}
					AND
							o.id_office = {$office_id}
					AND		
							s.id = {$nStatus}
				
	
			
			";
			
			if( isset( $aParams['sObjectName'] ) && !empty( $aParams['sObjectName'] ) )
			{
				$sQuery .= "
					AND ( o.name LIKE  '%{$aParams['sObjectName']}%'  )
				";
			}
			
			 
			
			$this->getResult( $sQuery, 'id', 1, $oResponse );
			 
			
		}
		
		
		public function getOffices()
		{
			global $db_name_sod;
			
			$aParams = Params::getAll();
			
			$firm_id = isset( $aParams['nIDFirms'] )  ? $aParams['nIDFirms']  : 0; 			
			
			$sQuery = "
				SELECT 
					SQL_CALC_FOUND_ROWS
						o.id		AS	id,
						o.name		AS	name
			
					FROM {$db_name_sod}.offices o
	
					WHERE
							to_arc = 0
						AND
							id_firm = {$firm_id}
				
			";
			
					
			return $this->selectAssoc( $sQuery );	
			
		}
		
		
		
		public function getFirms()
		{
			global $db_name_sod;
			
			$sQuery = "
				SELECT 
					SQL_CALC_FOUND_ROWS
						f.id		AS	id,
						f.name		AS	name
			
					FROM  {$db_name_sod}.firms f
	
					WHERE
							f.to_arc = 0
				
			";
			
			
			
			return $this->selectAssoc( $sQuery );	
			
		}
		
		
		
	}
	
?>
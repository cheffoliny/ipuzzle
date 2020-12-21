<?php

	class DBAssetsStoragehouses extends DBBase2
	{
		public function __construct()
		{
			global $db_storage;
			//$db_storage->debug=true;
			parent::__construct( $db_storage, 'assets_storagehouses' );
		}
		
		public function getAssetsStoragehouses() {
			
			$sQuery = "
				SELECT
					id,
					name
				FROM assets_storagehouses
				WHERE to_arc = 0
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getAssetsStoragehouses2() {
			
			$sQuery = "
				SELECT
					id,
					name
				FROM assets_storagehouses
				WHERE to_arc = 0
				ORDER BY name
			";
			
			return $this->select($sQuery);
		}
		
		public function getMOL( $nID )
		{
			global $db_name_personnel;
			
			if( empty( $nID ) || !is_numeric( $nID ) )return array();
			
			$sQuery = "
					SELECT
						p.id,
						CONCAT_WS( ' ', p.fname, p.mname, p.lname ) AS names
					FROM assets_storagehouses AS a
						LEFT JOIN {$db_name_personnel}.personnel AS p ON a.id_mol = p.id
					WHERE a.id = {$nID}
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		/**
		 * Функция която взима ID и съобразено с мола връща името на склада мола а също и id на офиса
		 * param nID - ID на конкретния ред 
		 * 
		 * @return unknown
		 */
		public function getFirmIDByMOL($nID)
		{
			global $db_name_personnel;
			
			$sQuery = "
				SELECT
					a.id,
					a.name,
					a.id_mol,
					p.id_office
				FROM assets_storagehouses as a
				LEFT JOIN {$db_name_personnel}.personnel as p on a.id_mol=p.id
				WHERE a.id='".$nID."'
			";
	
			return $this->selectOnce($sQuery);
		}
		
		public function getREPORT(DBResponse $oResponse)
		{
			global $db_name_personnel;
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS 
					a.id,
					a.name,
					a.id_mol,
					CONCAT(p.fname,' ', p.mname,' ', p.lname) as MOL
				FROM assets_storagehouses a
				LEFT JOIN {$db_name_personnel}.personnel p 
				ON (a.id_mol=p.id AND p.to_arc = 0)
				WHERE a.to_arc = 0
	 		";
			
		$this->getResult( $sQuery, 'id', DBAPI_SORT_ASC, $oResponse );
		
		$oResponse->setField( "name","Име","Сортирай по име");
		$oResponse->setField( "MOL","МОЛ","Сортитай по МОЛ");
		$oResponse->setField( '', '', '', 'images/cancel.gif',	'delAssetsStoragehouse' , '' );
		$oResponse->setFieldLink("name","editStorageHouse");
		}
		
		
		
	}
	
?>
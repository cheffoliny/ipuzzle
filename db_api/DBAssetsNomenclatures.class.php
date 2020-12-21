<?php
	class DBAssetsNomenclatures extends DBBase2 
	{
		
		function __construct()
		{
			global $db_storage;
			//$db_storage->debug=true;
			parent::__construct( $db_storage, 'assets_nomenclatures' );
		}
		
		public function getNomenclaturesByGroup($nIdGroup)
		{
			
			$sQuery="
						SELECT 
						id,
						name
						FROM assets_nomenclatures
						WHERE 1
						AND id_group={$nIdGroup}
						AND to_arc=0";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getAssetsNomenclatures($nID) {
			
			$sQuery = "
				SELECT
					id,
					name,
					id_group
				FROM assets_nomenclatures
				WHERE to_arc = 0 AND id= $nID
			";
			return $this->selectOnce($sQuery);
		}
		
		
		public function getREPORT(DBResponse $oResponse)
		{
			global $db_name_storage;
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS 
					a.id,
					a.name,
					g.name as gr
				FROM assets_nomenclatures as a
				LEFT JOIN storage.assets_groups as g
				ON (a.id_group=g.id AND g.to_arc = 0)
				WHERE a.to_arc = 0
	 		";
			
		$this->getResult( $sQuery, 'id', DBAPI_SORT_ASC, $oResponse );
		
		$oResponse->setField( "name","Име","Сортирай по номенклатура");
		$oResponse->setField( "gr","Група","Сортитай по име на група");
		$oResponse->setField( '', '', '', 'images/cancel.gif',	'delAssetsNomenclatures' , '' );
		$oResponse->setFieldLink("name","editNomenclatures");
		}
		
		public function getGroup()
		{
			$sQuery ="
				SELECT
					id,
					name
					FROM storage.assets_groups
					WHERE to_arc = 0";
			return $this->selectAssoc($sQuery);
				
		}
		
		public function getDELETEfromAssetsNomenclaturesAttributes($nID)
		{
			$sQuery = 
				"
				 DELETE
				 FROM assets_nomenclatures_attributes
				 WHERE id_nomenclature= $nID ";
		$this->oDB->Execute($sQuery);		
		}
	}
?>
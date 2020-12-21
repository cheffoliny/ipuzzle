<?php

	class DBStoragehouses extends DBBase2
	{
		public function __construct()
		{
			global $db_storage;
			
			parent::__construct($db_storage, "storagehouses");
		}
		
		public function getStoragehousesByOffice( $nIDFirm, $nIDOffice, $sName, $nLimit )
		{
			global $db_name_sod;
//			if( empty( $nIDFirm ) || !is_numeric( $nIDFirm ) )
//			{
//				if( empty( $nIDOffice ) || !is_numeric( $nIDOffice ) )
//				{
//					$oOffices = new DBOffices();
//					$aOffices = $oOffices->getOffices2();
//					if( !empty( $aOffices ) )$nIDOffice = $aOffices[0]['id'];
//					else $nIDOffice = 0;
//				}
//			}
//			else
//			{
//				if( empty( $nIDOffice ) || !is_numeric( $nIDOffice ) )
//				{
//					$oOffices = new DBOffices();
//					$aOffices = $oOffices->getOfficesByFirm( $nIDFirm );
//					if( !empty( $aOffices ) )$nIDOffice = $aOffices[0]['id'];
//					else $nIDOffice = 0;
//				}
//			}
//			if( !is_numeric( $nIDOffice ) )
//			{
//				$nIDOffice = 0;
//			}
//			
//			$sQuery = "
//				SELECT * 
//				FROM storagehouses 
//				WHERE to_arc = 0
//				";
//			
//			if( !empty( $nIDOffice ) )$sQuery .= " AND id_office = {$nIDOffice} ";
//			
//			$sQuery .= " ORDER BY name ";
//			
//			return $this->select( $sQuery );
			if( empty( $nLimit ) || !is_numeric( $nLimit ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT 
					s.id,
					s.id AS num,
					s.name
				FROM storagehouses s
				LEFT JOIN {$db_name_sod}.offices o ON s.id_office = o.id
				LEFT JOIN {$db_name_sod}.firms f ON o.id_firm = f.id
				WHERE s.to_arc = 0 AND o.to_arc = 0 AND f.to_arc = 0
				";
			
			if( !empty( $nIDOffice ) )$sQuery .= " AND s.id_office = {$nIDOffice} ";
			if( !empty( $nIDFirm ) )$sQuery .= " AND f.id = {$nIDFirm} ";
			
			if( !empty( $sName ) )
			{
				if( is_numeric($sName) )$sQuery .= sprintf("AND s.id = '%s'\n", addslashes( $sName ) );
				if( !is_numeric($sName) )$sQuery .= sprintf("AND s.name LIKE '%%%s%%'\n", addslashes( $sName ) );
			}
			
			$sQuery .= "ORDER BY s.name\n";
			$sQuery .= "LIMIT {$nLimit}\n";
			
			return $this->select( $sQuery );
		}
		
		public function getByID( $nID )
		{
			if( empty( $nID ) || !is_numeric( $nID ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT * 
				FROM storagehouses 
				WHERE to_arc = 0
				AND id = {$nID} 
				LIMIT 1
				";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getTypeByID( $nID )
		{
			if( empty( $nID ) || !is_numeric( $nID ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
					SELECT
						type
					FROM storagehouses
					WHERE 1
						AND to_arc = 0
						AND id = {$nID}
					LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getByMOLID( $nID )
		{
			if( empty( $nID ) || !is_numeric( $nID ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT s.id as id_, s.* 
				FROM storagehouses s
				WHERE to_arc = 0
				AND mol_id_person = {$nID}
				OR {$nID} IN ( SELECT sm.id_person FROM storagehouses_mols sm WHERE sm.id_storagehouse = s.id )
				";
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getMOL( $nID )
		{
			global $db_name_personnel;
			
			if( empty( $nID ) || !is_numeric( $nID ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT
					s.id,
					s.name,
					p.id AS mol_id,
					CONCAT_WS( ' ', p.fname, p.mname, p.lname ) AS mol
				FROM storagehouses s
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = s.mol_id_person
				WHERE s.id = {$nID}
					AND s.to_arc = 0
					AND p.to_arc = 0
				LIMIT 1
				";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getStoragehouseByName( $sName )
		{
			if( empty( $sName ) ) return array();
			
			$sQuery = "
				SELECT * 
				FROM storagehouses 
				WHERE to_arc = 0
				AND name = {$this->oDB->Quote( $sName )} 
				LIMIT 1
				";
			
			return $this->selectOnce( $sQuery );
		}
		
		function getStoragehouseNameByName( $sName, $nLimit )
		{
			if( empty( $nLimit ) || !is_numeric( $nLimit ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT 
					id,
					id AS num,
					name
				FROM storagehouses
				WHERE to_arc = 0
				";
			
			if( !empty( $sName ) )
			{
				if( is_numeric($sName) )$sQuery .= sprintf("AND id = '%s'\n", addslashes( $sName ) );
				if( !is_numeric($sName) )$sQuery .= sprintf("AND name LIKE '%%%s%%'\n", addslashes( $sName ) );
			}
			
			$sQuery .= "ORDER BY name\n";
			$sQuery .= "LIMIT {$nLimit}\n";
			
			return $this->select( $sQuery );
		}
		
		public function getReport( $nIDOffice, $nIDFirm, $sType, DBResponse $oResponse )
		{
			global $db_name_personnel, $db_name_sod;
			$nIDOffice = (int) $nIDOffice;
			$nIDFirm = (int) $nIDFirm;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
			{
				if( in_array( 'storagehouses_edit', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			}
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						s.id, 
						s.name,
						CASE s.type
						    WHEN '' THEN 'Не е посочен'
						    WHEN 'new' THEN 'Нова Техника'
						    WHEN 'virtual' THEN 'Виртуален'
						    WHEN 'recik' THEN 'Рециклирана Техника'
						    WHEN 'removed' THEN 'Свалена Техника'
						END AS type,
						of.name as office,
						c.name as city,
						CONCAT_WS( ' ', ml.fname, ml.mname, ml.lname ) as MOL,
						CONCAT( CONCAT_WS( ' ', up.fname, up.mname, up.lname ), ' [', DATE_FORMAT( s.updated_time, '%d.%m.%Y %H:%i:%s' ), ']' ) AS updated_user
					FROM storagehouses s
						LEFT JOIN {$db_name_personnel}.personnel as up ON s.updated_user = up.id
						LEFT JOIN {$db_name_personnel}.personnel as ml ON s.mol_id_person = ml.id
						LEFT JOIN {$db_name_sod}.offices of ON s.id_office = of.id
						LEFT JOIN {$db_name_sod}.cities c ON s.address_city = c.id
					WHERE 1
						AND s.to_arc = 0
						AND of.id_firm = {$nIDFirm}
						
			";
			
			if ( !empty( $nIDOffice ) )
			{
				$sQuery .= "
						AND s.id_office = {$nIDOffice}
				";
			}
			
			if ( !empty( $sType ) )
			{
				$sQuery .= "
						AND s.type = '{$sType}'
				";
			}
			
			$this->getResult( $sQuery, 'name', DBAPI_SORT_ASC, $oResponse );
			
			$oStoragehouseMols = new DBStoragehousesMols();
			
			foreach( $oResponse->oResult->aData as $key => $value )
			{
				$nSearchID = $value['id'];
				
				$aStoragehouseMols = $oStoragehouseMols->getAllPersons( $nSearchID );
				
				if( !empty( $aStoragehouseMols ) )
				{
					$aAttributes = array();
					$aAttributes['style'] = "font-weight: bold;";
					$aAttributes['title'] = "";
					
					foreach( $aStoragehouseMols as $aMol )
					{
						$aAttributes['title'] .= $aMol['name'] . "\n";
					}
					
					$aAttributes['title'] = trim( $aAttributes['title'], "\n" );
					
					$oResponse->setDataAttributes( $key, 'MOL', $aAttributes );
				}
			}
			
			$oResponse->setField( 'id',				'id',					'сортирай по id' );
			$oResponse->setField( 'name',			'Име',					'сортирай по име' );
			$oResponse->setField( 'type',			'Тип',					'сортирай по тип' );
			$oResponse->setField( 'office',			'Регион',				'сортирай по офис' );
			$oResponse->setField( 'city',			'Адрес',				'сортирай по адрес' );
			$oResponse->setField( 'MOL',			'Материлано лице',		'сортирай по материлано лице' );
			$oResponse->setField( 'updated_user',	'Последна редакция',	'Сортиране по последно редактирал' );
			
			if( $right_edit )
			{
				$oResponse->setField( '', '', '', 'images/edit.gif', 	'editStoragehouse', '' );
				$oResponse->setField( '', '', '', 'images/cancel.gif',	'delStoragehouse', '' );
				$oResponse->setFieldLink('id','editStoragehouse');
				$oResponse->setFieldLink('name','editStoragehouse');
			}
		}
		
		public function getIDNova($id_office) {
			
			$sQuery = "
				SELECT 
					id
				FROM storagehouses
				WHERE 1
					AND to_arc = 0
					AND id_office = {$id_office}
					AND type = 'new'
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getIDRemoved($nIDOffice) {
			
			$sQuery = "
			
				SELECT
					id
				FROM storagehouses
				WHERE 1
					AND to_arc = 0
					AND id_office = {$nIDOffice}
					AND type = 'removed'
			
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getFirstNewStorage( $nIDMol )
		{
			//Първото срещане на склад от тип НОВА, на който съответства посочения МОЛ.
			$sQuery = "
					SELECT
						id,
						name
					FROM storagehouses
					WHERE 1
						AND to_arc = 0
						AND type = 'new'
						AND mol_id_person = {$nIDMol}
					LIMIT 1
			";
			
			$aStoragehouse = $this->selectOnce( $sQuery );
			
			if( !empty( $aStoragehouse ) )
			{
				//Намерен е склада
				return $aStoragehouse;
			}
			else
			{
				//Търсене сред други складове, на които е МОЛ посочения потребител.
				$sQuery = "
						SELECT
							s.id,
							s.name
						FROM storagehouses_mols sm
							LEFT JOIN storagehouses s ON s.id = sm.id_storagehouse
						WHERE 1
							AND sm.to_arc = 0
							AND s.to_arc = 0
							AND sm.id_person = {$nIDMol}
							AND s.type = 'new'
						LIMIT 1
				";
				
				$aStoragehouse = $this->selectOnce( $sQuery );
				
				if( !empty( $aStoragehouse ) )
				{
					//Намерен е склада
					return $aStoragehouse;
				}
				else
				{
					return array();
				}
			}
		}
		
		public function getFirstNewStorageForOffice( $nIDOffice )
		{
			global $db_name_personnel;
			
			if( empty( $nIDOffice ) || !is_numeric( $nIDOffice ) )return array();
			
			$sQuery = "
					SELECT
						s.id,
						s.name,
						CONCAT_WS( ' ', p.fname, p.mname, p.lname ) AS mol
					FROM storagehouses s
						LEFT JOIN {$db_name_personnel}.personnel p ON p.id = s.mol_id_person
					WHERE 1
						AND s.to_arc = 0
						AND s.type = 'new'
						AND s.id_office = {$nIDOffice}
					LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getFirstRemovedStorageFromOffice( $nIDOffice )
		{
			global $db_name_personnel;
			
			if( empty( $nIDOffice ) || !is_numeric( $nIDOffice ) )return array();
			
			$sQuery = "
					SELECT
						s.id,
						s.name,
						CONCAT_WS( ' ', p.fname, p.mname, p.lname ) AS mol
					FROM storagehouses s
						LEFT JOIN {$db_name_personnel}.personnel p ON p.id = s.mol_id_person
					WHERE 1
						AND s.to_arc = 0
						AND s.type = 'removed'
						AND s.id_office = {$nIDOffice}
					LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getAllStoragehouses()
		{
			$sQuery = "
					SELECT
						id,
						name
					FROM storagehouses
					WHERE to_arc = 0
					ORDER BY name
			";
			
			return $this->select( $sQuery );
		}
	}

?>
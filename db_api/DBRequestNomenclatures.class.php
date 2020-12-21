<?php

	class DBRequestNomenclatures extends DBBase2
	{
		public function __construct()
		{
			global $db_storage;
			
			parent::__construct($db_storage, 'request_nomenclatures');
		}
		
		public function getRequestElement( $nIDRequest, $nIDNomenclature )
		{
			if( empty($nIDRequest) || !is_numeric($nIDRequest) )
				throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
				
			if( empty($nIDNomenclature) || !is_numeric($nIDNomenclature) )
				throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
			
			$sQuery = "SELECT * FROM request_nomenclatures
					WHERE to_arc=0
					AND id_request={$nIDRequest}
					AND id_nomenclature={$nIDNomenclature}
					LIMIT 1
					";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function refreshNomenclatures( $nIDType, DBResponse $oResponse )
		{
			$nIDType = (int) $nIDType;
			
			//Get Nomenclatures From Type
			$sQuery = "
					SELECT * FROM nomenclatures
					WHERE to_arc=0 AND id_type={$nIDType}
			";
			
			$oNomenclatures = new DBNomenclatures();
			$aNomenclatures = $oNomenclatures->select( $sQuery );
			
			
			//Set Nomenclature
			$oResponse->setFormElement( 'form1', 'nIDNomenclature' );
			
			$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array("value" => 0), "--- Избери ---" );
			
			foreach( $aNomenclatures as $aNomenclature )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array('value'=>$aNomenclature['id']), $aNomenclature['name'] );
			}
		}

		public function fillFields( $nIDElement, DBResponse $oResponse )
		{
			$oNomenclatures = new DBNomenclatures();
			
			$sQuery = "
					SELECT * FROM nomenclatures
					WHERE to_arc=0
			";
			$aNomenclatures = $oNomenclatures->select( $sQuery );
			$aElement = $this->getRecord( $nIDElement );
			
			//Set Count
			$nCount = isset($aElement['count']) ? $aElement['count'] : 1;
			$oResponse->setFormElement( 'form1', 'nCount', array('value' => $nCount));

			$nIDNomenclature = isset($aElement['id_nomenclature']) ? $aElement['id_nomenclature'] : 0;

			//Get Nomenclature Type
			$nIDType = 0;
			foreach( $aNomenclatures as $aNomenclature )
			{
				if( $aNomenclature['id'] == $nIDNomenclature )
				{
					$nIDType = $aNomenclature['id_type'];
					break;
				}
			}

			//Set Nomenclature Types
			$oResponse->setFormElement( 'form1', 'nIDNomenclatureType', array('value' => $nIDType) );
			
			$oResponse->setFormElementChild( 'form1', 'nIDNomenclatureType', array("value" => 0), "--- Избери ---" );
			$oNomenclatures->getHierarchy( 0, 0, $nIDType, $oResponse );

			//Get Nomenclatures From Type
			$sQuery = "
					SELECT * FROM nomenclatures
					WHERE to_arc=0 AND id_type={$nIDType}
			";
			$aNomenclatures = $oNomenclatures->select( $sQuery );
			
			
			//Set Nomenclature
			$oResponse->setFormElement( 'form1', 'nIDNomenclature', array('value' => $nIDNomenclature) );
			
			$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array("value" => 0), "--- Избери ---" );
			
			foreach( $aNomenclatures as $aNomenclature )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array('value'=>$aNomenclature['id']), $aNomenclature['name'] );
			}

			//Get Schemes
			$sQuery = "
					SELECT * FROM schemes
					WHERE to_arc=0
			";
			$aSchemes = $oNomenclatures->select( $sQuery );
			
			$oResponse->setFormElement( 'form1', 'nIDScheme' );
			
			$oResponse->setFormElementChild( 'form1', 'nIDScheme', array("value" => 0), "--- Избери ---" );
			
			foreach( $aSchemes as $aScheme )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDScheme', array('value'=>$aScheme['id']), $aScheme['name'] );
			}
		}
		
		public function getOffices( DBResponse $oResponse )
		{
			global $db_name_sod, $db_name_storage;
			
			$sQuery = "
					SELECT
						o.id,
						CONCAT( f.name, ' - ', o.name ) AS name
					FROM $db_name_storage.storagehouses s
						LEFT JOIN $db_name_sod.offices o ON s.id_office = o.id
						LEFT JOIN $db_name_sod.firms f ON o.id_firm = f.id
					WHERE 1
						AND s.to_arc = 0
						AND f.to_arc = 0
						AND o.to_arc = 0
					GROUP BY o.id
					ORDER BY name
			";
			
			$aOffices = $this->select( $sQuery );
			
			$nSelectedOffice = 0;
			$oResponse->setFormElement( 'form1', 'nIDOffice' );
			foreach( $aOffices as $aOffice )
			{
				if( empty( $nSelectedOffice ) )
					$nSelectedOffice = $aOffice['id'];
				
				$oResponse->setFormElementChild( 'form1', 'nIDOffice', array( "value" => $aOffice['id'] ), $aOffice['name'] );
			}
			
			return $nSelectedOffice;
		}
		
		public function getStoragehouses( $aParams, DBResponse $oResponse, $nSelectedOffice = 0 )
		{
			global $db_name_sod;
			
			$nIDOffice = !empty( $aParams['nIDOffice'] ) ? $aParams['nIDOffice'] : $nSelectedOffice;
			
			$sQuery = "
					SELECT
						*
					FROM storagehouses
					WHERE 1
						AND to_arc = 0
						AND id_office = $nIDOffice
			";
			
			$aStoragehouses = $this->select( $sQuery );
			
			$nSelectedStorage = 0;
			$oResponse->setFormElement( 'form1', 'nIDStoragehouse' );
			foreach( $aStoragehouses as $aStoragehouse )
			{
				if( empty($nSelectedStorage) )
					$nSelectedStorage = $aStoragehouse['id'];
				
				$oResponse->setFormElementChild( 'form1', 'nIDStoragehouse', array( "value" => $aStoragehouse['id'] ), $aStoragehouse['name'] );
			}
			
			return $nSelectedStorage;
		}
		
		public function getMOL( $aParams, DBResponse $oResponse, $nSelectedStorage = 0 )
		{
			global $db_name_personnel, $db_name_sod;
			
			
			$nIDStoragehouse = !empty( $aParams['nIDStoragehouse'] ) ? $aParams['nIDStoragehouse'] : $nSelectedStorage;
			
			if( $nIDStoragehouse )
			{
				$sQuery = "SELECT
								CONCAT_WS(' ', p.fname, p.mname, p.lname) AS mol
							FROM storagehouses s
							LEFT JOIN {$db_name_personnel}.personnel p ON p.id = s.mol_id_person
							WHERE s.to_arc = 0
								AND p.to_arc = 0
								AND s.id = {$nIDStoragehouse}
						";
				
				$aStoragehouse = $this->selectOnce( $sQuery );
				
				$oResponse->setFormElement( 'form1', 'sMOL', array(), $aStoragehouse['mol'] );
			}
			else 
			{
				$oResponse->setFormElement( 'form1', 'sMOL',  array(), '' );
			}
		}
		
		public function getReport($aParams, DBResponse $oResponse )
		{
			
			$sQuery = "
					SELECT 
						rn.id, 
						n.name, 
						rn.count
						FROM request_nomenclatures rn
						LEFT JOIN nomenclatures n ON rn.id_nomenclature = n.id
						WHERE rn.to_arc=0
					";
			
			$sQuery .= "AND rn.id_request={$aParams['nID']} ";
			
			$this->getResult($sQuery, 'name', DBAPI_SORT_ASC, $oResponse);
			
			$oResponse->setField("name", "номенклатура", "Сортирай по Номенклатура");
			$oResponse->setField("count", "количество", "Сортирай по Количество");
			$oResponse->setField( '', '', '', 'images/cancel.gif', 'deleteRequestElement', '');
			
			$oResponse->setFieldLink("name", "setRequestElement");
		}
	}

?>
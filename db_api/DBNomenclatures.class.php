<?php
	class DBNomenclatures extends DBBase2
	{
		public function __construct()
		{
			global $db_storage;
			
			parent::__construct( $db_storage, 'nomenclatures' );
		}
		
		public function refreshNomenclatures( $aParams, DBResponse $oResponse )
		{
			//Get Nomenclatures From Type
			$nIDType = isset( $aParams['nIDNomenclatureType'] ) ? $aParams['nIDNomenclatureType'] : 0;
			
			$sQuery = "
					SELECT * FROM nomenclatures
					WHERE to_arc = 0
			";
			
			if( !empty( $nIDType ) )$sQuery .= "  AND id_type = {$nIDType} ";
			
			$sQuery .= "ORDER BY name";
			
			$aNomenclatures = $this->select( $sQuery );
			
			//Set Nomenclature
			$oResponse->setFormElement( 'form1', 'nIDNomenclature' );
			
			$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array( "value" => 0 ), "--- Избери ---" );
			
			foreach( $aNomenclatures as $aNomenclature )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array( 'value' => $aNomenclature['id'] ), $aNomenclature['name'] );
			}
		}
		
		public function refreshNomenclatureTypes( DBResponse $oResponse )
		{
			//Set Nomenclature Types
			$oResponse->setFormElement( 'form1', 'nIDNomenclatureType' );
			
			$oResponse->setFormElementChild( 'form1', 'nIDNomenclatureType', array( "value" => 0 ), "--- Избери ---" );
			$this->getHierarchy( 0, 0, 0, $oResponse );
		}
		
		public function setFormElements( $aParams, DBResponse $oResponse )
		{
			$oResponse->setFormElement( 'form1', 'nIDNomenclatureType' );
			$oResponse->setFormElement( 'form1', 'sName' );
			$oResponse->setFormElement( 'form1', 'sUnit' );
			//Set Unit Elements
			$oMeasures = new DBMeasures();
			$aMeasures = $oMeasures->getMeasures();
			foreach( $aMeasures as $aMeasure )
			{
				$oResponse->setFormElementChild( 'form1', 'sUnit', array( 'value' => $aMeasure['code'] ), $aMeasure['description'] );
			}
			//End Set Unit Elements
			$oResponse->setFormElement( 'form1', 'nPrice' );
			
			$nID = Params::get( "nID", 0 );
			$nType = Params::get( "nType", 0 );
			
			if( $nID )
			{
				$aNomenclature = $this->getRecord( $nID );
				
				$oResponse->setFormElement( 'form1', 'sName', array( 'value' => $aNomenclature['name'] ) );
				$oResponse->setFormElement( 'form1', 'sUnit', array( 'value' => $oMeasures->fixMeasureShortening( $aNomenclature['unit'] ) ) );
				$oResponse->setFormElement( 'form1', 'nPrice', array( 'value' => $aNomenclature['last_price'] ) );
				$nSelect = $aNomenclature['id_type'];
				$this->getHierarchy( 0, 0, $nSelect, $oResponse );
			}
			else
			{
				$this->getHierarchy( 0, 0, $nType, $oResponse );
			}
		}
		
		public function getHierarchy( $nRequestedID, $level, $nSelectedID, DBResponse $oResponse )
		{
			global $db_storage, $nCount;
			
			$nRequestedID = (int) $nRequestedID;
			$nSelectedID = (int) $nSelectedID;
			$nCount++;
			$sQuery = "
					SELECT
						nt.id,
						nt.id_parent,
						nt.name
					FROM nomenclature_types nt
					WHERE 1
						AND nt.to_arc = 0
						AND nt.id_parent = {$nRequestedID}
					ORDER BY name
			";
			
			$rs = $db_storage->Execute( $sQuery );
			
			$aData = array();
			$aData = $rs->getRows();
			
			if( empty( $aData ) )
			{
				return NULL;
			}
			
			for( $i = 0; $i < count( $aData ); $i++ )
			{
				$sQuery = "
						SELECT
							nt.id,
							nt.id_parent,
							nt.name
						FROM nomenclature_types nt
						WHERE 1
							AND nt.to_arc = 0
							AND nt.id_parent = {$nRequestedID}
							AND nt.id = {$aData[$i]['id']}
						ORDER BY name
				";
				
				if( $rs = $db_storage->Execute( $sQuery ) )
				{
					$aTemp = $rs->getRows();
					$sTemp = '';
					for( $q = 0; $q <= $level; $q++ ) $sTemp .= '    ';
					$aTemp[0]['name'] = $sTemp . $aTemp[0]['name'];
					
					//Selected Item
					$aSelected = array();
					if( $aTemp[0]['id'] == $nSelectedID )
					{
						$aSelected["selected"] = "selected";
					}
					
					$oResponse->setFormElementChild( 'form1', 'nIDNomenclatureType', array_merge( array( "value" => $aTemp[0]['id'] ), $aSelected ), $aTemp[0]['name'] );
				}
				
				$this->getHierarchy( $aData[$i]['id'], $level + 1, $nSelectedID, $oResponse );
			}
			
			return NULL;
		}
		
		public function getNomenclaturePrice( $nIDNomenclature )
		{
			$aNomenclature = $this->getRecord( $nIDNomenclature );
			
			if( !empty( $aNomenclature ) )
			{
				return $aNomenclature['last_price'];
			}
			
			else return 0;
		}
		
		public function getNomenclatureMeasure( $nIDNomenclature )
		{
			$aNomenclature = $this->getRecord( $nIDNomenclature );
			
			if( !empty( $aNomenclature ) )
			{
				return $aNomenclature['unit'];
			}
			
			else return 0;
		}
		
		public function fixNomenclatureMeasures()
		{
			$oMeasures = new DBMeasures();
			
			$sQuery = "
					SELECT
						*
					FROM nomenclatures
					WHERE to_arc = 0
			";
			
			$aContent = $this->select( $sQuery );
			
			foreach( $aContent as $aElement )
			{
				$sRightMeasure = $oMeasures->fixMeasureShortening( $aElement['unit'] );
				
				if( $sRightMeasure != $aElement['unit'] )
				{
					$aElement['unit'] = $sRightMeasure;
					$this->update( $aElement );
				}
			}
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel;
			$oNomenclatureTypes = new DBNomenclatureTypes();
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
				if( in_array( 'nomenclatures_edit', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						n.id,
						n.name,
						n.unit,
						CONCAT( n.last_price, ' лв.' ) AS last_price,
						nt.name as n_type,
						IF(
						p.id,
						CONCAT(
							CONCAT_WS(' ', p.fname, p.mname, p.lname),
							' (',
							DATE_FORMAT(n.updated_time, '%d.%m.%Y %H:%i:%s'),
							')'
							),
							''
							) AS updated_user
						FROM nomenclatures n
							LEFT JOIN {$db_name_personnel}.personnel p ON n.updated_user = p.id
							LEFT JOIN nomenclature_types nt ON n.id_type = nt.id
						WHERE n.to_arc = 0
			";
			
			if( isset( $aParams['nIDNomenclatureType'] ) && !empty( $aParams['nIDNomenclatureType'] ) )
			{
				$sIDList = $oNomenclatureTypes->searchIDsDeep( $aParams['nIDNomenclatureType'] );
				$sIDList = substr( $sIDList, 0, strlen( $sIDList ) - 1 );
				
				$sQuery .= "
						AND
						(
							n.id_type = {$aParams['nIDNomenclatureType']}
				";
				
				if( !empty( $sIDList ) )
				{
					$sQuery .= "
								OR
								n.id_type IN ({$sIDList})
					";
				}
				
				$sQuery .= "
						)
				";
			}
			
			$this->getResult( $sQuery, 'name', DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField( "name", 			"Име", 					"Сортирай по име" );
			$oResponse->setField( "n_type", 		"Тип", 					"Сортирай по тип" );
			$oResponse->setField( "unit", 			"Единица", 				"Сортирай по единица" );
			$oResponse->setField( "last_price", 	"Цена", 				"Сортирай по цена" );
			$oResponse->setField( "updated_user", 	"Последна редакция", 	"Сортирай по последна редакция" );
			
			if( $right_edit )
			{
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'deleteNomenclature', '' );
				$oResponse->setFieldLink( "name", "setupNomenclature" );
			}
		}
		
		public function getAllNames()
		{
			$sQuery = "
				SELECT
					id,
					name
				FROM nomenclatures
				WHERE to_arc = 0
				ORDER BY name ASC
			";
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getNamesByIDType($nIDType) {
			
			$sQuery = "
				SELECT 
					id,
					name
				FROM nomenclatures
				WHERE 1
					AND to_arc = 0
			";
			
			if(!empty($nIDType)) {
				$sQuery .= " AND id_type = {$nIDType}\n";
			}
			
			$sQuery .= " ORDER BY name\n";
			
			return $this->selectAssoc($sQuery);
			
		}
		
		
		public function getIDByName( $sName )
		{
			if( empty( $sName ) )
			{
				//throw new Exception( "Невалидно Име!", DBAPI_ERR_INVALID_PARAM );
				return 0;
			}
			
			$sName = addslashes( $sName );
			
			$sQuery = "
				SELECT
					id
				FROM nomenclatures
				WHERE 1
					AND to_arc = 0
					AND name = '{$sName}'
			";
			
			$aIDInfo = $this->selectOnce( $sQuery );
			if( empty( $aIDInfo ) || !isset( $aIDInfo['id'] ) )
			{
				return 0;
			}
			else
			{
				return $aIDInfo['id'];
			}
		}
		
		public function getNomenclaturesByIDScheme( $id )
		{
			if( !is_numeric( $id ) )
			{
				throw new Exception( "Невалиден шаблон!", DBAPI_ERR_INVALID_PARAM );
			}
			
			$sQuery = "
					SELECT
						n.id,
						se.count,
						n.name
					FROM nomenclatures n
						LEFT JOIN scheme_elements se ON se.id_scheme = {$id}
					WHERE 1
						AND se.to_arc = 0
						AND n.id = se.id_nomenclature
			";
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getNomenclaturesByIDOperation( $nID )
		{
			global $db_name_sod;
			
			if( !is_numeric( $nID ) )
			{
				throw new Exception( "Невалидна операция!", DBAPI_ERR_INVALID_PARAM );
			}
			
			$sQuery = "
					SELECT
						n.id,
						n.name
					FROM nomenclatures n
						LEFT JOIN {$db_name_sod}.tech_operations_nomenclatures op ON op.id_operation = {$nID}
					WHERE 1
						AND op.to_arc = 0
						AND n.id = op.id_nomenclature
			";
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getNomenclaturesOfType( $nIDType )
		{
			if( empty( $nIDType ) || !is_numeric( $nIDType ) )
			{
				throw new Exception( "Невалиден тип!", DBAPI_ERR_INVALID_PARAM );
			}
			
			$sQuery = "
					SELECT
						*
					FROM nomenclatures
					WHERE 1
						AND to_arc = 0
						AND id_type = {$nIDType}
					ORDER BY name
			";
			
			return $this->select( $sQuery );
		}
		
		public function getCountNomenclaturesByID ($nID) {
			$sQuery ="
				SELECT
					SUM(s.count) AS count,
					count(*) AS places
				FROM states s
				LEFT JOIN nomenclatures n on n.id = s.id_nomenclature
				WHERE 1
					AND n.to_arc = 0
					AND s.count != 0
					AND s.id_nomenclature = {$nID}
				GROUP BY n.id
				";
			return $this->selectOnce($sQuery);
		}
	}
?>
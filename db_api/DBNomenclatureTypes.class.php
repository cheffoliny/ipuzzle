<?php
	
	$repeats = 0;
	
	class DBNomenclatureTypes extends DBBase2 
	{
		public function __construct()
		{
			global $db_storage;
			
			parent::__construct($db_storage, 'nomenclature_types');
		}
		
		public function searchIDsDeep( $nIDParent )
		{
			if( !is_numeric( $nIDParent ) )
			{
				return "";
			}
			
			$sIDList = "";
			
			$sQuery = "
					SELECT
						id,
						id_parent
					FROM nomenclature_types
					WHERE 1
						AND to_arc = 0
						AND id_parent = {$nIDParent}
			";
			
			$aTypes = $this->select( $sQuery );
			
			if( !empty( $aTypes ) )
			{
				foreach( $aTypes as $aType )
				{
					$sIDList .= "{$aType['id']},";
					$sIDList .= $this->searchIDsDeep( $aType['id'] );
				}
				
				return $sIDList;
			}
			else
			{
				return "";
			}
		}
		
		public function putIDListInArray( $sIDList )
		{
			if( !empty( $sIDList ) )
			{
				return explode( ",", substr( $sIDList, 0, strlen( $sIDList ) ) );
			}
		}
		
		public function getIDByName( $sName )
		{
			if( empty( $sName ) )
			{
				//throw new Exception( "Невалидно име!", DBAPI_ERR_INVALID_PARAM );
				return array();
			}
			
			$sName = addslashes( $sName );
			$sName = str_replace( " ", "%", $sName );
			
			$sQuery = "
					SELECT
						id
					FROM nomenclature_types
					WHERE 1
						AND to_arc = 0
						AND name LIKE '{$sName}%'
					LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getHierarchy( $nRequestedID, $level )
		{
			global $db_storage, $repeats;
			$sSingleQuery = '';
			
			$nRequestedID = (int) $nRequestedID;
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						nt.id,
						nt.id_parent,
						nt.name
					FROM nomenclature_types nt
					WHERE 1
						AND nt.to_arc = 0
						AND nt.id_parent = {$nRequestedID}
			";
			
			$rs = $db_storage->Execute( $sQuery );
			
			$aData = array();
			$aData = $rs->getRows();
			
			if( empty( $aData ) )
			{
				return '';
			}
			
			for( $i = 0; $i < count( $aData ); $i++ )
			{
				if( $sSingleQuery == '' && $nRequestedID == 0 )
				{
					$repeats = 1;
					$sSingleQuery = "
						SELECT SQL_CALC_FOUND_ROWS
							nt.id,
							nt.id_parent,
							{$level} as level,
							{$repeats} as nom_order,
							nt.name
						FROM nomenclature_types nt
						WHERE 1
							AND nt.to_arc = 0
							AND nt.id_parent = {$nRequestedID}
							AND nt.id = {$aData[$i]['id']}
					";
				}
				else
				{
					$repeats++;
					$sSingleQuery .= "
						UNION
						SELECT
							nt.id,
							nt.id_parent,
							{$level} as level,
							{$repeats} as nom_order,
							nt.name
						FROM nomenclature_types nt
						WHERE 1
							AND nt.to_arc = 0
							AND nt.id_parent = {$nRequestedID}
							AND nt.id = {$aData[$i]['id']}
					";
				}
				
				$sDeepQuery = $this->getHierarchy( $aData[$i]['id'], $level + 1 );
				$sSingleQuery .= $sDeepQuery;
			}
			
			return $sSingleQuery;
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_storage;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
				if( in_array( 'nomenclature_types_edit', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			
			$sGeneralQuery = $this->getHierarchy( 0, 0 );
			
			$this->getResult( $sGeneralQuery, 'nom_order', DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField( "id", "ID", "Сортирай по ID" );
			//$oResponse->setField( "nom_order", "Ред", "Сортирай по ред" );
			$oResponse->setField( "name", "Име", "Сортирай по име" );
			
			if( $right_edit )
			{
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'deleteNomenclatureType', '' );
				$oResponse->setFieldLink( "name", "openNomenclatureType" );
			}
			
			foreach( $oResponse->oResult->aData as $key => $value )
			{
				if( isset( $value['level'] ) && ( $value['level'] > 0 ) )
				{
					$oResponse->setDataAttributes( $key, 'name', array( "style" => "padding-left: " . strval( $value["level"] * 5 ) . "mm" ) );
				}
				else
				{
					$oResponse->setDataAttributes( $key, 'name', array( "style" => "font-weight:bold" ) );
				}
			}
		}
		public function getChilds($nIDGroup) {
			
			$sQuery = " 
				SELECT 
					id,
					name
				FROM nomenclature_types
				WHERE 1
					AND to_arc = 0
					AND id_parent = {$nIDGroup}
				ORDER BY name
			";
			
			return $this->selectAssoc($sQuery);
		}
		
	}

?>
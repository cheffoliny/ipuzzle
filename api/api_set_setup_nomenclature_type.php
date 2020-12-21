<?php

	$nCount = 0;
	
	class ApiSetSetupNomenclatureType
	{
		
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
							nt.name,
							nt.is_control_panel
						FROM nomenclature_types nt 
						WHERE 1
							AND nt.to_arc = 0
							AND nt.id_parent = {$nRequestedID}
							AND nt.id = {$aData[$i]['id']}
				";
				
				$rs = $db_storage->Execute( $sQuery );
				if( $rs )
				{
					$aTemp = $rs->getRows();
					$sTemp = '';
					for( $q = 0; $q <= $level; $q++ ) $sTemp .= '    ';
					$sCurrentName  	= $aTemp[0]['name'];
					//$sIsCtrl		= $aTemp[0]['is_control_panel'] == 1 ? "checked" : "";
					$aTemp[0]['name'] = $sTemp . $aTemp[0]['name'];
					
					//Selected Item
					$aSelected = array();
					$sQuery = "SELECT * FROM nomenclature_types WHERE id = {$nSelectedID}";
					if( $rs = $db_storage->Execute( $sQuery ) )$aParentSelected = $rs->getRows();
					if($aParentSelected)
					{
						if( $aTemp[0]['id'] == $aParentSelected[0]['id_parent'] )	$aSelected["selected"] = "selected";
					}
					if( $aTemp[0]['id'] == $nSelectedID )						$oResponse->setFormElement( 'form1', 'sName', array(), $sCurrentName );
					
					if( $aTemp[0]['id'] == $nSelectedID && $aTemp[0]['is_control_panel'] == 1 ) {
						$oResponse->setFormElement( 'form1', 'nIsCtrl', array( 'checked' => 'checked' ) );
					}
					
					$oResponse->setFormElementChild( 'form1', 'sParent', array_merge( array( "value" => $aTemp[0]['id'] ), $aSelected ), $aTemp[0]['name'] );
				}
				
				$this->getHierarchy( $aData[$i]['id'], $level + 1, $nSelectedID, $oResponse );
			}
			
			return NULL;
		}
		
		public function load( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			//Извличане на йерархията на Типовете Номенклатури
			$oResponse->setFormElement( 'form1', 'sParent' );
			$oResponse->setFormElementChild( 'form1', 'sParent', array( "value" => 0 ), "--- Не е подчинен ---" );
			$this->getHierarchy( 0, 0, $nID, $oResponse );
			
			$oResponse->printResponse();
			
		}
		
		public function save( DBResponse $oResponse )
		{
			global $db_storage;
			$oNomenclatureTypes = new DBNomenclatureTypes();
			
			$nID = 			Params::get( 'nID', 0 );
			$sName = 		Params::get( "sName" );
			$nIDParent = 	Params::get( "sParent" );
			$nIsCtrl =		Params::get( 'nIsCtrl', 0 );

			if( empty( $sName ) )
				throw new Exception( "Въведете наименование!", DBAPI_ERR_INVALID_PARAM );
			
			if( !empty( $nID ) )
			{
				$aChildIDs = $oNomenclatureTypes->putIDListInArray( $oNomenclatureTypes->searchIDsDeep( $nID ) );
				
				foreach( $aChildIDs as $nChildID )
				{
					if( $nIDParent == $nChildID )
					{
						throw new Exception( "Типа не може да бъде подчинен на свой подтип!", DBAPI_ERR_INVALID_PARAM );
					}
				}
			}

			$aData = array();
			
			$aData['is_control_panel'] = 	( int ) $nIsCtrl;
			$aData['id'] = 					$nID;
			$aData['name'] = 				$sName;
			$aData['id_parent'] = 			$nIDParent;
			
			$oNomenclatureTypes->update( $aData );
			if($nIDParent != 0)
			{
				$sQuery="
				UPDATE nomenclature_types n, nomenclature_types n2
				set n.is_control_panel = n2.is_control_panel
				where n.id_parent = n2.id
				";
			}
			else
			{
				$sQuery="
				UPDATE nomenclature_types
				set is_control_panel = {$nIsCtrl}
				where id_parent = {$aData['id']}
				";
			}
			$rs = $db_storage->Execute( $sQuery );
		}
	}

?>
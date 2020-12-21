<?php

	class ApiStatesFilterFields
	{
		public function load( DBResponse $oResponse )
		{
			$nID = Params::get( 'nID', '0' );
			
			$oDBFiltersVisibleFields = new DBFiltersVisibleFields();
			$aShowFields = $oDBFiltersVisibleFields->getFieldsByIDFilter( $nID );
			
			foreach( $aShowFields as $sFieldName )
			{
				$oResponse->setFormElement( 'form1', $sFieldName, array( "checked" => "checked" ) );
			}
			
			$oResponse->printResponse();
		}
		
		public function save()
		{
			$nID = Params::get( 'nID', '0' );
			$nFieldOffice = Params::get( 'field_office', '0' );
			$nFieldStorageType = Params::get( 'field_storage_type', '0' );
			$nFieldNomenclatureType = Params::get( 'field_nomenclature_type', '0' );
			$nFieldCount = Params::get( 'field_count', '0' );
			
			$oDBFiltersVisibleFields = new DBFiltersVisibleFields();
 			$oDBFiltersVisibleFields->delByIDFilter( $nID );
			
			$aShowColumns = array();
			
			$aShowColumns['field_office'] = $nFieldOffice;
			$aShowColumns['field_storage_type'] = $nFieldStorageType;
			$aShowColumns['field_nomenclature_type'] = $nFieldNomenclatureType;
			$aShowColumns['field_count'] = $nFieldCount;
 			
 			foreach( $aShowColumns as $key => $value )
 			{
 				if( !empty( $value ) )
 				{
 					$aData = array();
 					$aData['id_filter'] = $nID;
 					$aData['field_name'] = $key;
 					
 					$oDBFiltersVisibleFields->update( $aData );
 				}
 			}
		}
	}

?>
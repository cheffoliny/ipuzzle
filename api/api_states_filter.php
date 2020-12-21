<?php

	class ApiStatesFilter
	{
		public function load( DBResponse $oResponse )
		{
			$nID = Params::get( 'nID', '0' );
			
			if( !empty( $nID ) )
			{
				$oDBFirms = new DBFirms();
				$oDBNomenclatures = new DBNomenclatures();
				$oDBStoragehouses = new DBStoragehouses();
				$oDBPersonnel = new DBPersonnel();
				$oDBObjects = new DBObjects();
				
				$oDBFilters = new DBFilters();
				$oDBFiltersParams = new DBFiltersParams();
				
				$aFilter = $oDBFilters->getRecord( $nID );
				$aFilterParams = $oDBFiltersParams->getParamsByIDFilter( $nID );
				
				$oResponse->setFormElement( 'form1', 'sName', array(), $aFilter['name'] );
				
				$aFirms = $oDBFirms->getFirms4();
				
				$oResponse->setFormElement( 'form1', 'nIDFirm', array() );
				$oResponse->setFormElementChild( 'form1', 'nIDFirm', array( "value" => "0" ), '---Всички---' );
				foreach( $aFirms as $key => $value )
				{
					if( $key == $aFilterParams['id_firm'] )
					{
						$ch = array( "selected" => "selected" );
					}
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild( 'form1', 'nIDFirm', array_merge( array( "value" => $key ), $ch ), $value );
				}
				
				$oResponse->setFormElement( 'form1', 'nIDOffice', array() );
				$oResponse->setFormElementChild( 'form1', 'nIDOffice', array( "value" => "0" ), '---Всички---' );
				if( !empty( $aFilterParams['id_firm'] ) )
				{
					$oDBOffices = new DBOffices();
					$aOffices = $oDBOffices->getOfficesByIDFirm( $aFilterParams['id_firm'] );
					
					foreach( $aOffices as $key => $value )
					{
						if( $key == $aFilterParams['id_office'] )
						{
							$ch = array( "selected" => "selected" );
						}
						else
						{
							$ch = array();
						}
						$oResponse->setFormElementChild( 'form1', 'nIDOffice', array_merge( array( "value" => $key ), $ch ), $value );
					}
				} 
				
				$aStorageTypes = array(	"storagehouse" => "Склад", "person" => "Служител", "object" => "Обект" );
				
				$oResponse->setFormElement( 'form1', 'sStorageType', array() );
				foreach( $aStorageTypes as $key => $value )
				{
					if( $key == $aFilterParams['storage_type'] )
					{
						$ch = array( "selected" => "selected" );
					}
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild( 'form1', 'sStorageType', array_merge( array( "value" => $key ), $ch ), $value );
				}
				
				if( !empty( $aFilterParams['id_storage'] ) )
				{
					$sStorageName = '';
					switch( $aFilterParams['storage_type'] )
					{
						case 'storagehouse':
							$aStoragehouse = $oDBStoragehouses->getRecord( $aFilterParams['id_storage'] );
							$sStorageName = $aStoragehouse['name'];
							break;
						
						case 'person':
							$aPerson = $oDBPersonnel->getPersonnelNames( $aFilterParams['id_storage'] );
							$sStorageName = $aPerson['names'];
							break;
						
						case 'object':
							$aObject = $oDBObjects->getRecord( $aFilterParams['id_storage'] );
							$sStorageName = $aObject['name'] . " [" . $aObject['num'] . "]";
							break;
					}
					
					$oResponse->setFormElement( 'form1', 'nIDStoragehouse', array(), $aFilterParams['id_storage'] );
					$oResponse->setFormElement( 'form1', 'sStoragehouse', array(), $sStorageName );
				}
				
				global $space;
				$oResponse->setFormElement( 'form1', 'nIDNomenclatureType', array() );
				$oResponse->setFormElementChild( 'form1', 'nIDNomenclatureType', array( "value" => "0" ), "---Всички---" );
				$this->showNomenclatureTypes( 0, $aFilterParams['id_nomenclature_type'], $oResponse );
				
				$aNomenclatures = $oDBNomenclatures->getNamesByIDType( $aFilterParams['id_nomenclature_type'] );
				
				$oResponse->setFormElement( 'form1', 'nIDNomenclature', array() );
				$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array( "value" => "0" ), "---Всички---" );
				foreach( $aNomenclatures as $key => $value )
				{
					if( $key == $aFilterParams['id_nomenclature'] )
					{
						$ch = array( "selected" => "selected" );
					}
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array_merge( array( 'value' => $key ), $ch ), $value );
				}
				
				$aStoragehouseTypes = array
				(
					"0" 		=> "---Всички---",
					"new" 		=> "Нова Техника",
					"recik" 	=> "Рециклирана Техника",
					"removed" 	=> "Свалена Техника"
				);
				
				$oResponse->setFormElement( 'form1', 'sStoragehouseType', array() );
				foreach( $aStoragehouseTypes as $key => $value )
				{
					if( $key == $aFilterParams['storagehouse_type'] )
					{
						$ch = array( "selected" => "selected" );
					}
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild( 'form1', 'sStoragehouseType', array_merge( array( "value" => $key ), $ch ), $value );
				}
				
				if( $aFilterParams['storage_type'] != 'storagehouse' )
				{
					$oResponse->setFormElementAttributes( 'form1', 'sStoragehouseType', array( "disabled" => "disabled" ) );
				}
				
				if( !empty( $aFilter['is_default'] ) )
				{
					$oResponse->setFormElement( 'form1', 'nDefault', array( "checked" => "checked" ) );
				}
			}
			else
			{
				$oDBFirms = new DBFirms();
				$oDBNomenclatures = new DBNomenclatures();
				
				$aFirms = $oDBFirms->getFirms4();
				
				$oResponse->setFormElement( 'form1', 'nIDFirm', array() );
				$oResponse->setFormElementChild( 'form1', 'nIDFirm', array( "value" => "0" ), '---Всички---' );
				foreach( $aFirms as $key => $value )
				{
					$oResponse->setFormElementChild( 'form1', 'nIDFirm', array( "value" => $key ), $value );
				}
				
				$oResponse->setFormElement( 'form1', 'nIDOffice', array() );
				$oResponse->setFormElementChild( 'form1', 'nIDOffice', array( "value" => "0" ), '---Всички---' );
				
				$aStorageTypes = array(	"storagehouse" => "Склад", "person" => "Служител", "object" => "Обект" );
				
				$oResponse->setFormElement( 'form1', 'sStorageType', array() );
				foreach( $aStorageTypes as $key => $value )
				{
					$oResponse->setFormElementChild( 'form1', 'sStorageType', array( "value" => $key ), $value );
				}
				
				global $space;
				
				$oResponse->setFormElement( 'form1', 'nIDNomenclatureType', array() );
				$oResponse->setFormElementChild( 'form1', 'nIDNomenclatureType', array( "value" => "0" ), "---Всички---" );
				$this->showNomenclatureTypes( 0, $aFilterParams['id_nomenclature_type'], $oResponse );
				
				$aNomenclatures = $oDBNomenclatures->getNamesByIDType( 0 );
				
				$oResponse->setFormElement( 'form1', 'nIDNomenclature', array() );
				$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array( "value" => "0" ), "---Всички---" );
				foreach( $aNomenclatures as $key => $value )
				{	
					$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array( 'value' => $key ), $value );
				}
				
				$aStoragehouseTypes = array
				(
					"0" 		=> "---Всички---",	
					"new" 		=> "Нова Техника",
					"recik" 	=> "Рециклирана Техника",
					"removed" 	=> "Свалена Техника"
				);
				
				$oResponse->setFormElement( 'form1', 'sStoragehouseType', array() );
				foreach( $aStoragehouseTypes as $key => $value )
				{
					$oResponse->setFormElementChild( 'form1', 'sStoragehouseType', array( "value" => $key ), $value );
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function showNomenclatureTypes( $nIDParent, $nIDNomenclatureTypeS, $oResponse )
		{
			$oDBNomenclatureTypes = new DBNomenclatureTypes();
			$aNomenclatureTypes = $oDBNomenclatureTypes->getChilds( $nIDParent );
			
			global $space;
			
			if( !empty( $nIDParent ) )$space .= "     ";
			
			foreach( $aNomenclatureTypes as $key => $value )
			{
				if( $key == $nIDNomenclatureTypeS )
				{
					$ch = array( "selected" => "selected" );
				}
				else
				{
					$ch = array();
				}
				$oResponse->setFormElementChild( 'form1', 'nIDNomenclatureType', array_merge( array( "value" => $key ), $ch ), $space . $value );
				$this->showNomenclatureTypes( $key, $nIDNomenclatureTypeS, $oResponse );
			}
			
			if( !empty( $nIDParent ) )$space = substr( $space, 5 );
		}
		
		public function loadOffices( DBResponse $oResponse )
		{
			$nFirm = Params::get( 'nIDFirm' );
			
			$oResponse->setFormElement( 'form1', 'nIDOffice', array(), '' );
			$oResponse->setFormElementChild( 'form1', 'nIDOffice', array_merge( array( "value" => '0' ) ), "---Всички---" );
			
			if( !empty( $nFirm ) )
			{
				$oDBOffices = new DBOffices();
				$aOffices = $oDBOffices->getOfficesByIDFirm( $nFirm );
				
				foreach( $aOffices as $key => $value )
				{
					$oResponse->setFormElementChild( 'form1', 'nIDOffice', array_merge( array( "value" => $key ) ), $value );
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function loadNomenclatures( DBResponse $oResponse )
		{
			$nIDNomenclatureType = Params::get( 'nIDNomenclatureType' );
			
			$oResponse->setFormElement( 'form1', 'nIDNomenclature', array(), '' );
			$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array_merge( array( "value" => '0' ) ), "---Всички---" );
			
			$oDBNomenclatures = new DBNomenclatures();
			$aNomenclatures = $oDBNomenclatures->getNamesByIDType( $nIDNomenclatureType );
			
			foreach( $aNomenclatures as $key => $value )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array_merge( array( "value" => $key ) ), $value );
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$nID = Params::get( 'nID', '0' );
			$sName = Params::get( 'sName', '' );
			$nIDFirm = Params::get( 'nIDFirm', '0' );
			$nIDOffice = Params::get( 'nIDOffice', '0' );
			$sStorageType = Params::get( 'sStorageType', '' );
			$nIDStoragehouse = Params::get( 'nIDStoragehouse', '' );
			$nIDNomenclatureType = Params::get( 'nIDNomenclatureType', '0' );
			$nIDNomenclatere = Params::get( 'nIDNomenclature', '0' );
			$sStoragehouseType = Params::get( 'sStoragehouseType', '' );
			$nDefault = Params::get( 'nDefault', '0' );
			
			$nIDPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : '';
			
			if( empty( $sName ) )
			{
				throw new Exception( "Въведете име на филтъра" );
			}
			
			$oDBFilters = new DBFilters();
			$oDBFiltersParams = new DBFiltersParams();
			
			if( !empty( $nDefault ) )
			{
				$oDBFilters->resetDefaults( "DBStates", $nIDPerson );
			}
			
			$aFilter = array();
			$aFilter['id'] = $nID;
			$aFilter['name'] = $sName;
			$aFilter['id_person'] = $nIDPerson;
			$aFilter['is_default'] = $nDefault;
			$aFilter['report_class'] = 'DBStates';
			
			$oDBFilters->update( $aFilter );
			
			$oDBFiltersParams->delParamsByIDFilter( $aFilter['id'] );
			
			$aFilterParams = array();
			
			$aFilterParams['id_firm'] = $nIDFirm;
			$aFilterParams['id_office'] = $nIDOffice;
			$aFilterParams['storage_type'] = $sStorageType;
			$aFilterParams['id_storage'] = $nIDStoragehouse;
			$aFilterParams['id_nomenclature_type'] = $nIDNomenclatureType;
			$aFilterParams['id_nomenclature'] = $nIDNomenclatere;
			$aFilterParams['storagehouse_type'] = $sStoragehouseType;
			
			foreach( $aFilterParams as $key => $value )
			{
 				$aData = array();
 				$aData['id_filter'] = $aFilter['id'];
 				$aData['name'] = $key;
 				$aData['value'] = $value;
 				$oDBFiltersParams->update( $aData );
 			}
			
			$oResponse->setFormElement( 'form1', 'nID', array(), $aFilter['id'] );
			$oResponse->printResponse();
		}
	}

?>
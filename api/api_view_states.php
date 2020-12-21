<?php

	class ApiViewStates
	{	
		public function load( DBResponse $oResponse )
		{
			$nIDScheme = Params::get( 'schemes', '' );
			$nIDPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			
			$oDBFilters = new DBFilters();
			$aFilters = $oDBFilters->getFiltersByReportClass( "DBStates", $nIDPerson );
			
			$oResponse->setFormElement( 'form1', 'schemes', array() );
			$oResponse->setFormElementChild( 'form1', 'schemes', array( "value" => "0" ), "---Изберете---" );
			
			$aFilter = array();
			foreach( $aFilters as $key => $value )
			{
				if( !empty( $nIDScheme ) )
				{
					if( $key == $nIDScheme )
					{
						$ch = array( "selected" => "selected" );
						$aFilter = $aFilters[$key];
					}
					else
					{
						$ch = array();
					}
				}
				else
				{
					if( $value['is_default'] == '1' )
					{
						$ch = array( "selected" => "selected" );
						$aFilter = $aFilters[$key];
						$nIDScheme = $key;
					}
					else
					{
						$ch = array();
					}
				}
				
				$oResponse->setFormElementChild( 'form1', 'schemes', array_merge( array( "value" => $key ), $ch ), $value['name'] );
			}
			
			if( !empty( $aFilter ) )
			{		
				$oDBFirms 			= new DBFirms();
				$oDBNomenclatures 	= new DBNomenclatures();
				$oDBStoragehouses 	= new DBStoragehouses();
				$oDBPersonnel 		= new DBPersonnel();
				$oDBObjects 		= new DBObjects();
				
				$oDBFiltersParams = new DBFiltersParams();
				$aFilterParams = $oDBFiltersParams->getParamsByIDFilter( $nIDScheme );
				
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
				$oResponse->setFormElementChild( 'form1', 'nIDNomenclatureType', array( "value" => "0" ), "--- Номенклатура тип ---" );
				$this->showNomenclatureTypes( 0, $aFilterParams['id_nomenclature_type'], $oResponse );
				
				$aNomenclatures = $oDBNomenclatures->getNamesByIDType( $aFilterParams['id_nomenclature_type'] );
				
				$oResponse->setFormElement( 'form1', 'nIDNomenclature', array() );
				$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array( "value" => "0" ), "--- Номенклатура ---" );
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
				
				$aStoragehouseTypesObj = array
				(
					"0" 		=> "---Всички---",
					"clientown" => "на клиента",
					"firmown" 	=> "на фирмата",
				);
				
				$oResponse->setFormElement( 'form1', 'sStoragehouseTypeObj', array() );
				foreach( $aStoragehouseTypesObj as $key => $value )
				{
					if( $key == $aFilterParams['storagehouse_type'] )
					{
						$ch = array( "selected" => "selected" );
					}
					else
					{
						$ch = array();
					}
					
					$oResponse->setFormElementChild( 'form1', 'sStoragehouseTypeObj', array_merge( array( "value" => $key ), $ch ), $value );
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
				$this->showNomenclatureTypes( 0, $aFilter['id_nomenclature_type'], $oResponse );
				
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
				
				
				
				
					$aStoragehouseTypesObj = array
				(
					"0" 		=> "---Всички---",
					"clientown" => "на клиента",
					"firmown" 	=> "на фирмата",
				);
				
				$oResponse->setFormElement( 'form1', 'sStoragehouseTypeObj', array() );
				foreach( $aStoragehouseTypesObj as $key => $value )
				{
					$oResponse->setFormElementChild( 'form1', 'sStoragehouseTypeObj', array( "value" => $key ), $value );
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function showNomenclatureTypes( $nIDParent, $nIDNomenclatureTypeS, $oResponse )
		{
			$oDBNomenclatureTypes = new DBNomenclatureTypes();
			$aNomenclatureTypes = $oDBNomenclatureTypes -> getChilds( $nIDParent );
			
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
			$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array_merge( array( "value" => '0' ) ), "--- Номенклатура ---" );
			
			$oDBNomenclatures = new DBNomenclatures();	
			$aNomenclatures = $oDBNomenclatures->getNamesByIDType( $nIDNomenclatureType );
			
			foreach( $aNomenclatures as $key => $value )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array_merge( array( "value" => $key ) ), $value );
			}
			
			$oResponse->printResponse();
		}
		
		public function deleteFilter( DBResponse $oResponse )
		{
			$nIDFilter = Params::get( 'schemes', '0' );
			
			$oDBFilters = new DBFilters();
			$oDBFilters->delete( $nIDFilter );
			
			$oResponse->printResponse();
		}
		
		public function result(DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oStates = new DBStates();
			$oStates->getReport( $aParams, $oResponse );
			
			$oResponse->printResponse();
			//$oResponse->printResponse( "Наличности", "states" );
		}
	}

?>
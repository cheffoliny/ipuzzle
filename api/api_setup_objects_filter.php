<?php

	class ApiSetupObjectsFilter
	{
		public function load( DBResponse $oResponse )
		{
			$nID = Params::get( 'nID', '0' );

			if( !empty( $nID ) )
			{
				$oFilters 				= new DBFilters();
				$oFiltersVisibleFields 	= new DBFiltersVisibleFields();
                $oFiltersParams 		= new DBFiltersParams();
				
				$aFilter 			= $oFilters->getRecord( $nID );
				$aVisibleColumns 	= $oFiltersVisibleFields->getFieldsByIDFilter( $nID );

				$oResponse->setFormElement( 'form1', 'filter_name', array(), $aFilter['name'] );
				
				if( !empty( $aFilter['is_default'] ) )
				{
					$oResponse->setFormElement( 'form1', 'is_default', array( "checked" => "checked" ) );
				}
				
				foreach( $aVisibleColumns as $key => $value )
				{
					if( !empty( $value ) )
					{
						$oResponse->setFormElement( 'form1', $value, array( "checked" => "checked" ) );
					}
				}

			}
			
			$oResponse->printResponse();
		}
		
		public function save()
		{
			$oFilters 				= new DBFilters();
			$oFiltersVisibleFields 	= new DBFiltersVisibleFields();
			
			$nID 		= Params::get( 'nID', 0 );
			$nIsDefault = Params::get( 'is_default', 0) ;
			$sName 		= Params::get( 'filter_name', '' );
			
			$aVisibleColumns = array();
			
			$aVisibleColumns['nTech'] 			= Params::get( 'nTech', 			'' );
			$aVisibleColumns['nMonthTax'] 		= Params::get( 'nMonthTax', 		'' );
			$aVisibleColumns['nUnpaidSingle'] 	= Params::get( 'nUnpaidSingle', 	'' );
			$aVisibleColumns['nLastPaid'] 		= Params::get( 'nLastPaid', 		'' );
			$aVisibleColumns['nObjectFunction'] = Params::get( 'nObjectFunction', 	'' );
			$aVisibleColumns['nObjectPhone'] 	= Params::get( 'nObjectPhone', 		'' );
			$aVisibleColumns['nStartDate'] 		= Params::get( 'nStartDate', 		'' );
			$aVisibleColumns['nAddress'] 		= Params::get( 'nAddress', 			'' );
			$aVisibleColumns['nDistance'] 		= Params::get( 'nDistance', 		'' );
			$aVisibleColumns['nOperativeInfo'] 	= Params::get( 'nOperativeInfo', 	'' );
			$aVisibleColumns['nAdminReg'] 		= Params::get( 'nAdminReg', 		'' );
			$aVisibleColumns['nTechReg'] 		= Params::get( 'nTechReg', 			'' );
			$aVisibleColumns['nReactReg'] 		= Params::get( 'nReactReg', 		'' );
            $aVisibleColumns['nWorkTime'] 		= Params::get( 'nWorkTime', 				'' );

            $aFilterTotals['id_filter'] 		= $nID;


			if ( empty($sName) ) {
				throw new Exception( "Въведете име на филтъра" );
			}
			
			$nIDPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			
			if ( !empty($nIsDefault) ) {
				$oFilters->resetDefaults( "DBObjects", $nIDPerson );
			}
			
			$aFilter = array();
			
			if ( !empty($nID) ) {
				$aFilter['id'] = $nID;
				$oFiltersVisibleFields->delByIDFilter( $nID );
			}
			
			$aFilter['name'] = $sName;
			$aFilter['report_class'] = "DBObjects";
			$aFilter['is_default'] = $nIsDefault;
			$aFilter['id_person'] = $nIDPerson;
			$aFilter['is_auto'] = 0;
			
			$oFilters->update( $aFilter );
			
			foreach ( $aVisibleColumns as $key => $value ) {
				$aBuffer = array();
				
				if ( !empty($value) ) {
					$aBuffer['id_filter'] = $aFilter['id'];
					$aBuffer['field_name'] = $key;
					
					$oFiltersVisibleFields->update( $aBuffer );
				}
			}

		}
	}

?>
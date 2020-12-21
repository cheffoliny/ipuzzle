<?php

	class ApiAdminVouchersFilter
	{
		public function load( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", "0" );
			
			$oDBFilters 				= new DBFilters();
			$oDBFiltersVisibleFields 	= new DBFiltersVisibleFields();
			
			if( !empty( $nID ) )
			{
				$aFilter = $oDBFilters->getRecord( $nID );
				
				$oResponse->setFormElement( "form1", "sFilterName", array(), $aFilter['name'] );
				
				if( !empty( $aFilter['is_default'] ) )
				{
					$oResponse->setFormElement( "form1", "nIsDefault", array( "checked" => "checked" ) );
				}
				
				$aVisibleColumns = $oDBFiltersVisibleFields->getFieldsByIDFilter( $nID );
				
				foreach( $aVisibleColumns as $nKey => $sValue )
				{
					if( !empty( $sValue ) )
					{
						$oResponse->setFormElement( "form1", $sValue, array( "checked" => "checked" ) );
					}
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$oDBFilters 				= new DBFilters();
			$oDBFiltersVisibleFields 	= new DBFiltersVisibleFields();
			
			$nID 		= Params::get( "nID" );
			$nIsDefault = Params::get( "nIsDefault", 0 );
			$sName 		= Params::get( "sFilterName", "" );
			
			$aVisibleColumns = array();
			$aVisibleColumns['person_name'] 		= Params::get( "person_name", 		0 );
			$aVisibleColumns['date_from'] 			= Params::get( "date_from", 		0 );
			$aVisibleColumns['vacate_date'] 		= Params::get( "vacate_date", 		0 );
			$aVisibleColumns['office_name'] 		= Params::get( "office_name", 		0 );
			$aVisibleColumns['object_name'] 		= Params::get( "object_name", 		0 );
			$aVisibleColumns['min_cost'] 			= Params::get( "min_cost", 			0 );
			$aVisibleColumns['unpaid_count'] 		= Params::get( "unpaid_count", 		0 );
			$aVisibleColumns['leave_count'] 		= Params::get( "leave_count", 		0 );
			$aVisibleColumns['vouchers_plus'] 		= Params::get( "vouchers_plus", 	0 );
			$aVisibleColumns['correction_five'] 	= Params::get( "correction_five", 	0 );
			$aVisibleColumns['vouchers_plus_c'] 	= Params::get( "vouchers_plus_c", 	0 );
			$aVisibleColumns['correction_five_c'] 	= Params::get( "correction_five_c", 0 );
			$aVisibleColumns['workdays'] 			= Params::get( "workdays", 			0 );
			$aVisibleColumns['vouchers'] 			= Params::get( "vouchers", 			0 );
			
			if( empty( $sName ) )
			{
				throw new Exception( "Въведете име на филтъра!", DBAPI_ERR_INVALID_PARAM );
			}
			
			$nIDPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			
			if( !empty( $nIsDefault ) )
			{
				$oDBFilters->resetDefaults( "AdminVouchers", $nIDPerson );
			}
			
			$aFilter = array();
			
			$aFilter['id'] = $nID;
			$aFilter['name'] = $sName;
			$aFilter['id_person'] = $nIDPerson;
			$aFilter['is_default'] = $nIsDefault;
			$aFilter['report_class'] = "AdminVouchers";
			
			$oDBFilters->update( $aFilter );
			
			$oDBFiltersVisibleFields->delByIDFilter( $aFilter['id'] );
			foreach( $aVisibleColumns as $sColumn => $nValue )
			{
				if( !empty( $nValue ) )
				{
					$aField = array();
					$aField['id'] = 0;
					$aField['id_filter'] = $aFilter['id'];
					$aField['field_name'] = $sColumn;
					
					$oDBFiltersVisibleFields->update( $aField );
				}
			}
			
			$oResponse->printResponse();
		}
	}

?>
<?php

	class ApiShiftsCountFilter
	{
		public function load( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", "0" );
			
			if( !empty( $nID ) )
			{
				$oDBFilters 				= new DBFilters();
				$oDBFiltersVisibleFields 	= new DBFiltersVisibleFields();
				
				$aFilter = $oDBFilters->getRecord( $nID );
				
				$oResponse->setFormElement( 'form1', 'sFilterName', array(), $aFilter['name'] );
				
				if( !empty( $aFilter['is_default'] ) )
				{
					$oResponse->setFormElement( 'form1', 'nIsDefault', array( "checked" => "checked" ) );
				}
				
				$aVisibleFields = $oDBFiltersVisibleFields->getFieldsByIDFilter( $nID );
				
				foreach( $aVisibleFields as $nID => $sValue )
				{
					if( !empty( $sValue ) )
					{
						$oResponse->setFormElement( 'form1', $sValue, array( "checked" => "checked" ) );
					}
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save()
		{
			$nID 		= Params::get( 'nID' );
			$nIsDefault = Params::get( 'nIsDefault', 0 );
			$sName 		= Params::get( 'sFilterName', '' );
			
			$aVisibleFields = array();
			$aVisibleFields['nShiftsCount'] 	= Params::get( "nShiftsCount", 		0 );
			$aVisibleFields['nDayShifts'] 		= Params::get( "nDayShifts", 		0 );
			$aVisibleFields['nNightShifts'] 	= Params::get( "nNightShifts", 		0 );
			$aVisibleFields['nSickDays'] 		= Params::get( "nSickDays", 		0 );
			$aVisibleFields['nLeaveDays'] 		= Params::get( "nLeaveDays", 		0 );
			$aVisibleFields['nOverallShifts'] 	= Params::get( "nOverallShifts", 	0 );
			$aVisibleFields['nHolidayHours'] 	= Params::get( "nHolidayHours", 	0 );
			$aVisibleFields['nExtraHours'] 		= Params::get( "nExtraHours", 		0 );
			$aVisibleFields['nNormHours'] 		= Params::get( "nNormHours", 		0 );
			$aVisibleFields['nYearExtraHours'] 	= Params::get( "nYearExtraHours", 	0 );
			
			if( empty( $sName ) )
			{
				throw new Exception( "Въведете име на филтъра" );
			}
			
			$nIDPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			
			$oDBFilters 				= new DBFilters();
			$oDBFiltersVisibleFields 	= new DBFiltersVisibleFields();
			
			if( !empty( $nIsDefault ) )
			{
				$oDBFilters->resetDefaults( "DBShiftsCount", $nIDPerson );
			}
			
			$aFilter = array();
			
			if( !empty( $nID ) )$aFilter['id'] = $nID;
			$aFilter['name'] = $sName;
			$aFilter['id_person'] = $nIDPerson;
			$aFilter['is_default'] = $nIsDefault;
			$aFilter['report_class'] = "DBShiftsCount";
			
			$nResult = $oDBFilters->update( $aFilter );
			if( $nResult != DBAPI_ERR_SUCCESS )
			{
				throw new Exception( "Грешка при запазване на филтъра!", $nResult );
			}
			
			$oDBFiltersVisibleFields->delByIDFilter( $aFilter['id'] );
			
			foreach( $aVisibleFields as $sKey => $nValue )
			{
				if( !empty( $nValue ) )
				{
					$aData = array();
					$aData['id_filter'] = $aFilter['id'];
					$aData['field_name'] = $sKey;
					
					$oDBFiltersVisibleFields->update( $aData );
				}
			}
		}
	}

?>
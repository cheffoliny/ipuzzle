<?php

	class ApiSetupClientsFilter
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
				$aFilterParams 		= $oFiltersParams->getParamsByIDFilter( $nID );
				
				$oResponse->setFormElement( 'form1', 'filter_name', array(), $aFilter['name'] );
				
				if( !empty( $aFilter['is_default'] ) )
				{
					$oResponse->setFormElement( 'form1', 'is_default', array( "checked" => "checked" ) );
				}
				
				if( !empty( $aFilter['is_auto'] ) )
				{
					$oResponse->setFormElement( 'form1', 'nAuto', array( "checked" => "checked" ) );
					
					$sRealDate = "";
					$aSQLDate = explode( "-", $aFilter['auto_start_date'] );
					if( !empty( $aSQLDate ) )
					{
						$sRealDate = $aSQLDate[2] . "." . $aSQLDate[1] . "." . $aSQLDate[0];
					}
					
					$oResponse->setFormElement( 'form1', 'sFromDate', array( "value" => $sRealDate ), $sRealDate );
					$oResponse->setFormElement( 'form1', 'sPeriod', array( "value" => $aFilter['auto_period'] ), $aFilter['auto_period'] );
				}
				
				foreach( $aVisibleColumns as $key => $value )
				{
					if( !empty( $value ) )
					{
						$oResponse->setFormElement( 'form1', $value, array( "checked" => "checked" ) );
					}
				}
				
				foreach( $aFilterParams as $key => $value )
				{
					if( $key != "nBringInvoice" 	&&
						$key != "nSalesDocsToPay" 	&&
						$key != "nSingleToPay" 			)
					{
						$oResponse->setFormElement( 'form1', $key, array( "value" => $value ), $value );
					}
					else
					{
						if( $value == 1 )
						{
							$oResponse->setFormElement( 'form1', $key, array( "checked" => "checked" ) );
						}
						else
						{
							$oResponse->setFormElement( 'form1', $key, array( "checked" => "" ) );
						}
					}
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save()
		{
			$oFilters 				= new DBFilters();
			$oFiltersVisibleFields 	= new DBFiltersVisibleFields();
			$oFiltersParams 		= new DBFiltersParams();
			$oFiltersTotals			= new DBFiltersTotals();
			
			$nID 		= Params::get( 'nID', 0 );
			$nIsDefault = Params::get( 'is_default', 0) ;
			$sName 		= Params::get( 'filter_name', '' );
			$nAuto 		= Params::get( 'nAuto', '0' );
			$sDateFrom 	= Params::get( 'sFromDate', '' );
			$sPeriod 	= Params::get( 'sPeriod', 'day' );
			
			$aVisibleColumns = array();
			$aVisibleColumns['name'] 					= Params::get( 'name', '' );
			$aVisibleColumns['invoice_ein'] 			= Params::get( 'invoice_ein', '' );
			$aVisibleColumns['invoice_mol'] 			= Params::get( 'invoice_mol', '' );
			$aVisibleColumns['invoice_address'] 		= Params::get( 'invoice_address', '' );
			$aVisibleColumns['address'] 				= Params::get( 'address', '' );
			$aVisibleColumns['email'] 					= Params::get( 'email', '' );
			$aVisibleColumns['phone'] 					= Params::get( 'phone', '' );
			$aVisibleColumns['invoice_bring_to_object'] = Params::get( 'invoice_bring_to_object', 0 );
			$aVisibleColumns['invoice_payment'] 		= Params::get( 'invoice_payment', '' );
			$aVisibleColumns['invoice_layout'] 			= Params::get( 'invoice_layout', '' );
			$aVisibleColumns['object_name'] 			= Params::get( 'object_name', '' );
			$aVisibleColumns['object_num'] 				= Params::get( 'object_num', '' );
			$aVisibleColumns['object_city'] 			= Params::get( 'object_city', '' );
			
			$aFilterParams = array();
			$aFilterParams['sName'] 			= Params::get( 'sName', '' );
			$aFilterParams['sEIN'] 				= Params::get( 'sEIN', '' );
			$aFilterParams['sMOL'] 				= Params::get( 'sMOL', '' );
			$aFilterParams['sInvoiceAddress'] 	= Params::get( 'sInvoiceAddress', '' );
			$aFilterParams['sAddress'] 			= Params::get( 'sAddress', '' );
			$aFilterParams['sEmail'] 			= Params::get( 'sEmail', '' );
			$aFilterParams['sPhone'] 			= Params::get( 'sPhone', '' );
			$aFilterParams['nBringInvoice'] 	= Params::get( 'nBringInvoice', 0 );
			$aFilterParams['sPayment'] 			= Params::get( 'sPayment', '' );
			$aFilterParams['sLayout'] 			= Params::get( 'sLayout', '' );
			$aFilterParams['sIsPaid'] 			= Params::get( 'sIsPaid', '' );
			$aFilterParams['nSalesDocsToPay'] 	= Params::get( 'nSalesDocsToPay', '' );
			$aFilterParams['nSingleToPay'] 		= Params::get( 'nSingleToPay', '' );
			$aFilterParams['sObjectName'] 		= Params::get( 'sObjectName', '' );
			$aFilterParams['sObjectNum'] 		= Params::get( 'sObjectNum', '' );
			$aFilterParams['sObjectCity'] 		= Params::get( 'sObjectCity', '' );
			
			$aFilterTotals['id_filter'] 		= $nID;
			$aFilterTotals['total_name'] 		= "total_count";
			
			if( empty( $sName ) )
			{
				throw new Exception( "Въведете име на филтъра" );
			}
			
			$nIDPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			
			if( !empty( $nIsDefault ) )
			{
				$oFilters->resetDefaults( "DBClients", $nIDPerson );
			}
			
			$aFilter = array();
			
			if( !empty( $nID ) )
			{
				$aFilter['id'] = $nID;
				$oFiltersVisibleFields->delByIDFilter( $nID );
				$oFiltersParams->delParamsByIDFilter( $nID );
				$oFiltersTotals->delFilterTotalsByIDFilter( $nID );
			}
			
			$aFilter['name'] = $sName;
			$aFilter['report_class'] = "DBClients";
			$aFilter['is_default'] = $nIsDefault;
			$aFilter['id_person'] = $nIDPerson;
			$aFilter['is_auto'] = $nAuto;
			$aFilter['auto_period'] = $sPeriod;
			
			$aRealDate = explode( ".", $sDateFrom );
			$sSQLDate = "";
			if( !empty( $aRealDate ) )
			{
				$sSQLDate = $aRealDate[2] . "-" . $aRealDate[1] . "-" . $aRealDate[0];
			}
			
			$aFilter['auto_start_date'] = $sSQLDate;
			
			$oFilters->update( $aFilter );
			
			foreach( $aVisibleColumns as $key => $value )
			{
				$aBuffer = array();
				if( !empty( $value ) )
				{
					$aBuffer['id_filter'] = $aFilter['id'];
					$aBuffer['field_name'] = $key;
					
					$oFiltersVisibleFields->update( $aBuffer );
				}
			}
			
			foreach( $aFilterParams as $key => $value )
			{
				$aBuffer = array();
				
				$aBuffer['id_filter'] = $aFilter['id'];
				$aBuffer['name'] = $key;
				$aBuffer['value'] = $value;
					
				$oFiltersParams->update( $aBuffer );
			}
			
			$oFiltersTotals->update( $aFilterTotals );
		}
	}

?>
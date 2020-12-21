<?php

	class ApiSetupClients
	{
		public function load( DBResponse $oResponse )
		{
			$oFilters = new DBFilters();
			
			$nIDPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			
			$aFilters = array();
			$aFilters = $oFilters->getFiltersByReportClass( "DBClients", $nIDPerson );
			
			$oResponse->setFormElement( 'form1', 'schemes' );
			$oResponse->setFormElementChild( 'form1', 'schemes', array( "value" => "0" ), "---Изберете---" );
			
			foreach( $aFilters as $key => $value )
			{
				if( $value['is_default'] == '1' )
				{
					$oResponse->setFormElementChild( 'form1', 'schemes', array( "value" => $key, "selected" => "selected" ), $value['name'] );
				}
				else
				{
					$oResponse->setFormElementChild( 'form1', 'schemes', array( "value" => $key ), $value['name'] );
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oClients = new DBClients();
			
			$oClients->getReport( $aParams , $oResponse );
			
			$oResponse->printResponse( "Клиенти", "clients" );
		}
		
		public function deleteFilter()
		{
			$nIDFilter = Params::get( 'schemes', '0' );
			
			$oFilters 				= new DBFilters();
			$oFiltersVisibleFields 	= new DBFiltersVisibleFields();
			$oFiltersParams 		= new DBFiltersParams();
			
			$oFiltersVisibleFields->delByIDFilter( $nIDFilter );
			$oFiltersParams->delParamsByIDFilter( $nIDFilter );
			$oFilters->delete( $nIDFilter );
		}
	}
?>
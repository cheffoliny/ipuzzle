<?php

	class ApiSetupDirections
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBDirections = new DBDirections();
			$oDBDirections->getReport( $aParams, $oResponse );
			
			$oResponse->printResponse( "Направления", "directions" );
		}
		
		public function checkRegions( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			$oDBOfficesDirections = new DBOfficesDirections();
			
			$bIsInOffice = $oDBOfficesDirections->isDirectionInAnOffice( $nID );
			
			if( $bIsInOffice )
			{
				$oResponse->setFormElement( "form1", "nIsInOffice", array( "value" => 1 ), 1 );
			}
			else
			{
				$oResponse->setFormElement( "form1", "nIsInOffice", array( "value" => 0 ), 0 );
			}
			
			$oResponse->printResponse();
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			$oDBDirections 			= new DBDirections();
			$oDBOfficesDirections 	= new DBOfficesDirections();
			
			$oDBOfficesDirections->deleteByDirection( $nID );
			$oDBDirections->delete( $nID );
			
			$oResponse->printResponse();
		}
	}
?>
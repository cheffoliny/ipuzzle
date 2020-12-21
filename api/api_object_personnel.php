<?php

	class ApiObjectPersonnel 
	{
		public function result( DBResponse $oResponse )
		{
			$oDBPersonnel = new DBPersonnel();
			$oDBPersonnel->getReport( $oResponse );
			
			$oResponse->printResponse();
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nIDPerson = Params::get("nIDPerson", 0);
			
			$oDBPersonnel = new DBPersonnel();
			$oDBPersonnel->detachPersonFromObject( $nIDPerson );
			
			$oResponse->printResponse();
		}
		
		public function addPerson( DBResponse $oResponse ) {
			$nIDObject   = Params::get("nID", 0);
			$nPersonCode = Params::get("nPersonCode", 0);
			$sPersonName = Params::get("sPersonName", "");
			
			if( empty( $nIDObject ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nPersonCode ) && empty( $sPersonName ) )
				throw new Exception("Изберете служител!");
				
			$aPerson = array();
			$oPersonnel = new DBPersonnel();
			
			if ( !empty( $nPersonCode ) ) {
				$aPerson = $oPersonnel->getPersonnelByCode( $nPersonCode );
			}
				
			if ( empty( $aPerson ) ) {
				if( !empty( $sPersonName ) ) {
					$aPerson = $oPersonnel->getPersonnelByNames( $sPersonName ); 
				}
			}
			
			if ( empty( $aPerson ) ) {
				throw new Exception("Служителя неможе да бъде намерен!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$oPersonnel->attachPersonToObject($aPerson['id'], $nIDObject);
			
			$oResponse->printResponse();
		}
	}
	
?>
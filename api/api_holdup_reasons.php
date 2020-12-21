<?php

	class ApiHoldupReasons
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oHoldupReasons = new DBHoldupReasons();
			$oHoldupReasons->getReport( $aParams, $oResponse );
			
			$oResponse->printResponse( "Причини за профилактика", "holdup_reasons" );
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			$oHoldupReasons = new DBHoldupReasons();
			$oHoldupReasons->delete( $nID );
			
			$oResponse->printResponse();
		}
	}

?>
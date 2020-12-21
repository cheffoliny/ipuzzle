<?php

	class ApiSetSetupDirection
	{
		public function get( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			if( !empty( $nID ) )
			{
				$oDBDirections = new DBDirections();
				$aDirection = $oDBDirections->getRecord( $nID );
				
				$oResponse->setFormElement( "form1", "sName", array( "value" => $aDirection["name"] ) );
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$sName = Params::get( "sName", "" );
			
			if( empty( $sName ) )
			{
				throw new Exception( "Въведете наименование!", DBAPI_ERR_INVALID_PARAM );
			}
			
			$aData = array();
			$aData["id"] 	= Params::get( "nID", 0 );
			$aData["name"] 	= $sName;
			
			$oDBDirections = new DBDirections();
			$oDBDirections->update( $aData );
		}
	}

?>
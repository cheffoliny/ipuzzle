<?php

	class ApiSetObjectType
	{
		public function get( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			if( !empty( $nID ) )
			{
				$oObjectTypes = new DBObjectTypes();
				$aType = $oObjectTypes->getRecord( $nID );
				
				$oResponse->setFormElement( 'form1', 'sName', array( 'value' => $aType['name'] ) );
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$sName = Params::get( "sName" );
			
			if( empty( $sName ) )
				throw new Exception( "Въведете тип!", DBAPI_ERR_INVALID_PARAM );
			
			$aData = array();
			$aData['id'] = Params::get( 'nID', 0 );
			$aData['name'] = $sName;
			
			$oObjectTypes = new DBObjectTypes();
			$oObjectTypes->update( $aData );
		}
	}

?>
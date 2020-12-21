<?php

	class ApiSetObjectFunction
	{
		public function get( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			if( !empty( $nID ) )
			{
				$oObjectFunctions = new DBObjectFunctions();
				$aFunction = $oObjectFunctions->getRecord( $nID );
				
				$oResponse->setFormElement( 'form1', 'sName', array( 'value' => $aFunction['name'] ) );
				if( $aFunction['is_sod'] )
				{
					$oResponse->setFormElementAttribute( 'form1', 'nIsSod', "checked", "checked" );
				}
				else
				{
					$oResponse->setFormElementAttribute( 'form1', 'nIsSod', "checked", "" );
				}
				if( $aFunction['is_fo'] )
				{
					$oResponse->setFormElementAttribute( 'form1', 'nIsFo', "checked", "checked" );
				}
				else
				{
					$oResponse->setFormElementAttribute( 'form1', 'nIsFo', "checked", "" );
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$sName = 	Params::get( "sName" );
			$nIsSod = 	Params::get( "nIsSod", 0 );
			$nIsFo = 	Params::get( "nIsFo", 0 );
			
			if( empty( $sName ) )
				throw new Exception( "Въведете наименование!", DBAPI_ERR_INVALID_PARAM );
			
			$aData = array();
			$aData['id'] = Params::get( 'nID', 0 );
			$aData['name'] = $sName;
			$aData['is_sod'] = $nIsSod;
			$aData['is_fo'] = $nIsFo;
			
			$oObjectFunctions = new DBObjectFunctions();
			$oObjectFunctions->update( $aData );
		}
	}

?>
<?php

	class ApiSetObjectStatus
	{
		public function get( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			if( !empty( $nID ) )
			{
				$oStatuses = new DBStatuses();
				$aStatus = $oStatuses->getRecord( $nID );
				
				$oResponse->setFormElement( 'form1', 'sName', array( 'value' => $aStatus['name'] ) );
				
				if( $aStatus['is_sod'] )
				{
					$oResponse->setFormElementAttribute( 'form1', 'nIsSod', "checked", "checked" );
				}
				else
				{
					$oResponse->setFormElementAttribute( 'form1', 'nIsSod', "checked", "" );
				}
				
				if( $aStatus['play'] )
				{
					$oResponse->setFormElementAttribute( 'form1', 'nPlay', "checked", "checked" );
				}
				else
				{
					$oResponse->setFormElementAttribute( 'form1', 'nPlay', "checked", "" );
				}
				
				if( $aStatus['payable'] )
				{
					$oResponse->setFormElementAttribute( 'form1', 'nPayable', "checked", "checked" );
				}
				else
				{
					$oResponse->setFormElementAttribute( 'form1', 'nPayable', "checked", "" );
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$sName 		= Params::get( "sName" );
			$nIsSod 	= Params::get( "nIsSod", 0 );
			$nPlay 		= Params::get( "nPlay", 0 );
			$nPayable 	= Params::get( "nPayable", 0 );
			
			if( empty( $sName ) )
				throw new Exception( "Въведете наименование!", DBAPI_ERR_INVALID_PARAM );
			
			$aData = array();
			$aData['id'] 		= Params::get( 'nID', 0 );
			$aData['name'] 		= $sName;
			$aData['is_sod'] 	= $nIsSod;
			$aData['play'] 		= $nPlay;
			$aData['payable'] 	= $nPayable;
			
			$oStatuses = new DBStatuses();
			$oStatuses->update( $aData );
		}
	}

?>
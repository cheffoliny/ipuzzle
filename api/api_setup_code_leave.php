<?php

	class ApiSetupCodeLeave
	{
		// remote method
		public function init( DBResponse $oResponse )
		{
			$aData = array();
			
			$oDBCodeLeave = new DBCodeLeave();
			$aData = $oDBCodeLeave->getResultData();
			
			$oResponse->setFlexVar( "aData", $aData );
			
			$oResponse->printResponse();
		}
		
		// remote method
		public function save( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBCodeLeave = new DBCodeLeave();
			
			foreach( $aParams['result_data'] as $nKey => $aValue )
			{
				$aRecord = parseObjectToArray( $aValue );
				
				if( $aRecord['isEditted'] || $aRecord['id'] == 0 )
				{
					$oDBCodeLeave->update( $aRecord );
				}
			}
			
			if( isset( $aParams['sDelIDs'] ) && !empty( $aParams['sDelIDs'] ) )
			{
				$aIDsToDel = explode( ",", $aParams['sDelIDs'] );
				
				foreach( $aIDsToDel AS $nIDToDel )
				{
					$oDBCodeLeave->delete( $nIDToDel );
				}
			}
			
			$this->init( $oResponse );
		}
	}

?>
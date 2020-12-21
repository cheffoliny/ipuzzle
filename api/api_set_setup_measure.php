<?php

	class ApiSetSetupMeasure
	{
		public function get( DBResponse $oResponse )	
		{
			$nID = Params::get("nID", 0);
			
			if( !empty( $nID ) )
			{
				$oMeasures = new DBMeasures();
				$aMeasure = $oMeasures->getRecord( $nID );
				
				$oResponse->setFormElement('form1', 'sCode', 		array('value' => $aMeasure['code']));
				$oResponse->setFormElement('form1', 'sDescription', array('value' => $aMeasure['description']));
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$sCode = 		Params::get("sCode");
			$sDescription = Params::get("sDescription");
			
			if( empty( $sCode ) )
				throw new Exception("Въведете име на причина!", DBAPI_ERR_INVALID_PARAM);
			if( empty( $sDescription ) )
				throw new Exception("Въведете име на причина!", DBAPI_ERR_INVALID_PARAM);
				
			$aData = array();
			$aData['id'] = Params::get('nID', 0);
			$aData['code'] = $sCode;
			$aData['description'] = $sDescription;
			
			$oMeasures = new DBMeasures();
			$oMeasures->update( $aData );
		}
	}
	
?>
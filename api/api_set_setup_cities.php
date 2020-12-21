<?php

	class ApiSetSetupCities
	{
		public function get( DBResponse $oResponse )	
		{
			$nID = Params::get("nID", 0);
			
			if( !empty( $nID ) )
			{
				$oCities = new DBCities();
				$aCity = $oCities->getRecord( $nID );
				
				$oResponse->setFormElement( 'form1', 'nPostCode', 	array( 'value' => $aCity['post_code'] ) );
				$oResponse->setFormElement( 'form1', 'sName', 		array( 'value' => $aCity['name'] ) );
			}
			else
			{
				$oResponse->setFormElement( 'form1', 'nPostCode', 	array( 'value' => 0 ) );
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$sName = 		Params::get("sName");
			$nPostCode = 	Params::get("nPostCode");
			
			if( empty( $sName ) )
				throw new Exception("Въведете Наименование!", DBAPI_ERR_INVALID_PARAM);
			
			if( empty( $nPostCode ) || !is_numeric( $nPostCode ) || $nPostCode < 0 )
				throw new Exception("Въведете валиден Пощенски Код!", DBAPI_ERR_INVALID_PARAM);
				
			$aData = array();
			$aData['id'] = Params::get('nID', 0);
			$aData['name'] = $sName;
			$aData['post_code'] = $nPostCode;
			
			$oCities = new DBCities();
			$oCities->update( $aData );
		}
	}
	
?>
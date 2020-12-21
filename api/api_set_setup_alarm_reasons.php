<?php

	class ApiSetSetupAlarmReasons
	{
			public function get( DBResponse $oResponse )	
			{
					$nID = Params::get("nID", 0);
					
					if( !empty( $nID ) )
					{
							$oReasons = new DBAlarmReasons();
							$aReason = $oReasons->getRecord( $nID );
							
							$oResponse->setFormElement('form1', 'sName', array('value' => $aReason['name']));	
					}
					
					$oResponse->printResponse();
			}
			
			public function save( DBResponse $oResponse )
			{
					$sName = Params::get("sName");
					
					if( empty( $sName ) )
						throw new Exception("Въведете име на причина!", DBAPI_ERR_INVALID_PARAM);
						
					$aData = array();
					$aData['id'] = Params::get('nID', 0);
					$aData['name'] = $sName;
					
					$oReasons = new DBAlarmReasons();
					$oReasons->update( $aData );
			}
			
	}
	
?>
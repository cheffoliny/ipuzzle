<?php

	class ApiSetSetupCompensation
	{
		public function get( DBResponse $oResponse )
		{
			$nID = Params::get("nID", 0);
			
			if( !empty( $nID ) )
			{
					$oCompensations = new DBCompensations();
					$aCompensation = $oCompensations->getRecord( $nID );
					
					$oResponse->setFormElement('form1', 'sType', 	array('value' => $aCompensation['type']));
					$oResponse->setFormElement('form1', 'nYearly', 	array('value' => $aCompensation['yearly']));
					$oResponse->setFormElement('form1', 'nSingle', 	array('value' => $aCompensation['single']));
					$oResponse->setFormElement('form1', 'nFactor', 	array('value' => $aCompensation['factor']));
			}
			else
			{
					$oResponse->setFormElement('form1', 'sType', 	array('value' => 'mdo'));
					$oResponse->setFormElement('form1', 'nYearly', 	array('value' => 0.00));
					$oResponse->setFormElement('form1', 'nSingle', 	array('value' => 0.00));
					$oResponse->setFormElement('form1', 'nFactor', 	array('value' => 1.00));
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$sType 		= Params::get("sType");
			$nYearly 	= Params::get("nYearly");
			$nSingle 	= Params::get("nSingle");
			$nFactor 	= Params::get("nFactor");
			
			if( empty( $sType ) )
				throw new Exception("Въведете тип!", DBAPI_ERR_INVALID_PARAM);
			
			if( !is_numeric( $nYearly ) || !is_numeric( $nSingle ) || !is_numeric( $nFactor ) )
			{
				throw new Exception("Въведена е невалидна стойност!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if( $nFactor < 1 )
			{
				throw new Exception("Коефициента не може да е по-нисък от 1!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$aData = array();
			$aData['id'] = Params::get('nID', 0);
			$aData['type'] = $sType;
			$aData['yearly'] = $nYearly;
			$aData['single'] = $nSingle;
			$aData['factor'] = $nFactor;
			
			$oCompensations = new DBCompensations();
			$oCompensations->update( $aData );
		}
	}
	
?>
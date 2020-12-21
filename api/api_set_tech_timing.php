<?php

	class ApiSetTechTiming
	{
		public function get( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			if( !empty( $nID ) )
			{
				$oTechTiming = new DBTechTiming();
				$aTechTiming = $oTechTiming->getRecord( $nID );
				
				$oResponse->setFormElement( 'form1', 'nMinutes', array( 'value' => $aTechTiming['minute'] ) );
				if( $aTechTiming['name'] == 'create' )
				{
					$oResponse->setFormElement( 'form1', 'nStepDetector', array( 'value' => $aTechTiming['step_detector'] ) );
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$nID = 				Params::get( "nID", 0 );
			$sLatinType = 		Params::get( "sType", "" );
			$nMinutes = 		Params::get( "nMinutes", 0 );
			$nStepDetector = 	Params::get( "nStepDetector", 0 );
			
			if( empty( $nID ) )
				throw new Exception( "Грешка при съхранение на информацията!", DBAPI_ERR_INVALID_PARAM );
			
			$aData = array();
			$aData['id'] = $nID;
			$aData['minute'] = $nMinutes;
			if( $sType = "create" )
			{
				$aData['step_detector'] = $nStepDetector;
			}
			
			$oTechTiming = new DBTechTiming();
			$oTechTiming->update( $aData );
		}
	}

?>
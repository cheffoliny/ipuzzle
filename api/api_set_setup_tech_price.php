<?php

	class ApiSetSetupTechPrice
	{
		public function get( DBResponse $oResponse )
		{
			$nID = Params::get("nID", 0);
			
			if( !empty( $nID ) )
			{
				$oTechPrices = new DBTechPrices();
				$aTechPrice = $oTechPrices->getRecord( $nID );
				
				$oResponse->setFormElement('form1', 'nBasePrice', 		array('value' => $aTechPrice['base_price']));
				$oResponse->setFormElement('form1', 'nFactor', 			array('value' => $aTechPrice['factor']));
				$oResponse->setFormElement('form1', 'sPriceListDate', 	array('value' => $this->timeSQLToStandard( $aTechPrice['price_list_date'] )));
			}
//			else
//			{
//				$oResponse->setFormElement('form1', 'nBasePrice', 		array('value' => 0));
//				$oResponse->setFormElement('form1', 'nFactor', 			array('value' => 1));
//				$oResponse->setFormElement('form1', 'sPriceListDate', 	array('value' => date( 'd.m.Y' )));
//			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$nBasePrice 	= Params::get("nBasePrice");
			$nFactor 		= Params::get("nFactor");
			$sPriceListDate = Params::get("sPriceListDate");
			
			if( !is_numeric( $nBasePrice ) || !is_numeric( $nFactor ) )
			{
				throw new Exception("Въведена е невалидна стойност!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$aData = array();
			$aData['id'] = Params::get('nID', 0);
			$aData['base_price'] = $nBasePrice;
			$aData['factor'] = $nFactor;
			$aData['price_list_date'] = $this->timeStandardToSQL( $sPriceListDate );
			
			$oTechPrices = new DBTechPrices();
			$oTechPrices->update( $aData );
		}
		
		public function timeStandardToSQL( $sTime )
		{
			return substr( $sTime, 6, 4 ) . '-' . substr( $sTime, 3, 2 ) . '-' . substr( $sTime, 0, 2 );
		}
		
		public function timeSQLToStandard( $sTime )
		{
			return substr( $sTime, 8, 2 ) . '.' . substr( $sTime, 5, 2 ) . '.' . substr( $sTime, 0, 4 );
		}
	}

?>
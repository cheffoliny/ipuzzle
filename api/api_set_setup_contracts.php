<?php

	class ApiSetSetupContracts {
		
		public function get( DBResponse $oResponse ) {
			$nID = Params::get("nID", 0);
			
			$oResponse->setFormElement( "form1", "nIDService", 	array(), "" );
			$oResponse->setFormElementChild( "form1", "nIDService", 	array('value' => 0), " .:: Изберете ::." );
			
			if ( !empty($nID) ) {
				$oContract 		= new DBSetupContracts();
				$aContract 		= $oContract->getRecord( $nID );
				
				$service_type 	= isset($aContract['service_type']) ? $aContract['service_type'] : "";
				$service_name 	= isset($aContract['service_name']) ? $aContract['service_name'] : "";
				$is_single 		= isset($aContract['is_single']) ? $aContract['is_single'] : 0;
				
				$oResponse->setFormElement( "form1", "sCode", 	array('value' => $service_type) );
				$oResponse->setFormElement( "form1", "sName", 	array('value' => $service_name) );
				
				if ( $is_single == 1 ) {
					$oResponse->setFormElement( "form1", "chSingle", array('checked' => 'checked') );
				} else {
					$oResponse->setFormElement( "form1", "chMonth", array('checked' => 'checked') );
				}
			} else {
				$oResponse->setFormElement( "form1", "chMonth", array('checked' => 'checked') );
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$sType = 				Params::get("sType");
			$nBasePrice = 			Params::get("nBasePrice");
			$nFactorDetector = 		Params::get("nFactorDetector");
			$nKClientTech = 		Params::get("nKClientTech");
			$nPriceRadioPanic = 	Params::get("nPriceRadioPanic");
			$nPriceStaticPanic = 	Params::get("nPriceStaticPanic");
			$nPriceKbdPanic = 		Params::get("nPriceKbdPanic");
			$nPriceOnlineBill = 	Params::get("nPriceOnlineBill");
			$nPriceTelepolVest = 	Params::get("nPriceTelepolVest");
			$nExpressOrderPrice = 	Params::get("nExpressOrderPrice");
			$nFastOrderPrice = 		Params::get("nFastOrderPrice");
			
			if( empty( $sType ) )
				throw new Exception("Въведете тип!", DBAPI_ERR_INVALID_PARAM);
			
			if( !is_numeric( $nBasePrice )
				|| !is_numeric( $nFactorDetector )
				|| !is_numeric( $nKClientTech )
				|| !is_numeric( $nPriceRadioPanic )
				|| !is_numeric( $nPriceStaticPanic )
				|| !is_numeric( $nPriceKbdPanic )
				|| !is_numeric( $nPriceOnlineBill )
				|| !is_numeric( $nPriceTelepolVest )
				|| !is_numeric( $nExpressOrderPrice )
				|| !is_numeric( $nFastOrderPrice ) 		)
			{
				throw new Exception("Въведена е невалидна стойност!", DBAPI_ERR_INVALID_PARAM);
			}
				
			$aData = array();
			$aData['id'] = Params::get('nID', 0);
			$aData['type'] = $sType;
			$aData['base_price'] = $nBasePrice;
			$aData['factor_detector'] = $nFactorDetector;
			$aData['k_client_tech'] = $nKClientTech;
			$aData['price_radio_panic'] = $nPriceRadioPanic;
			$aData['price_static_panic'] = $nPriceStaticPanic;
			$aData['price_kbd_panic'] = $nPriceKbdPanic;
			$aData['price_online_bill'] = $nPriceOnlineBill;
			$aData['price_telepol_vest'] = $nPriceTelepolVest;
			$aData['expres_order_price'] = $nExpressOrderPrice;
			$aData['fast_order_price'] = $nFastOrderPrice;
			
			$oCharges = new DBContractMonthCharges();
			$oCharges->update( $aData );
		}
	}
	
?>
<?php

	class ApiSetSetupContractMonthCharge
	{
		public function get( DBResponse $oResponse )
		{
			$nID = Params::get("nID", 0);
			
			if( !empty( $nID ) )
			{
					$oCharges = new DBContractMonthCharges();
					$aCharge = $oCharges->getRecord( $nID );
					
					$oResponse->setFormElement('form1', 'sType', 				array('value' => $aCharge['type']));
					$oResponse->setFormElement('form1', 'nBasePrice', 			array('value' => $aCharge['base_price']));
					$oResponse->setFormElement('form1', 'nFactorDetector', 		array('value' => $aCharge['factor_detector']));
					$oResponse->setFormElement('form1', 'nKClientTech', 		array('value' => $aCharge['k_client_tech']));
					
					$oResponse->setFormElement('form1', 'nPriceRadioPanic', 	array('value' => $aCharge['price_radio_panic']));
					$oResponse->setFormElement('form1', 'nPriceStaticPanic', 	array('value' => $aCharge['price_static_panic']));
					$oResponse->setFormElement('form1', 'nPriceKbdPanic', 		array('value' => $aCharge['price_kbd_panic']));
					$oResponse->setFormElement('form1', 'nPriceOnlineBill', 	array('value' => $aCharge['price_online_bill']));
					$oResponse->setFormElement('form1', 'nPriceTelepolVest', 	array('value' => $aCharge['price_telepol_vest']));

					$oResponse->setFormElement('form1', 'nExpressOrderPrice', 	array('value' => $aCharge['expres_order_price']));
					$oResponse->setFormElement('form1', 'nFastOrderPrice', 		array('value' => $aCharge['fast_order_price']));
			}
			else
			{
					$oResponse->setFormElement('form1', 'sType', 				array('value' => 'mdo'));
					$oResponse->setFormElement('form1', 'nBasePrice', 			array('value' => 0.00));
					$oResponse->setFormElement('form1', 'nFactorDetector', 		array('value' => 0.00));
					$oResponse->setFormElement('form1', 'nKClientTech', 		array('value' => 0.00));
					
					$oResponse->setFormElement('form1', 'nPriceRadioPanic', 	array('value' => 0.00));
					$oResponse->setFormElement('form1', 'nPriceStaticPanic', 	array('value' => 0.00));
					$oResponse->setFormElement('form1', 'nPriceKbdPanic', 		array('value' => 0.00));
					$oResponse->setFormElement('form1', 'nPriceOnlineBill', 	array('value' => 0.00));
					$oResponse->setFormElement('form1', 'nPriceTelepolVest', 	array('value' => 0.00));

					$oResponse->setFormElement('form1', 'nExpressOrderPrice', 	array('value' => 0.00));
					$oResponse->setFormElement('form1', 'nFastOrderPrice', 		array('value' => 0.00));
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
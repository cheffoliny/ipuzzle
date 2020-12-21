<?php
	class ApiSetTechSettings {
		
		public function load( DBResponse $oResponse ) {

			$aTechSettings = array();
			$oTechSettings = new DBTechSettings();
			
			$aTechSettings = $oTechSettings->getActiveSettings();
			
			if ( !empty($aTechSettings) ) {
				$oResponse->setFormElement('form1', 'sTechPriceDestroy', array('value' => $aTechSettings['tech_price_destroy']));	
				$oResponse->setFormElement('form1', 'sTechPriceArrange', array('value' => $aTechSettings['tech_price_arrange']));					
				$oResponse->setFormElement('form1', 'sTechPriceHoldup', array('value' => $aTechSettings['tech_price_holdup']));					
				$oResponse->setFormElement('form1', 'sTechPriceKm', array('value' => $aTechSettings['tech_price_km']));					
			}
						
			$oResponse->printResponse();
		}
			
		public function save( DBResponse $oResponse ) {
			$nID		= Params::get('nID', 0);
			$sTechPriceDestroy		= Params::get("sTechPriceDestroy");
			$sTechPriceArrange		= Params::get("sTechPriceArrange");
			$sTechPriceHoldup		= Params::get("sTechPriceHoldup");
			$sTechPriceKm			= Params::get("sTechPriceKm");
			
			if ( empty($sTechPriceDestroy) ) {
				throw new Exception("Въведете цена за сваляне!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($sTechPriceArrange) ) {
				throw new Exception("Въведете цена за аранжиране!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($sTechPriceKm) ) {
				throw new Exception("Въведете цена за преход на км!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$aData = array();
			$aData['id'] = $nID;
			$aData['tech_price_destroy'] = $sTechPriceDestroy;
			$aData['tech_price_arrange'] = $sTechPriceArrange;
			$aData['tech_price_holdup'] = $sTechPriceHoldup;
			$aData['tech_price_km'] = $sTechPriceKm;
			
			$oTechSettings = new DBTechSettings();
			$oTechSettings->update( $aData );
		}
			
	}
	
?>
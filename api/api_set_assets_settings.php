<?php
	class ApiSetAssetsSettings {
		
		public function load (DBResponse $oResponse) {

			$aAssetsSettings = array();
			$oAssetsSettings = new DBAssetsSettings();
			
			$aAssetsSettings = $oAssetsSettings->getActiveSettings();
			
			if ( !empty($aAssetsSettings ) )
			{
				$oResponse->setFormElement('form1', 'sAssetEarningCoef', array('value' => $aAssetsSettings['asset_earning_coef'] ) );	
				$oResponse->setFormElement('form1', 'sAssetOwnCoef', array('value' => $aAssetsSettings['asset_own_coef'] ) );					
								
			}
						
			$oResponse->printResponse();
		}
			
		public function save( DBResponse $oResponse ) {
			$nID		= Params::get('nID', 0);
			$sAssetEarningCoef	= Params::get("sAssetEarningCoef");
			$sAssetOwnCoef		= Params::get("sAssetOwnCoef");
			
			if ( empty($sAssetEarningCoef) ) {
				throw new Exception("Въведете коефициент наработка!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($sAssetOwnCoef) ) {
				throw new Exception("Въведете коефициент актив самоучастие!", DBAPI_ERR_INVALID_PARAM);
			}
			
						
			$aData = array();
			$aData['id'] 				 = $nID;
			$aData['asset_earning_coef'] = $sAssetEarningCoef;
			$aData['asset_own_coef'] 	 = $sAssetOwnCoef;
			
			
			$oAssetsSettings = new DBAssetsSettings();
			$oAssetsSettings->update( $aData );
		}
			
	}
	
?>
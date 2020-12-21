<?php
 class ApiSetAssetInfo {
 	public function load ( DBResponse $oResponse ) {
 		$aInfo		= array();
 		$oAsset 	= new DBAssets();
 		 		 
 		$nID = Params::get( 'nID', 0 );
 		
 		if($nID){
				$oAsset->getAssetInfo($nID,$aInfo);
				APILog::Log(0,$aInfo);
				$sPeriod = intval($aInfo[0]['amort_period']);
				$oResponse->setFormElement("form1","amort_period",array("value"=>$sPeriod));
 		}
 	
 		$oResponse->printResponse();
 	} 
 	
 	public function save( DBResponse $oResponse ) {
 		$nID 	 = Params::get( 'nID', 0 );
		$nPeriod = Params::get( 'amort_period', 0);
				
		$aInfo		= array();
 		$oAsset 	= new DBAssets();
		$oAsset->getAssetInfo($nID,$aInfo);
		APILog::Log(0,$aInfo);
 		$nPeriod2	= intval ($aInfo[0]['amort_period']);
 	
 		$now = date("m");
 		
 		$aData			= array();
 		$aData['id']	= $nID;
 		$aData['amortization_months']		= $nPeriod;
 		$aData['amortization_months_left']	= $nPeriod-intval($aInfo[0]['amort_period'])+intval($aInfo[0]['rest_term'])+1;
 		
 		if($nPeriod<=$nPeriod2) {
 			throw new Exception("Не може да се въвежда по-малък амортизационен период!!!", 0);
 		} 
 			else 	{
 				$oAsset->update($aData);
 				$aAmortChange = array();
 				$aAmortChange['id_asset']	= $nID;
 				$aAmortChange['old_value']	= $nPeriod2;
 				$aAmortChange['new_value']	= $nPeriod;
 		
 				$oDBAmortizationChanges = new DBAmortizationChanges();
 				$oDBAmortizationChanges->update($aAmortChange);
 				
 				$oDBSalaryEarning = new DBSalaryEarning();
 				$aSalaryEarning=$oDBSalaryEarning->getCodeEarning();

				$oDBAssetsSettings = new DBAssetsSettings();
				$aAssetsSettings = $oDBAssetsSettings->getActiveSettings();
				
 				$nRestPrice = ($nPeriod-intval($aInfo[0]['amort_period'])+intval($aInfo[0]['rest_term']))/$nPeriod*intval($aInfo[0]['aquire_price']);
 				 				
				$nSum = (intval($aInfo[0]['aquire_price']) - $nRestPrice)*($aAssetsSettings['asset_earning_coef']/100);
 				$nSum = round($nSum,2);
				 				
 				$aPerson = array();
 				$aPerson['id_person']	= $aInfo[0]['id_person'];
 				$aPerson['id_office']	= $aInfo[0]['id_region'];
 				$aPerson['month']		= date('Y'.'m');
 				$aPerson['code']		= $aSalaryEarning['code'];
 				$aPerson['is_earning']	= 1;
 				$aPerson['sum']			= $nSum;
 				$aPerson['description']	= $aSalaryEarning['name'];
 				$aPerson['count']		= 1;
 				$aPerson['total_sum']	= $aPerson['sum'];
 				
 				$oSalary = new DBSalary();
 				$oSalary->update($aPerson);
 				
 			}
 		 
 	}
 }
?>
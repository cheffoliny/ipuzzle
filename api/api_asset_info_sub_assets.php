<?php
		class	APIAssetInfoSubAssets
		{
			public function result(DBResponse $oResponse)
			{
				$oAsset = new DBAssets();
				$nID=Params::get("nID");
				//$aData = array();
				$aSubAssets = array();
				
				//$aData=$oAsset->getSubAssetInfo($nID,$aSubAssets);
				$this->getSubAssetsIds($nID,$aSubAssets);
				APILog::Log(0,$aSubAssets);
				$sSubAssets = implode(', ',$aSubAssets);
				$sSubAssetsIds = $nID;
				if(!empty($sSubAssets)){
					$sSubAssetsIds.=','.$sSubAssets;
				}
				$oAsset->getSubAssetInfo($nID,$sSubAssetsIds,$oResponse);
				$oResponse->printResponse();
				
			}
			
			public function getSubAssetsIds($nID,&$aId22)
			{
				
				$oAsset = new DBAssets();
				$aIDs=$oAsset->getSubAssetsIDs($nID);
				APILog::Log(0,$aIDs);
				if(count($aIDs)){
					for($i=0;$i<count($aIDs);$i++){
					array_push( $aId22,$aIDs[$i]["id"]);
					$this->getSubAssetsIds($aIDs[$i]["id"],$aId22);
					}
				}
		
			}
		}
?>
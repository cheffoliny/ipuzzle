<?php

	class ApiWasteNote {
		
		public function load(DBResponse $oResponse) {
			$nIDAsset = Params::get('nIDAsset','0');
			
			$oDBAssets = new DBAssets();
			$aAsset = $oDBAssets->getRecord($nIDAsset);
			
			global $aAssetsIDs;
			$oDBAssets->getSubAssetsIDsRecursive($nIDAsset);
			$sAssetsIDs = implode(',',$aAssetsIDs);
			$nPriceLeft = $oDBAssets->calcPriceLeft($sAssetsIDs);
			
			//throw new Exception($nIDAsset);
			
			$oResponse->setFormElement('form1','sAssetName',array(),$aAsset['name']." ".$nPriceLeft." лв.");
			$oResponse->printResponse();
		}
		
		public function save(DBResponse $oResponse) {
			
			$nIDAsset = Params::get('nIDAsset','0');
			$nIDPPP = Params::get('nIDPPP','0');
			$sNote = Params::get('sNote','');
			$sFor = Params::get('sFor','');
			$nIDPerson = Params::get('nIDPerson','0');
			
			
			if($sFor == 'person') {
				if(empty($nIDPerson)) {
					throw new Exception("Изберете служителят за сметка на когото ще се начисли удръжка");
				}
			}
			
			if(empty($nIDPPP)) {
				$oDBAssetsPPPs = new DBAssetsPPPs();
				
				$aPPP = array();
				$aPPP['created_user'] = $_SESSION['userdata']['id_person'];
				$aPPP['created_time'] = time();
				$aPPP['ppp_type'] = 'waste';
				
				$oDBAssetsPPPs->update($aPPP);
				$nIDPPP = $aPPP['id'];
				
				$oResponse->setFormElement('form1','nIDPPP',array(),$nIDPPP);
			}
			
			
			$oDBAssets = new DBAssets();
			$oDBAssetsPPPElements = new DBAssetsPPPElements();
			
			$aAsset = $oDBAssets->getRecord($nIDAsset);
			
			$aPPPElement = array();
			$aPPPElement['id_ppp'] = $nIDPPP;
			$aPPPElement['id_asset'] = $nIDAsset;
			$aPPPElement['source_type'] = $aAsset['storage_type'];
			$aPPPElement['id_source'] = $aAsset['id_storage'];
			$aPPPElement['dest_type'] = '';
			$aPPPElement['id_dest'] = '0';
			$aPPPElement['waste_note'] = $sNote;
			$aPPPElement['waste_person'] = $nIDPerson;
			$oDBAssetsPPPElements->update($aPPPElement);
			
			$oResponse->printResponse();
		}
	}

?>
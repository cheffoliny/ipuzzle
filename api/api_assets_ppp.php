<?php
	require_once( "pdf/pdf_assets_ppp.php" );

	class ApiAssetsPPP {
		
		public function result(DBResponse $oResponse) {
			
			$nID = Params::get('nID','0');
			$sApiAction = Params::get( "api_action", "" );
			
			$oDBAssetsPPPs = new DBAssetsPPPs();
			$oDBAssetsPPPElements = new DBAssetsPPPElements();
			$oDBAssetsStoragehouses = new DBAssetsStoragehouses();
			
			$aPPP = $oDBAssetsPPPs->getRecord($nID);
			
			if(!empty($aPPP['confirm_user'])) {
				$oResponse->setFormElement('form1','confirmed',array("checked" => "checked"));
			}
			
			
			$aStoragehouses = $oDBAssetsStoragehouses->getAssetsStoragehouses();
			
			$oResponse->setFormElement('form1','nIDStoragehouse',array());
			$oResponse->setFormElementChild('form1', 'nIDStoragehouse',	array('value' => '0'),"---Изберете---");
			foreach ( $aStoragehouses as $key => $val ) {
				$oResponse->setFormElementChild('form1', 'nIDStoragehouse',	array('value' => $key),$val);
			}		
			
			$oDBAssetsPPPElements->getReport($oResponse, $nID);
			
			if( $sApiAction == 'export_to_pdf' ) {
			    
				
				$oPDF = new apPDF('P');
				$oPDF -> PrintReport($oResponse, $nID) ;
				
			}
			
			$oResponse->printResponse(NULL,'assets_ppp');
	
		}
		public function del_asset(DBResponse $oResponse) {
			$nIDAsset = Params::get('nIDAssetToDel','');
			
			$oDBAssets = new DBAssets();
			$oDBAssetsPPPElements = new DBAssetsPPPElements();
			
			$aPPPElement = $oDBAssetsPPPElements->getRecord($nIDAsset);
			
			$oDBAssetsPPPElements -> delete($nIDAsset);
			
			$aAsset = $oDBAssets->getRecord($aPPPElement['id_asset']);
			
			if(empty($aAsset['status'])) {
				$oDBAssets->delete($aPPPElement['id_asset']);
			}
			
			
			$oResponse->printResponse();
		}
		
		public function add_asset(DBResponse $oResponse) {
			
			$nID = Params::get('nID','0');
			$sPPPType = Params::get('sPPPType','');
			$nIDAssetSource = Params::get('nIDAssetSource','0');			
			$nIDStorage = Params::get('nIDStoragehouse','0');
			$nCount = Params::get('nCount','1');
			
			
			if(empty($nIDAssetSource)) {
				throw new Exception('Няма избран актив');
			}
			
			$oDBAssets = new DBAssets();
			$oDBAssetsAttributes = new DBAssetsAttributes();
			$oDBAssetsPPPElements = new DBAssetsPPPElements();
			$aAsset = $oDBAssets->getRecord($nIDAssetSource);
			
			$aPPPElements = $oDBAssetsPPPElements->getElements($nID);
			
			foreach ($aPPPElements as $aElement) {
				if($aElement['id_asset'] == $nIDAssetSource) {
					throw new Exception('Активът е въведен вече');
				}
			}
			
			
			$sSourceType = $aAsset['storage_type'];
			$nIDSource = $aAsset['id_storage'];
			
			if($sPPPType == 'enter') {						// ПРИДОБИВАНЕ
			
				if(empty($nIDStorage)) {
					throw new Exception('Изберете приемащ склад');
				}
			
				if(empty($nCount) || !is_numeric($nCount) || $nCount < 1 || $nCount > 101) {
					throw new Exception('Въведете количество в диапазон от 1 до 100');
				}
				
				if(!empty($aAsset['status']) && $nCount != '1') {
					throw new Exception('Количество различно от единица е позволено само за нововъведени активи');
				}

				$sDestType = 'storagehouse';
				$nIDDest = $nIDStorage;
			
			} elseif( $sPPPType == 'attach' ) {				// ВЪВЕЖДАНЕ

				$sDestType = Params::get('sDestType','');
				
				if( $sDestType == 'person') {
					$nIDDest = Params::get('nIDPersonDest','');
					
					if(empty($nIDDest)) {
						throw new Exception('Не сте избразли приемащ служител');
					}
					
				} else {
					$nIDDest = Params::get('nIDAssetDest','');
					
					if(empty($nIDDest)) {
						throw new Exception('Не сте избрали приемащ актив');
					}
					
				}
				
				
			} else {										// БРАКУВАНЕ
				$sDestType = '';
				$nIDDest = 0;
			}
			
			if(empty($nID)) {
				$oDBAssetsPPPs = new DBAssetsPPPs();
				
				$aPPP = array();
				$aPPP['created_user'] = $_SESSION['userdata']['id_person'];
				$aPPP['created_time'] = time();
				$aPPP['ppp_type'] = $sPPPType;
				
				$oDBAssetsPPPs->update($aPPP);
				$nID = $aPPP['id'];
				
				//$oResponse->setFormElement('form1','refreshPage',array(),'yes');
				$oResponse->setFormElement('form1','nID',array(),$nID);
			}

			while( $nCount-- ) {
				
				$aPPPElement = array();
				$aPPPElement['id_ppp'] = $nID;
				$aPPPElement['id_asset'] = $nIDAssetSource;
				$aPPPElement['source_type'] = $sSourceType;
				$aPPPElement['id_source'] = $nIDSource;
				$aPPPElement['dest_type'] = $sDestType;
				$aPPPElement['id_dest'] = $nIDDest;
				$aPPPElement['add_time'] = time();
				$oDBAssetsPPPElements->update($aPPPElement);
			
				if(!empty($nCount)) {
					$aAsset['id'] = 0;
					$oDBAssets->update($aAsset);
					
					
					$aAttributes = $oDBAssetsAttributes->getAttributes($nIDAssetSource);
					foreach ($aAttributes as $value) {
						$aAttribute = array();
						$aAttribute['id_asset'] = $aAsset['id'];
						$aAttribute['id_attribute'] = $value['id_attribute'];
						$aAttribute['value'] = $value['value'];
						$oDBAssetsAttributes->update($aAttribute);
					}
					
					$nIDAssetSource = $aAsset['id'];
				}
			}
			$oDBAssetsPPPElements->getReport($oResponse, $nID);
			
			$oResponse->printResponse();
		}
		public function save(DBResponse $oResponse) {
			$nIDPPP = Params::get('nID','0');
			$sPPPType = Params::get('sPPPType','');
			$nIsConfirmed = Params::get('confirmed','0');
			
			$oDBAssets = new DBAssets();
			$oDBAssetsPPPs = new DBAssetsPPPs();
			$oDBAssetsPPPElements = new DBAssetsPPPElements();
			
			$aPPP = $oDBAssetsPPPs->getRecord($nIDPPP);
			$aPPPElements = $oDBAssetsPPPElements->getElements($nIDPPP);
				
			if(!empty($nIsConfirmed)) {
				
				if($sPPPType == 'enter') {						// ПРИДОБИВАНЕ
					
					foreach ($aPPPElements as $aElement) {
						
						global $aAssetsIDs;
						$aAssetsIDs = array();
						$oDBAssets->getSubAssetsIDsRecursive($aElement['id_asset']);
						
						foreach ($aAssetsIDs as $nIDAsset) {
						
							$aAsset = array();
							$aAsset = $oDBAssets->getRecord($nIDAsset);
							
							if($aAsset['status'] == 'attached') {
								$nAttachDate = strtotime($aAsset['attach_date']);
								$nMonthsToToday = (time() - $nAttachDate)/2592000;	
								
								if(floor($nMonthsToToday) < $aAsset['amortization_months_left']) {
									$aAsset['amortization_months_left'] -= floor($nMonthsToToday);
								}				
							}
									
							$aAsset['status'] = 'entered';
							$aAsset['storage_type'] = 'storagehouse';
							$aAsset['id_storage'] = $aElement['id_dest'];
							if($aAsset['enter_date'] == '0000-00-00 00:00:00')$aAsset['enter_date'] = time();
							
							$oDBAssets->update($aAsset);
						}
					}
					
				} elseif ($sPPPType == 'attach') {				// ВЪВЕЖДАНЕ

					foreach ($aPPPElements as $aElement) {
						
						global $aAssetsIDs;
						$aAssetsIDs = array();
						$oDBAssets->getSubAssetsIDsRecursive($aElement['id_asset']);
						
						foreach ($aAssetsIDs as $nIDAsset) {
									
							$aAsset = array();
							$aAsset = $oDBAssets->getRecord($nIDAsset);

							if($aAsset['status'] != 'attached') {
								$aAsset['attach_date'] = time();
							}
							
							if($nIDAsset == $aElement['id_asset']) {

								$aAsset['status'] = 'attached';
								$aAsset['storage_type'] = $aElement['dest_type'];
								$aAsset['id_storage'] = $aElement['id_dest'];
								if($aAsset['enter_date'] == '0000-00-00 00:00:00')$aAsset['enter_date'] = time();
							}
							
							$oDBAssets->update($aAsset);
						}
					}
					
					
				} elseif ($sPPPType == 'waste') {				// БРАКУВАНЕ
					
					
					foreach ($aPPPElements as $aElement) {
						
						global $aAssetsIDs;
						$aAssetsIDs = array();
						$oDBAssets->getSubAssetsIDsRecursive($aElement['id_asset']);

						foreach ($aAssetsIDs as $nIDAsset) {
						
							$aAsset = array();
							$aAsset['id'] = $nIDAsset;
							$aAsset['status'] = 'wasted';
							$aAsset['storage_type'] = '';
							$aAsset['id_storage'] = '0';
							$aAsset['client_name'] = '';
							$aAsset['end_date'] = time();
							
							$aSavedAsset = $oDBAssets->getRecord( $nIDAsset );
							
							if( !empty( $aSavedAsset ) )
							{
								$nAttachDate = strtotime( $aSavedAsset['attach_date'] );
								$nMonthsToToday = ( time() - $nAttachDate ) / 2592000;
								
								if( floor( $nMonthsToToday ) < $aSavedAsset['amortization_months_left'] )
								{
									$aAsset['amortization_months_left'] = $aSavedAsset['amortization_months_left'] - floor( $nMonthsToToday );
								}
							}
							
							$oDBAssets->update( $aAsset );
						}
						
						if(!empty($aElement['waste_person'])) {
							$oDBPersonnel = new DBPersonnel();
							$oDBSalary = new DBSalary();
							
							$aWastePerson = $oDBPersonnel->getRecord($aElement['waste_person']);
							$nPrice = $oDBAssets->getPrice($aElement['id_asset']);
							
							$aData = array();
							$aData['id_person'] = $aElement['waste_person'];
							$aData['id_office'] = $aWastePerson['id_office'];
							$aData['month'] = date('Y'.'m');
							$aData['code'] = '-БРАК';
							$aData['is_earning'] = '0';
							$aData['sum'] = $nPrice;
							$aData['description'] = '['.$aElement['asset_name'].'] '.$aElement['waste_note'];
							$aData['count'] = '1';
							$aData['total_sum'] = $nPrice;
							$aData['created_user'] = $_SESSION['userdata']['id_person'];
							$aData['created_time'] = time();
							
							$oDBSalary->update($aData);
							
						}
					}
				}
				
				$aPPP['confirm_user'] = $_SESSION['userdata']['id_person'];
				$oDBAssetsPPPs->update($aPPP);
				
				
			}
			$oResponse->printResponse();
		}
	}

?>
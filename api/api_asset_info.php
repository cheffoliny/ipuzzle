<?php
	class APIAssetInfo
	{
		public function setAssetInfo(DBResponse $oResponse)
		{
			$oAsset = new DBAssets();
			$oNomenclatures = new DBAssetsNomenclatures();
			$aGroups = array();

			$aNomenclatures = array();
			$this->getGroups($aGroups);
			
			$aData=array();

			$aInfo=array();
			//$nID = 1;//fictivno za proba
			$nID = Params::get('nID',0);
			$nPriceLeft= $oAsset->getSinglePrice($nID);

			//id na nomenclatura za <iframe src>
			$nIDNomenclature = Params::get('id_nomenclature',0);
			
				$oDBAmortizationChanges = new DBAmortizationChanges();
 				$aAmort = $oDBAmortizationChanges->getOneByID($nID);
				
 				$sTitle = "Променил ".$aAmort['updated_name']."\nот ".$aAmort['old_value']." мес. на ".$aAmort['new_value']." мес.\n"."на дата ".$aAmort['updated_time'];
 				$oResponse->setFormElement("form1","tips",array('title' => "{$sTitle}"),'...');
 				
			if($nID){
				
				$oAsset->getAssetInfo($nID,$aInfo);
				$sMOL =$aInfo[0]['storage_type']=='asset'? $this->getMOLForAsset($aInfo[0]['id'],$oAsset):$aInfo[0]['mol'];
				
				$aInfo[0]['rest_price'] = round($nPriceLeft,2).' лв.';
				$aNomenclatures = $oNomenclatures->getNomenclaturesByGroup($aInfo[0]["id_group"]);
				
				//grupi
				$oResponse->setFormElement("form1","id_group");
				foreach ($aGroups as $k=>$v)
				{
					if($k==$aInfo[0]["id_group"]){
						$oResponse->setFormElementChild('form1','id_group',array("value"=>$k,"selected"=>"selected"),$v['group']);
					}
					else $oResponse->setFormElementChild("form1","id_group",array("value"=>$k),$v['group']);
				}
				
				//nomenclaturi
				$oResponse->setFormElement("form1","id_nomenclature");
				$oResponse->setFormElementChild("form1",'id_nomenclature',array(),'---Изберете---');
				foreach($aNomenclatures as $k=>$v)
				{
					if($k == $aInfo[0]['id_nomenclature']){
						$oResponse->setFormElementChild("form1","id_nomenclature",array("value"=>$k,"selected"=>"selected"),$v);
					}
					else $oResponse->setFormElementChild("form1","id_nomenclature",array("value"=>$k),$v)	;
				}
				
				//status
				
				foreach ($aInfo[0] as $k=>$v) {
					
					if($k == 'invoice_num' && $v==0){
						$oResponse->setFormElement('form1',$k,array(),null);
					} else {
						$oResponse->setFormElement('form1',$k,array('value'=>$v),$v);
					}		
				}
			
				
				//mol 
				$oResponse->setFormElement("form1","mol",array('value'=>$sMOL),null);
				$this->setAttributesFieldByNomenc($oResponse,$aInfo[0]['id'],$aInfo[0]['id_nomenclature']);	
			
			}
			else {
				$nIDNomenclature=Params::get('id_nomenclature',0);
				//групи
				$oResponse->setFormElement("form1","id_group");
				$oResponse->setFormElementChild("form1","id_group",array("value"=>0),"---Изберете---");
				foreach ($aGroups as $k=>$v)
				{
					 $oResponse->setFormElementChild("form1","id_group",array("value"=>$k),$v['group']);
				}
				
				//номенклатури
				$oResponse->setFormElement("form1","id_nomenclature");
				$oResponse->setFormElementChild("form1","id_nomenclature",array(),"---Първо изберете група--- ");
				
				if($nIDNomenclature)
					$this->setAttributesFieldByNomenc($oResponse,0,$nIDNomenclature);	
			}
			

			
			$oResponse->printResponse();
		}
		
		public function setNomenclatureByGroup(DBResponse $oResponse)
		{
			$oNomenclatures = new DBAssetsNomenclatures();
			$nID=Params::get("id_group",0);
			$aNomenclatures = $oNomenclatures->getNomenclaturesByGroup($nID);
		
			$oResponse->setFormElement("form1","id_nomenclature");
			$oResponse->setFormElementChild("form1","id_nomenclature",array(),"---Изберете---");
			foreach($aNomenclatures as $k=>$v)
				{
					 $oResponse->setFormElementChild("form1","id_nomenclature",array("value"=>$k),$v)	;
				}
			$this->setAttributesFieldByNomenc($oResponse,'erase',0,0);
			$oResponse->printResponse();
		}
		
		public function getGroups(&$aData)
		{
			$sKeys   = "";
			$sValues = "";
			$this->showGroups(0,$sKeys,$sValues);
			$aKeys = explode("%",$sKeys);
			array_pop($aKeys);
			
			
			$aValues = explode("%",$sValues);
			array_pop($aValues);
			
		
			for($i=0; $i < count($aKeys);$i++)
			{
				$aData[$aKeys[$i]]['group']   = $aValues[$i];
			}
		}
		
		public function showGroups( $nIDGroup, &$sKeys, &$sValues)
			{
				$oDBAssetsGroup = new DBAssetsGroups();
				$aGroups = $oDBAssetsGroup -> getChilds($nIDGroup);	
				
				global $nBr;
				global $space;
				$space .= "    ";
					
				foreach ($aGroups as $key => $value ) {
					$sKeys .= $key."%";
					$sValues.= $space.$value."%";
				
					
				$this->showGroups( $key, $sKeys,$sValues);
				}
				
				$space = substr($space,4);
			}

		public function updateAssetInfo(DBResponse $oResponse)
		{
			$aParams = Params::getAll();
			
			$aStatuses = array("attached"=>"Въведен", "wasted"=>"Бракуван","entered"=>"Придобит");
						
			$aData['id']   = $aParams['id'];
			$aData['name'] = $aParams['name'];
			$aData['id_group'] = $aParams['id_group'];
			$aData['id_nomenclature'] = $aParams['id_nomenclature'];
			$aData['amortization_price_left'] = $aParams['rest_price'];
						
			$aData['amortization_months'] = intval($aParams['amort_period']);
			
			if(!$aData['id']){
				$aData['amortization_months_left'] = intval($aParams['amort_period']);
			} else {
					$oAsset 	= new DBAssets();
					$oAsset->getAssetInfo($aData['id'],$aInfo);
					if ( intval($aInfo[0]['amort_period']) != intval($aParams['amort_period']) )
					{
						$aData['amortization_months'] = intval($aParams['amort_period']);
						$aData['amortization_months_left'] = intval($aParams['amort_period']) - ( intval($aInfo[0]['amort_period']) - $aInfo[0]['amortization_months_left'] );
					}
			}
		
			$aData['id_mol'] = $aParams['mol'];
			$aData['price'] = floatval($aParams['aquire_price']);
			$aData['invoice'] = $aParams['invoice_num'];
			
			$aData['invoice_date']=jsDateToTimestamp($aParams['invoice_date']);
			
			if(empty($aParams['id']))
				$aData['storage_type'] = 'client';
			
			if(empty($aData['name'])){
				throw new Exception("Въведете име на актива");
			}
			if(empty($aData['id_nomenclature'])){
				throw new Exception('Изберете номенклатура за актива');
			}
		
			if(empty($aData['amortization_months'])){
				throw new Exception("Въведете амортизационен период на актива");
			}

			if(empty($aData['price'])){
				throw new Exception("Въведете цена на актива");
			}

			$oDB = new DBAssets();
			
			$oDB->update($aData);
			$this->updateAttributes($aData['id']);
			
			$oResponse->setFormElement("form1","insertID",array(),$aData['id']);
			$oResponse->setFormElement('form1','insertName',array(),$aData['name']);

			$oResponse->printResponse();
		}
		
		public function setAttributesFieldByNomenc(DBResponse $oResponse,$nID,$nNomenclatureID)
		{
			$aParams= Params::getAll();
			$oANomenclatures= new DBAssetsNomenclaturesAttributes();
			$oAssAttributes = new DBAssetsAttributes();
			$oAssetsNomenclatures = new DBAssetsNomenclatures();
			
			if($nID=="erase")
			{
				$sAttributes = '';
				$oResponse->setFormElement('form1','me',array('innerHTML'=>$sAttributes),null);
				return ;
			}

			


			$nID = isset($nID)? $nID: $aParams['nID'];
			if(empty($nID)) $nID = 0;
			$aAttributes= array();
			$nNomenclatureID = isset($nNomenclatureID) ? $nNomenclatureID : $aParams['id_nomenclature'];
			if(empty($nNomenclatureID)) $nNomenclatureID=0;
			
			$aAttributesIDs  = $oANomenclatures->getAttrIDsByNomId($nNomenclatureID);
			if(!empty($aAttributesIDs)){
				$sAttributesIDs = implode(',',$aAttributesIDs);
				$aAttributes = $oANomenclatures->getAtrInfoByIDs($sAttributesIDs);
			}
			$aAttributesPerId = $oAssAttributes->getAttributesByIDAsset($nID);
			
			$aData = array();
			$aData = $oAssetsNomenclatures->getAssetsNomenclatures($nNomenclatureID);
			$oResponse->setFormElement('form1', 'name', NULL, $aData['name']);
			
			
			foreach ($aAttributesPerId as $key => $value) {
				$aAttributes[$value['id_attribute']]['value'] = $value['value'];
			}
			
			if(!empty($aAttributes)){
				
				$sAttributes='<table class="input">';
				
				foreach ($aAttributes as $k => $v )
				{	
					$i=0;
					if($v['type']=='number' || $v['type']=='text')
					{
						if(!($i%2))
						{
							$sAttributes.= '<tr class="odd"><td style="width:120px;text-align:right;">'.$v['name'].'</td><td><input type="text" name="a_'.$v['id_attribute'].'" id="'.$v['id_attribute'].'" value="'.$v['value'].'" class="input200"/>&nbsp;'.$v['code'].'</td></tr>';
						}
						else
						{
							$sAttributes.='<tr class="even"><td style="width:120px;text-align:right;">'.$v['name'].'</td><td><input type="text" name="a_'.$aAttributes[$i]['id_attribute'].'" id="'.$v['id_attribute'].'" value="'.$v['value'].'" class="input200"/>&nbsp;'.$v['code'].'</td></tr>';
						}
						$i++;
					}
					if($v['type']=='list')
					{
						$aValues = explode(",",$v['type_values']);
						if(!($i%2))
						{
							$sAttributes.='<tr class="odd"><td style="width:120px;text-align:right;">'.$v['name'].'</td><td align="left"><select id="'.$v["id_attribute"].'" name="a_'.$v['id_attribute'].'" class="attributes">';
						}
						else 
						{
							$sAttributes.='<tr class="even"><td style="width:120px;text-align:right;">'.$v['name'].'</td><td align="left"><select id="'.$v["id_attribute"].'" name="a_'.$v['id_attribute'].'" class="attributes">';
						}
						$sAttributes.='<option value="0">--Изберете--</option>';
						$sValue = $v['value'];
						foreach ($aValues as $k=>$v)
						{
							if($v == $sValue)
								$sAttributes.='<option value="'.$v.'" selected = "selected">'.$v.'</option>';
							else 
								$sAttributes.='<option value="'.$v.'">'.$v.'</option>';
						}
						
						
						$sAttributes.='</select></td></tr>';
						$i++;	
					}
					
				}
											
				$sAttributes.='</table>';
				
			} else {
				$sAttributes=' ';
			}
			
			$oResponse->setFormElement('form1','me',array('innerHTML'=>$sAttributes),null);
						
			if($aParams['api_action'] != 'setAssetInfo')$oResponse->printResponse();	
		}
		public function updateAttributes($nID)
		{
			$aParams = Params::getAll();
			$aAttributes=array();
			$oANattributes = new DBAssetsNomenclaturesAttributes();
			$aAttributesIDs = array();
			$i=0;
			foreach ($aParams as $k => $v)
			{
				$aCheck = str_split($k,1);
				
				if($aCheck[0]=='a' && $aCheck[1]=='_')
				{
					//throw new Exception('vliza');
					$aAttributes[$i]['id_asset']=empty($aParams['nID'])? $nID:$aParams['nID'];
					$aAttributes[$i]['id_attribute'] = substr($k,2);
					$aAttributes[$i]['value'] = $v;
					$aAttributesIDs[$i]= substr($k,2);
					
					$i++;
				}	
			}
			
			if(empty($aAttributesIDs)) return ;
			$sAttributesIDs = implode(',',$aAttributesIDs);
			$aRefAttributes=$oANattributes->getAtrInfoByIDsArray($sAttributesIDs);

			if(!empty($aAttributes) ){
			
				for($i=0;$i<count($aAttributes);$i++)
				{
					
					if($aRefAttributes[$i]['is_require'] && empty($aAttributes[$i]['value']))
					{
						throw new Exception("Атрибутът ".$aRefAttributes[$i]['name']." е задължителен. Моля, въведете стойност! ");
					}
					if($aRefAttributes[$i]['type']=="number" && !empty($aAttributes[$i]['value']))
					{
						if(!is_numeric($aAttributes[$i]['value']))
							throw new Exception('Моля въведете числова стойност за атрибута'.$aRefAttributes[$i]['name']);
						if(!$this->validateDiapazon($aRefAttributes[$i]['type_values'],intval($aAttributes[$i]['value'])))
						{
							$aValues = explode(',',$aRefAttributes[$i]['type_values']);
							throw new Exception('Стоиността на атрибута '.$aRefAttributes[$i]['name'].' не е в диапазон. Въведете коректна стойност от '.$aValues[0].' до '.$aValues[1].' !');
						}
					}
				}

				$oAssetAttributes = new DBAssetsAttributes();
				
				$oAssetAttributes->updateAttributes($aAttributes);
					
			}
		}
		
		public function validateDiapazon($sDiapazon,$nValue)
		{
			$aDiapazon = explode(",",$sDiapazon);
			$check =0;
			
			if($nValue <= intval($aDiapazon[1]) && $nValue >= intval($aDiapazon[0])){
			
			$check=1;
			}
			return $check;
			
		}
		
		
		public  function getMOLForAsset($nID,DBAssets $oAsset)
		{
			$aData = array();
			$oAsset->getMasterAsset($nID,$aData);
			return $aData;
		}
		
	}
?>
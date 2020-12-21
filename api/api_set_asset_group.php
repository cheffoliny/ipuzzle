<?php

	class APISetAssetGroup{
		
			public function getID()
			{
				$nID = Params::get("id",0);
				if($nID) return $nID;
				return 0;
			}
			public function result(DBResponse $oResponse)
			{
				$sKeys="";
				$sValues="";
				$sPaddings="";
				$nID = $this->getID();
				
				
				$this->showGroups($oResponse,0, $sKeys, $sValues,$sPaddings);
				$oDBAssetsGroup = new DBAssetsGroups();
				$sParent = $oDBAssetsGroup ->getParent($nID);
				
				$aKeys = explode("%",$sKeys);
				array_pop($aKeys);
				$aGroups = explode('%',$sValues);
				$aPaddings = explode("%",$sPaddings);
				array_pop($aPaddings);
				
				APILog::Log(0,$aPaddings);
				$aData=array();
				
				for($i = 0;$i < count($aKeys); $i++)
				{
					$aData[$aKeys[$i]]['group']   = $aGroups[$i+1];
					$aData[$aKeys[$i]]['tr_id']   = $i+1; 
					$aData[$aKeys[$i]]['padding'] = $aPaddings[$i]; 
				}
				
				
				$sName="";
				foreach ($aData as $k=>$v)
				{
					if($k == $nID)$sName=trim($v['group']);
				}
				
				$oResponse->setFormElement("form1","name",null,$sName);
				$oResponse->setFormElement("form1","parent_id");
				$oResponse->setFormElementChild("form1","parent_id",array("value"=>"0"),"--Изберете--");
				foreach($aData as $k=>$v)
				{	
					if(trim($v['group'] )== $sParent){
						$oResponse->setFormElementChild("form1","parent_id",array("selected"=>"selected","value"=>$k),$v['group']);
						$oResponse->setFormElement("form1","offset");
						$oResponse->setFormElementAttribute("form1","offset","value",$v['padding']);
					}
					else $oResponse->setFormElementChild("form1","parent_id",array("value"=>$k),$v['group']);
				}

				$oResponse->printResponse();
			}
		
		public function showGroups(DBResponse $oResponse, $nIDGroup, &$sKeys, &$sValues,&$sPaddings)
			{
				$oDBAssetsGroup = new DBAssetsGroups();
				$aGroups = $oDBAssetsGroup -> getChilds($nIDGroup);
				
				
				
				global $nBr;
				global $space;
				$space .= "    ";
				$nBr=$nBr+1;
				
				
				foreach ($aGroups as $key => $value ) {
					$sKeys .= $key."%";
					$sValues.= "%".$space.$value;
					$sPaddings.=$nBr."%";
					
				$this->showGroups($oResponse,$key, $sKeys,$sValues,$sPaddings);
				}
				
				$space = substr($space,4);
				$nBr=$nBr-1;
			}
			
		public function update()
		{
			$aParams = Params::getAll();
			$oDB =new DBAssetsGroups();
			$oDB->update($aParams);
		}
	}

?>
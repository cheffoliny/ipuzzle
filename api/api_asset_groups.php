<?php
		
		class ApiAssetGroups{
			
			public function result( DBResponse $oResponse)
			{	
				$sKeys="";
				$sValues="";
				$sPaddings="";
				
				
				$this->showGroups($oResponse,0, $sKeys, $sValues, $sPaddings);
				
				
				$aKeys = explode("%",$sKeys);
				array_pop($aKeys);
				$aGroups = explode('%',$sValues);
				$aPaddings= explode('%',$sPaddings);
				
				
				$aData=array();
				
				for($i = 0;$i < count($aKeys); $i++)
				{
					$aData[$aKeys[$i]]['group']   = $aGroups[$i+1];
					$aData[$aKeys[$i]]['padding'] = $aPaddings[$i]*10; 
					$aData[$aKeys[$i]]['tr_id']   = $i+1; 
					$aData[$aKeys[$i]]['id']      = $aKeys[$i]; 
				}
				
				
				APILog::Log(0,count($aData));
				$oResponse->setField("group", "Група");
				$oResponse->setField("delete_group","","","images/cancel.gif","deleteGroup","");
				//$oResponse->setPaging($nRowLimit,count($aData),1);
				foreach ($aData as $k => $v)
				{	
					if($v['padding'] == 10)		$oResponse->setDataAttributes($k,'group',array("style"=>"padding-left:{$v['padding']}px;font-weight:bold"));		
					else $oResponse->setDataAttributes($k,'group',array("style"=>"padding-left:{$v['padding']}px;"));
				}
				$oResponse->setFieldLink("group","modifyGroup");
				$oResponse->setData($aData);

				$oResponse->printResponse("Активи - Групи","asset_groups");
			}
			
			public function showGroups(DBResponse $oResponse, $nIDGroup, &$sKeys, &$sValues, &$sPaddings)
			{
				$oDBAssetsGroup = new DBAssetsGroups();
				$aGroups = $oDBAssetsGroup -> getChilds($nIDGroup);
				global $nBr;
				global $space;
				$space .= "    ";
				$nBr=$nBr+1;
				
				
				foreach ($aGroups as $key => $value ) {
					$sKeys .= $key."%";
					$sPaddings.=$nBr."%";
					$sValues.= "%".$space.$value;
					
				$this->showGroups($oResponse,$key, $sKeys,$sValues,$sPaddings);
				}
				
				$space = substr($space,4);
				$nBr=$nBr-1;
			}
			public function showChilds(DBResponse $oResponse, $nIDGroup, &$sChilds)
			{
				$oDBAssetsGroup = new DBAssetsGroups();
				$aGroups = $oDBAssetsGroup -> getChilds($nIDGroup);
				
				foreach ($aGroups as $key => $value ) {
					$sChilds .= $key."%";	
					$this->showChilds($oResponse,$key, $sChilds);
				}		
			}
			
			public function delete( DBResponse $oResponse )
			{
				$nID = Params::get("nID",0);
				$oGroups = new DBAssetsGroups();
				$sChilds="";
				$this->showChilds($oResponse,$nID, $sChilds);

				$aChilds = explode("%",$sChilds);
				array_pop($aChilds);
				
				for($i = 0;$i < count($aChilds);$i++)
				{
					$oGroups->delete($aChilds[$i]);
				}
				
				$oGroups->delete($nID);
				$oResponse->printResponse();
			}
		}

?>
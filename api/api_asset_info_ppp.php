<?php
 	class APIAssetInfoPPP
 	{
 		
 		public function result(DBResponse $oResponse)
 		{
 			
 			$nID = intval(Params::get("nID",0));
 			$oDB = new DBAssetsPPPElements();
 			$oDB->getPPPsByAsset($nID,$oResponse);
 			$oResponse->printResponse();
 		}
 	}
?>
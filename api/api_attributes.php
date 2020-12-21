<?php
	
	class ApiAttributes
	{
		
		public function result( DBResponse $oResponse)
		{	
			
			$oAttributes = new DBAttributes();
			$oAttributes->showAllAttributes($oResponse);
			
			$oResponse->printResponse("Активи-АТРИБУТИ","atributes");
		}
		public function delete(DBResponse $oResponse)
		{
			$nID = Params::get("nID","alabala");
			APILog::Log(0,$nID);
			$oAttributes = new DBAttributes();
			
			$oDBAssetsNomenclaturesAttributes = new DBAssetsNomenclaturesAttributes();
			
			$nCount = $oDBAssetsNomenclaturesAttributes->getCountAttributesByID($nID);
			if (empty($nCount)) {
				$oAttributes->delete($nID);
			} else if ($nCount==1) throw new Exception("Не може да премахнете този атрибут!\nТой се използва в {$nCount} номенклатура!");
				else throw new Exception("Не може да премахнете този атрибут!\nТой се използва в {$nCount} номенклатури!");
			//$oResponse->printResponse("Активи-АТРИБУТИ","atributes");
		}
	}
	
?>
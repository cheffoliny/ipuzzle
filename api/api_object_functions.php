<?php

	class ApiObjectFunctions
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oObjectFunctions = new DBObjectFunctions();
			$oObjectFunctions->getReport( $aParams, $oResponse );
			
			$oResponse->printResponse( "Назначения на обекти", "object_functions" );
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			$oObjectFunctions = new DBObjectFunctions();
			$oDBObjects		  = new DBObjects();
			
			$nCount = $oDBObjects->getCountObjectsByFunctionID($nID);
			
			if (empty($nCount)) {
				$oObjectFunctions->delete( $nID );
			} else if ($nCount==1) throw new Exception("Не може да премахнете този тип назначения на обекти! \nТипът се използва за {$nCount} обект!");
				else throw new Exception("Не може да премахнете този тип назначения на обекти! \nТипът се използва за {$nCount} обекта!");
			
			$oResponse->printResponse();
		}
	}

?>
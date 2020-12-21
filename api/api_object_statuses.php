<?php

	class ApiObjectStatuses
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oStatuses = new DBStatuses();
			$oStatuses->getReport( $aParams, $oResponse );
			
			$oResponse->printResponse( "Статуси на обекти", "object_statuses" );
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			$oStatuses	= new DBStatuses();
			$oDBObjects	= new DBObjects();
			
			$nCount = $oDBObjects->getCountObjectsByStatusID($nID);
			
			if (empty($nCount)) {
				$oStatuses->delete( $nID );
			} else if ($nCount==1) throw new Exception("Не може да премахнете този тип статуси на обекти! \nТипът се използва за {$nCount} обект!");
				else throw new Exception("Не може да премахнете този тип статуси на обекти! \nТипът се използва за {$nCount} обекта!");
				
			$oResponse->printResponse();
		}
	}

?>
<?php

	class ApiObjectTypes
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oObjectTypes = new DBObjectTypes();
			$oObjectTypes->getReport( $aParams, $oResponse );
			
			$oResponse->printResponse( "Типове на обекти", "object_types" );
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( 'nID', 0 );
			
			$oObjectTypes	= new DBObjectTypes();
			$oObjects		= new DBObjects();
			
			$nCount = $oObjects->getCountObjectsByTypeID($nID);
			
			if (empty($nCount)) {
				$oObjectTypes->delete( $nID );
			} else if ($nCount==1) throw new Exception("Не може да премахнете този тип обекти! \nТипът се използва за {$nCount} обект!");
				else throw new Exception("Не може да премахнете този тип обекти! \nТипът се използва за {$nCount} обекта!");
			
			$oResponse->printResponse();
		}
	}

?>
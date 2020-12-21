<?php

	class ApiWorkingCardTechs
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			$oCurrentCard = new DBWorkCard();
			
			$oCurrentCard->getTechs( $aParams, $oResponse );
			
			$oResponse->printResponse();
		}
	}
?>
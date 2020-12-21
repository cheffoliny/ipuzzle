<?php

	class ApiSummaryObjectFinancesMain
	{
		
		public function loadFilter( DBResponse $oResponse )
		{
			//External Classes
			$oDBFirms = new DBFirms();
			//End External Classes
			
			//Get Firms
			$aFirms = $oDBFirms->getFirms4();
			
			$oResponse->setFormElement( "form1", "nIDFirm" );
			$oResponse->setFormElementChild( "form1", "nIDFirm", array( "value" => 0 ), "-- Изберете --" );
			
			foreach( $aFirms as $nKey => $sName )
			{
				$oResponse->setFormElementChild( "form1", "nIDFirm", array( "value" => $nKey ), $sName );
			}
			//End Get Firms
			
			$oResponse->printResponse();
		}
		
	}

?>
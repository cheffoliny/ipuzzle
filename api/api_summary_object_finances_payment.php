<?php

	class ApiSummaryObjectFinancesPayment
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oObjectServices = new DBObjectServices();
			$oObjectServices->getSummaryObjectPaymentReport( $oResponse, $aParams );
			
			$oResponse->printResponse( "Неплатили Обекти", "objects_unpaid" );
		}
	}

?>
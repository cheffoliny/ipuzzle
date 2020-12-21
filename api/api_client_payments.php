<?php

	class ApiClientPayments
	{
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			if( isset( $aParams['nID'] ) && !empty( $aParams['nID'] ) )
			{
				$oClients = new DBClients();
				
				$oClients->getPaymentsReport( $oResponse, $aParams );
			}
			
			$oResponse->printResponse( "Картон на клиента - Плащания", "clients_payments" );
		}
		
	}

?>
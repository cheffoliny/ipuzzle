<?php

	class ApiSetupBankAccounts
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oBankAccounts = new DBBankAccounts();
			$oBankAccounts->getReport( $aParams, $oResponse );
			
			$oResponse->printResponse( "Банкови сметки", "bank_accounts" );
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			$oBankAccounts = new DBBankAccounts();
			$oBankAccounts->delete( $nID );
			
			$oResponse->printResponse();
		}
	}
?>
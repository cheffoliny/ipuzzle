<?php

	class ApiSetSetupQuittanceList
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBPersonLeaves = new DBPersonLeaves();
			$oDBPersonLeaves->getReportQuittance( $aParams, $oResponse );
			
			$oResponse->printResponse( "Обезщетения", "quittances" );
		}
		
		public function delete( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			$nID = ( int ) $aParams['id'];
			
			$oDBSalary 			= new DBSalary();
			$oDBPersonLeaves 	= new DBPersonLeaves();
			
			//Delete Salary Rows
			$nResult = $oDBSalary->deleteSalaryRowsByApplication( $nID );
			
			if( $nResult != DBAPI_ERR_SUCCESS )
			{
				$oResponse->setError( $nResult, "Проблем при коригиране на работна заплата!", __FILE__, __LINE__ );
				print( $oResponse->toXML() );
			}
			//End Delete Salary Rows
			
			$oDBPersonLeaves->delete( $nID );
			
			$oResponse->printResponse();
		}
	}

?>
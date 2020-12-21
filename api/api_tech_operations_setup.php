<?php

	class ApiTechOperationsSetup
	{
		public function result( DBResponse $oResponse )
		{
			$oTechOperations = new DBTechOperations();
			
			$oTechOperations->getReport2( $oResponse );
			
			$oResponse->printResponse( "Операции", "tech_operations" );
		}
		
		function delete()
		{
			$nID = Params::get( 'nID' );
			$oTechOperations = new DBTechOperations();
			$oOperationNomenclatures = new DBTechOperationsNomenclatures();
			
			$oTechOperations->delete( $nID );
			$aOperationNomenclatures = $oOperationNomenclatures->select( "SELECT * FROM tech_operations_nomenclatures WHERE to_arc = 0 AND id_operation = {$nID}" );
			if( !empty( $aOperationNomenclatures ) )
			{
				foreach( $aOperationNomenclatures as $aOperationNomenclature )
				{
					$oOperationNomenclatures->delete( $aOperationNomenclature['id'] );
				}
			}
		}
	}

?>
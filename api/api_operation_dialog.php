<?php

	class ApiOperationDialog {
	
		public function init( DBResponse $oResponse ) 
		{
			
			$id = Params::get( "nID", 0 );
			$oDBOperations = new DBActivitiesOperations();
 				
			if ( $id > 0 ) {
				$aData = array();
				$aData = $oDBOperations->getRecord( $id );

				$oResponse->setFormElement( 'form1', 'nID', array(), $aData['id'] );
				$oResponse->setFormElement( 'form1', 'sName', array(), $aData['name'] );
				$oResponse->setFormElement( 'form1', 'sDesc', array(), $aData['description'] );

			}
			
			$oResponse->printResponse();	
			
		}
		
		public function save( DBResponse $oResponse )
		 {
		 	$aParams = Params::getAll();
			$oDBOperations = new DBActivitiesOperations();
	
		
			$aActivity = array();

			$aActivity['id']				= $aParams['nID'];
			$aActivity['item_type']			= 'operation';
			$aActivity['name'] 			= $aParams['sName'];
			$aActivity['description'] 		= $aParams['sDesc'];
	 			
			
			if ( $oDBOperations->checkName( $aParams['sName'], $aParams['nID'] ) ) {
				$oResponse->setFormElement( 'form1', 'sName', array( 'focused'=>'true', 'selected'=>'true' ) );
				throw new Exception( "Операцията вече съществува!", DBAPI_ERR_INVALID_PARAM );
			}	
			
			
			$nResult = $oDBOperations->update( $aActivity );
			
			if ( empty( $aParams['nID'] ) && !empty( $aActivity['id'] ) ) {
				$oResponse->setFormElement( 'form1', 'nID', array(), $aActivity['id'] );
			}
	
			$oResponse->printResponse();
		
		}
	
		

			
	}	

?>
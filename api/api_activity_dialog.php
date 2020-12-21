<?php

	class ApiActivityDialog {
	
		public function init( DBResponse $oResponse ) 
		{
			
			$id = Params::get( "nID", 0 );
			$oDBActivities = new DBActivitiesOperations();
 
			if ( $id > 0 ) {
				$aData = array();
				$aData = $oDBActivities->getRecord( $id );

				$oResponse->setFormElement( 'form1', 'nID', array(), $aData['id'] );
				$oResponse->setFormElement( 'form1', 'sName', array(), $aData['name'] );
				$oResponse->setFormElement( 'form1', 'sDesc', array(), $aData['description'] );

			}
			
			$oResponse->printResponse();	
			
		}
		
		public function save( DBResponse $oResponse )
		 {
		 	$aParams = Params::getAll();
			$oDBActivities = new DBActivitiesOperations();
	
		
			$aActivity = array();

			$aActivity['id']				= $aParams['nID'];
			$aActivity['item_type']			= "activity";
			$aActivity['name'] 			= $aParams['sName'];
			$aActivity['description'] 		= $aParams['sDesc'];
	 			
			
			if ( $oDBActivities->checkName( $aParams['sName'], $aParams['nID'] ) ) {
				$oResponse->setFormElement( 'form1', 'sName', array( 'focused'=>'true', 'selected'=>'true' ) );
				throw new Exception( "Дейността вече съществува!", DBAPI_ERR_INVALID_PARAM );
			}	
			
			
			$nResult = $oDBActivities->update( $aActivity );
			
			if ( empty( $aParams['nID'] ) && !empty( $aActivity['id'] ) ) {
				$oResponse->setFormElement( 'form1', 'nID', array(), $aActivity['id'] );
			}
	
			$oResponse->printResponse();
		
		}
	
		

			
	}	

?>
<?php
	class ApiTechAnalyticsObjects
	{
	 	
		public function result( DBResponse $oResponse )
		{
			$oDBTechAnalytics = new DBTechAnalytics();
				
			$aParams = Params::getAll();
			 
			$oDBTechAnalytics->getUnconfirmedObjects( $oResponse );
 			
			$oResponse->printResponse();	
			
		}
		
		
		
		public function loadFirms( DBResponse $oResponse )
		{

			$oDBTechAnalytics = new DBTechAnalytics();
		 
			
			$aFirms = $oDBTechAnalytics->getFirms();
			
			$oResponse->setFormElement( 'form1', 'nIDFirms' );
				
			foreach( $aFirms as $nkey => $Firm )
			{
				$oResponse->setFormElementChild( "form1", "nIDFirms", array ( "value" => $nkey ), $Firm );					
			}
			
			$oResponse->printResponse();
					
			
		}
		
		public function loadOffices( DBResponse $oResponse )
		{

			$oDBTechAnalytics = new DBTechAnalytics();
				
			$aParams = Params::getAll();	
			
			$aOffices = $oDBTechAnalytics->getOffices();
			
			
			$oDBTechAnalytics->getFims( $oResponse );
			
			$oResponse->setFormElement( 'form1', 'nIDOffices' );
			
			foreach( $aOffices as $nkey => $Office )
			{
				$oResponse->setFormElementChild( "form1", "nIDOffices", array ( "value" => $nkey ), $Office );					
			}		
			
			$oResponse->printResponse();
		}
		
		
		public function loadStatuses( DBResponse $oResponse )
		{
				$oStatuses = new DBStatuses();
				
				$aParams = Params::getAll();
				
				$aStatuses = $oStatuses->getStatuses2();
			
				
				$oResponse->setFormElement( 'form1', 'nStatus' );
				
				$nIDActive = 0;
				
				foreach( $aStatuses as $aStatus )
				{
					if( $aStatus['name'] == 'активен' )$nIDActive = $aStatus['id'];
					
					$oResponse->setFormElementChild( 'form1', 'nStatus', array( 'value' => $aStatus['id'] ), $aStatus['name'] );
				}
				if( $aParams['nStatus'] != 0 )
				{
					$oResponse->setFormElementAttribute( 'form1', 'nStatus', 'value', $aParams['nStatus'] );
				}
				else
				{
					if( !empty( $nIDActive ) )
					{
						$oResponse->setFormElementAttribute( 'form1', 'nStatus', 'value', $nIDActive );
					}
				}
				$oResponse->printResponse();
		}
		 
		
	}
?>
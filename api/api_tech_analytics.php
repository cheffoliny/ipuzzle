<?php
	class ApiTechAnalytics
	{
	 	
		public function result( DBResponse $oResponse )
		{
			$oDBTechAnalytics = new DBTechAnalytics();
				
			$aParams = Params::getAll();
		
			$Res = $oDBTechAnalytics->getReport( $oResponse, $aParams  );

			
			$oResponse->setFormElement( "form2", "nObjectNum", NULL, $Res['num'] );
			$oResponse->setFormElement( "form2", "sObjectName", NULL, $Res['name'] );
			$oResponse->setFormElement( "form2", "Address", NULL, $Res['address'] );
			$oResponse->setFormElement( "form2", "lat", NULL, $Res['lat'] );
			$oResponse->setFormElement( "form2", "lon", NULL, $Res['lon'] );
			
			if( $Res['confirmed']){
				$oResponse->setFormElement( "form2", "confirmed", array( 'checked' => 'checked' ), $Res['confirmed'] );
			}
			
			$oResponse->printResponse();	
			
		}
		

		public function save( DBResponse $oResponse )
		 {
		 	$aParams = Params::getAll();
			
		 	$oDBTechAnalytics = new DBTechAnalytics();
	 
			
			$aObject  = array();
			
			$aObject['id']				= $aParams['nIDObject'];
			$aObject['geo_lat'] 			= $aParams['lat'];
			$aObject['geo_lan'] 			= $aParams['lon'];
			$aObject['confirmed']			= 1;
		

			$nResult = $oDBTechAnalytics->update( $aObject );

		
			$oResponse->printResponse();
		
		}
		
	}
?>
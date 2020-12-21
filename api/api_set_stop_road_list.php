<?php
	class ApiSetStopRoadList {
		
		public function load( DBResponse $oResponse ) {
			$nID = Params::get("nID", 0);
			
			$aRoadList = array();
			$oRoadList = new DBRoadLists();
			
			if( !empty( $nID ) ) {
				$aRoadList = $oRoadList->getRoadList( $nID );
				
				$oResponse->setFormElement('form1', 'startKm', array('value' => $aRoadList['start_km']));	
			}
						
			$oResponse->printResponse();
		}
			
		public function save( DBResponse $oResponse ) {
			$nID			= Params::get('nID', 0);
			$startKm		= Params::get("startKm", 0);
			$endKm			= Params::get("endKm", 0);
			
			if ( empty($endKm) ) {
				throw new Exception("Въведете краен километраж!", DBAPI_ERR_INVALID_PARAM);
			}

			if ( $endKm <= $startKm ) {
				throw new Exception("Въведете коректен краен километраж!", DBAPI_ERR_INVALID_PARAM);
			}
						
			$aRoadList = array();
			$oRoadList = new DBRoadLists();
			
			$aData = array();
			$aData['id']		= $nID;
			$aData['end_km']	= $endKm;
			$aData['end_time'] = time();
			
			$oRoadList->update( $aData );
		}
			
	}
	
?>
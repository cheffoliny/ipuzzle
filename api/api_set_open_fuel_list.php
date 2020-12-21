<?php
	class ApiSetOpenFuelList {
		
		public function save( DBResponse $oResponse ) {
			$nID			= Params::get('nID', 0);
			$nKm			= Params::get("nKm", 0);
			$nFuelLiter		= Params::get("nFuelLiter", 0);
			$nFuelPrice		= Params::get("nFuelPrice", 0);
			$sFuelInvoice	= Params::get("sFuelInvoice", '');

			if ( empty($nFuelLiter) ) {
				throw new Exception("Въведете заредено гориво (л)!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($nKm) ) {
				throw new Exception("Въведете пробег (км)!", DBAPI_ERR_INVALID_PARAM);
			}


			if ( empty($nFuelPrice) ) {
				throw new Exception("Въведете стойност на гориво (лв)!", DBAPI_ERR_INVALID_PARAM);
			}

			if ( empty($sFuelInvoice) ) {
				throw new Exception("Въведете номер на фактура!", DBAPI_ERR_INVALID_PARAM);
			}

			$aData = array();
			$aData['id'] = 0;
			
			$aData['id_road_list']	= $nID;
			$aData['fuel_km']		= $nKm;
			$aData['fuel_in_litre'] = $nFuelLiter;
			$aData['fuel_in_money'] = $nFuelPrice;
			$aData['document_num']	= $sFuelInvoice;
			$aData['fuel_time']	= time();
					
			$aRoadList = array();
			$aFuelList = array();
			$oRoadList = new DBRoadLists();
			$oFuelList = new DBFuelLists();
			
			if( !empty( $nID ) ) {
				$aRoadList = $oRoadList->getRoadList( $nID );
				$aData['id_auto']	= $aRoadList['id_auto'];
				$aData['id_office'] = $aRoadList['id_office'];
				$aData['persons']	= $aRoadList['persons'];
			}
			
			$oFuelList->update( $aData );			
		}
			
	}
	
?>
<?php
	class ApiAssetsTotals {
						
		public function load( DBResponse $oResponse ) {
			$aParams = Params::getAll();
			
			$oFirms = new DBFirms();
			
			$aFirms = $oFirms->getFirms2();
			
			$oResponse->setFormElement( 'form1', 'nIDFirm' );
			$oResponse->setFormElementChild( 'form1', 'nIDFirm', array( "value" => 0 ), "--- Всички ---" );
		
			foreach( $aFirms as $aFirm ) {
				$oResponse->setFormElementChild( 'form1', 'nIDFirm', array( "value" => $aFirm['id'] ), $aFirm['name'] );
			}
			
			if ( isset($aParams['nIDFirm']) ) {
				$oResponse->setFormElementAttribute( 'form1', 'nIDFirm', 'value', $aParams['nIDFirm'] );
			}
									
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse ) {
			$aParams = Params::getAll();
			
			$oAssets = new DBAssets();
			$assets = array();
			
			$assets = $oAssets->getSingleAssets();
			//APILog::Log(0, $assets);
			$total = array();
			
			foreach ( $assets as $key => $val ) {
				if ( $val['storage_type'] == "person" ) {
					$office = $oAssets->getOfficeByPerson( $key );
					$nIDOffice = isset($office[0]['id_office']) ? $office[0]['id_office'] : 0;
					$nPrice = $oAssets->getPrice( $key );
					
					if ( isset($total[$nIDOffice]) ) {
						$total[$nIDOffice] += $nPrice;
					} else {
						$total[$nIDOffice] = $nPrice;
					}
				} elseif ( $val['storage_type'] == "storagehouse" ) {
					$office = $oAssets->getOfficeByStoragehouse( $key );
					$nIDOffice = isset($office[0]['id_office']) ? $office[0]['id_office'] : 0;
					$nPrice = $oAssets->getPrice( $key );

					if ( isset($total[$nIDOffice]) ) {
						$total[$nIDOffice] += $nPrice;
					} else {
						$total[$nIDOffice] = $nPrice;
					}
				}
			}
			
			APILog::Log(0, $total);
			//$oAssets->getReport2( $aParams, $oResponse );
			
			$oResponse->printResponse( "Активи - ОБОБЩЕНА", "assets_totals", false );
		}
	}

?>
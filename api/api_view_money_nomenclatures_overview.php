<?php
	class ApiViewMoneyNomenclaturesOverview {
		public function load( DBResponse $oResponse ) {
			$aParams = Params::getAll();
			
			$oDBFirms = new DBFirms();
			
			//Load Firms
			$aFirms = $oDBFirms->getFirms4();
			
			$oResponse->setFormElement("form1", "nIDFirm", array(), "");
			$oResponse->setFormElementChild("form1", "nIDFirm", array( "value" => '0' ), "--Изберете--");
			
			foreach( $aFirms as $key => $value ) {
				$oResponse->setFormElementChild("form1", "nIDFirm", array( "value" => $key ), $value);
			}
			//End Load Firms
			
			if ( !empty($aParams['nIDFirm']) ) {
				$oResponse->setFormElementAttribute("form1", "nIDFirm", "value", $aParams['nIDFirm']);
				
				//Refresh Offices
				$nFirm = Params::get( 'nIDFirm', 0 );
				
				$oResponse->setFormElement("form1", "nIDOffice", array(), "");
				
				if ( !empty($nFirm) ) {
					$oDBOffices = new DBOffices();
					$aOffices 	= $oDBOffices->getOfficesByIDFirm( $nFirm );
					
					$oResponse->setFormElementChild("form1", "nIDOffice", array( "value" => '0' ), "--Изберете--");
					
					foreach( $aOffices as $key => $value ) {
						$oResponse->setFormElementChild("form1", "nIDOffice", array( "value" => $key ), $value);
					}
				} else {
					$oResponse->setFormElementChild("form1", "nIDOffice", array( "value" => '0' ), "Първо изберете фирма");
				}
				//End Refresh Offices
			}
			
			//Initialize Offices
			if ( !empty($aParams['nIDOffice']) ) {
				$oResponse->setFormElementAttribute("form1", "nIDOffice", "value", $aParams['nIDOffice']);
			} else {
				$oResponse->setFormElement("form1", "nIDOffice", array(), "");
				$oResponse->setFormElementChild("form1", "nIDOffice", array( "value" => '0' ), "Първо изберете фирма");
			}
			//End Initialize Offices
			
			//Default Date and Time
			$from 	= isset($aParams['dFrom']) && !empty($aParams['dFrom']) ? $aParams['dFrom'] : date("d.m.Y");
			$to 	= isset($aParams['dTo']) && !empty($aParams['dTo']) ? $aParams['dTo'] : date("d.m.Y");
			
			$oResponse->setFormElement("form1", "sFromDate", array("value" => $from));
			$oResponse->setFormElement("form1", "sToDate", array("value" => $to));
			//End Default Date and Time
			
			//Set Dates
			$oResponse->setFormElement("form1", "sMonth", array(), "");
			
			for ( $i = -6; $i <= 5; $i++ ) {
				if( $i == 0 ) {
					$oResponse->setFormElementChild("form1", "sMonth", array( "value" => 0 ), "--Изберете--");
				}
				
				$oResponse->setFormElementChild("form1", "sMonth", array( "value" => date( "Y-m", strtotime( "{$i} months" ) ) ), date( "m Y", strtotime( "{$i} months" ) ) );
			}
			
			$oResponse->setFormElementAttribute("form1", "sMonth", "value", 0);
			//End Set Dates
			
			$oResponse->printResponse("Парични Потоци - Обобщена", "view_money_nomenclatures_overview");
		}
		
		public function result( DBResponse $oResponse ) {
			$aParams = Params::getAll();
			$oOrders = new DBOrders();
			
			$oOrders->getReportMoneyNomenclaturesOverview( $oResponse, $aParams );
			
			$oResponse->printResponse("Парични Потоци - Обобщена", "view_money_nomenclatures_overview");
		}
		
		public function loadOffices( DBResponse $oResponse ) {
			$nFirm = Params::get("nIDFirm", 0);
			
			$oResponse->setFormElement("form1", "nIDOffice", array(), '' );
			
			if ( !empty($nFirm) ) {
				$oDBOffices = new DBOffices();
				$aOffices 	= $oDBOffices->getOfficesByIDFirm($nFirm);
				
				$oResponse->setFormElementChild("form1", "nIDOffice", array( "value" => '0' ), "--Изберете--" );
				
				foreach ( $aOffices as $key => $value ) {
					$oResponse->setFormElementChild("form1", "nIDOffice", array( "value" => $key ), $value );
				}
			} else {
				$oResponse->setFormElementChild("form1", "nIDOffice", array( "value" => '0' ), "Първо изберете фирма" );
			}
			
			$oResponse->printResponse();
		}
	}
?>
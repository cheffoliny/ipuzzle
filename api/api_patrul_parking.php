<?php
	class ApiPatrulParking {
		
		public function load( DBResponse $oResponse ) {
			//throw new Exception("awae");
			
			$oDBFirms = new DBFirms();
			$oOffices = new DBOffices();
			$aFirms = $oDBFirms->getFirms4();

			$nIDOffice = $_SESSION['userdata']['id_office'];
			$nIDFirm = $oOffices->getFirmByIDOffice( $nIDOffice );
			$aOffices = $oOffices->getPatrulOfficesByIDFirm( $nIDFirm );
								
			$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>'0')), "--Изберете--");
			
			foreach ($aFirms as $key => $value) {
				if ( $key == $nIDFirm ) {
					$oResponse->setFormElementChild('form1', 'nIDFirm', array("value" => $key, 'selected' => 'selected'), $value);
				} else {
					$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value" => $key)), $value);
				}
			}		

			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Изберете--");
			
			foreach ( $aOffices as $key => $value ) {
				if ( $key == $nIDOffice ) {
					$oResponse->setFormElementChild('form1', 'nIDOffice', array("value" => $key, "selected" => "selected"), $value);
				} else {
					$oResponse->setFormElementChild('form1', 'nIDOffice', array("value" => $key), $value);
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function loadOffices(DBResponse $oResponse) {
			$nFirm 	=	Params::get('nIDFirm');
			
			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			
			if ( !empty($nFirm) ) {
				$oDBOffices = new DBOffices();
				$aOffices = $oDBOffices->getPatrulOfficesByIDFirm($nFirm);
	
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Всички--");
				foreach($aOffices as $key => $value)
				{
					$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key)), $value);
				}
			} else {
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
			}
			
			$oResponse->printResponse();
		}
		function result( DBResponse $oResponse )
		{
			$nIDFirm 	= Params::get('nIDFirm', 0);
			$nIDOffice 	= Params::get('nIDOffice', 0);
			 
			if(empty($nIDFirm))
			{
					throw new Exception("Изберете фирма!", DBAPI_ERR_INVALID_PARAM);
			}	
			
			$oPatrulParking = new DBPatrulParking();
			$oPatrulParking->getReport($nIDOffice,$nIDFirm, $oResponse);
				
			$oResponse->printResponse("Номенклатури - Стоянки", "sod_parking");
		}

		function delete( DBResponse $oResponse )
		{
			$nID = Params::get('nID');
			
			$oPatrulParking = new DBPatrulParking();
			$oPatrulParking->delete( $nID );
			
			$oResponse->printResponse();
		}
	}
?>
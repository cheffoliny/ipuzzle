<?php
	class ApiPatrulsMovement
	 {
		public function load( DBResponse $oResponse ) 
		{
			$nIDCard = Params::get("nIDCard", 0);
			
			$oOffices	= new DBOffices();
			$oDBMovementSchemes = new DBMovementSchemes();
			
			$aSchemes = $oDBMovementSchemes->getSchemes();
			
			$oResponse->setFormElement('form1', 'schemes',	array(), '');
			$oResponse->setFormElementChild('form1', 'schemes',	array('value' => '0'),'--- Изберете ---');
			foreach ( $aSchemes as $key => $value ) {
				if($value['def'] == '1') {
					$oResponse->setFormElementChild('form1','schemes',array_merge(array('value' => $key),array("selected" => "selected")),$value['name']);
				} else {
					$oResponse->setFormElementChild('form1','schemes',array('value' => $key),$value['name']);
				}
			}
			
			$nIDOffice = $_SESSION['userdata']['id_office'];
			$aOffice = array();
			$aOffice = $oOffices->getPatrulOffices();

			$oResponse->setFormElement('form1', 'nRegion', array(), '');
			$oResponse->setFormElementChild('form1', 'nRegion', array('value' => 0), 'Всички');
			foreach ( $aOffice as $key => $val ) {
				if( $nIDOffice == $key ) {
					$oResponse->setFormElementChild('form1', 'nRegion', array_merge(array('value' => $key),array('selected' => 'selected')), $val);
				} else {
					$oResponse->setFormElementChild('form1', 'nRegion', array('value' => $key), $val);
				}
			}			
			
			$oResponse->setFormElement( 'form1', 'date_from', array('value' => date('d.m.Y')) );
			$oResponse->setFormElement( 'form1', 'date_to', array('value' => date('d.m.Y')) );	

			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse )	{
			$nRegion = Params::get("nRegion", 0);								
			$nPatrul = Params::get("nPatrul", 0);	
			$dFrom	 = jsDateToTimestamp( Params::get("date_from", '') );
			$dTo	 = jsDateToTimestamp( Params::get("date_to", '') );
			$nIDScheme = Params::get('schemes','0');
			$sType = Params::get('type','');
			$sAlarmType = Params::get('sAlarmType','visited');
			
			$oWorkCardMovement	= new DBWorkCardMovement();
			
			$aData = array();
			$aData['id_office'] = $nRegion;
			$aData['num_patrul'] = $nPatrul;
			$aData['date_from'] = $dFrom;
			$aData['date_to'] = $dTo;
			$aData['nIDScheme'] = $nIDScheme;	
			$aData['sAlarmType'] = $sAlarmType;
			
			if($sType == 'detailed') {
				$oWorkCardMovement->getReport( $aData, $oResponse );
			} else {
				$oWorkCardMovement->getReportTotal($aData,$oResponse);
			}

			$oResponse->printResponse("Движение","movement");
			
		}
		
		public function deleteFilter() {
			$nID = Params::get('schemes','');
			
			$oDBMovementSchemes = new DBMovementSchemes();
			$oDBMovementSchemes->delete($nID);
		}
	}
?>
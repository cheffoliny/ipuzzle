<?php
	class ApiWorkingCardMovement
	 {
		public function load( DBResponse $oResponse ) 
		{
			$nIDCard = Params::get("nIDCard", 0);
			
			$oWorkingCards		= new DBWorkCardOffices();
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
			
			$aOffice = array();
			$aOffice = $oWorkingCards->getWorkCardOffices( $nIDCard );

			$oResponse->setFormElement('form1', 'nRegion', array(), '');
			$oResponse->setFormElementChild('form1', 'nRegion', array('value' => 0), 'Всички');
			foreach ( $aOffice as $key => $val )
			{
				$oResponse->setFormElementChild('form1', 'nRegion', array('value' => $key), $val);
			}				
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse )	{
			$nIDCard = Params::get("nIDCard", 0);
			$nRegion = Params::get("nRegion", 0);								
			$nPatrul = Params::get("nPatrul", 0);	
			$nIDScheme = Params::get('schemes',0);
			$sType = Params::get('type','');
			
			$oWorkCard = new DBWorkCard();
			$oWorkCardOffices		= new DBWorkCardOffices();
			$oWorkCardMovement	= new DBWorkCardMovement();
									
			$aWorkCard = $oWorkCard->getUnixTimestamp($nIDCard);
			$sOffices = $oWorkCardOffices->getWorkCardOffices2( $nIDCard );
			
			if( empty( $sOffices ) )	{
				throw new Exception("Не сте запаметили никакви региони");
			}
			else	{
				$aData = array();
				$aData['id_office'] = $nRegion;
				$aData['id_offices'] = $sOffices;
				$aData['num_patrul'] = $nPatrul;
				$aData['start_time'] = $aWorkCard['start_time'];
				$aData['end_time'] = $aWorkCard['end_time'];
				$aData['nIDScheme'] = $nIDScheme;		
				
				if($sType == 'detailed') {
					$oWorkCardMovement->getReport( $aData, $oResponse );
				} else {
					$oWorkCardMovement->getReportTotal($aData,$oResponse);
				}
				
				$oResponse->printResponse("Движение","movement");
			}
		}
	}
?>
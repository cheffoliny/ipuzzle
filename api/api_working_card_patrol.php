<?php
	class ApiWorkingCardPatrol {
		public function result( DBResponse $oResponse ) {

			$oWorkCard = new DBWorkCard();
			$oWorkingCards = new DBWorkCardOffices();
			$oRoadLists	= new DBRoadLists();
			
			$nIDCard = Params::get("nIDCard", 0);				
			$nRegion = Params::get("nRegion", 0);				
			
			$aOffice = array();
			$aWorkCard = array();
			$aOffice = $oWorkingCards->getWorkCardOffices( $nIDCard );
			$aWorkCard = $oWorkCard->getWorkCardInfo( $nIDCard );

			if ( isset($aWorkCard['endTime']) && empty($aWorkCard['endTime']) ) {
				$locked = "no";	
			} else $locked = "yes";
			
			$oResponse->setFormElement('form1', 'isLOCK', array('value' => $locked), $locked);

			$oResponse->setFormElement('form1', 'nRegion', array(), '');
			$oResponse->setFormElementChild('form1', 'nRegion', array('value' => 0), 'Всички');			
			
			foreach ( $aOffice as $key => $val ) {
				if ( $nRegion == $key ) {
					$oResponse->setFormElementChild('form1', 'nRegion', array('value' => $key, 'selected' => 'selected'), $val);
				} else  $oResponse->setFormElementChild('form1', 'nRegion', array('value' => $key), $val);
			}				
			
			$sOffices = $oWorkingCards->getWorkCardOffices2( $nIDCard );
			
			if( empty( $sOffices ))	{
				throw new Exception("Не сте запаметили никакви региони");
			}	else	{	
				//$aWorkCard = $oWorkCard->getRecord($nIDCard);
				$aWorkCard = $oWorkCard->getWorkCardInfo( $nIDCard );
				//APILog::Log(0, $aWorkCard);
				$nWorkCardStartTime = $aWorkCard['sttime'];		//strtotime($aWorkCard['start_time']);
				$nWorkCardEndTime 	= $aWorkCard['locked'];		//strtotime($aWorkCard['end_time']);
							
				$oRoadLists->getReport( $nRegion, $sOffices, $oResponse,$nWorkCardStartTime,$nWorkCardEndTime, $nIDCard );
				$oResponse->printResponse("Работни карти", "working_cards");
			}
		}
	}
?>
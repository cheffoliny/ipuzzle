<?php
	class ApiWorkingCards {
		public function result( DBResponse $oResponse ) {

			$oWorkingCards = new DBWorkCard();
			
			$nNum =				Params::get("nNum", 0);				
			$sStatus =			Params::get("sStatus", '');				
			$sFrom =			jsDateToTimestamp( Params::get("sFrom", 0) );				
			$sTo =				jsDateToTimestamp( Params::get("sTo", 0) );				
			$act =				Params::get("sAct", '');			
			$nIDDispatcher =	Params::get("nIDDispatcher", 0);				

			$aData = array();
			$aData['num']		= $nNum;
			$aData['status']	= $sStatus;
			$aData['from']		= $sFrom;
			$aData['to']		= $sTo;
			$aData['dispatcher']= $nIDDispatcher;
						
			$dNames = array();
			$dNames = $oWorkingCards->getDispecherName( );
			
			$oResponse->setFormElement('form1', 'nIDDispatcher', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDDispatcher', array('value' => 0), 'Всички');			
			
			foreach ( $dNames as $key => $val ) {
				if ( $nIDDispatcher == $key ) {
					$oResponse->setFormElementChild('form1', 'nIDDispatcher', array('value' => $key, 'selected' => 'selected'), $val);
				} else $oResponse->setFormElementChild('form1', 'nIDDispatcher', array('value' => $key), $val);
			}				
			
			//if ( $act == 'search' ) {
				$oWorkingCards->getReport( $aData, $oResponse );
			//}
			
			$oResponse->printResponse("Работни карти", "working_cards");
		}
	}
?>
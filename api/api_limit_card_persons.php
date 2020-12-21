<?php
	class ApiLimitCardPersons {
		public function result( DBResponse $oResponse ) {
			
			$nID = Params::get('nID', 0);

			$oLimitCards = new DBLimitCardPersons();
			$oLimitCards->getReport( $nID, $oResponse );	

			$oResponse->printResponse("Персонал", "limit_card_persons");
		}

		public function result2( DBResponse $oResponse ) {
			
			$nID = Params::get('nID', 0);

			$oLimitCards = new DBLimitCardPersons();
			$aLimitCard = array();
			
			$aLimitCard = $oLimitCards->getPersonsByLC( $nID );
			
			if ( isset($aLimitCard[0]['id']) ) {
				$aData['id'] = $aLimitCard[0]['id'];
				$aData['start'] = $aLimitCard[0]['planned_start'];
				
				$oLimitCards->getReport2( $aData, $oResponse );	
			}
			
			//APILog::Log(0, $oResponse);
			$oResponse->printResponse("Персонал", "limit_card_persons");
		}

		function delete( DBResponse $oResponse ) {
			$nID = Params::get('nIDPerson', 0);

			$oLimitCards = new DBLimitCardPersons();
			$oLimitCards->delete( $nID );	
			
			$oResponse->printResponse();
		}

		function limit( DBResponse $oResponse ) {
			$chk = Params::get('chk', 0);
			$bla = array();
			$oTechRequests = new DBTechRequests();
			
			foreach( $chk as $k => $v ) {
				if ( !empty($v) ) {
					array_push($bla, $k);
				}
			}
			
			if ( !empty($bla) ) {	
				$par = implode( ",", $bla );	
				$oTechRequests->makeLimitCard( $par );
			}
			
			$oResponse->printResponse();
		}

	}
?>
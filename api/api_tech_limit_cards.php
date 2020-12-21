<?php
	class ApiTechLimitCards {
		public function result( DBResponse $oResponse ) {
			$aData	= array();
			$aFirms = array();
			$aOffices = array();

			$nIDFirm	= Params::get("nIDFirm", 0);
			$nIDOffice	= Params::get("nIDOffice", 0);
			$nObject	= Params::get("nObject", 0);
			$nNoLimitCard	= Params::get("nNoLimitCard", 0);
			$sAct		= Params::get("sAct", 'load');
			$sTypeBugnq	= Params::get("sTypeBugnq", '');
			$sStatus	= Params::get("sStatus", '');
			$nNumber    = Params::get("nNumber",0);
			$dFrom		= jsDateToTimestamp( Params::get("date_from", '') );
			$dTo		= jsDateToTimestamp( Params::get("date_to", '') );
			
			$oTechSupportRequests	= new DBTechRequests();
			$oFirms					= new DBFirms();
			$oOffices				= new DBOffices();
			$oTechLimitCards		= new DBTechLimitCards();		

			
			if ( empty($nIDFirm) ) {
				$nIDOffice = $_SESSION['userdata']['id_office'];
				$nIDFirm = $oOffices->getFirmByIDOffice( $nIDOffice );
			}
			
			if($nIDFirm == -1) $nIDFirm = 0;
			
			$aFirms	= $oFirms->getFirms();

			$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			if( $_SESSION['userdata']['access_right_all_regions'] )
			{
				$oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => -1), '--Всички--');			
				$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => 0), '--Всички--');			
			}
			
			foreach ( $aFirms as $key => $val ) {
				if ( $nIDFirm == $key ) {
					$oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => $key, 'selected' => 'selected'), $val);
				} else $oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => $key), $val);
			}				
			
			unset($key); unset($val);
			
			if ( $nIDFirm > 0 ) {
				$aOffices = $oOffices->getFirmOfficesAssoc( $nIDFirm );
				foreach ( $aOffices as $key => $val ) {
					if ( $nIDOffice == $key ) {
						$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => $key, 'selected' => 'selected'), $val['name']);
					} else $oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => $key), $val['name']);
				}	
			}			

			if ( $sAct == 'search' ) {
				$aData['id_object']			= $nObject;
				$aData['type']				= $sTypeBugnq;
				$aData['startTime']			= $dFrom;
				$aData['endTime']			= $dTo;
				$aData['id_firm']			= $nIDFirm;
				$aData['id_office']			= $nIDOffice;
				$aData['status']			= $sStatus;
				$aData['id']				= $nNumber;
				
				$oTechLimitCards->getReport( $aData, $oResponse );
			}			
		
			$oResponse->printResponse("Задачи", "tech_requests");
		}

		function delete( DBResponse $oResponse ) {
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
				$oTechRequests->delRequests( $par );
			}
			
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
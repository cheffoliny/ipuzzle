<?php
	class ApiSetSetupTrouble {
		public function load( DBResponse $oResponse ) {
			$nID = Params::get("nID", 0);
			$sType = Params::get("sTroubleType", "");
			$nTroubleTypeName = Params::get("nTroubleTypeName", 0);
			$nReasonTypeName = Params::get("nReasonTypeName", 0);
			
			$oTrouble = new DBTroubles();
			$aTroubles = array();
			$aTrouble = array();
			$aReasons = array();
			
			$oResponse->setFormElement('form1', 'sTroubleType', array(), '' );	
			$oResponse->setFormElement('form1', 'nTroubleTypeName', array(), '');	
			$oResponse->setFormElement('form1', 'nReasonTypeName', array(), '');	
			$oResponse->setFormElementChild('form1', 'nTroubleTypeName', array('value' => 0), '---Изберете---');
			$oResponse->setFormElementChild('form1', 'nReasonTypeName', array('value' => 0), '---Изберете---');

			$aTroubles = $oTrouble->getTroubleNames();
			$aReasons = $oTrouble->getReasonsNames();
			
		//	$type = $sType == "tech" ? 0 : 1;
			
			if ( $nID > 0 ) { // редакция
				$sType = Params::get("sTroubleType", "");
				$aTrouble = $oTrouble->getTroubleByID( $nID );
				
				$sType = empty($sType) ? $aTrouble[0]['type'] : $sType;
				$nTroubleTypeName = empty($nTroubleTypeName) ? $aTrouble[0]['id_problem'] : $nTroubleTypeName;
				$nReasonTypeName = empty($nReasonTypeName) ? $aTrouble[0]['id_reason'] : $nReasonTypeName;
				
				if ( $sType == "tech" ) {
					$oResponse->setFormElementChild('form1', 'sTroubleType', array('value' => 'tech', 'selected' => 'selected'), 'Технически' );	
					$oResponse->setFormElementChild('form1', 'sTroubleType', array('value' => 'operativ'), 'Оперативен' );					
				} else {
					$oResponse->setFormElementChild('form1', 'sTroubleType', array('value' => 'tech'), 'Технически' );	
					$oResponse->setFormElementChild('form1', 'sTroubleType', array('value' => 'operativ', 'selected' => 'selected'), 'Оперативен' );					
				}
				
				$oResponse->setFormElement('form1', 'sTroubleInfo', array(), isset($aTrouble[0]['problem_info']) ? $aTrouble[0]['problem_info'] : '' );	
				$oResponse->setFormElement('form1', 'sReasonInfo', array(), isset($aTrouble[0]['reason_info']) ? $aTrouble[0]['reason_info'] : '');	
				
				
			} else {
				$oResponse->setFormElementChild('form1', 'sTroubleType', array('value' => 'tech', 'selected' => 'selected'), 'Технически' );	
				$oResponse->setFormElementChild('form1', 'sTroubleType', array('value' => 'operativ'), 'Оперативен' );					
			}

			APILog::Log(0, $sType);
			foreach ( $aTroubles as $key => $val ) {
				if ( $val['operativ'] == $sType ) {
					if ( $nTroubleTypeName == $key ) {
						$oResponse->setFormElementChild('form1', 'nTroubleTypeName', array('value' => $key, 'selected' => 'selected'), $val['name']);
					} else $oResponse->setFormElementChild('form1', 'nTroubleTypeName', array('value' => $key), $val['name']);
				}
			}
			
			foreach ( $aReasons as $key => $val ) {
				if ( $val['operativ'] == $sType ) {
					if ( $nReasonTypeName == $key ) {
						$oResponse->setFormElementChild('form1', 'nReasonTypeName', array('value' => $key, 'selected' => 'selected'), $val['name']);
					} else $oResponse->setFormElementChild('form1', 'nReasonTypeName', array('value' => $key), $val['name']);
				}
			}

			$oResponse->printResponse();
		}
			
		public function save( DBResponse $oResponse ) {
			$nID				= Params::get("nID", 0);
			$nIDObject			= Params::get("nIDObject", 0);
			$sType				= Params::get("sTroubleType", "tech");
			$nTroubleTypeName	= Params::get("nTroubleTypeName", 0);
			$nReasonTypeName	= Params::get("nReasonTypeName", 0);
			$sTroubleInfo		= Params::get("sTroubleInfo", "");
			$sReasonInfo		= Params::get("sReasonInfo", "");
			$updated_user = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;						

			$oTrouble = new DBTroubles();

			$aData = array();
			$aData['id'] = $nID;
			$aData['id_obj'] = $nIDObject;
			$aData['id_problem'] = $nTroubleTypeName;
			$aData['id_reason'] = $nReasonTypeName;
			$aData['problem_info'] = $sTroubleInfo;

			if ( $nID == 0 ) {
				$aData['problem_date'] = time();
				$aData['id_user_problem'] = $updated_user;
			}
			
			if ( $nReasonTypeName > 0 ) {
				$aData['id_user_problem'] = $updated_user;
				$aData['reason_date'] = time();
				$aData['reason_info'] = $sReasonInfo;
			} else {
				$aData['id_user_reason'] = 0;
				$aData['problem_date'] = time();
				$aData['id_user_problem'] = $updated_user;
			}				
			
			$oTrouble->update( $aData );
			
			$oResponse->printResponse();
		}
			
	}
	
?>
<?php

	class ApiSetupVisibleTabs {
		public function result(DBResponse $oResponse) {
			
			$nID = Params::get('nID', 0);

			$oResponse->setFormElement('form1', 'sName', array(), '');
			$oResponse->setFormElement('form1', 'sDefault', array(), '');
			$oResponse->setFormElement('form1', 'sCode', array(), '');
			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			$oResponse->setFormElement('form1', 'nIDReactionFirm', array(), '');
			$oResponse->setFormElement('form1', 'nIDReactionOffice', array(), '');
			$oResponse->setFormElement('form1', 'nIDTechFirm', array(), '');
			$oResponse->setFormElement('form1', 'nIDTechOffice', array(), '');
			$oResponse->setFormElement('form1', 'functions',array(),'');
			$oResponse->setFormElement('form1', 'objtype',array(),'');
			$oResponse->setFormElement('form1', 'statuses',array(),'');
			
			
			if ( !empty( $nID ) ) {  //Радактиране на таб	
				$oTabs = new DBVisibleTabs();
				$aTabs = $oTabs->getTabsByID($nID);
				
				$tabs =  isset($aTabs[0]['data']) && !empty($aTabs[0]['data']) ? unserialize($aTabs[0]['data']) : array();
				foreach ( $tabs as $key => $val ) {
					if ( $val == 1 ) {
						APILog::Log(0, $key);
						$oResponse->setFormElement('form1', $key, array('checked' => 1), "");
					}
				}		
				$oResponse->setFormElement('form1', 'sName', array(), isset($aTabs[0]['name']) ? $aTabs[0]['name'] : "");
				$oResponse->setFormElement('form1', 'sDefault', array('checked' => isset($aTabs[0]['def']) && !empty($aTabs[0]['def']) ? $aTabs[0]['def'] : ""), "");

			} else {    // Нов таб
				

			}
			
			$oResponse->printResponse(); 
			
		}
		
		
		public function deleleTab() {
			$nID = Params::get('nID', 0);
			
		//	$oTabs = new DBVisibleTabs();
		//	$oTabs->delete($nID);
		}
		
		public function save(DBResponse $oResponse) {
			$nID = Params::get('nID', 0);
			$def = Params::get('sDefault', 0);
			$name = Params::get('sName', 0);
			$oTabs = new DBVisibleTabs();
			
			if ( empty($name) ) {
				throw new Exception("Въведете наименование!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$aData = array();
			$aData['sCode']		= Params::get('sCode', 0);
			$aData['sEGN']		= Params::get('sEGN', 0);
			$aData['sLK_Num']	= Params::get('sLK_Num', 0);
			$aData['sPhone']	= Params::get('sPhone', 0);
			$aData['sBusinessPhone'] = Params::get('sBusinessPhone', 0);
			$aData['sMobile']	= Params::get('sMobile', 0);
			$aData['sAddress']	= Params::get('sAddress', 0);
			$aData['sIBAN']		= Params::get('sIBAN', 0);
			$aData['sEmail']	= Params::get('sEmail', 0);
			$aData['sFirm']		= Params::get('sFirm', 0);
			$aData['sObject']	= Params::get('sObject', 0);
			$aData['sRegion']	= Params::get('sRegion', 0);
			$aData['sPosition']	= Params::get('sPosition', 0);
			$aData['sDateFrom']	= Params::get('sDateFrom', 0);
			$aData['sDateVacate'] = Params::get('sDateVacate', 0);
			$aData['sPeriod']	= Params::get('sPeriod', 0);
			$aData['sStatus']	= Params::get('sStatus', 0);
			$aData['sCipher']	= Params::get('sCipher', 0);
			$aData['sPositionNC']	= Params::get('sPositionNC', 0);
			$aData['sMinSalary']	= Params::get('sMinSalary', 0);
			$aData['sEducation']	= Params::get('sEducation', 0);			
			
			$data = serialize( $aData );
			
			$aData = array();
			$aData['id'] = $nID;
			$aData['name'] = $name;
			$aData['data'] = $data;
			$aData['id_person'] = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
			$aData['def'] = $def;
			$aData['to_arc'] = 0;
			
			if ( $def == 1 ) {
				$oTabs->resetDefaults();
			}

			$oTabs->update( $aData );
			
			
			$oResponse->printResponse();
		}
	}
	


?>
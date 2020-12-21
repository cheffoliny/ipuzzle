<?php

	class ApiAssetSearch {
		
		public function load(DBResponse $oResponse) {
			
			$nIDFirmS = Params::get('nIDFirmS','0');
			$nIDOfficeS = Params::get('nIDOfficeS','0');
			$nIDPersonS = Params::get('nIDPersonS','0');
			$nIDGroupS = Params::get('nIDGroupS','0');
			
			$oDBFirms = new DBFirms();
			$oDBAssetsGroup = new DBAssetsGroups();
			
			$aFirms = $oDBFirms->getFirms4();
			
			$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>'0')), "--Всички--");
			foreach($aFirms as $key => $value) {
				if($key == $nIDFirmS) {
					$ch = array('selected' => 'selected');
				} else {
					$ch = array();
				}
				$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>$key),$ch), $value);
			}		

			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Всички--");
			
			if(!empty($nIDFirmS)) {
				$oDBOffices = new DBOffices();
				$aOffices = $oDBOffices->getOfficesByIDFirm($nIDFirmS);
				
				foreach ($aOffices as $key => $value) {
					if($key == $nIDOfficeS) {
						$ch = array("selected" => "selected" ); 
					} else {
						$ch = array();
					}
					$oResponse->setFormElementChild('form1','nIDOffice',array_merge(array("value" => $key),$ch),$value);
				}
			}
			
			$oResponse->setFormElement('form1', 'nIDPerson', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>'0')), "--Всички--");
			
			if(!empty($nIDOfficeS)) {
				$oDBPersonnel = new DBPersonnel();
				$oDBPersonnel = new DBPersonnel();
				$aPersons = $oDBPersonnel -> getPersonnelsByIDOffice($nIDOfficeS); 
				
				foreach ($aPersons as $key => $value) {
					if($key == $nIDPersonS) {
						$ch = array("selected" => "selected" ); 
					} else {
						$ch = array();
					}
					$oResponse->setFormElementChild('form1','nIDPerson',array_merge(array("value" => $key),$ch),$value);
				}
				
			}
			
			$oResponse->setFormElement('form1', 'nIDGroup', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDGroup', array_merge(array("value"=>'0')), "--Всички--");
			$this->showGroups($oResponse,0,$nIDGroupS);
			
			$oResponse->printResponse();
		}

		public function showGroups($oResponse,$nIDGroup,$nIDGroupSelected) {
			$oDBAssetsGroup = new DBAssetsGroups();
			$aGroups = $oDBAssetsGroup -> getChilds($nIDGroup);	
			
			global $space;
			if(!empty($nIDGroup))$space .= "    ";
			
			foreach ($aGroups as $key => $value ) {
				
				if($key == $nIDGroupSelected) {
					$ch = array("selected" => "selected");
				} else {
					$ch = array();
				}
				$oResponse->setFormElementChild('form1', 'nIDGroup', array_merge(array("value"=>$key),$ch), $space.$value);
				$this->showGroups($oResponse,$key,$nIDGroupSelected);
			}
			
			if(!empty($nIDGroup))$space = substr($space,4);
		}
		
		public function loadOffices(DBResponse $oResponse) {
			$nFirm 	=	Params::get('nIDFirm');
			
			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			
			if(!empty($nFirm)) {
				$oDBOffices = new DBOffices();
				$aOffices = $oDBOffices->getOfficesByIDFirm($nFirm);
				
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Всички--");
				foreach($aOffices as $key => $value) {
					if (in_array($key,$_SESSION['userdata']['access_right_regions'])) {
						$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key)), $value);
					}
				}
			} else {
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Всички--");
			}
			
			$oResponse->printResponse();
		}
		
		public function loadPersons(DBResponse $oResponse) {
			$nIDOffice 	=	Params::get('nIDOffice');
			
			$oResponse->setFormElement('form1', 'nIDPerson', array(), '');
			
			if(!empty($nIDOffice)) {

				$oDBPersonnel = new DBPersonnel();
				$aPersons = $oDBPersonnel -> getPersonnelsByIDOffice($nIDOffice); 
				
				$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>'0')), "--Всички--");
				foreach($aPersons as $key => $value) {
					$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>$key)), $value);
				}
			} else {
				$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>'0')), "--Всички--");
			}
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse) {
			$nIDFirm 	=	Params::get('nIDFirm','');
			$nIDOffice 	=	Params::get('nIDOffice','');
			$nIDPerson	=	Params::get('nIDPerson','');
			$nIDGroup	=	Params::get('nIDGroup','');
			$nNum		=	Params::get('nNum','');
			$nAssetSource =	Params::get('nIDSource','');
			$nAssetDest = 	Params::get('nIDDest', '');
			
			$oDBAssets = new DBAssets();
			

			
			//throw new Exception($nIDGroup);
			
			global $aSubGroups;
			$aSubGroups = array();
			$aSubGroups[] = $nIDGroup;
			
			if( !empty($nIDGroup)) {
				$this->getSubGroups($nIDGroup);
			}
			$sSubGroups = implode(',',$aSubGroups);
			
			
			$aData = array();
			$aData['nIDFirm'] = $nIDFirm;
			$aData['nIDOffice'] = $nIDOffice;
			$aData['nIDPerson'] = $nIDPerson;
			$aData['sIDGroups'] = $sSubGroups;
			$aData['nNum']		= $nNum;
			$aData['nAssetSource'] 	= $nAssetSource;
			$aData['nAssetDest'] = $nAssetDest;
			
			$oDBAssets -> searchAssets($aData,$oResponse);
			
			$oResponse->printResponse();
		}
		
		public function getSubGroups($nIDGroup) {
			$oDBAssetsGroup = new DBAssetsGroups();
			$aGroups = $oDBAssetsGroup -> getChilds($nIDGroup);
			
			global $aSubGroups;
			
			foreach ($aGroups as $key => $value ) {
				$this->showGroups($oResponse,$key);
				$aSubGroups[] = $key;
			}	
		}
	}

?>
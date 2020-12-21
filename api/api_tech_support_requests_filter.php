<?php

	class ApiTechSupportRequestsFilter {
		
		public function load( DBResponse $oResponse) {
			
			$nID = Params::get('nID','0');
			
			if(!empty($nID)) {
				
				$oTechSupportRequestsFilter = new DBTechSupportRequestsFilters();
				
				$aFilter = $oTechSupportRequestsFilter->getRecord($nID);
			
				$oResponse->setFormElement('form1','filter_name',array(),$aFilter['name']);
				
				if(!empty($aFilter['is_default'])) {
					$oResponse->setFormElement('form1','is_default',array("checked" => "checked"));
				}
				
				$aVisibleColumns = unserialize($aFilter['visible_columns']);
				
				foreach ($aVisibleColumns as $key => $value) {
					if(!empty($value)) {
						$oResponse->setFormElement('form1',$key,array("checked" => "checked"));
					}
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save() {
			
			$nID = Params::get('nID');
			$nIsDefault = Params::get('is_default',0);
			$sName = Params::get('filter_name','');
			
			
			$aVisibleColumns = array();
			$aVisibleColumns['firm'] = Params::get('firm',0);
			$aVisibleColumns['office'] = Params::get('office',0);
			$aVisibleColumns['type'] = Params::get('type',0);
			$aVisibleColumns['client'] = Params::get('client',0);
			$aVisibleColumns['created_user'] = Params::get('created_user',0);
			$aVisibleColumns['limit_card'] = Params::get('limit_card',0);
			$aVisibleColumns['make_planning_person_name'] = Params::get('make_planning_person_name',0);
			$aVisibleColumns['note'] = Params::get('note',0);
			
			$sVisibleColumns = serialize($aVisibleColumns);
			
			if(empty($sName)) {
				throw new Exception("Въведете име на филтъра");
			}
			
			$nIDPerson = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			
			$oTechSupportRequestsFilter = new DBTechSupportRequestsFilters();
			
			if(!empty($nIsDefault)) {
				$oTechSupportRequestsFilter->resetDefaults($nIDPerson);
			}
			
			$aFilter = array();
			
			if(!empty($nID)) {
				$aFilter['id'] = $nID;
			}
			$aFilter['name'] = $sName;
			$aFilter['is_default'] = $nIsDefault;
			$aFilter['id_person'] = $nIDPerson;
			$aFilter['visible_columns'] = $sVisibleColumns;
			
			$oTechSupportRequestsFilter->update($aFilter);
			
		}
	}

?>
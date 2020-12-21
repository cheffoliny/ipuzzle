<?php

	class ApiMovementScheme {
		
		public function load( DBResponse $oResponse) {
			
			$nID = Params::get('nID','0');
			
			$oDBMovementSchemes = new DBMovementSchemes();
			
			
			if(!empty($nID)) {
			
				$aFilters = $oDBMovementSchemes->getRecord($nID);
					
				$oResponse->setFormElement('form1','name',array(),$aFilters['name']);
				
				if(!empty($aFilters['def']))
				$oResponse->setFormElement('form1','def',array("checked" => "checked"));
				
				if(!empty($aFilters['office']))
				$oResponse->setFormElement('form1','office',array("checked" => "checked"));
				
				if(!empty($aFilters['start_time']))
				$oResponse->setFormElement('form1','start_time',array("checked" => "checked"));
				
				if(!empty($aFilters['end_time']))
				$oResponse->setFormElement('form1','end_time',array("checked" => "checked"));
				
				if(!empty($aFilters['reason_time']))
				$oResponse->setFormElement('form1','reason_time',array("checked" => "checked"));
				
				if(!empty($aFilters['stay_time']))
				$oResponse->setFormElement('form1','stay_time',array("checked" => "checked"));
				
				if(!empty($aFilters['note']))
				$oResponse->setFormElement('form1','note',array("checked" => "checked"));
				
			}
			
			$oResponse->printResponse();
		}
		
		public function save() {
			
			
			$nID = Params::get('nID','0');
			$sName = Params::get('name','');
			
			$def = Params::get('def','');
			
			$office = Params::get('office','0');
			$start_time = Params::get('start_time','0');
			$end_time = Params::get('end_time','0');
			$reason_time = Params::get('reason_time','0');
			$stay_time = Params::get('stay_time','0');
			$note = Params::get('note','0');
			
			if(empty($sName)) {
				throw new Exception('Въведете име за шаблона');
			}
			
			$oDBMovementSchemes = new DBMovementSchemes();
			
			if(!empty($def)) {
				$oDBMovementSchemes->eraseDefaults();
			}
			
			$aData = array();
						
			$aData['id'] = $nID;
			$aData['name'] = $sName;
			
			$aData['def'] = $def;
			
			$aData['office'] = $office;
			$aData['start_time'] = $start_time;
			$aData['end_time'] = $end_time;
			$aData['reason_time'] = $reason_time;
			$aData['stay_time'] = $stay_time;
			$aData['note'] = $note;
		
			$oDBMovementSchemes->update($aData);
		}
	}

?>
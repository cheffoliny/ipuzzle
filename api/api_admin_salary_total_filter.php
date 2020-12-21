<?php

	class ApiAdminSalaryTotalFilter {
		
		public function load( DBResponse $oResponse) {
			
			$nID = Params::get('nID','0');
			
			$oDBSalaryEarnings = new DBSalaryEarning();
			$oDBSalaryExpence = new DBSalaryExpense();
			$oDBAdminSalaryTotalFilters = new DBAdminSalaryTotalFilters();
			
			$aEarnings = array();
			$aExpence = array();
			
			$aEarnings = $oDBSalaryEarnings->getEarnings();
			$aExpence = $oDBSalaryExpence->getExpence();
			
			$oResponse->setFormElement('form1','all_earnings',array(),'');
			$oResponse->setFormElement('form1','all_expenses',array(),'');
			
			if(empty($nID)) {
			
				foreach ($aEarnings as $code => $name ) {
					$oResponse->setFormElementChild('form1','all_earnings',array('value' => $code),$code);
				}
				foreach ($aExpence as $code => $name ) {
					$oResponse->setFormElementChild('form1','all_expenses',array('value' => $code),$code);
				}
			} else {
				
				$aFilters = $oDBAdminSalaryTotalFilters->getRecord($nID);
				//throw new Exception($aFilters['earnings']);
				$aEarningsInFilter = explode(',',$aFilters['earnings']);
				$aExpencesInFilter = explode(',',$aFilters['expenses']);
				
				
				$oResponse->setFormElement('form1','name',array(),$aFilters['name']);
				
				if(!empty($aFilters['fix_salary']))
				$oResponse->setFormElement('form1','fix_salary',array("checked" => "checked"));
				
				if(!empty($aFilters['min_salary']))
				$oResponse->setFormElement('form1','min_salary',array("checked" => "checked"));
				
				if(!empty($aFilters['insurance']))
				$oResponse->setFormElement('form1','insurance',array("checked" => "checked"));
				
				if(!empty($aFilters['trial']))
				$oResponse->setFormElement('form1','trial',array("checked" => "checked"));
				
				if(!empty($aFilters['due_days']))
				$oResponse->setFormElement('form1','due_days',array("checked" => "checked"));
				
				if(!empty($aFilters['used_days']))
				$oResponse->setFormElement('form1','used_days',array("checked" => "checked"));
				
				if(!empty($aFilters['remain']))
				$oResponse->setFormElement('form1','remain',array("checked" => "checked"));
				
				if(!empty($aFilters['ear_exp']))
				$oResponse->setFormElement('form1','ear_exp',array("checked" => "checked"));
				
				// Pavel
				if ( !empty($aFilters['egn']) ) {
					$oResponse->setFormElement( "form1", "egn", array("checked" => "checked") );
				}				
				
				$oResponse->setFormElement('form1','account_earnings',array(),'');
				$oResponse->setFormElement('form1','account_expenses',array(),'');
				
				foreach ($aEarnings as $code => $name ) {
					
					if( in_array($code,$aEarningsInFilter) ) {
						$oResponse->setFormElementChild('form1','account_earnings',array('value' => $code),$code);
					} else {
						$oResponse->setFormElementChild('form1','all_earnings',array('value' => $code),$code);
					}
				}
				foreach ($aExpence as $code => $name ) {
					if( in_array($code,$aExpencesInFilter)) {
						$oResponse->setFormElementChild('form1','account_expenses',array('value' => $code),$code);
					} else {
						$oResponse->setFormElementChild('form1','all_expenses',array('value' => $code),$code);
					}
				}
				
			}
			
			$oResponse->printResponse();
		}
		
		public function save() {

			$nID = Params::get('nID','0');
			$sName = Params::get('name','');
			$aEarnings = Params::get('account_earnings','');
			$aExpenses = Params::get('account_expenses','');
			
			$ear_exp = Params::get('ear_exp','');
			
			$fix_salary = Params::get('fix_salary','0');
			$min_salary = Params::get('min_salary','0');
			$insurance = Params::get('insurance','0');
			$trial = Params::get('trial','0');
			$due_days = Params::get('due_days','0');
			$used_days = Params::get('used_days','0');
			$remain = Params::get('remain','0');
			// Pavel
			$egn = Params::get('egn', 0);
			
			if(empty($sName)) {
				throw new Exception('Въведете име за шаблона');
			}
			
			$sEarnings = implode(',',$aEarnings);
			$sExpenses = implode(',',$aExpenses);
			
			
			
			$oDBAdminSalaryTotalFilters = new DBAdminSalaryTotalFilters();
			
			$aData = array();
			
			$aData['id'] = $nID;
			$aData['name'] = $sName;
			$aData['earnings'] = $sEarnings;
			$aData['expenses'] = $sExpenses;
			
			$aData['ear_exp'] = $ear_exp;
			
			$aData['fix_salary'] = $fix_salary;
			$aData['min_salary'] = $min_salary;
			$aData['insurance'] = $insurance;
			$aData['trial'] = $trial;
			$aData['due_days'] = $due_days;
			$aData['used_days'] = $used_days;
			$aData['remain'] = $remain;
			$aData['egn'] = $egn;

		
			$oDBAdminSalaryTotalFilters->update($aData);
		}
		
	}

?>
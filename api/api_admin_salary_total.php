<?php

	class ApiAdminSalaryTotal
	{	
		public function load( DBResponse $oResponse ) {
			
			$oDBFirms = new DBFirms();
			$oDBOffices = new DBOffices();
			$oDBAdminSalaryTotalFilters = new DBAdminSalaryTotalFilters();
			$oDBPositions = new DBPositions();
			
			$aFirms = array();
			$aOffices = array();
			$aSchemes = array();
			
			$aFirms = $oDBFirms->getFirms4();
			$aOffices = $oDBOffices->getOffices4();
			
			$oResponse->setFormElement('form1', 'all_firms',		array(), '');
			$oResponse->setFormElement('form1', 'all_regions',		array(), '');
			$oResponse->setFormElement('form1', 'account_regions',	array(), '');
			$oResponse->setFormElement('form1', 'schemes',	array(), '');
			$oResponse->setFormElement('form1', 'positions',	array(), '');

			
			foreach ( $aFirms as $key => $val ) {
				$oResponse->setFormElementChild('form1', 'all_firms',	array('value' => $key),$val);
			}
			
			
			if(!empty($_SESSION['userdata']['access_right_all_regions'])) {
				foreach ( $aOffices as $key => $val ) {
					$oResponse->setFormElementChild('form1', 'all_regions',	array('value' => $key),$val);
				}
			} else {
				foreach ( $aOffices as $key => $val ) {
					if (in_array($key,$_SESSION['userdata']['access_right_regions'])) {
						$oResponse->setFormElementChild('form1', 'all_regions',	array('value' => $key),$val);
					}					
				}
			}
			
			$aSchemes = $oDBAdminSalaryTotalFilters->getSchemes();
			
			$oResponse->setFormElementChild('form1', 'schemes',	array('value' => '0'),'--- Изберете ---');
			foreach ( $aSchemes as $key => $value ) {
				$oResponse->setFormElementChild('form1','schemes',array('value' => $key),$value);
			}
			
			$aPositions = $oDBPositions->getPositions();
			
			$oResponse->setFormElementChild('form1', 'positions',	array('value' => '0'),'--- Всички ---');
			foreach ( $aPositions as $key => $value ) {
				$oResponse->setFormElementChild('form1','positions',array('value' => $key),$value);
			}
			
			
			$oResponse->printResponse();
		}
		
		public function result(  DBResponse $oResponse ) {
			$sAct = Params::get('sAct','1');
			switch ($sAct) {
				case '1':$this->result1($oResponse);break;
				case '2':$this->result2($oResponse);break;
			}
		}
		
		public function result1( DBResponse $oResponse )	{
			
			$account_firms 		= Params::get('account_firms','');
			$account_regions 	= Params::get('account_regions', '');
			$account_objects 	= Params::get('account_objects', '');
			$nIDObject 			= Params::get('nIDObject','');
			$nType 				= Params::get('type','1');
			$nRadio 			= Params::get('nRadio','1');
			$nYear 				= Params::get('year','');
			$nMonth 			= Params::get('month','');
			$nScheme 			= Params::get('schemes','');
			$nPosition 			= Params::get('positions','0');
			$nActive 			= Params::get('active', 0);

			
			if( empty($nYear) || $nYear < 2007 || $nYear > 2050 ) {
				throw new Exception("Въведете коректна година",DBAPI_ERR_INVALID_PARAM);
			}
			if( empty($nMonth) || $nMonth < 1 || $nMonth > 12 ) {
				throw new Exception("Въведете коректен месец",DBAPI_ERR_INVALID_PARAM);
			}
			
			$nMonth = zero_padding($nMonth,2);
			
			$oDBSalary = new DBSalary();
			
			$sIDFirms = "";
			if(!empty($account_firms)) {
				$sIDFirms = implode(",",$account_firms);
			}
			
			$sIDOffices = "";
			if(!empty($account_regions)) {
				$sIDOffices = implode(",",$account_regions);
			}
			
			$sIDObjects = "";
			if(!empty($account_objects)) {
				$sIDObjects = implode(",",$account_objects);
			}
			
			$aData 					= array();
			$aData['sIDFirms'] 		= $sIDFirms;
			$aData['sIDOffices'] 	= $sIDOffices;
			$aData['sIDObjects'] 	= $sIDObjects;
			$aData['nMonth'] 		= $nYear.$nMonth;
			$aData['id_scheme'] 	= $nScheme;
			$aData['id_position'] 	= $nPosition;
			$aData['nRadio'] 		= $nRadio;
			$aData['active'] 		= $nActive;

			if($nType == 1) {
				$oDBSalary->getReport1($aData,$oResponse);
				$oResponse->printResponse("Работна заплата(Обобщена)","salary_total");
			} else {
				$oDBSalary->getReport2($aData,$oResponse);
				$oResponse->printResponse("Работна заплата(Обобщена)","salary_total2");
			}
		}
		
		public function result2(DBResponse $oResponse) {
			
			$account_firms 		= Params::get('account_firms','');
			$account_regions 	= Params::get('account_regions', '');
			$nYear 				= Params::get('year','');
			$nMonth 			= Params::get('month','');
			$nScheme 			= Params::get('schemes','');
			$nPosition 			= Params::get('positions','0');
			$nRadio 			= Params::get('nRadio','1');
			$nActive 			= Params::get('active', 0);

			if( empty($nYear) || $nYear < 2007 || $nYear > 2050 ) {
				throw new Exception("Въведете коректна година",DBAPI_ERR_INVALID_PARAM);
			}
			if( empty($nMonth) || $nMonth < 1 || $nMonth > 12 ) {
				throw new Exception("Въведете коректен месец",DBAPI_ERR_INVALID_PARAM);
			}
			
			$nMonth = zero_padding($nMonth,2);
			
			$nMonth = $nYear.$nMonth;
			
			$oDBSalary = new DBSalary();
			$oDBOffices = new DBOffices();

			APILog::Log(154, $account_firms);

			$sIDFirms = implode(',',$account_firms);

			APILog::Log( 158, "<pre>".print_r($sIDFirms)."<pre>");

			APILog::Log(158, $account_regions);
			$sIDOfficesFrom = implode(",",$account_regions);

			APILog::Log( 161, "<pre>".print_r($sIDOfficesFrom)."<pre>");

			$aData 					= array();
			$aData['nMonth'] 		= $nMonth;
			$aData['sIDFirms'] 		= $sIDFirms;
			$aData['sIDOffices'] 	= $sIDOfficesFrom;
			$aData['id_position'] 	= $nPosition;
			$aData['id_scheme'] 	= $nScheme;
			$aData['nRadio'] 		= $nRadio;
			$aData['active'] 		= $nActive;

			$oDBSalary->getReportByRegions($aData,$oResponse);
			
			$oResponse->printResponse();
		}
		
		public function deleteFilter() {
			$nID = Params::get('schemes','');
			
			$oDBAdminSalaryTotalFilters = new DBAdminSalaryTotalFilters();
			$oDBAdminSalaryTotalFilters->delete($nID);
		}
	}
?>

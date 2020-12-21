<?php

	class ApiViewPersonLeaves
	{
		// remote method
		public function init( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBFirms 					= new DBFirms();
			$oDBOffices 				= new DBOffices();
			$oDBObjects 				= new DBObjects();
			$oDBLeaves 					= new DBLeaves();
			$oDBFilters 				= new DBFilters();
			$oDBFiltersVisibleFields 	= new DBFiltersVisibleFields();
			
			//Paging
			$aPager = array();
			$aPager = Params::get( "pagingandsorting", array() );
			//End Paging
			
			//Initialize Controls
			$aNullElement1 = array( "0" => array( "id" => "0", "label" => "-- Изберете --" ) );
			$aNullElement2 = array( "0" => array( "id" => "0", "label" => "-- Първо изберете Фирма --" ) );
			$aNullElement3 = array( "0" => array( "id" => "0", "label" => "-- Първо изберете Регион --" ) );
			
			//Set Filters
			$nIDPerson = ( int ) isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			$aFilters = $oDBFilters->getFiltersByReportClassForFlexCombo( "ViewPersonLeaves", $nIDPerson );
			$aFilters = array_merge( $aNullElement1, $aFilters );
			
			$nIDDefaultFilter = 0;
			foreach( $aFilters as $aValue )
			{
				if( $aValue['is_default'] == 1 )$nIDDefaultFilter = $aValue['id'];
			}
			
			$oResponse->SetFlexControl( "nIDFilter" );
			$oResponse->SetFlexControlDefaultValue( "nIDFilter", "id", isset( $aParams['nIDFilter'] ) ? $aParams['nIDFilter'] : $nIDDefaultFilter );
			//End Set Filters
			
			$aFirms = $oDBFirms->getFirmsForFlexCombo();
			$aFirms = array_merge( $aNullElement1, $aFirms );
			
			$oResponse->SetFlexControl( "nIDFirm" );
			$oResponse->SetFlexControlDefaultValue( "nIDFirm", "id", isset( $aParams['nIDFirm'] ) ? $aParams['nIDFirm'] : 0 );
			
			if( isset( $aParams['nIDFirm'] ) && !empty( $aParams['nIDFirm'] ) )
			{
				$aOffices = $oDBOffices->getOfficesByFirmForFlexCombo( $aParams['nIDFirm'] );
				$aOffices = array_merge( $aNullElement1, $aOffices );
			}
			else
			{
				$aOffices = $aNullElement2;
			}
			
			if( isset( $aParams['nIDOffice'] ) && !empty( $aParams['nIDOffice'] ) )
			{
				$aObjects = $oDBObjects->getFoObjectsByOfficeForFlexCombo( $aParams['nIDOffice'] );
				$aObjects = array_merge( $aNullElement1, $aObjects );
			}
			else
			{
				$aObjects = $aNullElement3;
			}
			
			$oResponse->SetFlexControl( "nIDOffice" );
			$oResponse->SetFlexControlDefaultValue( "nIDOffice", "id", isset( $aParams['nIDOffice'] ) ? $aParams['nIDOffice'] : 0 );
			
			$oResponse->SetFlexControl( "nIDObject" );
			$oResponse->SetFlexControlDefaultValue( "nIDObject", "id", isset( $aParams['nIDObject'] ) ? $aParams['nIDObject'] : 0 );
			
			if( !isset( $aParams['nIDOffice'] ) )$aParams['nIDOffice'] = 0;
			if( !isset( $aParams['nIDFirm'] ) )$aParams['nIDFirm'] = 0;
			if( !isset( $aParams['nIDObject'] ) )$aParams['nIDObject'] = 0;
			if( !isset( $aParams['sDateFrom'] ) )$aParams['sDateFrom'] = date( "Y-m-d" );
			if( !isset( $aParams['sDateTo'] ) )$aParams['sDateTo'] = date( "Y-m-d" );
			
			$aDates = array();
			$aDates['sDateFrom'] = $aParams['sDateFrom'];
			$aDates['sDateTo'] = $aParams['sDateTo'];
			//End Initialize Controls
			
			//Visible Fields
			$aFilterFields = $oDBFiltersVisibleFields->getFieldsByIDFilter( isset( $aParams['nIDFilter'] ) ? $aParams['nIDFilter'] : $nIDDefaultFilter );
			
			$aVisible = array();
			$nDefaultValue = ( ( isset( $aParams['nIDFilter'] ) && !empty( $aParams['nIDFilter'] ) ) || !empty( $nIDDefaultFilter ) ) ? 0 : 1;
			$aVisible['date'] 					= $nDefaultValue;
			$aVisible['person_name'] 			= $nDefaultValue;
			$aVisible['person_position'] 		= $nDefaultValue;
			$aVisible['firm'] 					= $nDefaultValue;
			$aVisible['office'] 				= $nDefaultValue;
			$aVisible['object'] 				= $nDefaultValue;
			$aVisible['leave_type'] 			= $nDefaultValue;
			$aVisible['leave_from'] 			= $nDefaultValue;
			$aVisible['leave_to'] 				= $nDefaultValue;
			$aVisible['application_days'] 		= $nDefaultValue;
			$aVisible['code_leave_name'] 		= $nDefaultValue;
			$aVisible['created_user'] 			= $nDefaultValue;
			$aVisible['status'] 				= $nDefaultValue;
			$aVisible['time_confirm'] 			= $nDefaultValue;
			$aVisible['res_leave_from'] 		= $nDefaultValue;
			$aVisible['res_leave_to'] 			= $nDefaultValue;
			$aVisible['res_application_days'] 	= $nDefaultValue;
			$aVisible['person_confirm'] 		= $nDefaultValue;
			
			foreach( $aFilterFields as $nID => $sFilterField )
			{
				$aVisible[$sFilterField] = "1";
			}
			//End Visible Fields
			
			//Get Result
			$aData = array();
			$aData = $oDBLeaves->getResultExtended( $aParams, $aPager );
			//End Get Result
			
			$oResponse->SetFlexVar( "aFirms", $aFirms );
			$oResponse->SetFlexVar( "aOffices", $aOffices );
			$oResponse->SetFlexVar( "aObjects", $aObjects );
			$oResponse->SetFlexVar( "aFilters", $aFilters );
			$oResponse->setFlexVar( "aDates", $aDates );
			$oResponse->setFlexVar( "aPaging", $aPager );
			$oResponse->setFlexVar( "aData", $aData );
			$oResponse->SetFlexVar( "aVisible", $aVisible );
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBLeaves 					= new DBLeaves();
			$oDBFiltersVisibleFields 	= new DBFiltersVisibleFields();
			
			$nIDFilter = isset( $aParams['nIDFilter'] ) ? $aParams['nIDFilter'] : 0;
			
			//Visible Fields
			$aFilterFields = $oDBFiltersVisibleFields->getFieldsByIDFilter( $nIDFilter );
			
			$aVisible = array();
			$nDefaultValue = ( isset( $aParams['nIDFilter'] ) && !empty( $aParams['nIDFilter'] ) ) ? 0 : 1;
			$aVisible['date'] 					= $nDefaultValue;
			$aVisible['person_name'] 			= $nDefaultValue;
			$aVisible['person_position'] 		= $nDefaultValue;
			$aVisible['firm'] 					= $nDefaultValue;
			$aVisible['office'] 				= $nDefaultValue;
			$aVisible['object'] 				= $nDefaultValue;
			$aVisible['leave_type'] 			= $nDefaultValue;
			$aVisible['leave_from'] 			= $nDefaultValue;
			$aVisible['leave_to'] 				= $nDefaultValue;
			$aVisible['application_days'] 		= $nDefaultValue;
			$aVisible['code_leave_name'] 		= $nDefaultValue;
			$aVisible['created_user'] 			= $nDefaultValue;
			$aVisible['status'] 				= $nDefaultValue;
			$aVisible['time_confirm'] 			= $nDefaultValue;
			$aVisible['res_leave_from'] 		= $nDefaultValue;
			$aVisible['res_leave_to'] 			= $nDefaultValue;
			$aVisible['res_application_days'] 	= $nDefaultValue;
			$aVisible['person_confirm'] 		= $nDefaultValue;
			
			foreach( $aFilterFields as $nID => $sFilterField )
			{
				$aVisible[$sFilterField] = "1";
			}
			//End Visible Fields
			
			$aPaging = array();
			$aPaging['sortCol'] = $aParams['nSortCol'];
			$aPaging['sortType'] = $aParams['nSortType'];
			
			if( $aParams['api_action'] == "export_to_pdf" || $aParams['api_action'] == "export_to_xls" )
			{
				//PDF Data
				$aData = array();
				$aData = $oDBLeaves->getResultExtended( $aParams, $aPaging );
				
				$oResponse->setField( "leave_num", "Номер" );
				
				if( isset( $aVisible['date'] ) && !empty( $aVisible['date'] ) )
					$oResponse->setField( "date", "Дата" );
				
				if( isset( $aVisible['person_name'] ) && !empty( $aVisible['person_name'] ) )
					$oResponse->setField( "person_name", "Служител" );
				
				if( isset( $aVisible['person_position'] ) && !empty( $aVisible['person_position'] ) )
					$oResponse->setField( "person_position", "Длъжност" );
				
				if( isset( $aVisible['firm'] ) && !empty( $aVisible['firm'] ) )
					$oResponse->setField( "firm", "Фирма" );
				
				if( isset( $aVisible['office'] ) && !empty( $aVisible['office'] ) )
					$oResponse->setField( "office", "Регион" );
				
				if( isset( $aVisible['object'] ) && !empty( $aVisible['object'] ) )
					$oResponse->setField( "object",	"Обект" );
				
				if( isset( $aVisible['leave_type'] ) && !empty( $aVisible['leave_type'] ) )
					$oResponse->setField( "leave_type",	"Тип" );
				
				if( isset( $aVisible['id'] ) && !empty( $aVisible['id'] ) )
					$oResponse->setField( "leave_from",	"Дата От" );
				
				if( isset( $aVisible['leave_to'] ) && !empty( $aVisible['leave_to'] ) )
					$oResponse->setField( "leave_to", "Дата До" );
				
				if( isset( $aVisible['application_days'] ) && !empty( $aVisible['application_days'] ) )
					$oResponse->setField( "application_days", "Дни" );
				
				if( isset( $aVisible['code_leave_name'] ) && !empty( $aVisible['code_leave_name'] ) )
					$oResponse->setField( "code_leave_name", "Чл. от КТ" );
				
				if( isset( $aVisible['created_user'] ) && !empty( $aVisible['created_user'] ) )
					$oResponse->setField( "created_user", "Въвел" );
				
				if( isset( $aVisible['status'] ) && !empty( $aVisible['status'] ) )
					$oResponse->setField( "status",	"Статус" );
				
				if( isset( $aVisible['time_confirm'] ) && !empty( $aVisible['time_confirm'] ) )
					$oResponse->setField( "time_confirm", "Дата на Потв." );
				
				if( isset( $aVisible['res_leave_from'] ) && !empty( $aVisible['res_leave_from'] ) )
					$oResponse->setField( "res_leave_from",	"Дата От" );
				
				if( isset( $aVisible['res_leave_to'] ) && !empty( $aVisible['res_leave_to'] ) )
					$oResponse->setField( "res_leave_to", "Дата До" );
				
				if( isset( $aVisible['res_application_days'] ) && !empty( $aVisible['res_application_days'] ) )
					$oResponse->setField( "res_application_days", "Дни" );
				
				if( isset( $aVisible['person_confirm'] ) && !empty( $aVisible['person_confirm'] ) )
					$oResponse->setField( "person_confirm",	"Потвърдил" );
				
				$oResponse->setData( $aData );
				//End PDF Data
			}
			
			$oResponse->printResponse( "Молби за Отпуск", "person_leaves", false );
		}
		
		// remote method
		public function getOffices( DBResponse $oResponse )
		{
			$nIDFirm = Params::get( "nIDFirm", 0 );
			
			$oDBOffices = new DBOffices();
			
			//Initialize Controls
			$aNullElement1 = array( "0" => array( "id" => "0", "label" => "-- Изберете --" ) );
			$aNullElement2 = array( "0" => array( "id" => "0", "label" => "-- Първо изберете Фирма --" ) );
			$aNullElement3 = array( "0" => array( "id" => "0", "label" => "-- Първо изберете Регион --" ) );
			
			if( !empty( $nIDFirm ) )
			{
				$aOffices = $oDBOffices->getOfficesByFirmForFlexCombo( $nIDFirm );
				$aOffices = array_merge( $aNullElement1, $aOffices );
			}
			else
			{
				$aOffices = $aNullElement2;
			}
			
			$oResponse->SetFlexControl( "nIDOffice" );
			$oResponse->SetFlexControlDefaultValue( "nIDOffice", "id", "0" );
			
			$aObjects = $aNullElement3;
			
			$oResponse->SetFlexControl( "nIDObject" );
			$oResponse->SetFlexControlDefaultValue( "nIDObject", "id", "0" );
			//End Initialize Controls
			
			$oResponse->SetFlexVar( "aOffices", $aOffices );
			$oResponse->SetFlexVar( "aObjects", $aObjects );
			
			$oResponse->printResponse();
		}
		
		// remote method
		public function getObjects( DBResponse $oResponse )
		{
			$nIDOffice = Params::get( "nIDOffice", 0 );
			
			$oDBObjects = new DBObjects();
			
			//Initialize Controls
			$aNullElement1 = array( "0" => array( "id" => "0", "label" => "-- Изберете --" ) );
			$aNullElement2 = array( "0" => array( "id" => "0", "label" => "-- Първо изберете Фирма --" ) );
			$aNullElement3 = array( "0" => array( "id" => "0", "label" => "-- Първо изберете Регион --" ) );
			
			if( !empty( $nIDOffice ) )
			{
				$aObjects = $oDBObjects->getFoObjectsByOfficeForFlexCombo( $nIDOffice );
				$aObjects = array_merge( $aNullElement1, $aObjects );
			}
			else
			{
				$aObjects = $aNullElement3;
			}
			
			$oResponse->SetFlexControl( "nIDObject" );
			$oResponse->SetFlexControlDefaultValue( "nIDObject", "id", "0" );
			//End Initialize Controls
			
			$oResponse->SetFlexVar( "aObjects", $aObjects );
			
			$oResponse->printResponse();
		}
		
		// remote method
		public function delFilter( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$nIDFilter = ( int ) isset( $aParams['nIDFilter'] ) ? $aParams['nIDFilter'] : 0;
			
			if( empty( $nIDFilter ) )
			{
				$oResponse->setAlert( "Изберете филтъра, който искате да изтриете." );
				$oResponse->printResponse();
				return;
			}
			
			$oDBFilters 				= new DBFilters();
			$oDBFiltersVisibleFields 	= new DBFiltersVisibleFields();
			
			$oDBFiltersVisibleFields->delByIDFilter( $nIDFilter );
			$oDBFilters->delete( $nIDFilter );
			
			Params::set( "nIDFilter", 0 );
			
			$this->init( $oResponse );
		}
		
		// remote method
		public function groupConfirm( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$aRecord = parseObjectToArray( $aValue );
			$sErrors = "";
			
			foreach( $aParams['result_data'] as $nKey => $aValue )
			{
				$aRecord = parseObjectToArray( $aValue );
				
				if( $aRecord['is_to_confirm'] && $aRecord['id'] != 0 && $aRecord['is_confirm'] == 0 )
				{
					$aConfirmParams = array();
					
					$aConfirmParams['nID'] 						= $aRecord['id'];
					$aConfirmParams['nIDPerson'] 				= $aRecord['id_person'];
					$aConfirmParams['nApplicationDaysOffer'] 	= $aRecord['application_days'];
					$aConfirmParams['sLeaveType']				= $aRecord['raw_leave_types'];
					$aConfirmParams['sLeaveFromOffer'] 			= $aRecord['raw_leave_from'];
					$aConfirmParams['nIDPersonSubstitute']		= $aRecord['id_person_substitute'];
					$aConfirmParams['nIDCodeLeave']				= $aRecord['id_code_leave'];
					
					$aConfirmParams['nIsAllowed']				= true;
					$aConfirmParams['nApplicationDays']			= $aConfirmParams['nApplicationDaysOffer'];
					$aConfirmParams['sLeaveFrom']				= $aConfirmParams['sLeaveFromOffer'];
					
					$sResult = $this->confirm( $aConfirmParams );
					if( !empty( $sResult ) )$sErrors .= "\n {$sResult}";
				}
			}
			
			if( !empty( $sErrors ) )
			{
				$oResponse->setAlert( "Следните молби не бяха одобрени: \n {$sErrors}" );
			}
			
			$this->init( $oResponse );
		}
		
		public function confirm( $aParams )
		{
			global $db_personnel;
			
			$oDBPersonLeaves 	= new DBBase2( $db_personnel, "person_leaves" );
			$oDBLeaves			= new DBLeaves();
			$oDBObjectPersonnel = new DBObjectPersonnel();
			$oDBObjectDuty 		= new DBObjectDuty();
			$oDBSalaryEarning 	= new DBSalaryEarning();
			$oDBSalary			= new DBSalary();
			$oDBPersonnel 		= new DBPersonnel();
			$oDBHolidays		= new DBHolidays();
			
			$nID 		= isset( $aParams['nID'] )			? $aParams['nID'] 		: 0;
			$nIDPerson 	= isset( $aParams['nIDPerson'] ) 	? $aParams['nIDPerson'] : 0;
			
			$aMonthStat = array();	//Разбивка на работните дни по месеци.
			$bIsSubstituteNeeded = $oDBPersonnel->isSubstituteNeeded( $nIDPerson );
			$nRemainLeaveDays = $oDBLeaves->getRemainingLeaveDays( substr( $aParams['sLeaveFromOffer'], 0, 4 ), $nIDPerson, $nID );
			
			//Validation
			if( empty( $aParams['nApplicationDaysOffer'] ) )return "{$nID} : Не е въведен брой работни дни!";
			if( !is_numeric( $aParams['nApplicationDaysOffer'] ) || $aParams['nApplicationDaysOffer'] < 1 )
			{
				return "{$nID} : Невалидна стойност за брой дни!";
			}
			if( empty( $aParams['sLeaveFromOffer'] ) )return "{$nID} : Невалидна дата!";
			if( $bIsSubstituteNeeded )
			{
				if( empty( $aParams['nIDPersonSubstitute'] ) )return "{$nID} : Не е въведен заместник!";
			}
			if( empty( $aParams['nIDCodeLeave'] ) )return "{$nID} : Не е въведен чл. от КТ!";
			
			if( $aParams['nIsAllowed'] )
			{
				if( empty( $aParams['nApplicationDays'] ) )return "{$nID} : Не е въведен брой работни дни!";
				if( !is_numeric( $aParams['nApplicationDays'] ) || $aParams['nApplicationDays'] < 1 )
				{
					return "{$nID} : Невалидна стойност за брой дни!";
				}
				if( empty( $aParams['sLeaveFrom'] ) )return "{$nID} : Невалидна дата!";
			}
			
			if( $aParams['sLeaveType'] == "due" )
			{
				if( $aParams['nApplicationDaysOffer'] > $nRemainLeaveDays )
				{
					return "{$nID} : Въведения брой дни е над лимита за годината!";
				}
				
				if( $aParams['nApplicationDays'] > $nRemainLeaveDays )
				{
					return "{$nID} : Въведения брой дни е над лимита за годината!";
				}
			}
			//End Validation
			
			$aData = array();
			$aData['id'] 						= $nID;
			$aData['id_person'] 				= $nIDPerson;
			$aData['type'] 						= "application";
			$aData['leave_types'] 				= $aParams['sLeaveType'];
			$aData['leave_from'] 				= $oDBHolidays->getNextWorkday( $aParams['sLeaveFromOffer'] ) . " 00:00:00";
			$aData['leave_to'] 					= $this->calcEndDate( $aData['leave_from'], $aParams['nApplicationDaysOffer'] ) . " 23:59:59";
			if( empty( $nID ) )
			{
				$aData['year'] 					= substr( $aData['leave_from'], 0, 4 );
			}
			$aData['application_days_offer'] 	= $aParams['nApplicationDaysOffer'];
			$aData['id_person_substitute'] 		= $aParams['nIDPersonSubstitute'];
			$aData['id_code_leave'] 			= $aParams['nIDCodeLeave'];
			
			if( $aParams['nIsAllowed'] )
			{
				$aData['application_days'] 		= $aParams['nApplicationDays'];
				$aData['res_leave_from'] 		= $oDBHolidays->getNextWorkday( $aParams['sLeaveFrom'] ) . " 00:00:00";
				$aData['res_leave_to'] 			= $this->calcEndDate( $aData['res_leave_from'], $aParams['nApplicationDays'], $aMonthStat ) . " 23:59:59";
			}
			else
			{
				$aData['application_days'] 		= 0;
				$aData['res_leave_from'] 		= "0000-00-00 00:00:00";
				$aData['res_leave_to'] 			= "0000-00-00 00:00:00";
			}
			$aData['is_confirm'] 				= 1;
			$aData['confirm_user'] 				= $_SESSION['userdata']['id_person'];
			$aData['confirm_time'] 				= date( "Y-m-d H:i:s" );
			
			if( empty( $nID ) )
			{
				$aData['created_user'] = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
				
				//Get a Number
				$aIDOffice = $oDBPersonnel->getPersonnelOffice( $nIDPerson );
				$nIDOffice = ( !empty( $aIDOffice ) && isset( $aIDOffice['id_office'] ) ) ? $aIDOffice['id_office'] : 0;
				$aIDFirm = $oDBOffices->getFirmByIDOffice( $nIDOffice );
				$nIDFirm = ( !empty( $aIDFirm ) && isset( $aIDFirm['id_firm'] ) ) ? $aIDFirm['id_firm'] : 0;
				
				$oDBPersonLeavesNumbers = new DBPersonLeavesNumbers();
				$nLeaveNumber = $oDBPersonLeavesNumbers->getNumberByFirm( $nIDFirm, $aData['year'] );
				//End Get a Number
				$aData['leave_num']	= $nLeaveNumber;
			}
			
			//Check Overlap
			if( $aParams['nIsAllowed'] )
			{
				$nIsOverlapped = $oDBLeaves->isThereApplication( $nIDPerson, $aData['res_leave_from'], $aData['res_leave_to'], $nID );
				if( $nIsOverlapped )return "{$nID} : Съществува молба, чиито дати се застъпват с въведените!";
			}
			//End Check Overlap
			
			//Fix Schedules
			$aObjectsWithShifts = $oDBObjectDuty->getObjectsForPersonShifts( $nIDPerson, $aData['res_leave_from'], $aData['res_leave_to'] );
			
			if( !empty( $aObjectsWithShifts ) )
			{
				$sErrorMessage = "Служителя има смени в следните обекти:";
				foreach( $aObjectsWithShifts as $aObjectsNames )
				{
					$sErrorMessage .= "\n " . $aObjectsNames['name'];
				}
				
				return "{$nID} : \n {$sErrorMessage} \n";
			}
			
			if( !empty( $nID ) )
			{
				$aSavedLeave = $oDBLeaves->getApplication( $nID );
				if( !empty( $aSavedLeave ) && isset( $aSavedLeave['is_confirm'] ) && isset( $aSavedLeave['application_days'] ) )
				{
					if( $aSavedLeave['is_confirm'] == 1 && $aSavedLeave['application_days'] != 0 )
					{
						$sSavedLeaveFrom = $aSavedLeave['res_leave_from'];
						$sSavedLeaveTo = $aSavedLeave['res_leave_to'];
						$nApplicationDays = $aSavedLeave['application_days'];
						$aSavedMonthStat = array();
						
						$this->calcEndDate( $sSavedLeaveFrom, $nApplicationDays, $aSavedMonthStat );
						
						$nResult = $oDBObjectDuty->clearPersonLeaveForDays( $nIDPerson, $sSavedLeaveFrom, $sSavedLeaveTo, $aSavedMonthStat );
						if( $nResult != DBAPI_ERR_SUCCESS )
						{
							return "{$nID} : Грешка при подновяване на графика!";
						}
						
						$nResult = $oDBSalary->deleteSalaryRowsByApplication( $nID );
						if( $nResult != DBAPI_ERR_SUCCESS )
						{
							return "{$nID} : Грешка при коригиране на работна заплата!";
						}
					}
				}
			}
			//End Fix Schedules
			
			$nResult = $oDBPersonLeaves->update( $aData );
			if( $nResult != DBAPI_ERR_SUCCESS )
			{
				return "{$nID} : Грешка при запазване на данните!";
			}
			
			if( $aParams['nIsAllowed'] )
			{
				//Fix Schedule
				$nResult = $oDBObjectDuty->putPersonLeaveForDays( $nIDPerson, $aData['res_leave_from'], $aData['application_days'] );
				if( $nResult != DBAPI_ERR_SUCCESS )
				{
					return "{$nID} : Грешка при запазване на информацията по графика!";
				}
				//End Fix Schedule
				
				//Add Salary Earning
				$aCodeLeaveEarning = $oDBSalaryEarning->getLeaveEarning( $aParams['sLeaveType'] );
				
				$aPersonOffice = $oDBPersonnel->getPersonnelOffice( $nIDPerson );
				
				if( !empty( $aMonthStat ) )
				{
					foreach( $aMonthStat as $nYearMonth => $nCount )
					{
						$aDataSalary = array();
						$aDataSalary['id'] 				= 0;
						$aDataSalary['id_person'] 		= $nIDPerson;
						$aDataSalary['id_office'] 		= ( !empty( $aPersonOffice ) && isset( $aPersonOffice['id_office'] ) ) ? $aPersonOffice['id_office'] : 0;
						$aDataSalary['month'] 			= $nYearMonth;
						$aDataSalary['is_earning'] 		= 1;
						$aDataSalary['sum'] 			= 0;
						$aDataSalary['count'] 			= $nCount;
						$aDataSalary['id_application'] 	= $aData['id'];
						
						$aDataSalary['code'] 		= isset( $aCodeLeaveEarning['code'] ) ? $aCodeLeaveEarning['code'] : "";
						$aDataSalary['description'] = isset( $aCodeLeaveEarning['name'] ) ? $aCodeLeaveEarning['name'] : "";
						
						$nResult = $oDBSalary->update( $aDataSalary );
						if( $nResult != DBAPI_ERR_SUCCESS )
						{
							return "{$nID} : Грешка при нанасяне на наработки!";
						}
					}
				}
				//End Add Salary Earning
			}
			
			return "";
		}
		
		public function calcEndDate( $sStartDate, $nDays, &$aMonthStat = array() )
		{
			$oDBHolidays = new DBHolidays();
			
			//Initial Data
			$aStartDate = explode( "-", $sStartDate );
			if( !isset( $aStartDate[0] ) || !isset( $aStartDate[1] ) || !isset( $aStartDate[2] ) )
			{
				return "0000-00-00";
			}
			else
			{
				$nYear 	= ( int ) $aStartDate[0];
				$nMonth = ( int ) $aStartDate[1];
				$nDay 	= ( int ) $aStartDate[2];
			}
			
			$aMonthStat = array();
			
			$nDaysInMonth = ( int ) date( "t", mktime( 0, 0, 0, $nMonth, $nDay, $nYear ) );
			$sYearMonthKey = $nYear . ( strlen( $nMonth ) < 2 ? ( "0" . $nMonth ) : $nMonth );
			$aMonthStat[$sYearMonthKey] = 0;
			//End Initial Data
			
			$nIteration = 0;
			do
			{
				$nMyWeekday = ( int ) date( "w", mktime( 0, 0, 0, $nMonth, $nDay, $nYear ) );
				
				if( $nMyWeekday == 0 || $nMyWeekday == 6 )
				{
					if( $oDBHolidays->isWorkday( $nDay, $nMonth, $nYear ) )
					{
						$nIteration++;
						$aMonthStat[$sYearMonthKey]++;
					}
				}
				else
				{
					if( !$oDBHolidays->isHoliday( $nDay, $nMonth ) && !$oDBHolidays->isRestday( $nDay, $nMonth, $nYear ) )
					{
						$nIteration++;
						$aMonthStat[$sYearMonthKey]++;
					}
				}
				
				//Progress Date
				if( $nIteration < $nDays )
				{
					$nDay++;
					if( $nDay > $nDaysInMonth )
					{
						$nDay = 1;
						$nMonth++;
						if( $nMonth > 12 ) { $nMonth = 1; $nYear++; }
						
						$nDaysInMonth = ( int ) date( "t", mktime( 0, 0, 0, $nMonth, $nDay, $nYear ) );
						$sYearMonthKey = $nYear . ( strlen( $nMonth ) < 2 ? ( "0" . $nMonth ) : $nMonth );
						$aMonthStat[$sYearMonthKey] = 0;
					}
				}
				//End Progress Date
			}
			while( $nIteration < $nDays );
			
			return $nYear . "-" . ( strlen( $nMonth ) < 2 ? ( "0" . $nMonth ) : $nMonth ) . "-" . ( strlen( $nDay ) < 2 ? ( "0" . $nDay ) : $nDay );
		}		
	}

?>
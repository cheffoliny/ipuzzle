<?php

	require_once( "pdf/pdf_person_leave.php" );
	
	class ApiSetupPersonLeave
	{
		// remote method
		public function init( DBResponse $oResponse )
		{
			global $db_personnel;
			$aParams = Params::getAll();
			
			$oDBPersonnel 		= new DBPersonnel();
			$oDBCodeLeave 		= new DBCodeLeave();
			$oDBHolidays		= new DBHolidays();
			$oDBPersonLeaves 	= new DBBase2( $db_personnel, "person_leaves" );
			
			$nID 		= Params::get( 'id' );
			$nIDPerson 	= Params::get( 'id_person' );
			
			$oResponse->SetHiddenParam( "nID", 			$nID 		);
			$oResponse->SetHiddenParam( "nIDPerson", 	$nIDPerson 	);
			
			//Initial Data
			$bRightResolute = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
			{
				if( in_array( 'setup_person_leave_resolution', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$bRightResolute = true;
				}
			}
			
			$bRightChange = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
			{
				if( in_array( 'setup_person_leave_change', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$bRightChange = true;
				}
			}
			
			if( empty( $nIDPerson ) )throw new Exception( "Служителя не е намерен!", DBAPI_ERR_INVALID_PARAM );
			
			$aPerson = $oDBPersonnel->getRecord( $nIDPerson );
			if( empty( $aPerson ) )
			{
				throw new Exception( "Служителя не е намерен!", DBAPI_ERR_INVALID_PARAM );
			}
			
			//Default Value
			$aNullElement = array( "0" => array( "id" => "0", "label" => "--- Изберете ---" ) );
			//End Default Value
			
			$sPersonNames 		= $aPerson['fname'] . " " . $aPerson['mname'] . " " . $aPerson['lname'];
			$aPersonSameOffice 	= $oDBPersonnel->getPersonnelsByIDOffice4( $aPerson['id_office'], $nIDPerson );
			$aPersonSameOffice 	= array_merge( $aNullElement, $aPersonSameOffice );
			$aCodesLeave		= $oDBCodeLeave->getCodesLeave();
			$aCodesLeave		= array_merge( $aNullElement, $aCodesLeave );
			
			$aData = array();
			$aData['id'] 				= $nID;
			$aData['id_person'] 		= $nIDPerson;
			$aData['sPersonName'] 		= $sPersonNames;
			$aData['bRightResolute'] 	= $bRightResolute;
			$aData['bRightChange'] 		= $bRightChange;
			
			if( empty( $nID ) )
			{
				$aData['nLeaveNum'] = 0;
				$aData['nApplicationDaysOffer'] = 1;
				$aData['sLeaveFromOffer'] = $oDBHolidays->getNextWorkday();
				$aData['nApplicationDays'] = 0;
				$aData['sLeaveFrom'] = "0000-00-00 00:00:00";
				$aData['nIsConfirm'] = 0;
				$aData['nIsAllowed'] = 0;
				$aData['sApplicationDate'] = "0000-00-00 00:00:00";
				$aData['sApplicationConDate'] = "0000-00-00 00:00:00";
				
				$oResponse->SetFlexControl( "sLeaveType" );
				$oResponse->SetFlexControlDefaultValue( "sLeaveType", "id", "due" );
				$oResponse->SetFlexControl( "nIDPersonSubstitute" );
				$oResponse->SetFlexControlDefaultValue( "nIDPersonSubstitute", "id", "0" );
				$oResponse->SetFlexControl( "nIDCodeLeave" );
				$oResponse->SetFlexControlDefaultValue( "nIDCodeLeave", "id", "0" );
			}
			else
			{
				$aPersonLeave = $oDBPersonLeaves->getRecord( $nID );
				
				if( empty( $aPersonLeave ) )
				{
					throw new Exception( "Записът не съществува!", DBAPI_ERR_INVALID_PARAM );
				}
				
				$aData['nLeaveNum'] = $aPersonLeave['leave_num'];
				$aData['nApplicationDaysOffer'] = $aPersonLeave['application_days_offer'];
				$aData['sLeaveFromOffer'] = substr( $aPersonLeave['leave_from'], 0, 10 );
				$aData['nApplicationDays'] = $aPersonLeave['application_days'];
				$aData['sLeaveFrom'] = $aPersonLeave['res_leave_from'];
				$aData['nIsConfirm'] = $aPersonLeave['is_confirm'];
				$aData['nIsAllowed'] = $aPersonLeave['application_days'] != 0 ? 1 : 0;
				$aData['sApplicationDate'] = $aPersonLeave['date'];
				$aData['sApplicationConDate'] = $aPersonLeave['confirm_time'];
				
				$oResponse->SetFlexControl( "sLeaveType" );
				$oResponse->SetFlexControlDefaultValue( "sLeaveType", "id", $aPersonLeave['leave_types'] );
				$oResponse->SetFlexControl( "nIDPersonSubstitute" );
				$oResponse->SetFlexControlDefaultValue( "nIDPersonSubstitute", "id", $aPersonLeave['id_person_substitute'] );
				$oResponse->SetFlexControl( "nIDCodeLeave" );
				$oResponse->SetFlexControlDefaultValue( "nIDCodeLeave", "id", $aPersonLeave['id_code_leave'] );
			}
			//End Initial Data
			
			$oResponse->SetFlexVar( "aData", 				$aData 				);
			$oResponse->SetFlexVar( "aPersonSameOffice", 	$aPersonSameOffice 	);
			$oResponse->SetFlexVar( "aCodesLeave",			$aCodesLeave 		);
			
			$oResponse->printResponse();
		}
		
		// remote method
		public function save( DBResponse $oResponse )
		{
			global $db_personnel;
			$aParams = Params::getAll();
			
			$oDBPersonLeaves 	= new DBBase2( $db_personnel, "person_leaves" );
			$oDBLeaves			= new DBLeaves();
			$oDBPersonnel		= new DBPersonnel();
			$oDBHolidays		= new DBHolidays();
			$oDBObjectDuty 		= new DBObjectDuty();
			$oDBOffices			= new DBOffices();
			
			$nID 		= isset( $aParams['hiddenParams']->nID ) 		? $aParams['hiddenParams']->nID 		: 0;
			$nIDPerson 	= isset( $aParams['hiddenParams']->nIDPerson ) 	? $aParams['hiddenParams']->nIDPerson 	: 0;
			
			$bIsSubstituteNeeded = $oDBPersonnel->isSubstituteNeeded( $nIDPerson );
			$nRemainLeaveDays = $oDBLeaves->getRemainingLeaveDays( substr( $aParams['sLeaveFromOffer'], 0, 4 ), $nIDPerson );
			
			//Validation
			if( empty( $aParams['nApplicationDaysOffer'] ) )throw new Exception( "Моля, въведете брой работни дни!", DBAPI_ERR_INVALID_PARAM );
			if( !is_numeric( $aParams['nApplicationDaysOffer'] ) || $aParams['nApplicationDaysOffer'] < 1 )
			{
				throw new Exception( "Невалидна стойност за брой дни!", DBAPI_ERR_SUCCESS );
			}
			if( empty( $aParams['sLeaveFromOffer'] ) )throw new Exception( "Невалидна дата!", DBAPI_ERR_INVALID_PARAM );
			if( $bIsSubstituteNeeded )
			{
				if( empty( $aParams['nIDPersonSubstitute'] ) )throw new Exception( "Моля, въведете заместник!", DBAPI_ERR_INVALID_PARAM );
			}
			if( empty( $aParams['nIDCodeLeave'] ) )throw new Exception( "Моля, въведете чл. от КТ!", DBAPI_ERR_INVALID_PARAM );

			if( $aParams['sLeaveType'] == "due" )
			{
				if( $aParams['nApplicationDaysOffer'] > $nRemainLeaveDays )
				{
					throw new Exception( "Въведения брой дни е над лимита за годината!", DBAPI_ERR_INVALID_PARAM );
				}
			}
			//End Validation
			
			$aData = array();
			$aData['id'] 						= $nID;
			$aData['id_person'] 				= $nIDPerson;
			$aData['type'] 						= "application";
			$aData['date'] 						= date( "Y-m-d H:i:s" );
			$aData['leave_types'] 				= $aParams['sLeaveType'];
			$aData['leave_from'] 				= $oDBHolidays->getNextWorkday( $aParams['sLeaveFromOffer'] ) . " 00:00:00";
			$aData['leave_to'] 					= $this->calcEndDate( $aData['leave_from'], $aParams['nApplicationDaysOffer'] ) . " 23:59:59";
			$aData['year'] 						= substr( $aData['leave_from'], 0, 4 );
			$aData['application_days_offer'] 	= $aParams['nApplicationDaysOffer'];
			$aData['id_person_substitute'] 		= $aParams['nIDPersonSubstitute'];
			$aData['id_code_leave'] 			= $aParams['nIDCodeLeave'];
			
			$aData['application_days'] 			= 0;
			$aData['res_leave_from'] 			= "0000-00-00 00:00:00";
			$aData['res_leave_to'] 				= "0000-00-00 00:00:00";
			$aData['is_confirm'] 				= 0;
			$aData['confirm_user'] 				= 0;
			$aData['confirm_time'] 				= "0000-00-00 00:00:00";
			
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
			$nIsOverlapped = $oDBLeaves->isThereApplication( $nIDPerson, $aData['leave_from'], $aData['leave_to'], $nID );
			if( $nIsOverlapped )throw new Exception( "Съществува молба, чиито дати се застъпват с въведените!" );
			//End Check Overlap
			
			//Fix Schedules
			$aObjectsWithShifts = $oDBObjectDuty->getObjectsForPersonShifts( $nIDPerson, $aData['leave_from'], $aData['leave_to'] );
			
			if( !empty( $aObjectsWithShifts ) )
			{
				$sErrorMessage = "Служителя има смени в следните обекти:";
				foreach( $aObjectsWithShifts as $aObjectsNames )
				{
					$sErrorMessage .= "\n " . $aObjectsNames['name'];
				}
				
				throw new Exception( $sErrorMessage, DBAPI_ERR_INVALID_PARAM );
			}
			//End Fix Schedules
			
			$nResult = $oDBPersonLeaves->update( $aData );
			if( $nResult != DBAPI_ERR_SUCCESS )
			{
				throw new Exception( "Грешка при запазване на данните!", $nResult );
			}
			
			$nID = $aData['id'];
			
			Params::set( 'id', 			$nID 		);
			Params::set( 'id_person',	$nIDPerson 	);
			
			$this->init( $oResponse );
		}
		
		// remote method
		public function confirm( DBResponse $oResponse )
		{
			global $db_personnel;
			$aParams = Params::getAll();
			
			$oDBPersonLeaves 	= new DBBase2( $db_personnel, "person_leaves" );
			$oDBLeaves			= new DBLeaves();
			$oDBObjectPersonnel = new DBObjectPersonnel();
			$oDBObjectDuty 		= new DBObjectDuty();
			$oDBSalaryEarning 	= new DBSalaryEarning();
			$oDBSalary			= new DBSalary();
			$oDBPersonnel 		= new DBPersonnel();
			$oDBHolidays		= new DBHolidays();
			
			$nID 		= isset( $aParams['hiddenParams']->nID ) 		? $aParams['hiddenParams']->nID 		: 0;
			$nIDPerson 	= isset( $aParams['hiddenParams']->nIDPerson ) 	? $aParams['hiddenParams']->nIDPerson 	: 0;
			
			$aMonthStat = array();	//Разбивка на работните дни по месеци.
			$bIsSubstituteNeeded = $oDBPersonnel->isSubstituteNeeded( $nIDPerson );
			$nRemainLeaveDays = $oDBLeaves->getRemainingLeaveDays( substr( $aParams['sLeaveFromOffer'], 0, 4 ), $nIDPerson, $nID );
			
			//Validation
			if( empty( $aParams['nApplicationDaysOffer'] ) )throw new Exception( "Моля, въведете брой работни дни!", DBAPI_ERR_INVALID_PARAM );
			if( !is_numeric( $aParams['nApplicationDaysOffer'] ) || $aParams['nApplicationDaysOffer'] < 1 )
			{
				throw new Exception( "Невалидна стойност за брой дни!", DBAPI_ERR_SUCCESS );
			}
			if( empty( $aParams['sLeaveFromOffer'] ) )throw new Exception( "Невалидна дата!", DBAPI_ERR_INVALID_PARAM );
			if( $bIsSubstituteNeeded )
			{
				if( empty( $aParams['nIDPersonSubstitute'] ) )throw new Exception( "Моля, въведете заместник!", DBAPI_ERR_INVALID_PARAM );
			}
			if( empty( $aParams['nIDCodeLeave'] ) )throw new Exception( "Моля, въведете чл. от КТ!", DBAPI_ERR_INVALID_PARAM );
			
			if( $aParams['nIsAllowed'] )
			{
				if( empty( $aParams['nApplicationDays'] ) )throw new Exception( "Моля, въведете брой работни дни!", DBAPI_ERR_INVALID_PARAM );
				if( !is_numeric( $aParams['nApplicationDays'] ) || $aParams['nApplicationDays'] < 1 )
				{
					throw new Exception( "Невалидна стойност за брой дни!", DBAPI_ERR_SUCCESS );
				}
				if( empty( $aParams['sLeaveFrom'] ) )throw new Exception( "Невалидна дата!", DBAPI_ERR_INVALID_PARAM );
			}
/*
			throw new Exception("sLeaveType=".$aParams['sLeaveType'].' , nApplicationDaysOffer='.$aParams['nApplicationDaysOffer'].
				' , nRemainLeaveDays='.$nRemainLeaveDays.' , nApplicationDays='.$aParams['nApplicationDays'].
				' , nIsConfirm='.$aParams['nIsConfirm']);
*/
			if( $aParams['sLeaveType'] == "due" )
			{
				if( $aParams['nApplicationDaysOffer'] > $nRemainLeaveDays )
				{
					throw new Exception( "Въведения брой дни е над лимита за годината!", DBAPI_ERR_INVALID_PARAM );
				}
				
				if( $aParams['nApplicationDays'] > $nRemainLeaveDays )
				{
					throw new Exception( "Въведения брой дни е над лимита за годината!", DBAPI_ERR_INVALID_PARAM );
				}
			}
			//End Validation
			
			$aData = array();
			$aData['id'] 						= $nID;
			$aData['id_person'] 				= $nIDPerson;
			$aData['type'] 						= "application";
			if( empty( $nID ) )
			{
				$aData['date'] 					= date( "Y-m-d H:i:s" );
			}
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
				if( $nIsOverlapped )throw new Exception( "Съществува молба, чиито дати се застъпват с въведените!" );
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
				
				throw new Exception( $sErrorMessage, DBAPI_ERR_INVALID_PARAM );
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
							throw new Exception( "Грешка при подновяване на графика!", $nResult );
						}
						
						$nResult = $oDBSalary->deleteSalaryRowsByApplication( $nID );
						if( $nResult != DBAPI_ERR_SUCCESS )
						{
							throw new Exception( "Грешка при коригиране на работна заплата!" );
						}
					}
				}
			}
			//End Fix Schedules
			
			$nResult = $oDBPersonLeaves->update( $aData );
			if( $nResult != DBAPI_ERR_SUCCESS )
			{
				throw new Exception( "Грешка при запазване на данните!", $nResult );
			}
			
			if( $aParams['nIsAllowed'] )
			{
				//Fix Schedule
				$nResult = $oDBObjectDuty->putPersonLeaveForDays( $nIDPerson, $aData['res_leave_from'], $aData['application_days'] );
				if( $nResult != DBAPI_ERR_SUCCESS )
				{
					throw new Exception( "Грешка при запазване на информацията по графика!", $nResult );
				}
				//End Fix Schedule
				
				//Add Salary Earning
				$aCodeLeaveEarning 	= $oDBSalaryEarning->getLeaveEarning( $aParams['sLeaveType'] );
				
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
							throw new Exception( "Грешка при нанасяне на наработки!", $nResult );
						}
					}
				}
				//End Add Salary Earning
			}
			
			$nID = $aData['id'];
			
			Params::set( 'id', 			$nID 		);
			Params::set( 'id_person',	$nIDPerson 	);
			
			$this->init( $oResponse );
		}
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBLeaves = new DBLeaves();
			
			$nID = isset( $aParams['id'] ) ? $aParams['id'] : 0;
			
			if( $aParams['api_action'] == "export_to_pdf" )
			{
				$aPDFData = array();
				$aPDFData = $oDBLeaves->getLeavePDFData( $nID );
				
				$aParams['PDFData'] = $aPDFData;
				
				$personLeavePDF = new personLeavePDF( "L" );
				$personLeavePDF->PrintReport( $aParams );
			}
			
			$oResponse->printResponse( "Молба за Отпуск", "PersonLeave" );
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
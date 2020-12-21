<?php

	class ApiSetupLeaveEarnings
	{
		// remote method
		public function init( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBFirms 					= new DBFirms();
			$oDBOffices 				= new DBOffices();
			$oDBSalary 					= new DBSalary();
			$oDBFilters 				= new DBFilters();
			$oDBFiltersVisibleFields 	= new DBFiltersVisibleFields();
			$oDBPersonLeaves			= new DBPersonLeaves();
			
			//Paging
			$aPager = array();
			$aPager = Params::get( "pagingandsorting", array() );
			//End Paging
			
			//Initialize Controls
			$aNullElement1 = array( "0" => array( "id" => "0", "label" => "-- Изберете --" ) );
			$aNullElement2 = array( "0" => array( "id" => "0", "label" => "-- Първо изберете Фирма --" ) );
			
			//Set Filters
			$nIDPerson = ( int ) isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			$aFilters = $oDBFilters->getFiltersByReportClassForFlexCombo( "SetupLeaveEarnings", $nIDPerson );
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
			
			$oResponse->SetFlexControl( "nIDOffice" );
			$oResponse->SetFlexControlDefaultValue( "nIDOffice", "id", isset( $aParams['nIDOffice'] ) ? $aParams['nIDOffice'] : 0 );
			
			$oResponse->SetFlexControl( "nMonth" );
			$oResponse->SetFlexControlDefaultValue( "nMonth", "id", isset( $aParams['nMonth'] ) ? $aParams['nMonth'] : date( "Ym", strtotime( "-1 MONTHS" ) ) );
			
			if( !isset( $aParams['nIDOffice'] ) )$aParams['nIDOffice'] = 0;
			if( !isset( $aParams['nIDFirm'] ) )$aParams['nIDFirm'] = 0;
			if( !isset( $aParams['nMonth'] ) )$aParams['nMonth'] = date( "Ym", strtotime( "-1 MONTHS" ) );
			
			$aMonths = array();
			for( $i = 6; $i > -6; $i-- )
			{
				$aMonths[] = array( "id" => date( "Ym", strtotime( "{$i} MONTHS" ) ), "label" => date( "m-Y", strtotime( "{$i} MONTHS" ) ) );
			}
			//End Initialize Controls
			
			//Visible Fields
			$aFilterFields = $oDBFiltersVisibleFields->getFieldsByIDFilter( isset( $aParams['nIDFilter'] ) ? $aParams['nIDFilter'] : $nIDDefaultFilter );
			
			$aVisible = array();
			$nDefaultValue = ( ( isset( $aParams['nIDFilter'] ) && !empty( $aParams['nIDFilter'] ) ) || !empty( $nIDDefaultFilter ) ) ? 0 : 1;
			$aVisible['person_name'] 			= $nDefaultValue;
			$aVisible['firm'] 					= $nDefaultValue;
			$aVisible['office'] 				= $nDefaultValue;
			$aVisible['leave_type'] 			= $nDefaultValue;
			$aVisible['leave_from_all'] 		= $nDefaultValue;
			$aVisible['sum'] 					= $nDefaultValue;
			$aVisible['application_days'] 		= $nDefaultValue;
			
			$aVisible['leave_num'] 				= $nDefaultValue;
			$aVisible['date'] 					= $nDefaultValue;
			$aVisible['person_position'] 		= $nDefaultValue;
			$aVisible['object'] 				= $nDefaultValue;
			$aVisible['leave_from'] 			= $nDefaultValue;
			$aVisible['leave_to'] 				= $nDefaultValue;
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
			$aData = $oDBSalary->getReportLeave( $aParams, $aPager );
			
			foreach( $aData as $nKey => &$aValue )
			{
				$aMonthStat = $oDBPersonLeaves->getMonthStatForApplication( $aValue['id_leave'], false );
				if( isset( $aMonthStat[$aParams['nMonth']] ) )$aValue['days_count'] = $aMonthStat[$aParams['nMonth']];
			}
			//End Get Result
			
			$oResponse->SetFlexVar( "aFirms", $aFirms );
			$oResponse->SetFlexVar( "aOffices", $aOffices );
			$oResponse->setFlexVar( "aMonths", $aMonths );
			$oResponse->setFlexVar( "aPaging", $aPager );
			$oResponse->setFlexVar( "aData", $aData );
			$oResponse->SetFlexVar( "aFilters", $aFilters );
			$oResponse->SetFlexVar( "aVisible", $aVisible );
			
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
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBSalary 					= new DBSalary();
			$oDBLeaves 					= new DBLeaves();
			$oDBFiltersVisibleFields 	= new DBFiltersVisibleFields();
			
			$nIDFilter = isset( $aParams['nIDFilter'] ) ? $aParams['nIDFilter'] : 0;
			
			//Visible Fields
			$aFilterFields = $oDBFiltersVisibleFields->getFieldsByIDFilter( $nIDFilter );
			
			$aVisible = array();
			$nDefaultValue = ( isset( $aParams['nIDFilter'] ) && !empty( $aParams['nIDFilter'] ) ) ? 0 : 1;
			$aVisible['person_name'] 			= $nDefaultValue;
			$aVisible['firm'] 					= $nDefaultValue;
			$aVisible['office'] 				= $nDefaultValue;
			$aVisible['leave_type'] 			= $nDefaultValue;
			$aVisible['leave_from_all'] 		= $nDefaultValue;
			$aVisible['sum'] 					= $nDefaultValue;
			$aVisible['application_days'] 		= $nDefaultValue;
			
			$aVisible['leave_num'] 				= $nDefaultValue;
			$aVisible['date'] 					= $nDefaultValue;
			$aVisible['person_position'] 		= $nDefaultValue;
			$aVisible['object'] 				= $nDefaultValue;
			$aVisible['leave_from'] 			= $nDefaultValue;
			$aVisible['leave_to'] 				= $nDefaultValue;
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
				$aData = $oDBSalary->getReportLeave( $aParams, $aPaging );
				
				if( isset( $aVisible['leave_num'] ) && !empty( $aVisible['leave_num'] ) )
					$oResponse->setField( "leave_num", "Молба Номер" );
				
				if( isset( $aVisible['code_leave_name'] ) && !empty( $aVisible['code_leave_name'] ) )
					$oResponse->setField( "code_leave_name", "Чл. от КТ" );
				
				if( isset( $aVisible['person_name'] ) && !empty( $aVisible['person_name'] ) )
					$oResponse->setField( "person_name", "Служител" );
				
				if( isset( $aVisible['person_position'] ) && !empty( $aVisible['person_position'] ) )
					$oResponse->setField( "person_position", "Длъжност" );
				
				if( isset( $aVisible['firm'] ) && !empty( $aVisible['firm'] ) )
					$oResponse->setField( "firm", "Фирма" );
				
				if( isset( $aVisible['office'] ) && !empty( $aVisible['office'] ) )
					$oResponse->setField( "office", "Регион" );
				
				if( isset( $aVisible['object'] ) && !empty( $aVisible['object'] ) )
					$oResponse->setField( "object", "Обект" );
				
				if( isset( $aVisible['leave_type'] ) && !empty( $aVisible['leave_type'] ) )
					$oResponse->setField( "leave_type", "Тип" );
				
				if( isset( $aVisible['date'] ) && !empty( $aVisible['date'] ) )
					$oResponse->setField( "date", "Дата" );
				
				if( isset( $aVisible['leave_from'] ) && !empty( $aVisible['leave_from'] ) )
					$oResponse->setField( "leave_from", "Молба От" );
				
				if( isset( $aVisible['leave_to'] ) && !empty( $aVisible['leave_to'] ) )
					$oResponse->setField( "leave_to", "Молба До" );
				
				if( isset( $aVisible['leave_from_all'] ) && !empty( $aVisible['leave_from_all'] ) )
					$oResponse->setField( "date_from", "От Дата" );
				
				if( isset( $aVisible['application_days'] ) && !empty( $aVisible['application_days'] ) )
					$oResponse->setField( "days_count", "Брой Дни" );
				
				if( isset( $aVisible['created_user'] ) && !empty( $aVisible['created_user'] ) )
					$oResponse->setField( "created_user", "Въвел" );
				
				if( isset( $aVisible['status'] ) && !empty( $aVisible['status'] ) )
					$oResponse->setField( "status", "Статус" );
				
				if( isset( $aVisible['time_confirm'] ) && !empty( $aVisible['time_confirm'] ) )
					$oResponse->setField( "time_confirm", "Дата" );
				
				if( isset( $aVisible['res_leave_from'] ) && !empty( $aVisible['res_leave_from'] ) )
					$oResponse->setField( "res_leave_from", "Дата От" );
				
				if( isset( $aVisible['res_leave_to'] ) && !empty( $aVisible['res_leave_to'] ) )
					$oResponse->setField( "res_leave_to", "Дата До" );
				
				if( isset( $aVisible['res_application_days'] ) && !empty( $aVisible['res_application_days'] ) )
					$oResponse->setField( "res_application_days", "Дни" );
				
				if( isset( $aVisible['person_confirm'] ) && !empty( $aVisible['person_confirm'] ) )
					$oResponse->setField( "person_confirm", "Потвърдил" );
				
				if( isset( $aVisible['sum'] ) && !empty( $aVisible['sum'] ) )
					$oResponse->setField( "total_sum", "Сума" );
				
				$oResponse->setData( $aData );
				//End PDF Data
			}
			
			$oResponse->printResponse( "Отпуски - Наработки", "leave_earnings" );
		}
		
		// remote method
		public function getOffices( DBResponse $oResponse )
		{
			$nIDFirm = Params::get( "nIDFirm", 0 );
			
			$oDBOffices = new DBOffices();
			
			//Initialize Controls
			$aNullElement1 = array( "0" => array( "id" => "0", "label" => "-- Изберете --" ) );
			$aNullElement2 = array( "0" => array( "id" => "0", "label" => "-- Първо изберете Фирма --" ) );
			
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
			//End Initialize Controls
			
			$oResponse->SetFlexVar( "aOffices", $aOffices );
			
			$oResponse->printResponse();
		}
		
		// remote method
		public function save( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBSalaryUnstored = new DBSalaryUnstored();
			
			$nID = isset( $aParams['nID'] ) ? $aParams['nID'] : 0;
			$nSum = isset( $aParams['nSum'] ) ? $aParams['nSum'] : 0;
			$nIDPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			$nMode = isset( $aParams['nMode'] ) ? $aParams['nMode'] : 0;
			
			if( !empty( $nIDPerson ) && !empty( $nID ) && !empty( $nSum ) )
			{
				$nResult = $oDBSalaryUnstored->saveSum( $nIDPerson, $nID, $nSum, $nMode );
				if( $nResult != DBAPI_ERR_SUCCESS )
				{
					throw new Exception( "Грешка при изпълнение на операцията!", $nResult );
				}
			}
			
			$oResponse->printResponse();
		}
		
		// remote method
		public function flush( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBSalaryUnstored = new DBSalaryUnstored();
			
			$nIDPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			$nMode = isset( $aParams['nMode'] ) ? $aParams['nMode'] : 0;
			
			if( !empty( $nIDPerson ) )
			{
				$nResult = $oDBSalaryUnstored->flushData( $nIDPerson, $nMode );
				if( $nResult != DBAPI_ERR_SUCCESS )
				{
					throw new Exception( "Грешка при изпълнение на операцията!", $nResult );
				}
			}
			
			Params::set( "pagingandsorting", Params::get( "pagingandsorting", array() ) );
			
			$this->init( $oResponse );
		}
		
		// remote method
		public function clear( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBSalaryUnstored = new DBSalaryUnstored();
			
			$nIDPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			$nMode = isset( $aParams['nMode'] ) ? $aParams['nMode'] : 0;
			
			if( !empty( $nIDPerson ) )
			{
				$nResult = $oDBSalaryUnstored->clearData( $nIDPerson, $nMode );
				if( $nResult != DBAPI_ERR_SUCCESS )
				{
					throw new Exception( "Грешка при изпълнение на операцията!", $nResult );
				}
			}
			
			Params::set( "pagingandsorting", Params::get( "pagingandsorting", array() ) );
			
			$this->init( $oResponse );
		}
	}

?>
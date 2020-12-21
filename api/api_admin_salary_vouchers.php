<?php

	class ApiAdminSalaryVouchers
	{
		public function load( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBFirms = new DBFirms();
			$oDBOffices = new DBOffices();
			$oDBObjects = new DBObjects();
			$oDBPersonnel = new DBPersonnel();
			
			if( !isset( $aParams['nIDOffice'] ) )
			{
				$nIDPerson = $_SESSION['userdata']['id_person'];
				$nIDOffice = $_SESSION['userdata']['id_office'];
				
				$aIDFirm = $oDBOffices->getFirmByIDOffice( $nIDOffice );
				$nIDFirm = isset( $aIDFirm['id_firm'] ) ? $aIDFirm['id_firm'] : 0;
				
				$nIDObject = $oDBPersonnel->getPersonObject( $nIDPerson );
				
				if( !empty( $nIDOffice ) && !empty( $nIDFirm ) )
				{
					$aParams['nIDFirm'] = $nIDFirm;
					$aParams['nIDOffice'] = $nIDOffice;
					$aParams['nIDObject'] = $nIDObject;
				}
			}
			
			//Get Firms
			$aFirms = $oDBFirms->getFirms2();
			
			$oResponse->setFormElement( "form1", "nIDFirm" );
			$oResponse->setFormElementChild( "form1", "nIDFirm", array( "value" => 0 ), OPTION_NULL );
			
			foreach( $aFirms as $aFirm )
			{
				$oResponse->setFormElementChild( "form1", "nIDFirm", array( "value" => $aFirm['id'] ), $aFirm['name'] );
			}
			
			if( isset( $aParams['nIDFirm'] ) )
			{
				$oResponse->setFormElementAttribute( "form1", "nIDFirm", "value", $aParams['nIDFirm'] );
			}
			//Get Firms
			
			//Get Offices
			$oResponse->setFormElement( "form1", "nIDOffice" );
			
			if( isset( $aParams['nIDFirm'] ) )
			{
				$oResponse->setFormElementChild( "form1", "nIDOffice", array( "value" => 0 ), OPTION_NULL );
				$aOffices = $oDBOffices->getOfficesByFirm( $aParams['nIDFirm'] );
				
				foreach( $aOffices as $aOffice )
				{
					$oResponse->setFormElementChild( "form1", "nIDOffice", array( "value" => $aOffice['id'] ), sprintf( "%s [%d]", $aOffice['name'], $aOffice['code'] ) );
				}
				
				if( isset( $aParams['nIDOffice'] ) )
				{
					$oResponse->setFormElementAttribute( "form1", "nIDOffice", "value", $aParams['nIDOffice'] );
				}
			}
			else
			{
				$oResponse->setFormElementChild( "form1", "nIDOffice", array( "value" => 0 ), "Първо изберете фирма" );
				$oResponse->setFormElementChild( "form1", "nIDObject", array( "value" => 0 ), "Първо изберете регион" );
			}
			//End Get Offices
			
			//Get Objects
			$oResponse->setFormElement( "form1", "nIDObject" );
			
			if( isset( $aParams['nIDOffice'] ) )
			{
				$oResponse->setFormElementChild( "form1", "nIDObject", array( "value" => 0 ), OPTION_NULL );
				$aObjects = $oDBObjects->getFoObjectsByOfficeAssoc( $nIDOffice );
				
				foreach( $aObjects as $aObject )
				{
					$oResponse->setFormElementChild( "form1", "nIDObject", array( "value" => $aObject['id'] ), sprintf( "[ %s ] %s", $aObject['num'], $aObject['name'] ) );
				}
				
				if( isset( $aParams['nIDObject'] ) )
				{
					$oResponse->setFormElementAttribute( "form1", "nIDObject", "value", $aParams['nIDObject'] );
				}
			}
			else
			{
				$oResponse->setFormElementChild( "form1", "nIDObject", array( "value" => 0 ), "Първо изберете регион" );
			}
			//End Get Objects
			
			$oResponse->printResponse();
		}
		
		public function loadOffices( DBResponse $oResponse )
		{
			$nIDFirm = Params::get( "nIDFirm", 0 );
			
			$oDBOffices = new DBOffices();
			
			$oResponse->setFormElement( "form1", "nIDObject" );
			$oResponse->setFormElement( "form1", "nIDOffice" );
			
			if( !empty( $nIDFirm ) )
			{
				$oResponse->setFormElementChild( "form1", "nIDOffice", array( "value" => 0 ), OPTION_NULL );
				$aOffices = $oDBOffices->getOfficesByFirm( $nIDFirm );
				
				foreach( $aOffices as $aOffice )
				{
					$oResponse->setFormElementChild( "form1", "nIDOffice", array( "value" => $aOffice['id'] ), sprintf( "%s [%d]", $aOffice['name'], $aOffice['code'] ) );
				}
			}
			else
			{
				$oResponse->setFormElementChild( "form1", "nIDOffice", array( "value" => 0 ), "Първо изберете фирма" );
			}
			
			$oResponse->setFormElementChild( "form1", "nIDObject", array( "value" => 0 ), "Първо изберете регион" );
			
			$oResponse->printResponse();
		}
		
		public function loadObjects( DBResponse $oResponse )
		{
			$nIDOffice = Params::get( "nIDOffice", 0 );
			
			$oDBObjects = new DBObjects();
			
			if( empty( $nIDOffice ) )
			{
				$oResponse->setFormElement( "form1", "nIDObject" );
				$oResponse->setFormElementChild( "form1", "nIDObject", array( "value" => 0 ), "Първо изберете регион" );
			}
			else
			{
				$aObjects = $oDBObjects->getFoObjectsByOfficeAssoc( $nIDOffice );
				
				$oResponse->setFormElement( "form1", "nIDObject" );
				$oResponse->setFormElementChild( "form1", "nIDObject", array( "value" => 0 ), "-- Всички --" );
				
				foreach( $aObjects as $nKey => $aValue )
				{
					$oResponse->setFormElementChild( "form1", "nIDObject", array( "value" => $aValue['id'] ), sprintf( "[ %s ] %s", $aValue['num'], $aValue['name'] ) );
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function groupToSalary( DBResponse $oResponse )
		{
			$aCheckboxes 	= Params::get( "chk", array() );
			$aVSums			= Params::get( "v_sum", array() );
			$aIsAvailable	= Params::get( "is_available", array() );
			$nYear 			= Params::get( "nYear", 0 );
			$nMonth			= Params::get( "nMonth", 0 );
			
			$oDBSalary = new DBSalary();
			
			if( !empty( $aCheckboxes ) )
			{
				foreach( $aCheckboxes as $nIDPerson => $nValue )
				{
					if( !empty( $nValue ) )
					{
						$nIsAvailable 	= $aIsAvailable[$nIDPerson];
						$nSum			= $aVSums[$nIDPerson];
						
						if( empty( $nIsAvailable ) )
						{
							throw new Exception( "Възникна грешка при прехвърлянето!", NULL );
						}
						
						$nResult = $oDBSalary->addVouchersToPerson( $nIDPerson, $nSum, $nYear, $nMonth );
						
						if( $nResult != DBAPI_ERR_SUCCESS )
						{
							throw new Exception( "Възникна грешка при прехвърлянето!", NULL );
						}
					}
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function toSalary( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBSalary = new DBSalary();
			
			$nIsAvailable 	= isset( $aParams['is_available'] ) ? $aParams['is_available'] 	: 0;
			$nIDPerson 		= isset( $aParams['id_person'] ) 	? $aParams['id_person'] 	: 0;
			$nSum 			= isset( $aParams['sum'] ) 			? $aParams['sum'] 			: 0;
			$nYear 			= isset( $aParams['nYear'] ) 		? $aParams['nYear'] 		: 0;
			$nMonth 		= isset( $aParams['nMonth'] ) 		? $aParams['nMonth'] 		: 0;
			
			if( empty( $nIsAvailable ) )
			{
				throw new Exception( "Вече е осъществено прехвърляне за този служител!", DBAPI_ERR_ALERT );
			}
			
			$nResult = $oDBSalary->addVouchersToPerson( $nIDPerson, $nSum, $nYear, $nMonth );
			
			if( $nResult != DBAPI_ERR_SUCCESS )
			{
				throw new Exception( "Грешка при прехвърляне към работна заплата!", $nResult );
			}
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBSalary = new DBSalary();
			
			$oDBSalary->getReportSalaryVouchers( $oResponse, $aParams );
			
			$oResponse->printResponse( "Работни заплати - Ваучери", "vouchers" );
		}
	}

?>
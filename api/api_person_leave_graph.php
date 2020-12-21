<?php

	class ApiPersonLeaveGraph
	{
		public function load( DBResponse $oResponse )
		{
			$oDBFirms 	= new DBFirms();
			$oDBOffices = new DBOffices();
			$oPositions	= new DBPositions();
			
			//Load Firms
			$aFirms = $oDBFirms->getFirms2();
			$oResponse->setFormElement( "form1", "nIDFirm" );
			$oResponse->setFormElementChild( "form1", "nIDFirm", array( "value" => 0 ), "-- Всички --" );
			
			foreach( $aFirms as $nKey => $aValue )
			{
				$oResponse->setFormElementChild( "form1", "nIDFirm", array( "value" => $aValue['id'] ), $aValue['name'] );
			}
			//End Load Firms
			
			//Initialize Offices
			$oResponse->setFormElement( "form1", "nIDOffice" );
			$oResponse->setFormElementChild( "form1", "nIDOffice", array( "value" => 0 ), "Първо изберете фирма" );
			//End Initialize Offices
			
			//Initialize Objects
			$oResponse->setFormElement( "form1", "nIDObject" );
			$oResponse->setFormElementChild( "form1", "nIDObject", array( "value" => 0 ), "Първо изберете регион" );
			//End Initialize Objects
			
			//Set Positions
			$aPositions	= $oPositions->getPositions();
			
			$oResponse->setFormElement( "form1", "nIDPosition", array(), "" );
			$oResponse->setFormElementChild( "form1", "nIDPosition", array( "value" => 0 ), "-- Всички --" );
			foreach( $aPositions as $key => $val )
			{
				$oResponse->setFormElementChild( "form1", "nIDPosition", array( "value" => $key ), $val );
			}
			//End Set Positions
			
			//Set Dates
			$aMonths = array
			(
				"01" => "Януари",
				"02" => "Февруари",
				"03" => "Март",
				"04" => "Април",
				"05" => "Май",
				"06" => "Юни",
				"07" => "Юли",
				"08" => "Август",
				"09" => "Септември",
				"10" => "Октомври",
				"11" => "Ноември",
				"12" => "Декември",
			);
			
			$oResponse->setFormElement( "form1", "sDate" );
			for( $i = -6; $i <= 5; $i++ )
			{
				$sValue = date( "Y-m", strtotime( "{$i} MONTHS" ) );
				$sText = date( "Y", strtotime( "{$i} MONTHS" ) ) . " " . $aMonths[date( "m", strtotime( "{$i} MONTHS" ) )];
				
				$oResponse->setFormElementChild( "form1", "sDate", array( "value" => $sValue ), $sText );
			}
			
			$oResponse->setFormElementAttribute( "form1", "sDate", "value", date( "Y-m" ) );
			//End Set Dates
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBPersonLeaves = new DBPersonLeaves();
			
			$oDBPersonLeaves->getReport( $aParams, $oResponse );
			
			$oResponse->printResponse( "Персонал - Отпуски", "person_leave_graph" );
		}
		
		public function loadOffices( DBResponse $oResponse )
		{
			$nIDFirm = Params::get( "nIDFirm", 0 );
			
			$oDBOffices = new DBOffices();
			
			if( empty( $nIDFirm ) )
			{
				$oResponse->setFormElement( "form1", "nIDOffice" );
				$oResponse->setFormElementChild( "form1", "nIDOffice", array( "value" => 0 ), "Първо изберете фирма" );
			}
			else
			{
				$aOffices = $oDBOffices->getOfficesByFirm( $nIDFirm );
				
				$oResponse->setFormElement( "form1", "nIDOffice" );
				$oResponse->setFormElementChild( "form1", "nIDOffice", array( "value" => 0 ), "-- Всички --" );
				
				foreach( $aOffices as $nKey => $aValue )
				{
					$oResponse->setFormElementChild( "form1", "nIDOffice", array( "value" => $aValue['id'] ), sprintf( "%s [%s]", $aValue['name'], $aValue['code'] ) );
				}
			}
			
			//Initialize Objects
			$oResponse->setFormElement( "form1", "nIDObject" );
			$oResponse->setFormElementChild( "form1", "nIDObject", array( "value" => 0 ), "Първо изберете регион" );
			//End Initialize Objects
			
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
	}

?>
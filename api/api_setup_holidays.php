<?php
	include_once( "pdf/pdf_holidays_calendar.php" );
	
	class ApiSetupHolidays
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			if( $aParams['api_action'] == "export_to_pdf" )
			{
				$oHolidaysPDF = new holidaysPDF( "L" );
				$oHolidaysPDF->PrintReport( $aParams['nYear'] );
			}
			
			$oHolidays = new DBHolidays();
			$sInfo = $oHolidays->getCalendarInfo( $aParams );
			$_SESSION['calendar_pdf'] = $oHolidays->getCalendarInfo( $aParams, true );
			
			$aWorkdays = array();
			for( $i = 1; $i <= 12; $i++ )
			{
				$aWorkdays[] = $oHolidays->getWorkdaysForMonth( $aParams['nYear'], $i );
			}
			
			$oResponse->setFormElement( "form1", "sInfo", array( "value" => $sInfo ) );
			$oResponse->setFormElement( "form1", "sWorkdays", array( "value" => implode( "|", $aWorkdays ) ) );
			
			$oResponse->printResponse( "Празнични / Работни дни", "holidays" );
		}
		
		function save( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBHolidays = new DBHolidays();
			
			//Validation
			$bToDelete = false;
			
			if( $aParams['sType'] == "workday" && $aParams['weekday'] < 5 )$bToDelete = true;
			if( $aParams['sType'] == "restday" && $aParams['weekday'] > 4 )$bToDelete = true;
			
			if( $aParams['sType'] == "holiday" )$aParams['year'] = 0;
			//End Validation
			
			$nID = $oDBHolidays->getDayID( $aParams['day'], $aParams['month'], $aParams['year'] );
			
			if( $bToDelete )
			{
				if( !empty( $nID ) )
				{
					$oDBHolidays->delete( $nID );
				}
			}
			else
			{
				$aData = array();
				$aData['id'] = $nID;
				$aData['day'] = $aParams['day'];
				$aData['month'] = $aParams['month'];
				$aData['year'] = $aParams['year'];
				$aData['type'] = $aParams['sType'];
				
				$oDBHolidays->update( $aData );
			}
			
			$oResponse->printResponse();
		}
	}
?>
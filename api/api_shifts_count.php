<?php

	class ApiShiftsCount
	{
		public function result( DBResponse $oResponse )
		{
			global $db_sod;
			$aParams = Params::getAll();
			
			$oFirms		= new DBFirms();
			$oOffices	= new DBOffices();
			$oPositions = new DBPositions();
			$oDBFilters = new DBFilters();
			$oDuty 		= new DBObjectDuty();
			
			//Set Firms
			$aFirms = $oFirms->getFirms2();
			
			$oResponse->setFormElement( 'form1', 'nIDFirm', array() );
			$oResponse->setFormElementChild( 'form1', 'nIDFirm', array( 'value' => 0 ), 'Всички Фирми' );
			
			foreach( $aFirms as $aFirm )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDFirm', array( 'value' => $aFirm['id'] ), $aFirm['name'] );
			}
			if( isset( $aParams['nIDFirm'] ) )
			{
				$oResponse->setFormElementAttribute( 'form1', 'nIDFirm', 'value', $aParams['nIDFirm'] );
			}
			//End Set Firms
			
			//Set Dates
			$oResponse->setFormElement( 'form1', 'sYearMonth' );
			$oResponse->setFormElementChild( 'form1', 'sYearMonth', array( 'value' => 0 ), ' --  Изберете -- ' );
			
			$aYearMonth = $oDuty->getObjectYearMonth();
			
			$sYearThisMonth = date( "Ym" );
			
			if( !array_key_exists( $sYearThisMonth, $aYearMonth ) )
				$oResponse->setFormElementChild( 'form1', 'sYearMonth', array( 'value' => date( "Y-m" ) ), date( "Y-m" ) );
			
			foreach( $aYearMonth as $nYearMonth => $sYearMonth )
				$oResponse->setFormElementChild( 'form1', 'sYearMonth', array( 'value' => $sYearMonth ), $sYearMonth );
			
			if( isset( $aParams['sYearMonth'] ) )
				$oResponse->setFormElementAttribute( 'form1', 'sYearMonth', 'value', $aParams['sYearMonth'] );
			else
			{
				$sDefaultTime = date( "Y-m", mktime( 0, 0, 0, date('m') - 1, date('d'), date('Y') ) );
				if ($oDuty->isExistMonth($sDefaultTime)) {
					$oResponse->setFormElementAttribute( 'form1', 'sYearMonth', 'value', $sDefaultTime );
					$aParams['sYearMonth'] = $sDefaultTime;
				}
			}
			//End Set Dates
			
			if( !isset( $aParams['nIDFirm'] ) && !isset( $aParams['nIDRegion'] ) )
			{
				$aOffices = $oOffices->retrieveLoggedUserOffice( 'nIDFirm', 'nIDRegion', $oResponse );
				$aParams['nIDRegion'] = $_SESSION['userdata']['id_office'];
				$aParams['nIDFirm'] = $oOffices->getFirmByIDOffice( $aParams['nIDRegion'] );
			}
			
			//Load Positions
			$aPositions = $oPositions->getPositions();
			
			$oResponse->setFormElement( "form1", "nIDPosition", array(), "" );
			$oResponse->setFormElementChild( "form1", "nIDPosition", array( "value" => 0 ), "-- Всички --" );
			
			foreach( $aPositions as $nKey => $sValue )
			{
				$oResponse->setFormElementChild( "form1", "nIDPosition", array( "value" => $nKey ), $sValue );
			}
			
			if( !empty( $aParams['nIDPosition'] ) )
			{
				$oResponse->setFormElementAttribute( "form1", "nIDPosition", "value", $aParams['nIDPosition'] );
			}
			//End Load Positions
			
			//Load Filters
			$nIDPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			
			$aFilters = $oDBFilters->getFiltersByReportClass( "DBShiftsCount", $nIDPerson );
			$oResponse->setFormElement( "form1", "nIDScheme" );
			$oResponse->setFormElementChild( "form1", "nIDScheme", array( "value" => 0 ), "-- Изберете --" );
			
			foreach( $aFilters as $nID => $aValues )
			{
				$aAttributes = ( $aValues['is_default'] == 1 ) ? array( "value" => $nID, "selected" => "selected" ) : array( "value" => $nID );
				$oResponse->setFormElementChild( "form1", "nIDScheme", $aAttributes, $aValues['name'] );
			}
			//End Load Filters
			
			$oDuty->countPersonShifts( $aParams, $oResponse );
			
			$oResponse->printResponse( "Брой смени", "shifts_count" );
		}
		
		public function refreshFilters( DBResponse $oResponse )
		{
			$oDBFilters = new DBFilters();
			
			//Load Filters
			$nIDPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			
			$aFilters = $oDBFilters->getFiltersByReportClass( "DBShiftsCount", $nIDPerson );
			$oResponse->setFormElement( "form1", "nIDScheme" );
			$oResponse->setFormElementChild( "form1", "nIDScheme", array( "value" => 0 ), "-- Изберете --" );
			
			foreach( $aFilters as $nID => $aValues )
			{
				$aAttributes = ( $aValues['is_default'] == 1 ) ? array( "value" => $nID, "selected" => "selected" ) : array( "value" => $nID );
				$oResponse->setFormElementChild( "form1", "nIDScheme", $aAttributes, $aValues['name'] );
			}
			//End Load Filters
			
			$oResponse->printResponse();
		}
		
		public function genregions( DBResponse $oResponse )
		{
			global $db_sod;
			$aParams = Params::getAll();
			
			//Set Regions
			$aRegions = array();
			$oRegions = new DBBase( $db_sod, 'offices' );
			
			$aCondition = array();
			if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
			{
				$sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
				$aCondition[] = " id IN ({$sAccessable}) \n";
			}
			
			$nFirmMatch = empty( $aParams['nIDFirm'] ) ? 0 : (int) $aParams['nIDFirm'];
			
			if( $nFirmMatch )$oRegions->getResult( $aRegions, NULL, array_merge( $aCondition, array( " to_arc = 0 ", " id_firm = $nFirmMatch " ) ), "name" );
			else $oRegions->getResult( $aRegions, NULL, array_merge( $aCondition, array( " to_arc = 0 " ) ), "name" );
			
			$oResponse->setFormElement( 'form1', 'nIDRegion' );
			
			$oResponse->setFormElementChild( 'form1', 'nIDRegion', array( 'value' => 0 ), 'Всички Региони');
			foreach( $aRegions as $aRegion )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDRegion', array( 'value' => $aRegion['id'] ), sprintf( "%s [%s]", $aRegion['name'], $aRegion['code'] ) );
			}
			//End Set Regions
			
			$oResponse->printResponse();
		}
		
		public function deleteFilter()
		{
			$nIDFilter = Params::get( "nIDScheme", "0" );
			
			$oDBFilters 				= new DBFilters();
			$oDBFiltersVisibleFields 	= new DBFiltersVisibleFields();
			
			$oDBFiltersVisibleFields->delByIDFilter( $nIDFilter );
			$oDBFilters->delete( $nIDFilter );
		}
	}

?>
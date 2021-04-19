<?php

require_once( "pdf/pdf_schedule.php" );

class ApiPersonSchedule
{
    public function init( DBResponse $oResponse )
    {
        global $db_sod;

        $aParams = &Params::getAll();

        if( !empty( $aParams["nIDSelectObject"] ) )
        {
            $oObjects = new DBObjects();
            $aObject = $oObjects->getRecord( $aParams["nIDSelectObject"] );

            if( empty( $aObject ) )
                throw new Exception( NULL, DBAPI_ERR_ASSERT );

            $oOffices = new DBOffices();
            $aOffice = $oOffices->getRecord( $aObject["id_office"] );

            if( empty( $aOffice ) )
                throw new Exception( NULL, DBAPI_ERR_ASSERT );

            $oFirms = new DBFirms();
            $aFirm = $oFirms->getRecord( $aOffice["id_firm"] );

            if( empty( $aFirm ) )
                throw new Exception( NULL, DBAPI_ERR_ASSERT );

            $aParams["nIDFirm"] 	= $aFirm["id"];
            $aParams["nIDOffice"] 	= $aOffice["id"];
            $aParams["nIDObject"] 	= $aObject["id"];

            $this->_getFirms( $oResponse );
            $this->_getOffices( $oResponse );
            $this->_getObjects( $oResponse );
            $this->_getYearMonth( $oResponse );

            $oResponse->setFormElementAttribute( "form1", "nIDFirm"	, "value", $aFirm["id"] );
            $oResponse->setFormElementAttribute( "form1", "nIDOffice", "value", $aOffice["id"] );
            $oResponse->setFormElementAttribute( "form1", "nIDObject", "value", $aObject["id"] );
        }
        else
        {
            $this->_getFirms( $oResponse );
            $this->_getOffices( $oResponse );
            $this->_getObjects( $oResponse );
        }

        $oResponse->printResponse();
    }

    public function _getFirms( DBResponse $oResponse )
    {
        $oFirms 	= new DBFirms();
        $oOffices	= new DBOffices();

        $aFirms = $oFirms->getAllAssoc();

        $nIDOffice = $_SESSION["userdata"]["id_office"];
        $nIDFirm = $oOffices->getFirmByIDOffice( $nIDOffice );

        $oResponse->setFormElement( "form1", "nIDFirm" );
        $oResponse->setFormElementChild( "form1", "nIDFirm", array( "value" => 0 ), " -- изберете -- " );

        foreach( $aFirms as $aFirm )
        {
            if( $aFirm["id"] == $nIDFirm )
            {
                $oResponse->setFormElementChild( "form1", "nIDFirm", array( "value" => $aFirm["id"], "selected" => "selected" ), $aFirm["name"] );
            }
            else
            {
                $oResponse->setFormElementChild( "form1", "nIDFirm", array( "value" => $aFirm["id"] ), $aFirm["name"] );
            }
        }
    }

    public function getFirms( DBResponse $oResponse )
    {
        $this->_getFirms( $oResponse );

        $oResponse->setFormElement( "form1", "nIDOffice" );
        $oResponse->setFormElementChild( "form1", "nIDOffice", array( "value" => 0 ), " -- изберете -- " );

        $oResponse->setFormElement( "form1", "nIDObject" );
        $oResponse->setFormElementChild( "form1", "nIDObject", array( "value" => 0 ), " -- изберете -- " );

        $oResponse->setFormElement( "form1", "sYearMonth" );
        $oResponse->setFormElementChild( "form1", "sYearMonth", array( "value" => 0 ), " -- изберете -- " );

        $oResponse->printResponse();
    }

    public function _getOffices( DBResponse $oResponse )
    {
        $nIDFirm = Params::get( "nIDFirm", 0 );
        $nIDOffice = $_SESSION["userdata"]["id_office"];

        if( $nIDFirm == 0 )
        {
            $oOffices = new DBOffices();
            $nIDFirm = $oOffices->getFirmByIDOffice( $nIDOffice );
        }

        $oResponse->setFormElement( "form1", "nIDOffice" );
        $oResponse->setFormElementChild( "form1", "nIDOffice", array( "value" => 0 ), " -- изберете -- " );

        if( !empty( $nIDFirm ) )
        {
            $oOffices = new DBOffices();
            $aOffices = $oOffices->getFirmOfficesRightAssoc( $nIDFirm );

            foreach( $aOffices as $aOffice )
            {
                if( $aOffice['id'] == $nIDOffice )
                {
                    $oResponse->setFormElementChild( "form1", "nIDOffice", array( "value" => $aOffice["id"], "selected" => "selected" ), $aOffice["name"] );
                }
                else
                {
                    $oResponse->setFormElementChild( "form1", "nIDOffice", array( "value" => $aOffice["id"] ), $aOffice["name"] );
                }
            }
        }
    }

    public function getOffices( DBResponse $oResponse )
    {
        $this->_getOffices( $oResponse );

        $oResponse->setFormElement( "form1", "nIDObject" );
        $oResponse->setFormElementChild( "form1", "nIDObject", array( "value" => 0 ), " -- изберете -- " );

        $oResponse->setFormElement( "form1", "sYearMonth" );
        $oResponse->setFormElementChild( "form1", "sYearMonth", array( "value" => 0 ), " -- изберете -- " );

        $oResponse->printResponse();
    }

    public function _getObjects( DBResponse $oResponse )
    {
        $nIDOffice 	= Params::get( "nIDOffice" );

        if( $nIDOffice == 0 )
        {
            $nIDOffice = $_SESSION["userdata"]["id_office"];
        }

        $oResponse->setFormElement( "form1", "nIDObject" );
        $oResponse->setFormElementChild( "form1", "nIDObject", array( "value" => 0 ), " -- изберете -- " );

        if( !empty( $nIDOffice ) )
        {
            $oObjects = new DBObjects();
            $aObjects = $oObjects->getFoObjectsByOfficeAssoc( $nIDOffice );

            foreach( $aObjects as $aObject )
                $oResponse->setFormElementChild( "form1", "nIDObject", array( "value" => $aObject["id"] ), sprintf( "[ %s ] %s", $aObject["num"], $aObject["name"] ) );
        }
    }

    public function getObjects( DBResponse $oResponse )
    {
        $this->_getObjects( $oResponse );

        $oResponse->setFormElement( "form1", "sYearMonth" );
        $oResponse->setFormElementChild( "form1", "sYearMonth", array( "value" => 0 ), " -- изберете -- " );

        $oResponse->printResponse();
    }

    public function _getYearMonth( DBResponse $oResponse )
    {
        $aParams = Params::getAll();
        $nIDObject = Params::get( "nIDObject" );

        $oResponse->setFormElement( "form1", "sYearMonth" );
        $oResponse->setFormElementChild( "form1", "sYearMonth", array( "value" => 0 ), " -- изберете -- " );

        if( !empty( $nIDObject ) )
        {
            $oDuty = new DBObjectDuty();
            $aYearMonth = $oDuty->getObjectYearMonth($nIDObject);

            // Таван!
            $t = date("Ym", mktime(0, 0, 0, 1, 1, date("Y") + 1));

            //Текущ месец
            $sYearThisMonth = date("Ym");

            for ( $i = 12; $i >= 1; $i-- ) {
                //$sNext = date("Ym", strtotime("+{$i} month"));
                $sNext = date("Ym", mktime(0, 0, 0, date("m") + $i, 1, date("Y")));

                if ( $sNext > $t ) {
                    continue;
                }

                if ( !array_key_exists($sNext, $aYearMonth) ) {
                    $oResponse->setFormElementChild( "form1", "sYearMonth", array("value" => $sNext), date("Y-m", mktime(0, 0, 0, date("m") + $i, 1, date("Y"))));
                }
            }

            //Следващ месец
            $sYearNextMonth = date( "Ym", strtotime( "+1 month" ) );

            //if( !array_key_exists( $sYearNextMonth, $aYearMonth ) )
            //	$oResponse->setFormElementChild( "form1", "sYearMonth", array( "value" => $sYearNextMonth ), date( "Y-m", strtotime( "+1 month" ) ) );


            if( !array_key_exists( $sYearThisMonth, $aYearMonth ) )
                $oResponse->setFormElementChild( "form1", "sYearMonth", array( "value" => $sYearThisMonth ), date( "Y-m" ) );

            foreach( $aYearMonth as $nYearMonth => $sYearMonth )
                $oResponse->setFormElementChild( "form1", "sYearMonth", array( "value" => $nYearMonth ), $sYearMonth );

            if( isset( $aParams["nCustomDate"] ) && !empty( $aParams["nCustomDate"] ) )$sYearThisMonth = $aParams["nCustomDate"];
            //if( count( $oResponse->oAction->aForms["form1"]->aFormElements["sYearMonth"]->aChilds ) > 1 )
            $oResponse->setFormElementAttribute( "form1", "sYearMonth", "value", $sYearThisMonth );
        }
    }

    public function getYearMonth( DBResponse $oResponse )
    {
        $this->_getYearMonth( $oResponse );

        $oResponse->printResponse();
    }

    public function _result( DBResponse $oResponse ) {
        $nIDObject 	= Params::get("nIDObject", 	0);
        $sYearMonth = Params::get("sYearMonth", "");

        if ( empty($nIDObject) ) {
            throw new Exception("Въведете обект!", DBAPI_ERR_INVALID_PARAM);
        }

        if ( empty($sYearMonth) ) {
            throw new Exception("Въведете месец!", DBAPI_ERR_INVALID_PARAM);
        }

        $nYear 	= substr( $sYearMonth, 0, 4 );
        $nMonth = substr( $sYearMonth, 4, 2 );

        if ( empty($nYear) || !is_numeric($nYear) ) {
            throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
        }

        if ( empty($nMonth) || !is_numeric($nMonth) ) {
            throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
        }

        $oShifts = new DBObjectShifts();
        $aShifts = $oShifts->getObjectShifts($nIDObject);

        $oResponse->setFormElement("form1", "nIDObject", array("value" => $nIDObject));
        $oResponse->setFormElement("form1", "object_shifts", $aShifts);

        // BEGIN CODE : Person Shift Hours Limit ( Часове смени за служител към края и началото на месеца )
        $oDBObjectScheduleSettings = new DBObjectScheduleSettings();
        // END CODE : Person Shift Hours Limit

        foreach( $aShifts as $aShift )
        {
            // BEGIN CODE : Person Shift Hours Limit ( Часове смени за служител към края и началото на месеца )
            $nDuration = $oDBObjectScheduleSettings->calculateShiftHours( $aShift['shiftFromShort'], $aShift['shiftToShort'], $aShift['rest'], true );

            $aShift["shiftCoefDuration"] = $nDuration;
            // END CODE : Person Shift Hours Limit

            $oResponse->setFormElementChild( "form1", "object_shifts", $aShift );
        }

        $oShifts->result($nIDObject, $nYear, $nMonth, $oResponse);

        $oResponse->setFormElement( "form1", "nResultIDObject", array( "value" => $nIDObject	) );
        $oResponse->setFormElement( "form1", "nResultYear", 	array( "value" => $nYear		) );
        $oResponse->setFormElement( "form1", "nResultMonth", 	array( "value" => $nMonth		) );

        $oObjects = new DBObjects();
        $aObject  = $oObjects->getRecord( $nIDObject );

        $phone = isset( $aObject["phone"] ) && !empty( $aObject["phone"] ) ? " " . $aObject["phone"] : "";

        $sTitle = "График за {$nMonth}.{$nYear} г. - {$aObject["name"]}";
        $oResponse->setFormElement( "form1", "divTitle", NULL, $sTitle );

        $oDBScheduleMonthNorms = new DBScheduleMonthNorms();
        $aNorms = $oDBScheduleMonthNorms->getActiveNormsByMonth( $nYear . $nMonth );

        $sShiftsNorm 	= isset( $aNorms["shifts"] ) ? $aNorms["shifts"] : "";
        $sHoursNorm 	= isset( $aNorms["hours"] ) ? $aNorms["hours"] : "";

        $oResponse->setFormElement( "form1", "max_shifts", array(), $sShiftsNorm );
        $oResponse->setFormElement( "form1", "max_hours", array(), $sHoursNorm );
    }

    public function result( DBResponse $oResponse )
    {
        $this->_result( $oResponse );

        foreach( $oResponse->oResult->aData as $key => $val )
        {
            // $val['planMoney'] == '0.00'
            if( !isset( $val["planDuration"] ) || $val["planDuration"] == "00:00" )
            {
                unset( $oResponse->oResult->aData[$key] );
            }
        }

        $sApiAction 	= Params::get( "api_action", "" );
        $nPrintetType 	= Params::get( "nIDPrintType", "" );
        $nIDObject 		= Params::get( "nIDObject", 0 );

        if( $sApiAction == "export_to_pdf" )
        {
            $aLegend = array();

            if( $nPrintetType == 1 )
            {
                $oDBObjectShifts = new DBObjectShifts();
                $aLegend = $oDBObjectShifts->getObjectShifts( $nIDObject, false, false );
            }

            $oPDF = new SchedulePDF( "L" );
            $oPDF->PrintReport( $oResponse, $nPrintetType, $aLegend );
        }
        elseif( $sApiAction == "export_to_xls" )
        {
            $oResponse->toXLS( "schedule.xls", "График за обект" );
        }
        else
        {
            $oResponse->printResponse();
        }
    }

    public function correctAllShiftHours( DBResponse $oResponse )
    {
        $oDBObjectDuty 	= new DBObjectDuty();
        $oPersonML 		= new DBPersonMonthLimits();

        //Get Halfyear
        if( date( "m" ) < 7 )
        {
            $nStartDate = date( "Y" ) . "01";
            $nEndDate = date( "Y" ) . "06";
        }
        else
        {
            $nStartDate = date( "Y" ) . "07";
            $nEndDate = date( "Y" ) . "12";
        }
        //End Get Halfyear

        //Get Personnel
        $sQuery = "
				SELECT
					*
				FROM
					person_month_limits
				WHERE
					#( month >= {$nStartDate} AND month <= {$nEndDate} )
					month = '201109'
					AND hours != '00:00:00'
			";
        /*
        $sQuery = "
            SELECT
                *
            FROM
                person_month_limits
            WHERE
                id_person = 417
                AND month = '201108'
                AND hours != '00:00:00'
        ";
        */

        $aMonthLimits = $oPersonML->select( $sQuery );
        //End Get Personnel

        $oPersonML->StartTrans();

        foreach( $aMonthLimits as $nKey => &$aValue )
        {
            $nYear = substr( $aValue['month'], 0, 4 );
            $nMonth = substr( $aValue['month'], 4, 2 );

            $sPersonHours = $oDBObjectDuty->getHourCurrentForDate( $aValue['id_person'], $nMonth, $nYear, false );
            $sPersonHours .= ":00";

            $aValue["hours"] = $sPersonHours;

            $oPersonML->update( $aValue );
        }

        $oPersonML->CompleteTrans();

        $oResponse->printResponse();
    }

    public function saveAllShiftHours( DBResponse $oResponse )
    {
        $oDBObjectDuty 	= new DBObjectDuty();
        $oPersonML 		= new DBPersonMonthLimits();

        //Get Halfyear
        $aDatesToSearch = array();

        if( date( "m" ) <= 6 )
        {
            $nStartDate = date( "Y01" );
            $nEndDate = date( "Ym" );
        }
        else
        {
            $nStartDate = date( "Ym" );
            $nEndDate = date( "Y12" );
        }

        for( $i = $nStartDate; $i <= $nEndDate; $i++ )
        {
            $aDatesToSearch[] = ( string ) $i;
        }
        //End Get Halfyear

        //Get Personnel
        $sQueryPersons = "
				SELECT
					DISTINCT id_person AS id
				FROM
					object_duty
			";

        $aPersons = $oDBObjectDuty->select( $sQueryPersons );
        //End Get Personnel

        $oPersonML->StartTrans();

        foreach( $aPersons as $nKey => $aPerson )
        {
            $nIDPerson = $aPerson["id"];

            foreach( $aDatesToSearch as $nKeyDate => $sDate )
            {
                $nYear = substr( $sDate, 0, 4 );
                $nMonth = substr( $sDate, 4, 2 );

                $sPersonHours = $oDBObjectDuty->getHourCurrentForDate( $nIDPerson, $nMonth, $nYear, false );
                $nPersonHours = getRoundTime( $sPersonHours );

                $aPersonML = array();
                $aPersonML["id"] = 0;
                $aPersonML["month"] = $sDate;
                $aPersonML["id_person"] = $nIDPerson;
                $aPersonML["hours"] = $nPersonHours;

                $oPersonML->update( $aPersonML );
            }
        }

        $oPersonML->CompleteTrans();
    }

    public function saveAllLeavesToSchedule( DBResponse $oResponse )
    {
        $oDBPersonLeaves = new DBPersonLeaves();
        $oDBObjectDuty = new DBObjectDuty();

        $sLeavesQuery = "
				SELECT
					*
				FROM
					person_leaves
				WHERE
					leave_num != 0
					AND type = 'application'
					AND year >= 2009
					AND ( leave_types = 'due' OR leave_types = 'unpaid' )
					AND application_days != 0
					AND is_confirm = 1
					AND res_leave_to >= '2009-11-01 00:00:00'
					#AND res_leave_to > '2009-06-30 23:59:59'
					AND to_arc = 0
			";

        $aData = $oDBPersonLeaves->select( $sLeavesQuery );

        foreach( $aData as $nKey => $aValue )
        {
            $oDBObjectDuty->putPersonLeaveForDays( $aValue['id_person'], substr( $aValue['res_leave_from'], 0 , 10 ), $aValue['application_days'] );
        }

        $oResponse->printResponse();
    }

    public function _save_leaves($nIDObject, $nYear, $nMonth) {
        global $db_sod, $db_name_sod;

        if ( empty($nYear) ) {
            throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
        }

        if ( empty($nMonth) ) {
            throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
        }

        if ( empty($nIDObject) ) {
            throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
        }

        $oPersonLeaves 	= new DBPersonLeaves();
        $oObjectDuty 	= new DBObjectDuty();
        $aPersons		= array();
        $aLeaves		= array();

        $aPersons 		= $oObjectDuty->getPersonsByObjectDuty($nIDObject, $nYear, $nMonth);
        $aLeaves 		= $oPersonLeaves->getPersonLeavesForMonth($nYear, $nMonth);

        $nLastDay		= date("d", mktime(0, 0, 0, $nMonth + 1, 0, $nYear));

        foreach ( $aPersons as $aPrs ) {
            $nIDPerson	= isset($aPrs['id_person']) && !empty($aPrs['id_person']) ? $aPrs['id_person'] : 0;
            $oObjectDuty->clearDutyLeavePerson($nIDPerson, $nIDObject, $nYear, $nMonth);

            if ( !empty($nIDPerson) && isset($aLeaves[$nIDPerson]) ) {
                foreach ( $aLeaves[$nIDPerson] as $aLve ) {
                    $d_from	= isset($aLve['leave_from']) 		? $aLve['leave_from'] 		: "0-0-0";
                    $d_to	= isset($aLve['leave_to']) 			? $aLve['leave_to'] 		: "0-0-0";
                    $type 	= isset($aLve['application_type']) 	? $aLve['application_type'] : "";

                    list($y, $m, $d) 	= explode('-', $d_from);
                    list($y1, $m1, $d1) = explode('-', $d_to);

                    // Omazvacia
                    if ( $m != $nMonth ) {
                        $m 		= $nMonth;
                        $y		= $nYear;
                        $d	 	= 1;
                    }

                    if ( $m1 != $nMonth ) {
                        $d1		= $nLastDay;
                    }

                    if ( $type == "application" ) {
                        for ( $i = $d; $i <= $d1; $i++ ) {
                            $time1 	= mktime(8, 0, 0, $m, $i, $y);
                            $time2 	= mktime(16, 0, 0, $m, $i, $y);

                            $sQuery = "
									UPDATE {$db_name_sod}.object_duty 
										SET startShift = FROM_UNIXTIME({$time1}), 
										startRealShift = FROM_UNIXTIME({$time1}), 
										endShift = FROM_UNIXTIME({$time2}), 
										endRealShift = FROM_UNIXTIME({$time2}), 
										id_shift = 1000001
									WHERE id_person = {$nIDPerson}	
										AND id_obj = {$nIDObject}
										AND YEAR(startShift) = {$y}
										AND MONTH(startShift) = {$m}
										AND DAY(startShift) = {$i}
								";

                            $db_sod->Execute($sQuery);
                        }
                    }
                }
            }
        }
    }

    public function _save( DBResponse $oResponse, $bValidate = false )
    {
        global $db_sod;

        $nYear 			= Params::get( "nResultYear" );
        $nMonth 		= Params::get( "nResultMonth" );
        $nIDObject 		= Params::get( "nResultIDObject" );
        $sValidateDate 	= Params::get( "sValidateDate" );
        $nValidateDate  = 0;

        if( empty( $nYear ) )
            throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
        if( empty( $nMonth ) )
            throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
        if( empty( $nIDObject ) )
            throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );

        if( $bValidate )
        {
            if( empty( $sValidateDate ) )
                throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );

            $aMatches = array();

            if( !preg_match( "/^(\d{1,2})\.(\d{1,2})\.(\d{4}) (\d{2})\:(\d{2})$/", $sValidateDate, $aMatches ) )
                throw new Exception( "Невалидна дата!", DBAPI_ERR_INVALID_PARAM );

            if( count( $aMatches ) != 6 )
                throw new Exception( "Невалидна дата!", DBAPI_ERR_INVALID_PARAM );

            $nValidateDate = mktime( $aMatches[4], $aMatches[5], 0, $aMatches[2], $aMatches[1], $aMatches[3] );

            if( date( "Y", $nValidateDate ) != $nYear )
                throw new Exception( "Годината на графика трябва да съвпада с годината на датата на валидиране!", DBAPI_ERR_INVALID_PARAM );
            if( date( "m", $nValidateDate ) != $nMonth )
                throw new Exception( "Месеца на графика трябва да съвпада с месеца на датата на валидиране!", DBAPI_ERR_INVALID_PARAM );
        }

        $aCells = Params::get( "c", array() );

        $oShifts = new DBObjectShifts();
        $aShifts = $oShifts->getObjectShifts( $nIDObject, true );

        //$persons = ( isset( $aCells[1] ) && is_array( $aCells[1] ) ) ? implode( ",", array_keys( $aCells[1] ) ) : 0;
        //APILog::Log( 0, $aShifts );
        //return;
        $aShiftsCodeIdMap = array();

        foreach( $aShifts as $aShift )
            $aShiftsCodeIdMap[ $aShift['code'] ] = $aShift['id'];

        foreach( $aCells as $nDay => $aPersons )
        {
            foreach( $aPersons as $nIDPerson => $sCode )
                $aCells[ $nDay ][ $nIDPerson ] = !empty( $aShiftsCodeIdMap[ $sCode ] ) ? $aShiftsCodeIdMap[ $sCode ] : 0;
        }

        $oDuty 				= new DBObjectDuty();
        $oDBPersonLeaves 	= new DBPersonLeaves();
        $oDBPersonnel		= new DBPersonnel();
        $oPersonML 			= new DBPersonMonthLimits();
        $oHolidays 			= new DBHolidays();
        $oSalary 			= new DBSalary();

        //$oDuty->clearPlan($nIDPerson, $aData['leave_from'], $aData['leave_to']);
        //$oDuty->createForLeave($nIDPerson, $aData['leave_from'], $aData['leave_to']);

        $oDuty->StartTrans();
        $oPersonML->StartTrans();
        $oSalary->StartTrans();

        $aPersonIDs = array();
        $aPersonCiphers = array();	//Шифър на длъжност по НКИД, за всеки служител.

        $aPersonsLeaves = $oDBPersonLeaves->getPersonLeavesForMonth( $nYear, $nMonth );

        try
        {
            $oDuty->clearAllInvalidatedMonthDutiesFromObject( $nIDObject, $nYear, $nMonth );

            foreach( $aCells as $nDay => $aPersons )
            {
                foreach( $aPersons as $nIDPerson => $aShift )
                {
                    if( !in_array( $nIDPerson, $aPersonIDs ) )
                    {
                        $aPersonIDs[] = $nIDPerson;
                    }

                    if( !isset( $aPersonCiphers[$nIDPerson] ) )
                    {
                        //Get Person NC Cipher
                        $aPersonPositionNC = $oDBPersonnel->getPersonPositionNC( $nIDPerson );
                        $aPersonCiphers[$nIDPerson] = isset( $aPersonPositionNC['cipher'] ) ? $aPersonPositionNC['cipher'] : 0;
                        //End Get Person NC Cipher
                    }

                    //-- Ако смяната е валидирана, избягваме да я запишем втори път в object_duty ( Мантис ID: 190 ) --
                    if( $oDuty->IsPersonDutyExistsOnDate( $nIDObject, $nIDPerson, $nYear, $nMonth, $nDay) )
                        continue;
                    //--

                    $nIDShift = $aCells[ $nDay ][ $nIDPerson ];

                    $aDuty = array();
                    $aDuty["id_obj"] 	 = $nIDObject;
                    $aDuty["id_person"]  = $nIDPerson;

                    if( !empty( $nIDShift ) )
                    {
                        $aDuty["id_shift"] 	 = $nIDShift;
                        $aDuty["startShift"] = mysqlDateToTimestamp( "{$nYear}-{$nMonth}-{$nDay} {$aShifts[ $nIDShift ]["shiftFrom"]}" );
                        $aDuty["endShift"]   = mysqlDateToTimestamp( "{$nYear}-{$nMonth}-{$nDay} {$aShifts[ $nIDShift ]["shiftTo"]}" );
                    }
                    else
                    {
                        $aDuty["id_shift"] 	 = 0;
                        $aDuty["startShift"] = mysqlDateToTimestamp( "{$nYear}-{$nMonth}-{$nDay} 00:00:00" );
                        $aDuty["endShift"]   = mysqlDateToTimestamp( "{$nYear}-{$nMonth}-{$nDay} 00:00:00" );
                    }

                    if( $aDuty["startShift"] >= $aDuty["endShift"] )
                        $aDuty["endShift"] += 24 * 60 * 60; //+ 1 ден

                    $bPO = $oShifts->checkForPO($nIDShift);

                    //-- Ако има молба за ОТПУСК или БОЛНИЧЕН на тази дата, извеждаме съобщение! ( Мантис ID: 1490 ) --
                    if( !empty( $nIDShift ) && !$bPO )
                    {
                        $sSYearMonth = date( "Y-m-d", $aDuty['startShift'] );
                        $sEYearMonth = date( "Y-m-d", $aDuty['endShift'] );

                        foreach( $aPersonsLeaves as $nIDLeavePerson => $aLeaves )
                        {
                            if( $nIDLeavePerson == $nIDPerson )
                            {
                                foreach( $aLeaves as $nIDLeave => $aLeave )
                                {
                                    if( $aPersonCiphers[$nIDPerson] == "41912012" || $aLeave['application_type'] == "hospital" )
                                    {
                                        if( $sSYearMonth >= $aLeave['leave_from'] && $sSYearMonth <= $aLeave['leave_to'] && !$bPO)
                                        {
                                            throw new Exception( "За " . $aLeave['person_name'] . " има запазени молби за отпуск по време на планиране на графика!", DBAPI_ERR_FAILED_TRANS );
                                        }
                                    }
                                    else
                                    {
                                        if( $sSYearMonth >= $aLeave['leave_from'] && $sSYearMonth <= $aLeave['leave_to'] ||
                                            $sEYearMonth >= $aLeave['leave_from'] && $sEYearMonth <= $aLeave['leave_to'] && !$bPO)
                                        {
                                            throw new Exception( "За " . $aLeave['person_name'] . " има запазени молби за отпуск по време на планиране на графика!", DBAPI_ERR_FAILED_TRANS );
                                        }
                                    }
                                }
                            }
                        }
                    }
                    //--

                    if( !empty($aDuty["id_shift"]) )
                    {
                        $oDuty->checkObjectDutyAvaliability( $nIDPerson, $nIDObject, $aDuty["startShift"], $aDuty["endShift"] );
                        $aDuty["auto"] = $oDuty->checkAutomatic( $aDuty["id_shift"] );
                    }

                    if( $bValidate && $aDuty["startShift"] <= $nValidateDate )
                    {
                        $aDuty["startRealShift"] 	= $aDuty["startShift"];
                        $aDuty["endRealShift"] 		= $aDuty["endShift"];
                        $aDuty["stake"]				= !empty( $aShifts[ $nIDShift ]["stake"] )? $aShifts[ $nIDShift ]["stake"] : 0.0;

                        $nStartDaySeconds 	= 0;	// Продължителността в секунди на първия ден от смяната, ако смяната е в рамките на деня, ще съдържа продължителността на цялата смяна
                        $nEndDaySeconds 	= 0;	// Продължителността в ... на втория ден .., ако смяната е в рамките на един ден, то стойността е 0

//							/*
//								@autor dido2k
//
//								NOTE:
//									Ако смяната застъпва 2 дати, например ( 2007-01-01 23:00:00 - 2007-01-02 06:00:00 )
//									трябва да сметнем ставките на времето от едната дата отделно от ставката за другата дата.
//									Причината е, че едната дата може да е празничен ден т.е. времето от смяната за едната дата да трябва
//									да се плати по различна ставка ( коефицент ).
//							*/
//
//							if( date("d", $aDuty['startShift']) != date("d", $aDuty['endShift']) )
//							{
//								/* <note author="dido2k">
//
//									В таблица object_shifts се отбелязва само време ( 4ас ) т.е. без дата.
//									Ако смяната започва с час по-голям от часа на приключване, примерно 23:00 - 06:00
//									тогава се налага да прибавим 24 4аса към времето "до" за да сметнем правилно различката
//									т.е. продължителността на смяната.
//
//								*/
//
//								$nNextDayOffsetSeconds = 24*60*60;
//
//								$nStartDaySeconds = mktime(
//									0,
//									0,
//									0,
//									date("m", $aDuty['startShift'] + $nNextDayOffsetSeconds),
//									date("d", $aDuty['startShift'] + $nNextDayOffsetSeconds),
//									date("Y", $aDuty['startShift'] + $nNextDayOffsetSeconds)
//									)
//									-
//									$aDuty['startShift'];
//
//								$nEndDaySeconds =
//									$aDuty['endShift']
//									-
//									mktime(
//										0,
//										0,
//										0,
//										date("m", $aDuty['endShift'] ),
//										date("d", $aDuty['endShift'] ),
//										date("Y", $aDuty['endShift'] )
//										);
//							}
//							else
//							{
//								$nStartDaySeconds 	= $aDuty['endShift'] - $aDuty['startShift'];
//								$nEndDaySeconds 	= 0;
//							}
//
//							$nStartDayStakeFactor = 1.0;
//
//							if( $oHolidays->isHoliday( date("d", $aDuty['startShift'] ), date("m", $aDuty['startShift'] ) ) && empty($aDuty['auto']) )
//								$nStartDayStakeFactor = $_SESSION['system']['holiday_stake_factor'];
//
//							$nEndDayStakeFactor = 1.0;
//
//							if( $oHolidays->isHoliday( date("d", $aDuty['endShift'] ), date("m", $aDuty['endShift'] ) ) && empty($aDuty['auto']) )
//								$nEndDayStakeFactor = $_SESSION['system']['holiday_stake_factor'];
//
//							$aDuty['stake'] = round(( ( ( $nStartDaySeconds * $nStartDayStakeFactor + $nEndDaySeconds * $nEndDayStakeFactor ) / 3600 ) * $aDuty['stake'] ) / (( $aDuty['endShift'] - $aDuty['startShift'] ) / 3600), 2);
                    }

                    $oDuty->update( $aDuty );
                }
            }

            // BEGIN CODE : Person Shift Hours Limit ( Часове смени за служител към края и началото на месеца )
            foreach( $aPersonIDs as $nKey => $nIDPerson )
            {
                $sDate = $nYear . $nMonth;
                $aPersonML = array();

                $sPersonHours = $oDuty->getHourCurrentForDate( $nIDPerson, $nMonth, $nYear, false );

                //Try to Get One (if there is)!
                $sPersonLimitQuery = "
						SELECT
							id
						FROM
							person_month_limits
						WHERE
							id_person = {$nIDPerson}
							AND month = '{$sDate}'
						LIMIT 1
					";

                $aPersonML = $oPersonML->selectOnce( $sPersonLimitQuery );

                if( !isset( $aPersonML["id"] ) )$aPersonML["id"] = 0;
                $aPersonML["month"] = $sDate;
                $aPersonML["id_person"] = $nIDPerson;
                $aPersonML["hours"] = $sPersonHours;

                $oPersonML->update( $aPersonML );
            }
            // END CODE : Person Shift Hours Limit

            if( $bValidate )
                $oSalary->transferMonthObjectSalaryFromDuty( $nIDObject, $nYear, $nMonth );

            if( $oDuty->HasFailedTrans() )
                throw new Exception( NULL, DBAPI_ERR_FAILED_TRANS );
            if( $oPersonML->HasFailedTrans() )
                throw new Exception( NULL, DBAPI_ERR_FAILED_TRANS );
            if( $oSalary->HasFailedTrans() )
                throw new Exception( NULL, DBAPI_ERR_FAILED_TRANS );
        }
        catch( Exception $e )
        {
            $oDuty->FailTrans();
            $oPersonML->FailTrans();
            $oSalary->FailTrans();
            throw $e;
        }

        $oDuty->CompleteTrans();
        $oPersonML->CompleteTrans();
        $oSalary->CompleteTrans();
    }

    public function save( DBResponse $oResponse ) {
        $nYear 			= Params::get( "nResultYear" );
        $nMonth 		= Params::get( "nResultMonth" );
        $nIDObject 		= Params::get( "nResultIDObject" );

        $this->_save( $oResponse, FALSE );

        //$oDuty->createForLeave($nIDPerson, $aData['leave_from'], $aData['leave_to']);
        $this->_save_leaves($nIDObject, $nYear, $nMonth);

        $this->_result( $oResponse );

        $oResponse->printResponse();
    }

    public function autoValidate( DBResponse $oResponse )
    {
        global $db_sod, $db_name_sod, $db_name_personnel;

        $day = date( "Y-m-d" );
        $user = !empty( $_SESSION["userdata"]["id_person"] ) ? $_SESSION["userdata"]["id_person"] : 0;
        $nIDs = "";

        $db_sod->startTrans();

        $sQueryID = "
				SELECT 
					GROUP_CONCAT( od.id ) as id
				FROM
					object_duty od
				LEFT JOIN
					object_shifts os ON os.id = od.id_shift
				WHERE
					od.id_shift > 0
					AND os.automatic = 1
					AND UNIX_TIMESTAMP( od.endRealShift ) = 0
					AND od.endShift <= '" . $day . " 23:59:00'
			";

        $res = $db_sod->Execute( $sQueryID );

        if( !$res->EOF )
        {
            $nIDs = $res->fields['id'];
        }

        if( empty( $nIDs ) )
        {
            $nIDs = "-1";
        }

        $sQuery = "
				UPDATE
					object_duty od, object_shifts os
				SET
					od.startRealShift = od.startShift,
					od.endRealShift = od.endShift,
					od.note = 'Автоматична смяна',
					od.stake = os.stake,
					od.updated_user = {$user},
					od.updated_time = NOW()
				WHERE od.id_shift = os.id
					AND od.id_shift > 0
					
					AND UNIX_TIMESTAMP( od.endRealShift ) = 0
					AND od.endShift <= '" . $day . " 23:59:00'
					AND od.id IN ( {$nIDs} )
			";

        $db_sod->Execute( $sQuery );

        $sQuery2 = "
				INSERT INTO {$db_name_personnel}.salary ( id_person, id_office, id_object, id_object_duty, month, code, is_earning, sum, description, count, total_sum, created_user, created_time, updated_user, updated_time, to_arc )
				SELECT 
					od.id_person,
					o.id_office,
					od.id_obj,
					od.id,
					CONCAT(
						DATE_FORMAT( od.startRealShift, '%Y' ),
						DATE_FORMAT( od.startRealShift, '%m' )
					),
					se.code,
					1,
					IF( od.stake > 0, IF( pc.rate_reward, ( ( od.stake * pc.rate_reward ) / 100 ), od.stake ), IF( pc.rate_reward, ( ( os.stake * pc.rate_reward ) / 100 ), os.stake ) ) AS stake,
					CONCAT( 'Автоматична - [', os.code, '] ', DATE_FORMAT( od.startRealShift, '%d.%m.%Y %H:%i' ) ) as name,
					CONCAT( ( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) div 3600, '.', ( ( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) mod 3600 ) / 60 ),
					ROUND( IF( od.stake > 0, IF( pc.rate_reward, ( ( od.stake * pc.rate_reward ) / 100 ), od.stake ), IF( pc.rate_reward, ( ( os.stake * pc.rate_reward ) / 100 ), os.stake ) ) * ( ( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) / 3600 ), 2 ),
					{$user},
					NOW(),
					{$user},
					NOW(),
					0
				FROM {$db_name_sod}.object_duty od
				LEFT JOIN {$db_name_sod}.objects o ON od.id_obj = o.id
				LEFT JOIN {$db_name_personnel}.salary_earning_types se ON se.source = 'schedule'
				LEFT JOIN {$db_name_sod}.object_shifts os ON od.id_shift = os.id
				LEFT JOIN {$db_name_personnel}.person_contract pc ON ( pc.to_arc = 0 AND pc.id_person = od.id_person AND UNIX_TIMESTAMP( ( INTERVAL 1 DAY + trial_from ) ) <= UNIX_TIMESTAMP( od.startRealShift ) AND UNIX_TIMESTAMP( ( INTERVAL 1 DAY + trial_to ) ) >= UNIX_TIMESTAMP( od.endRealShift ) )
				WHERE 1
					AND od.stake > 0
					AND od.startRealShift > 0
					AND od.endRealShift   > 0
					AND od.endShift > od.startShift
					AND od.id IN ( {$nIDs} )
			";

        $db_sod->Execute( $sQuery2 );

        $db_sod->completeTrans();

        $oResponse->printResponse();
    }

    public function validate( DBResponse $oResponse )
    {
        $this->_save( $oResponse, true );
        $this->_result( $oResponse );

        $oResponse->printResponse();
    }

    public function invalidate( DBResponse $oResponse )
    {
        global $db_sod;

        $nYear 				= Params::get( "nResultYear" );
        $nMonth 			= Params::get( "nResultMonth" );
        $nIDObject 			= Params::get( "nResultIDObject" );
        $sInvalidateDate 	= Params::get( "sInvalidateDate" );
        $nInvalidateDate  	= 0;

        if( empty( $nYear ) )
            throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
        if( empty( $nMonth ) )
            throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
        if( empty( $nIDObject ) )
            throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
        if( empty( $sInvalidateDate ) )
            throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );

        $aMatches = array();

        if( !preg_match( "/^(\d{1,2})\.(\d{1,2})\.(\d{4}) (\d{2})\:(\d{2})$/", $sInvalidateDate, $aMatches ) )
            throw new Exception( "Невалидна дата!", DBAPI_ERR_INVALID_PARAM );

        if( count( $aMatches ) != 6 )
            throw new Exception( "Невалидна дата!", DBAPI_ERR_INVALID_PARAM );

        $nInvalidateDate = mktime( $aMatches[4], $aMatches[5], 0, $aMatches[2], $aMatches[1], $aMatches[3] );

        if( date( "Y", $nInvalidateDate ) != $nYear )
            throw new Exception( "Годината на графика трябва да съвпада с годината на датата на валидиране!", DBAPI_ERR_INVALID_PARAM );
        if( date( "m", $nInvalidateDate ) != $nMonth )
            throw new Exception( "Месеца на графика трябва да съвпада с месеца на датата на валидиране!", DBAPI_ERR_INVALID_PARAM );

        $oDuty = new DBObjectDuty();
        $oDuty->invalidate( $nIDObject, $nInvalidateDate );

        $this->result( $oResponse );
    }
}

?>
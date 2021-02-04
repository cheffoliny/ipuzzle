<?php

require_once("pdf/pdf_person_leave.php");

class ApiSetupPersonLeave
{
    // remote method
    public function init(DBResponse $oResponse)
    {
        global $db_personnel;
        $aParams = Params::getAll();

        $oDBPersonnel = new DBPersonnel();
        $oDBCodeLeave = new DBCodeLeave();
        $oDBHolidays = new DBHolidays();
//			$oDBPersonLeaves 	= new DBBase2( $db_personnel, "person_leaves" );
        $oDBPersonLeaves = new DBPersonLeaves();

        $nID = Params::get('nID');
        $nIDPerson = Params::get('id_person');



//			$oResponse->SetHiddenParam( "nID", 			$nID 		);
//			$oResponse->SetHiddenParam( "nIDPerson", 	$nIDPerson 	);

        //Initial Data
        $bRightResolute = false;
        if (!empty($_SESSION['userdata']['access_right_levels'])) {
            if (in_array('setup_person_leave_resolution', $_SESSION['userdata']['access_right_levels'])) {
                $bRightResolute = true;
            }
        }

        $bRightChange = false;
        if (!empty($_SESSION['userdata']['access_right_levels'])) {
            if (in_array('setup_person_leave_change', $_SESSION['userdata']['access_right_levels'])) {
                $bRightChange = true;
            }
        }

        if (empty($nIDPerson)) throw new Exception("Служителя не е намерен!", DBAPI_ERR_INVALID_PARAM);

        $aPerson = $oDBPersonnel->getRecord($nIDPerson);
        if (empty($aPerson)) {
            throw new Exception("Служителя не е намерен!", DBAPI_ERR_INVALID_PARAM);
        }


        $sPersonNames = $aPerson['fname'] . " " . $aPerson['mname'] . " " . $aPerson['lname'];
        $aPersonSameOffice = $oDBPersonnel->getPersonnelsByIDOffice5($aPerson['id_office'], $nIDPerson);
        $aCodesLeave = $oDBCodeLeave->getCodesLeave2();

//			throw new Exception(ArrayToString($aPersonSameOffice));
        $aData = array();
        $aData['id'] = $nID;
        $aData['id_person'] = $nIDPerson;
        $aData['sPersonName'] = $sPersonNames;
        $aData['bRightResolute'] = $bRightResolute;
        $aData['bRightChange'] = $bRightChange;

        if (empty($nID)) {
            $aData['nLeaveNum'] = 0;
            $aData['nApplicationDaysOffer'] = 1;
            $aData['sLeaveFromOffer'] = $oDBHolidays->getNextWorkday();
            $aData['nApplicationDays'] = 0;
            $aData['sLeaveFrom'] = "0000-00-00 00:00:00";
            $aData['nIsConfirm'] = 0;
            $aData['nIsAllowed'] = 0;
            $aData['sApplicationDate'] = "0000-00-00 00:00:00";
            $aData['sApplicationConDate'] = "0000-00-00 00:00:00";
            $aData['forYear'] = $oDBPersonLeaves->getYearsWithRemainingDays($nIDPerson);

            //за да може да се пускат отпуски за текушата година които са допълнителни при свършена отпуска от текущата!!!
            if(!in_array(date("Y"),$aData['forYear'])) {
                $aData['forYear'][date("Y")] = date("Y");
                arsort($aData['forYear']);
            }



            $oResponse->setFormElement("form1", "id", array(), $aData["id"]);
            $oResponse->setFormElement("form1", "sPersonName", array(), $aData['sPersonName']);
            $oResponse->setFormElement("form1", "nApplicationDaysOffer", array(), $aData["nApplicationDaysOffer"]);
            $oResponse->setFormElement("form1", "sLeaveFromOffer", array(), mysqlDateToJsDate($aData["sLeaveFromOffer"]));
            $oResponse->setFormElement("form1", "nIDPersonSubstitute", array());
            $oResponse->setFormElement("form1", "is_confirmed", array(), 0);
            $oResponse->setFormElementChild('form1', 'nIDPersonSubstitute', array("value" => 0), '--Изберете--');


            $oResponse->setFormElement("form1", "nIDCodeLeave", array());
            $oResponse->setFormElementChild('form1', 'nIDCodeLeave', array("value" => 0), '--Изберете--');

            $oResponse->setFormElement("form1", "forYear", array());

            foreach($aData['forYear'] as $year) {
                $oResponse->setFormElementChild('form1', 'forYear', array("value" => $year), $year);
            }

//				throw new Exception(ArrayToString($aCodesLeave));

            foreach ($aPersonSameOffice as $id_person => $person) {
                $oResponse->setFormElementChild('form1', 'nIDPersonSubstitute', array("value" => $id_person), $person);
            }

            foreach ($aCodesLeave as $id_code_leave => $name) {
                $oResponse->setFormElementChild('form1', 'nIDCodeLeave', array("value" => $id_code_leave), $name);
            }

//				$oResponse->SetFlexControl( "sLeaveType" );
//				$oResponse->SetFlexControlDefaultValue( "sLeaveType", "id", "due" );
//				$oResponse->SetFlexControl( "nIDPersonSubstitute" );
//				$oResponse->SetFlexControlDefaultValue( "nIDPersonSubstitute", "id", "0" );
//				$oResponse->SetFlexControl( "nIDCodeLeave" );
//				$oResponse->SetFlexControlDefaultValue( "nIDCodeLeave", "id", "0" );
        } else {
            $aPersonLeave = $oDBPersonLeaves->getRecord($nID);
//            throw new Exception(ArrayToString($aPersonLeave));
//				$remaining_days = $oDBPersonLeaves->getAllRemainingDays($nIDPerson);
//
//                if($aPersonLeave['application_days'] > $remaining_days['remaining_days']) {
//                    throw new Exception( "Нямате достатъчно дни!", DBAPI_ERR_INVALID_PARAM );
//                }

            if (empty($aPersonLeave)) {
                throw new Exception("Записът не съществува!", DBAPI_ERR_INVALID_PARAM);
            }

            $aData['nLeaveNum'] = $aPersonLeave['leave_num'];
            $aData['nApplicationDaysOffer'] = $aPersonLeave['application_days_offer'];
            $aData['sLeaveFromOffer'] = mysqlDateToJsDate(substr($aPersonLeave['leave_from'], 0, 10));
            $aData['nApplicationDays'] = !empty($aPersonLeave['application_days']) ? $aPersonLeave['application_days'] : $aData['nApplicationDaysOffer'];
            $aData['sLeaveFrom'] = $aPersonLeave['res_leave_from'] == "0000-00-00 00:00:00" ? mysqlDateToJsDate(substr($aPersonLeave['leave_from'], 0, 10)) : mysqlDateToJsDate(substr($aPersonLeave['res_leave_from'], 0, 10));
            $aData['nIsConfirm'] = $aPersonLeave['is_confirm'];
            $aData['nIsAllowed'] = $aPersonLeave['application_days'] != 0 ? 1 : 0;
            $aData['sApplicationDate'] = $aPersonLeave['date'];
            $aData['sApplicationConDate'] = $aPersonLeave['confirm_time'];
            $aData['confirm_time'] = $aPersonLeave['confirm_time'] == "0000-00-00 00:00:00" ? "" : date("d.m.Y",strtotime($aPersonLeave['confirm_time']));
            $aData['created_time'] = $aPersonLeave['date'] == "0000-00-00 00:00:00" ? "" : date("d.m.Y",strtotime($aPersonLeave['date']));

            $oResponse->setFormElement("form1", "sPersonName", array(), $aData['sPersonName']);
            $oResponse->setFormElement("form1", "nLeaveNum", array(), $aData['nLeaveNum']);
            $oResponse->setFormElement("form1", "nLeaveNumRes", array(), $aData['nLeaveNum']);
            $oResponse->setFormElement("form1", "nApplicationDaysOffer", array(), $aData['nApplicationDaysOffer'] );
            $oResponse->setFormElement("form1", "nApplicationDays", array(), $aData['nApplicationDays'] );
            $oResponse->setFormElement("form1", "sLeaveFromOffer", array(), $aData['sLeaveFromOffer']);
            $oResponse->setFormElement("form1", "sLeaveFrom", array(), $aData['sLeaveFromOffer']);
            $oResponse->setFormElement("form1", "is_confirmed", array(), $aData['nIsConfirm'] ? 1 : 0);

            $oResponse->setFormElement("form1", "confirm_time", array("readonly"=>"readonly"), $aData['confirm_time']);
            $oResponse->setFormElement("form1", "created_time", array("readonly"=>"readonly"), $aData['created_time']);

            if($aData['nIsAllowed']) {
                $oResponse->setFormElement("form1", "nIsAllowed", array('checked'=>'checked'));
            }
            $oResponse->setFormElement("form1", "sLeaveType", array());
            $oResponse->setFormElementChild("form1", "sLeaveType", array('value'=> $aPersonLeave['leave_types']),$aPersonLeave['leave_types'] == 'due' ? 'Платен' : 'Неплатен' );


            $oResponse->setFormElement("form1", "nIDPersonSubstitute", array());
            $oResponse->setFormElementChild('form1', 'nIDPersonSubstitute', array("value" => 0), '--Изберете--');

            foreach ($aPersonSameOffice as $id_person => $person) {
                if ($aPersonLeave['id_person_substitute'] == $id_person) {
                    $oResponse->setFormElementChild('form1', 'nIDPersonSubstitute', array("value" => $id_person, "selected" => 'selected'), $person);
                } else {
                    $oResponse->setFormElementChild('form1', 'nIDPersonSubstitute', array("value" => $id_person), $person);
                }
            }
            $oResponse->setFormElement("form1", "nIDCodeLeave", array());
            $oResponse->setFormElementChild('form1', 'nIDCodeLeave', array("value" => 0), '--Изберете--');

//				throw new Exception(ArrayToString($aCodesLeave));
            foreach ($aCodesLeave as $id_code_leave => $name) {
                if ($aPersonLeave['id_code_leave'] == $id_code_leave) {
                    $oResponse->setFormElementChild('form1', 'nIDCodeLeave', array("value" => $id_code_leave, 'selected' => 'selected'), $name);
                } else {
                    $oResponse->setFormElementChild('form1', 'nIDCodeLeave', array("value" => $id_code_leave), $name);
                }
            }

            $oResponse->setFormElement("form1", "forYear", array());
            $oResponse->setFormElementChild('form1', 'forYear', array("value" => $aPersonLeave['for_year']), $aPersonLeave['for_year']);

//				$oResponse->SetFlexControl( "sLeaveType" );
//				$oResponse->SetFlexControlDefaultValue( "sLeaveType", "id", $aPersonLeave['leave_types'] );
//				$oResponse->SetFlexControl( "nIDPersonSubstitute" );
//				$oResponse->SetFlexControlDefaultValue( "nIDPersonSubstitute", "id", $aPersonLeave['id_person_substitute'] );
//				$oResponse->SetFlexControl( "nIDCodeLeave" );
//				$oResponse->SetFlexControlDefaultValue( "nIDCodeLeave", "id", $aPersonLeave['id_code_leave'] );
        }
        //End Initial Data

//			$oResponse->SetFlexVar( "aData", 				$aData 				);
//			$oResponse->SetFlexVar( "aPersonSameOffice", 	$aPersonSameOffice 	);
//			$oResponse->SetFlexVar( "aCodesLeave",			$aCodesLeave 		);

        $oResponse->printResponse();
    }

    public function changeLeaveType(DBResponse $oResponse) {

        $oDBCodeLeave = new DBCodeLeave();
        $oDBPersonLeaves = new DBPersonLeaves();


        $nIDCodeLeave = Params::get('nIDCodeLeave',0);
        $nIDPerson = Params::get('id_person',0);

        $aData['forYear'] = $oDBPersonLeaves->getYearsWithRemainingDays($nIDPerson);

        //за да може да се пускат отпуски за текушата година които са допълнителни при свършена отпуска от текущата!!!
        if(!in_array(date("Y"),$aData['forYear'])) {
            $aData['forYear'][date("Y")] = date("Y");
            arsort($aData['forYear']);
        }

        $aLeaveTypes = array(
            'due' => 'Платен',
            'unpaid' => 'Неплатен',
        );

        if(!empty($nIDCodeLeave)) {

            $aCodeLeave = $oDBCodeLeave->getRecord($nIDCodeLeave);

            $oResponse->setFormElement("form1", "forYear", array());
            if($aCodeLeave['is_due_leave']) {
                //пълни се полето за годината в полагаемия отпуска
                foreach($aData['forYear'] as $year) {
                    $oResponse->setFormElementChild('form1', 'forYear', array("value" => $year), $year);
                }
            } else {
                //Дават се за избор текущата и предходната година за отпуск които не се взема от полагаемия!
                //дава се предходната година ако в началото на годината се пуска отпуска за миналата година която е неплатена
                $oResponse->setFormElementChild('form1', 'forYear', array("value" => date("Y")), date("Y"));
                $oResponse->setFormElementChild('form1', 'forYear', array("value" => date("Y")-1), date("Y")-1);
            }

            $oResponse->setFormElement("form1", "sLeaveType", array());
            $oResponse->setFormElementChild('form1', 'sLeaveType', array("value" => 0), '--Изберете--');

            foreach ($aLeaveTypes as $sType => $name) {

                //ЗАРАДИ РАЗЛИКАТА В ИМЕНАТА В ОТПУСКИТЕ Е due в code_leave e paid !!!!! ПРОСТОТИЯ
                if ($aCodeLeave['leave_type'] == 'paid' && $sType == 'due') {
                    $oResponse->setFormElementChild('form1', 'sLeaveType', array("value" => $sType, 'selected' => 'selected'), $name);
                }

                if ($aCodeLeave['leave_type'] == $sType) {
                    $oResponse->setFormElementChild('form1', 'sLeaveType', array("value" => $sType, 'selected' => 'selected'), $name);
                } else {
                    $oResponse->setFormElementChild('form1', 'sLeaveType', array("value" => $sType), $name);
                }
            }

        }



        $oResponse->printResponse();
    }

    public function initFlex(DBResponse $oResponse)
    {
        global $db_personnel;
        $aParams = Params::getAll();

        $oDBPersonnel = new DBPersonnel();
        $oDBCodeLeave = new DBCodeLeave();
        $oDBHolidays = new DBHolidays();
//			$oDBPersonLeaves 	= new DBBase2( $db_personnel, "person_leaves" );
        $oDBPersonLeaves = new DBPersonLeaves();

        $nID = Params::get('id');
        $nIDPerson = Params::get('id_person');

        $oResponse->SetHiddenParam("nID", $nID);
        $oResponse->SetHiddenParam("nIDPerson", $nIDPerson);

        //Initial Data
        $bRightResolute = false;
        if (!empty($_SESSION['userdata']['access_right_levels'])) {
            if (in_array('setup_person_leave_resolution', $_SESSION['userdata']['access_right_levels'])) {
                $bRightResolute = true;
            }
        }

        $bRightChange = false;
        if (!empty($_SESSION['userdata']['access_right_levels'])) {
            if (in_array('setup_person_leave_change', $_SESSION['userdata']['access_right_levels'])) {
                $bRightChange = true;
            }
        }

        if (empty($nIDPerson)) throw new Exception("Служителя не е намерен!", DBAPI_ERR_INVALID_PARAM);

        $aPerson = $oDBPersonnel->getRecord($nIDPerson);
        if (empty($aPerson)) {
            throw new Exception("Служителя не е намерен!", DBAPI_ERR_INVALID_PARAM);
        }

        //Default Value
        $aNullElement = array("0" => array("id" => "0", "label" => "--- Изберете ---"));
        //End Default Value

        $sPersonNames = $aPerson['fname'] . " " . $aPerson['mname'] . " " . $aPerson['lname'];
        $aPersonSameOffice = $oDBPersonnel->getPersonnelsByIDOffice4($aPerson['id_office'], $nIDPerson);
        $aPersonSameOffice = array_merge($aNullElement, $aPersonSameOffice);
        $aCodesLeave = $oDBCodeLeave->getCodesLeave();
        $aCodesLeave = array_merge($aNullElement, $aCodesLeave);

        $aData = array();
        $aData['id'] = $nID;
        $aData['id_person'] = $nIDPerson;
        $aData['sPersonName'] = $sPersonNames;
        $aData['bRightResolute'] = $bRightResolute;
        $aData['bRightChange'] = $bRightChange;

        if (empty($nID)) {
            $aData['nLeaveNum'] = 0;
            $aData['nApplicationDaysOffer'] = 1;
            $aData['sLeaveFromOffer'] = $oDBHolidays->getNextWorkday();
            $aData['nApplicationDays'] = 0;
            $aData['sLeaveFrom'] = "0000-00-00 00:00:00";
            $aData['nIsConfirm'] = 0;
            $aData['nIsAllowed'] = 0;
            $aData['sApplicationDate'] = "0000-00-00 00:00:00";
            $aData['sApplicationConDate'] = "0000-00-00 00:00:00";

            $oResponse->SetFlexControl("sLeaveType");
            $oResponse->SetFlexControlDefaultValue("sLeaveType", "id", "due");
            $oResponse->SetFlexControl("nIDPersonSubstitute");
            $oResponse->SetFlexControlDefaultValue("nIDPersonSubstitute", "id", "0");
            $oResponse->SetFlexControl("nIDCodeLeave");
            $oResponse->SetFlexControlDefaultValue("nIDCodeLeave", "id", "0");
        } else {
            $aPersonLeave = $oDBPersonLeaves->getRecord($nID);
//				$remaining_days = $oDBPersonLeaves->getAllRemainingDays($nIDPerson);
//
//                if($aPersonLeave['application_days'] > $remaining_days['remaining_days']) {
//                    throw new Exception( "Нямате достатъчно дни!", DBAPI_ERR_INVALID_PARAM );
//                }

            if (empty($aPersonLeave)) {
                throw new Exception("Записът не съществува!", DBAPI_ERR_INVALID_PARAM);
            }

            $aData['nLeaveNum'] = $aPersonLeave['leave_num'];
            $aData['nApplicationDaysOffer'] = $aPersonLeave['application_days_offer'];
            $aData['sLeaveFromOffer'] = substr($aPersonLeave['leave_from'], 0, 10);
            $aData['nApplicationDays'] = $aPersonLeave['application_days'];
            $aData['sLeaveFrom'] = $aPersonLeave['res_leave_from'];
            $aData['nIsConfirm'] = $aPersonLeave['is_confirm'];
            $aData['nIsAllowed'] = $aPersonLeave['application_days'] != 0 ? 1 : 0;
            $aData['sApplicationDate'] = $aPersonLeave['date'];
            $aData['sApplicationConDate'] = $aPersonLeave['confirm_time'];

            $oResponse->SetFlexControl("sLeaveType");
            $oResponse->SetFlexControlDefaultValue("sLeaveType", "id", $aPersonLeave['leave_types']);
            $oResponse->SetFlexControl("nIDPersonSubstitute");
            $oResponse->SetFlexControlDefaultValue("nIDPersonSubstitute", "id", $aPersonLeave['id_person_substitute']);
            $oResponse->SetFlexControl("nIDCodeLeave");
            $oResponse->SetFlexControlDefaultValue("nIDCodeLeave", "id", $aPersonLeave['id_code_leave']);
        }
        //End Initial Data

        $oResponse->SetFlexVar("aData", $aData);
        $oResponse->SetFlexVar("aPersonSameOffice", $aPersonSameOffice);
        $oResponse->SetFlexVar("aCodesLeave", $aCodesLeave);

        $oResponse->printResponse();
    }

    // remote method
    public function save(DBResponse $oResponse)
    {
        global $db_personnel;
        $aParams = Params::getAll();
//        throw new Exception(jsDateToMySQLDate($aParams['sLeaveFromOffer']));
//			$oDBPersonLeaves 	= new DBBase2( $db_personnel, "person_leaves" );
        $oDBPersonLeaves = new DBPersonLeaves();
        $oDBLeaves = new DBLeaves();
        $oDBPersonnel = new DBPersonnel();
        $oDBHolidays = new DBHolidays();
        $oDBObjectDuty = new DBObjectDuty();
        $oDBOffices = new DBOffices();
        $oDBCodeLeave = new DBCodeLeave();

//			$nID 		= isset( $aParams['hiddenParams']->nID ) 		? $aParams['hiddenParams']->nID 		: 0;
//			$nIDPerson 	= isset( $aParams['hiddenParams']->nIDPerson ) 	? $aParams['hiddenParams']->nIDPerson 	: 0;

        $nID = Params::get('id');
        $nIDPerson = Params::get('id_person');

        $bIsSubstituteNeeded = $oDBPersonnel->isSubstituteNeeded($nIDPerson);
//			$nRemainLeaveDays = $oDBLeaves->getRemainingLeaveDays( substr( $aParams['sLeaveFromOffer'], 0, 4 ), $nIDPerson );
        $nRemainLeaveDays = $oDBPersonLeaves->getRemainingDays($nIDPerson,$aParams['forYear']);
        ob_toFile($nRemainLeaveDays,"leave.txt");

        //Validation
        if (empty($aParams['nApplicationDaysOffer'])) throw new Exception("Моля, въведете брой работни дни!", DBAPI_ERR_INVALID_PARAM);
        if (!is_numeric($aParams['nApplicationDaysOffer']) || $aParams['nApplicationDaysOffer'] < 1) {
            throw new Exception("Невалидна стойност за брой дни!", DBAPI_ERR_SUCCESS);
        }
        if (empty($aParams['sLeaveFromOffer'])) throw new Exception("Невалидна дата!", DBAPI_ERR_INVALID_PARAM);
        if ($bIsSubstituteNeeded) {
            if (empty($aParams['nIDPersonSubstitute'])) throw new Exception("Моля, въведете заместник!", DBAPI_ERR_INVALID_PARAM);
        }
        if (empty($aParams['nIDCodeLeave'])) throw new Exception("Моля, въведете чл. от КТ!", DBAPI_ERR_INVALID_PARAM);

        $aTmpLeave = $oDBCodeLeave->getRecord($aParams['nIDCodeLeave']);

//        if ($aParams['sLeaveType'] == "due") {

        $aTmpLeave = $oDBCodeLeave->getRecord($aParams['nIDCodeLeave']);

        if($aTmpLeave['is_due_leave']) {
            if ($aParams['nApplicationDaysOffer'] > $nRemainLeaveDays['remaining_days']) {
                throw new Exception("Въведения брой дни е над лимита!", DBAPI_ERR_INVALID_PARAM);
            }
        }
//        }
        //End Validation

        $aData = array();
        $aData['id'] = $nID;
        $aData['id_person'] = $nIDPerson;
        $aData['type'] = "application";
        $aData['date'] = date("Y-m-d H:i:s");
        $aData['leave_types'] = $aParams['sLeaveType'];
        $aData['leave_from'] = $oDBHolidays->getNextWorkday(jsDateToMySQLDate($aParams['sLeaveFromOffer'])) . " 00:00:00";
        $aData['leave_to'] = $this->calcEndDate(jsDateToMySQLDate($aParams['sLeaveFromOffer']), $aParams['nApplicationDaysOffer']) . " 23:59:59";
        $aData['year'] = substr($aData['leave_from'], 0, 4);
        $aData['for_year'] = $aParams['forYear'];
        $aData['application_days_offer'] = $aParams['nApplicationDaysOffer'];
        $aData['id_person_substitute'] = $aParams['nIDPersonSubstitute'];
        $aData['id_code_leave'] = $aParams['nIDCodeLeave'];

        $aData['application_days'] = 0;
        $aData['res_leave_from'] = "0000-00-00 00:00:00";
        $aData['res_leave_to'] = "0000-00-00 00:00:00";
        $aData['is_confirm'] = 0;
        $aData['confirm_user'] = 0;
        $aData['confirm_time'] = "0000-00-00 00:00:00";

        if (empty($nID)) {
            $aData['created_user'] = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;

            //Get a Number
            $aIDOffice = $oDBPersonnel->getPersonnelOffice($nIDPerson);
            $nIDOffice = (!empty($aIDOffice) && isset($aIDOffice['id_office'])) ? $aIDOffice['id_office'] : 0;
            $nIDFirm = $oDBOffices->getFirmByIDOffice($nIDOffice);
            //$nIDFirm = ( !empty( $aIDFirm ) && isset( $aIDFirm['id_firm'] ) ) ? $aIDFirm['id_firm'] : 0; // WTF?!!

            $oDBPersonLeavesNumbers = new DBPersonLeavesNumbers();
            $nLeaveNumber = $oDBPersonLeavesNumbers->getNumberByFirm($nIDFirm, $aData['year']);

            //throw new Exception(ArrayToString($aIDFirm), DBAPI_ERR_INVALID_PARAM );
            //End Get a Number
            $aData['leave_num'] = $nLeaveNumber;
        }
        //throw new Exception(ArrayToString($aData), DBAPI_ERR_INVALID_PARAM );

        //Check Overlap
        $nIsOverlapped = $oDBLeaves->isThereApplication($nIDPerson, $aData['leave_from'], $aData['leave_to'], $nID);
        if ($nIsOverlapped) throw new Exception("Съществува молба / болничен, чиито дати се застъпват с въведените!");
        //End Check Overlap

        //Fix Schedules
        $aObjectsWithShifts = $oDBObjectDuty->getObjectsForPersonShifts($nIDPerson, $aData['leave_from'], $aData['leave_to']);

        if (!empty($aObjectsWithShifts)) {
            $sErrorMessage = "Служителя има смени в следните обекти:";
            foreach ($aObjectsWithShifts as $aObjectsNames) {
                $sErrorMessage .= "\n " . $aObjectsNames['name'];
            }

            throw new Exception($sErrorMessage, DBAPI_ERR_INVALID_PARAM);
        }
        //End Fix Schedules

        // Планиран отпуск - изчистване!
        $oDBObjectDuty->clearPlan($nIDPerson, $aData['leave_from'], $aData['leave_to']);
        $oDBObjectDuty->createForLeave($nIDPerson, $aData['leave_from'], $aData['leave_to']);
        //$bResult	= $oDBObjectDuty->checkForPlan($nIDPerson, $aData['leave_from'], $aData['leave_to']);


        $nResult = $oDBPersonLeaves->update($aData);
        if ($nResult != DBAPI_ERR_SUCCESS) {
            throw new Exception("Грешка при запазване на данните!", $nResult);
        }

        $nID = $aData['id'];

//        Params::set('id', $nID);
//        Params::set('id_person', $nIDPerson);

        if(isset($_SESSION['leave_tmp'])) {
            unset($_SESSION['leave_tmp']);
        }
        $_SESSION['leave_tmp']['id'] = $nID;
        $_SESSION['leave_tmp']['id_person'] = $nIDPerson;

    }

    public function saveFlex(DBResponse $oResponse)
    {
        global $db_personnel;
        $aParams = Params::getAll();

//			$oDBPersonLeaves 	= new DBBase2( $db_personnel, "person_leaves" );
        $oDBPersonLeaves = new DBPersonLeaves();
        $oDBLeaves = new DBLeaves();
        $oDBPersonnel = new DBPersonnel();
        $oDBHolidays = new DBHolidays();
        $oDBObjectDuty = new DBObjectDuty();
        $oDBOffices = new DBOffices();

        $nID = isset($aParams['hiddenParams']->nID) ? $aParams['hiddenParams']->nID : 0;
        $nIDPerson = isset($aParams['hiddenParams']->nIDPerson) ? $aParams['hiddenParams']->nIDPerson : 0;

        $bIsSubstituteNeeded = $oDBPersonnel->isSubstituteNeeded($nIDPerson);
//			$nRemainLeaveDays = $oDBLeaves->getRemainingLeaveDays( substr( $aParams['sLeaveFromOffer'], 0, 4 ), $nIDPerson );
        $nRemainLeaveDays = $oDBPersonLeaves->getAllRemainingDays($nIDPerson);

        //Validation
        if (empty($aParams['nApplicationDaysOffer'])) throw new Exception("Моля, въведете брой работни дни!", DBAPI_ERR_INVALID_PARAM);
        if (!is_numeric($aParams['nApplicationDaysOffer']) || $aParams['nApplicationDaysOffer'] < 1) {
            throw new Exception("Невалидна стойност за брой дни!", DBAPI_ERR_SUCCESS);
        }
        if (empty($aParams['sLeaveFromOffer'])) throw new Exception("Невалидна дата!", DBAPI_ERR_INVALID_PARAM);
        if ($bIsSubstituteNeeded) {
            if (empty($aParams['nIDPersonSubstitute'])) throw new Exception("Моля, въведете заместник!", DBAPI_ERR_INVALID_PARAM);
        }
        if (empty($aParams['nIDCodeLeave'])) throw new Exception("Моля, въведете чл. от КТ!", DBAPI_ERR_INVALID_PARAM);

        if ($aParams['sLeaveType'] == "due") {
            if ($aParams['nApplicationDaysOffer'] > $nRemainLeaveDays['remaining_days']) {
                throw new Exception("Въведения брой дни е над лимита!", DBAPI_ERR_INVALID_PARAM);
            }
        }
        //End Validation

        $aData = array();
        $aData['id'] = $nID;
        $aData['id_person'] = $nIDPerson;
        $aData['type'] = "application";
        $aData['date'] = date("Y-m-d H:i:s");
        $aData['leave_types'] = $aParams['sLeaveType'];
        $aData['leave_from'] = $oDBHolidays->getNextWorkday($aParams['sLeaveFromOffer']) . " 00:00:00";
        $aData['leave_to'] = $this->calcEndDate($aData['leave_from'], $aParams['nApplicationDaysOffer']) . " 23:59:59";
        $aData['year'] = substr($aData['leave_from'], 0, 4);
        $aData['application_days_offer'] = $aParams['nApplicationDaysOffer'];
        $aData['id_person_substitute'] = $aParams['nIDPersonSubstitute'];
        $aData['id_code_leave'] = $aParams['nIDCodeLeave'];

        $aData['application_days'] = 0;
        $aData['res_leave_from'] = "0000-00-00 00:00:00";
        $aData['res_leave_to'] = "0000-00-00 00:00:00";
        $aData['is_confirm'] = 0;
        $aData['confirm_user'] = 0;
        $aData['confirm_time'] = "0000-00-00 00:00:00";

        if (empty($nID)) {
            $aData['created_user'] = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;

            //Get a Number
            $aIDOffice = $oDBPersonnel->getPersonnelOffice($nIDPerson);
            $nIDOffice = (!empty($aIDOffice) && isset($aIDOffice['id_office'])) ? $aIDOffice['id_office'] : 0;
            $nIDFirm = $oDBOffices->getFirmByIDOffice($nIDOffice);
            //$nIDFirm = ( !empty( $aIDFirm ) && isset( $aIDFirm['id_firm'] ) ) ? $aIDFirm['id_firm'] : 0; // WTF?!!

            $oDBPersonLeavesNumbers = new DBPersonLeavesNumbers();
            $nLeaveNumber = $oDBPersonLeavesNumbers->getNumberByFirm($nIDFirm, $aData['year']);
            //throw new Exception(ArrayToString($aIDFirm), DBAPI_ERR_INVALID_PARAM );
            //End Get a Number
            $aData['leave_num'] = $nLeaveNumber;
        }
        //throw new Exception(ArrayToString($aData), DBAPI_ERR_INVALID_PARAM );

        //Check Overlap
        $nIsOverlapped = $oDBLeaves->isThereApplication($nIDPerson, $aData['leave_from'], $aData['leave_to'], $nID);
        if ($nIsOverlapped) throw new Exception("Съществува молба / болничен, чиито дати се застъпват с въведените!");
        //End Check Overlap

        //Fix Schedules
        $aObjectsWithShifts = $oDBObjectDuty->getObjectsForPersonShifts($nIDPerson, $aData['leave_from'], $aData['leave_to']);

        if (!empty($aObjectsWithShifts)) {
            $sErrorMessage = "Служителя има смени в следните обекти:";
            foreach ($aObjectsWithShifts as $aObjectsNames) {
                $sErrorMessage .= "\n " . $aObjectsNames['name'];
            }

            throw new Exception($sErrorMessage, DBAPI_ERR_INVALID_PARAM);
        }
        //End Fix Schedules

        // Планиран отпуск - изчистване!
        $oDBObjectDuty->clearPlan($nIDPerson, $aData['leave_from'], $aData['leave_to']);
        $oDBObjectDuty->createForLeave($nIDPerson, $aData['leave_from'], $aData['leave_to']);
        //$bResult	= $oDBObjectDuty->checkForPlan($nIDPerson, $aData['leave_from'], $aData['leave_to']);


        $nResult = $oDBPersonLeaves->update($aData);
        if ($nResult != DBAPI_ERR_SUCCESS) {
            throw new Exception("Грешка при запазване на данните!", $nResult);
        }

        $nID = $aData['id'];

        Params::set('id', $nID);
        Params::set('id_person', $nIDPerson);

        $this->init($oResponse);
    }

    // remote method
    public function confirm(DBResponse $oResponse)
    {
        global $db_personnel;
        $aParams = Params::getAll();
//        throw new Exception(ArrayToString($aParams));

//			$oDBPersonLeaves 	= new DBBase2( $db_personnel, "person_leaves" );
        $oDBPersonLeaves = new DBPersonLeaves();
        $oDBLeaves = new DBLeaves();
        $oDBObjectPersonnel = new DBObjectPersonnel();
        $oDBObjectDuty = new DBObjectDuty();
        $oDBSalaryEarning = new DBSalaryEarning();
        $oDBSalary = new DBSalary();
        $oDBPersonnel = new DBPersonnel();
        $oDBHolidays = new DBHolidays();
        $oDBOffices = new DBOffices();
        $оDBCodeLeave = new DBCodeLeave();

//        $nID = isset($aParams['hiddenParams']->nID) ? $aParams['hiddenParams']->nID : 0;
//        $nIDPerson = isset($aParams['hiddenParams']->nIDPerson) ? $aParams['hiddenParams']->nIDPerson : 0;

        $nID = Params::get('nID',0);
        $nIDPerson = Params::get('id_person',0);

        $aCodeLeaveInfo = $оDBCodeLeave->getRecord($aParams['nIDCodeLeave']);

        $aMonthStat = array();    //Разбивка на работните дни по месеци.
        $bIsSubstituteNeeded = $oDBPersonnel->isSubstituteNeeded($nIDPerson);
//			$nRemainLeaveDays = $oDBLeaves->getRemainingLeaveDays( substr( $aParams['sLeaveFromOffer'], 0, 4 ), $nIDPerson, $nID );
        $nRemainLeaveDays = $oDBPersonLeaves->getRemainingDays($nIDPerson,$aParams['forYear']);
//        throw new Exception(ArrayToString($nRemainLeaveDays));

//            throw new Exception($nID);
//            throw new Exception(ArrayToString($aParams));

        //Validation
        if (empty($aParams['nApplicationDaysOffer'])) throw new Exception("Моля, въведете брой работни дни!", DBAPI_ERR_INVALID_PARAM);
        if (!is_numeric($aParams['nApplicationDaysOffer']) || $aParams['nApplicationDaysOffer'] < 1) {
            throw new Exception("Невалидна стойност за брой дни!", DBAPI_ERR_SUCCESS);
        }
        if (empty($aParams['sLeaveFromOffer'])) throw new Exception("Невалидна дата!", DBAPI_ERR_INVALID_PARAM);
        if ($bIsSubstituteNeeded) {
            if (empty($aParams['nIDPersonSubstitute'])) throw new Exception("Моля, въведете заместник!", DBAPI_ERR_INVALID_PARAM);
        }
        if (empty($aParams['nIDCodeLeave'])) throw new Exception("Моля, въведете чл. от КТ!", DBAPI_ERR_INVALID_PARAM);

        if ($aParams['nIsAllowed']) {
            if (empty($aParams['nApplicationDays'])) throw new Exception("Моля, въведете брой работни дни!", DBAPI_ERR_INVALID_PARAM);
            if (!is_numeric($aParams['nApplicationDays']) || $aParams['nApplicationDays'] < 1) {
                throw new Exception("Невалидна стойност за брой дни!", DBAPI_ERR_SUCCESS);
            }
            if (empty($aParams['sLeaveFrom'])) throw new Exception("Невалидна дата!", DBAPI_ERR_INVALID_PARAM);
        }

        if($aParams['nApplicationDaysOffer'] < $aParams['nApplicationDays']) {
            throw new Exception("Въведения брой дни е над исканите дни за отпуска!", DBAPI_ERR_INVALID_PARAM);
        }

        if($aCodeLeaveInfo['is_due_leave']) {
//                throw new Exception( $aParams['nApplicationDaysOffer'], DBAPI_ERR_INVALID_PARAM );

//            if ($aParams['nApplicationDaysOffer'] > $nRemainLeaveDays['remaining_days']) {
//                throw new Exception("Въведения брой дни е над лимита за годината!", DBAPI_ERR_INVALID_PARAM);
//            }

            $aOldTmpLeave = array();
            if($nID) {
                $aOldTmpLeave = $oDBPersonLeaves->getRecord($nID);
            }

            if($aOldTmpLeave['is_confirm']) {

                $nTmpLeaveDays = $aParams['nApplicationDaysOffer']; // дните които са оставащи при рекдацията

                if ($aParams['nApplicationDays'] > ($nRemainLeaveDays['remaining_days']+$nTmpLeaveDays)) {
                    throw new Exception("Въведения брой дни е над лимита за годината!", DBAPI_ERR_INVALID_PARAM);
                }
            } else {
                if ($aParams['nApplicationDays'] > $nRemainLeaveDays['remaining_days']) {
                    throw new Exception("Въведения брой дни е над лимита за годината!", DBAPI_ERR_INVALID_PARAM);
                }
            }

        }


        //End Validation

        $aData = array();
        $aData['id'] = $nID;
        $aData['id_person'] = $nIDPerson;
        $aData['type'] = "application";
        if (empty($nID)) {
            $aData['date'] = date("Y-m-d H:i:s");
        }
        $aData['leave_types'] = $aParams['sLeaveType'];
        $aData['leave_from'] = $oDBHolidays->getNextWorkday(jsDateToMySQLDate($aParams['sLeaveFromOffer'])) . " 00:00:00";
        $aData['leave_to'] = $this->calcEndDate($aData['leave_from'], $aParams['nApplicationDaysOffer']) . " 23:59:59";


        if (empty($nID)) {
            $aData['year'] = substr($aData['leave_from'], 0, 4);
        }
        $aData['application_days_offer'] = $aParams['nApplicationDaysOffer'];
        $aData['id_person_substitute'] = $aParams['nIDPersonSubstitute'];
        $aData['id_code_leave'] = $aParams['nIDCodeLeave'];

        if ($aParams['nIsAllowed']) {

            $aData['application_days'] = $aParams['nApplicationDays'];
            $aData['res_leave_from'] = $oDBHolidays->getNextWorkday(jsDateToMySQLDate($aParams['sLeaveFrom'])) . " 00:00:00";
            $aData['res_leave_to'] = $this->calcEndDate($aData['res_leave_from'], $aParams['nApplicationDays'], $aMonthStat) . " 23:59:59";
        } else {
            $aData['application_days'] = 0;
            $aData['res_leave_from'] = "0000-00-00 00:00:00";
            $aData['res_leave_to'] = "0000-00-00 00:00:00";


        }
        $aData['is_confirm'] = 1;
        $aData['confirm_user'] = $_SESSION['userdata']['id_person'];
        $aData['confirm_time'] = date("Y-m-d H:i:s");

        if (empty($nID)) {

            $aData['created_user'] = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;

            //Get a Number
            $aIDOffice = $oDBPersonnel->getPersonnelOffice($nIDPerson);
            $nIDOffice = (!empty($aIDOffice) && isset($aIDOffice['id_office'])) ? $aIDOffice['id_office'] : 0;
            $aIDFirm = $oDBOffices->getFirmByIDOffice($nIDOffice);
            $nIDFirm = (!empty($aIDFirm) && isset($aIDFirm['id_firm'])) ? $aIDFirm['id_firm'] : 0;

            $oDBPersonLeavesNumbers = new DBPersonLeavesNumbers();
            $nLeaveNumber = $oDBPersonLeavesNumbers->getNumberByFirm($nIDFirm, $aData['year']);
            //End Get a Number
            $aData['leave_num'] = $nLeaveNumber;
        }

        //Check Overlap
        if ($aParams['nIsAllowed']) {
            $nIsOverlapped = $oDBLeaves->isThereApplication($nIDPerson, $aData['res_leave_from'], $aData['res_leave_to'], $nID);
            if ($nIsOverlapped) throw new Exception("Съществува молба / болничен, чиито дати се застъпват с въведените!");
        }
        //End Check Overlap

        //Fix Schedules
        $aObjectsWithShifts = $oDBObjectDuty->getObjectsForPersonShifts($nIDPerson, $aData['res_leave_from'], $aData['res_leave_to']);

        if (!empty($aObjectsWithShifts)) {
            $sErrorMessage = "Служителя има смени в следните обекти:";
            foreach ($aObjectsWithShifts as $aObjectsNames) {
                $sErrorMessage .= "\n " . $aObjectsNames['name'];
            }

            throw new Exception($sErrorMessage, DBAPI_ERR_INVALID_PARAM);
        }

        if (!empty($nID)) {
            $aSavedLeave = $oDBLeaves->getApplication($nID);
            if (!empty($aSavedLeave) && isset($aSavedLeave['is_confirm']) && isset($aSavedLeave['application_days'])) {
                if ($aSavedLeave['is_confirm'] == 1 && $aSavedLeave['application_days'] != 0) {
                    $sSavedLeaveFrom = $aSavedLeave['res_leave_from'];
                    $sSavedLeaveTo = $aSavedLeave['res_leave_to'];
                    $nApplicationDays = $aSavedLeave['application_days'];
                    $aSavedMonthStat = array();

                    $this->calcEndDate($sSavedLeaveFrom, $nApplicationDays, $aSavedMonthStat);


                    $nResult = $oDBObjectDuty->clearPersonLeaveForDays($nIDPerson, $sSavedLeaveFrom, $sSavedLeaveTo, $aSavedMonthStat);
//                    $oDBPersonLeaves->devalidateLastLeave($nIDPerson, date('Y', strtotime($sSavedLeaveFrom)), $nApplicationDays);
                    $oDBPersonLeaves->devalidateLastLeaveOnlyYear($nIDPerson, $aSavedLeave['for_year'], $nApplicationDays);

                    if ($nResult != DBAPI_ERR_SUCCESS) {
                        throw new Exception("Грешка при подновяване на графика!", $nResult);
                    }

                    $nResult = $oDBSalary->deleteSalaryRowsByApplication($nID);

                    if ($nResult != DBAPI_ERR_SUCCESS) {
                        throw new Exception("Грешка при коригиране на работна заплата!");
                    }
                }
            }
        }
        //End Fix Schedules
//
        $nResult = $oDBPersonLeaves->update($aData);

//            if( empty( $nID ) && !$aParams['nIsAllowed']) {
//                if($nResult) {
//                    $oDBPersonLeaves->devalidateLastLeave($nResult);
//                }
//            } else if( $nID && !$aParams['nIsAllowed']) {
//                $oDBPersonLeaves->devalidateLastLeave($nID);
//            }

//            throw new Exception( $nID , DBAPI_ERR_INVALID_PARAM );
//            throw new Exception( $aData['id_code_leave'] , DBAPI_ERR_INVALID_PARAM );

        if ($nResult != DBAPI_ERR_SUCCESS) {
            throw new Exception("Грешка при запазване на данните!", $nResult);
        }


        if ($aParams['nIsAllowed']) {
            //Fix Schedule


            $nResult = $oDBObjectDuty->putPersonLeaveForDays($nIDPerson, $aData['res_leave_from'], $aData['application_days']);

            if ($nResult != DBAPI_ERR_SUCCESS) {
                throw new Exception("Грешка при запазване на информацията по графика!", $nResult);
            }
            //End Fix Schedule

            //L1-633 Проблем с отпуските
            //ДА СЕ МАХА ОСТАВАЩИ ДНИ ЗА ГОДИНАТА КОГАТО Е НЕПЛАТЕНА

            if($aCodeLeaveInfo['is_due_leave']) {
                $oDBPersonLeaves->usePaydLeave($nIDPerson, $aParams['nApplicationDays'],$aParams['forYear']);
            }



            //Add Salary Earning
            $aCodeLeaveEarning = $oDBSalaryEarning->getLeaveEarning($aParams['sLeaveType']);

            $aPersonOffice = $oDBPersonnel->getPersonnelOffice($nIDPerson);

            if (!empty($aMonthStat)) {
                foreach ($aMonthStat as $nYearMonth => $nCount) {
                    $aDataSalary = array();
                    $aDataSalary['id'] = 0;
                    $aDataSalary['id_person'] = $nIDPerson;
                    $aDataSalary['id_office'] = (!empty($aPersonOffice) && isset($aPersonOffice['id_office'])) ? $aPersonOffice['id_office'] : 0;
                    $aDataSalary['month'] = $nYearMonth;
                    $aDataSalary['is_earning'] = 1;
                    $aDataSalary['sum'] = 0;
                    $aDataSalary['count'] = $nCount;
                    $aDataSalary['id_application'] = $aData['id'];

                    $aDataSalary['code'] = isset($aCodeLeaveEarning['code']) ? $aCodeLeaveEarning['code'] : "";
                    $aDataSalary['description'] = isset($aCodeLeaveEarning['name']) ? $aCodeLeaveEarning['name'] : "";

                    $nResult = $oDBSalary->update($aDataSalary);
                    if ($nResult != DBAPI_ERR_SUCCESS) {
                        throw new Exception("Грешка при нанасяне на наработки!", $nResult);
                    }
                }
            }
            //End Add Salary Earning
        }

        $nID = $aData['id'];

        if (!empty($nID)) {
            $aLv = array();
            $aLv = $oDBLeaves->getOne($nID);

            if (!empty($aLv)) {
                $from = isset($aLv['leave_from']) ? $aLv['leave_from'] : "";
                $to = isset($aLv['leave_to']) ? $aLv['leave_to'] : "";

                if (!$aParams['nIsAllowed'] && !empty($from) && !empty($to)) {
                    $oDBObjectDuty->clearPlan($nIDPerson, $from, $to);
                }
            }
        }

        Params::set('id', $nID);
        Params::set('id_person', $nIDPerson);

        if(isset($_SESSION['leave_tmp'])) {
            unset($_SESSION['leave_tmp']);
        }
        $_SESSION['leave_tmp']['id'] = $nID;
        $_SESSION['leave_tmp']['id_person'] = $nIDPerson;
    }

    public function result(DBResponse $oResponse)
    {
        $aParams = Params::getAll();

        $oDBLeaves = new DBLeaves();

        $nID = isset($aParams['id']) ? $aParams['id'] : 0;

        if ($aParams['api_action'] == "export_to_pdf") {
            $aPDFData = array();
            $aPDFData = $oDBLeaves->getLeavePDFData($nID);

            $aParams['PDFData'] = $aPDFData;

            $personLeavePDF = new personLeavePDF("L");
            $personLeavePDF->PrintReport($aParams);
        }

        $oResponse->printResponse("Молба за Отпуск", "PersonLeave");
    }

    public function printPDF(DBResponse $oResponse)
    {

        $aParams = Params::getAll();
        $oDBLeaves = new DBLeaves();
        $nID = isset($aParams['nID']) ? $aParams['nID'] : 0;

//        if ($aParams['api_action'] == "export_to_pdf") {
        $aPDFData = array();
        $aPDFData = $oDBLeaves->getLeavePDFData($nID);

        $aParams['PDFData'] = $aPDFData;

        $personLeavePDF = new personLeavePDF("L");
        $personLeavePDF->PrintReport($aParams);
//        }

        $oResponse->printResponse("Молба за Отпуск", "PersonLeave");

    }

    public function calcEndDate($sStartDate, $nDays, &$aMonthStat = array())
    {
        $oDBHolidays = new DBHolidays();

        //Initial Data
        $aStartDate = explode("-", $sStartDate);
        if (!isset($aStartDate[0]) || !isset($aStartDate[1]) || !isset($aStartDate[2])) {
            return "0000-00-00";
        } else {
            $nYear = ( int )$aStartDate[0];
            $nMonth = ( int )$aStartDate[1];
            $nDay = ( int )$aStartDate[2];
        }

        $aMonthStat = array();

        $nDaysInMonth = ( int )date("t", mktime(0, 0, 0, $nMonth, $nDay, $nYear));
        $sYearMonthKey = $nYear . (strlen($nMonth) < 2 ? ("0" . $nMonth) : $nMonth);
        $aMonthStat[$sYearMonthKey] = 0;
        //End Initial Data

        $nIteration = 0;
        do {
            $nMyWeekday = ( int )date("w", mktime(0, 0, 0, $nMonth, $nDay, $nYear));

            if ($nMyWeekday == 0 || $nMyWeekday == 6) {
                if ($oDBHolidays->isWorkday($nDay, $nMonth, $nYear)) {
                    $nIteration++;
                    $aMonthStat[$sYearMonthKey]++;
                }
            } else {
                if (!$oDBHolidays->isHoliday($nDay, $nMonth , $nYear) && !$oDBHolidays->isRestday($nDay, $nMonth, $nYear)) {
                    $nIteration++;
                    $aMonthStat[$sYearMonthKey]++;
                }
            }

            //Progress Date
            if ($nIteration < $nDays) {
                $nDay++;
                if ($nDay > $nDaysInMonth) {
                    $nDay = 1;
                    $nMonth++;
                    if ($nMonth > 12) {
                        $nMonth = 1;
                        $nYear++;
                    }

                    $nDaysInMonth = ( int )date("t", mktime(0, 0, 0, $nMonth, $nDay, $nYear));
                    $sYearMonthKey = $nYear . (strlen($nMonth) < 2 ? ("0" . $nMonth) : $nMonth);
                    $aMonthStat[$sYearMonthKey] = 0;
                }
            }
            //End Progress Date
        } while ($nIteration < $nDays);

        return $nYear . "-" . (strlen($nMonth) < 2 ? ("0" . $nMonth) : $nMonth) . "-" . (strlen($nDay) < 2 ? ("0" . $nDay) : $nDay);
    }

    public function test(DBResponse $oResponse)
    {
//			$oResponse->setAlert('Test');

        $oResponse->printResponse();

    }
}

?>
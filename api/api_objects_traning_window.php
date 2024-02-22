<?php
class ApiObjectsTraningWindow {

    public function result(DBResponse $oResponse)
    {

        global $db_sod, $db_name_sod, $db_personnel, $db_name_personnel;
        $oBase = new DBBase2($db_sod,'sod');

        $nIDPerson  = Params::get('nIDPerson',  "");
        $nIDOffice  = Params::get("nIDOffice",  "");
        $sType      = Params::get("sType",      "");

        $sQuery = " 
            SELECT
              SQL_CALC_FOUND_ROWS
              CONCAT(vo.id, '@', p.id, '@', o.id) as id,
              0 AS cancel_checkbox,
              CONCAT(ROUND(vo.distance_to_object, 3), ' км.') AS distance_to_object,
              DATE_FORMAT( vo.created_time, '%H:%i %d.%m.%Y' ) AS sTime,
              CONCAT( p.fname, ' ', p.lname ) AS pName,
              o.num AS oNum,
              o.name AS oName
            FROM {$db_name_sod}.objects o
            LEFT JOIN {$db_name_sod}.visited_objects vo ON o.id = vo.id_object AND vo.id_person = {$nIDPerson} AND vo.to_arc = 0
            LEFT JOIN {$db_name_personnel}.personnel p ON p.id = vo.id_person
            WHERE o.id_status NOT IN(4) AND o.is_sod = 1
                
        ";

        if ( $nIDOffice > 0 ) {
            $sQuery .= " AND o.id_office =  {$nIDOffice} ";
        }

        if( $sType == 'unknown' ) {
            $sQuery .= " AND vo.id_object IS NULL ";
        } else if ($sType <> '' && $sType <> 'unknown') {
            $sQuery .= " AND vo.`type` = '{$sType}' ";
        }
//        $aWhere = array();

//        if(!empty($sDeadlineTo) && $sDeadlineTo != '0000-00-00') {
//            $aWhere[] = " DATE_FORMAT(tr.deadline, '%Y-%m-%d') BETWEEN '{$sDeadlineFrom}' AND '{$sDeadlineTo}' ";
//        }

//        $sQuery .= " GROUP BY tlc.id ";

        $oBase->getResult($sQuery, ' sTime ', DBAPI_SORT_DESC, $oResponse);

      //  $oResponse->printResponse();
        APILog::Log(222111, $sQuery);

        foreach ($oResponse->oResult->aData as $aRow) {

        }


        $oResponse->setField('cancel_checkbox', '', '');
        $oResponse->setFieldData('cancel_checkbox', 'input', array('type' => 'checkbox', 'exception' => 'false'));

        $oResponse->setField('sTime', 'Опознат на...', 'сортирай по време на опознаване', NULL, NULL, NULL, array());
        $oResponse->setField('oNum', '№ на обект', 'сортирай по Номер на обект', NULL, NULL, NULL, array());
        $oResponse->setField('oName', 'Обект', 'сортирай по Име на обект', NULL, NULL, NULL, array('class' => 'px-1'));
        $oResponse->setField('distance_to_object', 'Дистанция', 'сортирай по Дистанция', NULL, NULL, NULL, array());
        $oResponse->setField('pName', 'Опознал', 'сортирай по Опознал', NULL, NULL, NULL, array());


        $oResponse->setFormElement("form1", "sel");
        $oResponse->setFormElementChild("form1", "sel", array("value" => "mark_all"), "--- Маркирай всички ---");
        $oResponse->setFormElementChild("form1", "sel", array("value" => "unmark_all"), "--- Отмаркирай всички ---");
        $oResponse->setFormElementChild("form1", "sel", array("value" => ""), "-----------------------------------------");
        $oResponse->setFormElementChild("form1", "sel", array("value" => "del"), "Връщане на избраните задачи");

        $oResponse->printResponse();
    }


    function cancel2(DBResponse $oResponse)
    {
        global $db_sod;

        $sIDs = Params::get("sIDForCancel", 0);
        $aTmp = explode(',',$sIDs);

        foreach ($aTmp as $nID) {

            $sQuery = "
			UPDATE visited_objects
			SET to_arc = 1
			WHERE id IN({$sIDs})
			";

            $db_sod->Execute($sQuery);
        }

        $oResponse->printResponse();
    }

//    public function getWorkStatus(DBResponse $oResponse)
//    {
//        $nID = Params::get("sIDForCancel", 0);
//
//        $oLock = new DBTechLimitCards();
//        $aWork = $oLock->getWorkStatus($nID);
//        if (isset($aWork[0]['nTime']) && $aWork[0]['nTime'] > 9999) {
//            $oResponse->setFormElement('form1', 'nTimeOff', array(), '1');
//        } else {
//            $oResponse->setFormElement('form1', 'nTimeOff', array(), '0');
//        }
//        $oResponse->printResponse();
//    }

}
?>

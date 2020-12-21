<?php

class DBHoldupReasons extends DBBase2 {

    public function __construct() {
        global $db_sod;

        parent::__construct($db_sod, 'tech_reason');
    }

    public function getAllReasons() {
        $sQuery = "
					SELECT
						*
					FROM tech_reason
					WHERE to_arc = 0
			";

        return $this->select($sQuery);
    }

    public function getReasonById($idReason) {
        global $db_name_sod;
        $sQuery = "
					SELECT
						*
					FROM {$db_name_sod}.tech_reason
					WHERE
					id = {$idReason}
					AND to_arc = 0
			";

        return $this->selectOnce($sQuery);
    }

    public function isDeletable($nIDReason) {
        global $db_name_sod;
        if(empty($nIDReason) || !is_numeric($nIDReason)) {
            return false;
        }

        $sQuery = "
            SELECT
                not_deletable
            FROM {$db_name_sod}.tech_reason
            WHERE id = {$nIDReason}
        ";
        return $this->selectOnce($sQuery);
    }

    public function getReasonByType($idType) {
        $sQuery = "
					SELECT
					    id,
						name
					FROM tech_reason
					WHERE
					id_tech_timing = {$idType}
					AND to_arc = 0
			";


        return $this->select($sQuery);
    }

    public function getTechReason() {
        $sQuery = "
				SELECT id
				FROM tech_reason
				WHERE to_arc = 0
					AND from_tech_signals = 1
			";

        return $this->selectOne($sQuery);
    }

    public function isWarranty($nIDObj) {

        $oDBTechLimitCards = new DBTechLimitCards();
        $aLastService = $oDBTechLimitCards->getLastService($nIDObj, 1);

        if (!empty($nIDObj)) {

//                APILog::Log($sQuery);
//                APILog::Log(0,date('Y-d-m', strtotime($aLastService['date']))).' дата на изтичнане на гаранцията';
//                APILog::Log(0,date('Y-d-m',strtotime('today - ' . $aLastService['warranty_time'] . ' months'))).' дата последно обслужване';
//                APILog::Log(0,strtotime('today - ' . $aLastService['warranty_time'] . ' months'));
//                APILog::Log(0,strtotime($aLastService['created_time']));
//
//                APILog::Log(0,ArrayToString($data));
//                APILog::Log(0,ArrayToString($aLastService));
//                APILog::Log($aLastService['is_warranty']);

            if (!empty($aLastService['is_warranty']) && $aLastService['is_warranty'] == 1 && (strtotime('today - ' . $aLastService['warranty_time'] . ' months') <= strtotime($aLastService['created_time']))) {
//                    throw new Exception ('Obekta e v garanciq');
                return true;
            }
//                throw new Exception ('Error 1 ');
            return false;
        }
//            throw new Exception ('Error 2');
        return false;
    }

    public function isToObjectSingles($nIDReason) {
        global $db_sod_name;
        if (!empty($nIDReason)) {
            $sQuery = "
                SELECT
                    to_object_singles
                FROM {$db_sod_name}.tech_reason
                WHERE id = {$nIDReason}
                ";
            $data = $this->selectOnce($sQuery);
            if ($data['to_object_singles'] == 1) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function getReport($aParams, DBResponse $oResponse) {

        global $db_name_personnel;
        $nType = Params::get('nType');
        $nFirm = Params::get('nFirm');

        $right_edit = false;
        if (!empty($_SESSION['userdata']['access_right_levels']))
            if (in_array('holdup_reasons', $_SESSION['userdata']['access_right_levels'])) {
                $right_edit = true;
            }

        $sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						tr.id,
						tr.name,
						tr.from_tech_signals,
						CONCAT(tr.price, ' лв') as price,
						tr.operations_affect,
                        CONCAT(tr.warranty_time, ' месеца') as warranty_time,
                        tr.is_warranty,
                        tt.description,
						IF
						(
							p.id,
							CONCAT(
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
								' (',
								DATE_FORMAT( tr.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
								),
								''
						) AS updated_user
					FROM tech_reason tr
						LEFT JOIN {$db_name_personnel}.personnel p ON tr.updated_user = p.id
						LEFT JOIN tech_timing tt ON tt.id = tr.id_tech_timing
						LEFT JOIN tech_timing_firms ttf ON ttf.id_tech_timing = tr.id_tech_timing
					WHERE tr.to_arc = 0
			";

        if(!empty($nType)) $sQuery.=" AND tt.id={$nType} ";

        if(!empty($nFirm)) {
            $sQuery.=" AND ttf.id_firm={$nFirm} GROUP BY tr.id ";
        }

        $this->getResult($sQuery, 'name', DBAPI_SORT_ASC, $oResponse);

        $oResponse->setField("name", "Наименование", "Сортирай по наименование");
        $oResponse->setField("description", "Тип", "Сортирай по тип");
//        $oResponse->setField("from_tech_signals", "Техн. справки", "", "images/confirm.gif");
        $oResponse->setField("operations_affect", "Зависима от операции", "Цената се влияе от стойността на извършваните операции", "images/confirm.gif");
        $oResponse->setField("warranty_time", "Гаранционен срок", "Гаранционен срок");
        $oResponse->setField("price", "Цена", "Минимална цена");
        $oResponse->setField("updated_user", "Последна редакция", "Сортирай по последна редакция");

        if ($right_edit) {
            $oResponse->setField("", "", "", "images/cancel.gif", "deleteHoldupReason", "");
            $oResponse->setFieldLink("name", "openHoldupReason");
        }
    }

}

?>
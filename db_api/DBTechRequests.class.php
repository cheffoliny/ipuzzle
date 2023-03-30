<?php

class DBTechRequests extends DBBase2 {

    public $nIDReasonContinueArrange = 14; //ИД НА ПРИЧИНА ЗА ЗАЯВКА ЗА НЕДОВЪРШЕН РЕМОНТ

    public function __construct() {
        global $db_sod;

        parent::__construct($db_sod, 'tech_requests');
    }

    public function getAllAssocWithoutOrder() {
        $sQ = "SELECT t.id as _id, t.* FROM tech_requests t  WHERE t.to_arc = 0";
        return $this->selectAssoc($sQ);
    }

    public function getReport($aData, DBResponse $oResponse) {
        global $db_name_personnel, $db_name_sod;
//			$id_work_card = isset( $aData['id_work_card'] ) ? $aData['id_work_card'] : 0;
//			$id_office = isset( $aData['id_office'] ) ? $aData['id_office'] : 0;
        //debug($aData);
        $nObject = $aData['id_object'];
        $sType = $aData['type'];
        $dFrom = $aData['startTime'];
        $dTo = $aData['endTime'];
        $date_to = mktime(0, 0, 0, date("m", $dTo), date("d", $dTo) + 1, date("Y", $dTo));
        //APILog::Log(0, $date_to);
        $nIDFirm = $aData['id_firm'];
        $nIDOffice = $aData['id_office'];
        $nNoLimitCard = $aData['have_no_limit_card'];
        $nActiveLC = $aData['have_active_limit_card'];
        $nIDScheme = $aData['nIDScheme'];
        $idTechTiming = $aData['idTechTiming'];


        if (!empty($_SESSION['userdata']['access_right_levels'])) {
            if (in_array('tech_support', $_SESSION['userdata']['access_right_levels'])) {
                $right_edit = true;
            }
        }

        $sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					IF
					(
						tr.id_limit_card > 0,
						IF
						(
							obj.id > 0,
							CONCAT_WS( ',', tr.id, tr.id_limit_card, obj.id ),
							CONCAT( tr.id, ',', tr.id_limit_card )
						),
						IF
						(
							obj.id > 0,
							CONCAT_WS( ',', tr.id, '0', obj.id ),
							tr.id
						)
					) AS id,
					tr.id AS num,
					tr.id AS chk,
					tr.id_object AS id_object,
					DATE_FORMAT(tr.created_time,'%d.%m.%Y %H:%i:%s') AS created_time_,
					tr.created_time,

					CASE
					  WHEN tr.request_by = 'client' THEN 'Клиент'
					  WHEN tr.request_by = 'telepol' THEN 'Изпълнител'
					END AS requested_by,

                    trr.name AS reason_name,
                    trr.warranty_time AS warranty_time,
                    trr.is_warranty AS is_warranty,
					frm.name AS firm,
					off.name AS office,
					obj.name AS object,
					obj.num AS object_num,
					#cl.name AS client,
					tr.request_person_name AS client,
					CONCAT_WS(' ', cu.fname, cu.mname, cu.lname) AS created_user,
					tr.id_limit_card AS limit_card,

					/*CASE
						WHEN tr.type = 'create' THEN 'изграждане'
						WHEN tr.type = 'destroy' THEN 'сваляне'
						WHEN tr.type = 'arrange' THEN 'аранжиране'
						WHEN tr.type = 'holdup' THEN 'профилактика'
						WHEN tr.type = 'plan' THEN 'планово обсл.'
					END AS type,*/
					/* CASE 
						WHEN tr.tech_type = 'no_signal' THEN 'няма сигнал'
						WHEN tr.tech_type = 'no_restore' THEN 'няма възстановяване'
                                                WHEN tr.tech_type = 'no_power' THEN 'няма захранване'
                                            END AS tech_type, 
                                        */
                        
                    tm.description AS type,
   
					IF
					(
						tr.id_limit_card != 0 AND lc.status = 'active',
						1,
						0
					) AS unfinished,
					CONCAT_WS(' ',plann.fname,plann.mname,plann.lname) AS make_planning_person_name,
					tr.note
				FROM {$db_name_sod}.tech_requests tr 
					LEFT JOIN {$db_name_sod}.objects obj ON obj.id = tr.id_object
					LEFT JOIN {$db_name_sod}.offices off ON off.id = obj.id_office
					LEFT JOIN {$db_name_sod}.firms frm ON frm.id = off.id_firm
					LEFT JOIN {$db_name_sod}.tech_limit_cards lc ON lc.id = tr.id_limit_card
					LEFT JOIN {$db_name_sod}.tech_reason trr ON trr.id = tr.id_tech_reason
					#LEFT JOIN clients cl ON obj.id_client = cl.id
					LEFT JOIN {$db_name_personnel}.personnel cu ON cu.id = tr.created_user
					LEFT JOIN {$db_name_personnel}.personnel plann ON plann.id = tr.make_planning_person
                    LEFT JOIN {$db_name_sod}.tech_timing tm ON tm.id = tr.id_tech_timing

				WHERE 1
					AND tr.to_arc = 0
					AND tr.id_contract = 0
			";

        if (!empty($dFrom)) {
            $sQuery .= " AND UNIX_TIMESTAMP(tr.created_time) >= '{$dFrom}' \n";
        }

        if (!empty($dTo)) {
            $sQuery .= " AND UNIX_TIMESTAMP(tr.created_time) <= '{$date_to}' \n";
        }

        if (!empty($sType)) {
            $sQuery .= " AND tr.type = '{$sType}' \n";
        }

        if (!empty($nObject)) {
            $sQuery .= " AND tr.id_object = '{$nObject}' \n";
        }

        if (!empty($nIDOffice)) {
            $sQuery .= " AND obj.id_office = '{$nIDOffice}' \n";
        }

        if (!empty($nIDFirm)) {
            $sQuery .= " AND off.id_firm = '{$nIDFirm}' \n";
        }

        if (!empty($nNoLimitCard) && !empty($nActiveLC)) {
            $sQuery .= " AND ( tr.id_limit_card = 0 OR lc.status = 'active' ) \n";
        } else {
            if (!empty($nNoLimitCard)) {
                $sQuery .= " AND tr.id_limit_card = 0 \n";
            }
            if (!empty($nActiveLC)) {
                $sQuery .= " AND lc.status = 'active' \n";
            }
        }

        if(!empty($idTechTiming)) {
            $sQuery.= " AND tr.id_tech_timing = {$idTechTiming} ";
        }
        APILog::Log($sQuery);
        $this->getResult($sQuery, 'tr.created_time_', DBAPI_SORT_DESC, $oResponse);

//            APILog::Log(0, ArrayToString($oResponse));
        //		if ( $right_level == 'edit' ) {
        $oResponse->setField('chk', '', NULL, NULL, NULL, NULL, array('type' => 'checkbox'));
        $oResponse->setFieldData('chk', 'input', array('type' => 'checkbox', 'exception' => 'false'));
        $oResponse->setFieldAttributes('chk', array('style' => 'width: 25px;'));

        $oResponse->setFormElement('form1', 'sel', array(), '');
        $oResponse->setFormElementChild('form1', 'sel', array('value' => '1'), "--- Маркирай всички ---");
        $oResponse->setFormElementChild('form1', 'sel', array('value' => '2'), "--- Отмаркирай всички ---");
        $oResponse->setFormElementChild('form1', 'sel', array('value' => '0'), "------");
        $oResponse->setFormElementChild('form1', 'sel', array('value' => '3'), "--- Лимитни карти ---");
        $oResponse->setFormElementChild('form1', 'sel', array('value' => '4'), "--- Анулиране ---");
        //		}


        foreach ($oResponse->oResult->aData as $key => &$val) {
            $val['num'] = zero_padding($val['num']);
            $oResponse->setDataAttributes($key, 'limit_card', array('style' => 'text-align: center; width: 75px;'));

            if ($val['limit_card'] > 0) {
                $val['limit_card'] = zero_padding($val['limit_card']);
                $oResponse->setDataAttributes($key, 'chk', array('style' => 'visibility: hidden;'));
            } else {
                $val['limit_card'] = '';
            }

            $sFullNote = "";

            if (utf8_strlen($val['note']) > 8) {
                $sFullNote = $val['note'];
                $val['note'] = utf8_substr(trim($val['note']), 0, 8) . " ...";
            }
            $oResponse->setDataAttributes($key, "note", array("width" => "100px", "title" => $sFullNote));

            $oResponse->setDataAttributes($key, 'num', array('style' => 'text-align: center; width: 75px;'));
            $oResponse->setDataAttributes($key, 'object_num', array('style' => 'text-align: right;'));
            $oResponse->setDataAttributes($key, 'created_time_', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));

            if ($val['unfinished'] == 1) {
                $oResponse->setDataAttributes($key, 'created_time_', array("style" => "font-weight:bold"));
                $oResponse->setDataAttributes($key, 'firm', array("style" => "font-weight:bold"));
                $oResponse->setDataAttributes($key, 'office', array("style" => "font-weight:bold"));
                $oResponse->setDataAttributes($key, 'type', array("style" => "font-weight:bold"));
                $oResponse->setDataAttributes($key, 'object', array("style" => "font-weight:bold"));
                $oResponse->setDataAttributes($key, 'client', array("style" => "font-weight:bold"));
            }

            $oDBHoldupReasons = new DBHoldupReasons();

//            if ($aLastService['is_warranty'] == 1) {
//                if (strtotime('today - ' . $aLastService['warranty_time'] . ' months') < strtotime($aLastService['date'])) {
            if ($oDBHoldupReasons->isWarranty($val['id_object'])) {
                $oResponse->setDataAttributes($key, 'num', array('style' => 'font-weight: bold; background-color: #FFD4AA; text-align: center; color: red;'));
            }

//                APILog::Log(0, $aLastService);
//            }
        }

        if (empty($nIDScheme)) {
            $oResponse->setField('num', 'номер', 'сортирай по номер');
            $oResponse->setField('created_time_', 'дата', 'сортирай по дата');
            $oResponse->setField('firm', 'фирма', 'сортирай по фирма');
            $oResponse->setField('office', 'регион', 'сортирай по регион');
            $oResponse->setField('type', 'тип', 'сортирай по тип');
            $oResponse->setField('reason_name', 'причина', 'сортирай по причина');
            $oResponse->setField('object_num', 'номер на обект', 'сортирай по обект');
            $oResponse->setField('object', 'обект', 'сортирай по обект');
            //$oResponse->setField( 'client',						'клиент',			'сортирай по клиент' );
            $oResponse->setField('requested_by', 'заявил', 'сортирай по заявил');
            $oResponse->setField('note', 'забележка', 'сортирай по забележка');
            $oResponse->setField('created_user', 'съставил', 'сортирай по съставил');
            $oResponse->setField('limit_card', 'лим. карта', 'сортирай по карта');
            $oResponse->setField('make_planning_person_name', 'планирал', 'сортирай по планирал');


            $oResponse->setFIeldLink('num', 'editRequest');
            $oResponse->setFIeldLink('limit_card', 'viewLimitCard');
            $oResponse->setFIeldLink('object_num', 'viewObject');
        } else {
            $oDBTechSupportRequestsFilter = new DBTechSupportRequestsFilters();

            $aFilter = array();
            $aFilter = $oDBTechSupportRequestsFilter->getRecord($nIDScheme);

            $aVisibleColumns = unserialize($aFilter['visible_columns']);

            $oResponse->setField('num', 'номер', 'сортирай по номер');
            $oResponse->setField('created_time_', 'дата', 'сортирай по дата');

            if (!empty($aVisibleColumns['firm'])) {
                $oResponse->setField('firm', 'фирма', 'сортирай по фирма');
            }
            if (!empty($aVisibleColumns['office'])) {
                $oResponse->setField('office', 'регион', 'сортирай по регион');
            }
            if (!empty($aVisibleColumns['type'])) {
                $oResponse->setField('type', 'тип', 'сортирай по тип');
            }

            $oResponse->setField('object_num', 'номер на обект', 'сортирай по обект');
            $oResponse->setField('object', 'обект', 'сортирай по обект');

            if (!empty($aVisibleColumns['client'])) {
                $oResponse->setField('client', 'клиент', 'сортирай по клиент');
            }

            if (!empty($aVisibleColumns['note'])) {
                $oResponse->setField('note', 'забележка', 'сортирай по забележка');
            }


            if (!empty($aVisibleColumns['created_user'])) {
                $oResponse->setField('created_user', 'съставил', 'сортирай по съставил');
            }
            if (!empty($aVisibleColumns['limit_card'])) {
                $oResponse->setField('limit_card', 'лим. карта', 'сортирай по карта');
                $oResponse->setFIeldLink('limit_card', 'viewLimitCard');
            }
            if (!empty($aVisibleColumns['make_planning_person_name'])) {
                $oResponse->setField('make_planning_person_name', 'планирал', 'сортирай по планирал');
            }

            $oResponse->setFIeldLink('num', 'editRequest');
            $oResponse->setFIeldLink('object_num', 'viewObject');
        }
    }

    public function delRequests($nIDs) {
        global $db_sod;

        $sQuery = "UPDATE tech_requests SET to_arc = 1 WHERE id IN ({$nIDs})";
        $db_sod->Execute($sQuery);
    }

    public function makeLimitCard($nIDs) {
        global $db_sod;

        $requests = array();

        if (!empty($nIDs)) {
            $requests = explode(",", $nIDs);

            foreach ($requests as $key => $val) {
                $db_sod->StartTrans();
                $tmpData = '';
                $tmpData = $this->getRequest($val);
                $cuser = !empty($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;

                $sQuery = "INSERT INTO tech_limit_cards (status, type, id_object, id_request, created_time, created_user, updated_user, updated_time, to_arc) VALUES ('active', '{$tmpData['type']}', {$tmpData['id_object']}, {$val}, NOW(), {$cuser}, {$cuser}, NOW(), 0 )";
                $db_sod->Execute($sQuery);
                $lastID = $db_sod->Insert_ID();

                $sQuery = "UPDATE tech_requests SET id_limit_card = {$lastID} WHERE id = {$val}";
                $db_sod->Execute($sQuery);
                $db_sod->CompleteTrans();
            }
        }
    }

    public function getRequest($nID) {
        global $db_name_personnel;

        $nID = (int) $nID;

        $sQuery = "
				SELECT 
					tr.id,
					tr.id AS num,
					DATE_FORMAT(tr.created_time, '%d.%m.%Y') AS created_time,
					CASE
						WHEN tr.planned_start = '0000-00-00 00:00:00' THEN ''
						WHEN tr.planned_start != '0000-00-00 00:00:00' THEN DATE_FORMAT(tr.planned_start, '%d.%m.%Y')
					END AS planned_date,
					CASE
						WHEN tr.planned_start = '0000-00-00 00:00:00' THEN ''
						WHEN (DATE_FORMAT(tr.planned_start, '%Y-%m-%d') != '0000-00-00') AND (DATE_FORMAT(tr.planned_start, '%H:%i:%s') != '00:00:00') THEN DATE_FORMAT(tr.planned_start, '%H:%i')
						WHEN (DATE_FORMAT(tr.planned_start, '%Y-%m-%d') != '0000-00-00') AND (DATE_FORMAT(tr.planned_start, '%H:%i:%s') = '00:00:00') THEN '' 
					END AS planned_hour,
					off.id_firm,
					obj.id_office,
					tr.id_object,
					obj.name AS object,
					tr.id_limit_card AS limit_card,
					tr.request_by,
					tr.id_tech_reason AS reason,
					tr.id_tech_timing AS id_tech_timing,
					tr.type,
					IF ( tr.created_type = 'manual', CONCAT_WS(' ', cu.fname, cu.mname, cu.lname), 'Автоматична заявка' ) AS created_user,
					IF ( tr.updated_user != 0, CONCAT_WS( ' ', uu.fname, uu.mname, uu.lname ), ' ' ) AS updated_user,
					tr.request_person_name,
					tr.request_time,
					tr.note,
					tr.id_contract,
					tr.updated_time
				FROM tech_requests AS tr
					LEFT JOIN objects obj ON obj.id = tr.id_object
					LEFT JOIN offices off ON off.id = obj.id_office

					LEFT JOIN {$db_name_personnel}.personnel cu ON cu.id = tr.created_user
					LEFT JOIN {$db_name_personnel}.personnel uu ON uu.id = tr.updated_user
				WHERE 1
					AND tr.id = {$nID}
			";

        return $this->selectOnce($sQuery);
    }

    public function getRequests($nIDOffice, DBResponse $oResponse) {

        global $db_name_finance, $db_name_personnel , $db_sod_backup;

        $oDBSalesDocs = new DBSalesDocs();
        $oDBClients = new DBClients();

        $nIDTechTiming = Params::get('nIDTechTiming',0);
        $sObjectName = Params::get('sObjectName',0);
        $sObjectName = Params::get('sObjectName',0);

        $right_edit = false;

        $oDBHoldupReasons = new DBHoldupReasons();

        if (!empty($_SESSION['userdata']['access_right_levels'])) {
            if (in_array('tech_planning_edit', $_SESSION['userdata']['access_right_levels'])) {
                $right_edit = true;
            }
        }

        $sQuery = "
                SELECT
                t.*
                FROM
                (

                    (
                    SELECT
                        tr.id,
                        tr.id AS btn_delete,
                        tr.id AS num,
                        DATE_FORMAT(tr.created_time, '%d.%m.%Y') AS created_time_,
                        tr.created_time,
                        tr.planned_start,
                        CASE
                            WHEN tr.planned_start = '0000-00-00 00:00:00' THEN ''
                            WHEN (DATE_FORMAT(tr.planned_start, '%Y-%m-%d') != '0000-00-00') AND (DATE_FORMAT(tr.planned_start, '%H:%i:%s') != '00:00:00') THEN DATE_FORMAT(tr.planned_start, '%d.%m.%Y %H:%i')
                            WHEN (DATE_FORMAT(tr.planned_start, '%Y-%m-%d') != '0000-00-00') AND (DATE_FORMAT(tr.planned_start, '%H:%i:%s') = '00:00:00') THEN DATE_FORMAT(tr.planned_start, '%d.%m.%Y')
                        END AS planned_start_,
                        obj.name,
                        CONCAT('[',obj.num,'] ' ,obj.name) AS name_,
                        /*CASE
                            WHEN c.build_type IS NULL THEN tt.description
                            WHEN c.build_type = 'express' THEN CONCAT(tt.description, ' - Експресна')
                            WHEN c.build_type = 'fast' THEN CONCAT(tt.description, ' - Бърза')
                            WHEN c.build_type = 'normal' THEN CONCAT(tt.description, ' - Обикновна')
                        END AS type,*/
                        tt.description AS type,
                        CASE
                            WHEN tt.id = 1 THEN '#3232A0'
                            WHEN tt.id = 2 THEN '#AA3232'
                            WHEN tt.id = 3 THEN '#328232'
                            WHEN tt.id = 4 THEN '#781E78'
                            WHEN tt.id = 5 THEN '#3232A0'
                        END AS type_color,
                        #CONCAT('#',tt.color) AS type_color,
                        tr.tech_type,
                        tr.tech_type AS tech_type_orig,
                        tr.id_contract,
                        hr.name AS holdup_reason,
                        '' as obj_distance,
                        tr.time,
                        tr.note AS some_info,
                        tr.id_object,
                        CONCAT_WS( ' ', per.fname, per.mname, per.lname ) AS created_person,
                        /*
                        (
                            SELECT
                                GROUP_CONCAT( CONCAT_WS(' ', person.fname, person.lname )  )
                            FROM limit_card_persons lcp
                            LEFT JOIN {$db_name_personnel}.personnel person ON person.id = lcp.id_person
                            WHERE 1
                                AND lcp.id_limit_card =
                                (
                                    SELECT IF(id, id, 0)
                                    FROM tech_limit_cards
                                    WHERE 1
                                      AND id_object = tr.id_object
                                      AND status = 'closed'
                                      AND to_arc = 0

                                    ORDER BY real_end DESC
                                    LIMIT 1
                                )
                                AND lcp.id_person != 0
                            GROUP BY lcp.id_limit_card
                        ) AS tech_name,
                        */
                        '' AS tech_name,
                        '' AS contract_is_signed,
                    CASE
						WHEN c.contract_status = 'signed' THEN 'Подписан'
						WHEN c.contract_status = 'entered' THEN 'Офериран'
						WHEN c.contract_status = 'validated' THEN 'Активирана'
						WHEN c.contract_status = 'ignored' THEN 'Отказан'
						WHEN c.contract_status IS NULL THEN ''
					END AS contract_status,
					IF( c.signed_time IS NOT NULL , DATE_FORMAT(DATE_ADD(c.signed_time,INTERVAL (c.delivery_days + c.build_days) DAY),'%d.%m.%Y')  , '' ) AS last_build_date,
					IF( c.entered_from IS NOT NULL, DATE_FORMAT(c.entered_from,'%d.%m.%Y') , '' ) AS entered_from,
                    CASE
						WHEN c.contract_status = 'signed' THEN DATE_ADD(c.signed_time,INTERVAL (c.delivery_days + c.build_days) DAY)
						WHEN c.contract_status = 'entered' THEN c.entered_from
						WHEN c.contract_status IS NULL THEN ''
					END AS execute_to,
                        '' AS id_contract_termination_reason,
                        tr.priority,
                        tr.appointment,
                        'no' AS from_contract,
                        obj.id_office,
                        tr.id_tech_timing,
                        '' AS some_info_2
                    FROM tech_requests tr
                    LEFT JOIN objects obj ON obj.id = tr.id_object
                    LEFT JOIN tech_reason hr ON hr.id = tr.id_tech_reason
                    LEFT JOIN tech_timing tt ON tt.id = tr.id_tech_timing
                    LEFT JOIN {$db_name_personnel}.personnel per ON per.id = tr.created_user
                    LEFT JOIN {$db_name_finance}.contracts c ON c.id = tr.id_contract
                    WHERE 1
                        AND tr.to_arc = 0
                        AND tr.id_object != 0
                        AND tr.tech_type != 'contract'";

        $sQuery .= !empty($nIDOffice) ? " AND obj.id_office = {$nIDOffice} " : " ";

        if(!empty($sObjectName)) {
            $sQuery.= " AND obj.name LIKE '%$sObjectName%' ";
        };

        $sQuery.="
                        AND tr.id_limit_card = 0
                    )

                     UNION
                    (
                    SELECT
                        tr.id,
                        tr.id AS btn_delete,
                        tr.id AS num,
                        DATE_FORMAT(tr.created_time, '%d.%m.%Y') AS created_time_,
                        tr.created_time,
                        tr.planned_start,
                        CASE
                            WHEN tr.planned_start = '0000-00-00 00:00:00' THEN ''
                            WHEN (DATE_FORMAT(tr.planned_start, '%Y-%m-%d') != '0000-00-00') AND (DATE_FORMAT(tr.planned_start, '%H:%i:%s') != '00:00:00') THEN DATE_FORMAT(tr.planned_start, '%d.%m.%Y %H:%i')
                            WHEN (DATE_FORMAT(tr.planned_start, '%Y-%m-%d') != '0000-00-00') AND (DATE_FORMAT(tr.planned_start, '%H:%i:%s') = '00:00:00') THEN DATE_FORMAT(tr.planned_start, '%d.%m.%Y')
                        END AS planned_start_,
                        c.obj_name AS name,
                        #IF(c.obj_name != '',c.obj_name,c.obj_address) AS name_,
                        IF(c.obj_name != '',IF(o.id IS NOT NULL, CONCAT('[',o.num,'] ',o.name), c.obj_name),c.obj_address) AS name_,
                        /*CASE
                            WHEN c.build_type IS NULL THEN tt.description
                            WHEN c.build_type = 'express' THEN CONCAT(tt.description, ' - Експресна')
                            WHEN c.build_type = 'fast' THEN CONCAT(tt.description, ' - Бърза')
                            WHEN c.build_type = 'normal' THEN CONCAT(tt.description, ' - Обикновнна')
                        END AS type,*/
                        tt.description AS type,
                        '#3232A0' AS type_color,
                        #CONCAT('#',tt.color) AS type_color,
                        'ел. договор' AS tech_type,
                        tr.tech_type AS tech_type_orig,
                        tr.id_contract,
                        hr.name AS holdup_reason,
                        c.obj_distance,
                        tr.time,
                        c.info_tehnics AS some_info,
                        tr.id_object,
                        CONCAT_WS( ' ', per.fname, per.mname, per.lname ) AS created_person,
                        '' as tech_name,
                        c.signed AS contract_is_signed,
                                            CASE
						WHEN c.contract_status = 'signed' THEN 'Подписан'
						WHEN c.contract_status = 'entered' THEN 'Офериран'
						WHEN c.contract_status = 'validated' THEN 'Активирана'
						WHEN c.contract_status = 'ignored' THEN 'Отказан'
						WHEN c.contract_status IS NULL THEN ''
					END AS contract_status,
					IF( c.signed_time IS NOT NULL , DATE_FORMAT(DATE_ADD(c.signed_time,INTERVAL (c.delivery_days + c.build_days) DAY),'%d.%m.%Y')  , '' ) AS last_build_date,
					IF( c.entered_from IS NOT NULL, DATE_FORMAT(c.entered_from,'%d.%m.%Y') , '' ) AS entered_from,
                    CASE
						WHEN c.contract_status = 'signed' THEN DATE_ADD(c.signed_time,INTERVAL (c.delivery_days + c.build_days) DAY)
						WHEN c.contract_status = 'entered' THEN c.entered_from
						WHEN c.contract_status IS NULL THEN ''
					END AS execute_to,
                        c.id_contract_termination_reason,
                        tr.priority,
                        tr.appointment,
                        'yes' AS from_contract,
                        c.id_office,
                        tr.id_tech_timing,
                        tr.note AS some_info_2
                    FROM tech_requests tr
                    LEFT JOIN {$db_name_finance}.contracts c ON c.id = tr.id_contract
                    LEFT JOIN {$db_name_personnel}.personnel per ON per.id = tr.created_user
                    LEFT JOIN tech_timing tt ON tt.id = tr.id_tech_timing
                    LEFT JOIN tech_reason hr ON hr.id = tr.id_tech_reason
                    LEFT JOIN objects o ON o.id = c.id_obj
                    WHERE 1
                        AND tr.to_arc = 0
                        AND tr.tech_type = 'contract'
                        AND tr.id_limit_card = 0
                    )
            ) AS t

            WHERE 1

			";

        $sQuery .= ! empty($nIDOffice) ? " AND t.id_office = {$nIDOffice} " : '';

        if(!empty($sRequestSource)) {
            $sQuery.= " AND t.from_contract = '{$sRequestSource}' ";
        }

        if(!empty($nIDTechTiming)) {
            $sQuery.= " AND t.id_tech_timing = {$nIDTechTiming} ";
        }

//        if(!empty($sObjectName)) {
//            $sQuery.= " AND obj.name LIKE '%$sObjectName%' ";
//        }

//        APILog::Log(0,$sQuery);
        $this->getResult($sQuery, 'created_time_', DBAPI_SORT_DESC, $oResponse , $db_sod_backup,1,200);

        $i = 0;
//        APILog::Log(0, ArrayToString($oResponse->oResult->aData));
        $aClientPayment = array();

        foreach ($oResponse->oResult->aData as $key => &$val) {

            $i++;
            $val['num'] = zero_padding($val['num']);
            $pic_num = $val['timing'] / 30;

            $nDistance = $val['obj_distance'];

            //проверка за неплатени фактури

//            $aClient = $oDBClients->getClientByObject($val['id_object']);

//            APILog::Log($val['name_'],$val['id_object']);
//            APILog::Log($val['name_'],$aClient);
//            APILog::Log($val['name_'],$aResult);
            $aResult = array();
//            if(!empty($aClient['id'])) {
//                if(!isset($aClientPayment[$aClient['id']])) {
//                    $aResult  =  $oDBSalesDocs->getCountUnpaidInvoice($aClient['id']);
//                }
//            }

            if ($nDistance > 15) {
                $nDistance -= 15;
                $pic_num += round($nDistance / 30);
            }
            if ($pic_num > 16)
                $pic_num = 16;

            $pad = ($val['timing'] / 30) * 30;
            $val['timing'] = '';
            $row_color = $i % 2 ? '#FFFFFF' : '#F0F0F0';
            $oResponse->setDataAttributes($key, 'timing', array('style' => "background: {$row_color} url(images/time/red{$pic_num}.gif) no-repeat; padding-left: {$pad}px; "));
//            APILog::Log(0,'test '.$val['id_object']);


            $oResponse->setDataAttributes($key, 'type', array('onclick' => "openRequest({$val['id']})",
                'style' => 'cursor:pointer'));
            $oResponse->setDataAttributes($key, 'num', array('onclick' => "openRequest({$val['id']})",
                'style' => 'cursor:pointer'));
            if (!empty($val['planned_start_'])) {
                $oResponse->setDataAttributes($key, 'planned_start_', array('style' => 'background: #FFDDDD;'));
            }

            if ($val['tech_type'] == 'ел. договор') {
                $oResponse->setDataAttributes($key, 'tech_type', array('onclick' => "openContract({$val['id_contract']})",
                    'style' => "cursor: pointer; background: {$val['type_color']}; color: #FFFFFF;",
                    "title" => $val['created_person']));

//                if( $val['contract_is_signed'] == '0' && $val['id_contract_termination_reason'] == '0' )
//	            {
//	            	//договора не е подписан
//	            	$oResponse->setDataAttributes($key, 'type', array('style' => 'border: 2px solid red'));
//	            	$oResponse->setDataAttributes($key, 'num', array('style' => 'border: 2px solid red'));
//	            }

                if (!empty($val['some_info'])) {
                    $oResponse->setDataAttributes($key, 'name_', array('title' => $val['some_info'], 'style' => 'cursor:pointer;background-image: url("images/info.gif");background-position: center right;background-repeat:no-repeat;'));
                } else {
                    $oResponse->setDataAttributes($key, 'name_', array('title' => $val['some_info']));
                }
            }
            else {
                $oResponse->setDataAttributes($key, 'tech_type', array('onclick' => "openRequest({$val['id']})",
                    'style' => "cursor: pointer; background: {$val['type_color']}; color: #FFFFFF;",
                    "title" => $val['created_person']));



//                if(count($aResult) >= 2) {
//                    $oResponse->setRowAttributes($val['id'], array('style' => "background-color:#F2D8C9;", 'not-edit-color' => 1));
//                }

                if (!empty($val['some_info'])) {
//                    if (!empty($val['id_object']) && $oDBHoldupReasons->isWarranty($val['id_object'])) {
//                        $oResponse->setDataAttributes($key, 'name_', array('title' => $val['some_info'], 'style' => 'background-color: red; color:white; cursor:pointer; background-image: url("images/info.gif");background-position: center right;background-repeat:no-repeat;', 'onclick' => "openObject({$val['id_object']})"));
//                    } else {
//                        $oResponse->setDataAttributes($key, 'name_', array('title' => $val['some_info'], 'style' => 'cursor:pointer; background-image: url("images/info.gif");background-position: center right;background-repeat:no-repeat;', 'onclick' => "openObject({$val['id_object']})"));
//                    }

                    $oResponse->setDataAttributes($key, 'name_', array('style' => 'cursor:pointer;', 'onclick' => "openObject({$val['id_object']})"));

                    $sSomeInfo = (mb_strlen($val['some_info'],'UTF-8') > 10)? mb_substr($val['some_info'],0,10,"UTF-8")."..." : $val['some_info'];

                    $oResponse->setDataAttributes($key, 'some_info', array('title' => $val['some_info'] , 'style' => 'cursor:pointer;'));
                    $val['some_info'] = $sSomeInfo;
                } else {
                    if (!empty($val['id_object']) && $oDBHoldupReasons->isWarranty($val['id_object'])) {
                        $oResponse->setDataAttributes($key, 'name_', array('style' => 'background-color: red; color:white; cursor:pointer;', 'onclick' => "openObject({$val['id_object']})"));
                    } else {
                        $oResponse->setDataAttributes($key, 'name_', array('style' => 'cursor:pointer;', 'onclick' => "openObject({$val['id_object']})"));
                    }
//                    $oResponse->setDataAttributes($key, 'name_', array('title' => $val['some_info'], 'onclick' => "openObject({$val['id_object']})", 'style' => 'cursor:pointer;'));
                }
            }

            $aNames = explode(" ", $val['created_person']);
            if (isset($aNames[0]) && isset($aNames[1]) && isset($aNames[2])) {
                $val['tech_type'] = $aNames[0] . " " . utf8_substr($aNames[1], 0, 1) . ". " . utf8_substr($aNames[2], 0, 1) . ".";
            }


            $sBGColor= "#fff";
            $sColor= "#111111";
            //офертите да са само за ремонт
            if($val['id_tech_timing'] == 3)
            {

                if($val['expired']) {
                    $sBGColor='red';
                    $sColor='#fff';
                }

                $val['offer'] = 'Не офериран';
                $val['offer_col'] = '';
                $val['offer_title'] = '';
                if($val['contract_is_signed'] !== '0' && strlen($val['contract_is_signed']) > 3) {
                    switch ($val['contract_is_signed']) {
                        case 'signed':
                            $val['offer'] = 'Подписан';
                            $val['offer_col'] = '#87ba21';
                            $val['offer_title'] = "Крайна дата за изпълнение на ремонт ".$val['last_build_date'];
                            break;

                        case 'ignored':
                            $val['offer'] = 'Отказан';
                            $val['offer_col'] = '#D9534F';
                            $val['offer_title'] = "Офертата е анулирана!";
                            break;

                        case 'entered':
                            $val['offer'] = 'Офериран';
                            $val['offer_col'] = "#5293c4";
                            $val['offer_title'] = "Офертата е валидна до ".$val['entered_from'];
                            break;

                        default:
                            $val['offer'] = 'Не офериран';
                            break;
                    }
                }
//                $oResponse->setDataAttributes($key, 'contract_status', array('style' => "background-color:{$val['offer_col']};cursor:pointer;" , 'title' => $val['offer_title'] , 'onClick' => "openTp( '{$val['id']}@{$val['id_contract']}' )"));

                $oResponse->setDataAttributes($key, 'execute_to', array('style' => "background-color:{$sBGColor}; color: {$sColor}"));
            } else {
                $val['offer'] ='';
//                $oResponse->setDataAttributes($key, 'contract_status', array());
            }
        }



        $oResponse->setField('created_time_', 'дата', 'сортирай по дата', NULL, NULL, NULL, array('style' => 'width: 70px;'));
        $oResponse->setField('name_', 'обект', 'сортирай по име на обект', NULL, NULL, NULL, array('style' => 'width: 300px;'));
        $oResponse->setField('num', 'номер', 'сортирай по номер на заявка', NULL, NULL, NULL, array('style' => 'width: 100px;'));
        $oResponse->setField('type', 'тип', 'сортирай по тип на заявката', NULL, NULL, NULL, array('style' => 'width: 100px;'));
        $oResponse->setField('holdup_reason', 'причина', 'сортирай по причина за профилактика', NULL, NULL, NULL, array('style' => 'width: 150px;'));
        $oResponse->setField('some_info', 'бележка', 'сортирай по бележка', NULL, NULL, NULL, array('style' => 'width: 100px;'));


        $oResponse->setField('tech_type', 'източник', 'сортирай по източник на заявката', NULL, NULL, NULL, array('style' => 'width: 100px;'));
//        $oResponse->setField('offer', 'оферта', 'сортирай по оферта', NULL, NULL , NULL , array('style' => "width: 100px;"));
//        $oResponse->setField('contract_status', 'оферта', 'сортирай по оферта', NULL, NULL , NULL , array('style' => "width: 100px;"));
        $oResponse->setField('execute_to', 'Изпълнение до', 'сортирай по Изпълнение до', NULL, NULL, NULL, array('DATA_FORMAT' => DF_DATE ,'style' => "width: 90px;"));




//        $oResponse->setField('timing', 'времетраене', 'сортирай по времетраене');
        //$oResponse->setFieldLink('type','openRequest');
        //$oResponse->setFieldLink('num','openRequest');
        //$oResponse->setFieldLink('tech_type','openRequest');

        if ($right_edit) {
//            $oResponse->setField('','','','images/cancel.gif','delRequest','');
            $oResponse->setField('','','','images/cancel.gif','delRequest','Анулирай');
//            $oResponse->setField("btn_delete", "", NULL, "images/cancel.gif", "delRequest", "Анулирай");
//            $oResponse->setField('btn_delete', 'оферт2а', 'сортирай по оферта', NULL, NULL , NULL);
        }
    }

    public function getInfoForPersonCard($nID) {

        $sQuery = "
				SELECT
					tr.id,
					tr.id_contract,
					DATE_FORMAT(tr.created_time, '%d.%m.%Y') AS created_time,
					tr.note,
					tr.id_tech_reason,
					hr.name AS holdup_reason,
					tr.type,

					 CASE
				      WHEN tr.request_by = 'telepol' THEN 'Компанията'
                      WHEN tr.request_by = 'client'  THEN 'Клиент'
                    END AS request_by


				FROM tech_requests tr
				LEFT JOIN tech_reason hr ON hr.id = tr.id_tech_reason
				WHERE tr.id = {$nID}
			";

        return $this->selectOnce($sQuery);
    }

    public function getFactorTechSupport($nIDRequest) {

        global $db_name_finance;

        $sQuery = "
				SELECT 
					o.factor_tech_support,
					o.factor_tech_distance
				FROM tech_requests tr
				LEFT JOIN {$db_name_finance}.contracts c ON c.id = tr.id_contract
				LEFT JOIN offices o ON o.id = c.id_office
				WHERE tr.id = $nIDRequest
			";

        return $this->selectOnce($sQuery);
    }

    public function delByIDContract($nIDContract) {

        $sQuery = "
				UPDATE
					tech_requests
				SET to_arc = 1
				WHERE id_contract = {$nIDContract}
			";

        $this->oDB->Execute($sQuery);
    }

    public function deattachLimitCard($nIDLimitCard) {

        $sQuery = "
				UPDATE
					tech_requests
				SET id_limit_card = 0 
				WHERE id_limit_card = {$nIDLimitCard}
			";

        $this->oDB->Execute($sQuery);
    }

    public function getByObject($nIDObject) {
        if (empty($nIDObject) || !is_numeric($nIDObject))
            return array();

        $sQuery = "
					SELECT
						r.id_limit_card,
						r.created_time,
						CASE 
							WHEN r.type = 'create' THEN 'изграждане'
							WHEN r.type = 'destroy' THEN 'сваляне'
							WHEN r.type = 'arrange' THEN 'аранжиране'
							WHEN r.type = 'holdup' THEN 'профилактика'
							WHEN r.type = 'plan' THEN 'планово обсл.'
						END AS type,
						r.request_person_name
					FROM tech_requests r
					WHERE 1
						AND r.to_arc = 0
						AND r.id_object = {$nIDObject}
			";

        return $this->select($sQuery);
    }

    //проверява за заявки неизпълнени в период от 1 ден от датата на планиран старт и ги връща за планиране
    public function resetOldRequests($now = false) {
        $oDBTechLimitCards = new DBTechLimitCards();
        $oDBTechRequests = new DBTechRequests();
        $oDBLimitCardPersons = new DBLimitCardPersons();

        $aOldLimitCards = $oDBTechLimitCards->getOldLimitCards($now);

        foreach ($aOldLimitCards as $OldLimitCard) {
            $aTechRequest = array();
            $aTechRequest['id'] = $OldLimitCard['id_request'];
            $aTechRequest['id_limit_card'] = 0;
            $oDBTechRequests->update($aTechRequest);

            $oDBTechLimitCards->delete($OldLimitCard['id_limit_card']);

            $oDBLimitCardPersons->deleteByLimitCardId($OldLimitCard['id_limit_card']);
        }
    }

    public function getRequestBy($nIDLimitCard) {
        global $db_name_sod;

        if (!empty($nIDLimitCard) && is_numeric($nIDLimitCard)) {
            $sQuery = "
            SELECT
                request_by
            FROM {$db_name_sod}.tech_requests
            WHERE
            id_limit_card = {$nIDLimitCard}
            AND to_arc = 0
            ";

            $data = $this->selectOnce($sQuery);
            if (!empty($data['request_by']))
                return $data['request_by'];
            return false;
        }
        return false;
    }

    public function getByLimitCard($nIDLimitCard) {
        global $db_name_sod;

        if (empty($nIDLimitCard) || !is_numeric($nIDLimitCard)) {
            return array();
        }

        $sQuery = "SELECT * FROM {$db_name_sod}.tech_requests WHERE id_limit_card = {$nIDLimitCard}";

        return $this->selectOnce($sQuery);
    }

    public function getRequestData($nID) {
        if (empty($nID) || !is_numeric($nID)) {
            return false;
        }

        $sQuery = "
    				SELECT
    					*
    				FROM tech_requests
    				WHERE id = {$nID}
    			";
        return $this->selectOnce($sQuery);
    }

    public function hasByIDContract($nIDContract) {

        $sQuery = "
			SELECT
				*
			FROM tech_requests
			WHERE to_arc = 0
				AND tech_type = 'contract'
				AND id_contract = {$nIDContract}
		";

        $aData = $this->select($sQuery);

        if (empty($aData)) {
            return false;
        }
        return true;
    }

    public function getByIDContract($nIDContract) {
        if(empty($nIDContract)) {
            return array();
        }


        $sQuery = "
			SELECT
				*
			FROM tech_requests
			WHERE to_arc = 0
				AND id_contract = {$nIDContract}
		";

        return $this->selectOnce($sQuery);
    }

    /*
     * Връща масив със заявки за ремонт на обекта
     */
    public function getRepairRequestByIDObject($nIDObject) {
        if(empty($nIDObject)) {
            return array();
        }

        $sQuery = "
            SELECT
            tr.id,
            DATE_FORMAT(tr.request_time,'%d.%m.%Y %H:%i') AS request_time,
            tr.note,
            tt.description AS type_name,
            t_reason.name AS reason_name
            FROM
            tech_requests tr
            LEFT JOIN tech_reason t_reason
            ON tr.id_tech_reason = t_reason.id
            LEFT JOIN tech_timing tt
            ON tr.id_tech_timing = tt.id
            WHERE 1
            AND tr.to_arc = 0
            AND tr.id_limit_card = 0
            AND tr.id_tech_timing = 3
            AND tr.id_object = {$nIDObject}
        ";

        return $this->select($sQuery);
    }

    public function getRequestByParentIdLimitCard($nIdLimitCard) {

        if(empty($nIdLimitCard)) {
            return array();
        }

        $sQuery = "
            SELECT
            *
            FROM
            tech_requests
            WHERE 1
            AND to_arc = 0
            AND id_parent_limit_card = {$nIdLimitCard}
        ";

        return $this->selectOnce($sQuery);
    }


    public function getLastRequestWithoutLC($nIDObject, $nIDReason) {

        $sQuery = "
            SELECT
              *
            FROM
            tech_requests tr
            LEFT JOIN tech_limit_cards tlc ON tlc.id = tr.id_limit_card AND tlc.to_arc = 0 AND tlc.status = 'active'
            WHERE 1
            AND tr.to_arc = 0
            AND tr.id_object = {$nIDObject}
            AND tr.id_tech_reason = {$nIDReason}
            AND ((tr.id_limit_card = 0) OR tlc.id IS NOT NULL)

        ";

        return $this->selectOnce($sQuery);

    }

}

?>
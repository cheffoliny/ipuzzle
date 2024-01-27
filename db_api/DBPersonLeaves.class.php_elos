<?php

class DBPersonLeaves extends DBBase2
{
    public function __construct()
    {
        global $db_personnel;

        parent::__construct($db_personnel, "person_leaves");
    }

    public function getReport($aParams, DBResponse $oResponse)
    {
        global $db_name_sod;

        //Params
        $aYearMonth = isset($aParams['sDate']) ? explode("-", $aParams['sDate']) : array();
        if (isset($aYearMonth[0]) && isset($aYearMonth[1])) {
            $nYear = ( int )$aYearMonth[0];
            $nMonth = ( int )$aYearMonth[1];
        } else {
            $nYear = ( int )date("Y");
            $nMonth = ( int )date("m");
        }

        $nMonthDays = date("t", mktime(0, 0, 0, $nMonth, 1, $nYear));
        //End Params

        //Query
        $sQuery = "
				SELECT
					per_lea.id_person AS id,
					per_lea.id AS id_application,
					CONCAT_WS( ' ', per.fname, per.mname, per.lname ) AS person_name,
					off.name AS office_name,
					fir.name AS firm_name,
					obj.name AS object_name,
					IF
					(
						( per_lea.is_confirm = 1 AND per_lea.application_days != 0 ),
						DATE_FORMAT( per_lea.res_leave_from, '%d.%m.%Y' ),
						DATE_FORMAT( per_lea.leave_from, '%d.%m.%Y' )
					) AS leave_from,
					IF
					(
						( per_lea.is_confirm = 1 AND per_lea.application_days != 0 ),
						DATE_FORMAT( per_lea.res_leave_to, '%d.%m.%Y' ),
						DATE_FORMAT( per_lea.leave_to, '%d.%m.%Y' )
					) AS leave_to,
					IF
					(
						( per_lea.is_confirm = 1 AND per_lea.application_days != 0 ),
						DATE_FORMAT( per_lea.res_leave_from, '%Y-%m-%d' ),
						DATE_FORMAT( per_lea.leave_from, '%Y-%m-%d' )
					) AS leave_from_raw,
					IF
					(
						( per_lea.is_confirm = 1 AND per_lea.application_days != 0 ),
						DATE_FORMAT( per_lea.res_leave_to, '%Y-%m-%d' ),
						DATE_FORMAT( per_lea.leave_to, '%Y-%m-%d' )
					) AS leave_to_raw,
					IF
					(
						per_lea.is_confirm = 1,
						per_lea.application_days,
						per_lea.application_days_offer
					) AS application_days,
					per_lea.is_confirm,
					per_lea.leave_types AS type,
					CASE leave_types
						WHEN 'due' THEN 'Платен'
						WHEN 'unpaid' THEN 'Неплатен'
						WHEN 'other' THEN 'Болничен'
						ELSE ''
					END AS leave_type,
					
					IF
					(
						( per_lea.is_confirm = 1 AND per_lea.application_days = 0 ),
						0,
						1
					) AS z_order
				FROM
					person_leaves per_lea
				LEFT JOIN
					personnel per ON per.id = per_lea.id_person
				LEFT JOIN
					{$db_name_sod}.offices off ON off.id = per.id_office
				LEFT JOIN
					{$db_name_sod}.firms fir ON fir.id = off.id_firm
				LEFT JOIN
					{$db_name_sod}.objects obj ON obj.id = per.id_region_object
				WHERE
					per_lea.to_arc = 0
					AND per_lea.type = '{$aParams['sResultType']}'
					AND IF
					(
						per_lea.type = 'application',
						( per_lea.leave_types = 'due' OR per_lea.leave_types = 'unpaid' ),
						per_lea.leave_types = 'other'
					)
			";

        if (isset($aParams['nIDFirm']) && !empty($aParams['nIDFirm'])) {
            $sQuery .= "
					AND off.id_firm = {$aParams['nIDFirm']}
				";
        }

        if (isset($aParams['nIDOffice']) && !empty($aParams['nIDOffice'])) {
            $sQuery .= "
					AND off.id = {$aParams['nIDOffice']}
				";
        }

        if (isset($aParams['nIDObject']) && !empty($aParams['nIDObject'])) {
            $sQuery .= "
					AND obj.id = {$aParams['nIDObject']}
				";
        }

        if (isset($aParams['nIDPosition']) && !empty($aParams['nIDPosition'])) {
            $sQuery .= "
					AND per.id_position = {$aParams['nIDPosition']}
				";
        }

        if (isset($aParams['sDate']) && !empty($aParams['sDate'])) {
            $sQuery .= "
					AND IF
					(
						( per_lea.is_confirm = 1 AND per_lea.application_days != 0 ),
						( DATE_FORMAT( per_lea.res_leave_from, '%Y-%m' ) <= '{$aParams['sDate']}' AND DATE_FORMAT( per_lea.res_leave_to, '%Y-%m' ) >= '{$aParams['sDate']}' ),
						( DATE_FORMAT( per_lea.leave_from, '%Y-%m' ) <= '{$aParams['sDate']}' AND DATE_FORMAT( per_lea.leave_to, '%Y-%m' ) >= '{$aParams['sDate']}' )
					)
				";
        }

        if (isset($aParams['nIsToday']) && $aParams['nIsToday']) {
            $sQuery .= "
					AND IF
					(
						( per_lea.is_confirm = 1 AND per_lea.application_days != 0 ),
						( DATE_FORMAT( per_lea.res_leave_from, '%Y-%m-%d' ) <= DATE_FORMAT( NOW(), '%Y-%m-%d' ) AND DATE_FORMAT( per_lea.res_leave_to, '%Y-%m-%d' ) >= DATE_FORMAT( NOW(), '%Y-%m-%d' ) ),
						( DATE_FORMAT( per_lea.leave_from, '%Y-%m-%d' ) <= DATE_FORMAT( NOW(), '%Y-%m-%d' ) AND DATE_FORMAT( per_lea.leave_to, '%Y-%m-%d' ) >= DATE_FORMAT( NOW(), '%Y-%m-%d' ) )
					)
				";
        }

        $sQuery .= "
				ORDER BY person_name
			";

        $aData = $this->select($sQuery);
        //End Query

        //Sortings
        $oParams = Params::getInstance();

        $sSortField = $oParams->get("sfield", "person_name");
        $nSortType = $oParams->get("stype", DBAPI_SORT_ASC);

        if (empty($sSortField)) $sSortField = "person_name";

        foreach ($aData as $key => $row) {
            $person_name[$key] = $row['person_name'];
        }

        if ($nSortType == DBAPI_SORT_ASC) $nSortOrderArray = SORT_ASC;
        if ($nSortType == DBAPI_SORT_DESC) $nSortOrderArray = SORT_DESC;

        $nSortTypeArray = SORT_STRING;

        array_multisort($$sSortField, $nSortOrderArray, $nSortTypeArray, $aData);

        $oResponse->setSort($sSortField, $nSortType);
        //End Sortings

        //Fields
        $aWeekdays = array(0 => "Нд", 1 => "Пн", 2 => "Вт", 3 => "Ср", 4 => "Чт", 5 => "Пт", 6 => "Сб");

        $oResponse->setField("person_name", "Служител");
        $oResponse->setFieldLink("person_name", "openPerson");
        $oResponse->setTitle(1, 1, "");

        for ($i = 1; $i <= $nMonthDays; $i++) {
            $nWeekday = date("w", mktime(0, 0, 0, $nMonth, $i, $nYear));
            if ($nWeekday == 0 || $nWeekday == 6) $sColor = "#FF6666";
            else $sColor = "#FFFFFF";

            $oResponse->setTitle(1, $i + 1, $aWeekdays[$nWeekday], array("style" => "color: {$sColor};"));
            $oResponse->setField("day" . $i, $i, NULL, NULL, NULL, NULL, array("disabled" => "disabled", "style" => "width: 25px; color: {$sColor};"));
        }
        //End Fields

        //Data
        $aFinalData = array();

        //Not Allowed
        foreach ($aData as $nKey => $aValue) {
            if ($aValue['z_order'] == 1) continue;

            $aFinalData[$aValue['id']]['id'] = $aValue['id'];
            $aFinalData[$aValue['id']]['person_name'] = $aValue['person_name'];

            for ($i = 1; $i <= $nMonthDays; $i++) {
                $aFinalData[$aValue['id']]['day' . $i] = "";

                $sColor = "#000000";
                $sLink = "setApplication( {$aValue['id_application']}, {$aValue['id']} );";

                $sLeaveInfo = "Брой Дни : {$aValue['application_days']} \n";
                $sLeaveInfo .= "Начална Дата : {$aValue['leave_from']} \n";
                $sLeaveInfo .= "Крайна Дата : {$aValue['leave_to']} \n";
                $sLeaveInfo .= "Тип : {$aValue['leave_type']}";

                $sPersonInfo = "Фирма : {$aValue['firm_name']} \n";
                $sPersonInfo .= "Регион : {$aValue['office_name']} \n";
                $sPersonInfo .= "Обект : {$aValue['object_name']}";

                if (($nYear . "-" . (strlen($nMonth) < 2 ? "0" . $nMonth : $nMonth) . "-" . (strlen($i) < 2 ? "0" . $i : $i)) >= $aValue['leave_from_raw'] &&
                    ($nYear . "-" . (strlen($nMonth) < 2 ? "0" . $nMonth : $nMonth) . "-" . (strlen($i) < 2 ? "0" . $i : $i)) <= $aValue['leave_to_raw']
                ) {
                    $oResponse->setDataAttributes($aValue['id'], "day" . $i, array("style" => "cursor: pointer; background-color: {$sColor};", "title" => $sLeaveInfo, "onclick" => $sLink));
                }

                $oResponse->setDataAttributes($aValue['id'], "person_name", array("title" => $sPersonInfo));
            }
        }
        //End Not Allowed

        //Allowed
        foreach ($aData as $nKey => $aValue) {
            if ($aValue['z_order'] == 0) continue;

            $aFinalData[$aValue['id']]['id'] = $aValue['id'];
            $aFinalData[$aValue['id']]['person_name'] = $aValue['person_name'];

            for ($i = 1; $i <= $nMonthDays; $i++) {
                $aFinalData[$aValue['id']]['day' . $i] = "";

                if ($aValue['is_confirm'] == 1) {
                    switch ($aValue['type']) {
                        case "unpaid":
                            $sColor = "#FF6464";
                            $sLink = "setApplication( {$aValue['id_application']}, {$aValue['id']} );";
                            break;
                        case "due":
                            $sColor = "#64FF64";
                            $sLink = "setApplication( {$aValue['id_application']}, {$aValue['id']} );";
                            break;
                        case "other":
                            $sColor = "#DC64FF";
                            $sLink = "setHospital( {$aValue['id_application']}, {$aValue['id']} );";
                            break;
                        default:
                            $sColor = "#FFFFFF";
                            $sLink = "setApplication( {$aValue['id_application']}, {$aValue['id']} );";
                    }
                } else {
                    $sColor = "#FF9632";
                    $sLink = "setApplication( {$aValue['id_application']}, {$aValue['id']} );";
                }

                $sLeaveInfo = "Брой Дни : {$aValue['application_days']} \n";
                $sLeaveInfo .= "Начална Дата : {$aValue['leave_from']} \n";
                $sLeaveInfo .= "Крайна Дата : {$aValue['leave_to']} \n";
                $sLeaveInfo .= "Тип : {$aValue['leave_type']}";

                $sPersonInfo = "Фирма : {$aValue['firm_name']} \n";
                $sPersonInfo .= "Регион : {$aValue['office_name']} \n";
                $sPersonInfo .= "Обект : {$aValue['object_name']}";

                if (($nYear . "-" . (strlen($nMonth) < 2 ? "0" . $nMonth : $nMonth) . "-" . (strlen($i) < 2 ? "0" . $i : $i)) >= $aValue['leave_from_raw'] &&
                    ($nYear . "-" . (strlen($nMonth) < 2 ? "0" . $nMonth : $nMonth) . "-" . (strlen($i) < 2 ? "0" . $i : $i)) <= $aValue['leave_to_raw']
                ) {
                    $oResponse->setDataAttributes($aValue['id'], "day" . $i, array("style" => "cursor: pointer; background-color: {$sColor};", "title" => $sLeaveInfo, "onclick" => $sLink));
                }

                $oResponse->setDataAttributes($aValue['id'], "person_name", array("title" => $sPersonInfo));
            }
        }
        //End Allowed

        //End Data

        $oResponse->setData($aFinalData);
    }

    public function getReportQuittance($aParams, DBResponse $oResponse)
    {
        $nIDPerson = ( int )isset($aParams['id_person']) ? $aParams['id_person'] : 0;

        $sQuery = "
				SELECT
					SQL_CALC_FOUND_ROWS
					per_lea.id as _id,
					per_lea.id as id,
					per_lea.year as year,
					DATE_FORMAT( per_lea.date, '%d.%m.%Y' ) AS date,
					CASE SUBSTR( per_lea.leave_from, 6, 2 )
						WHEN '01' THEN 'Януари'
						WHEN '02' THEN 'Февруари'
						WHEN '03' THEN 'Март'
						WHEN '04' THEN 'Април'
						WHEN '05' THEN 'Май'
						WHEN '06' THEN 'Юни'
						WHEN '07' THEN 'Юли'
						WHEN '08' THEN 'Август'
						WHEN '09' THEN 'Септември'
						WHEN '10' THEN 'Октомври'
						WHEN '11' THEN 'Ноември'
						WHEN '12' THEN 'Декември'
					END AS month,
					IF( LENGTH( per_lea.info ) > 30, CONCAT( LEFT( per_lea.info, 20 ), '...' ), per_lea.info ) AS info,
					per_lea.application_days,
					CONCAT(
						CONCAT_WS( ' ', up.fname, up.mname, up.lname ),
						' (',
						DATE_FORMAT( per_lea.updated_time, '%d.%m.%y %H:%i:%s' ),
						')'
					) AS updated_user
				FROM
					person_leaves per_lea
				LEFT JOIN
					personnel AS up ON up.id = per_lea.updated_user
				WHERE
					per_lea.to_arc = 0
					AND per_lea.type = 'application'
					AND per_lea.leave_types = 'quittance'
					AND per_lea.id_person = {$nIDPerson}
			";

        if (isset($aParams['year']) && !empty($aParams['year'])) {
            $sQuery .= "
					AND per_lea.year = {$aParams['year']}
				";
        }

        $this->getResult($sQuery, "date", DBAPI_SORT_DESC, $oResponse);

        $oResponse->setField("year", "година", "Сортирай по година");
        $oResponse->setField("date", "дата", "Сортирай по дата");
        $oResponse->setField("month", "месец", "Сортирай по месец");
        $oResponse->setField("info", "информация", "Сортирай по информация");
        $oResponse->setField("application_days", "дни", "Сортирай по дни", NULL, NULL, NULL, array("DATA_FORMAT" => DF_NUMBER));
        $oResponse->setField("updated_user", "...", "Сортиране по последно редактирал", "images/dots.gif");
        $oResponse->setField("", "", "Изтрий", "images/cancel.gif", "delApplication", "");

        $oResponse->setFIeldLink("leave_types", "setQuittance");
        $oResponse->setFIeldLink("date", "setQuittance");
        $oResponse->setFIeldLink("year", "setQuittance");
        $oResponse->setFIeldLink("month", "setQuittance");
        $oResponse->setFIeldLink("application_days", "setQuittance");
    }

    public function getReportCommonNew(DBResponse $oResponse, $aParams)
    {

        global $db_name_sod, $db_name_personnel;

        $nYear = ( int )(isset($aParams) && !empty($aParams)) ? $aParams['nYear'] : date("Y");

        if ($nYear < 2000 || $nYear > 3000) {
            throw new Exception("Невалидна година!", DBAPI_ERR_INVALID_PARAM);
        }

        $nStartYear = $nYear - 2;

        $sQuery = "

                SELECT SQL_CALC_FOUND_ROWS
                    t.person_id as id,
                    t.person_name,
                    IF(t.all_due_days IS NOT NULL AND t.all_due_days > 0, (t.all_due_days+t.this_year_due),t.this_year_due) as all_due_days,
                    t.used_this_year,
                    t.remaining_days
                FROM
                (
                SELECT
                    p.id as person_id,
                    CONCAT_WS(' ',p.fname, p.mname, p.lname ) as person_name,
                    (SUM(IF(pl.year != {$nYear},pl.due_days,0)) - SUM(IF(pl.is_confirm = 1 AND pl.leave_types = 'due' AND pl.year != {$nYear},pl.application_days,NULL))) as all_due_days,
                    (SELECT pll.due_days FROM {$db_name_personnel}.person_leaves pll WHERE pll.year={$nYear} AND pll.id_person = pl.id_person AND pll.type = 'leave' AND pll.to_arc=0) as this_year_due,
                    SUM(IF(pl.year={$nYear},pl.application_days,0)) as used_this_year,
                    SUM(pl.remaining_days) as remaining_days
                FROM person_leaves pl

                LEFT JOIN {$db_name_personnel}.personnel p ON p.id = pl.id_person
                LEFT JOIN {$db_name_sod}.offices o ON o.id = p.id_office
                WHERE 1
                AND ((pl.year >= {$nStartYear} AND pl.year <={$nYear}) OR pl.year <= 2009)
                AND pl.to_arc = 0
                AND p.status = 'active'
                <where>
                #AND pl.id_person=3945
                GROUP BY pl.id_person
                ) as t

            ";

        $sWhere = '';
        if (isset($aParams['nIDFirm']) && !empty($aParams['nIDFirm'])) {
            $sWhere .= "
					AND o.id_firm = {$aParams['nIDFirm']}
				";
        }

        if (isset($aParams['nIDOffice']) && !empty($aParams['nIDOffice'])) {
            $sWhere .= "
					AND o.id = {$aParams['nIDOffice']}
				";
        }

        if (isset($aParams['nIDObject']) && !empty($aParams['nIDObject'])) {
            $sWhere .= "
					AND p.id_region_object = {$aParams['nIDObject']}
				";
        }

        $sQuery = str_replace('<where>', $sWhere, $sQuery);

        $this->getResult($sQuery, 'person_name', DBAPI_SORT_ASC, $oResponse);
        $oResponse->setField('person_name', 'Служител', 'Сортирай по служител');
        $oResponse->setField('all_due_days', 'Полагаем отпуск', 'Сортирай по полагаем отпуск');
        $oResponse->setField('used_this_year', 'Използван отпуск', 'Сортирай по използван отпуск');
        $oResponse->setField('remaining_days', 'Оставащи дни', 'Сортирай по оставащи дни');
        $oResponse->setFieldLink("person_name", "openPerson");

    }

    public function getReportCommon(DBResponse $oResponse, $aParams)
    {

        global $db_name_sod;

        $nYear = ( int )(isset($aParams) && !empty($aParams)) ? $aParams['nYear'] : date("Y");

        if ($nYear < 2000 || $nYear > 3000) {
            throw new Exception("Невалидна година!", DBAPI_ERR_INVALID_PARAM);
        }

        $startYear = $nYear - 2;

        $sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					per.id AS id,
					CONCAT_WS( ' ', per.fname, per.mname, per.lname ) AS person_name,
					
					/*(
						SELECT
							SUM( due_days )
						FROM
							person_leaves
						WHERE
							to_arc = 0
							AND type = 'leave'
							AND leave_types = 'due'
							AND year <= {$nYear}
							AND id_person = per.id
					) AS due_days,*/


					(
						SELECT
							SUM( due_days )
						FROM
							person_leaves
						WHERE
							to_arc = 0
							AND type = 'leave'
							AND leave_types = 'due'
							AND ((year <= {$nYear} AND year >= {$startYear}) OR year <= 2009)
							AND id_person = per.id
					) AS due_days,

					(
						SELECT
							SUM( pl.application_days )
						FROM
							person_leaves pl
						LEFT JOIN
							code_leave cl ON cl.id = pl.id_code_leave
						WHERE
							pl.to_arc = 0
							AND type = 'application'
							AND IF
							(
								pl.id_code_leave != 0,
								(
									cl.to_arc = 0
									AND cl.is_due_leave = 1
								),
								1
							)
							AND
							(
								pl.leave_types = 'due'
								OR
								pl.leave_types = 'student'
								OR
								pl.leave_types = 'quittance'
							)
							AND pl.year < {$nYear}
							AND pl.id_person = per.id
					) AS used_days_prev,
					
					(
						SELECT
							SUM( pl.application_days )
						FROM
							person_leaves pl
						LEFT JOIN
							code_leave cl ON cl.id = pl.id_code_leave
						WHERE
							pl.to_arc = 0
							AND type = 'application'
							AND IF
							(
								pl.id_code_leave != 0,
								(
									cl.to_arc = 0
									AND cl.is_due_leave = 1
								),
								1
							)
							AND
							(
								pl.leave_types = 'due'
								OR
								pl.leave_types = 'student'
								OR
								pl.leave_types = 'quittance'
							)
							AND pl.year = {$nYear}
							AND pl.id_person = per.id
					) AS used_days_all,
					
					(
						SELECT
							SUM( pl.application_days )
						FROM
							person_leaves pl
						LEFT JOIN
							code_leave cl ON cl.id = pl.id_code_leave
						WHERE
							pl.to_arc = 0
							AND IF
							(
								pl.id_code_leave != 0,
								(
									cl.to_arc = 0
									AND cl.is_due_leave = 1
								),
								1
							)
							AND pl.type = 'application'
							AND pl.leave_types = 'due'
							AND pl.year = {$nYear}
							AND pl.id_person = per.id
					) AS used_days
					
				FROM
					personnel per
				LEFT JOIN
					{$db_name_sod}.offices off ON off.id = per.id_office
				LEFT JOIN
					person_leaves per_lea ON per_lea.id_person = per.id
				WHERE
					per.to_arc = 0
					AND per_lea.to_arc = 0
					AND per.status = 'active'
			";

        if (isset($aParams['nIDFirm']) && !empty($aParams['nIDFirm'])) {
            $sQuery .= "
					AND off.id_firm = {$aParams['nIDFirm']}
				";
        }

        if (isset($aParams['nIDOffice']) && !empty($aParams['nIDOffice'])) {
            $sQuery .= "
					AND off.id = {$aParams['nIDOffice']}
				";
        }

        if (isset($aParams['nIDObject']) && !empty($aParams['nIDObject'])) {
            $sQuery .= "
					AND per.id_region_object = {$aParams['nIDObject']}
				";
        }

        $sQuery .= "
				GROUP BY per.id
			";

        $aFinalData = $this->select($sQuery);

        foreach ($aFinalData as $nKey => &$aValue) {
            $aValue['due_days'] = $aValue['due_days'] - $aValue['used_days_prev'];
            $aValue['remain_days'] = $aValue['due_days'] - $aValue['used_days_all'];
        }

        //Sortings
        $oParams = Params::getInstance();

        $sSortField = $oParams->get("sfield", "person_name");
        $nSortType = $oParams->get("stype", DBAPI_SORT_ASC);

        if (empty($sSortField)) $sSortField = "person_name";

        foreach ($aFinalData as $key => $row) {
            $id[$key] = $row['id'];
            $person_name[$key] = $row['person_name'];
            $due_days[$key] = $row['due_days'];
            $used_days[$key] = $row['used_days'];
            $remain_days[$key] = $row['remain_days'];
        }

        if ($nSortType == DBAPI_SORT_ASC) $nSortOrderArray = SORT_ASC;
        if ($nSortType == DBAPI_SORT_DESC) $nSortOrderArray = SORT_DESC;

        if ($sSortField == "id" ||
            $sSortField == "due_days" ||
            $sSortField == "used_days" ||
            $sSortField == "remain_days"
        ) $nSortTypeArray = SORT_NUMERIC;
        else $nSortTypeArray = SORT_STRING;

        array_multisort($$sSortField, $nSortOrderArray, $nSortTypeArray, $aFinalData);

        $oResponse->setSort($sSortField, $nSortType);
        //End Sortings

        //Paging
        $nPage = $oParams->get("current_page", 1);

        $nRowCount = $_SESSION['userdata']['row_limit'];
        $nRowOffset = ($nPage - 1) * $nRowCount;
        $nRowTotal = count($aFinalData);

        $nIndex = 0;
        $aPagedData = array();
        foreach ($aFinalData as $FDKey => $FDValue) {
            if ($nIndex >= $nRowOffset && $nIndex < ($nRowOffset + $nRowCount)) {
                $aPagedData[$FDKey] = $FDValue;
            }

            $nIndex++;
        }

        $oResponse->setPaging($nRowCount, $nRowTotal, ceil($nRowOffset / $nRowCount) + 1);
        //End Paging

        $oResponse->setField("person_name", "Служител", "Сортирай по Служител", NULL, NULL, NULL, array("DATA_FORMAT" => DF_STRING));
        $oResponse->setField("due_days", "Полагаем Отпуск", "Сортирай по Полагаем Отпуск", NULL, NULL, NULL, array("DATA_FORMAT" => DF_CENTER));
        $oResponse->setField("used_days", "Използван Отпуск", "Сортирай по Използван Отпуск", NULL, NULL, NULL, array("DATA_FORMAT" => DF_CENTER));
        $oResponse->setField("remain_days", "Оставащи Дни", "Сортирай по Оставащи Дни", NULL, NULL, NULL, array("DATA_FORMAT" => DF_CENTER));

        $oResponse->setFieldLink("person_name", "openPerson");

        if (isset($aParams['api_action']) && ($aParams['api_action'] == "export_to_xls" || $aParams['api_action'] == "export_to_pdf")) {
            $oResponse->setData($aFinalData);
        } else {
            $oResponse->setData($aPagedData);
        }
    }

    function getMonthStatForApplication($nIDApplication, $bResolved = true)
    {
        $oDBHolidays = new DBHolidays();

        if (empty($nIDApplication) || !is_numeric($nIDApplication)) return array();

        $aData = $this->getRecord($nIDApplication);
        $aMonthStat = array();

        if (empty($aData)) return array();

        //Initial Data
        $sStartDate = ($bResolved) ? substr($aData['res_leave_from'], 0, 10) : substr($aData['leave_from'], 0, 10);
        $nDays = ($bResolved) ? $aData['application_days'] : $aData['application_days_offer'];

        $aStartDate = explode("-", $sStartDate);
        if (!isset($aStartDate[0]) || !isset($aStartDate[1]) || !isset($aStartDate[2])) {
            return "0000-00-00";
        } else {
            $nYear = ( int )$aStartDate[0];
            $nMonth = ( int )$aStartDate[1];
            $nDay = ( int )$aStartDate[2];
        }

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

        return $aMonthStat;
    }

    public function getPersonLeavesForMonth($nYear, $nMonth)
    {
        //Validation
        if (empty($nYear) || !is_numeric($nYear) || $nYear < 2000 || $nYear > 3000) {
            return array();
        }

        if (empty($nMonth) || !is_numeric($nMonth) || $nMonth < 1 || $nMonth > 12) {
            return array();
        }
        //End Validation

        $sYearMonth = $nYear . "-" . LPAD($nMonth, 2, 0);

        $sQuery = "
				SELECT
					per_lea.id AS id,
					id_person AS id_person,
					CONCAT_WS( ' ', per.fname, per.mname, per.lname ) AS person_name,
					IF( per_lea.is_confirm = 1, DATE_FORMAT( per_lea.res_leave_from, '%Y-%m-%d' ), DATE_FORMAT( per_lea.leave_from, '%Y-%m-%d' ) ) AS leave_from,
					IF( per_lea.is_confirm = 1, DATE_FORMAT( per_lea.res_leave_to, '%Y-%m-%d' ), DATE_FORMAT( per_lea.leave_to, '%Y-%m-%d' ) ) AS leave_to,
					IF( per_lea.is_confirm = 1, DATE_FORMAT( per_lea.res_leave_from, '%d.%m.%Y' ), DATE_FORMAT( per_lea.leave_from, '%d.%m.%Y' ) ) AS leave_from_bg,
					IF( per_lea.is_confirm = 1, DATE_FORMAT( per_lea.res_leave_to, '%d.%m.%Y' ), DATE_FORMAT( per_lea.leave_to, '%d.%m.%Y' ) ) AS leave_to_bg,
					per_lea.application_days as days,
					CASE per_lea.type
						WHEN 'application' THEN
							CASE per_lea.leave_types
								WHEN 'due' THEN 'платен'
								WHEN 'unpaid' THEN 'неплатен'
								ELSE ''
							END
						WHEN 'hospital' THEN 'болничен'
					END AS leave_type,
					IF( per_lea.is_confirm = 1, 'потвърден', 'непотвърден' ) AS is_confirm,
					per_lea.is_confirm AS is_confirm_num,
					per_lea.type AS application_type
				FROM
					person_leaves per_lea
				LEFT JOIN
					personnel per ON per.id = per_lea.id_person
				WHERE
					per_lea.to_arc = 0
					AND per.to_arc = 0
					AND
					(
						(
							per_lea.type = 'application'
							AND
							(
								per_lea.leave_types = 'unpaid'
								OR per_lea.leave_types = 'due'
							)
						)
						OR
						(
							per_lea.type = 'hospital'
						)
					)
					AND IF
					(
						per_lea.is_confirm = 1,
						( SUBSTR( per_lea.res_leave_from, 1, 7 ) <= '{$sYearMonth}' AND SUBSTR( per_lea.res_leave_to, 1, 7 ) >= '{$sYearMonth}' ),
						( SUBSTR( per_lea.leave_from, 1, 7 ) <= '{$sYearMonth}' AND SUBSTR( per_lea.leave_to, 1, 7 ) >= '{$sYearMonth}' )
					)
			";

        $aData = $this->select($sQuery);

        $aOutput = array();

        foreach ($aData as $nKey => $aValue) {
            $aOutput[$aValue['id_person']][$aValue['id']]['leave_from'] = $aValue['leave_from'];
            $aOutput[$aValue['id_person']][$aValue['id']]['leave_to'] = $aValue['leave_to'];
            $aOutput[$aValue['id_person']][$aValue['id']]['leave_from_bg'] = $aValue['leave_from_bg'];
            $aOutput[$aValue['id_person']][$aValue['id']]['leave_to_bg'] = $aValue['leave_to_bg'];
            $aOutput[$aValue['id_person']][$aValue['id']]['person_name'] = $aValue['person_name'];
            $aOutput[$aValue['id_person']][$aValue['id']]['leave_type'] = $aValue['leave_type'];
            $aOutput[$aValue['id_person']][$aValue['id']]['application_type'] = $aValue['application_type'];
            $aOutput[$aValue['id_person']][$aValue['id']]['is_confirm'] = $aValue['is_confirm'];
            $aOutput[$aValue['id_person']][$aValue['id']]['is_confirm_num'] = $aValue['is_confirm_num'];
            $aOutput[$aValue['id_person']][$aValue['id']]['days'] = $aValue['days'];
        }

        return $aOutput;
    }

    public function getHowManyDaysToAdd($nIDPerson)
    {
        global $db_personnel;

        if (empty($nIDPerson) || !is_numeric($nIDPerson)) {
            return 20;
        }

        $sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					year,
					due_days
				FROM
					person_leaves
				WHERE
					to_arc = 0
					AND type = 'leave'
					AND leave_types = 'due'
					AND id_person = {$nIDPerson}
				ORDER BY year ASC
			";

        $aData = $this->select($sQuery);
        $nRows = ( int )$db_personnel->foundRows();

        if ($nRows < 2) {
            return 20;
        } else {
            $aDays = end($aData);
            return isset($aDays['due_days']) ? $aDays['due_days'] : 20;
        }
    }

    public function scheduleGetHospitalsBeforeMonth($nIDPerson, $nYear, $nMonth)
    {
        // Validation
        if (empty($nIDPerson) || !is_numeric($nIDPerson)) return "00:00:00";
        if ($nMonth < 1 || $nMonth > 12 || strlen($nYear) != 4) return "00:00:00";
        // End Validation

        $oDBHolidays = new DBHolidays();

        // Initialization
//			$sStartDate = ( $nMonth <= 6 )
//				? $nYear . "-01"
//				: $nYear . "-07";

        if ($nMonth <= 3) {
            $sStartDate = $nYear . "-01";
        } else if ($nMonth > 3 && $nMonth <= 6) {
            $sStartDate = $nYear . "-04";
        } else if ($nMonth > 6 && $nMonth <= 9) {
            $sStartDate = $nYear . "-07";
        } else if ($nMonth > 9 && $nMonth <= 12) {
            $sStartDate = $nYear . "-10";
        }

        $nMonth--;
        if ($nMonth < 1) {
            $nMonth = 12;
            $nYear--;
        }

        $sEndDate = $nYear . "-" . ((strlen($nMonth) < 2) ? ("0" . $nMonth) : $nMonth);

        if ($sEndDate < $sStartDate) return "00:00:00";
        // End Initialization

        $sQuery = "
				SELECT
					IF( per_lea.is_confirm = 1, per_lea.application_days, per_lea.application_days_offer ) AS application_days,
					IF( per_lea.is_confirm = 1, DATE_FORMAT( per_lea.res_leave_from, '%Y-%m-%d' ), DATE_FORMAT( per_lea.leave_from, '%Y-%m-%d' ) ) AS leave_from,
					IF( per_lea.is_confirm = 1, DATE_FORMAT( per_lea.res_leave_to, '%Y-%m-%d' ), DATE_FORMAT( per_lea.leave_to, '%Y-%m-%d' ) ) AS leave_to
				FROM
					person_leaves per_lea
				WHERE
					per_lea.to_arc = 0
					AND per_lea.type = 'hospital'
					AND id_person = {$nIDPerson}
					AND IF
					(
						per_lea.is_confirm = 1,
						(
							NOT
							(
								( DATE_FORMAT( per_lea.res_leave_from, '%Y-%m' ) < '{$sStartDate}' AND DATE_FORMAT( per_lea.res_leave_to, '%Y-%m' ) < '{$sStartDate}' )
								OR
								( DATE_FORMAT( per_lea.res_leave_from, '%Y-%m' ) > '{$sEndDate}' AND DATE_FORMAT( per_lea.res_leave_to, '%Y-%m' ) > '{$sEndDate}' )
							)
						),
						(
							NOT
							(
								( DATE_FORMAT( per_lea.leave_from, '%Y-%m' ) < '{$sStartDate}' AND DATE_FORMAT( per_lea.leave_to, '%Y-%m' ) < '{$sStartDate}' )
								OR
								( DATE_FORMAT( per_lea.leave_from, '%Y-%m' ) > '{$sEndDate}' AND DATE_FORMAT( per_lea.leave_to, '%Y-%m' ) > '{$sEndDate}' )
							)
						)
					)
			";

        $aHospitals = $this->select($sQuery);

        $nApplicationDays = 0;
        foreach ($aHospitals as $aHospital) {
            $nApplicationDays += $aHospital['application_days'];
            $nDaysInMonth = date("t", mktime(0, 0, 0, $nMonth, 1, $nYear));

            if ($aHospital['leave_from'] < ($sStartDate . "-01")) {
                $nApplicationDays -= $oDBHolidays->getWorkdaysInPeriod($aHospital['leave_from'], ($sStartDate . "-01"), true, false);
            }

            if ($aHospital['leave_to'] > ($sEndDate . "-{$nDaysInMonth}")) {
                $nApplicationDays -= $oDBHolidays->getWorkdaysInPeriod(($sForDate . "-{$nDaysInMonth}"), $aHospital['leave_to'], false, true);
            }
        }

        return SecToTime(($nApplicationDays * 8 * 60 * 60));
    }

    public function usePaydLeave($nIDPerson, $days, $year = null)
    {
        if (empty($nIDPerson) || !is_numeric($nIDPerson) || empty($days) || !is_numeric($days)) {
            return false;
        }

        $year = is_null($year) ? date('Y') : $year;
        $y = $year;

        if($year == date('Y')) {
            while ($days) {
                if ($y < $year - 2 && $y != 2009) {
                    $y--;
                    continue;
                }
                if ($remaing_days_tmp = $this->getRemainingDays($nIDPerson, $y)) {
//                    echo $remaing_days_tmp['remaining_days']."||".$days."\n";

                    if ($days > $remaing_days_tmp['remaining_days']) {
                        $aData['id'] = $remaing_days_tmp['id'];
                        $aData['remaining_days'] = 0;
                        $days -= $remaing_days_tmp['remaining_days'];
                        $this->update($aData);
                        $y -= 1;
                    } else {
                        $aData['id'] = $remaing_days_tmp['id'];
                        $aData['remaining_days'] = $remaing_days_tmp['remaining_days'] - $days;
                        $this->update($aData);
                        return true;
                    }
                } else {
                    continue;
                }
                if ($y < 2009) {
                    break;
                }
            }
        } else {
            while ($days) {
                if ($y < $year - 2 && $y != 2009) {
                    ++$y;
                    continue;
                }
                if ($remaing_days_tmp = $this->getRemainingDays($nIDPerson, $y)) {
//                    echo $remaing_days_tmp['remaining_days']."||".$days."\n";

                    if ($days > $remaing_days_tmp['remaining_days']) {
                        $aData['id'] = $remaing_days_tmp['id'];
                        $aData['remaining_days'] = 0;
                        $days -= $remaing_days_tmp['remaining_days'];
                        $this->update($aData);
                        $y += 1;
                    } else {
                        $aData['id'] = $remaing_days_tmp['id'];
                        $aData['remaining_days'] = $remaing_days_tmp['remaining_days'] - $days;
                        $this->update($aData);
                        return true;
                    }
                } else {
                    continue;
                }
                if ($y > date('Y')) {
                    break;
                }
            }
        }
        return true;
    }

    public function devalidateLastLeave($nIDPerson, $nYear, $nDays)
    {

        if (empty($nIDPerson) || !is_numeric($nIDPerson) || empty($nYear) || !is_numeric($nYear) || empty($nDays) || !is_numeric($nDays)) {
            return false;
        }


        $year = $nYear;
        $days = $nDays;

        while ($days) {

            if ($year < (date('Y') - 2)) {
                $year = 2009;
            }

            $due_days_year = $this->getRemainingDays($nIDPerson, $year);

            if (($due_days_year['remaining_days'] + $$days) > $due_days_year['due_days'] && $year > 2009) {

                $aData['id'] = $due_days_year['id'];
                $aData['remaining_days'] = $due_days_year['due_days'];
                $this->update($aData);
                $days -= ($due_days_year['remaining_days'] + $$days) - $due_days_year['due_days'];
                $year--;
            } else {
                $aData['id'] = $due_days_year['id'];
                $aData['remaining_days'] = $due_days_year['remaining_days'] + $days;
                $this->update($aData);
                $days = 0;
            }
        }
    }

    public function devalidateLastLeaveOnlyYear($nIDPerson, $nYear, $nDays) {

        if(empty($nIDPerson) || !is_numeric($nIDPerson) || empty($nYear) || !is_numeric($nYear) || empty($nDays) || !is_numeric($nDays)) {
            return false;
        }

        $year = $nYear;
        $days = $nDays;

        $due_days_year = $this->getRemainingDays($nIDPerson, $year);

        $aData['id'] = $due_days_year['id'];
        $aData['remaining_days'] = $due_days_year['remaining_days'] + $days;
        $this->update($aData);
    }

    public function getRemainingDays($nIDPerson, $year)
    {
        if (empty($nIDPerson) || !is_numeric($nIDPerson) || empty($year) || !is_numeric($year)) {
            return false;
        }

        $sQuery = "
            SELECT
                pl.id,
                pl.remaining_days,
                pl.due_days
            FROM person_leaves pl
            WHERE 1
                AND pl.year = {$year}
               AND pl.type = 'leave'
               AND pl.leave_types = 'due'
               AND pl.id_person = {$nIDPerson}
               AND pl.to_arc = 0
               GROUP BY pl.id_person
            ";

        return $this->selectOnce($sQuery);
    }

    public function getAllRemainingDays($nIDPerson)
    {
        global $db_name_personnel;
        if (empty($nIDPerson) || !is_numeric($nIDPerson)) {
            return false;
        }

        $year = date('Y');
        $sQuery = "
            SELECT
                pl.id,
                SUM(pl.remaining_days) as remaining_days
            FROM {$db_name_personnel}.person_leaves pl
            WHERE 1
               AND (pl.year <= {$year}
	  				AND pl.year >= {$year}-2
   				    OR pl.year = 2009)
               AND pl.type = 'leave'
               AND pl.leave_types = 'due'
               AND pl.id_person = {$nIDPerson}
               AND pl.to_arc = 0
               GROUP BY pl.id_person
            ";
        return $this->selectOnce($sQuery);
    }

    public function nullAllRemaingDays($nIDPerson)
    {
        if (empty($nIDPerson) || !is_numeric($nIDPerson)) {
            return false;
        }

        $sQuery = "UPDATE person_leaves pl SET pl.remaining_days = 0 WHERE pl.id_person = {$nIDPerson}";
        $this->select($sQuery);

    }

    public function addQuittance($nIDPerson, $nDays)
    {
        if (empty($nIDPerson) || !is_numeric($nIDPerson) && empty($nDays) || !is_numeric($nDays)) {
            return false;
        }

        $aData['id'] = 0;
        $aData['id_person'] = $nIDPerson;
        $aData['type'] = 'application';
        $aData['year'] = date('Y');
        $aData['date'] = date('Y-m-d');
        $aData['leave_types'] = 'quittance';
        $aData['leave_from'] = date('Y-m-d');
        $aData['application_days'] = $nDays;
        $aData['res_leave_from'] = date('Y-m-d');
        $aData['is_confirm'] = 1;

        $this->update($aData);

        $this->nullAllRemaingDays($nIDPerson);

    }

    public function scheduleGetHospitalsForMonth($nIDPerson, $nYear, $nMonth)
    {
        // Validation
        if (empty($nIDPerson) || !is_numeric($nIDPerson)) return "00:00:00";
        if ($nMonth < 1 || $nMonth > 12 || strlen($nYear) != 4) return "00:00:00";
        // End Validation

        $oDBHolidays = new DBHolidays();

        // Initialization
        $sForDate = $nYear . "-" . ((strlen($nMonth) < 2) ? ("0" . $nMonth) : $nMonth);
        // End Initialization

        $sQuery = "
				SELECT
					IF( per_lea.is_confirm = 1, per_lea.application_days, per_lea.application_days_offer ) AS application_days,
					IF( per_lea.is_confirm = 1, DATE_FORMAT( per_lea.res_leave_from, '%Y-%m-%d' ), DATE_FORMAT( per_lea.leave_from, '%Y-%m-%d' ) ) AS leave_from,
					IF( per_lea.is_confirm = 1, DATE_FORMAT( per_lea.res_leave_to, '%Y-%m-%d' ), DATE_FORMAT( per_lea.leave_to, '%Y-%m-%d' ) ) AS leave_to
				FROM
					person_leaves per_lea
				WHERE
					per_lea.to_arc = 0
					AND per_lea.type = 'hospital'
					AND id_person = {$nIDPerson}
					AND IF
					(
						per_lea.is_confirm = 1,
						( DATE_FORMAT( per_lea.res_leave_from, '%Y-%m' ) <= '{$sForDate}' AND DATE_FORMAT( per_lea.res_leave_to, '%Y-%m' ) >= '{$sForDate}' ),
						( DATE_FORMAT( per_lea.leave_from, '%Y-%m' ) <= '{$sForDate}' AND DATE_FORMAT( per_lea.leave_to, '%Y-%m' ) >= '{$sForDate}' )
					)
			";

        $aHospitals = $this->select($sQuery);

        $nApplicationDays = 0;
        foreach ($aHospitals as $aHospital) {
            $nApplicationDays += $aHospital['application_days'];
            $nDaysInMonth = date("t", mktime(0, 0, 0, $nMonth, 1, $nYear));

            if ($aHospital['leave_from'] < ($sForDate . "-01")) {
                $nApplicationDays -= $oDBHolidays->getWorkdaysInPeriod($aHospital['leave_from'], ($sForDate . "-01"), true, false);
            }

            if ($aHospital['leave_to'] > ($sForDate . "-{$nDaysInMonth}")) {
                $nApplicationDays -= $oDBHolidays->getWorkdaysInPeriod(($sForDate . "-{$nDaysInMonth}"), $aHospital['leave_to'], false, true);
            }
        }

        return SecToTime(($nApplicationDays * 8 * 60 * 60));
    }


    /**
     * Функията връща информация дали служителя е в отпуска или болничен за подадената дата
     * @param $nIDPerson
     * @param $sDate - '2014-11-27'
     * @return array
     */
    public function personInHospitalOrApplicationByIDPersonAndDate($nIDPerson, $sDate)
    {

        if (empty($nIDPerson) || empty($sDate)) {
            return array();
        }

        $sQuery = "
                SELECT
                pl.*,
                DATE_FORMAT(pl.res_leave_from , '%d.%m.%Y %H:%i:%s') AS format_res_leave_from,
                DATE_FORMAT(pl.res_leave_to , '%d.%m.%Y %H:%i:%s') AS format_res_leave_to
                FROM
                person_leaves pl
                WHERE 1
                AND pl.to_arc = 0
                AND pl.id_person = {$nIDPerson}
                AND pl.is_confirm = 1
                AND pl.application_days > 0
                AND pl.`type` IN ('hospital','application')
                AND '{$sDate}' BETWEEN pl.res_leave_from AND pl.res_leave_to
            ";

        return $this->selectOnce($sQuery);
    }

    public function getYearsWithRemainingDays($nIDPerson)
    {
        $viableYear = date('Y') - 2;
        $sQuery = "
				SELECT
				    id,
                    year
				FROM person_leaves pl
				WHERE `type` = 'leave'
				AND id_person = {$nIDPerson}
				AND (year >= {$viableYear} OR year = 2009 )
				AND remaining_days > 0
				AND to_arc = 0
				ORDER BY id DESC

			";

        return $this->selectAssoc($sQuery);

    }

    public function getDueDaysByIDPeronAndYear($nIDPerson,$sYear)
    {

        if(empty($nIDPerson) || empty($sYear))
            return 0;

        $sQuery = "
				SELECT 
					sum(application_days) as used_days
				FROM
					person_leaves
				WHERE 1
					AND to_arc = 0
					AND type = 'application'
					AND leave_types = 'due'
					AND is_confirm = 1
					AND id_person = {$nIDPerson}
					AND `year` = $sYear

			";

        $aData = $this->selectOnce($sQuery);

        return !empty($aData['used_days'])? $aData['used_days'] : 0;

    }
}

?>

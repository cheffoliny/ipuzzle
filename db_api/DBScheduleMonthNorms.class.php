<?php

class DBScheduleMonthNorms extends DBBase2 {

    public function __construct() {
        global $db_sod;
        parent::__construct($db_sod, 'schedule_month_norms');
    }

    public function getYears() {
        $sQuery = "
					SELECT DISTINCT
						SUBSTR( smn.month, 1, 4 ) AS year
					FROM
						schedule_month_norms smn
					WHERE
						smn.to_arc = 0;
			";

        return $this->select($sQuery);
    }

    public function getReport(DBResponse $oResponse) {
        global $db_name_personnel;

        $aData = array();

        $right_edit = false;

        if (!empty($_SESSION['userdata']['access_right_levels'])) {
            if (in_array('schedule_month_norms', $_SESSION['userdata']['access_right_levels'])) {
                $right_edit = true;
            }
        }

        //Get entered years.
        $aFoundYears = $this->getYears();

        //Create Columns
        $aData[1]['month'] = "Януари";
        $aData[2]['month'] = "Февруари";
        $aData[3]['month'] = "Март";
        $aData[4]['month'] = "Април";
        $aData[5]['month'] = "Май";
        $aData[6]['month'] = "Юни";
        $aData[7]['month'] = "Юли";
        $aData[8]['month'] = "Август";
        $aData[9]['month'] = "Септември";
        $aData[10]['month'] = "Октомври";
        $aData[11]['month'] = "Ноември";
        $aData[12]['month'] = "Декември";
        $oResponse->setTitle(1, 1, ' ', $aAttributes = array('colspan' => 2));
        $oResponse->setField('month', 'Месец', '', NULL, NULL, NULL, array("DATA_FORMAT" => DF_STRING));

        $sStartHeaderIndex = 5;

        $aYearsRecord = array();
        foreach ($aFoundYears as $aFoundYear) {
            $aYearsRecord[] = $aFoundYear['year'];
            //Set Fields
            $oResponse->setField('shifts' . $aFoundYear['year'], 'Макс. Брой Смени', '', NULL, NULL, NULL, array("DATA_FORMAT" => DF_ZEROLEADNUM));
            $oResponse->setField('hours' . $aFoundYear['year'], 'Макс. Брой Часове', '', NULL, NULL, NULL, array("DATA_FORMAT" => DF_ZEROLEADNUM));
            $oResponse->setField('year_overtime' . $aFoundYear['year'], 'Год. норма извънреден труд', '', NULL, NULL, NULL, array("DATA_FORMAT" => DF_ZEROLEADNUM));
            $oResponse->setField('is_start_month_report' . $aFoundYear['year'], "Начален мес. за отчитане", '', 'images/confirm.gif', NULL, NULL, array("DATA_FORMAT" => DF_ZEROLEADNUM));
            $oResponse->setField('updated_by' . $aFoundYear['year'], '...', '', 'images/dots.gif', NULL, NULL, array("DATA_FORMAT" => DF_CENTER));
            //End Set Fields
            //Set Editable Fields
            $oResponse->setFieldAttributes('shifts' . $aFoundYear['year'], array('type' => 'text'));
            $oResponse->setFieldAttributes('hours' . $aFoundYear['year'], array('type' => 'text'));
            $oResponse->setFieldAttributes('year_overtime' . $aFoundYear['year'], array('type' => 'text'));
            //End Set Editable Fields
            //Total Variables
            $nTotalHours = 0;
            $nTotalShifts = 0;
            //End Total Variables

            $oResponse->setTitle(1, $sStartHeaderIndex, $aFoundYear['year'], $aAttributes = array('colspan' => 5));
            $sStartHeaderIndex += 5;

            //Set Data
            $aMonthData = array();
            $aMonthData = $this->getActiveNormsByYear((int) $aFoundYear['year']);
            //APILog::Log(32131,$aMonthData);
            for ($i = 1; $i <= 12; $i++) {
                $aData[$i]['id'] = $i;

                $aData[$i]['shifts' . $aFoundYear['year']] = ( isset($aMonthData[$i]['shifts']) && !empty($aMonthData[$i]['shifts']) ) ? $aMonthData[$i]['shifts'] : "0";

                $nTotalShifts += (int) $aData[$i]['shifts' . $aFoundYear['year']];

                $aData[$i]['hours' . $aFoundYear['year']] = ( isset($aMonthData[$i]['hours']) && !empty($aMonthData[$i]['hours']) ) ? $aMonthData[$i]['hours'] : "0";

                $nTotalHours += (int) $aData[$i]['hours' . $aFoundYear['year']];

                $aData[$i]['year_overtime' . $aFoundYear['year']] = ( isset($aMonthData[$i]['year_overtime']) && !empty($aMonthData[$i]['year_overtime']) ) ? $aMonthData[$i]['year_overtime'] : "0";


                $aData[$i]['is_start_month_report' . $aFoundYear['year']] = ( isset($aMonthData[$i]['is_start_month_report']) && !empty($aMonthData[$i]['is_start_month_report']) ) ? 1 : 0;

                $aData[$i]['updated_by' . $aFoundYear['year']] = ( isset($aMonthData[$i]['updated_by']) && !empty($aMonthData[$i]['updated_by']) ) ? $aMonthData[$i]['updated_by'] : "---";

                //Set Links
                if ($right_edit) {
                    $oResponse->setDataAttributes($i, 'shifts' . $aFoundYear['year'], array("style" => "cursor: pointer;", "onclick" => "onClickItemShifts( {$i}, {$aFoundYear['year']} );"));
                    $oResponse->setDataAttributes($i, 'hours' . $aFoundYear['year'], array("style" => "cursor: pointer;", "onclick" => "onClickItemHours( {$i}, {$aFoundYear['year']} );"));
                    $oResponse->setDataAttributes($i, 'year_overtime' . $aFoundYear['year'], array("style" => "cursor: pointer;", "onclick" => "onClickItemOverTime( {$i}, {$aFoundYear['year']} );"));
                }
                //End Set Links
            }
            //End Set Data
            //Set Totals
            $oResponse->addTotal('shifts' . $aFoundYear['year'], $nTotalShifts);
            $oResponse->addTotal('hours' . $aFoundYear['year'], $nTotalHours);
            //End Set Totals
        }
        //End Create Columns

        $sYearsRecord = implode("|", $aYearsRecord);
        $oResponse->setFormElement("form1", "sYearsFound", array("value" => $sYearsRecord), $sYearsRecord);

        $oResponse->setData($aData);

        if ($right_edit) {
            $oResponse->setFieldLink('month', 'editNorms');
        }
    }

    public function getActiveNormsByYear($nYear) {
        global $db_name_personnel;

        if (empty($nYear) || !is_numeric($nYear) || strlen($nYear) != 4) {
            throw new Exception("Невалиден година!", DBAPI_ERR_INVALID_PARAM);
        }

        $aReturnData = array();

        $sQuery = "
				SELECT
					smn.id AS id,
					SUBSTR( smn.month, 5, 2 ) AS month,
					smn.norm_shifts AS shifts,
					smn.norm_hours AS hours,
					smn.is_start_month_report AS is_start_month_report,
					smn.year_overtime AS year_overtime,
					IF
					(
						smn.updated_user,
						CONCAT( CONCAT_WS( ' ', upd.fname, upd.mname, upd.lname ), ' [ ', DATE_FORMAT( smn.updated_time, '%d.%m.%Y %H:%i:%s' ), ' ]' ),
						''
					) AS updated_by
				FROM
					schedule_month_norms smn
				LEFT JOIN
					{$db_name_personnel}.personnel upd ON upd.id = smn.updated_user
				WHERE
					smn.to_arc = 0
					AND SUBSTR( smn.month, 1, 4 ) LIKE '{$nYear}%'
			";

        $aData = $this->select($sQuery);

        foreach ($aData as $aElement) {
            $nKey = (int) $aElement['month'];

            $aReturnData[$nKey] = array();
            $aReturnData[$nKey]['id'] = $aElement['id'];
            $aReturnData[$nKey]['shifts'] = $aElement['shifts'];
            $aReturnData[$nKey]['hours'] = $aElement['hours'];
            $aReturnData[$nKey]['is_start_month_report'] = $aElement['is_start_month_report'];
            $aReturnData[$nKey]['year_overtime'] = $aElement['year_overtime'];
            $aReturnData[$nKey]['updated_by'] = $aElement['updated_by'];
        }

        return $aReturnData;
    }

    /* stanislav - i az q polzvam taq funkciq, ako pravi6 promeni po neq sa obadi ili si napi6i nova :) */

    public function getActiveNormsByMonth($nYearMonth) {
        if (empty($nYearMonth) || !is_numeric($nYearMonth)) {
            throw new Exception("Невалиден месец!", DBAPI_ERR_INVALID_PARAM);
        }

        $sQuery = "
				SELECT
					smn.id,
					CASE SUBSTR( smn.month, 5, 2 )
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
					smn.norm_shifts AS shifts,
					smn.norm_hours AS hours,
					smn.year_overtime AS year_overtime,
					smn.is_start_month_report AS is_start_month_report
				FROM
					schedule_month_norms smn
				WHERE
					smn.to_arc = 0
					AND smn.month = {$nYearMonth}
				LIMIT 1
			";

        return $this->selectOnce($sQuery);
    }

    /**
     * Връща сумата часове по норма за полугодието, до посочения месец.
     *
     * @param int $nYear
     * @param int $nMonth
     * @param bool $bInclude ( Включително последния месец )
     *
     * @return int
     */
    public function getHalfyearHourNormsToDate($nYear, $nMonth, $bInclude) {
        //Validation
        if ($nMonth < 1 || $nMonth > 12 || strlen($nYear) != 4) {
            return 0;
        }
        //End Validation
        //Initialization
//			$sStartDate = ( $nMonth <= 6 )
//				? $nYear . "01"
//				: $nYear . "07";

        if ($nMonth <= 4) {
            $sStartDate = $nYear . "01";
        } else if ($nMonth > 4 && $nMonth <= 8) {
            $sStartDate = $nYear . "05";
        } else if ($nMonth > 8 && $nMonth <= 12) {
            $sStartDate = $nYear . "09";
        }

        $sEndDate = $nYear . ( ( strlen($nMonth) < 2 ) ? ( "0" . $nMonth ) : $nMonth );
        //End Initialization

        $sQuery = "
				SELECT
					SUM( sch_mon_nor.norm_hours ) AS hours
				FROM
					schedule_month_norms sch_mon_nor
				WHERE
					sch_mon_nor.to_arc = 0
					AND sch_mon_nor.month >= '{$sStartDate}'
			";

        if ($bInclude) {
            $sQuery .= "
					AND sch_mon_nor.month <= '{$sEndDate}'
				";
        } else {
            $sQuery .= "
					AND sch_mon_nor.month < '{$sEndDate}'
				";
        }

        $sQuery .= "
				LIMIT 1
			";
//			APILog::Log($sQuery);
        $aData = $this->selectOnce($sQuery);

        if (!empty($aData) && isset($aData['hours'])) {
            return $aData['hours'];
        } else
            return 0;
    }

    /**
     * Връща часовете по норма за посочения месец.
     *
     * @param int $nYear
     * @param int $nMonth
     *
     * @return int
     */
    public function getHourNormsForDate($nYear, $nMonth) {
        //Validation
        if ($nMonth < 1 || $nMonth > 12 || strlen($nYear) != 4) {
            return 0;
        }
        //End Validation
        //Initialization
        $sDate = $nYear . ( ( strlen($nMonth) < 2 ) ? ( "0" . $nMonth ) : $nMonth );
        //End Initialization

        $sQuery = "
				SELECT
					sch_mon_nor.norm_hours AS hours
				FROM
					schedule_month_norms sch_mon_nor
				WHERE
					sch_mon_nor.to_arc = 0
					AND sch_mon_nor.month = '{$sDate}'
				LIMIT 1
			";
            APILog::Log(0,ArrayToString($sQuery));
        $aData = $this->selectOnce($sQuery);

        if (!empty($aData) && isset($aData['hours'])) {
            return $aData['hours'];
        } else
            return 0;
    }

    public function getPersonalMonthNorm($nIDPerson, $nYear, $nMonth) {

        if ($nMonth < 1 || $nMonth > 12 || strlen($nYear) != 4 || empty($nIDPerson)) {
            return 0;
        }
        $oDBPersonnelContractsHistory = new DBPersonnelContractsHistory();

        $normMonth = $this->getHourNormsForDate($nYear, $nMonth);
        $workHours = $oDBPersonnelContractsHistory->getContractDataForSchedule($nIDPerson);
        $oDBObjectScheduleSettings = new DBObjectScheduleSettings();
        $settings = $oDBObjectScheduleSettings->getActiveSettings();


        $person_norm = HoursToSec($normMonth) * ($workHours['work_time'] / $settings['max_day_work_hours']);

        if(date('Y-m',strtotime($workHours['start_date'])) == "{$nYear}-{$nMonth}") {
            $oDBObjectsDuty = new DBObjectsDuty();
            $workDays = $oDBObjectsDuty->getWorkDaysArray($nYear.$nMonth);
            $workDaysCnt = count($workDays, 1);
            $dayNorm = !empty($workDaysCnt) ? (int) ($normMonth / $workDaysCnt) * ($workHours['work_time'] / $settings['max_day_work_hours']) : 0;

            $cnt = 0;

            for($i = 0; $i < date('d',strtotime($workHours['start_date']));$i++) {
                if(!$oDBObjectsDuty->checkHolyDay("{$nYear}-{$nMonth}-$i")) {
                    $cnt++;
                }
            }

//            die(var_dump(HoursToSec((int)$cnt*$dayNorm)));


            $person_norm = $person_norm - HoursToSec((int)$cnt*$dayNorm);


        }

        // echo 'month_norms-'.$normMonth.'-work_time-'.$workHours['work_time'].'-max_day_work_hours-'.$settings['max_day_work_hours'].'<br>';
        if ($person_norm) {
            return getOnlyHours(SecToTime($person_norm));
        }

        return false;
    }

    public function getPersonPeriodNorms($nIDPerson, $period_array) {
        $next_tmp = array();
        $prev_tmp = array();

        if (empty($period_array['next']) || strtotime($period_array['next']) < strtotime($period_array['prev'])) {
            throw new Exception('Няма зададена дата за следващ отчетен период!');
//                return false;
        }

//        echo ArrayToString($period_array) . '<br>';
        if (!empty($period_array['next'])) {
            $next_tmp = explode('-', $period_array['next']);
        }


        if (!empty($period_array['prev'])) {
            $prev_tmp = explode('-', $period_array['prev']);
        }

        $norm_sum = 0;
        $start_year = $prev_tmp[0];
        $start_month = $prev_tmp[1];

        while ($start_year != $next_tmp[0] || $start_month != $next_tmp[1]) {
            $norm = $this->getPersonalMonthNorm($nIDPerson, $start_year, $start_month);
            $norm_sum += $norm;

            if ($start_month <= $next_tmp[1] || $start_year < $next_tmp[0]) {
                $start_month++;
            }

            if ($start_month > 12 && $start_year != $next_tmp[0]) {
                $start_month = 1;
                $start_year++;
            }
        }
//        die();
        return $norm_sum;
    }

    /**
     * @param $nYearMonth 201301
     * @return int 201301
     */
    public function getNextReportDate($nYearMonth) {
        if (empty($nYearMonth)) {
            return false;
        }

        $sQuery = "
                SELECT
                    smn.month
                FROM schedule_month_norms smn
                WHERE smn.month > '{$nYearMonth}'
                AND smn.is_start_month_report = 1
                AND smn.to_arc = 0
                ORDER BY smn.month ASC
                LIMIT 1
            ";
//        die($sQuery);
        return $this->selectOne($sQuery);
    }

    /**
     * @param $nYearMonth 201301
     * @return int 201301
     */
    public function getPrevReportDate($nYearMonth) {
        if (empty($nYearMonth)) {
            return false;
        }

        $sQuery = "
                SELECT
                    smn.month
                FROM schedule_month_norms smn
                WHERE smn.month <= '{$nYearMonth}'
                AND smn.is_start_month_report = 1
                AND smn.to_arc = 0
                ORDER BY smn.month DESC
                LIMIT 1
            ";
        return $this->selectOne($sQuery);
    }

}

?>
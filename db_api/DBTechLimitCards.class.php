<?php

class DBTechLimitCards extends DBBase2 {
    // Коефициент за наработките

    const SALARY_COEFFICIENT = 1.55; // Чефо каза да се промени

    public function __construct() {
        global $db_sod;
        //$db_sod->debug=true;

        parent::__construct($db_sod, 'tech_limit_cards');
    }

    public function getReport($aData, DBResponse $oResponse) {
        global $db_name_personnel;
        global $db_name_finance;

        $nObject = $aData['id_object'];
        $sType = $aData['type'];
        $dFrom = $aData['startTime'];
        $dTo = !empty($aData['endTime']) ? date("Y-m-d", $aData['endTime']) : "";
        $nIDFirm = $aData['id_firm'];
        $nIDOffice = $aData['id_office'];
        $sStatus = $aData['status'];
        $nNumber = $aData['id'];
        $nIDClient = Params::get('nIDClient',0);
        $nByOffer = Params::get('nByOffer',0);
        $nIDReason = Params::get('nIDReason',0);

        if (!empty($_SESSION['userdata']['access_right_levels'])) {
            if (in_array('tech_support', $_SESSION['userdata']['access_right_levels'])) {
                $right_edit = true;
            }
        }

        $sQuery = "
		SELECT SQL_CALC_FOUND_ROWS
		tl.id as _id,
		CONCAT_WS(',', tl.id, tl.id_request, tl.status , tl.id_tech_timing ) as id,
		tl.id AS num,
		UNIX_TIMESTAMP(tl.created_time) AS created_time,
		CASE
		WHEN tl.status = 'active' THEN 'активна'
		WHEN tl.status = 'closed' THEN 'приключена'
		WHEN tl.status = 'cancel' THEN 'анулирана'
		END AS status,
		#CASE
		#WHEN tl.id_tech_timing = 1 THEN 'Изграждане'
		#WHEN tl.id_tech_timing = 2 THEN 'Снемане'
		#WHEN tl.id_tech_timing = 3 THEN 'Аранжировка'
		#WHEN tl.id_tech_timing = 4 THEN 'АТО'
		#WHEN tl.id_tech_timing = 5 THEN 'ПТО'
		#END AS type,
		tt.description as type,
		obj.name AS object,
		cl.name AS client,
		GROUP_CONCAT(CONCAT_WS(' ', p.fname, p.lname) SEPARATOR ', ' ) AS persons,
		tl.distance,
		#IF(
		#	LENGTH(tr.note) > 45,
		#	CONCAT(substring(tr.note,1,45),'...'),
		#	tr.note
		#) as note,
		tr.note,
		tl.id_request AS request,
		UNIX_TIMESTAMP(tl.planned_start)AS planned_start,
		UNIX_TIMESTAMP(tl.planned_end) AS planned_end,
		UNIX_TIMESTAMP(tl.real_start) AS real_start,
		UNIX_TIMESTAMP(tl.real_end) AS real_end,
		IF(c.id_main_contract <> 0, CONCAT(c.id_main_contract,'-',c.contract_num) , c.id ) AS offer_num,
        IF(
			tr.time_limit != '0000-00-00 00:00:00',
			IF(
				tl.real_end = '0000-00-00 00:00:00',
				TIMEDIFF( NOW() , tr.time_limit),
				TIMEDIFF( tl.real_end , tr.time_limit)
			),	
			0
		) As deadline,
		tr.time_limit,
        IF(
			tr.time_limit != '0000-00-00 00:00:00',
			IF(
				tl.real_end = '0000-00-00 00:00:00',
				IF( NOW() > tr.time_limit , 1 , 0),
				IF( tl.real_end >  tr.time_limit, 1 , 0)
			),	
			0
		) As red_line
		FROM tech_limit_cards tl
		LEFT JOIN objects obj ON obj.id = tl.id_object
		LEFT JOIN offices of ON of.id = obj.id_office
		LEFT JOIN firms frm ON frm.id = of.id_firm
        LEFT JOIN clients_objects cl_obj ON ( cl_obj.id_object = obj.id  AND cl_obj.to_arc = 0)
		LEFT JOIN clients cl ON cl_obj.id_client = cl.id
		LEFT JOIN limit_card_persons lcp ON lcp.id_limit_card = tl.id
		LEFT JOIN {$db_name_personnel}.personnel p ON FIND_IN_SET(p.id, lcp.id_person)
		LEFT JOIN tech_requests tr ON tr.id = tl.id_request
		LEFT JOIN {$db_name_finance}.contracts c ON c.id = tr.id_contract
		LEFT JOIN offices off ON off.id = c.id_office
		LEFT JOIN tech_timing tt ON tt.id = tl.id_tech_timing
		WHERE 1
		AND tl.to_arc = 0
		AND tl.id_object = 0
		";

        if (!empty($dFrom)) {
            $sQuery .= " AND UNIX_TIMESTAMP(tl.created_time) >= '{$dFrom}' \n";
        }

        if (!empty($dTo)) {
            $sQuery .= " AND DATE(tl.created_time) <= '{$dTo}' \n";
        }

        if (!empty($sType)) { //&& !empty($nObject)
//            $sQuery .= " AND tl.type = '{$sType}' \n";
            $sQuery .= " AND tl.id_tech_timing = '{$sType}' \n";
        }

        if (!empty($nObject)) {
            $sQuery .= " AND tl.id_object = '{$nObject}' \n";
        }

        if (!empty($nIDOffice)) {
            $sQuery .= " AND (c.id_office = '{$nIDOffice}' ) \n";
        }

        if (!empty($nIDFirm)) {
            $sQuery .= " AND (off.id_firm = '{$nIDFirm}') \n";
        }

        if (!empty($sStatus)) {
            $sQuery .= " AND tl.status = '{$sStatus}' \n";
        }
        if (!empty($nNumber)) {
            $sQuery .= " AND tl.id = {$nNumber} ";
        }

        if(!empty($nIDClient)) {
            $sQuery.= " AND cl.id = {$nIDClient} ";
        }

        if(!empty($nIDReason)) {
            $sQuery.= " AND tr.id_tech_reason = {$nIDReason} ";
        }


        $sQuery .= " GROUP BY tl.id ";
        $sQuery .= " UNION
			
		SELECT
		tl.id as _id,
		CONCAT_WS(',', tl.id, tl.id_request , tl.id_request, tl.status , tl.id_tech_timing , c.id) as id,
		tl.id AS num,
		UNIX_TIMESTAMP(tl.created_time) AS created_time,
		CASE
		WHEN tl.status = 'active' THEN 'активна'
		WHEN tl.status = 'closed' THEN 'приключена'
		WHEN tl.status = 'cancel' THEN 'анулирана'
		END AS status,
		#CASE
		#WHEN tl.id_tech_timing = 1 THEN 'Изграждане'
		#WHEN tl.id_tech_timing = 2 THEN 'Снемане'
		#WHEN tl.id_tech_timing = 3 THEN 'Аранжировка'
		#WHEN tl.id_tech_timing = 4 THEN 'АТО'
		#WHEN tl.id_tech_timing = 5 THEN 'ПТО'
		#END AS type,
		tt.description as type,
		obj.name AS object,
		cl.name AS client,
		GROUP_CONCAT(CONCAT_WS(' ', p.fname, p.lname) SEPARATOR ', ' ) AS persons,
		tl.distance,
		#IF(
		#	LENGTH(tr.note) > 45,
		#	CONCAT(substring(tr.note,1,45),'...'),
		#	tr.note
		#) as note,
		tr.note,
		tl.id_request AS request,
		UNIX_TIMESTAMP(tl.planned_start)AS planned_start,
		UNIX_TIMESTAMP(tl.planned_end) AS planned_end,
		UNIX_TIMESTAMP(tl.real_start) AS real_start,
		UNIX_TIMESTAMP(tl.real_end) AS real_end,
		IF(c.id_main_contract <> 0, CONCAT(c.id_main_contract,'-',c.contract_num) , c.id ) AS offer_num,
        IF(
			tr.time_limit != '0000-00-00 00:00:00',
			IF(
				tl.real_end = '0000-00-00 00:00:00',
				TIMEDIFF( NOW() , tr.time_limit),
				TIMEDIFF( tl.real_end , tr.time_limit)
			),	
			0
		) As deadline,
		tr.time_limit,
        IF(
			tr.time_limit != '0000-00-00 00:00:00',
			IF(
				tl.real_end = '0000-00-00 00:00:00',
				IF( NOW() > tr.time_limit , 1 , 0),
				IF( tl.real_end >  tr.time_limit, 1 , 0)
			),	
			0
		) As red_line
		FROM tech_limit_cards tl
		LEFT JOIN tech_requests tr ON tr.id_limit_card = tl.id
		LEFT JOIN {$db_name_finance}.contracts c ON c.id = tr.id_contract
		LEFT JOIN objects obj ON obj.id = tl.id_object
		LEFT JOIN offices of ON of.id = obj.id_office
		LEFT JOIN firms frm ON frm.id = of.id_firm
        LEFT JOIN clients_objects cl_obj ON ( cl_obj.id_object = obj.id  AND cl_obj.to_arc = 0)
		LEFT JOIN clients cl ON cl_obj.id_client = cl.id
		LEFT JOIN limit_card_persons lcp ON lcp.id_limit_card = tl.id
		LEFT JOIN {$db_name_personnel}.personnel p ON FIND_IN_SET(p.id, lcp.id_person)
		LEFT JOIN tech_timing tt ON tt.id = tl.id_tech_timing
		WHERE 1
		AND tl.to_arc = 0
		AND tl.id_object != 0
		";
        if (!empty($dFrom)) {
            $sQuery .= " AND UNIX_TIMESTAMP(tl.created_time) >= '{$dFrom}' \n";
        }

        if (!empty($dTo)) {
            $sQuery .= " AND DATE(tl.created_time) <= '{$dTo}' \n";
        }

        if (!empty($sType)) { //&& !empty($nObject)
//            $sQuery .= " AND tl.type = '{$sType}' \n";
            $sQuery .= " AND tl.id_tech_timing = '{$sType}' \n";
        }

        if (!empty($nObject)) {
            $sQuery .= " AND tl.id_object = '{$nObject}' \n";
        }

        if (!empty($nIDOffice)) {
            $sQuery .= " AND obj.id_office = '{$nIDOffice}' \n";
        }

        if (!empty($nIDFirm)) {
            $sQuery .= " AND of.id_firm = '{$nIDFirm}' \n";
        }

        if (!empty($sStatus)) {
            $sQuery .= " AND tl.status = '{$sStatus}' \n";
        }
        if (!empty($nNumber)) {
            $sQuery .= " AND tl.id = {$nNumber} ";
        }

        if(!empty($nIDClient)) {
            $sQuery.= " AND cl.id = {$nIDClient} ";
        }

        if(!empty($nByOffer)) {
            $sQuery.= " AND c.id <> 0 ";
        }

        if(!empty($nIDReason)) {
            $sQuery.= " AND tr.id_tech_reason = {$nIDReason} ";
        }

        $sQuery .= " GROUP BY tl.id ";

        $this->getResult($sQuery, 'num', DBAPI_SORT_DESC, $oResponse);

        //	//		if ( $right_level == 'edit' ) {
        //				$oResponse->setField('chk', '', NULL, NULL, NULL, NULL, array('type' => 'checkbox'));
        //				$oResponse->setFieldData('chk', 'input', array('type' => 'checkbox', 'exception' => 'false'));
        //				$oResponse->setFieldAttributes('chk', array('style' => 'width: 25px;'));
        //
        //				$oResponse -> setFormElement('form1', 'sel', array(), '');
        //				$oResponse -> setFormElementChild('form1', 'sel', array('value' => '1'), "--- анулиране ---");
        //				$oResponse -> setFormElementChild('form1', 'sel', array('value' => '2'), "--- лимитни карти ---");
        //	//		}
        //

        APILog::Log($sQuery);
        foreach ($oResponse->oResult->aData as $key => &$val) {
            $val['num'] = zero_padding($val['num']);
            $val['request'] = zero_padding($val['request']);

            $val['created_time'] = !empty($val['created_time']) ? date('d.m.Y H:i:s', $val['created_time']) : '';

            $val['planned_start'] = !empty($val['planned_start']) ? date('d.m.Y H:i:s', $val['planned_start']) : '';
            $val['planned_end'] = !empty($val['planned_end']) ? date('d.m.Y H:i:s', $val['planned_end']) : '';
            $val['real_start'] = !empty($val['real_start']) ? date('d.m.Y H:i:s', $val['real_start']) : '';
            $val['real_end'] = !empty($val['real_end']) ? date('d.m.Y H:i:s', $val['real_end']) : '';

            if($val['red_line']) {
                $oResponse->setRowAttributes($val['id'] ,array('style'=>'background-color:#F2D8C9'));
            }

            $oResponse->setDataAttributes($key, 'created_time', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));

            $oResponse->setDataAttributes($key, 'planned_start', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'planned_end', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'real_start', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'real_end', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));

            $oResponse->setDataAttributes($key, 'status', array('nowrap' => 'nowrap', 'style' => 'text-align: right; width: 80px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'type', array('nowrap' => 'nowrap', 'style' => 'text-align: right; width: 90px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'request', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 80px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'num', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 80px; white-space: nowrap !important;'));

            $oResponse->setDataAttributes($key, 'distance', array('nowrap' => 'nowrap', 'style' => 'text-align: right; width: 100px; white-space: nowrap !important;'));

            if ($val['status'] == 'анулирана') {
                $oResponse->setDataAttributes($key, 'num', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 80px; white-space: nowrap !important; font-style: italic; color: #969696;'));
                $oResponse->setDataAttributes($key, 'status', array('nowrap' => 'nowrap', 'style' => 'text-align: right; width: 80px; white-space: nowrap !important; font-style: italic; color: #969696;'));
                $oResponse->setDataAttributes($key, 'created_time', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important; font-style: italic; color: #969696;'));
                $oResponse->setDataAttributes($key, 'type', array('nowrap' => 'nowrap', 'style' => 'text-align: right; width: 90px; white-space: nowrap !important; font-style: italic; color: #969696;'));
                $oResponse->setDataAttributes($key, 'object', array('style' => 'font-style: italic; color: #969696;'));
                $oResponse->setDataAttributes($key, 'client', array('style' => 'font-style: italic; color: #969696;'));
                $oResponse->setDataAttributes($key, 'persons', array('style' => 'font-style: italic; color: #969696;'));
                $oResponse->setDataAttributes($key, 'request', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 80px; white-space: nowrap !important; font-style: italic; color: #969696;'));
                $oResponse->setDataAttributes($key, 'planned_start', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important; font-style: italic; color: #969696;'));
                $oResponse->setDataAttributes($key, 'planned_end', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important; font-style: italic; color: #969696;'));
                $oResponse->setDataAttributes($key, 'real_start', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important; font-style: italic; color: #969696;'));
                $oResponse->setDataAttributes($key, 'real_end', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important; font-style: italic; color: #969696;'));
//                $oResponse->setDataAttributes($key, 'deadline', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important; font-style: italic; color: #969696;'));
                $oResponse->setDataAttributes($key, 'distance', array('nowrap' => 'nowrap', 'style' => 'text-align: right; width: 100px; white-space: nowrap !important; font-style: italic; color: #969696;'));
            }
        }
        //				$val['num'] = zero_padding($val['num']);
        //				$oResponse->setDataAttributes( $key, 'limit_card', array('style' => 'text-align: center; width: 75px;'));
        //
        //				if ( $val['limit_card'] > 0 ) {
        //					$val['limit_card'] = zero_padding($val['limit_card']);
        //					$oResponse->setDataAttributes( $key, 'chk', array('style' => 'visibility: hidden;'));
        //				} else {
        //					$val['limit_card'] = '';
        //				}
        //
        //				$val['created_time'] = date('d.m.Y H:i:s', $val['created_time']);
        //				$oResponse->setDataAttributes( $key, 'num', array('style' => 'text-align: center; width: 75px;'));
        //				$oResponse->setDataAttributes( $key, 'created_time', array('nowrap' => 'nowrap','style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));
        //			}

        $oResponse->setField('num', 'номер', 'сортирай по номер');
        $oResponse->setField('created_time', 'дата', 'сортирай по дата');
        $oResponse->setField('status', 'статус', 'сортирай по статус');
        $oResponse->setField('type', 'тип', 'сортирай по тип');
        $oResponse->setField('object', 'обект', 'сортирай по обект');
//        $oResponse->setField('client', 'клиент', 'сортирай по клиент');
        $oResponse->setField('persons', 'изпълнил', 'сортирай по изпълнил');
        $oResponse->setField('note', 'забележка', 'сортирай по забележка');
        $oResponse->setField('request', 'заявка', 'сортирай по заявка');
        $oResponse->setField('offer_num', 'оферта', 'сортирай по оферта');
//        $oResponse->setField('planned_start', 'планиран старт', 'сортирай по време');
//        $oResponse->setField('planned_end', 'планиран край', 'сортирай по време');
        $oResponse->setField('real_start', 'реален старт', 'сортирай по време');
        $oResponse->setField('real_end', 'реален край', 'сортирай по време');
        $oResponse->setField('deadline', 'Просрочие', 'Срок на изпълнение');
        $oResponse->setField('time_limit', 'Краен срок на изпълнение',  'Краен срок на изпълнение', NULL,NULL,NULL,array('DATA_FORMAT'=>DF_DATETIME));
//        $oResponse->setField('distance', 'отдалеченост', 'сортирай по км');
//        $oResponse->setField('operations', 'операции', 'сортирай по операции', 'images/pdf.gif','printPDF','');
        $oResponse->setField('',            'протокол', '',                     'images/pdf.gif','printProtocolPDF','');

        $oResponse->setFIeldLink('num', 'editLimitCard');
        $oResponse->setFIeldLink('request', 'editRequest');
        $oResponse->setFIeldLink('offer_num', 'OpenOffer');
    }


    /**
     * Обобщена справка за обслужванията в Техници->Лимитни карти
     * @param $aData
     * @param DBResponse $oResponse
     *
     * @throws Exception
     */
    public function getReportSummary( $aData, DBResponse $oResponse ) {

        $nObject = $aData['id_object'];
        $dFrom = $aData['startTime'];
        $dTo = !empty($aData['endTime']) ? date("Y-m-d", $aData['endTime']) : "";
        $nIDFirm = $aData['id_firm'];
        $nIDOffice = $aData['id_office'];
        $sStatus = $aData['status'];
        $nNumber = $aData['id'];
        $nIDClient = Params::get('nIDClient', 0);

        $oDBTechTimings = new DBTechTiming();

        $aTechTimings = $oDBTechTimings->selectAssoc(
            "SELECT
				name AS __key,
				id,
				name,
				description AS d

			FROM tech_timing
			WHERE to_arc = 0
			"
        );

        $oResponse->setField('object', 'обект', 'сортирай по обект', NULL, 'showObject' );
        $oResponse->setField('client', 'клиент', 'сортирай по клиент');

        $sTimings = '';
        foreach ( $aTechTimings as $aTechTiming ) {
            $name = explode( ' ', $aTechTiming['d'] );
            $sTimings .= "SUM(IF(tl.id_tech_timing = '{$aTechTiming['id']}', 1, 0)) AS '{$aTechTiming['name']}', \n";

            $oResponse->setField( $aTechTiming['name'], $name[0], $aTechTiming['d'] );
        }


        $sQuery = "

				SELECT SQL_CALC_FOUND_ROWS
					tl.id_object AS _id,
					tl.id_object AS id,
					obj.name AS object,
					{$sTimings}
					cl.name AS client

				FROM tech_limit_cards tl
				LEFT JOIN objects obj ON obj.id = tl.id_object
				LEFT JOIN offices of ON of.id = obj.id_office
				LEFT JOIN firms frm ON frm.id = of.id_firm
		        LEFT JOIN clients_objects cl_obj ON ( cl_obj.id_object = obj.id  AND cl_obj.to_arc = 0)
				LEFT JOIN clients cl ON cl_obj.id_client = cl.id

				WHERE 1
					AND tl.to_arc = 0
					AND tl.id_object != 0
		";
        if (!empty($dFrom)) {
            $sQuery .= " AND UNIX_TIMESTAMP(tl.created_time) >= '{$dFrom}' \n";
        }

        if (!empty($dTo)) {
            $sQuery .= " AND DATE(tl.created_time) <= '{$dTo}' \n";
        }

        if (!empty($nObject)) {
            $sQuery .= " AND tl.id_object = '{$nObject}' \n";
        }

        if (!empty($nIDOffice)) {
            $sQuery .= " AND obj.id_office = '{$nIDOffice}' \n";
        }

        if (!empty($nIDFirm)) {
            $sQuery .= " AND of.id_firm = '{$nIDFirm}' \n";
        }

        if (!empty($sStatus)) {
            $sQuery .= " AND tl.status = '{$sStatus}' \n";
        }
        if (!empty($nNumber)) {
            $sQuery .= " AND tl.id = {$nNumber} ";
        }

        if(!empty($nIDClient)) {
            $sQuery.= " AND cl.id = {$nIDClient} ";
        }

        $sQuery .= " GROUP BY tl.id_object ";

        $this->getResult($sQuery, 'num', DBAPI_SORT_DESC, $oResponse);

        foreach ($oResponse->oResult->aData as $key => &$val) {

            foreach ( $aTechTimings as $id_timing => $aTimings ) {
                if ( $val[$id_timing] == 0 ) {
                    $val[$id_timing] = '---';
                }
            }

        }

    }

    public function delRequests($nIDs) {
        global $db_sod;

        $sQuery = "UPDATE tech_requests SET to_arc = 1 WHERE id IN ({$nIDs})";
        $db_sod->Execute($sQuery);
    }

    public function makeLimitCard($nIDs) {
        global $db_sod;

        //$sQuery = "UPDATE tech_requests SET to_arc = 1 WHERE id IN ({$nIDs})";
        //$db_sod->Execute($sQuery);
    }

    public function getRequest($nID) {
        global $db_name_personnel;

        $nID = (int) $nID;

        $sQuery = "
		SELECT
		tr.id,
		tr.id AS num,
		DATE_FORMAT(tr.created_time, '%d.%m.%Y') AS created_time,
		of.id_firm,
		obj.id_office,
		tr.id_object,
		obj.name AS object,
		tr.id_limit_card AS limit_card,
		tr.type,
		IF ( tr.created_type = 'manual', CONCAT_WS(' ', cu.fname, cu.mname, cu.lname), 'Автоматична заявка' ) AS created_user,
		tr.request_person_name,
		tr.note
		FROM tech_requests AS tr
		LEFT JOIN objects obj ON obj.id = tr.id_object
		LEFT JOIN offices of ON of.id = obj.id_office
		LEFT JOIN {$db_name_personnel}.personnel cu ON cu.id = tr.created_user
		WHERE 1
		AND tr.id = {$nID}
		";
        // LEFT JOIN firms frm ON frm.id = of.id_firm
        // LEFT JOIN clients cl ON obj.id_client = cl.id

        return $this->selectOnce($sQuery);
    }

    public function getLimitCard($nID) {
        global $db_name_personnel;

        $nID = (int) $nID;

        $sQuery = "
		SELECT
		tl.id,
		tl.id AS num,
		DATE_FORMAT(tl.created_time, '%d.%m.%Y') AS created_time,
		tl.id_object,
		CONCAT('[', obj.num, '] ', obj.name) AS object,
		ct.name as city,
		obj.address as address,
		obj.id_office as office,
		obj.id_tech_office as tech_office,
		CASE
		WHEN tl.status = 'active' THEN 'Активна'
		WHEN tl.status = 'closed' THEN 'Приключена'
		WHEN tl.status = 'cancel' THEN 'Анулирана'
		END AS status,
		tl.id_tech_timing as type,
		IF ( tl.distance > 0, tl.distance, '') AS distance,
		IF ( tl.arrange_count > 0, tl.arrange_count, '') AS arrange_count,
		cl.name AS client,
		tl.note,
		IF ( UNIX_TIMESTAMP(tl.planned_start) > 0, DATE_FORMAT(tl.planned_start, '%d.%m.%Y'), '') AS pstartdate,
		IF ( UNIX_TIMESTAMP(tl.planned_start) > 0, DATE_FORMAT(tl.planned_start, '%H:%i'), '')  AS pstarttime,
		IF ( UNIX_TIMESTAMP(tl.planned_end) > 0, DATE_FORMAT(tl.planned_end, '%d.%m.%Y'), '')  AS penddate,
		IF ( UNIX_TIMESTAMP(tl.planned_end) > 0, DATE_FORMAT(tl.planned_end, '%H:%i'), '')  AS pendtime,
		IF ( UNIX_TIMESTAMP(real_start) > 0, DATE_FORMAT(tl.real_start, '%d.%m.%Y'), '')  AS rstartdate,
		IF ( UNIX_TIMESTAMP(real_start) > 0, DATE_FORMAT(tl.real_start, '%H:%i'), '')  AS rstarttime,
		IF ( UNIX_TIMESTAMP(tl.real_end) > 0, DATE_FORMAT(tl.real_end, '%d.%m.%Y'), '')  AS renddate,
		IF ( UNIX_TIMESTAMP(tl.real_end) > 0, DATE_FORMAT(tl.real_end, '%H:%i'), '')  AS rendtime,
		tl.planned_start,
		tl.planned_end,
		tr.id as reqNum,
		tr.created_type,
		tr.id_contract,
		tr.id_tech_reason AS req_type,
		tr.tech_type,
		tr.id_tech_timing,
		hr.name as holdup_reason,
		tr.note as reqInfo,
		DATE_FORMAT(tr.created_time, '%d.%m.%Y') AS req_created_time,
		CONCAT_WS(' ' ,cu.fname,cu.lname) as created_user,
		tl.planned_start as planned_start_raw
		FROM tech_limit_cards AS tl
		LEFT JOIN tech_requests tr ON (tr.id = tl.id_request AND tr.to_arc = 0 AND tr.id IS NOT NULL)
		LEFT JOIN objects obj ON obj.id = tl.id_object
		LEFT JOIN clients cl ON cl.id = obj.id_client
		LEFT JOIN cities ct ON obj.address_city = ct.id
		LEFT JOIN {$db_name_personnel}.personnel cu ON cu.id = tl.created_user
		LEFT JOIN tech_reason hr ON hr.id = tr.id_tech_reason
		WHERE 1
		AND tl.id = {$nID}
		";

        $data = $this->selectOnce($sQuery);

        $city = isset($data['city']) ? $data['city'] : "";
        //			$street = isset($data['street']) ? $data['street'] : "";
        //			$num = isset($data['addr_num']) ? $data['addr_num'] : "";
        //			$other = isset($data['other']) ? $data['other'] : "";
        //			$data['address'] = "[".$city."], ".$street."  ".$num."  /".$other."/";

        return $data;
    }

    public function getMol($nID) {
        global $db_name_personnel;

        $nIDObj = (int) $nID > 0 ? (int) $nID : -1;

        $sQuery = "
		SELECT
		f.id,
		f.name,
		f.phone
		FROM objects o
		LEFT JOIN faces f ON f.id = o.id_face
		WHERE f.to_arc = 0
		AND f.id_obj = {$nIDObj}
		";

        return $this->selectOnce($sQuery);
    }

    public function getReportOnce($nID, DBResponse $oResponse) {
        global $db_name_personnel;

        //			$id_work_card = isset( $aData['id_work_card'] ) ? $aData['id_work_card'] : 0;
        //			$id_office = isset( $aData['id_office'] ) ? $aData['id_office'] : 0;
        //debug($aData);

        if (!empty($_SESSION['userdata']['access_right_levels'])) {
            if (in_array('tech_support', $_SESSION['userdata']['access_right_levels'])) {
                $right_edit = true;
            }
        }

        $sQuery = "
		SELECT SQL_CALC_FOUND_ROWS
		tl.id,
		tl.persons,
			
		FROM {$db_name_personnel}.personnel p
		LEFT JOIN tech_limit_cards tl ON tl
		LEFT JOIN objects obj ON obj.id = tl.id_object
		LEFT JOIN clients cl ON obj.id_client = cl.id
		LEFT JOIN {$db_name_personnel}.personnel p ON FIND_IN_SET(p.id, tl.persons)
		WHERE 1
		AND tl.to_arc = 0
		GROUP BY tl.id
		";

        $this->getResult($sQuery, 'id', DBAPI_SORT_ASC, $oResponse);

        //	//		if ( $right_level == 'edit' ) {
        //				$oResponse->setField('chk', '', NULL, NULL, NULL, NULL, array('type' => 'checkbox'));
        //				$oResponse->setFieldData('chk', 'input', array('type' => 'checkbox', 'exception' => 'false'));
        //				$oResponse->setFieldAttributes('chk', array('style' => 'width: 25px;'));
        //
        //				$oResponse -> setFormElement('form1', 'sel', array(), '');
        //				$oResponse -> setFormElementChild('form1', 'sel', array('value' => '1'), "--- анулиране ---");
        //				$oResponse -> setFormElementChild('form1', 'sel', array('value' => '2'), "--- лимитни карти ---");
        //	//		}
        //
        foreach ($oResponse->oResult->aData as $key => &$val) {
            $val['num'] = zero_padding($val['num']);
            $val['request'] = zero_padding($val['request']);

            $val['created_time'] = !empty($val['created_time']) ? date('d.m.Y H:i:s', $val['created_time']) : '';

            $val['planned_start'] = !empty($val['planned_start']) ? date('d.m.Y H:i:s', $val['planned_start']) : '';
            $val['planned_end'] = !empty($val['planned_end']) ? date('d.m.Y H:i:s', $val['planned_end']) : '';
            $val['real_start'] = !empty($val['real_start']) ? date('d.m.Y H:i:s', $val['real_start']) : '';
            $val['real_end'] = !empty($val['real_end']) ? date('d.m.Y H:i:s', $val['real_end']) : '';

            $oResponse->setDataAttributes($key, 'created_time', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));

            $oResponse->setDataAttributes($key, 'planned_start', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'planned_end', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'real_start', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'real_end', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));

            $oResponse->setDataAttributes($key, 'status', array('nowrap' => 'nowrap', 'style' => 'text-align: right; width: 80px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'request', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 80px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'num', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 80px; white-space: nowrap !important;'));

            $oResponse->setDataAttributes($key, 'distance', array('nowrap' => 'nowrap', 'style' => 'text-align: right; width: 100px; white-space: nowrap !important;'));
        }
        //				$val['num'] = zero_padding($val['num']);
        //				$oResponse->setDataAttributes( $key, 'limit_card', array('style' => 'text-align: center; width: 75px;'));
        //
        //				if ( $val['limit_card'] > 0 ) {
        //					$val['limit_card'] = zero_padding($val['limit_card']);
        //					$oResponse->setDataAttributes( $key, 'chk', array('style' => 'visibility: hidden;'));
        //				} else {
        //					$val['limit_card'] = '';
        //				}
        //
        //				$val['created_time'] = date('d.m.Y H:i:s', $val['created_time']);
        //				$oResponse->setDataAttributes( $key, 'num', array('style' => 'text-align: center; width: 75px;'));
        //				$oResponse->setDataAttributes( $key, 'created_time', array('nowrap' => 'nowrap','style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));
        //			}

        $oResponse->setField('num', 'номер', 'сортирай по номер');
        $oResponse->setField('created_time', 'дата', 'сортирай по дата');
        $oResponse->setField('status', 'статус', 'сортирай по статус');
        $oResponse->setField('object', 'обект', 'сортирай по обект');
        $oResponse->setField('client', 'клиент', 'сортирай по клиент');
        $oResponse->setField('persons', 'техници', 'сортирай по техници');
        $oResponse->setField('request', 'заявка', 'сортирай по заявка');
        $oResponse->setField('planned_start', 'планиран старт', 'сортирай по време');
        $oResponse->setField('planned_end', 'планиран край', 'сортирай по време');
        $oResponse->setField('real_start', 'реален старт', 'сортирай по време');
        $oResponse->setField('real_end', 'реален край', 'сортирай по време');
        $oResponse->setField('distance', 'отдалеченост', 'сортирай по км');

        $oResponse->setFIeldLink('num', 'editLimitCard');
        $oResponse->setFIeldLink('request', 'editRequest');
    }

    public function detachPersonFromLimitCard($nIDPerson, $nIDCard, $nDate) {
        if (empty($nIDPerson) || !is_numeric($nIDPerson))
            throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);

        if (empty($nIDCard) && !is_numeric($nIDCard))
            throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);

        if (empty($nDate) || !is_numeric($nDate))
            throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);

        $sQuery = "
		DELETE lp
		FROM limit_card_persons lp
		LEFT JOIN tech_limit_cards lc ON lp.id_limit_card = lc.id
		WHERE 1
		AND lc.status = 'active'
		AND DATE( lc.planned_start ) = DATE( FROM_UNIXTIME( {$nDate } ) )
		AND lp.id_person = {$nIDPerson}
		";

        if (!empty($nIDCard))
            $sQuery .= "AND lp.id_limit_card = {$nIDCard}\n";

        $this->oDB->Execute($sQuery);
    }

    public function getStatus($nID) {
        $nID = (int) $nID;

        $sQuery = "
		SELECT
		status
		FROM tech_limit_cards
		WHERE id = '{$nID}'
		";

        return $this->selectOne($sQuery);
    }

    public function getWorkStatus($nID) {
        $nID = (int) $nID;

        $sQuery = "
		SELECT
		UNIX_TIMESTAMP(lc.real_start) as nTime,
		IF ((tr.tech_type = 'contract' and tr.id_tech_timing = 1), 'contract', tr.id_tech_timing) as type,
		CONCAT(p.fname, ' ', p.mname, ' ', p.lname, ' ', '[', DATE_FORMAT(lc.updated_time, '%d.%m.%Y %H:%i:%s'), ']') as person
		FROM tech_limit_cards lc
		LEFT JOIN personnel.personnel p ON p.id = lc.updated_user
		LEFT JOIN tech_requests tr ON tr.id = lc.id_request
		WHERE lc.id = '{$nID}'
		";
        APILog::Log(0, $sQuery);

        $data = $this->select($sQuery);
        APILog::Log(0, $data);
        if (isset($data[0]['nTime']) && $data[0]['nTime'] > 9999) {
            return $data;
        }
        else
            return $data;
    }

    public function getReportObject($aData, DBResponse $oResponse) {
        global $db_name_personnel;

        $nObject = $aData['obj'];
        $status = $aData['service'];
        $tech_timing = $aData['tech_timing'];

        if (empty($nObject) || !is_numeric($nObject)) {
            throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
        }

        if (!empty($_SESSION['userdata']['access_right_levels'])) {
            if (in_array('tech_support', $_SESSION['userdata']['access_right_levels'])) {
                $right_edit = true;
            }
        }

        $sQuery = "
            SELECT SQL_CALC_FOUND_ROWS

              t.*
            FROM
            ( SELECT
            CONCAT_WS('@',tl.id,tl.id_request, IF( ap.id IS NOT NULL , ap.id , 0),tl.id_tech_timing , tr.id_contract , tl.status ) AS id,
            tl.id AS num,
            ap.id AS num_protocol,
            tl.id_tech_timing,
            tr.id_contract,
            UNIX_TIMESTAMP(tl.created_time) AS created_time,
            SEC_TO_TIME(TIMESTAMPDIFF(SECOND,tl.real_start,tl.real_end )) AS duration,
            CASE
            WHEN tl.status = 'active' THEN 'активна'
            WHEN tl.status = 'closed' THEN 'приключена'
            END AS status,

            CASE
              WHEN tl.status = 'closed' AND tl.type = '' THEN 'ok'
              WHEN tl.status = 'closed' AND tl.type = 'hand' THEN 'hand'
              WHEN tl.status = 'closed' AND tl.type = 'fictive' THEN 'fictive'
              WHEN tl.status = 'active' THEN 'active'
              WHEN tl.status = 'cancel' THEN 'cancel'
            END AS status2,
            tt.description AS type,
            GROUP_CONCAT(CONCAT_WS(' ', p.fname, p.lname) SEPARATOR ', ' ) AS persons,
            UNIX_TIMESTAMP(tl.planned_start)AS planned_start,
            UNIX_TIMESTAMP(tl.planned_end) AS planned_end,
            UNIX_TIMESTAMP(tl.real_start) AS real_start,
            UNIX_TIMESTAMP(tl.real_end) AS real_end
            FROM tech_limit_cards tl
            LEFT JOIN tech_requests tr ON tr.id = tl.id_request
            LEFT JOIN ascertainments_protocols ap ON tr.id = ap.id_request
            LEFT JOIN objects obj ON obj.id = tl.id_object
            LEFT JOIN offices of ON of.id = obj.id_office
            LEFT JOIN firms frm ON frm.id = of.id_firm
            LEFT JOIN clients cl ON obj.id_client = cl.id
            LEFT JOIN limit_card_persons lcp ON lcp.id_limit_card = tl.id
            LEFT JOIN tech_timing tt ON tt.id = tl.id_tech_timing
            LEFT JOIN {$db_name_personnel}.personnel p ON FIND_IN_SET(p.id, lcp.id_person)
            WHERE 1
            AND tl.to_arc = 0
		";

        if (!empty($nObject)) {
            $sQuery .= " AND tl.id_object = '{$nObject}' \n";
        }

        if (!empty($status)) {
            $sQuery .= " AND tl.status = 'active' \n";
        }

        if(!empty($tech_timing)) {
            $sQuery.=" AND tt.id = {$tech_timing} ";
        }

        $sQuery .= " GROUP BY tl.id ) as t ";

//        APILog::Log('22223',$sQuery);

        $this->getResult($sQuery, 't.created_time', DBAPI_SORT_DESC, $oResponse);

        $sType = 'id';


        foreach ($oResponse->oResult->aData as $key => &$val) {
            $val['num'] = zero_padding($val['num']);
            $val['request'] = zero_padding($val['request']);

            //има констативни протоколи само за ремонт!
            if($val['id_tech_timing'] == 3) {
                if($val['num_protocol'] == 0) {
                    $val['num_protocol'] = 'Няма протокол';
                } else {
                    $val['num_protocol'] = 'КП '.zero_padding($val['num_protocol']);
                }
            } else {
                $val['num_protocol'] = '';
            }

            $val['id_contract'] = (!empty($val['id_contract']))? $val['id_contract'] : '';

            $val['created_time'] = !empty($val['created_time']) ? date('d.m.Y H:i', $val['created_time']) : '';

            $val['planned_start'] = !empty($val['planned_start']) ? date('d.m.Y H:i', $val['planned_start']) : '';
            $val['planned_end'] = !empty($val['planned_end']) ? date('d.m.Y H:i', $val['planned_end']) : '';
            $val['real_start'] = !empty($val['real_start']) ? date('d.m.Y H:i', $val['real_start']) : '';
            $val['real_end'] = !empty($val['real_end']) ? date('d.m.Y H:i', $val['real_end']) : '';

            $oResponse->setDataAttributes($key, 'created_time', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));

//            $oResponse->setDataAttributes($key, 'planned_start', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));
//            $oResponse->setDataAttributes($key, 'planned_end', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'real_start', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'real_end', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 125px; white-space: nowrap !important;'));


            $aAtributes['id'] = $val['status2'];
            $oResponse->setDataAttributes($key , 'status2', $aAtributes);
//            $val['status2'] = '';

            $oResponse->setDataAttributes($key, 'status', array('nowrap' => 'nowrap', 'style' => 'text-align: right; width: 80px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'type', array('nowrap' => 'nowrap', 'style' => 'text-align: right; width: 90px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'num', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 80px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'duration', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 80px; white-space: nowrap !important;'));
            $oResponse->setDataAttributes($key, 'id_contract', array('nowrap' => 'nowrap', 'style' => 'text-align: center; width: 80px; white-space: nowrap !important;'));
        }

//        $oResponse->setField('num', 'номер', 'сортирай по номер');
        $oResponse->setField('created_time', 'дата', 'сортирай по дата');
        $oResponse->setField('status2', '', '');
        $oResponse->setField('type', 'тип', 'сортирай по тип');
        $oResponse->setField('persons', 'техници', 'сортирай по техници');
//        $oResponse->setField('planned_start', 'планиран старт', 'сортирай по време');
//        $oResponse->setField('planned_end', 'планиран край', 'сортирай по време');
        $oResponse->setField('real_start', 'реален старт', 'сортирай по време');
        $oResponse->setField('real_end', 'реален край', 'сортирай по време');
        $oResponse->setField('duration', 'времетраене', 'сортирай по времетраене');
        $oResponse->setField('id_contract', '№ оферта', 'сортирай по № оферта');
        $oResponse->setField('', '', '', 'images/pdf.gif','openProtocol','');

        $oResponse->setFIeldLink('created_time', 'editLimitCard');
        $oResponse->setFIeldLink('id_contract', 'openOffer');
    }

    public function getIDObject($nID) {

        $sQuery = "
		SELECT
		id_object
		FROM tech_limit_cards
		WHERE id = {$nID}
		";

        return $this->selectOne($sQuery);
    }

    public function getInfoForPersonCard($nID) {
        global $db_name_auto_trans;

        $sQuery = "
		SELECT
		tlc.id,
		tlc.id_request,
		/*CASE
		WHEN tlc.id_tech_timing = 1 THEN 'Изграж / Аранж'
		WHEN tlc.id_tech_timing = 2 THEN 'Снемане'
		WHEN tlc.id_tech_timing = 3 THEN 'Аранжиране'
		WHEN tlc.id_tech_timing = 4 THEN 'Профилактика'
		WHEN tlc.id_tech_timing = 5 THEN 'Планово обсл.'
		END AS type,*/
		tlc.type AS type_tlc,
		tt.description AS type,
		o.id AS id_object,
		o.num AS num_object,
		CONCAT('[',o.num,'] ',o.name) AS obj_name,
		o.name AS obj_name2,
		CONCAT(IF(c.name != '',CONCAT(c.name,'     '),''),IF(ca.name != '',CONCAT(ca.name,'     '),''),o.address) AS obj_address,
		o.phone,
		f.name AS face_name,
		f.phone AS face_phone,
		tlc.distance,
		DATE_FORMAT(tlc.planned_start, '%d.%m.%Y %H:%i ') AS planned_start,
		DATE_FORMAT(tlc.planned_end, '%d.%m.%Y %H:%i') AS planned_end,
		DATE_FORMAT(tlc.real_start, '%d.%m.%Y %H:%i') AS real_start,
		DATE_FORMAT(tlc.real_end, '%d.%m.%Y %H:%i') AS real_end,
		IF(
		  tlc.real_start = '0000-00-00 00:00:00',
		  0,
		  DATE_FORMAT(tlc.real_start, '%d.%m.%Y')
		) AS real_start_date,
		IF(
		  tlc.real_start = '0000-00-00 00:00:00',
		  0,
		  DATE_FORMAT(tlc.real_start, '%H:%i')
		) AS real_start_hour,
        IF(
		  tlc.real_end = '0000-00-00 00:00:00',
		  0,
		  DATE_FORMAT(tlc.real_end, '%d.%m.%Y')
		) AS real_end_date,
        IF(
		  tlc.real_end = '0000-00-00 00:00:00',
		  0,
		  DATE_FORMAT(tlc.real_end, '%H:%i')
		) AS real_end_hour,
		a.tech_status,
		trr.current_status

		FROM tech_limit_cards tlc
		LEFT JOIN objects o ON o.id = tlc.id_object
		LEFT JOIN cities c ON c.id = o.address_city
		LEFT JOIN city_areas ca ON ca.id = o.address_area
		LEFT JOIN tech_timing tt ON tt.id = tlc.id_tech_timing
		#LEFT JOIN city_streets cs ON cs.id = o.address_street
		LEFT JOIN faces f ON f.id = o.id_face
		LEFT JOIN {$db_name_auto_trans}.auto a ON tlc.id = a.id_tech_limit_card
        LEFT JOIN tech_register trr ON tlc.id = trr.id_tech_limit_card AND trr.current_status != 'cancel'

		WHERE tlc.id = {$nID}
		";

        return $this->selectOnce($sQuery);
    }

    public function getInfoForPersonCard2($nID) {
        global $db_name_finance, $db_name_auto_trans;

        $sQuery = "
		SELECT
		tlc.id,
		tlc.id_request,
		CASE
			WHEN 'create' THEN 'Прием за сервиз'
		   WHEN 'destroy' THEN 'Спиране от сервиз'
		   WHEN 'arrange' THEN 'Ремонт'
		   WHEN 'holdup' THEN 'Авария'
		   WHEN 'plan' THEN 'Планово'
		END AS type,
        tlc.type AS type_tlc,
		c.id AS id_contract,
		c.obj_name,
		c.obj_address,
		c.obj_phone AS phone,
		c.client_mol AS face_name,
		c.client_phone AS face_phone,
		tlc.distance,
		DATE_FORMAT(tlc.planned_start, '%H:%i %d.%m.%Y') AS planned_start,
		DATE_FORMAT(tlc.planned_end, '%H:%i %d.%m.%Y') AS planned_end,
		DATE_FORMAT(tlc.real_start, '%H:%i %d.%m.%Y') AS real_start,
		DATE_FORMAT(tlc.real_end, '%H:%i %d.%m.%Y') AS real_end,
		a.tech_status,
		trr.current_status,
		trr_a.current_status as is_arrival

		FROM tech_limit_cards tlc
		LEFT JOIN tech_requests tr ON tr.id = tlc.id_request
		LEFT JOIN {$db_name_finance}.contracts c ON c.id = tr.id_contract
		LEFT JOIN {$db_name_auto_trans}.auto a ON tlc.id = a.id_tech_limit_card
        LEFT JOIN tech_register trr ON tlc.id = trr.id_tech_limit_card AND trr.current_status != 'cancel'
        LEFT JOIN tech_register trr_a ON tlc.id = trr_a.id_tech_limit_card AND trr_a.current_status = 'arrival'

		WHERE tlc.id = {$nID}


			
		";
        return $this->selectOnce($sQuery);
    }

    public function  setSalaries($nIDLimitCard) {
        global $db_name_sod;



        $oDBLimitCards = new DBTechLimitCards();
        $oDBTechRequests = new DBTechRequests();
        $oDBObjects = new DBObjects();
        $oDBObjects2 = new DBObjects2();
        $oDBContract = new DBContracts();
        $oDBNomenclatureServices = new DBNomenclaturesServices();
        $oDBObjectsSingles = new DBObjectsSingles();
        $oDBSalary = new DBSalary();
        $oDBHoldupReasons = new DBHoldupReasons();
        $oDBObjectServices = new DBObjectServices();
        $oLCPersons = new DBLimitCardPersons();
        $oDBLimitCardOperations = new DBLimitCardOperations();
        $oDBLimitCardPersons = new DBLimitCardPersons();
        $oDBStates = new DBStates();
//        $DBPersonCompetentionsLimitcard = new DBPersonCompetentionsLimitcard();
        $aLimitCard = $oDBLimitCards->getRecord($nIDLimitCard);
        $aRequest = $oDBTechRequests->getRecord($aLimitCard['id_request']);
        $aObject = $oDBObjects->getRecord($aLimitCard['id_object']);
        $oDBObjectsFeePersons = new DBObjectsFeePersons();
        $oDBContractsTerminationReasons = new DBContractsTerminationReasons();
        $oDBPersonContract = new DBPersonContract();


        $aLastService = $this->getLastService($aRequest['id_object']);
        $object_nomenclature = $oDBStates->getNomenclaturesForObject($aLimitCard['id_object']);
        $aLCPersons = array();
        $aLCPersons = $oLCPersons->getPersonByLC($nIDLimitCard);
        $dDate = date('d.m.Y', strtotime($aLimitCard['real_start']));

        if (strstr($aLastService['persons_id'], ',')) {
            $aLCLastPersons = explode(',', $aLastService['persons_id']);
        } else {
            $aLCLastPersons = $aLastService['persons_id'];
        }
        if ($aLimitCard['id_tech_timing'] == 2) { // Снемане на обект
            if (!$oDBContract->isContractOver($aLimitCard['id_object'])) { // ако договорът не е изтекъл
                // Добавя еднократно задължение
                $month_price = $oDBObjectServices->getSumPriceByObject($aLimitCard['id_object']); // месечна такса
                $month_remaining = $oDBContract->getRemainingTimeInMonths($aLimitCard['id_object']); // оставащи месеци по договора
                $to_destroy_id = (int) $oDBNomenclatureServices->getToDestroyId();
                $create_price = $oDBSalary->getCreateSalaryByObject($aLimitCard['id_object']);


//                if (empty($to_destroy_id) || !is_numeric($to_destroy_id))
//                    throw new Exception('Не е посочена услуга при сваляне > Номенклатури > Финанси > Номенклатури услуги!');

                $total_price = 0;

                if ($create_price > 0) { // Добавя като задължение наработката на техника от изграждането
                    $total_price += $create_price;
                }

                if ($month_remaining) { // добавя като задължение оставащите месеци по договора
                    //                    $price = $month_remaining * $month_price;
                    //                    $total_price += $price;
                    //                        $sQuery = "
                    //                            INSERT INTO {$db_name_sod}.objects_singles
                    //                              (id_object, id_office, id_service, id_limit_card, service_name, single_price, quantity, total_sum, start_date, paid_date,id_sale_doc,
                    //                               created_time)
                    //                            VALUES
                    //                              ({$aObject['id']}, {$aObject['id_office']}, {$to_destroy_id}, {$nIDLimitCard}, {$sInfo}, {$price}, 1, {$price}, ".date('Y-m-d').", 0000-00-00, 0, ".time().")
                    //                            ";
                    // Логиката е преместена в анекса за снемане
//                    $sInfo = "НЕУСТОЙКА ПО ДОГОВОР";
//
//                    $aSin = array();
//                    $aSin['id_object'] = $aObject['id'];
//                    $aSin['id_office'] = $aObject['id_office'];
//                    $aSin['id_service'] = $to_destroy_id;
//                    $aSin['id_schet'] = 0;
//                    $aSin['service_name'] = trim($sInfo);
//                    $aSin['quantity'] = 1;
//                    $aSin['single_price'] = $total_price;
//                    $aSin['total_sum'] = $total_price;
//                    $aSin['start_date'] = date('Y-m-d');
//                    $aSin['paid_date'] = "0000-00-00";
//                    $aSin['id_sale_doc'] = 0;
//                    $aSin['id_limit_card'] = $nIDLimitCard;
//                    $aSin['created_time'] = time();
//                    $aSin['to_arc'] = 0;
//
//                    $oDBObjectsSingles->update($aSin);

                    // наработка на техник

                    $price = $oDBHoldupReasons->getReasonById($aRequest['id_tech_reason']);

                    $total_sum = $price['price'];


//                    foreach ($aLCPersons as $val) {
//                        $price_percent_tmp = $oDBPersonContract->getTechSupportFactorByIDPersonLC($val['id_person']);
//                        $price_percent = $price_percent_tmp ? floatval($price_percent_tmp) : 1.0;
//
//
//                        $total_sum_tmp = (($total_sum * $val['percent'] / 100)*$price_percent);
//                        $aData = array();
//                        $aData['id_person'] = $val['id_person'];
//                        $aData['id_office'] = $aObject['id_office'];
//                        $aData['id_object'] = $aLimitCard['id_object'];
//                        $aData['id_limit_card'] = $nIDLimitCard;
//                        $aData['month'] = date('Ym');
//                        $aData['code'] = $val['code'];
//                        $aData['is_earning'] = '1';
//                        $aData['sum'] = $total_sum_tmp;
//                        $aData['description'] = "Наработка" . ' [' . $val['code'] . '/' . $dDate . '] - ' . date('H:i', strtotime($aLimitCard['real_start'])) . '/' . date('H:i', strtotime($aLimitCard['real_end']));
//                        $aData['count'] = '1';
//                        $aData['total_sum'] = $total_sum_tmp;
//
//                        $oDBSalary->update($aData);
//                    }

                    //                        if(!$oDBObjectsSingles->insert($sQuery)){
                    //                            throw new Exception('Error in query');
                    //                        }
                    // Начисляване на отрицателен хонорар

                    $requestData = $oDBTechRequests->getRequestData($aLimitCard['id_request']);

                    if (!empty($requestData) && is_array($requestData) && $requestData['tech_type'] == "contract" && !empty($requestData['id_contract'])) {

                        $destroyRequestAnex = $requestData['id_contract'];
                        $anexDestroy = $oDBContract->getContractByID($destroyRequestAnex);

                        $contractData = $oDBContract->getContractByID($anexDestroy['id_parent']);
                        $anexTermReason = $oDBContractsTerminationReasons->getTerminationReasonsByID($anexDestroy['id_contract_termination_reason']);
                        $lastPaidDate = $oDBObjectServices->getLastPaidMonth($aLimitCard['id_object']);



                        //Време от последното плащане до изтичане на договора
                        if(!empty($contractData) && !empty($lastPaidDate)) {
                            $diffMonths = round((strtotime($contractData['signed_time'] . ' + ' . $contractData['period_in_month'] . ' months') - strtotime($lastPaidDate . ' + 1 month')) / 60 / 60 / 24 / 30);
                            // Ако причината за прекратяване изисква начисляване на задължения

                            if ($contractData['period_in_month']) {
                                $remainingPercent = round($diffMonths / $contractData['period_in_month'], 2);
                            }
// 					APILog::Log(0,ArrayToString($remainingPercent));
// 					throw new Exception('stop');

                            if ($anexTermReason['is_duty'] == 1) {
                                $earningByObj = $oDBObjectsFeePersons->getPersonEarningFeeByObject($aLimitCard['id_object']);

                                foreach ($earningByObj as $val) {
                                    if($val['fee_type'] != 'contract_sign') {

                                        $remaining_sum = $val['fee_sum'] * $remainingPercent;

                                        if($remaining_sum < 0) {
                                            continue;
                                        }

                                        $aData = array();
                                        $aData['id_person'] = $val['id_person'];
                                        $aData['id_office'] = $aObject['id_office'];
                                        $aData['id_object'] = $aLimitCard['id_object'];
                                        $aData['id_limit_card'] = $nIDLimitCard;
                                        $aData['month'] = date('Ym');
                                        $aData['code'] = "-КОРЕКЦИЯ8";
                                        $aData['is_earning'] = '0';
                                        $aData['sum'] = $remaining_sum;
                                        $aData['description'] = "Удръжка хонорар по договор {$contractData['id']}";
                                        $aData['count'] = '1';
                                        $aData['total_sum'] = $remaining_sum;

                                        $oDBSalary->update($aData);



                                    }
                                }
                            }
                        }
                    }
                }
            } else { // ако договорът е изтекъл
                // наработка на техник
                $reason = $oDBHoldupReasons->getReasonById($aRequest['id_tech_reason']);
//                foreach ($aLCPersons as $val) {
//
//                    $price_percent_temp = $oDBPersonContract->getTechSupportFactorByIDPersonLC($val['id_person']);
//                    $price_percent = !empty($price_percent_temp['tech_support_factor'])  ? floatval($price_percent_temp['tech_support_factor']) : 1.0;
//
//
//                    $total_sum_tmp = (($reason['price'] * $val['percent'] / 100)*$price_percent);
//                    $aData = array();
//                    $aData['id_person'] = $val['id_person'];
//                    $aData['id_office'] = $aObject['id_office'];
//                    $aData['id_object'] = $aLimitCard['id_object'];
//                    $aData['id_limit_card'] = $nIDLimitCard;
//                    $aData['month'] = date('Ym');
//                    $aData['code'] = $val['code'];
//                    $aData['is_earning'] = '1';
//                    $aData['sum'] = $total_sum_tmp;
//                    $aData['description'] = "Наработка" . ' [' . $val['code'] . '/' . $dDate . '] - ' . date('H:i', strtotime($aLimitCard['real_start'])) . '/' . date('H:i', strtotime($aLimitCard['real_end']));
//                    $aData['count'] = '1';
//                    $aData['total_sum'] = $total_sum_tmp;
//
//                    $oDBSalary->update($aData);
//                }
            }


            //Status History

            if ($aObject['id_status'] != '4') {
                $oDBObjectStatuses = new DBObjectStatuses();
                $aData = array();
                $aData['id_status'] = '4';
                $aData['id_old_status'] = $aObject['id_status'];
                $aData['id_obj'] = $aObject['id'];

                $oDBObjectStatuses->update($aData);
            }
            //End Status History

            // Vremenno sprqn - po jelanie na klienta
            if($aRequest['id_tech_reason'] == 15) {
                $aObject['id_status'] = '5';

            } else {
                $aObject['id_status'] = '4';
            }

            $oDBObjects->update($aObject);

            if (!empty($aObject['id_oldobj'])) {
                $oDBObjects2->makeNotActive($aObject['id_oldobj']);
            }
        } elseif ($oDBTechRequests->getRequestBy($aLimitCard['id']) === 'company') { //
            $reason = $oDBHoldupReasons->getReasonById($aRequest['id_tech_reason']);
            if ($oDBHoldupReasons->isWarranty($aLimitCard['id_object']) && (int) $reason['is_warranty'] == 1) { // ако обекта е в гаранция
                //                    APILog::Log(0,ArrayToString($aLastService));
                //                    APILog::Log(0,ArrayToString($aLCPersons));
                //                    throw new Exception('stop');
                //                    if ($aLastService['persons_id'] == $aLCPersons[0]['id_person'] ) { // ако техника е един и същ се добавя нулева наработка
                ////                        APILog::Log(0,ArrayToString($aLastService));
                ////                        APILog::Log(0,ArrayToString($aLCPersons));
                ////
                ////                        throw new Exception ('stop');
                //
                //                        //throw new Exception ('edin i sy6t tehnik');
                //
                //                        foreach ($aLCPersons as $val) {
                //
                //                            $aData = array();
                //                            $aData['id_person'] = $val['id_person'];
                //                            $aData['id_office'] = $aObject['id_office'];
                //                            $aData['id_object'] = $aLimitCard['id_object'];
                //                            $aData['id_limit_card'] = $nIDLimitCard;
                //                            $aData['month'] = date('Ym');
                //                            $aData['code'] = $val['code'];
                //                            $aData['is_earning'] = '1';
                //                            $aData['sum'] = 0;
                //                            $aData['description'] = "Наработка - от гаранционен обект " . ' [' . $val['code'] . '/' . $dDate . '] - ' . date('H:i', strtotime($aLimitCard['real_start'])) . '/' . date('H:i', strtotime($aLimitCard['real_end']));
                //                            $aData['count'] = '1';
                //                            $aData['total_sum'] = 0;
                //
                //                            $oDBSalary->update($aData);
                //                        }
                //
                //                    } else { // ако техникът е различен
                //                           else {  //ако не се добавя едн. задължение
                $aLastService = $this->getLastService($aRequest['id_object'], 1);
                $reason = $oDBHoldupReasons->getReasonById($aRequest['id_tech_reason']);
                $lastSalary = $oDBSalary->getSalaryByLimitCard($aLastService['id_limit_card']);




                //                        if (!empty($reason['price']) && $reason['id'] != 1) // прибавяме цената на операцията
                //                            $total_price -= $reason['price'];
                //
                //
                //                        if ($reason['operations_affect'] == 1) { // ако причината зависи от операциите
                //                            $aEarnings = $oDBLimitCardOperations->getEarning($nIDLimitCard);
                //                            $total_price -= $aEarnings['price1'];
                //                        }
                // прибавяне на наработка към нов техник

                $total_price_add = 0;

                if (!empty($reason['price'])) // прибавяме цената на операцията
                    $total_price_add += $reason['price'];


                if ((int) $reason['operations_affect'] == 1) { // ако причината зависи от операциите
                    $aEarnings = $oDBLimitCardOperations->getEarning($nIDLimitCard);
                    $total_price_add += $aEarnings['price1'];
                }

//                foreach ($aLCPersons as $val) {
//
//                    $price_percent_tmp = $oDBPersonContract->getTechSupportFactorByIDPersonLC($val['id_person']);
//                    $price_percent = !empty($price_percent_tmp)  ? floatval($price_percent_tmp) : 1.0;
//
//                    $total_sum_tmp = (($total_price_add * $val['percent'] / 100)*$price_percent);
//                    $aData = array();
//                    $aData['id_person'] = $val['id_person'];
//                    $aData['id_office'] = $aObject['id_office'];
//                    $aData['id_object'] = $aLimitCard['id_object'];
//                    $aData['id_limit_card'] = $nIDLimitCard;
//                    $aData['month'] = date('Ym');
//                    $aData['code'] = $val['code'];
//                    $aData['is_earning'] = '1';
//                    $aData['sum'] = $total_sum_tmp;
//                    $aData['description'] = "Наработка" . ' [' . $val['code'] . '/' . $dDate . '] - ' . date('H:i', strtotime($aLimitCard['real_start'])) . '/' . date('H:i', strtotime($aLimitCard['real_end']));
//                    $aData['count'] = '1';
//                    $aData['total_sum'] = $total_sum_tmp;
//
//                    $oDBSalary->update($aData);
//                }

                // отрицателна наработка на предишния техник
                if (count($lastSalary) > 0) {
                    foreach ($lastSalary as $tmp) {
                        $last_pice = 0;
                        if (is_array($tmp) && isset($tmp['total_sum']) && (int) $tmp['total_sum'] > 0 && (int) $aObject['id_office'] != 82 && (int) $aLimitCard['id_tech_timing'] != 3) {
                            if ((int) $tmp['id_tech_timing'] == 1) {
                                // Ако последната лимитна карта е от изграждане вадим от предния техник само текущата цена
                                $last_pice -= (int) $total_price_add;
                            } else {
                                // В противен случай вадим цялата заработка
                                $last_pice -= (int) $tmp['total_sum'];
                            }

//                            $aData = array();
//                            $aData['id_person'] = $tmp['id_person'];
//                            $aData['id_office'] = $aObject['id_office'];
//                            $aData['id_object'] = $aLimitCard['id_object'];
//                            $aData['id_limit_card'] = $nIDLimitCard;
//                            $aData['month'] = date('Ym');
//                            $aData['code'] = "+ТЕХН";
//                            $aData['is_earning'] = '1';
//                            $aData['sum'] = $last_pice;
//                            $aData['description'] = "Наработка - отрицателна /гаранция/" . ' [+ТЕХН/' . $dDate . '] - ' . date('H:i', strtotime($aLimitCard['real_start'])) . '/' . date('H:i', strtotime($aLimitCard['real_end']));
//                            $aData['count'] = '1';
//                            $aData['total_sum'] = $last_pice;
//
//                            $oDBSalary->update($aData);
                        }
                    }
                }


                //                    }
                //                            $aEarnings = $oDBLimitCardOperations->getEarning($nIDLimitCard);
                //                            $nLimitCardPrice = $aEarnings['price1'];
            } else { // обекта не е в гаранция
                if ($oDBHoldupReasons->isToObjectSingles($aRequest['id_tech_reason'])) { // еднократно задължение
                    //                        throw new Exception ('elektronno zadyljenie');
                    $to_destroy_id = (int) $oDBNomenclatureServices->getToDestroyId();

                    $total_price = 0;

                    $reason = $oDBHoldupReasons->getReasonById($aRequest['id_tech_reason']);

                    if (!empty($reason['price'])) // прибавяме цената на операцията
                        $total_price += $reason['price'];


                    if ($reason['operations_affect'] == 1) { // ако причината зависи от операциите
                        $aEarnings = $oDBLimitCardOperations->getEarning($nIDLimitCard);
                        $total_price += $aEarnings['price1'];
                    }

                    //прибавяме задължението

                    $sInfo = "{$reason['name']} ";

                    $aSin = array();
                    $aSin['id_object'] = $aObject['id'];
                    $aSin['id_office'] = $aObject['id_office'];
                    $aSin['id_service'] = $to_destroy_id;
                    $aSin['id_schet'] = 0;
                    $aSin['service_name'] = trim($sInfo);
                    $aSin['quantity'] = 1;
                    $aSin['single_price'] = $total_price * self::SALARY_COEFFICIENT;
                    $aSin['total_sum'] = $total_price * self::SALARY_COEFFICIENT;
                    $aSin['start_date'] = date('Y-m-d');
                    $aSin['paid_date'] = "0000-00-00";
                    $aSin['id_sale_doc'] = 0;
                    $aSin['id_limit_card'] = $nIDLimitCard;
                    $aSin['created_time'] = time();
                    $aSin['to_arc'] = 0;
                    //начисление на обект е спрано за сега за лифтком
                    //$oDBObjectsSingles->update($aSin);

                    //добавяме наработката на техника


//                    foreach ($aLCPersons as $val) {
//
//                        $price_percent_tmp = $oDBPersonContract->getTechSupportFactorByIDPersonLC($val['id_person']);
//                        $price_percent = !empty($price_percent_tmp)  ? floatval($price_percent_tmp) : 1.0;
//
//
//                        $total_sum_tmp = (($total_price * $val['percent'] / 100)*$price_percent);
//                        $aData = array();
//                        $aData['id_person'] = $val['id_person'];
//                        $aData['id_office'] = $aObject['id_office'];
//                        $aData['id_object'] = $aLimitCard['id_object'];
//                        $aData['id_limit_card'] = $nIDLimitCard;
//                        $aData['month'] = date('Ym');
//                        $aData['code'] = $val['code'];
//                        $aData['is_earning'] = '1';
//                        $aData['sum'] = $total_sum_tmp;
//                        $aData['description'] = "Наработка" . ' [' . $val['code'] . '/' . $dDate . '] - ' . date('H:i', strtotime($aLimitCard['real_start'])) . '/' . date('H:i', strtotime($aLimitCard['real_end']));
//                        $aData['count'] = '1';
//                        $aData['total_sum'] = $total_sum_tmp;
//
//                        $oDBSalary->update($aData);
//                    }
                } else { // не добавя задължение
                    $total_price = 0;

                    $reason = $oDBHoldupReasons->getReasonById($aRequest['id_tech_reason']);

                    // прибавяне на наработка към нов техник

                    $total_price = 0;

                    if (!empty($reason['price'])) // прибавяме цената на операцията
                        $total_price += $reason['price'];


                    if ($reason['operations_affect'] == 1) { // ако причината зависи от операциите
                        $aEarnings = $oDBLimitCardOperations->getEarning($nIDLimitCard);
                        $total_price += $aEarnings['price1'];
                    }

                    /* APILog::Log(0, ArrayToString($aLCPersons));
                      throw new Exception ('dsadas'); */


//                    foreach ($aLCPersons as $val) {
//                        //                            APILog::Log(0, ArrayToString($val));
//
//                        $price_percent_tmp = $oDBPersonContract->getTechSupportFactorByIDPersonLC($val['id_person']);
//                        $price_percent = !empty($price_percent_tmp)  ? floatval($price_percent_tmp) : 1.0;
//
//
//                        $total_sum_tmp = (($total_price * $val['percent'] / 100)*$price_percent);
//                        $aData = array();
//                        $aData['id_person'] = $val['id_person'];
//                        $aData['id_office'] = $aObject['id_office'];
//                        $aData['id_object'] = $aLimitCard['id_object'];
//                        $aData['id_limit_card'] = $nIDLimitCard;
//                        $aData['month'] = date('Ym');
//                        $aData['code'] = $val['code'];
//                        $aData['is_earning'] = '1';
//                        $aData['sum'] = $total_sum_tmp;
//                        $aData['description'] = "Наработка" . ' [' . $val['code'] . '/' . $dDate . '] - ' . date('H:i', strtotime($aLimitCard['real_start'])) . '/' . date('H:i', strtotime($aLimitCard['real_end']));
//                        $aData['count'] = '1';
//                        $aData['total_sum'] = $total_sum_tmp;
//
//                        $oDBSalary->update($aData);
//                    }
                }
            }
        }  elseif($aLimitCard['id_tech_timing'] == 1 && $aRequest['id_tech_reason'] == 1){

            //Status History

            if ($aObject['id_status'] == '5') {
                $oDBObjectStatuses = new DBObjectStatuses();
                $aData = array();
                $aData['id_status'] = '1';
                $aData['id_old_status'] = $aObject['id_status'];
                $aData['id_obj'] = $aObject['id'];

                $oDBObjectStatuses->update($aData);
            }
            //End Status History

            $aObject['id_status'] = '1';


            $oDBObjects->update($aObject);


        } else { // ако е заяван от клиент
            $to_destroy_id = (int) $oDBNomenclatureServices->getToDestroyId();

            $total_price = 0;

            $reason = $oDBHoldupReasons->getReasonById($aRequest['id_tech_reason']);

            if (!empty($reason['price'])) // прибавяме цената на операцията
                $total_price += $reason['price'];


            if ($reason['operations_affect'] == 1) { // ако причината зависи от операциите
                $aEarnings = $oDBLimitCardOperations->getEarning($nIDLimitCard);
                $total_price += $aEarnings['price1'];
            }

            //прибавяме задължението

            $sInfo = "{$reason['name']} ";

            $aSin = array();
            $aSin['id_object'] = $aObject['id'];
            $aSin['id_office'] = $aObject['id_office'];
            $aSin['id_service'] = $to_destroy_id;
            $aSin['id_schet'] = 0;
            $aSin['service_name'] = trim($sInfo);
            $aSin['quantity'] = 1;
            $aSin['single_price'] = $total_price * self::SALARY_COEFFICIENT;
            $aSin['total_sum'] = $total_price * self::SALARY_COEFFICIENT;
            $aSin['start_date'] = date('Y-m-d');
            $aSin['paid_date'] = "0000-00-00";
            $aSin['id_sale_doc'] = 0;
            $aSin['id_limit_card'] = $nIDLimitCard;
            $aSin['created_time'] = time();
            $aSin['to_arc'] = 0;

            //спират се начисленията за обект
            //$oDBObjectsSingles->update($aSin);

            //добавяме наработката на техника


//            foreach ($aLCPersons as $val) {
//
//                $price_percent_tmp = $oDBPersonContract->getTechSupportFactorByIDPersonLC($val['id_person']);
//                $price_percent = !empty($price_percent_tmp)  ? floatval($price_percent_tmp) : 1.0;
//
//                $total_sum_tmp = (($total_price * $val['percent'] / 100)*$price_percent);
//                $aData = array();
//                $aData['id_person'] = $val['id_person'];
//                $aData['id_office'] = $aObject['id_office'];
//                $aData['id_object'] = $aLimitCard['id_object'];
//                $aData['id_limit_card'] = $nIDLimitCard;
//                $aData['month'] = date('Ym');
//                $aData['code'] = $val['code'];
//                $aData['is_earning'] = '1';
//                $aData['sum'] = $total_sum_tmp;
//                $aData['description'] = "Наработка" . ' [' . $val['code'] . '/' . $dDate . '] - ' . date('H:i', strtotime($aLimitCard['real_start'])) . '/' . date('H:i', strtotime($aLimitCard['real_end']));
//                $aData['count'] = '1';
//                $aData['total_sum'] = $total_sum_tmp;
//
//                $oDBSalary->update($aData);
//            }
        }



    }

    //        }


    public function setSalaries_old($nIDLimitCard) {
        global $db_sod;

        $oDBTechRequests = new DBTechRequests();
        $oDBObjects = new DBObjects();
        $oDBTechSettings = new DBTechSettings();
        $oLCPersons = new DBLimitCardPersons();
        $oDBLimitCardOperations = new DBLimitCardOperations();
        $oDBSalary = new DBSalary();

        $aLimitCard = $this->getRecord($nIDLimitCard);

        $aLCPersons = array();
        $aLCPersons = $oLCPersons->getPersonByLC($nIDLimitCard);

        $dDate = date('d.m.Y', strtotime($aLimitCard['real_start']));

        $aTechSettings = $oDBTechSettings->getActiveSettings();

        $nLimitCardPrice = 0;

        //            APILog::Log(0, $aLimitCard);
        //            throw new Exception ('dadsada');

        if (($aLimitCard['id_tech_timing'] != 1)) { //|| ($aLimitCard['type'] != 'plan') //|| ($aLimitCard['type'] != 'plan')
            switch ($aLimitCard['id_tech_timing']) {
                case 2:
                    $nLimitCardPrice = $aTechSettings['tech_price_destroy'];
                    break;
                case 3:
                    $nLimitCardPrice = $aTechSettings['tech_price_arrange'];
                    break;
                case 4:
                    $nLimitCardPrice = $aTechSettings['tech_price_holdup'];
                    break;
                //case 'plan': 	$nLimitCardPrice = $aTechSettings['tech_price_arrange'];break;
            }
        } else {
            $aEarnings = $oDBLimitCardOperations->getEarning($nIDLimitCard);
            $nLimitCardPrice = $aEarnings['price1'];
        }

        if (empty($aLimitCard['id_object'])) {
            $aFactors = $oDBTechRequests->getFactorTechSupport($aLimitCard['id_request']);
        } else {
            $aFactors = $oDBObjects->getFactorTechSupport($aLimitCard['id_object']);
        }

        if ($aLimitCard['distance'] > 15) {
            $nLimitCardPrice += ($aLimitCard['distance'] - 15) * $aTechSettings['tech_price_km'] * $aFactors['factor_tech_distance'];
        }

        $aRequest = $oDBTechRequests->getRecord($aLimitCard['id_request']);

        //		$filename = "tech.txt";
        //
        //		$content = ArrayToString( $aRequest );
        //
        //		if ( !$handle = fopen($filename, "w+") ) {
        //			exit;
        //		}
        //
        //		if ( fwrite($handle, $content) === FALSE ) {
        //			exit;
        //		}

        if ((($aRequest['type'] == "create")) && ($aRequest['tech_type'] != "contract")) { //&& ($aRequest['tech_type'] != "contract") //|| ($aRequest['type'] == "plan")
            $nIDReq = $aRequest['id'];
            $db_sod->Execute("UPDATE tech_limit_cards SET `type` = 'arrange' WHERE id = '{$nIDLimitCard}'");
            //$db_sod->Execute("UPDATE tech_requests SET `type` = 'arrange' WHERE id = '{$nIDReq}'");
        }

        foreach ($aLCPersons as $val) {

            $nEarning = $nLimitCardPrice * ($val['percent'] / 100) * $val['factor'];

            $aData = array();
            $aData['id_person'] = $val['id_person'];
            $aData['id_office'] = $aFactors['id_tech_office'];
            $aData['id_object'] = $aLimitCard['id_object'];
            $aData['id_limit_card'] = $nIDLimitCard;
            $aData['month'] = date('Ym');
            $aData['code'] = $val['code'];
            $aData['is_earning'] = '1';
            $aData['sum'] = $nEarning;
            $aData['description'] = "Наработка" . ' [' . $val['code'] . '/' . $dDate . '] - ' . date('H:i', strtotime($aLimitCard['real_start'])) . '/' . date('H:i', strtotime($aLimitCard['real_end']));
            $aData['count'] = '1';
            $aData['total_sum'] = $nEarning;

            $oDBSalary->update($aData);
        }
    }

    public function cancel2DaysLimitCards() {

        $sQuery = "
				UPDATE
				tech_limit_cards tlc
				LEFT JOIN tech_requests tr ON  tr.id = tlc.id_request
				SET
				tlc.status = 'cancel',
				tr.id_limit_card = 0

				WHERE 1
				AND tlc.to_arc = 0
				AND tlc.status = 'active'
				AND tlc.real_start = '0000-00-00 00:00:00'
				AND (
				(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(tlc.planned_start)) > (2 * 24 * 60 * 60)
				)
				";

        $this->oDB->Execute($sQuery);
    }

    public function getHours($nIDPerson, $sDate, &$sHint = "") {
        /*$sQuery = "
		SELECT
		SUM(
		FLOOR(
		( UNIX_TIMESTAMP(tlc.real_end) - UNIX_TIMESTAMP(tlc.real_start) ) / (60 * 60)
		)
		) AS hours,

		SUM(
		IF
		(
		tlc.id_tech_timing = 1,
		FLOOR( ( UNIX_TIMESTAMP(tlc.real_end) - UNIX_TIMESTAMP(tlc.real_start) ) / (60 * 60) ),
		NULL
		)
		) AS create_hours,
		SUM(
		IF
		(
		tlc.id_tech_timing = 5,
		FLOOR( ( UNIX_TIMESTAMP(tlc.real_end) - UNIX_TIMESTAMP(tlc.real_start) ) / (60 * 60) ),
		NULL
		)
		) AS plan_hours,
		SUM(
		IF
		(
		tlc.id_tech_timing = 2,
		FLOOR( ( UNIX_TIMESTAMP(tlc.real_end) - UNIX_TIMESTAMP(tlc.real_start) ) / (60 * 60) ),
		NULL
		)
		) AS destroy_hours,
		SUM(
		IF
		(
		tlc.id_tech_timing = 3,
		FLOOR( ( UNIX_TIMESTAMP(tlc.real_end) - UNIX_TIMESTAMP(tlc.real_start) ) / (60 * 60) ),
		NULL
		)
		) AS arrange_hours,
		SUM(
		IF
		(
		tlc.id_tech_timing = 4,
		FLOOR( ( UNIX_TIMESTAMP(tlc.real_end) - UNIX_TIMESTAMP(tlc.real_start) ) / (60 * 60) ),
		NULL
		)
		) AS holdup_hours
		FROM tech_limit_cards tlc
		LEFT JOIN limit_card_persons lcp ON lcp.id_limit_card = tlc.id
		WHERE 1
		AND tlc.to_arc = 0
		AND FLOOR( ( UNIX_TIMESTAMP(tlc.real_end) - UNIX_TIMESTAMP(tlc.real_start) ) / (60 * 60) ) != 0
		AND tlc.status = 'closed'
		AND lcp.id_person = {$nIDPerson}
		AND tlc.real_start LIKE '{$sDate}%'
		";*/

        $sQuery = "
                    SELECT
                    TIME_FORMAT(SEC_TO_TIME(
                        SUM( TIME_TO_SEC(TIMEDIFF(tlc.real_end,tlc.real_start)))
                    )
                    ,
                    '%H:%i')
                    as hours,


                    SEC_TO_TIME(
                        SUM(
                            IF(
                                tlc.id_tech_timing = 1,
                                TIME_TO_SEC(TIMEDIFF(tlc.real_end,tlc.real_start)),
                                NULL
                            )
                        )
                    ) as create_hours,

                    SEC_TO_TIME(
                    SUM(
                        IF(
                            tlc.id_tech_timing = 2,
                            TIME_TO_SEC(TIMEDIFF(tlc.real_end,tlc.real_start)),
                            NULL
                        )
                    )
                    ) as destroy_hours,

                    SEC_TO_TIME(
                    SUM(
                        IF(
                            tlc.id_tech_timing = 3,
                            TIME_TO_SEC(TIMEDIFF(tlc.real_end,tlc.real_start)),
                            NULL
                        )
                    )
                    ) as arrange_hours,

                    SEC_TO_TIME(
                    SUM(
                        IF(
                            tlc.id_tech_timing = 4,
                            TIME_TO_SEC(TIMEDIFF(tlc.real_end,tlc.real_start)),
                            NULL
                        )
                    )
                    ) as holdup_hours,

                    SEC_TO_TIME(
                    SUM(
                        IF(
                            tlc.id_tech_timing = 5,
                            TIME_TO_SEC(TIMEDIFF(tlc.real_end,tlc.real_start)),
                            NULL
                        )
                    )
                    ) as plan_hours




                FROM tech_limit_cards tlc
                LEFT JOIN limit_card_persons lcp ON lcp.id_limit_card = tlc.id
                WHERE 1
                AND tlc.status = 'closed'
                AND tlc.real_start LIKE '{$sDate}%'
                AND lcp.id_person = {$nIDPerson}
        ";

//        APILog::Log($sQuery);
        $aData = $this->selectOnce($sQuery);

        if (!empty($aData)) {
            if (isset($aData['create_hours']) &&
                isset($aData['destroy_hours']) &&
                isset($aData['arrange_hours']) &&
                isset($aData['plan_hours']) &&
                isset($aData['holdup_hours'])
            ) {
                $sHint = "";
                if (!empty($aData['create_hours']))
                    $sHint .= "Прием за поддръжка:\t\t{$aData['create_hours']} ч.";
                if (!empty($aData['destroy_hours']))
                    $sHint .= "\nСнемане :\t\t{$aData['destroy_hours']} ч.";
                if (!empty($aData['arrange_hours']))
                    $sHint .= "\nРемонт:\t\t{$aData['arrange_hours']} ч.";
                if (!empty($aData['plan_hours']))
                    $sHint .= "\nПланово:\t\t{$aData['plan_hours']} ч.";
                if (!empty($aData['holdup_hours']))
                    $sHint .= "\nПрофилактика :\t{$aData['holdup_hours']} ч.";
            }

            if (isset($aData['hours']))
                return $aData['hours'];
            else
                return 0;
        }
        else
            return 0;
    }

    public function getCountServices($nIDPerson, $sDate) {

        $sQuery = "
		SELECT
		#tlc.type,
		tlc.id_tech_timing,
		count(*) AS count
		FROM tech_limit_cards tlc
		RIGHT JOIN limit_card_persons lcp ON lcp.id_limit_card = tlc.id
		WHERE 1
		AND tlc.status = 'closed'
		AND lcp.id_person = {$nIDPerson}
		AND tlc.real_start LIKE '{$sDate}%'
		#GROUP BY tlc.type
		GROUP BY tlc.id_tech_timing
		";

        return $this->selectAssoc($sQuery);
    }

    public function getCountLimitCardsTypes($nIDPerson, $sFirsDay , $sLastDay) {
        $sQuery = "
            SELECT
            tt.name as type,
            count(*) AS count
            FROM tech_limit_cards tlc
            LEFT JOIN limit_card_persons lcp ON lcp.id_limit_card = tlc.id
            LEFT JOIN tech_timing tt ON
                tlc.id_tech_timing = tt.id
            WHERE 1
            AND tlc.status = 'closed'
            AND lcp.id_person = {$nIDPerson}
            AND tlc.real_start BETWEEN '{$sFirsDay}' AND '{$sLastDay}'
            GROUP BY tt.name
		";

        return $this->selectAssoc($sQuery);

    }

    public function attachObject($nID, $nIDObject) {

        $sQuery = "
		UPDATE
		tech_limit_cards
		SET id_object = {$nIDObject}
		WHERE id = {$nID}

		";
        $this->oDB->Execute($sQuery);
    }

    public function setRealStart($nID) {
        global $db_name_sod;

        $nIDUser = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;

        $sQuery = "
			UPDATE
				tech_limit_cards
			SET real_start = NOW(),
				updated_user = {$nIDUser},
				updated_time = NOW()				
			WHERE id = {$nID}
		";
        $this->oDB->Execute($sQuery);
    }

    public function setRealEnd($nID) {
        global $db_name_sod;

        $nIDUser = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;

        $sQuery = "
			UPDATE
				tech_limit_cards
			SET
				real_end = now(),
				updated_user = {$nIDUser},
				updated_time = NOW()			
			WHERE id = {$nID}
		";
        $this->oDB->Execute($sQuery);
    }

    public function closedStatus($nID) {

        $sQuery = "
		UPDATE
		tech_limit_cards
		SET
		status = 'closed'
		WHERE id = {$nID}
		";
        $this->oDB->Execute($sQuery);
    }

    public function getRealStart($nID) {

        $sQuery = "
		SELECT
		DATE_FORMAT(real_start, '%H:%i %d.%m.%Y') AS real_start
		FROM tech_limit_cards
		WHERE id = {$nID}
		";

        return $this->selectOne($sQuery);
    }

    public function getRealEnd($nID) {

        $sQuery = "
		SELECT
		DATE_FORMAT(real_end, '%H:%i %d.%m.%Y') AS real_end
		FROM tech_limit_cards
		WHERE id = {$nID}
		";

        return $this->selectOne($sQuery);
    }

    public function getLastService($nIDObject, $isWarranty = 0) {
        global $db_name_personnel;

        $warrantyOrder = '';
        if ($isWarranty) {
            $warrantyOrder = 'AND tre.is_warranty = 1';
        }

        if (empty($nIDObject) || !is_numeric($nIDObject))
            return array();

        /* $sQuery = "
          SELECT
          MAX( tl.created_time ) AS created_time,
          DATE_FORMAT( tl.created_time, '%d.%m.%Y' ) AS date,
          CASE
          WHEN tl.type = 'create' THEN 'Изграждане'
          WHEN tl.type = 'destroy' THEN 'Снемане'
          WHEN tl.type = 'arrange' THEN 'Аранжиране'
          WHEN tl.type = 'holdup' THEN 'Профилактика'
          WHEN tl.type = 'plan' THEN 'Планово обсл.'
          END AS type,
          GROUP_CONCAT( CONCAT_WS( ' ', p.fname, p.lname ) SEPARATOR ', ' ) AS persons,
          p.id AS persons_id
          FROM tech_limit_cards tl
          LEFT JOIN objects obj ON obj.id = tl.id_object
          LEFT JOIN limit_card_persons lcp ON lcp.id_limit_card = tl.id
          LEFT JOIN {$db_name_personnel}.personnel p ON FIND_IN_SET( p.id, lcp.id_person )
          WHERE 1
          AND obj.id = {$nIDObject}
          AND tl.to_arc = 0
          AND tl.status = 'closed'
          GROUP BY created_time
          ORDER BY created_time DESC
          LIMIT 1
          "; */

        $sQuery = "
		SELECT

		#MAX( tl.created_time ) AS created_time,
		MAX( tl.real_end ) AS created_time,



		#DATE_FORMAT( tl.created_time, '%d.%m.%Y' ) AS date,
		DATE_FORMAT( tl.real_end, '%d.%m.%Y' ) AS date,

		#DATE_FORMAT( tl.real_end, '%Y.%m.%d' ) AS date_end,

		MAX( tl.id) AS max_id,

		obj.name AS obekt,

		obj.id AS id_oj,

		tre.name AS reason_name,

		tre.id AS reason_id,

		tre.is_warranty,

		tre.warranty_time,

		tt.description AS type,

		lcp.id_limit_card,


		DATE_FORMAT( tl.created_time, '%d.%m.%Y' ) AS date,

		/*			CASE

		WHEN tl.type = 'create' THEN 'Изграждане'

		WHEN tl.type = 'destroy' THEN 'Снемане'

		WHEN tl.type = 'arrange' THEN 'Аранжиране'

		WHEN tl.type = 'holdup' THEN 'Профилактика'

		WHEN tl.type = 'plan' THEN 'Планово обсл.'

		END AS type,*/

		GROUP_CONCAT( CONCAT_WS( ' ', p.fname, p.lname ) SEPARATOR ', ' ) AS persons,

		GROUP_CONCAT( p.id SEPARATOR',') AS persons_id

		FROM tech_limit_cards tl

		LEFT JOIN objects obj ON obj.id = tl.id_object

		LEFT JOIN limit_card_persons lcp ON lcp.id_limit_card = tl.id

		LEFT JOIN {$db_name_personnel}.personnel p ON FIND_IN_SET( p.id, lcp.id_person )

		LEFT JOIN tech_requests tr ON tr.id = tl.id_request

		LEFT JOIN tech_reason tre ON tre.id = tr.id_tech_reason

		LEFT JOIN tech_timing tt ON tt.id = tl.id_tech_timing

		WHERE 1

		AND obj.id = {$nIDObject}

		AND tl.to_arc = 0

		AND tr.to_arc = 0

		AND tl.status = 'closed'

		{$warrantyOrder}

		GROUP BY tl.created_time

		ORDER BY tl.created_time DESC

		LIMIT 1

		";

        //            APILog::Log($sQuery);
        //        APILog::Log(0, $sQuery);
        //        throw  new Exception('stop');
        return $this->selectOnce($sQuery);
    }

    public function getTechLimitCardsForWorkCardMovement($nIDObject, $dFrom, $dTo) {
        $sQuery = "
		SELECT
		tlc.id,
		tlc.id_object,
		tlc.note as expl,
		DATE_FORMAT(tlc.real_end, '%d-%m') as real_end,
		hr.name as reason,
		CASE
			WHEN 'create' THEN 'Прием за сервиз'
			WHEN 'destroy' THEN 'Спиране от сервиз'
		   WHEN 'arrange' THEN 'Ремонт'
		   WHEN 'holdup' THEN 'Авария'
		   WHEN 'plan' THEN 'Планово'
		END AS type
		FROM tech_limit_cards tlc
		LEFT JOIN tech_requests		tr		ON	tr.id	=	tlc.id_request
		LEFT JOIN tech_reason	hr		ON	hr.id	=	tr.id_tech_reason
		WHERE 1
		AND tlc.to_arc = 0
		AND (tlc.type = 'arrange' OR tlc.type = 'holdup' OR tlc.type = 'plan')
		AND tlc.status = 'closed'
		AND tlc.id_object = {$nIDObject}
		";
        if (!empty($dFrom))
            $sQuery .= " AND UNIX_TIMESTAMP(tlc.real_end) >= {$dFrom}	\n";
        if (!empty($dTo))
            $sQuery .= " AND UNIX_TIMESTAMP(tlc.real_end) <= {$dTo} \n";
        return $this->select($sQuery);
    }

    public function getByObjAndDate($nIDObject, $sDate) {

        $sQuery = "
		SELECT
		SUM(IF(tlc.status = 'closed',1,0)) AS closed,
		SUM(IF(tlc.status = 'active',1,0)) AS active
		FROM tech_limit_cards tlc
		WHERE tlc.to_arc = 0
		AND DATE(tlc.planned_start) = '{$sDate}'
		AND tlc.id_object = {$nIDObject}
		GROUP BY tlc.id_object
		";

        return $this->selectOnce($sQuery);
    }

    public function getOldLimitCards($now = false) {

        global $db_name_sod;

        $sQuery = "
			
		SELECT
		SQL_CALC_FOUND_ROWS
		lc.id AS id_limit_card,
		lc.id_request
			
		FROM
		{$db_name_sod}.tech_limit_cards as lc
		LEFT JOIN {$db_name_sod}.tech_requests as tr ON tr.id = lc.id_request
		WHERE 1
        AND tr.request_by != 'office'
		AND lc.to_arc = 0
		AND lc.status = 'active'
		AND lc.real_start = 0
		#AND lc.planned_start < ( NOW() - INTERVAL 1 DAY )
			
		";

        $sQuery .= ($now == false) ? " AND lc.planned_start < ( NOW() - INTERVAL 1 DAY ) " : " AND lc.planned_start < NOW() ";

        $aExpired = $this->select($sQuery);

        return ($aExpired);
    }

    public function getLimitCardByPPP($nIDPPP) {
        global $db_name_storage;

        if (empty($nIDPPP) || !is_numeric($nIDPPP)) {
            return array();
        }

        $sQuery = "
		SELECT
		tlc.*
		FROM
		{$db_name_storage}.ppp ppp
		LEFT JOIN
		tech_limit_cards tlc ON tlc.id = ppp.id_limit_card
		WHERE
		ppp.to_arc = 0
		AND tlc.to_arc = 0
		AND ppp.id = {$nIDPPP}
		LIMIT 1
		";

        return $this->selectOnce($sQuery);
    }

    public function getContractByLimitCard($nIDLimitCard) {
        global $db_name_sod, $db_name_finance;

        if (empty($nIDLimitCard) || !is_numeric($nIDLimitCard)) {
            return array();
        }

        $sQuery = "
		SELECT
		c.*
		FROM {$db_name_finance}.contracts c
		LEFT JOIN {$db_name_sod}.tech_requests tr ON ( tr.id_contract = c.id AND tr.to_arc = 0 )
		LEFT JOIN {$db_name_sod}.tech_limit_cards tlc ON ( tlc.id_request = tr.id )
		WHERE tlc.id = {$nIDLimitCard}
		LIMIT 1
		";

        return $this->selectOnce($sQuery);
    }

    public function getCreateSalaryByObjID($nIDObj) {
        global $db_name_sod, $db_name_personnel;

        $sQuery = "
				SELECT

				";

        $a = new DBContracts();
        //            $a->getReport()
    }

    public function getCalculatedTimeByLimitCards($nIDObect) {
        global $db_name_sod, $db_name_personnel;

        if (empty($nIDObect) || !is_numeric($nIDObect)) {
            return array();
        }

        /*06.07.2015
          movement_time - времето изразходвано за придвижване към и от обекта (в часове), също умножено по броя техници - разстоянието до обекта при скорост 30 км/ч
          добавено pers.pc - брой техници;
          19.05.2015
          добавено да се изключат ремонтите L1-310 */
        $sQuery = "
                SELECT
                    pers.id_lk,
                    DATE_FORMAT(pers.real_end, '%Y%m') AS tlc_YM,
                    obj.id AS tech_object,
                    SUM( DISTINCT pers.sc) AS tech_times,
                    ( (obj.remoteness / 1000) / 30 * ( COALESCE(avgs.avg_tech_support_factor, 1) ) * pers.pc * 2 ) AS movement_sum,
                    IF( trq.id_tech_timing != 3, (SUM( DISTINCT pers.sc) * ( COALESCE(avgs.avg_tech_support_factor, 1)/60 ) * pers.pc), 0 ) AS total_sum
                FROM {$db_name_sod}.objects obj
                        /* Заради tech_office необходим за средните коефициенти за техниците */
                JOIN {$db_name_sod}.tech_requests trq  ON obj.id = trq.id_object AND trq.type != 'fictive' AND trq.to_arc = 0
                JOIN (SELECT tlc.id AS id_lk, tlc.real_end, COUNT(lcp.id_person) AS pc, (TIMESTAMPDIFF(MINUTE, tlc.real_start, tlc.real_end)) AS sc, tlc.`status`
                        FROM {$db_name_sod}.tech_limit_cards tlc
                        JOIN {$db_name_sod}.limit_card_persons lcp ON lcp.id_limit_card = tlc.id
                        WHERE tlc.to_arc = 0 AND tlc.`status` = 'closed'
                        GROUP BY lcp.id_limit_card ) AS pers ON pers.id_lk = trq.id_limit_card
                LEFT JOIN
                     (SELECT p.id_office, ROUND(AVG(p.tech_support_factor),2) AS avg_tech_support_factor
                      FROM {$db_name_personnel}.personnel p
                      WHERE p.status = 'active'
                        AND p.to_arc = 0
                        AND p.id_position_nc = 74
                      GROUP BY p.id_office) AS avgs ON avgs.id_office = obj.id_tech_office
                WHERE obj.id = {$nIDObect}
                GROUP BY pers.id_lk
        ";

        return $this->select($sQuery);
    }

    public function getClosedLimitCardsByObject($nIDObect, $bGroup = 1) {
        global $db_name_sod, $db_name_personnel;

        if (empty($nIDObect) || !is_numeric($nIDObect)) {
            return array();
        }

        if ($bGroup) {
            $sQuery = "
			SELECT
			tlc.id,
			tlc.type,
			tlc.id_tech_timing,
			SUM(s.total_sum) as total_sum,
			s.is_earning,
			DATE_FORMAT(tlc.real_end, '%Y%m') as tlc_YM
			FROM {$db_name_personnel}.salary s
			LEFT JOIN {$db_name_sod}.tech_limit_cards tlc ON (tlc.id = s.id_limit_card)
			WHERE s.to_arc = 0
			AND s.id_object = {$nIDObect}
			AND s.id_limit_card != 0
			AND tlc.`status` = 'closed'
			AND tlc.to_arc = 0
			GROUP BY tlc.id
			";
        } else {
            $sQuery = "
			SELECT
			tlc.id,
			tlc.type,
			tlc.id_tech_timing,
			s.total_sum,
			s.is_earning,
			DATE_FORMAT(tlc.real_end, '%Y%m') as tlc_YM
			FROM {$db_name_personnel}.salary s
			LEFT JOIN {$db_name_sod}.tech_limit_cards tlc ON (tlc.id = s.id_limit_card)
			WHERE s.to_arc = 0
			AND s.id_object = {$nIDObect}
			AND s.id_limit_card != 0
			AND tlc.`status` = 'closed'
			AND tlc.to_arc = 0
			";
        }

        return $this->select($sQuery);

        /*
          $sQuery = "
          SELECT
          tlc.id,
          tlc.type,
          s.total_sum,
          s.is_earning,
          DATE_FORMAT(tlc.real_end, '%Y%m') as tlc_YM
          FROM {$db_name_sod}.tech_limit_cards tlc
          LEFT JOIN {$db_name_sod}.limit_card_persons lcp ON (lcp.id_limit_card = tlc.id)
          LEFT JOIN {$db_name_personnel}.salary s ON ( s.id_object = tlc.id_object AND s.code = '+ТЕХН' AND lcp.id_person = s.id_person AND DATE_FORMAT(s.created_time, '%Y-%m-%d %H:%i') = DATE_FORMAT(tlc.real_end, '%Y-%m-%d %H:%i') AND s.to_arc = 0 )
          WHERE tlc.id_object = {$nIDObect}
          AND tlc.to_arc = 0
          AND tlc.status = 'closed'
          ";
         */
    }

    public function getReportHoldCard(DBResponse $oResponse) {
        global $db_name_sod, $db_name_personnel , $db_name_auto_trans;


        $wh = '';
        if($_SESSION['userdata']['access_right_regions'] != 1 ) {
            $off = implode(",", $_SESSION['userdata']['access_right_regions']);
            $wh = " AND o.id_tech_office IN({$off}) \n";
        }

        $sQuery = "
            SELECT
            SQL_CALC_FOUND_ROWS
            CONCAT_WS( '@' , tlc.id, o.id , tlc.id) AS id,
            tlc.id AS tlc_id,
            tlc.planned_start AS tlc_start,
            tlc.annonce_time AS tlc_annconce_time,
            CONCAT( '[' , o.num , '] ' , o.name) AS object_name,
            CONCAT_WS(' ', p.fname , p.mname , p.lname ) AS person_name,
            tlc.real_start,
            tr.created_time request_time,
            tlc.receive_time,
            t_reg.start_time
            FROM {$db_name_sod}.tech_limit_cards tlc
            LEFT JOIN {$db_name_sod}.tech_requests tr ON tr.id_limit_card = tlc.id
            LEFT JOIN {$db_name_sod}.limit_card_persons lcp ON lcp.id_limit_card = tlc.id
            LEFT JOIN {$db_name_personnel}.personnel p ON p.id = lcp.id_person
            LEFT JOIN {$db_name_sod}.objects o ON o.id = tlc.id_object
            LEFT JOIN {$db_name_auto_trans}.auto a ON a.id_tech_limit_card = tlc.id
            LEFT JOIN {$db_name_sod}.tech_register t_reg ON t_reg.id = a.tech_register
            WHERE  tlc.to_arc =0
            AND tlc.id_tech_timing = 4
            AND tlc.planned_start >= CURDATE()
            AND tlc.real_start = '0000-00-00 00:00:00'
            AND tlc.status = 'active'
            {$wh}
            AND
            (
              tlc.receive_time != '0000-00-00 00:00:00'
              OR ( ( UNIX_TIMESTAMP(NOW())) - UNIX_TIMESTAMP(tlc.receive_time) ) > 30*60
            )
            GROUP BY tlc.id
        ";

//        APILog::Log('22313131', $sQuery);

        $this->getResult($sQuery, 'tlc_id', DBAPI_SORT_DESC, $oResponse);

        $oResponse->setField('object_name', 'Обект', 'Сортирай по Обект', NULL, 'openObject');
        $oResponse->setField('tlc_id', 'Лимитна карта', 'Сортирай по Лимитна карта', NULL , 'editLimitCard');
        $oResponse->setField('person_name', 'Планиран на', 'Сортирай по Планиран на');
        $oResponse->setField('request_time', 'Време на заявка', 'Сортирай по Време на заявка', NULL, NULL, NULL, array('DATA_FORMAT' => DF_DATETIME));
        $oResponse->setField('tlc_annconce_time', 'Време на анонс', 'Сортирай по Време анонс техник',  NULL, NULL, NULL, array('DATA_FORMAT' => DF_DATETIME));
        $oResponse->setField('receive_time', 'Време на приемане', 'Сортирай по Време приемане',  NULL, NULL, NULL, array('DATA_FORMAT' => DF_DATETIME));
        $oResponse->setField('tlc_start', 'Планиран старт', 'Сортирай по Планиран старт', NULL, NULL, NULL, array('DATA_FORMAT' => DF_DATETIME) );
        $oResponse->setField('start_time', 'Време на тръгване', 'Сортирай по Време на тръгване',  NULL, NULL, NULL, array('DATA_FORMAT' => DF_DATETIME));

        $oResponse->printResponse();
    }

    /*
     * Връща true ако има планирана авария която е неотработена
     */
    public function havePlanAccidentNotRealStart($IDsPersons) {

        if(empty($IDsPersons)) {
            return false;
        }

        $sQuery = "
                 SELECT
                    tlc.id as tlc_id,
                    tlc.planned_start as tlc_start,
                    o.num as object_num,
                    o.id as object_id,
                    o.name as object_name
                FROM tech_limit_cards tlc
                JOIN limit_card_persons lcp ON lcp.id_limit_card = tlc.id
                LEFT JOIN objects o ON o.id = tlc.id_object
                WHERE 1
                AND tlc.to_arc = 0
                AND lcp.id_person IN ( {$IDsPersons} )
                AND tlc.id_tech_timing = 4
                AND tlc.planned_start >= CURDATE()
                AND tlc.real_start = '0000-00-00 00:00:00'
                AND tlc.status = 'active'
        ";

        $aRes = $this->selectOnce($sQuery);

        if(empty($aRes)) {
            return false;
        }

        return true;

    }

    public function checkTimeToClose($nTechLimitCard) {
        $oDBLimitCardPersons = new DBLimitCardPersons();
        $oDBTechTiming = new DBTechTiming();

        $aResponse = array();

        if(empty($nTechLimitCard)) {
            throw new Exception('Грешка при определяне на номер на лимитна карта!');
        }

        $nCntPerson = $oDBLimitCardPersons->getCntPersonByIDLC($nTechLimitCard);

        $aLimitCard = $this->getRecord($nTechLimitCard);

        $aTechTiming = $oDBTechTiming->getRecord($aLimitCard['id_tech_timing']);

        $nMinutesToEnd = (int)($aTechTiming['minute'] / $nCntPerson);

        $nMinTimeEnd = strtotime($aLimitCard['real_start'].' + '.$nMinutesToEnd.' minutes');

        if( $nMinTimeEnd > time() ) {
            $aResponse['status'] = false;
            $aResponse['msg'] = date("d.m.Y H:i:s" , $nMinTimeEnd);
            return $aResponse;
        }

        $aResponse['status'] = true;
        $aResponse['msg'] = '';
        return $aResponse;
    }


    /**
     * Изежда лимитните карти на техник за избран период
     *
     * @param int $nIDPerson
     * @param null|int $fromDate - От коя дата - timestamp
     * @param null|int $toDate - До коя дата - timestamp
     *
     * @return array
     */
    public function getLimitCardsForPerson( $nIDPerson, $fromDate = null, $toDate = null ) {
        global $db_name_sod;

        if ( empty( $nIDPerson ) ) {
            return array();
        }

        $fromDate = ( is_null( $fromDate ) ) ? date( 'Y-m-d 00:00:00' ) : date( 'Y-m-d H:i:s', $fromDate );
        $toDate =   ( is_null( $toDate ) )   ? date( 'Y-m-d 23:59:59' ) : date( 'Y-m-d H:i:s', $toDate );

        $sQuery = "
			SELECT
				tlc.id AS __key,
				tlc.id,
				tlc.id_request,
				DATE_FORMAT(tlc.real_start, '%d.%m.%Y %H:%i') AS real_start,
				DATE_FORMAT(tlc.real_end, '%d.%m.%Y %H:%i') AS real_end
			FROM {$db_name_sod}.tech_limit_cards tlc
			JOIN {$db_name_sod}.limit_card_persons lcp ON lcp.id_limit_card = tlc.id

			WHERE lcp.id_person = {$nIDPerson}
			AND tlc.to_arc = 0
			AND DATE(tlc.real_start) != '0000-00-00'
			AND
			(
				(
					# Дали старта попада в старта на някоя заявка
					( '{$fromDate}' >= tlc.real_start AND '{$fromDate}' < tlc.real_end)
					OR
					# Дали края попада в някоя заявка
					( '{$toDate}' > tlc.real_start AND '{$toDate}' <= tlc.real_end)
				)
				OR
				(
					# Дали имаме старт на заявка в този интервал
					(tlc.real_start >= '{$fromDate}' AND tlc.real_start < '{$toDate}' )
					OR
					# Дали имаме край на заявка в този интервал
				  	(tlc.real_end > '{$fromDate}' AND tlc.real_end <= '{$toDate}' )
				)
			)
		";

        return $this->selectAssoc( $sQuery );
    }


    public function getOnDutyLimitCards($nIDPerson,$before = true, $nDate = false) {

        if(!$nDate) {
            $nDate = date('Y-m-d');
        } else {
            $nDate = date('Y-m-d',strtotime($nDate));
        }

        $sQuery = "

            SELECT
              tlc.id,
              status,
              id_object,
              id_request,
              type,
              id_tech_timing,
              note,
              distance,
              arrange_count,
              remoteness_tax_in,
              planned_start,
              planned_end,
              real_start,
              real_end,
              created_time,
              annonce_time,
              receive_time,
              created_user,
              updated_time,
              updated_user,
              to_arc,
              lcp.id,
              id_limit_card,
              id_person,
              percent
            FROM tech_limit_cards tlc
            JOIN limit_card_persons lcp ON lcp.id_limit_card = tlc.id
            WHERE lcp.id_person = {$nIDPerson}

        ";

        if($before) {
            $sQuery .= " AND (tlc.planned_start >= '{$nDate} 00:00:01' AND tlc.planned_start < '{$nDate} 08:00:00') ";
        } else {
            $sQuery .= " AND (tlc.planned_start >= '{$nDate} 18:00:01' AND tlc.planned_start <= '{$nDate} 23:59:59') ";

        }

        $sQuery .= " ORDER BY tlc.planned_start ASC";
        return $this->selectAssoc($sQuery);

    }

    public function getDataForPDF($nIDTechLimitCard) {

        $sQuery = "
            SELECT
              o.name AS object_name,
              o.address AS object_address,
              c.name AS object_city,
              o.num AS object_num,
              o.product_num AS product_num,
              o.id AS id_object,
              o.id_objtype,
              tt.description AS tech_timing_name,
              tt.id AS tech_timing_id,
              treason.name AS tech_reason_name,
              treason.id AS id_tech_reason_id,
              tlc.object_situation,
              obj_t.name AS object_type,
              tr.note AS tech_request_note,
              CASE tlc.id_tech_timing
                WHEN 3 THEN 'Р'
                WHEN 4 THEN 'А'
                WHEN 5 THEN 'ТО'
                ELSE ''
              END AS char_num,
              IF(tlc.closed_people = 1 , 'Да' , 'Не') AS closed_people,
              tlc.note AS tech_limit_card_note,
              DATE_FORMAT(tlc.real_start,'%d.%m.%Y') AS date_limit_card,
              DATE_FORMAT(tlc.real_start,'%H:%i') AS hour_start_limit_card,
              DATE_FORMAT(tlc.real_end,'%H:%i') AS hour_end_limit_card,
              DATE_FORMAT(tr.request_time,'%d.%m.%Y %H:%i') AS client_request_time,
              TIMESTAMPDIFF(second, reg.start_time , reg.end_time)  as duration_travel,
              TIMESTAMPDIFF(second, tr.request_time, tlc.real_start)  as reaction_time
            FROM
            tech_limit_cards tlc
            LEFT JOIN tech_requests tr
            ON tlc.id = tr.id_limit_card
            LEFT JOIN tech_reason treason
            ON treason.id = tr.id_tech_reason
            LEFT JOIN tech_timing tt
            ON tt.id = tr.id_tech_timing
            LEFT JOIN objects o
            ON o.id = tr.id_object
            LEFT JOIN object_types obj_t
            ON o.id_objtype = obj_t.id
            LEFT JOIN tech_register reg
            ON reg.id_tech_limit_card = tlc.id
            LEFT JOIN cities c
            ON c.id = o.address_city
            WHERE 1
            AND tlc.id = {$nIDTechLimitCard}
        ";

        return $this->selectOnce($sQuery);
    }

    public function protocolPDF($nIDTechLimitCard, $nProtocolNumber) {

        $oDBPPPElements         = new DBPPPElements();
        $oDBLimitCardPersons    = new DBLimitCardPersons();
        $oDBClients             = new DBClients();
        $oDBTechRequests        = new DBTechRequests();

        if(empty($nIDTechLimitCard)) {
            return '<page style="font-family: freeserif"><h1 style="text-align: center">НЯМА НАМЕРЕНА ЛИМИТНА КАРТА</h1></page>';
        }

        include_once '../include/liftkom_signs.php';

        $aLimitCard = $this->getDataForPDF($nIDTechLimitCard);
        $aPPPElements = $oDBPPPElements->getElementsByIDLimitCard($nIDTechLimitCard);
        $aLimitPersons = $oDBLimitCardPersons->getPersonNamesByLC($nIDTechLimitCard);
//        $aClient = $oDBClients->getClientByObject($aLimitCard['id_object']);
        $aRequestChildOnLimitCard = $oDBTechRequests->getRequestByParentIdLimitCard($nIDTechLimitCard);
        $aClient = $this->getSignPerson($nIDTechLimitCard);

        $sNomenclatures = '';
        if(!empty($aPPPElements)) {
            $aTmp = array();
            foreach($aPPPElements as $aNom) {
                $aTmp[] = $aNom['name'].' '.$aNom['count'].' '.$aNom['unit'];
            }
            $sNomenclatures = implode(',',$aTmp);
        } else {
            $sNomenclatures = "няма вложени резервни части";
        }

        //компоненти за смяна
        $sTechToReplace = ( !empty($aRequestChildOnLimitCard) && !empty($aRequestChildOnLimitCard['note']) )? $aRequestChildOnLimitCard['note'] : ' няма компоненти за смяна ';

        $sDurationTravel = seconds2text($aLimitCard['duration_travel']);
        $sReactionTime = seconds2text($aLimitCard['reaction_time']);

        $html = '<page style="font-family: freeserif;font-size: 14;">';
        $html.= '
                <style type="text/css">
                    .table_nom {width: 100%; margin: 0 auto;}
                    .dot_underline { border-bottom: 1px black dotted; }
                    .brd { border: 1px black; }
                    .wd10 {width: 10%; }
                    .wd15p {width: 105px; }
                    .wd20 {width: 20%; }
                    .wd25 {width: 25%; }
                    .wd30 {width: 30%; }
                    .wd33 {width: 33%; }
                    .wd40 {width: 40%; }
                    .wd50 {width: 50%; }
                    .wd60 {width: 60%; }
                    .wd70 {width: 70%; }
                    .wd80 {width: 80%; }
                    .wd85 {width: 85%; }
                    .wd90 {width: 90%; }
                    .wd100 {width: 100%; }
                    table.table_row {width: 100%; margin: 0 auto;border-collapse: collapse;}
                    table.table_info {width: 100%; margin: 0 auto;}
                    table.payment{padding-top: 20px;}
                    table.person_update{padding-top: 40px;}
                    div.table_content table{height: 25%;}
                    table.table_nom td, th{ border: 1px solid black;}
                    .text_r{ text-align: right;}
                    .text_c{ text-align: center;}
                </style>';

        $char_num = ($aLimitCard['id_tech_reason_id'] == 3)? 'ФП' : $aLimitCard['char_num'];

        $sPathTitle = '../images/title.png';

        $html.='<div><img src="'.$sPathTitle.'" style="width:100%; margin-top: -20px; margin-bottom: 30px;"></div>';
        $html.="<div style='margin: 20px 0px 30px 0px;'><h3 class='text_c'>ПРОТОКОЛ ЗА ".mb_strtoupper($aLimitCard['tech_timing_name'],'UTF-8')." № ".$char_num.' '.zero_padding($nProtocolNumber,7)."</h3></div>";


//        $aLimitCard['object_type']
        $html.="<table class='wd100'>
                    <tr>
                        <td >ОБЕКТ/Адрес: </td>
                        <td class='dot_underline text_c' style='width: 658px;'>{$aLimitCard['object_type']} на адрес: {$aLimitCard['object_city']}, {$aLimitCard['object_address']} с производствен №: {$aLimitCard['product_num']}</td>
                    </tr>
                </table>";

        //ако е ремонт няма път!!!

        if($aLimitCard['tech_timing_id'] == 3) {
            $html.="<table class='table_row'>
                    <tr>
                        <td>Дата: </td>
                        <td class='wd25 dot_underline text_c'>{$aLimitCard['date_limit_card']}</td>
                        <td>Час на пристигане: </td>
                        <td class='wd20 dot_underline text_c'>{$aLimitCard['hour_start_limit_card']}</td>
                        <td>Час на пускане: </td>
                        <td class='wd25 dot_underline text_c' style='width: 170px;'>{$aLimitCard['hour_end_limit_card']}</td>
                    </tr>
                </table>";
        } else {
            $html.="<table class='table_row'>
                    <tr>
                        <td>Дата: </td>
                        <td class='wd10 dot_underline text_c'>{$aLimitCard['date_limit_card']}</td>
                        <td>Час на пристигане: </td>
                        <td class='wd20 dot_underline text_c'>{$aLimitCard['hour_start_limit_card']}</td>
                        <td>Час на пускане: </td>
                        <td class='wd20 dot_underline text_c'>{$aLimitCard['hour_end_limit_card']}</td>
                        <td>Път: </td>
                        <td class='dot_underline text_c' style='width: 105px;'>{$sDurationTravel}</td>
                    </tr>
                </table>";
        }



        $html.="<table class='table_row'>
                    <tr>
                        <td class='wd40'>Дата/час на получаване на обаждането от клиент*: </td>
                        <td class='wd60 dot_underline text_c'>{$aLimitCard['client_request_time']}</td>
                    </tr>
                </table>";


        $html.="<table class='table_row'>
                    <tr>
                        <td>Време за реакция*: </td>
                        <td class='dot_underline text_c' style='width: 300px;'>{$sReactionTime}</td>
                        <td class='text_r'>Заседнали хора:</td>
                        <td class='dot_underline text_c' style='width: 235px;'>{$aLimitCard['closed_people']}</td>
                    </tr>
                </table>";

        $html.="<p>
                    Сервизната поддържка от КАЛАМАРИС ГРУП  ЕООД</p>";
//
        $html.= "<div class='table_content' style='height: 50%;'>";

        $html.="
                <br>
                <div style='height: 25%;'>
                    <table class='table_row'>
                        <tr>
                            <td class='wd50' style='font-weight: bold'>Ситуация на обекта: </td>
                        </tr>
                        <tr>
                            <td class='wd100'>{$aLimitCard['object_situation']}</td>
                        </tr>
                    </table>
                </div>";

        $html.="
                <br>
                <div style='height: 25%;'>
                    <table class='table_row'>
                        <tr>
                            <td class='wd50' style='font-weight: bold'>Предпиети действия: </td>
                        </tr>
                        <tr>
                            <td class='wd100'>{$aLimitCard['tech_limit_card_note']}</td>
                        </tr>
                    </table>
                </div>";

        $html.="
                <br>
                <div style='height: 25%;'>
                    <table class='table_row'>
                        <tr>
                            <td class='wd50' style='font-weight: bold'>Вложени резервни части: </td>
                        </tr>
                        <tr>
                            <td class='wd100'>{$sNomenclatures}</td>
                        </tr>
                    </table>
                </div>";

        $html.="
                <br>
                <div style='height: 25%;'>
                    <table class='table_row'>
                        <tr>
                            <td class='wd50' style='font-weight: bold'>Компоненти, които следва да бъдят подменени: </td>
                        </tr>
                        <tr>
                            <td class='wd100'>{$sTechToReplace}</td>
                        </tr>
                    </table>
                </div>";

        $html.= "</div>";

        $sPerson1 = isset($aLimitPersons[0])?  $aLimitPersons[0]['person_name'] : "";
        $sPerson2 = isset($aLimitPersons[1])?  $aLimitPersons[1]['person_name'] : "";

        if($img_path = LiftkomSigns::getSignImagePath($this->getSignFile($nIDTechLimitCard))) {
            $html.= '<div id="sign" style="width: 100px; height: 80px; z-index: 1000; position: absolute; bottom: 0px; left: 550px; margin-bottom: 0px;"><img src="'.$img_path.'" style="width: 100px; height: 80px;"></div>';
        }

        //служители подписи
        $html.=
            '<page_footer style="font-family: freeserif;">
                <table style="width: 100%;">
                    <tr>
                        <td colspan="2" class="wd50">Техници:</td>
                        <td colspan="2" class="wd50">Клиент:</td>
                    </tr>
                    <tr>
                        <td class="text_r">1.</td>
                        <td class="dot_underline wd40">'.$sPerson1.'</td>
                        <td class="text_r">1.</td>
                        <td class="dot_underline wd40">'.$aClient['name'].'</td>
                    </tr>
                    <tr>
                        <td class="text_r">2.</td>
                        <td class="dot_underline">'.$sPerson2.'</td>
                        <td class="text_r">2.</td>
                        <td class="dot_underline"></td>
                    </tr>
                </table>
        </page_footer>';

        $html.= '</page>';

        //замествам всяко главно българско Р а P защото няма символ за главно Р
        return str_replace('Р','P',$html);
    }

    public function protocolPDFPlan($nIDTechLimitCard, $nProtocolNumber ) {

        $oDBLimitCardOperations = new DBLimitCardOperations();
        $oDBLimitCardPersons    = new DBLimitCardPersons();
        $oDBClients             = new DBClients();
        $oDBPPPElements         = new DBPPPElements();
        $oDBTechRequest         = new DBTechRequests();

        $sPathTitle = '../images/title.png';
        $sConfirm = '../images/confirm.gif';

        include_once '../include/liftkom_signs.php';

        $aTechReason = array(3 , 4); // ako e ТО ИЛИ ФП да извежда от протокола информация
        $aObjectTypes = array( 2 ,3 ); // ЗА КОЙ ТИПОВЕ ОБЕКТИ ДА ИЗВЕЖДА ВИЗИЯТА С ПОДОПЕРАЦИИ
        $aPPPElements = array();

        $html = '<page style="font-family: freeserif;" backtop="5mm" backleft="5mm" backright="5mm">';

        $html.= '
                <style type="text/css">
                    .table_nom {width: 100%; margin: 0 auto;}
                    .dot_underline { border-bottom: 1px black dotted; }
                    .brd { border: 1px black; }
                    .wd5 {width: 5%; }
                    .wd4 {width: 4%; }
                    .wd10 {width: 10%; }
                    .wd15p {width: 105px; }
                    .wd20 {width: 20%; }
                    .wd25 {width: 25%; }
                    .wd30 {width: 30%; }
                    .wd33 {width: 33%; }
                    .wd40 {width: 40%; }
                    .wd50 {width: 50%; }
                    .wd60 {width: 60%; }
                    .wd70 {width: 70%; }
                    .wd80 {width: 80%; }
                    .wd85 {width: 85%; }
                    .wd86 {width: 86%; }
                    .wd90 {width: 90%; }
                    .wd100 {width: 100%; }
                    table.table_row {width: 100%; margin: 0 auto;}
                    table.table_info {width: 100%; margin: 0 auto;border-collapse: collapse;}
                    table.table_info td,th{border: 1px solid;}
                    table.payment{padding-top: 20px;}
                    table.person_update{padding-top: 40px;}
                    table.table_nom td, th{ border: 1px solid black;}
                    .text_r{ text-align: right;}
                    .text_c{ text-align: center;}
                </style>';

        $temp = $oDBLimitCardOperations->getLimitCardOperations($nIDTechLimitCard);

        $aLimitCard = $this->getDataForPDF($nIDTechLimitCard);
        $aLimitPersons = $oDBLimitCardPersons->getPersonNamesByLC($nIDTechLimitCard);
//        $aClient = $oDBClients->getClientByObject($aLimitCard['id_object']);
        $aClient = $this->getSignPerson($nIDTechLimitCard);

//        $sDurationTravel = seconds2text($aLimitCard['duration_travel']);

        if( in_array( $aLimitCard['id_objtype'] , $aObjectTypes) ) {
            $aElevatorOperation  = array();
            foreach($temp as $aVal) {
                if($aVal['id_parent'] == 0 ) {
                    $aElevatorOperation[$aVal['id_operation']] = $aVal;
                } else {
                    $aElevatorOperation[$aVal['id_parent']]['children'][] = $aVal;
                }
            }
        }

//        $char_num = ($aLimitCard['id_tech_reason_id'] == 3)? 'ФП' : $aLimitCard['char_num'];

        switch($aLimitCard['id_tech_reason_id']) {
            case 3: $char_num = 'ФП';
                break;

            case 6 : $char_num = 'И';
                break;

            default:
                $char_num = $aLimitCard['char_num'];
        }

        if( in_array( $aLimitCard['id_tech_reason_id'] , $aTechReason) ) {
            $aPPPElements = $oDBPPPElements->getElementsByIDLimitCard($nIDTechLimitCard);
            $aNextRequest = $oDBTechRequest->getRequestByParentIdLimitCard($nIDTechLimitCard);
        }


        $html.='<div><img src="'.$sPathTitle.'" style="width:100%; margin-top: -20px;"></div>';
        $html.="<div style='margin: 0px 0px 10px 0px;'><h3 class='text_c'>ПРОТОКОЛ ЗА ".mb_strtoupper($aLimitCard['tech_reason_name'],'UTF-8')." № ".$char_num.' '.zero_padding($nProtocolNumber,7)."</h3></div>";

//        $aLimitCard['object_type']
        $html.="<table class='wd100'>
                    <tr>
                        <td >ОБЕКТ/Адрес: </td>
                        <td class='dot_underline text_c' style='width: 635px;'>{$aLimitCard['object_type']} на адрес: {$aLimitCard['object_city']}, {$aLimitCard['object_address']} с производствен №: {$aLimitCard['product_num']}</td>
                    </tr>
                </table>";

        $html.="<table class='table_row'>
                    <tr>
                        <td>Дата: </td>
                        <td class='wd20 dot_underline text_c'>{$aLimitCard['date_limit_card']}</td>
                        <td>Час на пристигане: </td>
                        <td class='wd25 dot_underline text_c'>{$aLimitCard['hour_start_limit_card']}</td>
                        <td>Час на пускане: </td>
                        <td class='wd20 dot_underline text_c' style='width: 170px;'>{$aLimitCard['hour_end_limit_card']}</td>
                    </tr>
                </table>";

        $html.="<p style='font-size: 13px;'>
                    Сервизната поддържка от КАЛАМАРИС ГРУП  ЕООД. Извършени са следните действия:
                </p>";

        if(!empty($temp)) {

            $html .="<table class='table_info' style='font-size: 12px;'>";
            $html .="<tr>";
            $html .="<th class='text_c'>№</th>";
            $html .="<th class='text_c'>Видове дейности</th>";
            $html .="<th class='text_c'>Отметка</th>";
            $html .="</tr>";

            $cnt= 0;

            //ако е ескалатор или пътека
            if( in_array( $aLimitCard['id_objtype'] , $aObjectTypes) )
            {
                foreach ($aElevatorOperation as $operation) {

                    $sImg = '';
                    if($operation['is_done']) {
                        $sImg = "<img src='".$sConfirm."' />";
                    }

                    ++$cnt;
                    $name = ( isset($operation['children']) && !empty($operation['children']) )?  $operation['name'].":"  : $operation['name'];
                    $html .= "<tr>";
                    $html .= "<td class='wd4 text_c'>{$cnt}</td>";
                    $html .= "<td class='wd86' style='font-weight: bold'>{$name}</td>";
                    $html .= "<td class='wd10 text_c'>{$sImg}</td>";
                    $html .= "</tr>";

                    if(isset($operation['children']) && !empty($operation['children'])) {
                        foreach ($operation['children'] as $aChild) {

                            $sImg = '';
                            if($aChild['is_done']) {
                                $sImg = "<img src='".$sConfirm."' />";
                            }

                            $name = $aChild['name'];
                            $html .= "<tr>";
                            $html .= "<td class='wd4 text_c'></td>";
                            $html .= "<td class='wd86' style='padding-left: 10px;'>{$name}</td>";
                            $html .= "<td class='wd10 text_c'>{$sImg}</td>";
                            $html .= "</tr>";
                        }
                    }
                }
            }
            else
            {
                foreach ($temp as $operation) {

                    $sImg = '';
                    if($operation['is_done']) {
                        $sImg = "<img src='".$sConfirm."' />";
                    }

                    ++$cnt;
                    $name = $operation['name'];
                    $html .= "<tr>";
                    $html .= "<td class='wd4 text_c'>{$cnt}</td>";
                    $html .= "<td class='wd86'>{$name}</td>";
                    $html .= "<td class='wd10 text_c'>{$sImg}</td>";
                    $html .= "</tr>";
                }
            }

            $html .="</table>";

            if(!empty($aPPPElements)) {

                $html .="<table class='table_info' style='margin-top: 10px; font-size: 12px'>";
                $html .="<tr>";
                $html .="<th colspan='2' class='text_c'>Допълнителни</th>";
                $html .="</tr>";

                foreach ($aPPPElements as $aVal) {
                    $html .= "<tr>";
                    $html .= "<td class='wd80'>{$aVal['name']}</td>";
                    $html .= "<td class='wd20 text_c'>{$aVal['count']} {$aVal['unit']}</td>";
                    $html .= "</tr>";
                }

                $html .="</table>";
            }

            if(!empty($aNextRequest)) {
                $html .= "<p><b>Компоненти, които следва да бъдат подменени:</b>&nbsp;";
                $html .= "{$aNextRequest['note']}";
                $html .= "</p>";
            }

        } else {
            $html .= '<h2>За избраната лимитна карта няма операции!</h2>';
        }


        $sPerson1 = isset($aLimitPersons[0])?  $aLimitPersons[0]['person_name'] : "";
        $sPerson2 = isset($aLimitPersons[1])?  $aLimitPersons[1]['person_name'] : "";
        //служители подписи

        if($img_path = LiftkomSigns::getSignImagePath($this->getSignFile($nIDTechLimitCard))) {
            $html.= '<div id="sign" style="width: 100px; height: 80px; z-index: 1000; position: absolute; bottom: 0px; left: 550px; margin-bottom: 0px;"><img src="'.$img_path.'" style="width: 100px; height: 80px;"></div>';
        }

        $html.=
            '<page_footer style="font-family: freeserif;">
                <table style="width: 100%;">
                    <tr>
                        <td colspan="2" class="wd50">Техници:</td>
                        <td colspan="2" class="wd50">Клиент:</td>
                    </tr>
                    <tr>
                        <td class="text_r">1.</td>
                        <td class="dot_underline wd40">'.$sPerson1.'</td>
                        <td class="text_r">1.</td>
                        <td class="dot_underline wd40">'.$aClient['name'].'</td>
                    </tr>
                    <tr>
                        <td class="text_r">2.</td>
                        <td class="dot_underline">'.$sPerson2.'</td>
                        <td class="text_r">2.</td>
                        <td class="dot_underline"></td>
                    </tr>
                </table>
        </page_footer>';

        $html .= '</page>';

        //замествам всяко главно българско Р а P защото няма символ за главно Р
        return str_replace('Р','P',$html);
    }


    public function getLastLimitCardByObject($nIDObject,$id_reason = null) {

        $sQuery = "
            SELECT
                tlc.id,
                tlc.`status`,
                tlc.id_object,
                tlc.id_request,
                tr.id_tech_reason,
                tlc.type,
                tlc.id_tech_timing,
                tlc.note,
                tlc.distance,
                tlc.arrange_count,
                tlc.remoteness_tax_in,
                tlc.planned_start,
                tlc.planned_end,
                tlc.real_start,
                tlc.real_end,
                tlc.created_time,
                tlc.annonce_time,
                tlc.receive_time,
                tlc.created_user,
                tlc.updated_time,
                tlc.updated_user,
                tlc.to_arc
            FROM
                tech_limit_cards tlc
            JOIN tech_requests tr ON tr.id = tlc.id_request
            WHERE
                tlc.id_object = {$nIDObject}
            AND tlc.`status` = 'closed'
            AND tlc.to_arc = 0


        ";

        if(!is_null($id_reason)) {
            $sQuery .= " AND tr.id_tech_reason = ".$id_reason." \n ";
        }

        $sQuery .= "
            ORDER BY
                id DESC
            LIMIT 1
        ";

        return $this->selectOnce($sQuery);

    }


    public function checkIsPlannedHourFree($nIDPersons,$dateTime) {

        if(is_array($nIDPersons)) {
            $nIDPersons = implode(',',$nIDPersons);
        }

        $sQuery = "
        SELECT
            tlc.id,
            tlc.planned_start,
            tlc.planned_end
        FROM tech_limit_cards tlc
        LEFT JOIN limit_card_persons lcp ON lcp.id_limit_card = tlc.id
        WHERE
        lcp.id_person IN ({$nIDPersons})
        AND tlc.planned_start = '{$dateTime}'
        AND tlc.to_arc = 0
        ";

        return $this->selectOnce($sQuery);
    }

    public function getArrangeByIDObjectAndDays($nIDObject , $nDayInterval = 10) {

        if(empty($nIDObject)) {
            return false;
        }

        $sQuery = "
            SELECT
            tlc.*,
            tr.id_contract,
            DATE_ADD(now(),INTERVAL -3 DAY)
            FROM tech_limit_cards tlc
            LEFT JOIN tech_requests tr
            ON tr.id = tlc.id_request
            WHERE 1
            AND tlc.to_arc = 0
            AND tlc.`status`= 'closed'
            AND tlc.id_tech_timing = 3
            AND tlc.id_object = {$nIDObject}
            AND tlc.real_end >= DATE_ADD(now(),INTERVAL -{$nDayInterval} DAY)
            ORDER BY real_end DESC
        ";

        return $this->select($sQuery);
    }

    public function setSignPerson($nIDLimitCard,$nIDObjectFace)
    {
        $aData['id'] = $nIDLimitCard;
        $aData['sign_person'] = $nIDObjectFace;

        $this->update($aData);

    }

    public function getSignPerson($nIDLimitCard)
    {
        $oDBFaces = new DBFaces();
        $lc = $this->getRecord($nIDLimitCard);
        return $oDBFaces->getFace($lc['sign_person']);
    }



    public function getTodayLCByIDClient($nIDClient,$nIDLimitCard = null)
    {
        $todayDate = date('Y-m-d');

        $sQuery = "
          SELECT
                tlc.*,
                o.name as object_name,
                o.num as object_num
            FROM tech_limit_cards tlc
            JOIN objects o ON o.id = tlc.id_object
            JOIN clients_objects co ON co.id_object = o.id and co.to_arc = 0
            WHERE
            co.id_client = {$nIDClient}
            AND tlc.planned_start >= '{$todayDate} 00:00:01'
        ";

        if(!is_null($nIDLimitCard)) {
            $sQuery .= " AND tlc.id != $nIDLimitCard ";
        }

        return $this->selectAssoc($sQuery);
    }

    public function getSignFile($nIDLimitCard) {

        $data = $this->getRecord($nIDLimitCard);
        return $data['sign_filename'];

    }

    public function setSignFile($nIDLimitCard,$signFile) {
        $aData['id'] = $nIDLimitCard;
        $aData['sign_filename'] = $signFile;
        $this->update($aData);
    }
}

?>

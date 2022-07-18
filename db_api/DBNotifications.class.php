<?php

class DBNotifications extends DBBase2 {

	public function __construct() {
		global $db_system;
		parent::__construct($db_system,'notifications');
	}

	public function addNotification($aData) {

		$this->update($aData);
	}

	public function getReport($oResponse) {

		global $db_name_sod;

		$nIDEvent = (int) Params::get('id_event',0);
		$sDateFrom = Params::get('sDateFrom','');
		$sDateTo = Params::get('sDateTo','');

		$sQuery = "
			SELECT SQL_CALC_FOUND_ROWS
				n.id,
				n.target,
				DATE_FORMAT(n.created_time, '%d-%m-%Y %H:%i:%s') AS created_time_,
				DATE_FORMAT(n.send_after, '%d-%m-%Y %H:%i:%s') AS send_after_,
				n.channel,
				CASE n.status
					WHEN 'wait' THEN 'изчакване'
					WHEN 'sending' THEN 'в процес на изпращане'
					WHEN 'sent' THEN 'изпратено'
					WHEN 'failed' THEN 'неуспешно изпращане'
					WHEN 'canceled' THEN 'отказано'
				END AS status_bg,
				CASE n.channel
					WHEN 'mail' THEN 'email'
					WHEN 'sms' THEN 'SMS'
					WHEN 'system' THEN 'система'
					WHEN 'tel' THEN 'телефон'
				END AS channel_bg,
				ne.description AS event_description,
				o.name AS object_name
			FROM notifications n
			LEFT JOIN notifications_events ne ON ne.id = n.id_event
			LEFT JOIN {$db_name_sod}.objects o ON o.id = n.id_object
			WHERE 1
		";

		if(!empty($nIDEvent)) {
			$sQuery .= " AND n.id_event = ".$nIDEvent." ";
		}

		if(!empty($sDateFrom)) {
			$sDateFrom = jsDateToMySQLDate($sDateFrom);
			$sDateFrom .= ' 00:00:00';
			$sQuery .= " AND n.created_time >= '{$sDateFrom}'";
		}
		if(!empty($sDateTo)) {
			$sDateTo = jsDateToMySQLDate($sDateTo);
			$sDateTo .= ' 23:59:59';
			$sQuery .= " AND n.created_time <= '{$sDateTo}'";
		}

		$this->getResult($sQuery,'id', DBAPI_SORT_DESC, $oResponse);


		$oResponse->setField('event_description','Събитие');
		$oResponse->setField('created_time_','Създаден','', NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATETIME ) );
		$oResponse->setField('send_after_','Изпращане след','', NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATETIME ) );
		$oResponse->setField('channel_bg','Канал');
		$oResponse->setField('status_bg','Статус');
		$oResponse->setField('target','Получател');
		$oResponse->setField('object_name','За обект');
	}


	public function getReport2(DBResponse $oResponse) {

		global $db_name_sod;

		$nNotificationsEvents = (int) Params::get('nNotificationsEvents',0);
		$sDateFrom = Params::get('sDateFrom','');
		$sDateTo = Params::get('sDateTo','');
		$sClientName = Params::get('sClientName','');
		$sPhone = Params::get('sPhone','');
		$nIDObject = Params::get('nIDObject',0);
		$sStatus = Params::get('sStatus','');
		$sChannel = Params::get('sChannel','');

		$sQuery ="
            SELECT
            SQL_CALC_FOUND_ROWS
            CONCAT_WS( '@' , n.id, IF(cl.id IS NOT NULL  , cl.id , 0 ) , IF(obj.id  IS NOT NULL , obj.id , 0 ) ) AS id,
            n.target,
            DATE_FORMAT(n.created_time, '%d-%m-%Y %H:%i:%s') AS created_time_,
            DATE_FORMAT(n.send_after, '%d-%m-%Y %H:%i:%s') AS send_after_,
            ne.description AS event_description,
            ne.code,
            ne.description,
            n.send_after,
            CASE n.status
                WHEN 'wait' THEN 'изчакване'
                WHEN 'sending' THEN 'в процес на изпращане'
                WHEN 'sent' THEN 'изпратено'
                WHEN 'failed' THEN 'неуспешно изпращане'
                WHEN 'canceled' THEN 'отказано'
            END AS status_bg,
            CASE n.channel
                WHEN 'mail' THEN 'email'
                WHEN 'sms' THEN 'SMS'
                WHEN 'system' THEN 'система'
                WHEN 'tel' THEN 'телефон'
            END AS channel_bg,
            CONCAT( '[' ,obj.num , ']' , ' ' , obj.name) AS object_name,
            cl.name AS client_name
            FROM
            notifications n
            LEFT JOIN
            notifications_events ne
            ON n.id_event = ne.id
            LEFT JOIN
            {$db_name_sod}.clients cl
            ON cl.id = n.id_client
            LEFT JOIN
            {$db_name_sod}.objects obj
            ON obj.id = n.id_object
            WHERE 1
        ";

		if(!empty($sDateFrom)) {
			$sDateFrom = jsDateToMySQLDate($sDateFrom);
			$sDateFrom .= ' 00:00:00';
			$sQuery .= " AND n.created_time >= '{$sDateFrom}' ";
		}
		if(!empty($sDateTo)) {
			$sDateTo = jsDateToMySQLDate($sDateTo);
			$sDateTo .= ' 23:59:59';
			$sQuery .= " AND n.created_time <= '{$sDateTo}' ";
		}

		if(!empty($nNotificationsEvents)) {
			$sQuery .= " AND n.id_event = {$nNotificationsEvents} ";
		}

		if(!empty($sClientName)) {
			$sQuery.= " AND cl.name LIKE '%{$sClientName}%' ";
		}

		if(!empty($sPhone)) {
			$sQuery.= " AND n.target LIKE '%{$sPhone}%' ";
		}

		if(!empty($nIDObject)) {
			$sQuery.= " AND n.id_object = {$nIDObject} ";
		}

		if(!empty($sStatus)) {
			$sQuery.= " AND n.status = '{$sStatus}' ";
		}

		if(!empty($sChannel)) {
			$sQuery.= " AND n.channel = '{$sChannel}' ";
		}

		$this->getResult($sQuery,'n.id', DBAPI_SORT_DESC, $oResponse);

		$oResponse->setField('event_description','Събитие');
		$oResponse->setField('created_time_','Създаден','', NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATETIME ) );
		$oResponse->setField('send_after_','Изпращане след','', NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATETIME ) );
		$oResponse->setField('channel_bg','Канал');
		$oResponse->setField('status_bg','Статус');
		$oResponse->setField('target','Получател');
		$oResponse->setField('client_name','Клиент', 'Сортирай по Клиент' , NULL , 'openClient');
		$oResponse->setField('object_name','Обект', 'Сортирай по Обект' , NULL , 'openObject');

		$oResponse->printResponse();
	}


	function getNotificationRow($nID) {

		$aData = $this->getRecord($nID);
		if(!empty($aData)) {
			$aData['additional_params'] = json_decode($aData['additional_params']);
		}

		return $aData;
	}


	public function existNotPaidNotification($id_event,$sYearMonth,$channel,$target,$id_object) {

		$sQuery = "
			SELECT
				*
			FROM notifications
			WHERE id_event = {$id_event}
				AND help_key = {$sYearMonth}
				AND channel = '{$channel}'
				AND target = '{$target}'
				AND id_object = {$id_object}
		";

		$aData = $this->select($sQuery);

		if(!empty($aData)) {
			return true;
		} else {
			return false;
		}
	}
	// probva da izprati nanovo neuspeshnite eventi pri puskane na robota
	function getUnsentNotifications($for_date,$channel = '') {

		$sFromTime = date('Y-m-d H:i:s',strtotime("-12 hours"));

		$sQuery = "
			SELECT
				n.*
			FROM notifications n
				#WHERE status IN ('wait','failed')
				WHERE status IN ('wait')
				AND n.id_by_operator = 0
				AND send_after <= '$for_date'
				AND send_after > '$sFromTime'
		";

		if(!empty($channel)) {
			$sQuery .= " AND channel = ".$this->oDB->Quote($channel);
		}

		$sQuery .= " LIMIT 30 ";
		return $this->select($sQuery);
	}

	function getTelNotifications() {

		global $db_name_sod;

		$sQuery = "
				SELECT
					n.*,
					o.num AS object_num,
					o.name AS object_name
				FROM notifications n
				JOIN {$db_name_sod}.objects o ON o.id = n.id_object
				WHERE n.channel = 'tel'
					#AND n.status IN ('wait')
					AND UNIX_TIMESTAMP(created_time) >= UNIX_TIMESTAMP(NOW()) - 86400
				ORDER BY n.id DESC
			";

		$aData = $this->select($sQuery);

		foreach($aData as $key =>$value) {
			$aData[$key]['additional_params'] = json_decode($value['additional_params']);
		}

		return $aData; //array(); //$aData;
	}


	function changeStatus($new_status,$aIDs) {

		if(empty($aIDs)) return false;

		$sIDs = implode(',',$aIDs);

		$sQuery = "
			UPDATE
				notifications
			SET status = '{$new_status}'
			WHERE id IN ({$sIDs})
		";

		return $this->oDB->Execute($sQuery);
	}

	/*
        function addSignalNotifications($aNotificationInfo, $aSignal) {
            $oDBObjectsSingles = new DBObjectsSingles();

            $aData 					= array();
            $aData['id_event'] 		= 1;

            if ( in_array($aNotificationInfo['service_code'],array('SMS','SMS_AB')) ) {
                $aData['channel'] 	= 'sms';
            }

            if ( in_array($aNotificationInfo['service_code'],array('TEL','TEL_AB')) ) {
                $aData['channel'] 	= 'tel';
            }

            $aData['id_object'] 	= $aSignal['id_object'];
            $aData['send_after'] 	= time();
            $aData['target'] 		= $aNotificationInfo['target_gsm'];

            $aAdditionalParams 						= array();
            $aAdditionalParams['id_signal'] 		= $aSignal['id_sig'];
            $aAdditionalParams['signal_time'] 		= $aSignal['alarm_time'];
            $aAdditionalParams['signal_name'] 		= $aSignal['message'];
            $aAdditionalParams['signal_name_en'] 	= convertCyr2Pho($aSignal['message']);
            $aAdditionalParams['id_service'] 		= $aNotificationInfo['id_service'];
            $aAdditionalParams['object_name_en'] 	= convertCyr2Pho($aSignal['object_name']);
            $aAdditionalParams['object_name'] 		= $aSignal['object_name'];
            $aAdditionalParams['object_num'] 		= $aSignal['object_num'];
            $aAdditionalParams['object_addr'] 		= $aSignal['object_address'];
            $aAdditionalParams['object_addr_en'] 	= convertCyr2Pho($aSignal['object_address']);

            $aData['additional_params'] 			= json_encode($aAdditionalParams);

            $this->addNotification($aData);

            if ( $aNotificationInfo['service_code'] == 'SMS' ) {
                $aSingle 					= array();
                $aSingle['id_object'] 		= $aSignal['id_object'];
                $aSingle['id_office'] 		= $aNotificationInfo['id_office'];
                $aSingle['id_service'] 		= $aNotificationInfo['id_service'];
                $aSingle['service_name'] 	= 'Изпращане на SMS за аларма от '.date("d.m.Y H:i:s")." на GSM: ".$aNotificationInfo['target_gsm'];
                $aSingle['single_price'] 	= $aNotificationInfo['single_price'];
                $aSingle['quantity'] 		= 1;
                $aSingle['total_sum'] 		= $aNotificationInfo['single_price'];
                $aSingle['start_date'] 		= date('Y-m-d H:i:s');

                $oDBObjectsSingles->update($aSingle);
            }
        }
    */

	public function checkSmsFilter($nIDObject, $nIDSignal, $nStatus, $gsm) {
		global $db_name_system, $db_name_sod;

		$control_time 	= 60 * 15; // 15 min
		$aData			= array();

		$sQuery = "
			SELECT
				n.additional_params
			FROM {$db_name_system}.notifications n
			JOIN {$db_name_sod}.signals s ON ( s.id = {$nIDSignal} )
			WHERE n.id_object = {$nIDObject}
				AND n.channel = 'sms'
				AND n.id_event = 1
				AND UNIX_TIMESTAMP(n.created_time) + {$control_time} >= UNIX_TIMESTAMP(NOW())
				AND n.target = '{$gsm}'
				#AND s.play_alarm = 2
		";

		$aData = $this->select($sQuery);

		foreach ( $aData as $key => $val ) {
			$aData = json_decode($val['additional_params']);

			if ( (in_array("id_signal", $aData) && $aData['id_signal'] == $nIDSignal) && (in_array("is_alarm", $aData) && $aData['is_alarm'] == $nStatus) ) {
				return true;
			}
		}

		return false;
	}

	function addSignalNotifications($aNotificationInfoGSM, $aSignal) {
		$oDBObjectsSingles 		= new DBObjectsSingles();

		foreach ( $aNotificationInfoGSM as $aNotificationInfo) {
			$aData = array();
			$aData['id_event'] 		= 1;

			if ( $this->checkSmsFilter($aSignal['id_object'], $aSignal['id_sig'], $aSignal['alarm'], $aNotificationInfo['target_gsm']) ) {
				return;
			}

			if(in_array($aNotificationInfo['service_code'],array('SMS','SMS_AB'))) {
				$aData['channel'] 	= 'sms';
			}
			if(in_array($aNotificationInfo['service_code'],array('TEL','TEL_AB'))) {
				$aData['channel'] 	= 'tel';
			}
			$aData['id_object'] 	= $aSignal['id_object'];
			$aData['send_after'] 	= time();
			$aData['target'] 		= $aNotificationInfo['target_gsm'];
			/*
                    $aAdditionalParams = array();
                    $aAdditionalParams['id_signal'] = $aSignal['id_sig'];
                    $aAdditionalParams['signal_time'] = $aSignal['alarm_time'];
                    $aAdditionalParams['signal_name'] = $aSignal['msg_al'];
                    $aAdditionalParams['id_service'] = $aNotificationInfo['id_service'];
            */
            $strSpecials = array("№", "в„–", "&", "`", "``", "'", "''", "~", "„", "„", "в", "^", "", '"', '""', "....", "{", "}", "~", "[", "]", "|", "€",  "/", "\\");
            $strMessages = str_replace($strSpecials, "", $aSignal['message']);
            $strObjectN  = str_replace($strSpecials, "", $aSignal['object_name']);
            $strAddress  = str_replace($strSpecials, "", $aSignal['object_address']);

            $aAdditionalParams 						= array();
            $aAdditionalParams['id_signal'] 		= $aSignal['id_sig'];
            $aAdditionalParams['signal_time'] 		= $aSignal['alarm_time'];
            $aAdditionalParams['signal_name'] 		= $aSignal['message'];
            $aAdditionalParams['signal_name_en'] 	= convertCyr2Pho($strMessages); //$aSignal['message']);
            $aAdditionalParams['id_service'] 		= $aNotificationInfo['id_service'];
            $aAdditionalParams['object_name_en'] 	= convertCyr2Pho($strObjectN); //$aSignal['object_name']);
            $aAdditionalParams['object_name'] 		= $aSignal['object_name'];
            $aAdditionalParams['object_num'] 		= $aSignal['object_num'];
            $aAdditionalParams['object_addr'] 		= $aSignal['object_address'];
            $aAdditionalParams['object_addr_en'] 	= convertCyr2Pho($strAddress); //$aSignal['object_address']);
            $aAdditionalParams['is_alarm'] 			= $aSignal['alarm'];

			$aData['additional_params'] 			= json_encode($aAdditionalParams);

			$this->addNotification($aData);

			if ( $aNotificationInfo['service_code'] == 'SMS' ) {
				$aSingle = array();
				$aSingle['id_object'] 		= $aSignal['id_object'];
				$aSingle['id_office'] 		= $aNotificationInfo['id_office'];
				$aSingle['id_service'] 		= $aNotificationInfo['id_service'];
				$aSingle['service_name'] 	= 'Изпращане на SMS нотификация от '.date("d.m.Y H:i:s")." на GSM: ".$aNotificationInfo['target_gsm'];
				$aSingle['single_price'] 	= $aNotificationInfo['single_price'];
				$aSingle['quantity'] 		= 1;
				$aSingle['total_sum'] 		= $aNotificationInfo['single_price'];
				$aSingle['start_date'] 		= date('Y-m-d H:i:s');

				$oDBObjectsSingles->update($aSingle);
			} else if ( $aNotificationInfo['service_code'] == 'TEL' ) {
				$aSingle = array();
				$aSingle['id_object'] 		= $aSignal['id_object'];
				$aSingle['id_office'] 		= $aNotificationInfo['id_office'];
				$aSingle['id_service'] 		= $aNotificationInfo['id_service'];
				$aSingle['service_name'] 	= 'Телефонна нотификация от '.date("d.m.Y H:i:s")." на GSM: ".$aNotificationInfo['target_gsm'];
				$aSingle['single_price'] 	= $aNotificationInfo['single_price'];
				$aSingle['quantity'] 		= 1;
				$aSingle['total_sum'] 		= $aNotificationInfo['single_price'];
				$aSingle['start_date'] 		= date('Y-m-d H:i:s');
				$oDBObjectsSingles->update($aSingle);
			}
		}
	}

	function addObjectFeePersonNotification($nIDPerson, $nIDObject, $aContract, $fee_type) {
		$oDBNotificationsEvents = new DBNotificationsEvents();
		$oDBPersonnel 			= new DBPersonnel();
		$oDBObjectServices 		= new DBObjectServices();
		$oDBCalculatorFees 		= new DBCalculatorFees();

		$nObjectMonthTax 		= $oDBObjectServices->getSumPriceByObject($nIDObject);

		$aObjectFeePersonsEvent = $oDBNotificationsEvents->getByCode('object_fee_persons');
		$aPerson 				= $oDBPersonnel->getRecord($nIDPerson);
		$aPersonFees 			= $oDBCalculatorFees->getCalculatedFees($aPerson['id_office'],$nObjectMonthTax);


		$user_id 				= $aContract['entered_user'];
		$user_email 			= $aPerson['email'];
		$user_phone 			= !empty($aPerson['mobile']) ? $aPerson['mobile'] : (!empty($aPerson['business_phone']) ? $aPerson['business_phone'] : '');

		$aNotificationsMulti 	= array();
		$event_channels 		= array();
		$object_fee_persons_event_id = $aObjectFeePersonsEvent['id'];

		if ( !empty($aObjectFeePersonsEvent['sms']) && !empty($user_phone) ) {
			$event_channels[] = 'sms';
		}

		if ( !empty($aObjectFeePersonsEvent['mail']) && !empty($user_email) ) {
			$event_channels[] = 'mail';
		}

		if ( !empty($aObjectFeePersonsEvent['system']) ) {
			$event_channels[] = 'system';
		}

		foreach($event_channels as $event_channel) {
			$aNotification 					= array();
			$aNotification['id_event'] 		= $object_fee_persons_event_id;
			$aNotification['channel'] 		= $event_channel;
			$aNotification['send_after'] 	= date('Y-m-d H:i:s');

			switch($event_channel) {
				case 'mail':	$aNotification['target'] = $user_email;	break;
				case 'sms':		$aNotification['target'] = $user_phone;	break;
				case 'system':	$aNotification['target'] = $user_id;	break;
			}

			$aNotification['id_user'] 		= $user_id;
			$aNotification['id_object'] 	= $nIDObject;

			$aAdditionalParams 						= array();
			$aAdditionalParams['id_contract'] 		= $aContract['id'];
			$aAdditionalParams['fee_type'] 			= $fee_type;
			$aAdditionalParams['object_month_tax'] 	= $nObjectMonthTax;
			$aAdditionalParams['fee_sum'] 			= $aPersonFees[$fee_type];

			$aNotification['additional_params'] 	= json_encode($aAdditionalParams);

			$aNotificationsMulti[] 					= $aNotification;
		}

		$this->multiInsert($aNotificationsMulti);
	}
}

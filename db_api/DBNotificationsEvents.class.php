<?php

class DBNotificationsEvents extends DBBase2 {

	public function __construct() {
		global $db_system;
		parent::__construct($db_system,'notifications_events');
	}
	
	public function getReport(DBResponse $oResponse) {		
		
		$sQuery = "
			SELECT SQL_CALC_FOUND_ROWS
				ne.*,
				CONCAT_WS(',',ne.id,ne.sms,'sms') AS sms_,
				CONCAT_WS(',',ne.id,ne.mail,'mail') AS mail_,
				CONCAT_WS(',',ne.id,ne.system,'system') AS system_
			FROM notifications_events ne				
		";

		
		$this->getResult($sQuery,'id', DBAPI_SORT_DESC, $oResponse);
		
		foreach($oResponse->oResult->aData as $k => &$aRow) {							
			$aRow['template'] = '';
			$oResponse->setDataAttributes($k,'sms_',array('class'=>'on_off'));
			$oResponse->setDataAttributes($k,'mail_',array('class'=>'on_off'));
			$oResponse->setDataAttributes($k,'system_',array('class'=>'on_off'));			
		}
		unset($aRow);
		
		$oResponse->setField('description','Събитие');		
		$oResponse->setField('sms_','Известяване чрез sms');
		$oResponse->setField('mail_','Известяване чрез имейл');
		$oResponse->setField('system_','Известяване в Клиентската система');
		$oResponse->setField("template",	"", NULL, "images/edit.gif", "openTemplate", "Шаблон" );
		
	}
	
	function changeState($nIDEvent,$sState,$sType) {
				
		$nIDEvent = (int) $nIDEvent;				
		$nState = $sState == 'on' ? 1 : 0;

		if(empty($nIDEvent)) return false;
		if(!in_array($sType,array('mail','sms','system'))) return false;

		$aData['id'] = $nIDEvent;
		$aData[$sType] = $nState;
		
		$this->update($aData);
	}
	
	function getNotificationEventsWithTemplates() {
		
		$sQuery = "
				SELECT
					ne.*,
					nt.sms_text,
					nt.email_subject,
					nt.email_body
				FROM notifications_events ne
				LEFT JOIN notifications_templates nt ON nt.id_event = ne.id
				GROUP BY ne.id
			";

		return $this->selectAssoc($sQuery);		
	}
	
	function getNotificationsEvents() {
		$sQuery = "
			SELECT
				ne.id AS __key,
				ne.*
			FROM notifications_events ne
		";

		return $this->selectAssoc($sQuery);
	}
	
	function getByCode($sCode) {
		
		$sQuery = "
				SELECT
					*
				FROM notifications_events
				WHERE code = ".$this->oDB->Quote($sCode);

		return $this->selectOnce($sQuery);
	}

    function getTemplateByCode($sCode) {
        $sQuery = "
			SELECT
				nt.*
			FROM notifications_events ne
			JOIN notifications_templates nt ON nt.id_event = ne.id
			WHERE
				ne.code = ".$this->oDB->Quote($sCode);
        return $this->selectOnce($sQuery);
    }

    public function getAllEventHandmade() {
        $sQuery = "
            SELECT
            *
            FROM
            notifications_events
            WHERE 1
            AND handmade = 1
        ";

        return $this->select($sQuery);
    }

    public function getNotificationEventForMail() {
        $sQuery = "
            SELECT
            *
            FROM
            notifications_events
            WHERE 1
            AND mail = 1
        ";

        return $this->selectOnce($sQuery);
    }
}
<?php

class ApiAddNotification {
    public function load(DBResponse $oResponse) {

        $oDBNotificationsEvents = new DBNotificationsEvents();

        $aAllEvent = $oDBNotificationsEvents->getAllEventHandmade();

        $oResponse->setFormElement('form1', 'nIDNotification');
        $oResponse->setFormElementChild('form1', 'nIDNotification', array( 'value' => 0 ), '--Изберете нотификация--');


        foreach ($aAllEvent as $aRow) {
            $oResponse->setFormElementChild('form1', 'nIDNotification', array( 'value' => $aRow['id'] ), $aRow['description']);
        }

        $oResponse->printResponse();
    }

    public function loadTemplate(DBResponse $oResponse) {

        $nIDClient          = Params::get('nIDClient',0);
        $nIDNotification    = Params::get('nIDNotification',0);
        $nCount             = Params::get('nCount',0);

        $oDBNotificationTemplate = new DBNotificationsTemplates();
        $oDBClients = new DBClients();

        if(empty($nIDClient)) {
            throw new Exception("Трябва да изберете клиент");
        }

        if(empty($nIDNotification)) {
            throw new Exception("Трябва да изберете нотификация");
        }

        $aEvent = $oDBNotificationTemplate->getTemplateByEvent($nIDNotification);
        $aClients = $oDBClients->getRecord($nIDClient);

        $aEvent['sms_text'] = str_replace('{{id_client}}',$aClients['id_wf'] , $aEvent['sms_text']);
        $aEvent['sms_text'] = str_replace('{{count}}',$nCount , $aEvent['sms_text']);

        $sPhone = empty($aClients['sms_phone'])? "Без телефон за известявавне" : $aClients['sms_phone'];

        $oResponse->setFormElement('form1','sTemplate',array(),$aEvent['sms_text']);
        $oResponse->setFormElement('form1','sPhone',array(),$sPhone);

        $oResponse->printResponse();
    }

    public function save(DBResponse $oResponse) {
        $nIDClient          = Params::get('nIDClient',0);
        $nIDNotification    = Params::get('nIDNotification',0);
        $nCount             = Params::get('nCount',0);

        $oDBNotification = new DBNotifications();
        $oDBClient = new DBClients();

        $aData = [];
        if(empty($nIDClient)) {
            throw new Exception("Трябва да изберете клиент");
        }

        $aClient = $oDBClient->getByID($nIDClient);

        if(empty($aClient)) {
            throw new Exception("Грешка при определяне на клиент!");
        }



        if(empty($nIDNotification)) {
            throw new Exception("Трябва да изберете нотификация");
        }

        $aAdditionalParams = array();
        $aAdditionalParams['id_client'] = $nIDClient;
        $aAdditionalParams['count'] = $nCount;

        $aData['id_client'] = $nIDClient;
        $aData['id_event'] = $nIDNotification;
        $aData['channel'] = 'sms';
        $aData['status'] = 'wait';
        $aData['target'] = $aClient['sms_phone'];
        $aData['send_after'] 	= time();
        $aData['additional_params'] = json_encode($aAdditionalParams);

        $oDBNotification->update($aData);

        $oResponse->printResponse();
    }
}
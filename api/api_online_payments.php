<?php
class ApiOnlinePayments {
    private $oDB_OnlinePayment;

    public function __construct() {
        $this->oDB_OnlinePayment = new DBOnlinePayments();
    }

    public function result(DBResponse $oResponse) {
        $aParams = Params::getAll();

        $this->oDB_OnlinePayment->getReportNew($oResponse, $aParams);
        $oResponse->printResponse('OnlinePayment','OnlinePayment');
    }

}
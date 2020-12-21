<?php
//require_once("pdf/pdf_contract_sales.php");

class ApiTechPlanningRequests {

    /*
    public function load( DBResponse $oResponse ) {
        $nIDOffice = Params::get('id_office',0);

        $oDBOffices = new DBOffices();
        $aOffices = array();
        $aOffices = $oDBOffices->getOffices4();
        $oResponse->setFormElement('form1', 'nIDOffice', array(), '');
        $oResponse->setFormElementChild('form1', 'nIDOffice',	array('value' => '0'),'Изберете');

        foreach ( $aOffices as $key => $val ) {
            if($nIDOffice == $key) {
                $oResponse->setFormElementChild('form1', 'nIDOffice',	array('value' => $key, 'selected' => 'selected'),$val);
            } else {
                $oResponse->setFormElementChild('form1', 'nIDOffice',	array('value' => $key),$val);
            }
        }

        if(!empty($nIDOffice)) {
            $oDBTechRequests = new DBTechRequests();
            $oDBTechRequests->getRequests($nIDOffice, $oResponse);
        }

        $oResponse->printResponse();

    }
    */

    public function load( DBResponse $oResponse ) {

        $nIDOffice = Params::get('id_office',0);

        $oDBFirms = new DBFirms();
        $oDBOffices = new DBOffices();

        $aFirms = $oDBFirms->getFirms3();

        if( empty($nIDOffice)) {
            $nIDOffice = $_SESSION['userdata']['id_office'];
        }

        $nIDFirm = $oDBOffices->getFirmByIDOffice($nIDOffice);

        $oResponse->setFormElement('form1', 'nIDFirm', array(), '');
        $oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>'0')), "--Изберете--");
        foreach($aFirms as $key => $value)
        {
            if( $nIDFirm == $key ) {
                $oResponse->setFormElementChild('form1', 'nIDFirm', array("value"=>$key , 'selected' => 'selected'), $value);
            } else {
                $oResponse->setFormElementChild('form1', 'nIDFirm', array("value"=>$key), $value);
            }
        }

        $oResponse->setFormElement('form1', 'nIDOffice', array(), '');

        $oDBOffices = new DBOffices();
        $aOffices = $oDBOffices->getAllOfficesByIDFirm($nIDFirm);

        $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Изберете--");
        foreach($aOffices as $key => $value)
        {
            if( $nIDOffice == $key ) {
                $oResponse->setFormElementChild('form1', 'nIDOffice', array("value"=>$key, 'selected' => 'selected'), $value);
            } else {
                $oResponse->setFormElementChild('form1', 'nIDOffice', array("value"=>$key), $value);
            }
        }
        $oDBTechRequests = new DBTechRequests();
        $oDBTechRequests->getRequests($nIDOffice, $oResponse);

        $oResponse->printResponse();
    }

    public function loadOffices(DBResponse $oResponse)
    {
        $nFirm 	=	Params::get('nIDFirm');

        $oResponse->setFormElement('form1', 'nIDOffice', array(), '');

        if(!empty($nFirm))
        {
            $oDBOffices = new DBOffices();
            $aOffices = $oDBOffices->getAllOfficesByIDFirm($nFirm);

            $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Изберете--");
            foreach($aOffices as $key => $value)
            {
                $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key)), $value);
            }
        }
        else
        {
            $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
        }

        $oResponse->printResponse();
    }


    public function result( DBResponse $oResponse ) {

        $sObjectName = Params::get('sObjectName',"");
        $nIDOffice = Params::get('nIDOffice',0);
        $sApiAction = Params::get("api_action", "");
        $nIDContract = Params::get('id_contract','0');

        if( $sApiAction == 'export_to_pdf') {
            $oPDF = new ContractSalesPDF("P");
            $oPDF -> PrintReport($nIDContract);
        }

        $oDBTechRequests = new DBTechRequests();

        //проверява за заявки неизпълнени в период от 1 ден от датата на планиран старт и ги връща за планиране
        $oDBTechRequests->resetOldRequests();

        $oDBTechRequests->getRequests($nIDOffice, $oResponse);

        $oResponse->printResponse();
    }


    public function delRequest( DBResponse $oResponse )
    {

        $nID = Params::get( 'id_request', 0 );

        $oDBTechRequests = new DBTechRequests();

        if( $nID > 0 )
        {
            $aTechRequest = $oDBTechRequests->getRecord($nID);

            if(!empty($aTechRequest['id_contract'])) {
                throw new Exception("Не можете да изтриете заявка към която има оферта. Заявака ще се изтрие след анулиране на офертата!");
            }

            $oDBTechRequests->delete( $nID );
        }
    }
}
?>
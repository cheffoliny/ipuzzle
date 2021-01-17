<?php

/**
 * Created by PhpStorm.
 * User: adm
 * Date: 8.9.2020 г.
 * Time: 15:23
 */

if ( !isset($_SESSION) ) {
    session_start();
}


if ( !defined('PREFIX_SALES_DOCS') ) {
    define('PREFIX_SALES_DOCS',		    'sales_docs_');
    define('PREFIX_SALES_DOCS_ROWS',    'sales_docs_rows_');
}

if ( !defined('PREFIX_ORDERS') ) {
    define('PREFIX_ORDERS',			'orders_');
    define('PREFIX_ORDERS_ROWS',	'orders_rows_');
}

function sortAB( $a, $b ) { return strtotime($a) - strtotime($b); }

class NEWDBSalesDocs extends DBBase2 {
    private $alerts = [];
    private $client = [];
    private $error = "";
    public  $monthlyCaption = "";

    function __construct() {
        global $db_finance;

        parent::__construct($db_finance, "bank_accounts");
    }

    private function getPerson() {
        return isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
    }

    public function getOfficesCityByIdFrim($nIDFirm) {
        global $db_name_sod;

        if ( !is_numeric($nIDFirm) || empty($nIDFirm) ) {
            return array();
        }

        $sQuery = "
            SELECT
                c.id,
                c.name
            FROM {$db_name_sod}.cities c
            JOIN {$db_name_sod}.offices o ON c.id_reaction_office = o.id
            WHERE c.to_arc = 0
                AND c.id_reaction_office != 0
                AND o.id_firm = {$nIDFirm}
            ORDER BY c.name
        ";

        return $this->select($sQuery);
    }

    public function getDefaultCityNameByIdPerson($id) {
        global $db_name_sod, $db_name_personnel;

        if ( !is_numeric($id) || empty($id) ) {
            return 0;
        }

        $sQuery = "
            (
                SELECT
                    c.name
                FROM {$db_name_personnel}.personnel p
                JOIN {$db_name_sod}.offices o ON p.id_office = o.id
                JOIN {$db_name_sod}.cities c ON o.address_city = c.id
                WHERE p.id = {$id}
            ) UNION (
                SELECT 'София'
            )
            LIMIT 1
        ";

        return $this->selectOne($sQuery);
    }
        
    public function getDefaultIdCityForPerson() {
        $cities = $this->getOfficesCityByIdFrim($this->getDelivererByPerson()['id']);
        return $cities[array_search($this->getDefaultCityNameByIdPerson($this->getPerson()), array_column($cities, 'name'))]['id'];
    }

    public function getFirmsAsClient() {
        global $db_name_sod;

        $sQuery = "
				SELECT 
					jur_name AS name,
					address, 
					idn,
					idn_dds,
					jur_mol
				FROM {$db_name_sod}.firms
				WHERE to_arc = 0 
					AND jur_name != ''	
				GROUP BY jur_name		
			";

        return $this->select($sQuery);
    }

    public function getBankAccountsForOrders() {
        global $db_name_finance;

        $nIDUser = $this->getPerson();

        $sQuery = "
            SELECT 
                ba.id,
                IF ( ba.cash, CONCAT(ba.name_account, ' [каса]'), CONCAT(ba.name_account, ' [банка]') ) as name,
                ba.iban as iban,
                IF ( ba.cash, 'cash', 'bank' ) as type

            FROM {$db_name_finance}.bank_accounts ba
            LEFT JOIN {$db_name_finance}.cashier c ON (FIND_IN_SET(ba.id, c.bank_accounts_operate) AND c.to_arc = 0)
            WHERE ba.to_arc = 0
                AND c.id_person = '{$nIDUser}'
            ORDER BY ba.name_account
        ";

        return $this->select( $sQuery );
    }

    public function getBankAccounts() {
        global $db_name_finance;

        $sQuery = "
				SELECT 
					ba.id,
					ba.name_account as name,
					ba.iban as iban,
					IF ( ba.cash, 'cash', 'bank' ) as type
				FROM {$db_name_finance}.bank_accounts ba
				WHERE ba.to_arc = 0
					AND ba.cash = 0
				ORDER BY ba.name_account
			";

        return $this->select( $sQuery );
    }

    public function getFirmsByOffice() {
        global $db_name_sod;

        $sAccessRegions = "";

        if ( isset($_SESSION['userdata']['access_right_regions']) ) {
            $sAccessRegions = implode(',', $_SESSION['userdata']['access_right_regions']);
        }

        $sQuery = "
				SELECT 
					f.id as id_firm, 
					f.name as firm, 
					o.id as id_office, 
					o.name as region,
					f.idn,
					f.jur_name 
				FROM {$db_name_sod}.firms f
				RIGHT JOIN {$db_name_sod}.offices o ON ( o.id_firm = f.id AND o.to_arc = 0 ) 
				WHERE f.to_arc = 0
		    ";

        // Todo: ako nqmame?
        if ( !empty($sAccessRegions) ) {
            $sQuery .= " AND o.id IN ({$sAccessRegions})\n";
        }

        $sQuery .= "
				ORDER BY id_firm, region
			";

        return $this->select($sQuery);
    }

    public function getFirmNames($regions) {
        $aFirms = [];

        foreach ( $regions as $region ) {
            $aFirms[$region['id_firm']] = ['id_firm' => $region['id_firm'], 'name' => $region['firm'], 'idn' => $region['idn']];
        }

        return array_values($aFirms);
    }

    public function isValidID( $nID ) {
        return preg_match("/^\d{13}$/", $nID);
    }

    public function getFirmServices() {
        global $db_name_finance, $db_name_sod, $db_name_storage;

        $sAccessRegions = "";

        if ( isset($_SESSION['userdata']['access_right_regions']) ) {
            $sAccessRegions = implode(',', $_SESSION['userdata']['access_right_regions']);
        }

        $sQuery = "
            SELECT 
                ns.id as id_service,
                nsf.id_firm, 
                ns.name,
                1 as quantity,
                ns.is_month,
                ns.price,
                m.code as measure,
                ns.vat_tax as vat
            FROM {$db_name_finance}.nomenclatures_services ns
            JOIN {$db_name_finance}.nomenclatures_services_firms nsf ON nsf.id_nomenclature_service = ns.id
            JOIN {$db_name_sod}.offices o ON ( o.id_firm = nsf.id_firm AND o.to_arc = 0 ) 
            JOIN {$db_name_storage}.measures m ON (m.id = ns.id_measure)
            WHERE ns.to_arc = 0
        ";

        if ( !empty($sAccessRegions) ) {
            $sQuery .= " AND o.id IN ({$sAccessRegions})\n";
        }

        $sQuery .= "
            GROUP BY id_service, id_firm
            ORDER BY id_firm, name
        ";

        return $this->select( $sQuery );
    }

    public function getDelivererByPerson() {
        global $db_name_sod, $db_name_personnel;

        $nIDPerson 	    = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;

        if ( empty($nIDPerson) || !is_numeric($nIDPerson) ) {
            return [];
        }
        
        $sQuery = "
                SELECT 
                    f.id,
					f.jur_name,
					f.idn,
					f.idn_dds,
					f.jur_mol,
					f.address,
					
					f.short_jur_mol,
					f.jur_pos
				FROM {$db_name_personnel}.personnel p
				LEFT JOIN {$db_name_sod}.offices o ON o.id = p.id_office
				LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm
				WHERE p.id = {$nIDPerson}
				LIMIT 1
			";
        // f.mol_egn,
        return $this->selectOnce($sQuery);
    }

    public function getCashierByIDPerson() {
        global $db_name_finance;

        $nIDPerson 	    = $this->getPerson();

        if ( empty($nIDPerson) || !is_numeric($nIDPerson) ) {
            return [];
        }

        $sQuery = "
                SELECT
                    *
                FROM {$db_name_finance}.cashier
                WHERE to_arc = 0
                    AND id_person = {$nIDPerson}
                LIMIT 1
			";

        $aData = $this->selectOnce( $sQuery );

        if ( !empty($aData) ) {
            return $aData;
        } else {
            return [];
        }
    }

    public function getClientByID($nID) {
        global $db_name_sod;

        if ( empty($nID) || !is_numeric($nID) ) {
            return [];
        }

        $sQuery = "
            SELECT 
              * 
            FROM {$db_name_sod}.clients 
            WHERE id = {$nID} 
            LIMIT 1
		";

        return $this->selectOnce($sQuery);
    }

    public function getClientByObjectID($nID) {
        global $db_name_sod;

        if ( empty($nID) || !is_numeric($nID) ) {
            return [];
        }

        $sQuery = "
            SELECT
                c.*
            FROM {$db_name_sod}.clients_objects co
            JOIN {$db_name_sod}.clients c ON c.id = co.id_client
            WHERE co.id_object = {$nID}
            AND co.to_arc = 0
            LIMIT 1 
        ";

        /*
        $sQuery = "
            SELECT * FROM {$db_name_sod}.clients
            WHERE id=( 
                SELECT id_client
                FROM {$db_name_sod}.clients_objects
                WHERE id_object = {$nID}
                AND to_arc=0
            )
        ";
        */

        return $this->selectOnce($sQuery);
    }

    private function getObjectsByClient($nID) {
        global $db_name_sod;

        if ( empty($nID) || !is_numeric($nID) ) {
            return [];
        }

        $sQuery = "
            SELECT 
                DISTINCT o.*
            FROM {$db_name_sod}.objects o
            LEFT JOIN {$db_name_sod}.statuses s ON s.id = o.id_status 
            LEFT JOIN {$db_name_sod}.clients_objects c ON (c.id_object = o.id AND c.to_arc = 0)
            WHERE c.id_client = {$nID}
                AND s.payable = 1
		";

        return $this->select($sQuery);
    }

    private function getBlankDoc() {
        $aDeliverer		= $this->getDelivererByPerson();
        $aCashier       = $this->getCashierByIDPerson();
        $aData      = [];
        $aData['id'] 				= 0;
        $aData['doc_date'] 			= date("Y-m-d");
        $aData['doc_type'] 			= "faktura";
        $aData['doc_status']		= "final";
        $aData['doc_num']		    = "";
        $aData['paid_type'] 		= "cash";
        $aData['id_bank_epayment']  = 0;
        $aData['view_type']			= "extended";    //"single";
        $aData['total_sum'] 		= 0;
        $aData['orders_sum'] 		= 0;
        $aData['dds_sum'] 			= 0;
        $aData['dds_payed'] 		= false;
        $aData['dds_for_payment'] 	= true;
        $aData['note']				= "";
        $aData['single_view_name']	= "yслуга";
        //$aData['locked']			= false;
        $aData['from_book']			= false;
        $aData['is_book']			= 0;
        $aData['id_city']			= $this->getDefaultIdCityForPerson();
        $aData['doc_date_create']	= date("Y-m-d");
        $aData['created_user']		= "";
        $aData['created_time']		= date("Y-m-d");
        $aData['updated_user']		= "";
        $aData['updated_time']		= date("Y-m-d");
        $aData['invoice_payment'] 	= "cash";

        $aData['id_client']		    = 0;
        $aData['client_name']		= "";
        $aData['client_ein']		= "";
        $aData['client_ein_dds']	= "";
        $aData['client_address']	= "";
        $aData['client_mol']		= "";
        $aData['client_recipient']	= "";

        $aData['user_id'] 			= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
        $aData['user_name'] 		= isset($_SESSION['userdata']['name']) 		? $_SESSION['userdata']['name']  		: "";
        $aData['user_office_id'] 	= isset($_SESSION['userdata']['id_office']) ? $_SESSION['userdata']['id_office']  	: 0;
        $aData['user_office_name'] 	= isset($_SESSION['userdata']['region']) 	? $_SESSION['userdata']['region']  		: "";
        $aData['user_uname'] 		= isset($_SESSION['userdata']['username']) 	? $_SESSION['userdata']['username']  	: "";
        $aData['user_row_limit'] 	= isset($_SESSION['userdata']['row_limit']) ? $_SESSION['userdata']['row_limit']  	: 0;
        $aData['id_schet_account'] 	= isset($_SESSION['userdata']['id_schet_account']) ? $_SESSION['userdata']['id_schet_account']  : 0;
        $aData['user_has_debug'] 	= isset($_SESSION['userdata']['has_debug']) ? $_SESSION['userdata']['has_debug']  	: 0;


        $aData['deliverer_ein']		= isset($aDeliverer['idn']) 				? $aDeliverer['idn'] 					: 0;
        $aData['deliverer_name']	= isset($aDeliverer['jur_name']) 			? $aDeliverer['jur_name'] 				: "";
        $aData['deliverer_address']	= isset($aDeliverer['address']) 			? $aDeliverer['address'] 				: "";
        $aData['deliverer_ein_dds']	= isset($aDeliverer['idn_dds']) 			? $aDeliverer['idn_dds'] 				: "";
        $aData['deliverer_mol']	    = isset($aDeliverer['jur_mol']) 			? $aDeliverer['jur_mol'] 				: "";

        $aData['sale_doc_view'] 	= in_array('sale_doc_view', $_SESSION['userdata']['access_right_levels']);

        // При право за редакция - добавяме и право за преглед
        if ( in_array('sale_doc_edit', $_SESSION['userdata']['access_right_levels']) ) {
            $aData['sale_doc_view']	= true;
            $aData['sale_doc_edit'] = true;
        } else {
            $aData['sale_doc_edit'] = false;
        }

        // При пълно право за редакция - добавяме и право за преглед и редакция
        if ( in_array('sale_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
            $aData['sale_doc_view']	= true;
            $aData['sale_doc_edit'] = true;
            $aData['sale_doc_grant'] = true;
            $aData['locked']		= false;
        } else {
            $aData['sale_doc_grant'] = false;
            $aData['locked']		= false;
        }

        $aData['sale_doc_order_view'] = in_array('sale_doc_order_view', $_SESSION['userdata']['access_right_levels']) && $aData['sale_doc_view'];

        // При право за редакция - добавяме и право за преглед
        if ( in_array('sale_doc_order_edit', $_SESSION['userdata']['access_right_levels']) && $aData['sale_doc_view'] ) {
            $aData['sale_doc_order_view'] = true;
            $aData['sale_doc_order_edit'] = true;
        } else {
            $aData['sale_doc_order_edit'] = false;
        }

        $aData['id_cash_default'] = isset($aCashier['id_cash_default']) && !empty($aCashier['id_cash_default']) ? $aCashier['id_cash_default'] : -1;

        return $aData;
    }

    public function getDocData($nID) {
        global $db_name_finance, $db_name_personnel , $db_name_sod;

        $docData = $this->getBlankDoc();
        $client = $this->getClient();

        if ( !$this->isValidID($nID) ) {
            $docData['single_view_name'] = $client['invoice_single_view_name'] ?? "услуга";

            return $docData;
        }

        $sTable		= PREFIX_SALES_DOCS.substr($nID, 0, 6);

        $sQuery = "
            SELECT
                sd.*,
                ci.name as city_name,
                CONCAT(CONCAT_WS(' ',p_cr.fname,p_cr.mname,p_cr.lname),' [',DATE_FORMAT(sd.created_time,'%d.%m.%Y %H:%i.%s'),']') AS created,	
                CONCAT(CONCAT_WS(' ',p_up.fname,p_up.mname,p_up.lname),' [',DATE_FORMAT(sd.updated_time,'%d.%m.%Y %H:%i.%s'),']') AS updated				
            FROM {$db_name_finance}.{$sTable} sd
            LEFT JOIN {$db_name_personnel}.personnel p_cr ON p_cr.id = sd.created_user
            LEFT JOIN {$db_name_sod}.cities ci ON ci.id = sd.id_city
            LEFT JOIN {$db_name_personnel}.personnel p_up ON p_up.id = sd.updated_user
            WHERE sd.id = {$nID}
        
        ";

        $aData = $this->selectOnce($sQuery);

        if ( !empty($aData) ) {
            $aData['doc_date_create'] = substr($aData['created_time'], 0, 10);

            $docData = array_merge($docData, $aData);
        }

        if ( !empty($docData) && (!isset($docData['single_view_name']) || empty($docData['single_view_name'])) ) {
            $docData['single_view_name'] = $client['invoice_single_view_name'] ?: "услуга";
        }

        return $docData;
    }

    public function getDocRows($nID) {
        global $db_name_finance, $db_name_sod;

        if ( !$this->isValidID($nID) ) {
            return [];
        }

        $sTable		= PREFIX_SALES_DOCS_ROWS.substr($nID, 0, 6);

        $sQuery = "
            SELECT
                sd.*,
                ofc.id_firm,
                ofc.name AS region,
                frm.name AS firm
            FROM {$db_name_finance}.{$sTable} sd
            LEFT JOIN {$db_name_sod}.offices ofc ON ofc.id = sd.id_office
            LEFT JOIN {$db_name_sod}.firms frm ON frm.id = ofc.id_firm
            WHERE sd.id_sale_doc = {$nID}
      
        ";

        return $this->select($sQuery);
    }

    public function getVatTotal($nID) {
        global $db_name_finance;

        if ( !$this->isValidID($nID) ) {
            return [];
        }

        $sTable		= PREFIX_SALES_DOCS_ROWS.substr($nID, 0, 6);

        $sQuery = "
            SELECT
            SUM(`total_sum`) as 'vat_total'
            FROM {$db_name_finance}.{$sTable}
            WHERE id_sale_doc = {$nID}
            AND is_dds = 1
        ";
        return $this->selectOnce($sQuery);
    }

    public function getDocRowsViewByMonth($nID) {
        global $db_name_finance;

        if ( !$this->isValidID($nID) ) {
            return [];
        }

        $sTable		= PREFIX_SALES_DOCS_ROWS.substr($nID, 0, 6);

        $sQuery = "
            SELECT
                object_name,
                 IF(
                        `type` = 'free' AND id_object=0,
                        '',
                        IF(
                        LENGTH(TRIM(view_type_by_services)),
                        view_type_by_services,
                        service_name
                 )) AS service_name,
                measure,
                `month`,
                IF ( `type` = 'month', SUM(total_sum), SUM(single_price) ) as single_price,
                IF ( `type` = 'month', 1, quantity ) as quantity,
                SUM(total_sum) AS total_sum,
                SUM(paid_sum) AS paid_sum,
                case
                    #when for_smartsot = 1 AND `type` = 'month' then 1
                    #when for_smartsot = 0 AND `type` = 'free' AND id_object != 0 then id_duty_row
                    #when for_smartsot = 1 AND `type` = 'free' AND id_object != 0 then 2
                    #when for_smartsot = 0 AND `type` = 'month' then id_service
                    when `type` = 'single' or `type` = 'free' then id
                END AS view_type
                
            FROM {$db_name_finance}.{$sTable}
            WHERE id_sale_doc = {$nID}
                AND is_dds != 1
            GROUP BY id_object, view_type, `month`
            ORDER BY object_name, `month`, service_name
		";

        return $this->select($sQuery);
    }

    public function getDocRowsViewByService($nID) {
        global $db_name_finance;

        if ( !$this->isValidID($nID) ) {
            return [];
        }

        $sTable		= PREFIX_SALES_DOCS_ROWS.substr($nID, 0, 6);

        $sQuery = "
            SELECT
                object_name,
                 IF(
                        `type` = 'free' AND id_object=0,
                        object_name,
                        IF(
                        LENGTH(TRIM(view_type_by_services)),
                        view_type_by_services,
                        service_name
                 )) AS service_name,
                 measure,
                measure,
                `month`,
                IF ( `type` = 'month', SUM(total_sum), SUM(single_price) ) as single_price,
                IF ( `type` = 'month', 1, quantity ) as quantity,
                SUM(total_sum) AS total_sum,
                SUM(paid_sum) AS paid_sum,
                case
                    when id_object AND `type` = 'free' then 2
                    when `type` = 'single' then id
                    WHEN `type` = 'free' AND id_object=0 THEN id
                END AS view_type
                
            FROM {$db_name_finance}.{$sTable}
            WHERE id_sale_doc = {$nID}
                AND is_dds != 1
            GROUP BY view_type
            ORDER BY service_name
		";

        return $this->select($sQuery);
    }

    public function getDocRowsViewBySingle($nID) {
        global $db_name_finance;

        if ( !$this->isValidID($nID) ) {
            return [];
        }

        $sTable		= PREFIX_SALES_DOCS_ROWS.substr($nID, 0, 6);
        $sBaseTable = PREFIX_SALES_DOCS.substr($nID, 0, 6);

        $sQuery = "
            SELECT
                sdr.object_name,
                sd.single_view_name AS service_name,
                1 as quantity,
                sdr.measure,
                sdr.`month`,
                SUM(sdr.total_sum) AS single_price,
                SUM(sdr.total_sum) AS total_sum,
                SUM(sdr.paid_sum) AS paid_sum
                
            FROM {$db_name_finance}.{$sTable} sdr
            JOIN {$db_name_finance}.{$sBaseTable} sd ON sd.id = sdr.id_sale_doc
            WHERE sdr.id_sale_doc = {$nID}
                AND is_dds != 1
		";

        return $this->select($sQuery);
    }

    public function getDocRowsViewByObject($nID) {
        global $db_name_finance;

        if ( !$this->isValidID($nID) ) {
            return [];
        }

        $sTable		= PREFIX_SALES_DOCS_ROWS.substr($nID, 0, 6);

        $sQuery = "
            SELECT
                object_name,
                object_name AS service_name,
                case
                when id_object THEN 1
                WHEN `type` = 'free' AND id_object=0 THEN quantity
                END as quantity,
                measure,
                `month`,
                SUM(single_price) AS single_price,
                SUM(total_sum) AS total_sum,
                SUM(paid_sum) AS paid_sum,
                case
                    when id_object then id_object
                    WHEN `type` = 'free' AND id_object=0 THEN id
                END AS view_type
            FROM {$db_name_finance}.{$sTable}
            WHERE id_sale_doc = {$nID}
                AND is_dds != 1
            GROUP BY view_type
            ORDER BY object_name
		";

        return $this->select($sQuery);
    }

    private function setAlert($message) {
        if ( !empty($message) ) {
            $this->alerts[] = $message;
        }
    }

    private function setError($message) {
        if ( empty($message) ) {
            $message = "Недефинирана грешка!";
        }

        http_response_code(400);
        $this->error = $message;

        return ["error" => $message];
    }

    public function getAlerts() {
        return $this->alerts;
    }

    public function getError() {
        return $this->error;
    }

    public function setClient($client) {
        if ( !empty($client) ) {
            $this->client = $client;
        }
    }

    public function getClient() {
        $client = $this->client;

        if ( !empty($client) && empty($client['invoice_single_view_name']) ) {
            $client['invoice_single_view_name'] = "услуга";
        }

        return $client;
    }

    private function getVatCoefficient($vat) {
        return ($vat / 100) + 1;
    }

    public function getSinglesDutyByObject($nIDObject, $sMonth) {
        global $db_name_sod, $db_name_finance, $db_name_storage;

        $aDuty	= array();

        if ( empty($nIDObject) || !is_numeric($nIDObject) ) {
            return array();
        }

        $aMon = explode("-", $sMonth);

        $dayTo 		= intval($aMon[2]);
        $monthTo 	= intval($aMon[1]);
        $yearTo 	= intval($aMon[0]);

        if ( empty($sMonth) || ($sMonth == "0000-00-00") || !checkdate($monthTo, $dayTo, $yearTo) ) {
            return array();
        }

        $sQuery = "
				SELECT
					os.id,
					os.start_date AS payment_date,
					os.id_office,
					r.name as region,
					r.id_firm,
					f.name as firm,
					os.id_service,
					os.service_name as name,
					IF ( char_length(o.invoice_name), o.invoice_name, o.name ) as object_name,
                    '' as view_type_detail,
                    '' as view_type_by_services,
					os.single_price,
					os.quantity,
					os.total_sum AS payment_sum,
					ns.vat_tax as vat,
                    m.code as measure,
					IF ( MIN(oser.last_paid) IS NULL, '0000-00-00', MIN(oser.last_paid) ) as last_paid
				FROM {$db_name_sod}.objects_singles os
				LEFT JOIN {$db_name_sod}.objects o ON o.id = os.id_object
				LEFT JOIN {$db_name_sod}.offices r ON r.id = os.id_office
				LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
				LEFT JOIN {$db_name_sod}.objects_services oser ON ( oser.id_object = o.id AND oser.to_arc = 0 )
				LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ( ns.id = os.id_service )
                LEFT JOIN {$db_name_storage}.measures m ON (m.id = ns.id_measure)
				WHERE os.to_arc = 0
					AND os.id_object = {$nIDObject}
					AND os.not_payable = 0
					AND UNIX_TIMESTAMP(paid_date) = 0 
					AND id_sale_doc = 0
				GROUP BY os.id
			";


        $aData = $this->select( $sQuery );

        foreach ( $aData as $val ) {
            $aPayMon = explode("-", $val['payment_date']);

            if ( !isset($aPayMon[2]) ) {
                continue;
            }

            $day 		= intval($aPayMon[2]);
            $month 		= intval($aPayMon[1]);
            $year 		= intval($aPayMon[0]);
            
            if ( mktime(0, 0, 0, $month, $day, $year) <= mktime(0, 0, 0, $monthTo, date('d'), $yearTo) ) {
                $aTmp							        = array();

                $aTmp['id_duty']				        = $val['id'];
                $aTmp['id_object'] 				        = $nIDObject;
                $aTmp['id_office']	                    = $val['id_office'];
                $aTmp['region']	                        = $val['region'];
                $aTmp['id_firm']	                    = $val['id_firm'];
                $aTmp['firm']	                        = $val['firm'];
                $aTmp['id_service'] 			        = $val['id_service'];
                $aTmp['month'] 					        = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
                $aTmp['service_name'] 			        = $val['name'];
                $aTmp['object_name'] 			        = $val['object_name'];
                $aTmp['view_type_detail']               = $val['view_type_detail'];
                $aTmp['view_type_by_services']          = $val['view_type_by_services'];
                $aTmp['single_price'] 			        = floatval(($val['single_price'] / $this->getVatCoefficient($val['vat'])));
                $aTmp['quantity'] 				        = floatval($val['quantity']);
                $aTmp['total_sum'] 				        = floatval(($val['payment_sum'] / $this->getVatCoefficient($val['vat'])));
                $aTmp['total_sum_with_dds'] 	        = floatval(($val['payment_sum']));
                $aTmp['payed']					        = floatval(0);
                $aTmp['type']					        = "single";
                $aTmp['for_payment']			        = true;
                $aTmp['vat']                            = $val['vat'];
                $aTmp['measure']                        = $val['measure'];
                //$aTmp['for_smartsot']			        = $val['for_smartsot'];
                $aTmp['last_paid']				        = $val['last_paid'];

                $aDuty[] 						        = $aTmp;
            }
        }
        
        $array_map1 = array_map('strtotime', array_column($aDuty, 'month'));
        array_multisort($array_map1, SORT_ASC, array_column($aDuty, 'total_sum'), SORT_DESC, $aDuty);
        unset($array_map1);
         
        return $aDuty;
    }

    public function getDutyByObject($nIDObject, $sMonth) {
        global $db_name_sod, $db_name_finance, $db_name_storage;

        $oSaleDoc           = new NEWDBSalesDocRows();

        $aDuty				= array();
        $numargs 			= func_num_args();
        $bPays				= $numargs == 3 ? func_get_arg(2) : false;
        $nTotalSum          = 0;

        if ( empty($nIDObject) || !is_numeric($nIDObject) ) {
            return array();
        }

        $aMon = explode("-", $sMonth);

        $dayTo 		= intval($aMon[2]);
        $monthTo 	= intval($aMon[1]);
        $yearTo 	= intval($aMon[0]);

        if ( empty($sMonth) || ($sMonth == "0000-00-00") || !checkdate($monthTo, $dayTo, $yearTo) ) {
            return [];
        }

        $ts 		= mktime(0, 0, 0, $monthTo -1, 1, $yearTo);

        $sQuery = "
            SELECT
                os.id,
                IF ( os.start_date > os.last_paid AND MONTH(os.start_date) != MONTH(os.last_paid), os.start_date, os.last_paid ) AS payment_date,
                IF ( DATE_FORMAT(os.real_paid, '%Y%m') > DATE_FORMAT(os.e_paid, '%Y%m'), os.real_paid, os.e_paid ) as real_paid,
                os.last_paid,
                IF ( os.start_date > os.last_paid AND MONTH(os.start_date) != MONTH(os.last_paid), 1, 0 ) AS start,	
                UNIX_TIMESTAMP(os.end_date) as end_date, 			
                os.id_office,
                r.name as region,
                r.id_firm,
                f.name as firm,
                os.id_service,
                os.service_name as name,
                IF ( char_length(o.invoice_name), o.invoice_name, o.name ) as object_name,
                '' as view_type_detail,
                '' as view_type_by_services,
                os.single_price,
                os.quantity,
                os.total_sum AS payment_sum,
                ns.vat_tax as vat,
                m.code as measure,
                ns.name AS original_nomenclature_name
            FROM {$db_name_sod}.objects_services os
            LEFT JOIN {$db_name_sod}.objects o ON o.id = os.id_object
            LEFT JOIN {$db_name_sod}.offices r ON r.id = os.id_office
            LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
            LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ( ns.id = os.id_service )
            LEFT JOIN {$db_name_storage}.measures m ON (m.id = ns.id_measure)
            WHERE os.to_arc = 0
                AND os.id_object = {$nIDObject}
        ";

        $aData = $this->select($sQuery);

        foreach ( $aData as $val ) {
            $aPayMon = explode("-", $val['payment_date']);

            if ( !isset($aPayMon[2]) ) {
                continue;
            }

            $day 		= intval($aPayMon[2]);
            $month 		= intval($aPayMon[1]);
            $year 		= intval($aPayMon[0]);
            $pay_days	= 0;

            if ( $bPays === true ) {
                $aRealPay 	= explode("-", $val['real_paid']);
                $aLastPay 	= explode("-", $val['last_paid']);

                $dayTo1 	= intval($aRealPay[2]);
                $monthTo1 	= intval($aRealPay[1]);
                $yearTo1 	= intval($aRealPay[0]);

                $nRControReal	= date("Ym", mktime(0, 0, 0, $monthTo1, $dayTo1, $yearTo1));
                $nRControlAppl	= date("Ym", mktime(0, 0, 0, $month, $day, $year));

                // Заден месец от търсения месец
                $nPrevDate	= date("Ym", mktime(0, 0, 0, $monthTo - 1, 1, $yearTo));

                // Пропуснат фактуриран месец
                if ( $nRControlAppl < $nPrevDate ) {
                    return array();
                }

                // Имаме издадена фактура, но нямаме пълно изплащане. Преверка за 5 лв. праг!
                if ( $nRControlAppl > $nRControReal && $aLastPay[0] > 2000 ) {
                    $nRem 	= $oSaleDoc->getObjectRemainingPriceForMonth($nIDObject, $ts);

                    if ( !is_numeric($nRem) || $nRem > 5 ) {
                        continue;
                    }
                }
            }

            // Приемаме, че целия месец е платен на първо число АКО старт = 0!
            if ( $val['start'] == 0 ) {
                if ( $day == 1 ) {
                    $month++;
                }

                if ( $month > 12 ) {
                    $month = 1;
                    $year++;
                }
            }

            $i = 0;

            while ( mktime(0, 0, 0, $month + $i, 1, $year) <= mktime(0, 0, 0, $monthTo, 1, $yearTo) ) {
                $aTmp		= array();

                $currentYM 	= date("Ym", mktime(0, 0, 0, $month + $i, 1, $year));
                $lastday	= date("d", mktime(0, 0, 0, $month + $i + 1, 0, $year));
                $cLast		= $lastday;

                if ( $val['end_date'] != 0 ) {
                    if ( $currentYM == date("Ym", $val['end_date']) ) {
                        if ( date("d", $val['end_date']) == 1 ) {
                            $i++;
                            continue;
                        } else {
                            $cLast 	= date("d", $val['end_date']) - 1;
                        }
                    } elseif ( $currentYM > date("Ym", $val['end_date']) ) {
                        $i++;
                        continue;
                    }
                }

                if ( $day != 1 ) {
                    $pay_days 	= $cLast - $day + 1;

                    if ( $pay_days <= 0 ) {
                        return [];
                    }

                    $nTotalSum += floatval((($pay_days * $val['payment_sum']) / $lastday) / $this->getVatCoefficient($val['vat']));

                    $aTmp['id_duty']				= $val['id'];
                    $aTmp['id_object'] 				= $nIDObject;
                    $aTmp['id_office']	            = $val['id_office'];
                    $aTmp['region']	                = $val['region'];
                    $aTmp['id_firm']	            = $val['id_firm'];
                    $aTmp['firm']	                = $val['firm'];
                    $aTmp['id_service'] 			= $val['id_service'];
                    $aTmp['month'] 					= $val['payment_date'];
                    $aTmp['service_name'] 			= $val['name'];
                    $aTmp['object_name'] 			= $val['object_name'];
                    $aTmp['view_type_detail']       = $val['original_nomenclature_name'] . ": за м. " . join('.', array_reverse(explode('-', substr($aTmp['month'], 0, -3)))) . " г.";
                    $aTmp['view_type_by_services']  = $val['original_nomenclature_name'];
                    $aTmp['single_price'] 			= floatval((($pay_days * $val['single_price']) / $lastday) / $this->getVatCoefficient($val['vat']));
                    $aTmp['quantity'] 				= floatval($val['quantity']);
                    $aTmp['total_sum'] 				= floatval((($pay_days * $val['payment_sum']) / $lastday) / $this->getVatCoefficient($val['vat']));
                    $aTmp['total_sum_with_dds'] 	= floatval(($pay_days * $val['payment_sum']) / $lastday );
                    $aTmp['payed']					= floatval(0);
                    $aTmp['type']					= "month";
                    $aTmp['for_payment']			= true;
                    $aTmp['vat']                    = $val['vat'];
                    $aTmp['measure']                 = $val['measure'];
                    //die(print("<pre>" . print_r($aTmp, true) . "</pre>"));

                } else {
                    $nTotalSum += floatval((($cLast * $val['payment_sum']) / $lastday) / $this->getVatCoefficient($val['vat']));

                    $aTmp['id_duty']				= $val['id'];
                    $aTmp['id_object'] 				= $nIDObject;
                    $aTmp['id_office']	            = $val['id_office'];
                    $aTmp['region']	                = $val['region'];
                    $aTmp['id_firm']	            = $val['id_firm'];
                    $aTmp['firm']	                = $val['firm'];
                    $aTmp['id_service'] 			= $val['id_service'];
                    $aTmp['month'] 					= date("Y-m-d", mktime(0, 0, 0, $month + $i, 1, $year));
                    $aTmp['service_name'] 			= $val['name'];
                    $aTmp['object_name'] 			= $val['object_name'];
                    $aTmp['view_type_detail']       = $val['original_nomenclature_name'] . ": за м. " . join('.', array_reverse(explode('-', substr($aTmp['month'], 0, -3)))) . " г.";
                    $aTmp['view_type_by_services']  = $val['original_nomenclature_name'];
                    $aTmp['single_price'] 			= floatval((($cLast * $val['single_price']) / $lastday) / $this->getVatCoefficient($val['vat']));
                    $aTmp['quantity'] 				= floatval($val['quantity']);
                    $aTmp['total_sum'] 				= floatval((($cLast * $val['payment_sum']) / $lastday) / $this->getVatCoefficient($val['vat']));
                    $aTmp['total_sum_with_dds'] 	= floatval(($cLast * $val['payment_sum']) / $lastday);
                    $aTmp['payed']					= floatval(0);
                    $aTmp['type']					= "month";
                    $aTmp['for_payment']			= true;
                    $aTmp['vat']                    = $val['vat'];
                    $aTmp['measure']                = $val['measure'];
                    //die(print("<pre>" . print_r($aTmp, true) . "</pre>"));

                }

                $aDuty[] 	= $aTmp;

                $day 		= 1;

                $i++;
            }
        }

        if ( $nTotalSum <= 0 ) {
            //die(print("<pre>" . print_r($aTmp, true) . "</pre>"));
            return [];
        }

        $array_map = array_map('strtotime', array_column($aDuty, 'month'));
        array_multisort($array_map, SORT_ASC, array_column($aDuty, 'total_sum'), SORT_DESC, $aDuty);
        unset($array_map);
        //die(print("<pre>" . print_r($aDuty, true) . "</pre>"));
        return $aDuty;
    }

    private function getJurNameByServiceID($nID) {
        global $db_name_sod;

        $sQuery = "
            SELECT
                f.jur_name,
                f.address,
                f.idn,
                f.idn_dds,
                f.jur_mol,
                f.id_office_dds as dds
            FROM {$db_name_sod}.objects_services os
            LEFT JOIN {$db_name_sod}.objects o ON o.id = os.id_object
            LEFT JOIN {$db_name_sod}.offices off ON off.id = o.id_office
            LEFT JOIN {$db_name_sod}.firms f ON f.id = off.id_firm
            WHERE os.id = {$nID}
        ";

        return $this->selectOnce($sQuery);
    }

    private function getConcession($nMonths) {
        $oConcession	= new DBConcession();
        $nIDConcession	= 0;
        $nCurrent		= 0;

        $aConcession = $oConcession->getAll();

        foreach ( $aConcession as $val ) {
            $month = isset($val['months_count']) ? $val['months_count'] : 0;

            if ( ($nMonths >= $month) && ($nCurrent < $month) ) {
                $nCurrent  		= $month;
                $nIDConcession	= isset($val['id']) ? $val['id'] : 0;
            }
        }

        return $nIDConcession;
    }

    public function serviceMonthCaption($aMonthDuty) {
        $aClient = $this->getClient();
        $isCaption = $aClient['invoice_last_paid_caption'] ?? 1;

        if ( !$isCaption ) {
            return $aMonthDuty;
        }

        $uniqueMonths = array_unique(
            array_map(
                function($month) {
                    return join('.', array_reverse(explode('-', substr($month, 0, -3))));
                },
                array_column(
                    array_filter(
                        $aMonthDuty,
                        function($service) {
                            return $service['type'] == 'month';
                        }
                    )
                    ,'month'
                )
            )
        );

        usort($uniqueMonths, "sortAB");

        if (!empty($uniqueMonths)) {
            count($uniqueMonths) > 1
                ? $view_type_by_services_suffix = ': до м. ' . end($uniqueMonths) . ' г. вкл.'
                : $view_type_by_services_suffix = ': за ' . $uniqueMonths[0] . ' г.';
            
            $this->monthlyCaption = $view_type_by_services_suffix;

            foreach($aMonthDuty as $key => $value) {
                if ($aMonthDuty[$key]['type'] != 'month') {
                    continue;
                }
                $aMonthDuty[$key]['view_type_by_services'] = $aMonthDuty[$key]['view_type_by_services'] . $view_type_byby_services_suffix;
            }
        }

        unset($uniqueMonths);

        return $aMonthDuty;
    }

    public function getDutyByOneObject($nIDObject) {
        global $db_finance, $db_name_sod, $db_name_finance, $db_name_storage;

        $oSaleDoc = new NEWDBSalesDocRows();
        $dutyDate = date("Y-m-01");

        $isCredit       = false;
        $isConcession 	= false;

        if ( func_num_args() > 1 ) {
            $dateArgument = func_get_arg(1);

            $tmpDate = DateTime::createFromFormat('Y-m-d', $dateArgument);

            if ( $tmpDate !== false ) {
                $dutyDate = $tmpDate->format('Y-m-01');
            }
        }

        $aClient = $this->getClient();

        if ( empty($aClient) ) {
            $aClient = $this->getClientByObjectID($nIDObject);
            $this->setClient($aClient);
        }

        // Проверка за стари задължения
        if ( !empty($nIDClient) ) {
            $unpaidDocs = $oSaleDoc->getUnpaidDocsByClient($nIDClient, date('Y-m-d'));

            if ( !empty($unpaidDocs) ) {
                $this->setAlert("Клиента има непогасени стари задължения!!!");
            }
        }

        $aServices = [];
        $aConcession = [];

        // Основна логика
        $aMonthDuty = $this->getDutyByObject($nIDObject, $dutyDate);
        $aSingleDuty = $this->getSinglesDutyByObject($nIDObject, $dutyDate);


        $aMonthDuty = $this->serviceMonthCaption($aMonthDuty);

        //die(print("<pre>".print_r($aMonthDuty,true)."</pre>"));
        
        foreach ($aMonthDuty as $service) {
            $aServices[] = $service;

            // Отстъпки
            $time = strtotime($service['month']);

            $dNow = mktime(0, 0, 0, date("m"), 1, date("Y"));

            $dCon = mktime(0, 0, 0, date("m", $time), 1, date("Y", $time));

            if ($dCon >= $dNow) {
                $aConcession[$service['id_duty']][] = $service['month'];

            }
        }

        unset($service);

        foreach ($aConcession as $key => $val) {
            $cnt = count($val);

            $aTmp = array();

            $nIDConcession = $this->getConcession($cnt);

            if ($nIDConcession) {
                //$oDBOS      = new DBBase2($db_sod, 'objects_services');
                $oDBConces  = new DBBase2($db_finance, 'concession');


                $sQuery = "
                        SELECT
                           os.*,
                           IF ( char_length(o.invoice_name), o.invoice_name, o.name ) as object_name,
                           r.name as region,
                           f.id as id_firm,
                           f.name as firm,
                           ns.vat_tax as vat,
                           m.code as measure,
                           ns.for_smartsot,
                           ns.name AS 'original_nomenclature_name'
                        FROM {$db_name_sod}.objects_services os
                        JOIN {$db_name_sod}.offices r ON r.id = os.id_office
                        JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
                        JOIN {$db_name_finance}.nomenclatures_services ns ON ( ns.id = os.id_service )
                        JOIN {$db_name_sod}.objects o on os.id_object = o.id
                        LEFT JOIN {$db_name_storage}.measures m ON (m.id = ns.id_measure)
                        WHERE os.id = {$key}
                    ";

                $aData = $this->selectOnce($sQuery);

                //$aData = $oDBOS->getRecord($key);
                $aGrrr = $oDBConces->getRecord($nIDConcession);


                $isConcession = true;

                $aTmp['id'] = 0;
                $aTmp['reference_service_id'] = $aData['id_service'];
                $aTmp['reference_service_name'] = $aData['service_name'];
                $aTmp['id_duty'] = $key;
                $aTmp['id_object'] = $aData['id_object'];
                $aTmp['id_office'] = $aData['id_office'];
                $aTmp['region'] = $aData['region'];
                $aTmp['id_firm'] = $aData['id_firm'];
                $aTmp['firm'] = $aData['firm'];
                $aTmp['id_service'] = $aGrrr['id_service'];
                $aTmp['month'] = date("Y-m") . "-01";
                $aTmp['object_name'] = $aData['object_name'];
                $aTmp['service_name'] = $aData['total_sum'] < 0 ? "Корекция: [ " . $aData['service_name'] . " ]" : $aGrrr['name'] . " [ " . $aData['service_name'] ." ]";
                $aTmp['view_type_detail'] = $aData['for_smartsot'] ? $aGrrr['name'] . " [ Смарт СОТ ]" : $aGrrr['name'] ." [ ".$aData['original_nomenclature_name'] ." ]";
                //$aTmp['view_type_by_services'] = $aData['for_smartsot'] ? 'Отстъпка [ Смарт СОТ ]' : "Отстъпка". " [ " .$aData['original_nomenclature_name'] . " ]";
                $aTmp['view_type_by_services'] = 'Отстъпка';
                $aTmp['single_price'] = floatval((($aData['total_sum'] / $this->getVatCoefficient($aData['vat'])) * -1 * $cnt * $aGrrr['percent']) / 100);
                $aTmp['quantity'] = 1;
                $aTmp['total_sum'] = floatval((($aData['total_sum'] / $this->getVatCoefficient($aData['vat'])) * -1 * $cnt * $aGrrr['percent']) / 100);
                $aTmp['total_sum_with_dds'] = floatval($aData['total_sum'] * -1 * $cnt  * $aGrrr['percent']) / 100;
                $aTmp['concession_month_count'] = $aGrrr['months_count'];
                $aTmp['payed'] = floatval(0);
                $aTmp['type'] = "free";
                $aTmp['for_payment'] = true;
                $aTmp['vat'] = $aData['vat'];
                $aTmp['measure'] = $aData['measure'];
                $aTmp['for_smartsot'] = $aData['for_smartsot'];
                $aTmp['percent'] = $aGrrr['percent'];
                //die(print("<pre>".print_r($aTmp,true)."</pre>"));
                $aServices[] = $aTmp;
            }
        }

        foreach ($aSingleDuty as $service) {
            $aServices[] = $service;
        }

        if ($isConcession) {
            $this->setAlert("Има предложени отстъпки!!!");
        }

        unset($service);

        return $aServices;

    }

    public function getDuty($nIDClient, $sDelivererName) {
        global $db_sod, $db_finance, $db_name_sod, $db_name_finance, $db_name_storage;

        $oSaleDoc = new NEWDBSalesDocRows();
        $dutyDate = date("Y-m-01");

        $isCredit       = false;
        $isConcession 	= false;

        if ( empty($nIDClient) ) {
            return [];
        }

        if ( func_num_args() > 2 ) {
            $dateArgument = func_get_arg(2);

            $tmpDate = DateTime::createFromFormat('Y-m-d', $dateArgument);

            if ( $tmpDate !== false ) {
                $dutyDate = $tmpDate->format('Y-m-01');
            }
        }


        $aObjects   = $this->getObjectsByClient($nIDClient);


        $aClient = $this->getClient();

        if ( empty($aClient) ) {
            $aClient = $this->getClientByID($nIDClient);
            $this->setClient($aClient);
        }

        // Проверка за стари задължения
        $unpaidDocs = $oSaleDoc->getUnpaidDocsByClient($nIDClient, date('Y-m-d'));

        if ( !empty($unpaidDocs) ) {
            $this->setAlert("Клиента има непогасени стари задължения!!!");
        }

        $aServices = [];
        $aConcession = [];

        // Основна логика
        foreach ( $aObjects as $clientObject ) {
            $nIDObject 	= $clientObject['id'];

            if ( !$isCredit ) {
                $aMonthDuty = $this->getDutyByObject($nIDObject, $dutyDate);
                $aSingleDuty = $this->getSinglesDutyByObject($nIDObject, $dutyDate);


                // view_type_by_object_services за изглед по обекти и услуги за триене ?

                foreach ($aMonthDuty as $service) {

                    //$service['view_type_by_object_services'] = $service['view_type_by_object_services'] . $monthlyPeriodText;
                    $aJur = $this->getJurNameByServiceID($service['id_duty']);
                    $sJurName = $aJur['jur_name'];

                    $aJurNames[$sJurName] = $sJurName;

                    if ($sJurName == $sDelivererName) {
                        $aServices[] = $service;

                        // Отстъпки
                        $time = strtotime($service['month']);

                        $dNow = mktime(0, 0, 0, date("m"), 1, date("Y"));

                        $dCon = mktime(0, 0, 0, date("m", $time), 1, date("Y", $time));

                        if ($dCon >= $dNow) {
                            $aConcession[$service['id_duty']][] = $service['month'];

                        }
                    }
                }

                unset($service);

                foreach ($aConcession as $key => $val) {
                    $cnt = count($val);

                    $aTmp = array();

                    $nIDConcession = $this->getConcession($cnt);

                    if ($nIDConcession) {
                        //$oDBOS      = new DBBase2($db_sod, 'objects_services');
                        $oDBConces  = new DBBase2($db_finance, 'concession');


                        $sQuery = "
                        SELECT
                           os.*,
                           IF ( char_length(o.invoice_name), o.invoice_name, o.name ) as object_name,
                           r.name as region,
                           f.id as id_firm,
                           f.name as firm,
                           ns.vat_tax as vat,
                           m.code as measure,
                           ns.for_smartsot,
                           ns.name AS 'original_nomenclature_name'
                        FROM {$db_name_sod}.objects_services os
                        JOIN {$db_name_sod}.offices r ON r.id = os.id_office
                        JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
                        JOIN {$db_name_finance}.nomenclatures_services ns ON ( ns.id = os.id_service )
                        JOIN {$db_name_sod}.objects o on os.id_object = o.id
                        LEFT JOIN {$db_name_storage}.measures m ON (m.id = ns.id_measure)
                        WHERE os.id = {$key}
                    ";

                        $aData = $this->selectOnce($sQuery);

                        //$aData = $oDBOS->getRecord($key);
                        $aGrrr = $oDBConces->getRecord($nIDConcession);


                        $isConcession = true;

                        $aTmp['id'] = 0;
                        $aTmp['reference_service_id'] = $aData['id_service'];
                        $aTmp['reference_service_name'] = $aData['service_name'];
                        $aTmp['id_duty'] = $key;
                        $aTmp['id_object'] = $aData['id_object'];
                        $aTmp['id_office'] = $aData['id_office'];
                        $aTmp['region'] = $aData['region'];
                        $aTmp['id_firm'] = $aData['id_firm'];
                        $aTmp['firm'] = $aData['firm'];
                        $aTmp['id_service'] = $aGrrr['id_service'];
                        $aTmp['month'] = date("Y-m") . "-01";
                        $aTmp['object_name'] = $aData['object_name'];
                        $aTmp['service_name'] = $aData['total_sum'] < 0 ? "Корекция: [ " . $aData['service_name'] . " ]" : $aGrrr['name'] . " [ " . $aData['service_name'] ." ]";
                        $aTmp['view_type_detail'] = $aData['for_smartsot'] ? $aGrrr['name'] . " [ Смарт СОТ ]" : $aGrrr['name'] ." [ ".$aData['original_nomenclature_name'] ." ]";
                        //$aTmp['view_type_by_services'] = $aData['for_smartsot'] ? 'Отстъпка [ Смарт СОТ ]' : "Отстъпка". " [ " .$aData['original_nomenclature_name'] . " ]";
                        $aTmp['view_type_by_services'] = 'Отстъпка';
                        $aTmp['single_price'] = floatval((($aData['total_sum'] / $this->getVatCoefficient($aData['vat'])) * -1 * $cnt * $aGrrr['percent']) / 100);
                        $aTmp['quantity'] = 1;
                        $aTmp['total_sum'] = floatval((($aData['total_sum'] / $this->getVatCoefficient($aData['vat'])) * -1 * $cnt * $aGrrr['percent']) / 100);
                        $aTmp['total_sum_with_dds'] = floatval($aData['total_sum'] * -1 * $cnt  * $aGrrr['percent']) / 100;
                        $aTmp['concession_month_count'] = $aGrrr['months_count'];
                        $aTmp['payed'] = floatval(0);
                        $aTmp['type'] = "free";
                        $aTmp['for_payment'] = true;
                        $aTmp['vat'] = $aData['vat'];
                        $aTmp['measure'] = $aData['measure'];
                        $aTmp['for_smartsot'] = $aData['for_smartsot'];
                        $aTmp['percent'] = $aGrrr['percent'];
                        //die(print("<pre>".print_r($aTmp,true)."</pre>"));
                        $aServices[] = $aTmp;
                    }
                }

                $aConcession = [];

                
                foreach ($aSingleDuty as $service) {
                    $aServices[] = $service;
                }

                unset($service);

                //Todo: еднократни задължения
            } else {
                // Todo: кредити
            }
        }

        // Отстъпки
        if ( !$isCredit ) {
            if ($isConcession) {
                $this->setAlert("Има предложени отстъпки!!!");
            }
        }

        // valkata bre =>  темп шитс докато се върне павката... и го доизмисли
        $aServices = $this->serviceMonthCaption($aServices);

        
        return $aServices;
    }

    public function makeOrder() {
        $post = json_decode(file_get_contents('php://input'), true);

        if ( !isset($post['doc_id']) || !isset($post['bank_account_id']) ) { //|| !isset($post['order_sum'])
            http_response_code(400);
            die ("400 Bad Request");
        }

        $nID 	        = $post['doc_id'] ?? 0;
        $nIDAccount 	= $post['bank_account_id'] ?? 0;
        $orderSum    	= $post['order_sum'] ?? 0;     //sprintf("%01.2f", $post['order_sum'])

        if ( !$this->isValidID($nID) ) {
            return $this->setError("Невалиден документ!");
        }

        // Todo: кредити!!!
        //$isCredit = $oSaleDocRows->checkForCredit($nID);


        //if ( empty($forPay) && $isCredit ) {
        //    return $this->setError("Няма избрано плащане по кредит!");
        //}

        $oPay = new NEWDBFinanceOperations();
        $oPay->makeOrder($nID, $nIDAccount, $orderSum);

        $alerts = $oPay->getAlerts();

        if ( !empty($alerts) ) {
            $this->alerts = $alerts;
        }

        $err = $oPay->getError();

        if ( !empty($err) ) {
            return $this->setError($err);
        }

        return ['status' => 'OK'];  //$this->getOrdersByDoc($nID);
    }

    public function makeAdvice() {
        $post = json_decode(file_get_contents('php://input'), true);
        //die(print("<pre>".print_r($post,true)."</pre>"));
        if (!isset($post['document_data']) || !isset($post['document_rows'])) {
            return $this->setError("Невалиден документ!");
        }

        if ( !isset($post['document_data']['doc_type']) || !in_array($post['document_data']['doc_type'], ["debitno izvestie", "kreditno izvestie"]) ) {
            return $this->setError("Документа не е от тип известие!");
        }

        $nID = $post['document_data']['id'] ?? 0;

        if ( empty($nID) ) {
            return $this->setError("Невалиден документ!");
        }

        // проверка за надвишаване на сумата
        if ( $post['document_data']['doc_type'] == "kreditno izvestie" ) {
            $advices = $this->getCreditAdvices($nID);
            $document = $this->getDocData($nID);

            $totalSum = 0;

            foreach ( $advices as $advice ) {
                if ($advice['doc_status'] == 'canceled') {
                    continue;
                }
                $totalSum += $advice['total_sum'];
            }

            foreach ( $post['document_rows'] as $row ) {
                $totalSum += ($row['total_sum_with_dds'] * -1);
            }

            if ( (sprintf("%01.2f", $totalSum) * -1) > $document['total_sum'] + 0.05 ) {
                return $this->setError("Сумата на издадените кредитни известия \nе по-голяма от сумата на документа!");
            }
        }

        // Ново известие
        $post['document_data']['id_advice'] = 0;
        $post['document_data']['is_advice'] = 0;
        $post['document_data']['id_advice'] = $nID;
        $post['document_data']['id'] = 0;

        return $this->store($post);
    }

    public function updateClient() {
        global $db_finance, $db_name_finance;

        $post = json_decode(file_get_contents('php://input'), true);

        if ( !isset($post['doc_id']) || !isset($post['client_ein']) || !isset($post['id_client']) ) {
            return $this->setError("Невалиден документ!");
        }

        if ( !$this->isValidID($post['doc_id']) ) {
            return $this->setError("Невалиден документ: {$post['doc_id']}");
        }

        $document = $this->getDocData($post['doc_id']);

        if ( empty($document) || !isset($document['id']) ) {
            return $this->setError("Невалиден документ: {$post['doc_id']}");
        }

        if ( $document['doc_status'] == "canceled" ) {
            return $this->setError("Документа вече е анулиран!");
        }

        $oDocument = new DBMonthTable($db_name_finance,PREFIX_SALES_DOCS,$db_finance);

        $document['id_client']          = $post['id_client'] ?? 0;
        $document['client_ein']         = $post['client_ein'] ?? '';
        $document['client_ein_dds']     = $post['client_ein_dds'] ?? '';
        $document['client_mol']         = $post['client_mol'] ?? '';
        $document['client_name']        = $post['client_name'] ?? '';
        $document['client_recipient']   = $post['client_recipient'] ?? '';
        $document['client_address']     = $post['client_address'] ?? '';
        $document['id_city']            = $post['id_city'] ?? 0;

        $oDocument->update($document);

        return ["document_data" => $document];
    }

    public function annulmentOrder() {
        $post = json_decode(file_get_contents('php://input'), false);

        $nID = $post->id ?? 0;

        if ( !$this->isValidID($nID) ) {
            return $this->setError("Невалиден документ {$nID}!");
        }

        // Todo: order_status?
        $oPay = new NEWDBFinanceOperations();
        $oPay->annulment($nID);

        $alerts = $oPay->getAlerts();

        if ( !empty($alerts) ) {
            $this->alerts = $alerts;
        }

        $error = $oPay->getError();

        if ( !empty($error) ) {
            return $this->setError($error);
        }

        return $this->getOrdersByDoc($nID);
    }

    public function store( $post = null ) {
        global $db_sod, $db_system, $db_finance, $db_name_finance, $db_name_sod, $db_name_system;

        if ( $post == null ) {
            $post = json_decode(file_get_contents('php://input'), true);

            if ( !isset($post['document_data']) || !isset($post['document_rows']) ) {
                return $this->setError("Грешка при опит за създаване на документ!");
            }
        }

        $document = $post['document_data'] ?? [];
        $docRows = $post['document_rows'] ?? [];
        $nIDUser = isset($_SESSION['userdata']['id_person']) ?: 0;
        $nID = $document['id'] ?? 0;
        $view_type = null;
        $totalSum = 0;

        // Todo: tekushta sesiq
        $oFirms 		= new DBFirms();
        $oOffices		= new DBOffices();
        $oMonths		= new DBObjectServices();
        $oSingles		= new DBObjectsSingles();

        $oDocument = new DBMonthTable($db_name_finance,PREFIX_SALES_DOCS,$db_finance);
        $oDocRows = new DBMonthTable($db_name_finance,PREFIX_SALES_DOCS_ROWS,$db_finance);


        // Право за редакция - добавяме и право за преглед
        $docEditRight = in_array('sale_doc_edit', $_SESSION['userdata']['access_right_levels']);

        if ( empty($docEditRight) ) {
            return $this->setError("Нямате достатъчно права за операцията!");
        }

        if ( empty($docRows) ) {
            return $this->setError("Документа е празен!");
        }

        if ( $document['doc_type'] != "oprostena" ) {
            if (empty($document['client_name']) || empty($document['client_ein']) || empty($document['id_client'])) {
                return $this->setError("Въведете коректен клиент!");
            }
        }

        if ( $document['is_book'] == 1 && $document['doc_type'] != "faktura" ) {
            return $this->setError("Типа на документа от кочан \nтрябва да бъде ФАКТУРА!!");
        }

        foreach ( $docRows as $key => $row ) {
            $num = ++$key;
            $service_name = $row['service_name'];

            if ( isset($row['for_payment']) && !empty($row['for_payment']) ) {
                //if ( $row['total_sum'] > 200000 ) {
                //    return $this->setError("Проблем с приемането на данните!\nСвържете се с администратор!!!");
                //}

                if ( !isset($row['id_office']) || empty($row['id_office']) ) {
                    return $this->setError("Услуга на ред {$num} - {$service_name}\nв ДЕТАЙЛЕН изглед \nсе нуждае от уточняване на региона!!!");
                }

                if ( $row['type'] == "credit" ) {
                    $view_type = "extended";
                }
            }

            $totalSum += $row['total_sum'];
        }

        if ( $document['doc_type'] == "faktura" || $document['doc_type'] == "debitno izvestie" || $document['doc_type'] == "kvitanciq" ) {
            if ($totalSum < 0) {
                return $this->setError("Фактура с отрицателна стойност не може да бъде издадена!");
            }
        }

        //if ( empty($totalSum) ) {
        //    return $this->setError("Фактура с нулева стойност не може да бъде издадена!");
        //}

        $db_finance->StartTrans();
        $db_system->StartTrans();
        $db_sod->StartTrans();

        try {
            if ( empty($nID) ) {
                $jurName = $document['deliverer_name'] ?? "";

                if ( in_array($document['doc_type'], ["faktura", "kreditno izvestie", "debitno izvestie"]) ) {
                    if ( $document['is_book'] == 1 ) {
                        if ( !isset($document['doc_num']) || empty($document['doc_num']) ) {
                            throw new Exception("Въведете номер на фактура от кочан!", DBAPI_ERR_FAILED_TRANS);
                        }

                        $oBooks = new DBBooks();
                        $aBooks	= $oBooks->getRowByNum($document['doc_num']);

                        if ( isset($aBooks['id']) && !empty($aBooks['id']) ) {
                            // Имаме регистриран кочан с такъв диапазон
                            if ( isset($aBooks['is_use']) && empty($aBooks['is_use']) ) {
                                // Номера е свободен!!!
                                $nLastInvoiceNum = $document['doc_num'];

                                $aBook = [];
                                $aBook['id'] = $aBooks['id'];
                                $aBook['is_use'] = 1;

                                $ob = new DBBase2($db_finance, 'books');
                                $ob->update($aBook);
                            } else {
                                throw new Exception("Въведения номер за фактура вече е използван!!!", DBAPI_ERR_INVALID_PARAM);
                            }
                        } else {
                            throw new Exception("Няма регистриран кочан с такъв диапазон!!!", DBAPI_ERR_INVALID_PARAM);
                        }

                    } else {
                        $oRes = $db_sod->Execute("SELECT last_num_sale_doc FROM {$db_name_sod}.firms WHERE LOWER(jur_name) = LOWER('{$jurName}') AND to_arc = 0 LIMIT 1 FOR UPDATE");
                        $nLastInvoiceNum = !empty($oRes->fields['last_num_sale_doc']) ? $oRes->fields['last_num_sale_doc'] + 1 : 0;
                    }
                } else {
                    $oRes = $db_system->Execute("SELECT last_num_receipt FROM {$db_name_system}.system FOR UPDATE");
                    $nLastInvoiceNum = !empty($oRes->fields['last_num_receipt']) ? $oRes->fields['last_num_receipt'] + 1 : 0;
                }

                $document['doc_num'] = $nLastInvoiceNum;
                $document['doc_date'] = $document['doc_date'] ?: time();
                $document['doc_type'] = $document['doc_type'] ?: "faktura";
                $document['doc_status'] = "final";
                $document['client_recipient'] = $document['client_recipient'] ?: $document['client_mol'];
                $document['total_sum'] = 0;
                $document['orders_sum'] = 0;
                $document['last_order_id'] = 0;
                $document['last_order_time'] = "0000-00-00 00:00:00";
                $document['paid_type'] = $document['paid_type'] ?: "bank";

                $document['view_type'] = $view_type ?? $document['view_type'];
                $document['version'] = 2;
                $document['created_user'] = $nIDUser;
                $document['created_time'] = $document['doc_date_create'] ?: time();
                $document['updated_user'] = $nIDUser;
                $document['updated_time'] = time();

                $oDocument->update($document);

                if ( date("Y-m-d") != $document['doc_date_create'] ) {
                    $document['created_time'] = $document['doc_date_create'] ?: time();
                }

                $oDocument->update($document);

                $nID = $document['id'];

                if ( in_array($document['doc_type'], ["faktura", "kreditno izvestie", "debitno izvestie"]) ) {
                    if ( $document['is_book'] != 1 ) {
                        $db_sod->Execute("UPDATE {$db_name_sod}.firms SET last_num_sale_doc = {$nLastInvoiceNum} WHERE LOWER(jur_name) = LOWER('{$jurName}')");
                    }
                } else {
                    $db_system->Execute("UPDATE {$db_name_system}.system SET last_num_receipt = {$nLastInvoiceNum}");
                }

                if ( in_array($document['doc_type'], ["debitno izvestie", "kreditno izvestie"]) ) {
                    $nIDParent = $document['id_advice'] ?? 0;

                    if ( !empty($nIDParent) ) {
                        $oParent = new DBSalesDocs();
                        $oParent->getRecord($nIDParent, $aParent);

                        if ( !empty($aParent) ) {
                            $aParent['is_advice'] = 1;
                            $aParent['id_advice'] = $nID;

                            $oDocument->update($aParent);
                        }
                    } else {
                        throw new Exception("Невалиден документ {$nIDParent}!", DBAPI_ERR_FAILED_TRANS);
                    }
                }

                $nTotal         = 0;
                $s_dds			= false;
                $s_normal		= false;

                foreach ( $docRows as $key => $row ) {
                    if ( $row['for_payment'] ) {
                        $is_dds 	= 0;
                        $nTotal     += $row['total_sum_with_dds'];

                        // Номенклатура ДДС
                        if ( $row['id_service'] == -1 ) {
                            $aFirm 		= $oFirms->getFirmByIDOffice($row['id_office']);
                            $nIDOffice 	= isset($aFirm['id_office_dds']) 			? $aFirm['id_office_dds'] 					: 0;
                            $nIDFirm	= $oFirms->getFirmByOffice($nIDOffice);
                            $sFirm 		= $oOffices->getFirmNameByIDOffice($nIDOffice);

                            $obj['id_service'] 		= 0;
                            $obj['service_name']	= ".:: ДДС ::.";
                            $obj['object_name']		= ".:: ДДС ::. - ".$sFirm;
                            $is_dds 				= 2;
                            $s_dds					= true;

                            if ( $document['doc_type'] != "oprostena" ) {
                                throw new Exception("В описа на документа има номенклатура ДДС!\nМоля, изберете \"Квитанция\"!", DBAPI_ERR_FAILED_TRANS);
                            }

                        } else {
                            $s_normal 				= true;
                        }

                        if ( $s_dds && $s_normal ) {
                            throw new Exception("В документа има комбинация от услуги и ДДС!!!", DBAPI_ERR_INVALID_PARAM);
                        }

                        $nTempOffice 	= (int) $row['id_office'];
                        //$nTempFirm	 	= $oFirms->getFirmByOffice($nTempOffice);

                        $row['id'] = 0;
                        $row['id_sale_doc'] = $nID;
                        $row['id_duty_row'] = $row['id_duty'];
                        //$row['measure'] = $row['measure_code'];
                        $row['is_dds'] = $is_dds;

                        if ( $document['doc_type'] == "oprostena" ) {
                            $row['total_sum_with_dds'] = $row['total_sum'];
                        } else if ( $document['doc_type'] == "kreditno izvestie" ) {
                            $row['total_sum_with_dds'] *= -1;
                            $row['total_sum'] *= -1;
                            $row['single_price'] *= -1;
                        }

                        $row['updated_user'] = $this->getPerson();
                        $row['updated_time'] = time();

                        $oDocRows->update($row, null, null, $nID);

                        $docRows[$key] = $row;

                        // Падежи
                        if ( isset($row['id_duty_row']) && !empty($row['id_duty_row']) ) {
                            $nIDRow = $row['id_duty_row'];
                            $month 	= $row['month'];
                            $type 	= $row['type'];

                            if ( $type == "month" ) {
                                $aServ = $oMonths->getRecord($nIDRow);

                                if ( $aServ['last_paid'] < substr($month, 0, 7)."-01" ) {
                                    $aUpdateData                = array();
                                    $aUpdateData['id']          = $nIDRow;
                                    $aUpdateData['last_paid']   = substr($month, 0, 7) . "-01";

                                    $oMonths->update($aUpdateData);
                                }
                            } else if ( $type == "single" || $type == "credit" ) {
                                $aUpdateData 				= array();
                                $aUpdateData['id'] 			= $nIDRow;
                                $aUpdateData['paid_date'] 	= $month;
                                $aUpdateData['id_sale_doc'] = $nID;

                                $oSingles->update($aUpdateData);
                            }
                        }
                    }
                }

                $setDDS = $this->calculateDDS($nID, $db_finance);

                if ( isset($setDDS['error']) && !empty($setDDS['error']) ) {
                    throw new Exception($setDDS['error'], DBAPI_ERR_FAILED_TRANS);
                }
            }

            $db_finance->CompleteTrans();
            $db_system->CompleteTrans();
            $db_sod->CompleteTrans();
        } catch (Exception $ex) {
            $db_finance->FailTrans();
            $db_system->FailTrans();
            $db_sod->FailTrans();

            return $this->setError($ex->getMessage());
        }

        return ["document_data" => $document, "document_rows" => $docRows];
    }

    public function calculateDDS($nID, $db_finance_session = null) {
        global $db_finance, $db_name_finance;

        if ( $db_finance_session !== null ) {
            $db_finance = $db_finance_session;
        }

        $oFirms 		= new DBFirms();
        $oDocument      = new DBMonthTable($db_name_finance,PREFIX_SALES_DOCS,$db_finance);
        $oDocRows       = new DBMonthTable($db_name_finance,PREFIX_SALES_DOCS_ROWS,$db_finance);

        $aData			= [];
        $vat            = [];
        $nTotalSum		= 0;
        $nPaidSum		= 0;
        $doc_type       = "";
        $doc_date       = "";
        $ein            = "";
        $nDDS           = 0;

        if ( !$this->isValidID($nID) ) {
            return $this->setError("Невалиден документ!");
        }

        // Права за достъп
        $grant_right 	= false;

        if ( in_array('sale_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
            $grant_right 	= true;
        }

        $aSaleDoc = $this->getDocData($nID);

        $nLastOrder			= $aSaleDoc['last_order_id'] ?? 0;
        $lastOrderTime		= $aSaleDoc['last_order_time'] ?? "0000-00-00 00:00:00";

        if ( isset($aSaleDoc['deliverer_ein']) && !empty($aSaleDoc['deliverer_ein']) ) {
            $doc_type		= $aSaleDoc['doc_type'];
            $doc_date		= $aSaleDoc['doc_date'];
            $ein			= $aSaleDoc['deliverer_ein'];
        }

        if ( empty($ein) ) {
            return $this->setError("Невалиден ЕИН!");
        }

        $aSaleRows 	= $this->getDocRows($nID);

        foreach( $aSaleRows as $val ) {
            if ( $val['is_dds'] != 1 ) {
                $nTotalSum 	+= $val['total_sum_with_dds'];
                $nPaidSum	+= $val['paid_sum'];

                if ( isset($vat[$val['vat']]) ) {
                    $vat[$val['vat']] += $val['total_sum_with_dds'];
                } else {
                    $vat[$val['vat']] = $val['total_sum_with_dds'];
                }
            } else {
                $nDDS = $val['id'];
            }
        }

        if ( $doc_type != "oprostena" ) {
            if ( empty($nLastOrder) && !empty($nDDS) ) {
                $oDocRows->delete($nDDS);
            } elseif ( !empty($nLastOrder) && !empty($nDDS) ) {
                if (!$grant_right) {
                    return $this->setError("Нямате достатъчно права за операцията!");
                } else {
                    //$this->delRows($nDDS, $oResponse);
                    // Todo: какво правим тук?!!!
                    return $this->setError("Има платени задължения по документа!");
                }
            }

            $nIDOffice = $oFirms->getDDSOfficeByEIN($ein);

            foreach ( $vat as $vat_percent => $vat_sum ) {
                $aData['id'] = 0;
                $aData['id_sale_doc'] = $nID;
                $aData['id_office'] = $nIDOffice;        //isset($aFirms['id']) ? $aFirms['id'] : 0;
                $aData['id_object'] = 0;
                $aData['month'] = $doc_date;
                $aData['id_service'] = 0;
                $aData['service_name'] = "ДДС";
                $aData['quantity'] = 1;
                $aData['measure'] = "бр.";
                $aData['vat'] = $vat_percent;
                $aData['single_price'] = $vat_sum - ($vat_sum / $this->getVatCoefficient($vat_percent));  //* ($vat_percent / 100);
                $aData['total_sum'] = $vat_sum - ($vat_sum / $this->getVatCoefficient($vat_percent));  // * ($vat_percent / 100);
                $aData['total_sum_with_dds'] = $vat_sum - ($vat_sum / $this->getVatCoefficient($vat_percent));  //* ($vat_percent / 100);
                $aData['paid_sum'] = 0;
                $aData['paid_date'] = "0000-00-00 00:00:00";
                $aData['is_dds'] = 1;
                $aData['type'] = "month";

                $oDocRows->update($aData, null, true, $nID);
            }
        }

        $aData = [];
        $aData['id'] = $nID;
        $aData['total_sum'] = $nTotalSum;
        $aData['orders_sum'] = $nPaidSum;
        $aData['last_order_id'] = $nLastOrder;
        $aData['last_order_time'] = $lastOrderTime;

        $oDocument->update($aData);

        return "OK";
    }

    public function getOrdersByDoc($nID) {
        global $db_name_finance, $db_finance, $db_name_personnel;

        if ( !$this->isValidID($nID) ) {
            return $this->setError("Невалиден документ!");
        }

        $sQuery = "
                (SELECT
                    o.id,
                    o.num,
                    DATE_FORMAT(o.order_date, '%Y-%m-%d') as order_date,
                    ba.name_account as bank_account,
                    o.order_status,
                    IF ( o.order_type = 'expense', o.order_sum * -1, o.order_sum ) as order_sum,
                    CONCAT_WS( ' ', p.fname, p.mname, p.lname ) as user
                FROM {$db_name_finance}.<%tablename%> o
                LEFT JOIN {$db_name_finance}.bank_accounts ba ON (ba.id = o.bank_account_id AND ba.to_arc = 0)
                LEFT JOIN {$db_name_personnel}.personnel p ON (p.id = o.id_person AND p.to_arc = 0)
                WHERE o.doc_id = '{$nID}'
                    AND o.doc_type = 'sale')
            ";

        return SQL_union_search($db_finance, $sQuery, "orders_", "______", "id", "DESC");
    }

    public function getRelations($nID) {
        global $db_name_finance, $db_finance;

        if ( !$this->isValidID($nID) ) {
            return $this->setError("Невалиден документ!");
        }

        $sQuery = "
            (SELECT
                *
            FROM {$db_name_finance}.<%tablename%>
            WHERE id_advice = '{$nID}'
                OR id = '{$nID}'
             )
        ";

        $docData = SQL_union_search($db_finance, $sQuery, PREFIX_SALES_DOCS, "______", "id", "DESC");

        foreach ( $docData as &$doc ) {
            if ( $doc['doc_type'] == "kreditno izvestie" ) {
                $doc['total_sum'] *= -1;
                $doc['orders_sum'] *= -1;
            }
        }

        unset($doc);

        return $docData;
    }

    public function getCreditAdvices($nID) {
        global $db_name_finance, $db_finance;

        if ( !$this->isValidID($nID) ) {
            return $this->setError("Невалиден документ!");
        }

        $sQuery = "
            (SELECT
                *
            FROM {$db_name_finance}.<%tablename%>
            WHERE id_advice = '{$nID}'
                AND doc_type = 'kreditno izvestie'
             )
        ";

        return SQL_union_search($db_finance, $sQuery, PREFIX_SALES_DOCS, "______", "id", "DESC");
    }

    public function updateDocument() {
        global $db_sod, $db_system, $db_finance, $db_name_finance;

        $post = json_decode(file_get_contents('php://input'), true);

        if ( !isset($post['document_data']['id']) || empty($post['document_data']['id']) ) {
            return $this->setError("Невалиден документ!");
        }

        $nID = $post['document_data']['id'] ?? 0;

        if ( !$this->isValidID($nID) ) {
            return $this->setError("Невалиден документ: {$nID}");
        }

        $edit_right = in_array('sale_doc_edit', $_SESSION['userdata']['access_right_levels']);
        $easyPayRight = in_array('sale_doc_easypay', $_SESSION['userdata']['access_right_levels']);

        if ( !$edit_right ) {
            return $this->setError("Нямате достатъчни права за редакция!");
        }

        $document = $post['document_data'] ?? [];
        $docRows = $post['document_rows'] ?? [];

        if ( empty($document) || !isset($document['id']) ) {
            return $this->setError("Невалиден документ: {$nID}");
        }

        $docOrigin = $this->getDocData($nID);

        if ( empty($docOrigin) || !isset($docOrigin['id']) ) {
            return $this->setError("Невалиден документ: {$nID}");
        }

        if ( $docOrigin['doc_status'] == "canceled" ) {
            return $this->setError("Документа вече е анулиран!");
        }

        $isGenerated = $docOrigin['gen_pdf'] == 1;
        $isUserPrint = $docOrigin['is_user_print'] == 1;

        if ( $isUserPrint ) {
            return $this->setError("Документа вече е бил разпечатан от клиент!");
        }

        if ( $isGenerated && !$easyPayRight ) {
            return $this->setError("Документа вече е бил разпечатан или експортиран!");
        }


        /*
        $orders = $this->getOrdersByDoc($nID);

        if ( !empty($orders) ) {
            throw new Exception("Към документа има плащания!", DBAPI_ERR_INVALID_PARAM);
        }
        */

        $docRowsOrigin = $this->getDocRows($nID);

        $is_credit = false;

        foreach ( $docRowsOrigin as $row ) {
            if ( isset($row['type']) && $row['type'] == "credit" ) {
                $is_credit = true;
                break;
            }
        }

        if ( $is_credit ) {
            return $this->setError("Налични са задължения по кредит! \nРедакцията е забранена!");
        }

        if ( $document['doc_type'] == "faktura" || $document['doc_type'] == "debitno izvestie" || $document['doc_type'] == "kvitanciq" ) {
            if ( $document['total_sum'] < 0 ) {
                return $this->setError("Фактура с отрицателна стойност не може да бъде издадена!");
            }
        }

        $oDocument = new DBMonthTable($db_name_finance,PREFIX_SALES_DOCS,$db_finance);
        $oDocRows = new DBMonthTable($db_name_finance,PREFIX_SALES_DOCS_ROWS,$db_finance);

        $db_finance->StartTrans();
        $db_system->StartTrans();
        $db_sod->StartTrans();

        try {
            $aData = [];
            $aData['id'] = $document['id'];
            $aData['doc_date'] = $document['doc_date'] ?: $docOrigin['doc_date'];
            $aData['created_time'] = $document['doc_date_create'] ?: $docOrigin['created_time'];
            $aData['single_view_name'] = $document['single_view_name'] ?: $docOrigin['single_view_name'];
            $aData['client_recipient'] = $document['client_recipient'] ?: $docOrigin['client_recipient'];
            $aData['note'] = $document['note'] ?: $docOrigin['note'];
            $aData['view_type'] = $document['view_type'] ?: $docOrigin['view_type'];
            $aData['advice_reason'] = $document['advice_reason'] ?: $docOrigin['advice_reason'];
            $aData['id_city'] = $document['id_city'] ?: $docOrigin['id_city'];
            $aData['version'] = 2;

            $oDocument->update($aData);

            foreach ( $docRows as $dataRow ) {
                if ( !isset($dataRow['id']) || empty($dataRow['id']) ) {
                    throw new Exception("Грешка по време на операцията!", DBAPI_ERR_INVALID_PARAM);
                }

                $aData = [];
                $aData['id'] = $dataRow['id'];
                $aData['service_name'] = $dataRow['service_name'];
                $aData['object_name'] = $dataRow['object_name'];
                $aData['view_type_detail'] = $dataRow['view_type_detail'];
                $aData['view_type_by_services'] = $dataRow['view_type_by_services'];
                $aData['updated_user'] = $this->getPerson();
                $aData['updated_time'] = time();

                $oDocRows->update($aData, null, null, $nID);
            }

            if ( !empty($docOrigin['id_client']) ) {
                $oClient = new DBBase2($db_sod, "clients");

                $aData = [];
                $aData['id'] = $docOrigin['id_client'];
                $aData['note'] = $document['note'] ?? "";
                $aData['invoice_recipient'] = $document['client_recipient'] ?: $document['client_mol'];

                $oClient->update($aData);
            }

            $db_finance->CompleteTrans();
            $db_system->CompleteTrans();
            $db_sod->CompleteTrans();
        } catch (Exception $ex) {
            $db_finance->FailTrans();
            $db_system->FailTrans();
            $db_sod->FailTrans();

            return $this->setError("Грешка: " . $ex->getMessage());
        }

        return ["document_data" => $document, "document_rows" => $docRows];
    }

    public function getMaxLastPaid($id_service) {
        global $db_sod, $db_name_sod;

        $oService = new DBBase2($db_sod, 'objects_services');

        $sQuery = "SELECT last_paid FROM {$db_name_sod}.objects_services WHERE id = {$id_service}";

        return $oService->selectOne($sQuery);
    }

    public function annulment() {
        global $db_sod, $db_system, $db_finance, $db_name_finance;

        $post = json_decode(file_get_contents('php://input'), true);

        if ( !isset($post['document_data']['id']) || empty($post['document_data']['id']) ) {
            return $this->setError("Невалиден документ!");
        }

        $nID = $post['document_data']['id'] ?? 0;
        $nIDUser = isset($_SESSION['userdata']['id_person']) ?: 0;
        $requestConfirmed = $post['confirm_request'] ?? 0;

        if ( !$this->isValidID($nID) ) {
            return $this->setError("Невалиден документ: {$nID}");
        }

        $oServices		= new DBBase2($db_sod, "objects_services");
        $oSingles		= new DBBase2($db_sod, "objects_singles");

        $document = $this->getDocData($nID);
        $docRows = $this->getDocRows($nID);
        $aMonth = [];
        $aDataServices = [];
        $hasFuture = false;

        if ( empty($document) || !isset($document['id']) ) {
            return $this->setError("Невалиден документ: {$nID}");
        }

        if ( $document['doc_status'] == "canceled" ) {
            return $this->setError("Документа вече е анулиран!");
        }

        // Право за редакция - добавяме и право за преглед
        $docEditRight = in_array('sale_doc_edit', $_SESSION['userdata']['access_right_levels']);
        $easyPayRight = in_array('sale_doc_easypay', $_SESSION['userdata']['access_right_levels']);

        // При пълно право за редакция - добавяме и право за преглед и редакция
        if ( in_array('sale_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
            $docGrantRight 	= true;
            $docEditRight = true;
        } else {
            $docGrantRight = false;
        }

        // Друго право за редакция...
        if ( in_array('orders_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
            $docGrantRight 	= true;
            $docEditRight = true;
        }

        $lock = !in_array($document['doc_type'], ['oprostena', 'kvitanciq']);
        $isGenerated = $document['gen_pdf'] == 1;
        $isAuto = $document['is_auto'] == 1;
        $isUserPrint = $document['is_user_print'] == 1;
        $sTimeNow = date("Ym");
        $sTimeDoc = substr($nID, 0, 6);

        if ( !$docEditRight ) {
            return $this->setError("Нямате достатъчно права за операцията!");
        }

        if ( ($sTimeNow != $sTimeDoc) && $lock ) {
            return $this->setError("Документа е издаден в предходен месец!");
        }

        if ( $isUserPrint ) {
            return $this->setError("Документа вече е бил разпечатан от клиент!");
        }

        if ( $isGenerated && !$easyPayRight ) {
            return $this->setError("Документа вече е бил разпечатан или експортиран!");
        }

        if ( $isAuto && !$easyPayRight ) {
            return $this->setError("Документа е автоматично генериран!");
        }

        if ( $document['doc_type'] == "faktura" && !empty($document['id_advice']) ) {
            $relations = $this->getRelations($nID);

            foreach ( $relations as $advice ) {
                if ( $advice['doc_status'] == "final" && ($advice['doc_type'] == "kreditno izvestie" || $advice['doc_type'] == "debitno izvestie") ) {
                    return $this->setError("Към документа има издадено кредитно/дебитно известие!");
                }
            }
        }

        // Проверка и връщане на падежите
        foreach ($docRows as $val) {
            if ( $val['type'] == "month" && !empty($val['id_duty_row']) ) {
                if ( !isset($aDataServices[$val['id_duty_row']]) ) {
                    $aDataServices[$val['id_duty_row']] = [];
                    $aDataServices[$val['id_duty_row']]['max_month'] = $val['month'];
                    $aDataServices[$val['id_duty_row']]['min_month'] = $val['month'];
                    $aDataServices[$val['id_duty_row']]['last_paid'] = $this->getMaxLastPaid($val['id_duty_row']);
                } else {
                    if ($val['month'] > $aDataServices[$val['id_duty_row']]['max_month']) {
                        $aDataServices[$val['id_duty_row']]['max_month'] = $val['month'];
                    }

                    if ($val['month'] < $aDataServices[$val['id_duty_row']]['min_month']) {
                        $aDataServices[$val['id_duty_row']]['min_month'] = $val['month'];
                    }
                }
            }
        }

        foreach ($aDataServices as $arr_data) {
            if ($arr_data['max_month'] < $arr_data['last_paid']) {
                $hasFuture = true;
            }
        }

        if ( $hasFuture ) {
            if ( $docGrantRight ) {
                if ( $requestConfirmed == 0 ) {
                    $this->setAlert("Документа има фактурирани задължения за по-късен период! \nЖелаете ли да анулирате документа въпреки това?");

                    return ["document_data" => $document, "document_rows" => $docRows, "request_confirm" => 1];
                }
            } else {
                return $this->setError("Документа има фактурирани задължения за по-късен период!");
            }
        }

        $db_finance->StartTrans();
        $db_system->StartTrans();
        $db_sod->StartTrans();

        try {
            $orderSum = abs($document['orders_sum']);

            if ( abs($orderSum) > 0.01 ) {
                $orders = $this->getOrdersByDoc($nID);

                if ( !empty($orders) && !$docGrantRight ) {
                    throw new Exception("Нямате достатъчно права за операцията!", DBAPI_ERR_INVALID_PARAM);
                }

                $oPay = new NEWDBFinanceOperations();

                foreach ( $orders as $order ) {
                    if ( isset($order['order_status']) && ($order['order_status'] == "active") ) {
                        $nIDOrder = $order['id'] ?? 0;

                        if ( !$this->isValidID($nIDOrder) ) {
                            throw new Exception("Невалиден ордер {$order['num']}", DBAPI_ERR_INVALID_PARAM);
                        }
                    }
                }

                foreach ( $orders as $order ) {
                    if ( isset($order['order_status']) && ($order['order_status'] == "active") ) {
                        $nIDOrder	= $order['id'] ?? 0;

                        $oPay->annulment($nIDOrder);

                        $alerts = $oPay->getAlerts();

                        if ( !empty($alerts) ) {
                            $this->alerts = $alerts;
                        }

                        $error = $oPay->getError();

                        if ( !empty($error) ) {
                            throw new Exception($error, DBAPI_ERR_INVALID_PARAM);
                        }
                    }
                }
            }

            // Проверка
            $document = $this->getDocData($nID);
            $orderSum = abs($document['orders_sum']);

            if ( $orderSum > 0.01 ) {
                throw new Exception("Проблем при анулиране на ордер!", DBAPI_ERR_INVALID_PARAM);
            }

            // Връщаме падежите
            //$docRows = $this->getDocRows($nID);

            foreach ( $docRows as $docRow ) {
                $nIDDuty = $docRow['id_duty_row'] ?? 0;

                // Генериране на месечни задължения
                if ( !empty($nIDDuty) && isset($docRow['type']) && ($docRow['type'] == "month") ) {
                    if ( isset($aMonth[$nIDDuty]) ) {
                        if ( $aMonth[$nIDDuty] > $docRow['month'] ) {
                            $aMonth[$nIDDuty] = $docRow['month'];
                        }
                    } else $aMonth[$nIDDuty] = $docRow['month'];
                }

                // Анулиране на еднократни задължения
                if ( !empty($nIDDuty) && isset($docRow['type']) && ($docRow['type'] == "single" || $docRow['type'] == "credit") ) {
                    $aSingleData				= array();
                    $aSingleData['id']			= $nIDDuty;
                    $aSingleData['paid_date']	= "0000-00-00";
                    $aSingleData['id_sale_doc']	= 0;

                    $oSingles->update($aSingleData);
                }
            }

            unset($docRow);

            // Todo: $hasFuture
            // Анулиране на месечни задължения
            foreach ( $aMonth as $key => $docRow ) {
                $aData	= array();
                $aTmp	= explode("-", $docRow);

                if ( isset($aTmp[2]) ) {
                    $day = $aTmp[2];
                    $mon = $aTmp[1];
                    $yer = $aTmp[0];

                    $firstDayDate = date( "Y-m-d", mktime(0, 0, 0, $mon - 1, $day, $yer) );

                    if ( $day == 1 ) {
                        $newDate = $firstDayDate;
                    } else {
                        $newDate = $docRow;
                    }

                    if ( $hasFuture ) {
                        if ( isset($aDataServices[$key]) && $aDataServices[$key]['max_month'] < $aDataServices[$key]['last_paid'] ) {
                            continue;
                        }
                    }

                    $aData['id'] 		= $key;
                    $aData['last_paid']	= $newDate;

                    $oServices->update($aData);
                }
            }

            $sDocName	= PREFIX_SALES_DOCS.substr($nID, 0, 6);
            $db_finance->Execute("UPDATE {$db_name_finance}.$sDocName SET doc_status = 'canceled', updated_user = '{$nIDUser}', updated_time = NOW() WHERE id = {$nID} ");

            $db_finance->CompleteTrans();
            $db_system->CompleteTrans();
            $db_sod->CompleteTrans();
        } catch (Exception $ex) {
            $db_finance->FailTrans();
            $db_system->FailTrans();
            $db_sod->FailTrans();

            return $this->setError("Грешка: " . $ex->getMessage());
        }

        return ["document_data" => $document, "document_rows" => $docRows, "request_confirm" => 0];
    }
}
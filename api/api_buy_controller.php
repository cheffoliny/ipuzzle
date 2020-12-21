<?php
/**
 * Created by PhpStorm.
 * User: adm
 * Date: 29.7.2020 г.
 * Time: 11:46
 */

if ( !isset($_SESSION) ) {
    session_start();
}

$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );
set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../' );

require_once("../config/function.autoload.php");
require_once("../include/adodb/adodb-exceptions.inc.php");
require_once("../config/connect.inc.php");
require_once("../include/general.inc.php");

if ( !defined('PREFIX_BUY_DOCS') ) {
    define('PREFIX_BUY_DOCS',		'buy_docs_');
    define('PREFIX_BUY_DOCS_ROWS',	'buy_docs_rows_');
}

class BuyController extends DBBase2 {
    private $alerts = [];
    private $client = [];
    private $error = "";



    function __construct() {
        global $db_finance;

        parent::__construct($db_finance, "bank_accounts");
    }

    private function isValidID( $nID ) {
        return preg_match("/^\d{13}$/", $nID);
    }

    private function setAlert($message) {
        if ( !empty($message) ) {
            $this->alerts[] = $message;
        }
    }

    private function setError($message) {
        if ( !empty($message) ) {
            $this->error = $message;

            http_response_code(400);

            return ["error" => $message];
        }
    }

    public function getAlerts() {
        return $this->alerts;
    }

    public function getError() {
        return $this->error;
    }

    private function getPerson() {
        return isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
    }
    # vms temp 
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

    public function getFirmsAsClient() {
        global $db_name_sod;

        $sQuery = "
				SELECT
					jur_name as name, 
					address as address, 
					idn as ein,
					idn_dds as ein_dds,
					jur_mol as mol,
					id_office_dds
				FROM {$db_name_sod}.firms
				WHERE to_arc = 0 
					AND jur_name != ''	
				GROUP BY jur_name		
			";

        return $this->select($sQuery);
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
					f.id_office_dds,
					o.name as region,
					f.idn,
					f.jur_name 
				FROM {$db_name_sod}.firms f
				RIGHT JOIN {$db_name_sod}.offices o ON ( o.id_firm = f.id AND o.to_arc = 0 ) 
				WHERE f.to_arc = 0
		    ";

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

    public function getDDSFirmByEIN( $ein ) {
        global $db_name_sod;

        $sQuery = "
				SELECT ff.* 
				FROM {$db_name_sod}.firms f
				LEFT JOIN {$db_name_sod}.offices o on o.id = f.id_office_dds
				LEFT JOIN {$db_name_sod}.firms ff on ff.id = o.id_firm
				WHERE f.to_arc = 0 
					AND f.idn = '{$ein}'	
				LIMIT 1	
			";

        return $this->selectOnce($sQuery);
    }

    public function getNomenclatureGroups() {
        global $db_name_finance;

        $sQuery = "
				SELECT 
					id, 
					name
				FROM {$db_name_finance}.nomenclatures_groups
				WHERE to_arc = 0 	
					AND `type` = 'expense'
			";
        
        $aData=  $this->select($sQuery);
        // заради разходни номенклатури без група
        $aData[] = ['id' => 0, 'name' => ''];
        return $aData;
    }

    public function getExpenses($nID) {
        global $db_name_sod;

        $sQuery = "SELECT nomenclatures_expenses FROM {$db_name_sod}.directions_type WHERE id = {$nID} ";

        return $this->selectOne($sQuery);
    }

    public function getNomenclatures($nIDDirection = 0) {
        global $db_name_finance;

        /*
        if ( empty($nIDDirection) ) {
            return [];
        }
        */

        $nIDPerson  = $this->getPerson();
        $sDirection = $this->getExpenses($nIDDirection);

        /*
        if ( empty($sDirection) ) {
            return [];
        }
        */

        $sQuery = "
            SELECT
                ne.id,
                ne.code,
                ne.id_group,
                ne.name
            FROM {$db_name_finance}.nomenclatures_expenses ne
            LEFT JOIN {$db_name_finance}.cashier c ON (c.id_person = {$nIDPerson} AND c.to_arc = 0)
            WHERE ne.to_arc = 0
              AND FIND_IN_SET(ne.id, c.nomenclatures_expenses_create)
        ";

        if ( !empty($sDirection) ) {
            $sQuery .= " AND ne.id IN ({$sDirection}) ";
        }

        $aData = $this->select($sQuery);

        $aData[] = array("id" => "-1", "code" => "79999", "name" => " .:: ДДС ::.", "vat_transfer" => 1);

        return $aData;
    }

    public function getFunds() {
        global $db_name_sod, $db_name_finance;

        $nIDPerson = $this->getPerson();

        if ( empty($nIDPerson) || !is_numeric($nIDPerson) ) {
            return $this->setError("Няма валиден потребител!");
        }

        if ( isset($_SESSION['userdata']['access_right_regions']) ) {
            $sAccessRegions = implode(',', $_SESSION['userdata']['access_right_regions']);
        } else {
            return $this->setError("Нямате достъп до нто един регион!");
        }

        $sQuery = "
            SELECT
                dt.id,
                dt.id_firm,
                #ofc.id as id_office,
                dt.`name`,
                dt.nomenclatures_expenses
            FROM {$db_name_sod}.directions_type dt
            JOIN {$db_name_finance}.cashier c ON ( c.id_person = {$nIDPerson} AND FIND_IN_SET(dt.id, c.directions_type_create) AND c.`to_arc` = 0 )
            JOIN sod.offices ofc ON (dt.id_firm = ofc.id_firm AND ofc.to_arc = 0)
            WHERE dt.to_arc = 0
              AND dt.id_firm_dispose = 0
        ";

        if ( !empty($sAccessRegions) ) {
            $sQuery .= " AND ofc.id IN ({$sAccessRegions})\n";
        }

        $sQuery .= "
        GROUP BY dt.id
			ORDER BY name
		";

        return $this->select( $sQuery );
    }

    public function getBankAccountsForOrders() {
        global $db_name_finance;

        $nIDUser = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;

        $sQuery = "
				SELECT 
					ba.id,
					IF ( ba.cash, CONCAT(ba.name_account, ' [каса]'), CONCAT(ba.name_account, ' [банка]') ) as name,
					ba.iban as iban,
					IF ( ba.cash, 'cash', 'bank' ) as type,
					ba.is_paid,
					ba.tax
				FROM {$db_name_finance}.bank_accounts ba
				LEFT JOIN {$db_name_finance}.cashier c ON (FIND_IN_SET(ba.id, c.bank_accounts_operate) AND c.to_arc = 0)
				WHERE ba.to_arc = 0
					AND c.id_person = '{$nIDUser}'
					#AND ba.id = 30 
				ORDER BY ba.name_account
			";

        return $this->select( $sQuery );
    }
    
    // vms
    public function getEinByIdOffice() {

        global $db_name_sod;

        $id_office = isset($_SESSION['userdata']['id_office']) ? $_SESSION['userdata']['id_office'] : 0;

        $sQuery = "
            SELECT idn
            FROM $db_name_sod.firms
            WHERE id = (SELECT id_firm FROM $db_name_sod.offices WHERE id = $id_office AND to_arc = 0 LIMIT 1)
            LIMIT 1
        ";

        return $this->selectOnce( $sQuery );
    }

    public function getBlankDoc() {
        // vms 
        $defaultEin = $this->getEinByIdOffice()['idn'] ?? false;
        
        $aClient = $this->getFirmsAsClient();
        $key = array_search($defaultEin, array_column($aClient, 'ein'));

        if ( empty($aClient) || $key === false ) {
            return $this->setError("Няма валиден клиент!");
        }

        if ( !isset($aClient[$key]['name']) || empty($aClient[$key]['name']) ) {
            return $this->setError("Няма валиден клиент!");
        }

        $aCashier       = $this->getCashierByIDPerson();

        $aData      = [];

        // Информация за потребителя
        $aData['user_id'] 			= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
        $aData['user_name'] 		= isset($_SESSION['userdata']['name']) 		? $_SESSION['userdata']['name']  		: "";
        $aData['user_office_id'] 	= isset($_SESSION['userdata']['id_office']) ? $_SESSION['userdata']['id_office']  	: 0;
        $aData['user_office_name'] 	= isset($_SESSION['userdata']['region']) 	? $_SESSION['userdata']['region']  		: "";
        $aData['user_uname'] 		= isset($_SESSION['userdata']['username']) 	? $_SESSION['userdata']['username']  	: "";
        $aData['user_row_limit'] 	= isset($_SESSION['userdata']['row_limit']) ? $_SESSION['userdata']['row_limit']  	: 0;
        $aData['id_schet_account'] 	= isset($_SESSION['userdata']['id_schet_account']) ? $_SESSION['userdata']['id_schet_account']  : 0;
        $aData['user_has_debug'] 	= isset($_SESSION['userdata']['has_debug']) ? $_SESSION['userdata']['has_debug']  	: 0;

        // Blank document
        $aData['id'] 				= 0;
        $aData['doc_date'] 			= date("Y-m-d");
        $aData['doc_type'] 			= "faktura";
        $aData['doc_status']		= "final";
        $aData['doc_num']		    = 0;
        $aData['for_fuel'] 		    = 0;
        $aData['for_gsm']           = 0;

        $aData['client_name']		= $aClient[$key]['name'] ?? "";
        $aData['client_ein']		= $aClient[$key]['ein'] ?? 0;
        $aData['client_ein_dds']	= $aClient[$key]['ein_dds'] ?? "";
        $aData['client_address']	= $aClient[$key]['address'] ?? "";
        $aData['client_mol']		= $aClient[$key]['mol'] ?? "";
        $aData['client_recipient']	= $aData['user_name'] ?? "";

        $aData['id_deliverer']      = 0;
        $aData['deliverer_name']	= "";
        $aData['deliverer_address']	= "";
        $aData['deliverer_ein']		= "";
        $aData['deliverer_ein_dds']	= "";
        $aData['deliverer_mol']	    = "";

        $aData['total_sum'] 		= 0;
        $aData['orders_sum'] 		= 0;
        $aData['last_order_id'] 		= 0;
        $aData['paid_type'] 	    = "cash";
        $aData['view_type']			= "extended";
        $aData['note']				= "";
        $aData['doc_date_create']	= date("Y-m-d");
        $aData['created_user']		= $this->getPerson();
        $aData['created_time']		= date("Y-m-d");
        $aData['updated_user']		= $this->getPerson();
        $aData['updated_time']		= date("Y-m-d");


        // Права за достъп
        $aData['buy_doc_view'] 		= in_array('buy_doc_view', $_SESSION['userdata']['access_right_levels']);

        // При право за редакция - добавяме и право за преглед
        if ( in_array('buy_doc_edit', $_SESSION['userdata']['access_right_levels']) ) {
            $aData['buy_doc_view']	= true;
            $aData['buy_doc_edit'] 	= true;
        } else {
            $aData['buy_doc_edit'] 	= false;
        }

        // При пълно право за редакция - добавяме и право за преглед и редакция
        if ( in_array('buy_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
            $aData['buy_doc_view']	= true;
            $aData['buy_doc_edit'] 	= true;
            $aData['buy_doc_grant'] = true;
        } else {
            $aData['buy_doc_grant']	= false;
        }

        $aData['buy_doc_order_view'] = in_array('buy_doc_order_view', $_SESSION['userdata']['access_right_levels']) && $aData['buy_doc_view'];

        // При право за редакция - добавяме и право за преглед
        if ( in_array('buy_doc_order_edit', $_SESSION['userdata']['access_right_levels']) && $aData['buy_doc_view'] ) {
            $aData['buy_doc_order_view'] = true;
            $aData['buy_doc_order_edit'] = true;
        } else {
            $aData['buy_doc_order_edit'] = false;
        }

        if ( !$aData['buy_doc_view'] ) {
            $aData 						= array();

            $aData['id'] 				= 0;
            $aData['doc_date'] 			= date("Y-m-d");
            $aData['doc_type'] 			= "faktura";
            $aData['paid_type'] 		= "cash";
            $aData['dds_sum'] 			= 0;
            $aData['dds_payed'] 		= false;
            $aData['dds_for_payment'] 	= true;

            $aData['buy_doc_view']		 = false;
            $aData['buy_doc_edit'] 		 = false;
            $aData['buy_doc_grant'] 	 = false;
            $aData['buy_doc_order_view'] = false;
            $aData['buy_doc_order_edit'] = false;
        }

        $aData['id_cash_default'] = isset($aCashier['id_cash_default']) && !empty($aCashier['id_cash_default']) ? $aCashier['id_cash_default'] : -1;

        return $aData;
    }

    public function getDocData($nID) {
        global $db_name_finance, $db_name_personnel;

        $docData = $this->getBlankDoc();

        if ( !$this->isValidID($nID) ) {
            return $this->setError("Невалиден документ!");
        }

        $sTable	= PREFIX_BUY_DOCS . substr($nID, 0, 6);     //PREFIX_BUY_DOCS

        $sQuery = "
				SELECT
					sd.*,
					CONCAT(CONCAT_WS(' ',p_cr.fname,p_cr.mname,p_cr.lname),' [',DATE_FORMAT(sd.created_time,'%d.%m.%Y %H:%i.%s'),']') AS created,	
					CONCAT(CONCAT_WS(' ',p_up.fname,p_up.mname,p_up.lname),' [',DATE_FORMAT(sd.updated_time,'%d.%m.%Y %H:%i.%s'),']') AS updated				
				FROM {$db_name_finance}.{$sTable} sd
				LEFT JOIN {$db_name_personnel}.personnel p_cr ON p_cr.id = sd.created_user
				LEFT JOIN {$db_name_personnel}.personnel p_up ON p_up.id = sd.updated_user
				WHERE sd.id = {$nID}
			
			";

        $aData = $this->selectOnce($sQuery);
        
        if ( !empty($aData) ) {
            $aData['doc_date_create'] = substr($aData['created_time'], 0, 10);

            $docData = array_merge($docData, $aData);
        } else {
            return $this->setError("Невалиден документ!");
        }

        return $docData;
    }

    public function getDocRows($nID) {
        global $db_name_finance, $db_name_sod;

        if ( !$this->isValidID($nID) ) {
            return $this->setError("Невалиден документ!");
        }

        $sTable	= PREFIX_BUY_DOCS_ROWS . substr($nID, 0, 6);        //PREFIX_BUY_DOCS_ROWS

        $sQuery = "
				SELECT
					sd.*,
				    ofc.id_firm,
                    ofc.name as region,
				    frm.name as firm,
				    o.name as object,
				    fnd.name as fund,
				    ne.name as nomenclature
				FROM {$db_name_finance}.{$sTable} sd
				LEFT JOIN {$db_name_sod}.offices ofc ON ofc.id = sd.id_office
                LEFT JOIN {$db_name_sod}.firms frm ON frm.id = ofc.id_firm
                LEFT JOIN {$db_name_sod}.objects o ON o.id = sd.id_object
                LEFT JOIN {$db_name_sod}.directions_type fnd ON fnd.id = sd.id_direction
                LEFT JOIN {$db_name_finance}.nomenclatures_expenses ne ON ne.id = sd.id_nomenclature_expense
				WHERE sd.id_buy_doc = {$nID}
		  
			";

        return $aData = $this->select($sQuery);

        /*
        foreach ( $aData as &$data ) {
            if ( $data['is_dds'] == 2 ) {
                $data['id_nomenclature_expense'] = -1;
            }
        }

        return $aData;
        */
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
                IF ( o.order_type = 'earning', o.order_sum * -1, o.order_sum ) as order_sum,
                CONCAT_WS( ' ', p.fname, p.mname, p.lname ) as user
            FROM {$db_name_finance}.<%tablename%> o
            LEFT JOIN {$db_name_finance}.bank_accounts ba ON (ba.id = o.bank_account_id AND ba.to_arc = 0)
            LEFT JOIN {$db_name_personnel}.personnel p ON (p.id = o.id_person AND p.to_arc = 0)
            WHERE o.doc_id = '{$nID}'
                AND o.doc_type = 'buy')
        ";

        $aData = SQL_union_search($db_finance, $sQuery, "orders_", "______", "id", "DESC");

        return $aData ?: [];
    }

    public function checkDocNumber($nDocNum, $nIDDeliverer) {
        global $db_name_finance, $db_finance;

        if ( empty($nDocNum) || empty($nIDDeliverer) ) {
            return $this->setError("Некоректен документ или доставчик!");
        }

        $sQuery = "
            SELECT 
                id
            FROM {$db_name_finance}.<%tablename%>
            WHERE doc_num = {$nDocNum}
                AND id_deliverer = {$nIDDeliverer}
                AND doc_type != 'kvitanciq'
                AND doc_status != 'canceled'
        ";

        $aData = SQL_union_search($db_finance, $sQuery, PREFIX_BUY_DOCS, "______", "id", "DESC");   // PREFIX_BUY_DOCS

        return count($aData ?: []);
    }

    public function store($post = null) {
        global $db_sod, $db_system, $db_finance, $db_name_finance;

        if ($post == null) {
            $post = json_decode(file_get_contents('php://input'), true);
        }
        
        if ( !isset($post['document_data']) || !isset($post['document_rows']) ) {
            return $this->setError("Грешка при опит за създаване на документ!");
        }

        $document = $post['document_data'] ?? [];
        $docRows = $post['document_rows'] ?? [];

        $nIDUser = $this->getPerson();
        $nID = $document['id'] ?? 0;
        $totalSum = $document['doc_type'] == "kreditno izvestie" ? ($post['totalSum'] *= -1 ?? 0) : $post['totalSum'] ?? 0;
        $vatSum = $post['vatSum'] ?? 0;
        $rowSum = 0;

        // Право за редакция - добавяме, преглед и ордери
        $docViewRight = in_array('buy_doc_view', $_SESSION['userdata']['access_right_levels']);
        $docEditRight = in_array('buy_doc_edit', $_SESSION['userdata']['access_right_levels']);
        $docGrantRight = in_array('buy_doc_grant', $_SESSION['userdata']['access_right_levels']);
        $docOrderViewRight = in_array('buy_doc_order_view', $_SESSION['userdata']['access_right_levels']) && $docViewRight;
        $docOrderEditRight = in_array('buy_doc_order_edit', $_SESSION['userdata']['access_right_levels']) && $docViewRight;

        if ( empty($docEditRight) ) {
            return $this->setError("Нямате достатъчно права за операцията!");
        }

        if ( empty($docRows) ) {
            return $this->setError("Документа е празен!");
        }

        if ( !isset($document['doc_type']) ) {
            return $this->setError("Невалидни параметри - документа не може да бъде създаден!");
        }

        if ( $document['doc_type'] == "faktura" || $document['doc_type'] == "kvitanciq" ) {
            if ( !($document['id_deliverer'] ?? false) || !($document['deliverer_name'] ?? false) || !($document['deliverer_ein'] ?? false) ) {
                return $this->setError("Въведете коректен доставчик!");
            }

            if ( !($document['client_name'] ?? false) || !($document['client_ein'] ?? false) ) {
                return $this->setError("Изберете коректен клиент!");
            }

            if ( !($document['doc_num'] ?? false) ) {
                return $this->setError("Въведете номер на фактура!");
            }

            $checkDocumentIfExist = $this->checkDocNumber($document['doc_num'], $document['id_deliverer']);

            if ( !empty($checkDocumentIfExist) ) {
                return $this->setError("\"Вече има документ с номер {$document['doc_num']} \nиздаден от {$document['deliverer_name']}!" . $checkDocumentIfExist);
            }
        }

        $oBuyDoc = new DBMonthTable($db_name_finance,PREFIX_BUY_DOCS,$db_finance);
        $oBuyDocRows = new DBMonthTable($db_name_finance,PREFIX_BUY_DOCS_ROWS,$db_finance);
        $oService = new DBNomenclaturesExpenses();
        $oFirms = new DBFirms();

        $db_finance->StartTrans();
        $db_system->StartTrans();
        $db_sod->StartTrans();

        try {
            if ( empty($nID) ) {
                $document['doc_date'] = $document['doc_date'] ?: time();
                $document['doc_status'] = "final";
                $document['orders_sum'] = 0;
                $document['total_sum'] = $totalSum;
                $document['last_order_time'] = "0000-00-00 00:00:00";
                $document['paid_type'] = $document['paid_type'] ?: "bank";
                $document['created_user'] = $nIDUser;
                $document['created_time'] = $document['doc_date_create'] ?: time();
                $document['updated_user'] = $nIDUser;
                $document['updated_time'] = time();

                $oBuyDoc->update($document);

                if ( date("Y-m-d") != $document['doc_date_create'] ) {
                    $document['created_time'] = $document['doc_date_create'] ?: time();

                    $oBuyDoc->update($document);
                }

                $nID = $document['id'];

                if ( in_array($document['doc_type'], ["debitno izvestie", "kreditno izvestie"]) ) {
                    $nIDParent = $document['id_advice'] ?? 0;

                    if ( !empty($nIDParent) ) {
                        $oParent = new DBBuyDocs();
                        $oParent->getRecord($nIDParent, $aParent);

                        if ( !empty($aParent) ) {
                            $aParent['is_advice'] = 1;
                            $aParent['id_advice'] = $nID;

                            $oBuyDoc->update($aParent);
                        }
                    } else {
                        throw new Exception("Невалиден документ {$nIDParent}!", DBAPI_ERR_FAILED_TRANS);
                    }
                }

                $isSpecial		= false;
                $isNormal		= false;

                foreach ( $docRows as $key => $row ) {
                    /*
                    if ( $row['total_sum'] > 200000 ) {
                        throw new Exception("Проблем с приемането на данните!\nСвържете се с администратор!", DBAPI_ERR_FAILED_TRANS);
                    }
                    */

                    if ( !($row['id_office'] ?? false) ) {
                        throw new Exception("Има запис без фирма/регион!!!", DBAPI_ERR_FAILED_TRANS);
                    }

                    if ( !($row['id_nomenclature_expense'] ?? false) ) {
                        throw new Exception("Има запис без номенклатура!!!", DBAPI_ERR_FAILED_TRANS);
                    }

                    if ( !($row['id_direction'] ?? false) && $row['id_nomenclature_expense'] > 0 ) {
                        throw new Exception("Има запис без направление!!!", DBAPI_ERR_FAILED_TRANS);
                    }

                    // Прехвърляне по сметка - забраняваме тип на документ различен от витанция!
                    $bTransfer	= $oService->checkForTransfer($row['id_nomenclature_expense']);

                    if ( $bTransfer ) {
                        if ( $document['doc_type'] != "oprostena" ) {
                            throw new Exception("Документ с НАПРАВЛЕНИЕ по ТРАНСФЕР\nможе да бъде само квитанция!", DBAPI_ERR_FAILED_TRANS);
                        }

                        $isSpecial = true;
                    }

                    // Номенклатура ДДС
                    if ( $row['id_nomenclature_expense'] == -1 ) {
                        $row['id_nomenclature_expense'] = 0;
                        $isDds = 2;
                        $isSpecial = true;

                        $aFirm 		= $oFirms->getFirmByIDOffice($row['id_office']);
                        $row['id_office'] = $aFirm['id_office_dds'] ?? 0;

                        if ( $document['doc_type'] != "oprostena" ) {
                            throw new Exception("Не може да бъде записан документ\nот тип фактура с НАПРАВЛЕНИЕ по ДДС!", DBAPI_ERR_FAILED_TRANS);
                        }
                    } else {
                        $isDds = $row['is_dds'] ?? 0;

                        if ( !$bTransfer ) {
                            $isNormal = true;
                        }
                    }
                    
                    if ( $document['doc_type'] == "kreditno izvestie" ) {
                        $row['total_sum'] *= -1;
                        $row['single_price'] *= -1;
                    }

                    $rowSum += $row['total_sum'];

                    $row['id'] 				= 0;
                    $row['id_buy_doc'] 		= $nID;
                    $row['id_person'] 		= $nIDUser;
                    $row['id_salary_row'] 	= 0;
                    $row['id_order'] 		= 0;
                    $row['month'] 	        = $row['month'] ?? date("Y-m-d");
                    $row['quantity']	    = 1;
                    $row['measure']			= "бр.";
                    $row['paid_sum']		= 0;
                    $row['single_price']	= $row['total_sum'];
                    $row['paid_date']       = "0000-00-00";
                    $row['is_dds']	 		= $isDds;

                    $oBuyDocRows->update($row, null, null, $nID);
                }

                if ( $isSpecial && $isNormal ) {
                    throw new Exception("В документа има комбинация от услуги и ДДС/ТРАНСФЕР!!!", DBAPI_ERR_INVALID_PARAM);
                }

                // ДДС
                if ( $document['doc_type'] != "oprostena" && abs($vatSum) > 0 ) {
                    $ddsFirm = $this->getDDSFirmByEIN($document['client_ein']);
                    $ddsOffice = $ddsFirm['id_office_dds'] ?? 0;

                    if (empty($ddsOffice)) {
                        throw new Exception("Не може да бъде намерен офис по ДДС!", DBAPI_ERR_FAILED_TRANS);
                    }
                    
                    if ( $document['doc_type'] == "kreditno izvestie" ) {
                        $vatSum *= -1;
                    }

                    $rowSum += $vatSum;

                    $row = [];
                    $row['id'] = 0;
                    $row['id_buy_doc'] = $nID;
                    $row['id_office'] = $ddsOffice;
                    $row['id_person'] = $nIDUser;
                    $row['id_salary_row'] = 0;
                    $row['id_order'] = 0;
                    $row['month'] = date("Y-m-d");
                    $row['quantity'] = 1;
                    $row['measure'] = "бр.";
                    $row['paid_sum'] = 0;
                    $row['paid_date'] = "0000-00-00";
                    $row['is_dds'] = 1;
                    $row['single_price'] = $vatSum;
                    $row['total_sum'] = $vatSum;

                    $oBuyDocRows->update($row, null, null, $nID);
                }
                //die(var_dump($rowSum));
                //die(print("<pre>".print_r(abs(($rowSum - $totalSum)),true)."</pre>"));

                /* if ( $document['doc_type'] == "kreditno izvestie" ) {
                    if ( abs(($rowSum + $totalSum)) > 0.05 ) {
                        throw new Exception("Сумата от редовете и посоченият тотал се \nразминават с повече от пет стотинки!", DBAPI_ERR_FAILED_TRANS);
                    }
                } */
                else if ( abs(($rowSum - $totalSum)) > 0.05 ) {
                    throw new Exception("Сумата от редовете и посоченият тотал се \nразминават с повече от пет стотинки!", DBAPI_ERR_FAILED_TRANS);
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

    public function updateDocument() {
        global $db_sod, $db_system, $db_finance, $db_name_finance;

        $post = json_decode(file_get_contents('php://input'), true);

        if ( !isset($post['document_data']) || !isset($post['document_rows']) ) {
            return $this->setError("Невалиден документ!");
        }

        $document = $post['document_data'] ?? [];
        $docRows = $post['document_rows'] ?? [];

        $nID = $document['id'] ?? 0;
        $nIDUser = $this->getPerson();
        $totalSum = $document['doc_type'] == "kreditno izvestie" ? ($post['totalSum'] *= -1 ?? 0) : $post['totalSum'] ?? 0;
        $vatSum = $post['vatSum'] ?? 0;
        $rowSum = 0;

        if ( !$this->isValidID($nID) ) {
            return $this->setError("Невалиден документ: {$nID}");
        }

        if ( empty($docRows) ) {
            return $this->setError("Документа е празен!");
        }

        // Право за редакция - добавяме, преглед и ордери
        $docViewRight = in_array('buy_doc_view', $_SESSION['userdata']['access_right_levels']);
        $docEditRight = in_array('buy_doc_edit', $_SESSION['userdata']['access_right_levels']);
        $docGrantRight = in_array('buy_doc_grant', $_SESSION['userdata']['access_right_levels']);
        $docOrderGrantRight = in_array('orders_doc_grant', $_SESSION['userdata']['access_right_levels']);
        $docOrderViewRight = in_array('buy_doc_order_view', $_SESSION['userdata']['access_right_levels']) && $docViewRight;
        $docOrderEditRight = in_array('buy_doc_order_edit', $_SESSION['userdata']['access_right_levels']) && $docViewRight;

        if ( empty($docEditRight) ) {
            return $this->setError("Нямате достатъчно права за операцията!");
        }

        if ( !isset($document['doc_type']) ) {
            return $this->setError("Невалидни параметри - документа не може да бъде създаден!");
        }

        $docOrigin = $this->getDocData($nID);

        if ( empty($docOrigin) || !isset($docOrigin['id']) ) {
            return $this->setError("Невалиден документ: {$nID}");
        }

        if ( $docOrigin['doc_status'] == "canceled" ) {
            return $this->setError("Документа вече е анулиран!");
        }

        $docRowsOrigin = $this->getDocRows($nID);

        $oDocument = new DBMonthTable($db_name_finance,PREFIX_BUY_DOCS,$db_finance);
        $oDocRows = new DBMonthTable($db_name_finance,PREFIX_BUY_DOCS_ROWS,$db_finance);

        $oService = new DBNomenclaturesExpenses();
        $oFirms = new DBFirms();

        // orders
        $orderSum = abs($docOrigin['orders_sum']);
        
        if ( abs($orderSum) > 0.01 ) {
            return $this->setError("По документа има плащания!");

            /*
            $orders = $this->getOrdersByDoc($nID);

            if ( !empty($orders) ) {    //&& (!$docGrantRight || !$docOrderGrantRight)
                return $this->setError("По докуменмта има плащания!");
            }
            */
        }

        $db_finance->StartTrans();
        $db_system->StartTrans();
        $db_sod->StartTrans();

        try {
            $aData = [];

            $aData['id'] = $document['id'];
            $aData['doc_num'] = $document['doc_num'] ?: $docOrigin['doc_num'];
            $aData['doc_date'] = $document['doc_date'] ?: $docOrigin['doc_date'];
            $aData['id_deliverer'] = $document['id_deliverer'] ?: $docOrigin['id_deliverer'];
            $aData['deliverer_name'] = $document['deliverer_name'] ?: $docOrigin['deliverer_name'];
            $aData['deliverer_address'] = $document['deliverer_address'] ?: $docOrigin['deliverer_address'];
            $aData['deliverer_ein'] = $document['deliverer_ein'] ?: $docOrigin['deliverer_ein'];
            $aData['deliverer_ein_dds'] = $document['deliverer_ein_dds'] ?: $docOrigin['deliverer_ein_dds'];
            $aData['deliverer_mol'] = $document['deliverer_mol'] ?: $docOrigin['deliverer_mol'];
            $aData['orders_sum'] = 0;
            $aData['total_sum'] = $totalSum;
            $aData['note'] = $document['note'] ?? "";
            $aData['created_time'] = $document['doc_date_create'] ?: $docOrigin['created_time'];
            $aData['updated_user'] = $nIDUser;
            $aData['updated_time'] = time();

            $oDocument->update($aData);

            foreach ( $docRowsOrigin as $val ) {
                $oDocRows->delete($val['id']);
            }

            $isSpecial		= false;
            $isNormal		= false;

            foreach ( $docRows as $key => $row ) {
                if ( !($row['id_office'] ?? false) ) {
                    throw new Exception("Има запис без фирма/регион!!!", DBAPI_ERR_FAILED_TRANS);
                }

                if ( !($row['id_nomenclature_expense'] ?? false) ) {
                    throw new Exception("Има запис без номенклатура!!!", DBAPI_ERR_FAILED_TRANS);
                }

                if ( !($row['id_direction'] ?? false) && $row['id_nomenclature_expense'] > 0 ) {
                    throw new Exception("Има запис без направление!!!", DBAPI_ERR_FAILED_TRANS);
                }

                // Прехвърляне по сметка - забраняваме тип на документ различен от витанция!
                $bTransfer	= $oService->checkForTransfer($row['id_nomenclature_expense']);

                if ( $bTransfer ) {
                    if ( $document['doc_type'] != "oprostena" ) {
                        throw new Exception("Документ с НАПРАВЛЕНИЕ по ТРАНСФЕР\nможе да бъде само квитанция!", DBAPI_ERR_FAILED_TRANS);
                    }

                    $isSpecial = true;
                }

                // Номенклатура ДДС
                if ( $row['id_nomenclature_expense'] == -1 ) {
                    $row['id_nomenclature_expense'] = 0;
                    $isDds = 2;
                    $isSpecial = true;

                    $aFirm 		= $oFirms->getFirmByIDOffice($row['id_office']);
                    $row['id_office'] = $aFirm['id_office_dds'] ?? 0;

                    if ( $document['doc_type'] != "oprostena" ) {
                        throw new Exception("В описа на документа има номенклатура ДДС!\nМоля, изберете \"Квитанция\"!", DBAPI_ERR_FAILED_TRANS);
                    }
                } else {
                    $isDds = $row['is_dds'] ?? 0;

                    if ( !$bTransfer ) {
                        $isNormal = true;
                    }
                }

                if ( $document['doc_type'] == "kreditno izvestie" ) {
                    $row['total_sum'] *= -1;
                    $row['single_price'] *= -1;
                }

                $rowSum += $row['total_sum'];

                $row['id'] 				= 0;
                $row['id_buy_doc'] 		= $nID;
                $row['id_person'] 		= $nIDUser;
                $row['id_salary_row'] 	= 0;
                $row['id_order'] 		= 0;
                $row['month'] 	        = $row['month'] ?? date("Y-m-d");
                $row['quantity']	    = 1;
                $row['measure']			= "бр.";
                $row['paid_sum']		= 0;
                $row['single_price']	= $row['total_sum'];
                $row['paid_date']       = "0000-00-00";
                $row['is_dds']	 		= $isDds;

                $oDocRows->update($row, null, null, $nID);
            }

            if ( $isSpecial && $isNormal ) {
                throw new Exception("В документа има комбинация от услуги и ДДС/ТРАНСФЕР!!!", DBAPI_ERR_INVALID_PARAM);
            }

            // ДДС
            if ( $document['doc_type'] != "oprostena" && abs($vatSum) > 0 ) {
                $ddsFirm = $this->getDDSFirmByEIN($document['client_ein']);
                $ddsOffice = $ddsFirm['id_office_dds'] ?? 0;

                if (empty($ddsOffice)) {
                    throw new Exception("Не може да бъде намерен офис по ДДС!", DBAPI_ERR_FAILED_TRANS);
                }

                if ( $document['doc_type'] == "kreditno izvestie" ) {
                    $vatSum *= -1;
                }

                $rowSum += $vatSum;

                $row = [];
                $row['id'] 				= 0;
                $row['id_buy_doc'] 		= $nID;
                $row['id_office'] 		= $ddsOffice;
                $row['id_person'] 		= $nIDUser;
                $row['id_salary_row'] 	= 0;
                $row['id_order'] 		= 0;
                $row['month'] 	        = date("Y-m-d");
                $row['quantity']	    = 1;
                $row['measure']			= "бр.";
                $row['paid_sum']		= 0;
                $row['paid_date']       = "0000-00-00";
                $row['is_dds']	 		= 1;
                $row['single_price']	= $vatSum;
                $row['total_sum']	 	= $vatSum;

                $oDocRows->update($row, null, null, $nID);
            }

            //echo $rowSum . " - " . $totalSum . " - " . $vatSum;
            //die();

            if ( abs($rowSum - $totalSum) > 0.05 ) {
                throw new Exception("Сумата от редовете и посоченият тотал се \nразминават с повече от пет стотинки!", DBAPI_ERR_FAILED_TRANS);
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

    public function annulment() {
        global $db_sod, $db_system, $db_finance, $db_name_finance;

        $post = json_decode(file_get_contents('php://input'), true);

        if ( !isset($post['document_data']['id']) || empty($post['document_data']['id']) ) {
            return $this->setError("Невалиден документ!");
        }

        $nID = $post['document_data']['id'] ?? 0;
        $nIDUser = $this->getPerson();

        if ( !$this->isValidID($nID) ) {
            return $this->setError("Невалиден документ: {$nID}");
        }

        $document = $this->getDocData($nID);

        if ( empty($document) || !isset($document['id']) ) {
            return $this->setError("Невалиден документ: {$nID}");
        }

        if ( $document['doc_status'] == "canceled" ) {
            return $this->setError("Документа вече е анулиран!");
        }

        $docGrantRight = false;
        $docEditRight = in_array('buy_doc_edit', $_SESSION['userdata']['access_right_levels']);

        // При пълно право за редакция - добавяме и право за преглед и редакция
        if ( in_array('buy_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
            $docGrantRight 	= true;
            $docEditRight = true;
        }

        // Друго право за редакция...
        if ( in_array('orders_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
            $docGrantRight 	= true;
            $docEditRight = true;
        }

        if ( !$docEditRight ) {
            return $this->setError("Нямате достатъчно права за операцията!");
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

                foreach ( $orders as $order ) {
                    if ( isset($order['order_status']) && ($order['order_status'] == "active") ) {
                        $nIDOrder = $order['id'] ?? 0;

                        if ( !$this->isValidID($nIDOrder) ) {
                            throw new Exception("Невалиден ордер {$order['num']}", DBAPI_ERR_INVALID_PARAM);
                        }
                    }
                }

                $oPay = new NEWDBFinanceOperations();

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

            $sDocName	= PREFIX_BUY_DOCS . substr($nID, 0, 6); // PREFIX_BUY_DOCS
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

        return ["document_data" => $document];
    }

    public function annulmentOrder() {
        $post = json_decode(file_get_contents('php://input'), false);

        if ( !isset($post->id) ) {
            return $this->setError("Невалиден документ!");
        }

        $nID = $post->id ?? 0;

        if ( !$this->isValidID($nID) ) {
            return $this->setError("Невалиден документ {$nID}!");
        }

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

    public function makeOrder() {
        $post = json_decode(file_get_contents('php://input'), true);

        if ( !isset($post['doc_id']) || !isset($post['bank_account_id']) ) { //|| !isset($post['order_sum'])
            return $this->setError("Невалиден документ!");
        }

        $nID 	        = $post['doc_id'] ?? 0;
        $nIDAccount 	= $post['bank_account_id'] ?? 0;
        $orderSum    	= $post['order_sum'] ?? 0;     //sprintf("%01.2f", $post['order_sum'])

        if ( !$this->isValidID($nID) ) {
            return $this->setError("Невалиден документ!");
        }

        if ( empty($nIDAccount) ) {
            return $this->setError("Изберете валидна сметка!");
        }

        $oPay = new NEWDBFinanceOperations();
        $oPay->makeExpenseOrder($nID, $nIDAccount, $orderSum);
        
        $alerts = $oPay->getAlerts();

        if ( !empty($alerts) ) {
            $this->alerts = $alerts;
        }

        $err = $oPay->getError();

        if ( !empty($err) ) {
            return $this->setError($err);
        }
        

        return ['status' => 'OK'];
    }
    public function makeAdvice() {
        $post = json_decode(file_get_contents('php://input'), true);

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

        // Ново известие
        $post['document_data']['id_advice'] = 0;
        $post['document_data']['is_advice'] = 0;
        $post['document_data']['id_advice'] = $nID;
        $post['document_data']['id'] = 0;
        //die(print("<pre>".print_r($post,true)."</pre>"));
        return $this->store($post);
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
        
        $docData = SQL_union_search($db_finance, $sQuery, PREFIX_BUY_DOCS, "______", "id", "DESC");

        foreach ( $docData as &$doc ) {
            if ( $doc['doc_type'] == "kreditno izvestie" ) {
                $doc['total_sum'] *= -1;
                $doc['orders_sum'] *= -1;
            }
        }

        unset($doc);
        //die(print("<pre>".print_r($docData,true)."</pre>"));
        return $docData;
    }
}


function castDocumentData($data) {
    if(empty($data)) return $data;

    $data['id'] = intval($data['id']);
    $data['id_advice'] = intval($data['id_advice']);
    $data['is_advice'] = intval($data['is_advice']);
    $data['is_hide'] = intval($data['is_hide']);
    $data['id_deliverer'] = intval($data['id_deliverer']);
    $data['for_fuel'] = intval($data['for_fuel']);
    $data['for_gsm'] = intval($data['for_gsm']);
    $data['total_sum'] = floatval($data['total_sum']);
    $data['orders_sum'] = floatval($data['orders_sum']);
    $data['last_order_id'] = intval($data['last_order_id']);
    $data['id_cash_default'] = intval($data['id_cash_default']);
    $data['user_office_id'] = intval($data['user_office_id']);
    $data['exported'] = intval($data['exported']);

    return $data;
}
function castClients($data) {
    if(empty($data)) return $data;

    foreach ($data as $key => $value) {
        $data[$key]['id_office_dds'] = intval($data[$key]['id_office_dds']);
    }

    return $data;
}
function castRegions($data) {
    if(empty($data)) return $data;

    foreach ($data as $key => $value) {
        $data[$key]['id_firm'] = intval($data[$key]['id_firm']);
        $data[$key]['id_office'] = intval($data[$key]['id_office']);
        $data[$key]['id_office_dds'] = intval($data[$key]['id_office_dds']);
    }

    return $data;
}
function castFunds($data) {
    if(empty($data)) return $data;

    foreach ($data as $key => $value) {
        $data[$key]['id'] = intval($data[$key]['id']);
        $data[$key]['id_firm'] = intval($data[$key]['id_firm']);
    }

    return $data;
}
function castNomenclatureGroups($data) {
    if(empty($data)) return $data;

    foreach ($data as $key => $value) {
        $data[$key]['id'] = intval($data[$key]['id']);
    }

    return $data;
}
function castNomenclatures($data) {
    if(empty($data)) return $data;

    foreach ($data as $key => $value) {
        $data[$key]['id'] = intval($data[$key]['id']);
        $data[$key]['code'] = intval($data[$key]['code']);
        $data[$key]['id_group'] = intval($data[$key]['id_group']);
    }

    return $data;
}
function castBankAccounts($data) {
    if(empty($data)) return $data;

    foreach ($data as $key => $value) {
        $data[$key]['id'] = intval($data[$key]['id']);
        $data[$key]['is_paid'] = intval($data[$key]['is_paid']);
        $data[$key]['tax'] = floatval($data[$key]['tax']);
    }

    return $data;
}
function castDocumentRows($data) {
    if(empty($data)) return $data;

    foreach ($data as $key => $value) {
        $data[$key]['id'] = intval($data[$key]['id']);
        $data[$key]['id_buy_doc'] = intval($data[$key]['id_buy_doc']);
        $data[$key]['id_firm'] = intval($data[$key]['id_firm']);
        $data[$key]['id_office'] = intval($data[$key]['id_office']);
        $data[$key]['id_object'] = intval($data[$key]['id_object']);
        $data[$key]['id_direction'] = intval($data[$key]['id_direction']);
        $data[$key]['id_nomenclature_expense'] = intval($data[$key]['id_nomenclature_expense']);
        $data[$key]['id_salary_row'] = intval($data[$key]['id_salary_row']);
        $data[$key]['id_order'] = intval($data[$key]['id_order']);
        $data[$key]['id_schet_row'] = intval($data[$key]['id_schet_row']);
        $data[$key]['quantity'] = floatval($data[$key]['quantity']);
        $data[$key]['single_price'] = floatval($data[$key]['single_price']);
        $data[$key]['total_sum'] = floatval($data[$key]['total_sum']);
        $data[$key]['paid_sum'] = floatval($data[$key]['paid_sum']);
        $data[$key]['is_dds'] = intval($data[$key]['is_dds']);
    }

    return $data;
}
function castOrders($data) {
    if(empty($data)) return $data;

    foreach ($data as $key => $value) {
        $data[$key]['id'] = intval($data[$key]['id']);
        $data[$key]['num'] = intval($data[$key]['num']);
        $data[$key]['order_sum'] = floatval($data[$key]['order_sum']);
    }

    return $data;
}
function castRelations($data) {

    if(empty($data)) return $data;

    foreach ($data as $key => $value) {
        $data[$key]['id'] = intval($data[$key]['id']);
        $data[$key]['id_deliverer'] = intval($data[$key]['id_deliverer']);
        $data[$key]['id_client'] = intval($data[$key]['id_client']);
        $data[$key]['total_sum'] = floatval($data[$key]['total_sum']);
        $data[$key]['orders_sum'] = floatval($data[$key]['orders_sum']);
        $data[$key]['last_order_id'] = intval($data[$key]['last_order_id']);
        $data[$key]['id_bank_epayment'] = intval($data[$key]['id_bank_epayment']);
        $data[$key]['id_bank_account'] = intval($data[$key]['id_bank_account']);
        $data[$key]['is_book'] = intval($data[$key]['is_book']);
        $data[$key]['id_advice'] = intval($data[$key]['id_advice']);
        $data[$key]['is_advice'] = intval($data[$key]['is_advice']);
        $data[$key]['id_credit_master'] = intval($data[$key]['id_credit_master']);
        $data[$key]['is_auto'] = intval($data[$key]['is_auto']);
        $data[$key]['exported'] = intval($data[$key]['exported']);
        $data[$key]['version'] = intval($data[$key]['version']);
        $data[$key]['gen_pdf'] = intval($data[$key]['gen_pdf']);
        $data[$key]['is_user_print'] = intval($data[$key]['is_user_print']);
        $data[$key]['for_fuel'] = intval($data[$key]['for_fuel']);
        $data[$key]['for_gsm'] = intval($data[$key]['for_gsm']);
        $data[$key]['is_hide'] = intval($data[$key]['is_hide']);
    }

    return $data;
}

if ( !isset($_SESSION['telenet_valid_session']) || $_SESSION['telenet_valid_session'] !== true ) {
    http_response_code(401);

    echo json_encode(["error" => "Not authorized"]);
    die();
}

$buy = new BuyController();
$flag = false;
$data = [];

$request = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";


switch ($request) {
    case "init":

        $data = [];
        $data['document_data'] = castDocumentData($buy->getBlankDoc());

        $data['clients'] = castClients($buy->getFirmsAsClient());
        $data['regions'] = castRegions($buy->getFirmsByOffice());
        $data['firms'] = $buy->getFirmNames($data['regions']);
        $data['funds'] = castFunds($buy->getFunds());
        $data['nomenclature_groups'] = castNomenclatureGroups($buy->getNomenclatureGroups());
        $data['nomenclatures'] = castNomenclatures($buy->getNomenclatures());
        $data['bank_accounts'] = castBankAccounts($buy->getBankAccountsForOrders());

        // Todo: клиент по касиер!
        $key = array_search("837037876", array_column($data['clients'], 'ein'));
        $data['default_client'] = isset($data['clients'][$key]['name']) && !empty($data['clients'][$key]['name']) ? $data['clients'][$key]['name'] : "ТЕЛЕПОЛ ЕООД";

        $data['alerts'] = $buy->getAlerts();

        //echo json_encode($data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
        echo json_encode($data);
        $flag = true;

        break;

    case "load":
        $nID = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

        $data = [];
        $data['document_data'] = castDocumentData($buy->getDocData($nID));
        $data['document_rows'] = castDocumentRows($buy->getDocRows($nID));
        $data['orders'] = castOrders($buy->getOrdersByDoc($nID));
        $data['alerts'] = $buy->getAlerts();
        

        if(!empty($data) && $data['document_data']['doc_type'] == 'kreditno izvestie') {
            
            $data['document_data']['total_sum'] *= -1;
            $data['document_data']['orders_sum'] *= -1;
            
            foreach ( $data['document_rows'] as $key => $value ) {
                $data['document_rows'][$key]['single_price'] *= -1;
                $data['document_rows'][$key]['total_sum'] *= -1;
                $data['document_rows'][$key]['paid_sum'] *= -1;
            }

        }
        
        if ( !empty($data['document_data']['doc_type']) && in_array($data['document_data']['doc_type'], ["kreditno izvestie", "debitno izvestie"]) ) {
            $data['origin_document'] = castDocumentData($buy->getDocData($data['document_data']['id_advice']));
        } else {
            $data['origin_document'] = [];
        }

        echo json_encode($data);
        //echo json_encode($data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
        $flag = true;

        break;

    case "store":
        $data = $buy->store();
        $data['alerts'] = $buy->getAlerts();

        echo json_encode($data, JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK);
        $flag = true;

        break;

    case "update":
        $data = $buy->updateDocument();
        $data['alerts'] = $buy->getAlerts();

        echo json_encode($data, JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK);
        $flag = true;
        break;

    case "annulment":
        $data = $buy->annulment();
        $data['alerts'] = $buy->getAlerts();

        echo json_encode($data, JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK);
        $flag = true;
        break;

    case "annulment_order":
        $data = $buy->annulmentOrder();

        echo json_encode($data, JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK);
        $flag = true;
        break;

    case "make_order":
        //$data = $buy->makeOrder();
        try {
            $data = $buy->makeOrder();
            $data['alerts'] = $buy->getAlerts();
        } catch (NoticeException $nex) {
            // Nothing
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(["error" => $ex->getMessage()]);
            die();
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
            die();
        }

        echo json_encode($data, JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK);
        $flag = true;
        break;

    case "make_advice":
        try {
            $data = $buy->makeAdvice();
            $data['alerts'] = $buy->getAlerts();
        } catch (NoticeException $nex) {
            // Nothing
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(["error" => $ex->getMessage()]);
            die();
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
            die();
        }

        echo json_encode($data, JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK);
        $flag = true;
        break;

    case "get_relations":
        try {
            $nID = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;
            $data['alerts'] = $buy->getAlerts();
            $data['relations'] = castRelations($buy->getRelations($nID));

        } catch (NoticeException $nex) {
            // Nothing
        } catch (Exception $ex) {
            http_response_code(400);
            echo json_encode(["error" => $ex->getMessage()]);
            die();
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
            die();
        }

        echo json_encode($data);
        $flag = true;
        break;

}

if ( !$flag ) {
    http_response_code(400);

    echo json_encode(["error" => "Bad Request"]);
    die();
}
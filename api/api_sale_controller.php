<?php
/**
 * Created by PhpStorm.
 * User: adm
 * Date: 23.10.2019 г.
 * Time: 16:09
 */

if (!isset($_SESSION)) {
    session_start();
}

$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );
set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../' );

require_once("../config/function.autoload.php");
require_once("../include/adodb/adodb-exceptions.inc.php");
require_once("../config/connect.inc.php");
require_once("../include/general.inc.php");

class NoticeException extends Exception{
    protected $sDefaultMessage = 'Грешка при изпълнение на операцията.';
    protected $nDefaultCode = 1;

    public function __construct($sMessage = null, $nCode = null) {
        if ( empty($sMessage) ) {
            $sMessage = $this->sDefaultMessage;
        }

        if ( empty($nCode) ) {
            $nCode = $this->nDefaultCode;
        }

        parent::__construct($sMessage, $nCode);
    }
}

set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
    $levels = [2 , 8, 32, 128, 512, 1024, 8192, 16384];

    if ( 0 === error_reporting() ) {
        return false;
    }

    if ( in_array($errno, $levels) ) {
        return true;
    }

    throw new NoticeException($errstr, $errno, $errno, $errfile, $errline);
});

if ( !isset($_SESSION['telenet_valid_session']) || $_SESSION['telenet_valid_session'] !== true ) {
    http_response_code(401);

    echo json_encode(["error" => "Not authorized"]);
    die();
}

/** 
 * vms temp cast shits
 * докато се изнесе кастването директно в класовете които връщат данните първоначално
 * защото е безмислено да въртят по два пъти за нема нищо....
 */ 

function castFirms($data) {
    
    if(empty($data)) return $data;
    
    foreach ($data as $key => $value) {
        $data[$key]['id_firm'] = intval($data[$key]['id_firm']);
    }
    
    return $data;
}

function castRegions($data) {
    
    if(empty($data)) return $data;

    foreach ($data as $key => $value) {
        $data[$key]['id_firm'] = intval($data[$key]['id_firm']);
        $data[$key]['id_office'] = intval($data[$key]['id_office']);
    }
    
    return $data;
}

function castServices($data) {
    
    if(empty($data)) return $data;
    
    foreach ($data as $key => $value) {
        $data[$key]['id_firm'] = intval($data[$key]['id_firm']);
        $data[$key]['id_service'] = intval($data[$key]['id_service']);
        $data[$key]['price'] = floatval($data[$key]['price']);
        $data[$key]['quantity'] = floatval($data[$key]['quantity']);
        $data[$key]['vat'] = intval($data[$key]['vat']);
        $data[$key]['is_month'] = intval($data[$key]['is_month']);
    }
    
    return $data;
}

function castConcessions($data) {
    
    if(empty($data)) return $data;

    foreach ($data as $key => $value) {
        $data[$key]['id'] = intval($data[$key]['id']);
        $data[$key]['id_nomenclature_earning'] = intval($data[$key]['id_nomenclature_earning']);
        $data[$key]['id_object'] = intval($data[$key]['id_object']);
        $data[$key]['id_service'] = intval($data[$key]['id_service']);
        $data[$key]['months_count'] = intval($data[$key]['months_count']);
        $data[$key]['percent'] = intval($data[$key]['percent']);
    }

    return $data;
}

function castBankOrders($data) {
    
    if(empty($data)) return $data;

    foreach ($data as $key => $value) {
        $data[$key]['id'] = intval($data[$key]['id']);
        $data[$key]['is_paid'] = intval($data[$key]['is_paid']);
        $data[$key]['tax'] = floatval($data[$key]['tax']);
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

function castClient($data) {
    if(empty($data)) return $data;

    $data['id'] = intval($data['id']);
    $data['id_wf'] = intval($data['id_wf']);
    $data['invoice_last_paid_caption'] = intval($data['invoice_last_paid_caption']);
    $data['is_company'] = intval($data['is_company']);
    $data['address_confirm'] = intval($data['address_confirm']);
    $data['bill_account'] = intval($data['bill_account']);
    $data['email_confirm'] = intval($data['email_confirm']);
    $data['invoice_auto'] = intval($data['invoice_auto']);
    $data['invoice_bring_to_object'] = intval($data['invoice_bring_to_object']);
    $data['invoice_email_sign'] = intval($data['invoice_email_sign']);
    $data['is_public'] = intval($data['is_public']);
    $data['name_confirm'] = intval($data['name_confirm']);
    $data['sms_phone_confirm'] = intval($data['sms_phone_confirm']);

    return $data;    
}

function castDocumentData($data) {
    
    if(empty($data)) return $data;
    
    $data['id'] = intval($data['id']);
    $data['id_client'] = intval($data['id_client']);
    $data['total_sum'] = floatval($data['total_sum']);
    $data['dds_sum'] = floatval($data['dds_sum']);
    $data['orders_sum'] = floatval($data['orders_sum']);
    $data['last_order_id'] = intval($data['last_order_id']);
    $data['id_bank_epayment'] = intval($data['id_bank_epayment']);
    $data['id_bank_account'] = intval($data['id_bank_account']);
    $data['id_cash_default'] = intval($data['id_cash_default']);
    $data['is_book'] = intval($data['is_book']);
    $data['id_advice'] = intval($data['id_advice']);
    $data['is_advice'] = intval($data['is_advice']);
    $data['id_credit_master'] = intval($data['id_credit_master']);
    $data['is_auto'] = intval($data['is_auto']);
    $data['exported'] = intval($data['exported']);
    $data['version'] = intval($data['version']);
    $data['gen_pdf'] = intval($data['gen_pdf']);
    $data['is_user_print'] = intval($data['is_user_print']);
    $data['user_office_id'] = intval($data['user_office_id']);

    return $data;
}

function castDocumentRows($data) {

    if(empty($data)) return $data;

    foreach ($data as $key => $value) {
        $data[$key]['id'] = intval($data[$key]['id']);
        $data[$key]['id_sale_doc'] = intval($data[$key]['id_sale_doc']);
        $data[$key]['id_office'] = intval($data[$key]['id_office']);
        $data[$key]['id_object'] = intval($data[$key]['id_object']);
        $data[$key]['id_service'] = intval($data[$key]['id_service']);
        $data[$key]['id_duty'] = intval($data[$key]['id_duty']);
        $data[$key]['id_duty_row'] = intval($data[$key]['id_duty_row']);
        $data[$key]['for_smartsot'] = intval($data[$key]['for_smartsot']);
        $data[$key]['vat'] = intval($data[$key]['vat']);
        $data[$key]['quantity'] = floatval($data[$key]['quantity']);
        $data[$key]['single_price'] = floatval($data[$key]['single_price']);
        $data[$key]['total_sum'] = floatval($data[$key]['total_sum']);
        $data[$key]['total_sum_with_dds'] = floatval($data[$key]['total_sum_with_dds']);
        $data[$key]['paid_sum'] = floatval($data[$key]['paid_sum']);
        $data[$key]['is_dds'] = intval($data[$key]['is_dds']);
        $data[$key]['id_firm'] = intval($data[$key]['id_firm']);
        if (array_key_exists('concession_month_count', $data[$key])) {
            $data[$key]['concession_month_count'] = intval($data[$key]['concession_month_count']);
            $data[$key]['percent'] = intval($data[$key]['percent']);
            $data[$key]['reference_service_id'] = intval($data[$key]['reference_service_id']);
        }
    }

    return $data;
}

function castRelations($data) {

    if(empty($data)) return $data;

    foreach ($data as $key => $value) {
        
        $data[$key]['id'] = intval($data[$key]['id']);
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
    }

    return $data;
}

function castDocumentOrders($data) {

    if(empty($data)) return $data;

    foreach ($data as $key => $value) {
        $data[$key]['id'] = intval($data[$key]['id']);
        $data[$key]['num'] = intval($data[$key]['num']);
        $data[$key]['order_sum'] = floatval($data[$key]['order_sum']);
    }

    return $data;
}

//$sale = new SaleController();
$sale = new NEWDBSalesDocs();
$flag = false;
$data = [];

$request = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";

switch ($request) {
    case "init":
        global $db_finance;
        $oDBConcessions  = new DBBase2($db_finance, 'concession');

        try {
            $data['deliverers'] = $sale->getFirmsAsClient();
            $data['bank_orders'] = castBankOrders($sale->getBankAccountsForOrders());
            $data['bank_accounts'] = castBankAccounts($sale->getBankAccounts());
            $data['regions'] = castRegions($sale->getFirmsByOffice());
            $data['firms'] = castFirms($sale->getFirmNames($data['regions']));
            $data['services'] = castServices($sale->getFirmServices());
            $data['view_types'] = ['single', 'detail', 'by_objects', 'by_services', 'extended'];
            $data['concessions'] = castConcessions($oDBConcessions->getAll());

            // Todo: da se implementira!!!
            $aDeliverer = $sale->getDelivererByPerson();
            $data['default_deliverer'] = isset($aDeliverer['jur_name']) ? $aDeliverer['jur_name'] : "ТЕЛЕПОЛ ЕООД";

            // Doc ID
            $nID = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;

            $data['document_data'] = castDocumentData($sale->getDocData($nID));
            $data['alerts'] = $sale->getAlerts();

            //trigger_error("Грешка някаква, ама що с код 2...", E_USER_ERROR);
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

    case "duty":
        try {
            $clientId = $_GET['id_client'] ?? 0;
            $objectId = $_GET['id_object'] ?? 0;
            $delivererName = $_GET['deliverer_name'] ?? "";
            $documentDate = $_GET['duty_date'] ?? date("Y-m-01");
            $nID = $_GET['id'] ?? 0;
            $isCreditAdvice = false;

            $docData = $sale->getDocData($nID);

            if ( empty($nID) ) {
                if (!empty($clientId)) {
                    $data['document_rows'] = $sale->getDuty($clientId, $delivererName, $documentDate);
                } else if (!empty($objectId)) {
                    $data['document_rows'] = $sale->getDutyByOneObject($objectId, $documentDate);
                } else {
                    $data['document_rows'] = [];
                }

                $data['orders'] = [];
            } else {
                $docRows = $sale->getDocRows($nID);
                $docOrders = $sale->getOrdersByDoc($nID);

                if ($isCreditAdvice) {
                    if (!empty($docRows) && is_array($docRows)) {
                        foreach ($docRows as &$row) {
                            $row['single_price'] *= -1;
                            $row['total_sum'] *= -1;
                            $row['paid_sum'] *= -1;
                            $row['total_sum_with_dds'] *= -1;
                        }
                    }

                    unset($row);

                    if (!empty($docOrders) && is_array($docOrders)) {
                        foreach ($docOrders as &$row) {
                            $row['order_sum'] *= -1;
                        }
                    } else {
                        $docOrders = [];
                    }
                }

                $data['document_rows'] = $docRows;
                $data['orders'] = $docOrders;

                $clientId = $docData['id_client'] ?? 0;
            }

            $aClient = $sale->getClient();

            if ( !empty($clientId) ) {
                $aClient = $sale->getClientByID($clientId);
                $sale->setClient($aClient);
            }

            $docData = $sale->getDocData($nID);

            if ( $docData['doc_type'] == "kreditno izvestie" ) {
                $docData['total_sum'] *= -1;
                $docData['orders_sum'] *= -1;

                if ( !empty($data['document_rows']) ) {
                    foreach ( $data['document_rows'] as $key => $adviceRow ) {
                        $data['document_rows'][$key]['total_sum'] *= -1;
                        $data['document_rows'][$key]['single_price'] *= -1;
                        $data['document_rows'][$key]['paid_sum'] *= -1;
                        $data['document_rows'][$key]['total_sum_with_dds'] *= -1;
                    }
                }

                $isCreditAdvice = true;
            }

            $data['document_data'] = $docData;

            $data['alerts'] = $sale->getAlerts();
            $data['client'] = $sale->getClient();

            if ($nID == 0) {
                $data['document_data']['id_client'] = $data['client']['id'] ?? 0;
                $data['document_data']['client_name'] = $data['client']['name'] ?? "";
                $data['document_data']['client_ein'] = $data['client']['invoice_ein'] ?? "";
                $data['document_data']['client_ein_dds'] = $data['client']['invoice_ein_dds'] ?? "";
                $data['document_data']['client_address'] = $data['client']['invoice_address'] ?? "";
                $data['document_data']['client_mol'] = $data['client']['invoice_mol'] ?? "";
                $data['document_data']['client_recipient'] = $data['client']['invoice_recipient'] ?? "";
            }

            if ( !empty($docData['doc_type']) && in_array($docData['doc_type'], ["kreditno izvestie", "debitno izvestie"]) ) {
                $data['origin_document'] = castDocumentData($sale->getDocData($docData['id_advice']));
            } else {
                $data['origin_document'] = [];
            }
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

        $data['client'] = castClient($data['client']);
        $data['document_data'] = castDocumentData($data['document_data']);
        $data['document_rows'] = castDocumentRows($data['document_rows']);
        $data['orders'] = castDocumentOrders($data['orders']);

        echo json_encode($data);
        $flag = true;
        break;

    case "store":
        try {
            $data = $sale->store();
            $data['alerts'] = $sale->getAlerts();
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

    case "make_order":
        try {
            $data = $sale->makeOrder();
            $data['alerts'] = $sale->getAlerts();
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

    case "annulment_order":
        try {
            $data = $sale->annulmentOrder();
            $data['alerts'] = $sale->getAlerts();
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

    case "update_client":
        try {
            $data = $sale->updateClient();
            $data['alerts'] = $sale->getAlerts();
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
            $data = $sale->makeAdvice();
            $data['alerts'] = $sale->getAlerts();
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

            $data['alerts'] = $sale->getAlerts();
            $data['relations'] = castRelations($sale->getRelations($nID));
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

    case "annulment":
        try {
            $data = $sale->annulment();
            $data['alerts'] = $sale->getAlerts();
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

    case "update":
        try {
            $data = $sale->updateDocument();
            $data['alerts'] = $sale->getAlerts();
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
}

if ( !$flag ) {
    http_response_code(400);

    echo json_encode(["error" => "Bad Request"]);
    die();
}
<?php
/**
 * Created by PhpStorm.
 * User: lamerko
 * Date: 20.1.2015 г.
 * Time: 12:17
 */

class DBFinanceOperations extends DBMonthTable {
    public $withThrown      = true;
    public $withCashier     = 0;
    public $withUser        = true;
    public $massPay         = false;
    private $currentUser    = 0;

    function __construct() {

    }

    private function getCurentUser() {
        $this->currentUser = $this->withUser ? (isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0) : 0;
    }

    private function getUserRights() {

    }

    private function makeError($message) {
        throw new Exception($message, DBAPI_ERR_INVALID_PARAM);
    }

//    public function makeBuyOrder( $nIDDocument, $nIDAccount, $forPay = array(), $is_pay_dds = false ) {
//        global $db_sod, $db_system, $db_finance, $db_name_system, $db_name_sod, $db_name_finance, $mname;
//
//        $oBuyDocRows	= new DBBuyDocsRows();
//        $oBuyDoc		= new DBBuyDocs();
//        $oFirms 		= new DBFirms();
//        $oOffices       = new DBOffices();
//        $oOrders		= new DBOrders();
//        $oOrdersRows	= new DBOrdersRows();
//        $oSaldo			= new DBSaldo();
//        $oObject        = new DBObjects();
//        $oBank			= new DBBankAccounts();
//
//        $nIDFirm        = 0;
//        $nPaidSum       = 0;
//        $nAccState      = 0;
//
//        // Инитализация
//        $this->initalize();
//
//        // Валидации
//        if ( empty($nIDDocument) || !$this->isValidID($nIDDocument) ) {
//            $this->makeError("Невалиден документ!");
//        }
//
//        if ( empty($nIDAccount) || !is_numeric($nIDAccount) ) {
//            $this->makeError("Изберете валидна сметка!");
//        }
//
//        $nIDTran	    = $oBuyDocRows->checkForTransfer( $nIDDocument );
//        $nIDTran2	    = $oBuyDocRows->checkForTransfer( $nIDDocument, 1 );
//
//        if ( !empty($nIDTran) && !empty($nIDTran2) ) {
//            $this->makeError("В документа участват комбинации от услуги и ТРАНСФЕР!!!");
//        }
//
//        $sTypeBank		= $oBank->getTypeAccoutById($nIDAccount);
//        $aDocument		= $oBuyDoc->getDoc($nIDDocument);
//        $aDocumentRows	= $oBuyDocRows->getRowsByDoc($nIDDocument);
//
//        if ( isset($aDocument['doc_status']) && $aDocument['doc_status'] == "canceled" ) {
//            $this->makeError("Документа е анулиран!");
//        }
//
//        // ДДС
//        $aDDS 		    = $oBuyDocRows->getDDSByDoc($nIDDocument);
//        $nDDSUnpayed	= isset($aDDS[0]['paid_sum']) 	? $aDDS[0]['sum'] - $aDDS[0]['paid_sum'] 	    : 0;
//        $nIDDDS 		= isset($aDDS[0]['id']) 		? $aDDS[0]['id'] 								: 0;
//        $nDDSType       = isset($aDDS[0]['is_dds']) 	? $aDDS[0]['is_dds'] 							: 1;
//        $sDDSDate       = isset($aDDS[0]['month']) 	    ? $aDDS[0]['month'] 							: date("Y-m-d");
//        $checkDDS	    = $oBuyDocRows->checkForDDS($nIDDocument);
//
//        $sBuyName 	    = PREFIX_BUY_DOCS.substr($nIDDocument, 0, 6);
//        $sRowsName	    = PREFIX_BUY_DOCS_ROWS.substr($nIDDocument, 0, 6);
//
//        $db_finance->StartTrans();
//        $db_system->StartTrans();
//        $db_sod->StartTrans();
//
//        try {
//            // Следващ номер за ордер
//            $oRes       = $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
//            $nLastOrder = !empty($oRes->fields['last_num_order'])   ? $oRes->fields['last_num_order'] + 1   : 0;
//
//            // НАЧАЛНА наличност по сметка
//            $oRes       = $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1");
//            $nAccState  = !empty($oRes->fields['current_sum'])      ? $oRes->fields['current_sum']          : 0;
//
//            $nEin       = isset($aDocument['client_ein'])           ? $aDocument['client_ein']              : 0;
//            $nIDClient  = isset($aDocument['id_deliverer'])         ? $aDocument['id_deliverer']            : 0;
//            $nDocNum    = isset($aDocument['doc_num'])              ? $aDocument['doc_num']                 : 0;
//
//            $aFirm      = $oFirms->getDDSFirmByEIN($nEin);
//            $nIDFirm    = isset($aFirm['id'])                       ? $aFirm['id']                          : 0;
//
//            $aDataOrder                     = array();
//            $aDataOrder['id']				= 0;
//            $aDataOrder['num']				= $nLastOrder;
//            $aDataOrder['doc_num']			= $nDocNum;
//            $aDataOrder['order_type'] 		= "expense";
//            $aDataOrder['id_transfer']		= 0;
//            $aDataOrder['id_contragent']	= $nIDClient;
//            $aDataOrder['id_doc_firm']		= $nIDFirm;
//            $aDataOrder['order_date']		= time();
//            $aDataOrder['order_sum']		= 0;
//            $aDataOrder['account_type']		= $sTypeBank;
//            $aDataOrder['id_person']	    = $this->currentUser;
//            $aDataOrder['account_sum']		= 0;
//            $aDataOrder['bank_account_id']	= $nIDAccount;	//isset($aParams['cbAccount']) ? $aParams['cbAccount'] : 0;
//            $aDataOrder['doc_id']			= $nIDDocument;
//            $aDataOrder['doc_type']			= "buy";
//            $aDataOrder['note']				= $this->massPay ? "Групово валидиране!" : "";        // TODO:
//            $aDataOrder['created_user']		= $this->currentUser;
//            $aDataOrder['created_time']		= time();
//            $aDataOrder['updated_user']		= $this->currentUser;
//            $aDataOrder['updated_time']		= time();
//
//            $oOrders->update($aDataOrder);
//
//            // Вдигаме номер за следващ ордер
//            $db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");
//
//            $nIDOrder                   = $aDataOrder['id'];
//
//            // Плащаме първо ДДС
//            if ( $is_pay_dds ) {
//                $sFirmName	        = isset($aFirms['name']) 	? $aFirms['name'] 	: "";
//
//                $aSaldo			    = $oSaldo->getSaldoByFirm($nIDFirm, 1);
//                $nIDSaldo		    = 0;
//                $nCurrentSaldo	    = 0;
//
//                if ( !empty($aSaldo) ) {
//                    $nIDSaldo 	    = $aSaldo['id'];
//                }
//
//                // Салдо на фирмата с изчакване!!!
//                if ( !empty($nIDSaldo) ) {
//                    $oRes           = $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
//                    $nCurrentSaldo  = !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
//                } else {
//                    $this->makeError("Неизвестно салдо по фирма!");
//                }
//
//                // Наличност по сметка
//                if ( !empty($nIDAccount) ) {
//                    $oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1 FOR UPDATE");
//                    $nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;
//                } else {
//                    $this->makeError("Неизвестнa сметка!");
//                }
//
//                if ( $nAccountState < $nDDSUnpayed ) {
//                    $this->makeError("Нямате достатъчно наличност по сметката!!!");
//                }
//
//                // Ордери - разбивка!
//                $aDataRows								= array();
//                $aDataRows['id']						= 0;
//                $aDataRows['id_order']					= $nIDOrder;
//                $aDataRows['id_doc_row']				= $nIDDDS;
//                $aDataRows['id_office']					= isset($aDDS[0]['id_office']) 		? $aDDS[0]['id_office']		: 0;
//                $aDataRows['id_object']					= 0;
//                $aDataRows['id_service']				= 0;
//                $aDataRows['id_direction']				= 0;
//                $aDataRows['id_nomenclature_earning']	= 0;
//                $aDataRows['id_nomenclature_expense']	= 0;
//                $aDataRows['id_saldo']					= $nIDSaldo;
//                $aDataRows['id_bank']					= $nIDAccount;
//                $aDataRows['saldo_state']				= !empty($nIDTran) 					? $nCurrentSaldo 			: $nCurrentSaldo - $nDDSUnpayed;
//                $aDataRows['account_state']				= $nAccountState - $nDDSUnpayed;
//                $aDataRows['month']						= $sDDSDate;
//                $aDataRows['type']						= "free";
//                $aDataRows['paid_sum']					= $nDDSUnpayed;
//                $aDataRows['is_dds']					= $nDDSType;
//                $aDataRows['updated_user']              = $this->currentUser;
//                $aDataRows['updated_time']              = time();
//
//                $oOrdersRows->update($aDataRows);
//
//                $db_finance->Execute("UPDATE {$db_name_finance}.$sRowsName SET paid_sum = paid_sum + '{$nDDSUnpayed}', paid_date = NOW() WHERE id = '{$nIDDDS}' ");
//
//                // Салда на фирмите
//                if ( empty($nIDTran) ) {
//                    if ( $nCurrentSaldo < $nDDSUnpayed ) {
//                        $this->makeError("Надхвърлено е салдото на фирма {$sFirmName}!!!");
//                    }
//
//                    $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum - '{$nDDSUnpayed}' WHERE id = {$nIDSaldo} LIMIT 1");
//                }
//
//                $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum - '{$nDDSUnpayed}' WHERE id_bank_account = {$nIDAccount} ");
//
//                $nAccState  -= $nDDSUnpayed;
//                $nPaidSum   += $nDDSUnpayed;
//            }
//
//            // Опис
//            foreach ($aDocumentRows as $row) {
//                if ( (!empty($forPay) && !in_array($row['id'], $forPay)) ) {
//                    continue;
//                }
//
//                if ( ($row['sum'] - $row['paid_sum']) != 0) {
//                    $currentRowSum  = $row['sum'] - $row['paid_sum'];
//                    $sMonth         = substr($row['month'], 0, 7) . "-01";
//                    $nIDObject      = $row['id_object'];
//                    $nIDDirect      = $row['id_direction'];
//                    $nIDExpens      = $row['id_nomenclature'];
//
//                    if ( !isset($row['id_office']) || empty($row['id_office']) ) {
//                        $this->makeError("Има услуга без направление!");
//                    } else {
//                        $nIDOffice  = $row['id_office'];
//                    }
//
//                    $nIDFirm        = $oFirms->getFirmByOffice($nIDOffice);
//                    $sFirm          = $oOffices->getFirmNameByIDOffice($row['id_office']);
//
//                    if ( $row['is_dds'] != 0 ) {
//                        $aSaldo = $oSaldo->getSaldoByFirm($nIDFirm, 1);
//                    } else {
//                        $aSaldo = $oSaldo->getSaldoByFirm($nIDFirm, 0);
//                    }
//
//                    $nIDSaldo       = 0;
//                    $nCurrentSaldo  = 0;
//
//                    if ( !empty($aSaldo) ) {
//                        $nIDSaldo   = $aSaldo['id'];
//                    }
//
//                    // Салдо на фирмата с изчакване!!!
//                    if ( !empty($nIDSaldo) ) {
//                        $oRes 			= $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
//                        $nCurrentSaldo 	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
//                    } else {
//                        $this->makeError("Неизвестно салдо по фирма!");
//                    }
//
//                    // Наличност по сметка
//                    if ( !empty($nIDAccount) ) {
//                        $oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1 FOR UPDATE");
//                        $nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;
//                    } else {
//                        $this->makeError("Неизвестнa сметка!");
//                    }
//
//                    if ( $nAccountState < $currentRowSum ) {
//                        $this->makeError("Нямате достатъчно наличност по сметката!!!");
//                    }
//
//                    // Ордери - разбивка!
//                    $aDataRows								= array();
//                    $aDataRows['id']						= 0;
//                    $aDataRows['id_order']					= $nIDOrder;
//                    $aDataRows['id_doc_row']				= $row['id'];
//                    $aDataRows['id_office']					= $nIDOffice;
//                    $aDataRows['id_object']					= $nIDObject;
//                    $aDataRows['id_service']				= 0;
//                    $aDataRows['id_direction']				= $nIDDirect;
//                    $aDataRows['id_nomenclature_earning']	= 0;
//                    $aDataRows['id_nomenclature_expense']	= $nIDExpens;
//                    $aDataRows['id_saldo']					= $nIDSaldo;
//                    $aDataRows['id_bank']					= $nIDAccount;
//                    $aDataRows['saldo_state']				= !empty($nIDTran) ? $nCurrentSaldo : $nCurrentSaldo - $currentRowSum;
//                    $aDataRows['account_state']				= $nAccountState - $currentRowSum;
//                    $aDataRows['month']						= $sMonth;
//                    $aDataRows['type']						= "free";
//                    $aDataRows['paid_sum']					= $currentRowSum;
//                    $aDataRows['is_dds']					= $row['is_dds'];
//                    $aDataRows['updated_user']              = $this->currentUser;
//                    $aDataRows['updated_time']              = time();
//
//                    $oOrdersRows->update($aDataRows);
//
//                    $db_finance->Execute("UPDATE {$db_name_finance}.$sRowsName SET paid_sum = paid_sum + '{$currentRowSum}', paid_date = NOW() WHERE id = '{$row['id']}' ");
//
//                    // Салда на фирмите
//                    if ( empty($nIDTran) ) {
//                        if ( $nCurrentSaldo < $currentRowSum ) {
//                            $this->makeError("Надхвърлено е салдото на фирма {$sFirm}!!!");
//                        }
//
//                        $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum - '{$currentRowSum}' WHERE id = {$nIDSaldo} LIMIT 1");
//                    }
//
//                    $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum - '{$currentRowSum}' WHERE id_bank_account = {$nIDAccount} ");
//
//                    $nAccState  -= $currentRowSum;
//                    $nPaidSum   += $currentRowSum;
//
//                    // Фондове
//                    if ( empty($nIDTran) && $currentRowSum != 0 && $row['is_dds'] == 0 ) {
//                        $aObjCheck	        = $oObject->getByID($nIDObject);
//                        $nNumObject         = isset($aObjCheck['num'])       ? $aObjCheck['num']	    : 0;
//
//                        // Totali
//                        $db_sod->Execute("UPDATE {$db_name_sod}.directions_type SET saldo = saldo - '{$currentRowSum}' WHERE id = {$nIDDirect} LIMIT 1");
//                    }
//                }
//            }
//
//            // Оправяме тоталите
//            $paid_account				= $nAccState - $nPaidSum;
//
//            if ( $nPaidSum >= 0 ) {
//                $sTypeNow = "expense";
//            } else {
//                $sTypeNow = "earning";
//            }
//
//            $aData					= array();
//            $aData['id']			= $nIDOrder;
//            $aData['order_type'] 	= $sTypeNow;
//            $aData['order_sum']		= $nPaidSum;
//            $aData['account_sum']	= $nAccState; //$paid_account;
//
//            $oOrders->update($aData);
//
//            $db_finance->Execute("UPDATE {$db_name_finance}.{$sBuyName} SET last_order_id = '{$nIDOrder}', last_order_time = NOW(), orders_sum = orders_sum + '{$nPaidSum}', updated_user = {$this->currentUser}, updated_time = NOW() WHERE id = '{$nIDDocument}'");
//
//            if ( sprintf("%01.2f", $nPaidSum) == "0.00" ) {
//                $db_finance->FailTrans();
//                $db_system->FailTrans();
//                $db_sod->FailTrans();
//            }
//
//            $db_finance->CompleteTrans();
//            $db_system->CompleteTrans();
//            $db_sod->CompleteTrans();
//        } catch (Exception $e) {
//            $sMessage 	= $e->getMessage();
//
//            if ( $sMessage == "" ) {
//                $sMessage = "Системна грешка. Свържете се с администратор!";
//            }
//
//            $db_finance->FailTrans();
//            $db_system->FailTrans();
//            $db_sod->FailTrans();
//
//            if ( $this->withThrown ) {
//                throw new Exception("Грешка: " . $sMessage, DBAPI_ERR_FAILED_TRANS);
//            }
//        }
//    }

    /**
     * @param $nIDDocument
     * @return mixed|null
     * @throws Exception
     *
     * Проверява за начислено еднократно задължение към фактура
     * платена на каса, за която има дефинирана такса "касово обслужване"
     */
//    public function checkForPayTax($nIDDocument) {
//        global $db_name_sod;
//
//        $oSingles = new DBObjectsSingles();
//
//        // Валидации
//        if ( empty($nIDDocument) || !$this->isValidID($nIDDocument) ) {
//            $this->makeError("Невалиден документ!");
//        }
//
//        $sQuery = "
//            SELECT
//              id
//            FROM {$db_name_sod}.objects_singles
//            WHERE to_arc = 0
//                AND depending_on = {$nIDDocument}
//			LIMIT 1
//        ";
//
//        return $oSingles->selectOne($sQuery);
//    }

//    public function checkIsMonth( $nIDDocument ) {
//        global $db_name_finance;
//
//        // Валидации
//        if ( empty($nIDDocument) || !$this->isValidID($nIDDocument) ) {
//            return false;
//        }
//
//        $oSaleDocRows   = new DBSalesDocsRows();
//        $sTableName     = PREFIX_SALES_DOCS_ROWS.substr($nIDDocument, 0, 6);
//
//        $sQuery = "
//            SELECT
//                1
//            FROM {$db_name_finance}.{$sTableName} sdr
//            WHERE sdr.id_sale_doc = {$nIDDocument}
//                AND sdr.`type` = 'month'
//                AND sdr.is_dds = 0
//            LIMIT 1
//        ";
//
//        return $oSaleDocRows->selectOne2($sQuery);
//    }

//    public function addPayTax($nIDDocument, $nIDAccount) {
//        global $db_name_sod, $db_name_finance;
//
//        $oSingles       = new DBObjectsSingles();
//        $oBankAcc       = new DBBankAccounts();
//        $oSaleDocRows   = new DBSalesDocsRows();
//
//        // Валидации
//        if ( empty($nIDDocument) || !$this->isValidID($nIDDocument) ) {
//            $this->makeError("Невалиден документ!");
//        }
//
//        $check = $this->checkForPayTax($nIDDocument);
//
//        if ( !empty($check) ) {
//            return;
//        }
//
//        if ( $chek = $this->checkIsMonth($nIDDocument) == false ) {
//            return;
//        }
//
//        if ( empty($nIDAccount) || !is_numeric($nIDAccount) ) {
//            $this->makeError("Невалидена сметка!");
//        }
//
//        $aBankAccount   = $oBankAcc->getBankAccoutById($nIDAccount);
//        $sTableName     = PREFIX_SALES_DOCS_ROWS.substr($nIDDocument, 0, 6);
//
//        if ( isset($aBankAccount['cash']) && $aBankAccount['cash'] == 1 && $aBankAccount['is_paid'] == 1 && $aBankAccount['tax'] > 0 ) {
//            $sQuery = "
//                SELECT
//                    o.id,
//                    o.id_office
//                FROM {$db_name_finance}.{$sTableName} sdr
//                JOIN {$db_name_sod}.objects o ON o.id = sdr.id_object
//                JOIN {$db_name_sod}.statuses s ON s.id = o.id_status
//                WHERE sdr.id_sale_doc = {$nIDDocument}
//                    AND sdr.`type` = 'month'
//                    AND sdr.total_sum > 0
//                    AND sdr.id_duty_row > 0
//                    AND s.payable = 1
//                    #AND s.temp_inactive = 0
//                LIMIT 1
//            ";
//
//            $aObject = $oSaleDocRows->selectOnce2($sQuery);
//
//            if ( !isset($aObject['id']) || empty($aObject['id']) ) {
//                return;
//            }
//
//            $aData = [];
//            $aData['id']            = 0;
//            $aData['id_object']     = $aObject['id'];
//            $aData['id_office']     = $aObject['id_office'];
//            $aData['id_service']    = 158;
//            $aData['id_schet']      = 0;
//            $aData['service_name']  = 'Такса касово плащане '.$nIDDocument.'/'.date("d.m.Y");
//            $aData['single_price']  = $aBankAccount['tax'];
//            $aData['quantity']      = 1;
//            $aData['total_sum']     = $aBankAccount['tax'];
//            $aData['start_date']    = time();
//            $aData['depending_on']  = $nIDDocument;
//
//            $oSingles->update($aData);
//
//            return $aObject['id'];
//        } else {
//            return 0;
//        }
//    }

    public function makeOrder( $nIDDocument, $nIDAccount, $nValidateSum = 0, $forPay = array(), $oResponse = null) {
        global $db_sod, $db_system, $db_finance, $db_name_system, $db_name_sod, $db_name_finance;

        $oFirms 		= new DBFirms();
        $oOrders		= new DBOrders();
        $oOrderRows		= new DBOrdersRows();
        $oSaleDocRows	= new DBSalesDocsRows();
        $oSaleDoc		= new DBSalesDocs();
        $oServices		= new DBObjectServices();
        $oObject		= new DBObjects();
        $oOffices		= new DBOffices();
        $oSaldo			= new DBSaldo();
        $oBank			= new DBBankAccounts();
        $oClients       = new DBClients();

        $isZeroDuty     = false;
        $nTotalSum      = 0;
        $nPaidSum       = 0;
        $nIDOrder       = 0;
        $nRealPayedSum  = 0;
        $nTypePayment   = 1;

        $nIDSMSFirm 	= 0;
        $nIDSMSOffice	= 0;

        // Инитализация
        $this->initalize();

        // Валидации
        if ( empty($nIDDocument) || !$this->isValidID($nIDDocument) ) {
            $this->makeError("Невалиден документ!");
        }

        $is_credit = $oSaleDocRows->checkForCredit($nIDDocument);

        if ( $is_credit && $this->massPay ) {
            return;
        }

        if ( empty($nIDAccount) || !is_numeric($nIDAccount) ) {
            $this->makeError("Изберете валидна сметка!");
        }

        $nIDTran	    = $oSaleDocRows->checkForTransfer($nIDDocument);
        $nIDTran2	    = $oSaleDocRows->checkForTransfer($nIDDocument, 1);

        if ( !empty($nIDTran) && !empty($nIDTran2) ) {
            $this->makeError("В документа участват комбинации от услуги и ТРАНСФЕР!!!");
        }

        $sTypeBank		= $oBank->getTypeAccoutById($nIDAccount);
        $aDocument 		= $oSaleDoc->getDoc($nIDDocument);
        $aDocumentRows	= $oSaleDocRows->getRowsByDoc($nIDDocument);

        if ( isset($aDocument['doc_status']) && $aDocument['doc_status'] == "canceled" ) {
            $this->makeError("Документа е анулиран!");
        }

        // ДДС
        $aDDS 		        = $oSaleDocRows->getDDSByDoc($nIDDocument);
        $nDDSUnpayed	    = isset($aDDS[0]['paid_sum']) 	? $aDDS[0]['total_sum'] - $aDDS[0]['paid_sum'] 	: 0;
        $nIDDDS 		    = isset($aDDS[0]['id']) 		? $aDDS[0]['id'] 								: 0;
        $nDDSType           = isset($aDDS[0]['is_dds']) 	? $aDDS[0]['is_dds'] 							: 1;
        $sDDSDate           = isset($aDDS[0]['month']) 	    ? $aDDS[0]['month'] 							: date("Y-m-d");
        $checkDDS	        = $oSaleDocRows->checkForDDS($nIDDocument);

        $sSaleName 	        = PREFIX_SALES_DOCS.substr($nIDDocument, 0, 6);
        $sRowsName	        = PREFIX_SALES_DOCS_ROWS.substr($nIDDocument, 0, 6);
        //$sFundsName         = PREFIX_FUNDS_DOCS.substr($nIDDocument, 0, 6);
        $hasFullyPayment    = true;
        $nDocAmount         = isset($aDocument['total_sum']) ?  $aDocument['total_sum']                     : 0;

        foreach( $aDocumentRows as $val ) {
            if ( !$this->massPay && !empty($forPay) && !in_array($val['id'], $forPay) ) {
                continue;
            }

            $nTotalSum 	+= $val['total_sum'];
            $nPaidSum	+= $val['paid_sum'];

            if ( $val['total_sum'] != $val['paid_sum'] ) {
                $isZeroDuty = true;
            }
        }

        $nUnpayedSum            = ($nTotalSum - $nPaidSum);

        if ( $nDDSType != 2 ) {
            $nUnpayedSum += $nDDSUnpayed;
        }

        $nUnpayedSumWithoutDDS  = ($nTotalSum - $nPaidSum);

        // Плащаме всичко
        if ( $this->massPay ) {
            $nValidateSum = $nUnpayedSum;
        }
        //$this->makeError("test: ".$nDDSType);
        // Имаме частично плащане
        if ( abs($nValidateSum - $nUnpayedSum) > 0.03 ) {
            $isZeroDuty         = false;
            $hasFullyPayment    = false;
            $nTypePayment       = 2;
        }

        if ( !$hasFullyPayment && ($checkDDS || $nIDTran) ) {
            $this->makeError("Не е възможно пропорционално\nплащане!!! Моля, изберете плащане!");
        }


        $db_finance->StartTrans();
        $db_system->StartTrans();
        $db_sod->StartTrans();

        try {
            // Създаваме ордер!
            if ($isZeroDuty || $nValidateSum != 0) {
                // Следващ номер за ордер
                $oRes       = $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
                $nLastOrder = !empty($oRes->fields['last_num_order'])   ? $oRes->fields['last_num_order'] + 1   : 0;

                // НАЧАЛНА наличност по сметка
                $oRes       = $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1");
                $nAccState  = !empty($oRes->fields['current_sum'])      ? $oRes->fields['current_sum']          : 0;

                $nEin       = isset($aDocument['deliverer_ein'])        ? $aDocument['deliverer_ein']           : 0;
                $nIDClient  = isset($aDocument['id_client'])            ? $aDocument['id_client']               : 0;
                $nDocNum    = isset($aDocument['doc_num'])              ? $aDocument['doc_num']                 : 0;

                $aFirm      = $oFirms->getDDSFirmByEIN($nEin);
                $nIDFirm    = isset($aFirm['id'])                       ? $aFirm['id']                          : 0;

                $aDataOrder                     = array();
                $aDataOrder['id']               = 0;
                $aDataOrder['num']              = $nLastOrder;
                $aDataOrder['doc_num']          = $nDocNum;
                $aDataOrder['order_type']       = "earning";
                $aDataOrder['id_transfer']      = 0;
                $aDataOrder['id_contragent']    = $nIDClient;
                $aDataOrder['id_doc_firm']      = $nIDFirm;
                $aDataOrder['order_date']       = time();
                $aDataOrder['order_sum']        = 0;
                $aDataOrder['account_type']     = $sTypeBank;
                $aDataOrder['id_person']        = $this->currentUser;
                $aDataOrder['account_sum']      = 0;
                $aDataOrder['bank_account_id']  = $nIDAccount;
                $aDataOrder['doc_id']           = $nIDDocument;
                $aDataOrder['doc_type']         = "sale";
                $aDataOrder['note']             = $this->massPay ? "Групово валидиране!" : "";        // TODO:
                $aDataOrder['created_user']     = $this->currentUser;
                $aDataOrder['created_time']     = time();
                $aDataOrder['updated_user']     = $this->currentUser;
                $aDataOrder['updated_time']     = time();

                $oOrders->update($aDataOrder);

                // Вдигаме номер за следващ ордер
                $db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");

                $nIDOrder           = $aDataOrder['id'];

                // Опис
                $currentRowSum      = 0;
                $isDDS              = 0;

                // Плащаме първо ДДС
                if ( $nDDSUnpayed != 0 ) {
                    $isDDS          = $nDDSType;
                    $nIDOffice      = isset($aFirm['id_office_dds']) ? $aFirm['id_office_dds'] : 0;
                    $nIDFirm        = $oFirms->getFirmByOffice($nIDOffice);
                    $sFirm          = $oOffices->getFirmNameByIDOffice($nIDOffice);

                    // Проверка за пълна наличност
                    if (abs($nValidateSum) >= abs($nDDSUnpayed)) {
                        $currentRowSum  = $nDDSUnpayed;
                        $nValidateSum   -= $nDDSUnpayed;
                    } else {
                        $currentRowSum  = $nValidateSum;
                        $nValidateSum   = 0;
                    }

                    $aSaldo             = $oSaldo->getSaldoByFirm($nIDFirm, 1);
                    $nIDSaldo           = 0;
                    $nCurrentSaldo      = 0;

                    if ( !empty($aSaldo) ) {
                        $nIDSaldo       = $aSaldo['id'];
                    }
                    //throw new Exception("aaa: ".$nIDSaldo);
                    // Салдо на фирмата с изчакване!!!
                    if ( !empty($nIDSaldo) ) {
                        $oRes           = $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
                        $nCurrentSaldo  = !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
                    } else {
                        $this->makeError("Неизвестно салдо по фирма!");
                    }

                    // Наличност по сметка
                    if (!empty($nIDAccount)) {
                        $oRes           = $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1 FOR UPDATE");
                        $nAccountState  = !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;
                    } else {
                        $this->makeError("Неизвестнa сметка!");
                    }

                    if (($nAccountState + $currentRowSum) < 0) {
                        $this->makeError("Нямате достатъчно наличност по сметката!!!");
                    }

                    // Ордери - разбивка!
                    $aDataRows                              = array();
                    $aDataRows['id']                        = 0;
                    $aDataRows['id_order']                  = $nIDOrder;
                    $aDataRows['id_doc_row']                = $nIDDDS;
                    $aDataRows['id_office']                 = $nIDOffice;
                    $aDataRows['id_object']                 = 0;
                    $aDataRows['id_service']                = 0;
                    $aDataRows['id_direction']              = 0;
                    $aDataRows['id_nomenclature_earning']   = 0;
                    $aDataRows['id_nomenclature_expense']   = 0;

                    $aDataRows['id_saldo']                  = $nIDSaldo;
                    $aDataRows['id_bank']                   = $nIDAccount;
                    $aDataRows['saldo_state']               = !empty($nIDTran) ? $nCurrentSaldo : $nCurrentSaldo + $currentRowSum;
                    $aDataRows['account_state']             = $nAccountState + $currentRowSum;
                    $aDataRows['month']                     = $sDDSDate;

                    $aDataRows['type']                      = "month";
                    $aDataRows['paid_sum']                  = $currentRowSum;
                    $aDataRows['is_dds']                    = $isDDS;
                    $aDataRows['updated_user']              = $this->currentUser;
                    $aDataRows['updated_time']              = time();

                    if (!empty($currentRowSum) || $isZeroDuty) {
                        $oOrderRows->update($aDataRows);

                        $nIDRow = $nIDDDS;
                        $db_finance->Execute("UPDATE {$db_name_finance}.$sRowsName SET paid_sum = paid_sum + '{$currentRowSum}', paid_date = NOW() WHERE id = '{$nIDRow}' ");
                    }

                    $nRealPayedSum += $currentRowSum;

                    // Салда на фирмите
                    if (empty($nIDTran)) {
                        if (($nCurrentSaldo + $currentRowSum) < 0) {
                            $this->makeError("Недостатъчно салдо по фирма {$sFirm}!!!");
                        }

                        $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum + '{$currentRowSum}' WHERE id = {$nIDSaldo} LIMIT 1");
                    }

                    $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum + '{$currentRowSum}' WHERE id_bank_account = {$nIDAccount} ");
                }

                // Схема за разпределение
                if ($nTypePayment == 2 && $nValidateSum <= $nUnpayedSumWithoutDDS) {
                    $nKoefficent = $nValidateSum / $nUnpayedSumWithoutDDS;
                } else {
                    $nKoefficent = 1;
                }

                $aFirmOffices = [];
                $aDocumentServices = [];

                // Опис
                foreach ($aDocumentRows as $row) {
                    $currentRowSum = 0;

                    if ((!$this->massPay && !empty($forPay) && !in_array($row['id'], $forPay)) || $row['is_dds'] != 0) {
                        continue;
                    }

                    if ($row['total_sum'] - $row['paid_sum'] != 0) {
                        $nIDObject      = $row['id_object'];

                        if ( !isset($row['id_office']) || empty($row['id_office']) ) {
                            $this->makeError("Има услуга без направление!");
                        } else {
                            $nIDOffice  = $row['id_office'];
                        }

                        // Фирма
                        if ( !isset($aFirmOffices[$row['id_office']]) ) {
                            $idfrm          = $oFirms->getFirmByOffice($row['id_office']);

                            $aFirmOffices[$row['id_office']] = [
                                'id_firm' => $idfrm,
                                'firm_name' => $oOffices->getFirmNameByIDOffice($row['id_office']),
                                'id_saldo' => $oSaldo->getSaldoByFirm($idfrm, 0)
                               // 'nCheckObject' => $nCheckObject,
                               // 'nCheckFirm' => $nCheckFirm,
                               // 'aFundsData' => $aFundsData
                            ];
                        }

                        // услуги
                        if ( !isset($aDocumentServices[$row['id_service']]) ) {
                            $aDocumentServices[$row['id_service']] = $oServices->getService($row['id_service']);
                        }

                        $nIDFirm        = $aFirmOffices[$row['id_office']]['id_firm'];
                        $sFirm          = $aFirmOffices[$row['id_office']]['firm_name'];

                        $nIDService     = $row['id_service'];
                        $nIDDuty        = $row['id_duty'];

                        //$aService       = $oServices->getService($row['id_service']);
                        $nIDEarning     = isset($aDocumentServices[$row['id_service']]['id_earning']) ? $aDocumentServices[$row['id_service']]['id_earning'] : 0;
                        //$nIDEarning     = isset($aService['id_earning']) ? $aService['id_earning'] : 0;

                        $tSum           = $row['total_sum'] - $row['paid_sum'];
                        $sMonth         = substr($row['month'], 0, 7) . "-01";

                        $currentRowSum  = $tSum * $nKoefficent;
                        $nCurrentSaldo  = 0;
                        $nAccountState  = 0;


                        //$aSaldo         = $oSaldo->getSaldoByFirm($nIDFirm, 0);
                        //$nIDSaldo       = 0;

                        //if (!empty($aSaldo)) {
                        //    $nIDSaldo   = $aSaldo['id'];
                        //}

                        $nIDSaldo        = $aFirmOffices[$row['id_office']]['id_saldo']['id'] ?? 0;

                        // Салдо на фирмата с изчакване!!!
                        if (!empty($nIDSaldo)) {
                            $oRes           = $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
                            $nCurrentSaldo  = !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
                        } else {
                            $this->makeError("Неизвестно салдо по фирма!");
                        }

                        // Наличност по сметка
                        if (!empty($nIDAccount)) {
                            $oRes = $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1 FOR UPDATE");
                            $nAccountState = !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;
                        } else {
                            $this->makeError("Неизвестнa сметка!");
                        }

                        if (($nAccountState + $currentRowSum) < 0) {
                            $this->makeError("Нямате достатъчно наличност по сметката!!!");
                        }

                        if ( $nIDFirm == 2 && $nIDOffice != 72 ) {
                            $nIDSMSFirm 	= $nIDFirm;
                            $nIDSMSOffice 	= $nIDOffice;
                        }

                        // Ордери - разбивка!
                        $aDataRows                              = array();
                        $aDataRows['id']                        = 0;
                        $aDataRows['id_order']                  = $nIDOrder;
                        $aDataRows['id_doc_row']                = $row['id'];
                        $aDataRows['id_office']                 = $nIDOffice;
                        $aDataRows['id_object']                 = $row['id_object'];
                        $aDataRows['id_service']                = $nIDService;
                        $aDataRows['id_direction']              = 0;
                        $aDataRows['id_nomenclature_earning']   = $nIDEarning;
                        $aDataRows['id_nomenclature_expense']   = 0;

                        $aDataRows['id_saldo']                  = $nIDSaldo;
                        $aDataRows['id_bank']                   = $nIDAccount;
                        $aDataRows['saldo_state']               = !empty($nIDTran) ? $nCurrentSaldo : $nCurrentSaldo + $currentRowSum;
                        $aDataRows['account_state']             = $nAccountState + $currentRowSum;
                        $aDataRows['month']                     = $row['month'];

                        $aDataRows['type']                      = $row['type'];
                        $aDataRows['paid_sum']                  = $currentRowSum;
                        $aDataRows['is_dds']                    = 0;
                        $aDataRows['updated_user']              = $this->currentUser;
                        $aDataRows['updated_time']              = time();

                        if ((!empty($currentRowSum) || $isZeroDuty)) {
                            $oOrderRows->update($aDataRows);

                            $db_finance->Execute("UPDATE {$db_name_finance}.$sRowsName SET paid_sum = paid_sum + '{$currentRowSum}', paid_date = NOW() WHERE id = '{$row['id']}' ");
                        }

                        $nRealPayedSum += $currentRowSum;

                        // Салда на фирмите
                        if (empty($nIDTran)) {
                            if (($nCurrentSaldo + $currentRowSum) < 0) {
                                $this->makeError("Недостатъчно салдо по фирма {$sFirm}!!!");
                            }

                            $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum + '{$currentRowSum}' WHERE id = {$nIDSaldo} LIMIT 1");
                        }

                        $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum + '{$currentRowSum}' WHERE id_bank_account = {$nIDAccount} ");

                        // Реално плащане
                        if ($row['type'] == "month" && !empty($nIDDuty)) {
                            $aServiceData   = $oServices->getServiceByID($nIDDuty);
                            $aObjCheck      = $oObject->getByID($nIDObject);
                            $nObjOffc       = isset($aObjCheck['id_office']) ? $aObjCheck['id_office'] : $nIDOffice;

                            if ((isset($aServiceData['real_paid']) && $aServiceData['real_paid'] < $sMonth) && abs($tSum - $currentRowSum) < 0.09) {
                                $aTempData              = array();
                                $aTempData['id']        = $nIDDuty;
                                $aTempData['real_paid'] = $sMonth;

                                $oServices->update($aTempData);
                            }

//                            // Някакви статистики...
//                            if (isset($aServiceData['real_paid']) && abs($tSum - $currentRowSum) < 0.09) {
//                                // Ъпдейтвам нещо си...
//                                $sQuery = "
//                                UPDATE {$db_name_sod}.statistics_rows_unpaid
//                                SET sum_paid = sum_paid + '{$currentRowSum}', id_office = {$nObjOffc}, is_paid = 1
//                                WHERE id_object = {$nIDObject}
//                                    AND id_service = {$nIDDuty}
//                                    AND stat_month = '{$sMonth}'
//										";
//
//                                $db_sod->Execute($sQuery);
//                            } else {
//                                // Ъпдейтвам нещо си...
//                                $sQuery = "
//                                UPDATE {$db_name_sod}.statistics_rows_unpaid
//                                SET sum_paid = sum_paid + '{$currentRowSum}', id_office = {$nObjOffc}, is_paid = 0
//                                WHERE id_object = {$nIDObject}
//                                    AND id_service = {$nIDDuty}
//                                    AND stat_month = '{$sMonth}'
//										";
//
//                                $db_sod->Execute($sQuery);
//                            }
                        }

                        // Фондове
                        if (empty($row['is_dds']) && empty($nIDTran) && $currentRowSum != 0) {   // Трансфери?

                            $nCheckFirm         = $aFirmOffices[$row['id_office']]['nCheckFirm'];
                            $aFundsData         = $aFirmOffices[$row['id_office']]['aFundsData'];

                            $real_sum           = $currentRowSum;

                        }

                    }

                    // Фикс нулеви услуги
                    if ($row['total_sum'] == 0 && $row['id_duty'] != 0 && $row['type'] == 'month') {
                        $aServiceData               = $oServices->getServiceByID($row['id_duty']);
                        $sMonth                     = substr($row['month'], 0, 7) . "-01";

                        if (isset($aServiceData['real_paid']) && $aServiceData['real_paid'] < $sMonth) {
                            $aTempData              = array();
                            $aTempData['id']        = $row['id_duty'];
                            $aTempData['real_paid'] = $sMonth;

                            $oServices->update($aTempData);
                        }
                    }
                }

                // Слагаме стойностите в описа че са платени
                $nState = $nAccState + $nRealPayedSum;
                $db_finance->Execute("UPDATE {$db_name_finance}.{$sSaleName} SET orders_sum = orders_sum + '{$nRealPayedSum}', last_order_id = '{$nIDOrder}', last_order_time = NOW() WHERE id = '{$nIDDocument}'");

                // Оправяме тоталите
                if ($nRealPayedSum >= 0) {
                    $sTypeNow = "earning";
                } else {
                    $sTypeNow = "expense";
                }

                $aData                      = array();
                $aData['id']                = $nIDOrder;
                $aData['order_type']        = $sTypeNow;
                $aData['order_sum']         = abs($nRealPayedSum);
                $aData['bank_account_id']   = $nIDAccount;
                $aData['account_sum']       = $nState;

                $oOrders->update($aData);


                // Фондове - тотали
                foreach ( $aTotalFundSaldo as $nIDDirection => $nSumAmount ) {
                    $db_sod->Execute("UPDATE {$db_name_sod}.directions_type SET saldo = saldo + '{$nSumAmount}' WHERE id = {$nIDDirection} LIMIT 1");
                }

                
            }
            //throw new Exception("Грешка: " . $nRealPayedSum." => ".$nUnpayedSum, DBAPI_ERR_FAILED_TRANS);

            $db_finance->CompleteTrans();
            $db_system->CompleteTrans();
            $db_sod->CompleteTrans();
        } catch (Exception $e) {
            $sMessage = $e->getMessage();

            $db_finance->FailTrans();
            $db_system->FailTrans();
            $db_sod->FailTrans();

            if ( $this->withThrown ) {
                throw new Exception("Грешка: " . $sMessage, DBAPI_ERR_FAILED_TRANS);
            }
        }

        //$oResponse->setAlert("Успешно");

        return $nIDOrder;
    }

    public function annulment($nID, DBResponse $oResponse) {
        global $db_name_system, $db_name_finance, $db_system, $db_finance, $db_sod, $db_name_sod, $mname;

        if ( !$this->isValidID($nID) ) {
            $this->makeError("Невалиден документ!");
        }

        $oOrders 	    = new DBOrders();
        $oOrderRow	    = new DBOrdersRows();
        $oSaldo		    = new DBSaldo();
        $oFirms		    = new DBFirms();
        $oSales		    = new DBSalesDocsRows();
        $oBuys		    = new DBBuyDocsRows();
//        $oFunds         = new DBFunds();

        $nIDUser	    = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
        $aOrder 	    = array();
        $aOrderRow      = array();
        $aDataServices  = array();
//        $aFundsSaldo    = array();
//        $aStartSaldo    = [];
//        $aMonthFundSaldo = [];
        $aTotalFundSaldo = [];
        $sSufixTableDoc = "";

        $nResult        = $oOrders->getRecord( $nID, $aOrder );

        if(  $nResult != DBAPI_ERR_SUCCESS ) {
            $this->makeError("Не може да бъдат извлечени данните за ордера!");
        }

        $nIDAccount 	= isset($aOrder['bank_account_id']) ? $aOrder['bank_account_id'] : 0;
        $nSum 			= isset($aOrder['order_sum']) 		? $aOrder['order_sum'] 		 : 0;
        $account_type 	= isset($aOrder['account_type']) 	? $aOrder['account_type'] 	 : "cash";
        $doc_id 		= isset($aOrder['doc_id']) 			? $aOrder['doc_id'] 		 : 0;
        $doc_type 		= isset($aOrder['doc_type']) 		? $aOrder['doc_type'] 	 	 : "sale";
        $order_status	= isset($aOrder['order_status']) 	? $aOrder['order_status'] 	 : "active";
        $doc_num		= isset($aOrder['num']) 			? $aOrder['num'] 	 	 	 : 0;
        $order_type 	= $aOrder['order_type'] == "earning" ? "expense"				 : "earning";

        if ( $order_status != "active" ) {
            $this->makeError("Ордера не подлежи на промяна!!!");
        }

        if ( empty($nIDAccount) ) {
            $this->makeError("Банковата сметка не може да бъде намерена!!!");
        }

        if ( !$this->isValidID($doc_id) ) {
            $this->makeError("Невалиден документ!");
        } else {
            $sSufixTableDoc	= substr($doc_id, 0, 6);
        }

        if ($doc_type == "sale") {
            $nIDTran = $oSales->checkForTransfer($doc_id);
        } else {
            $nIDTran = $oBuys->checkForTransfer($doc_id);
        }

        //$nIDFundOperation  = $oFunds->getInvoiceOperation();

        $db_finance->StartTrans();
        $db_system->StartTrans();
        $db_sod->StartTrans();

        $hasFuture = false;

        try {
            if ($doc_type == "sale") {
                $aOrderRow = $oOrderRow->getByIDOrderEx($nID, $doc_id);

                // Проверка и връщане на падежите
                foreach ($aOrderRow as $val) {
                    if ($val['type'] == "month" && !empty($val['id_duty_row'])) {
                        if (!isset($aDataServices[$val['id_duty_row']])) {
                            $aDataServices[$val['id_duty_row']] = array();
                            $aDataServices[$val['id_duty_row']]['max_month'] = $val['month'];
                            $aDataServices[$val['id_duty_row']]['min_month'] = $val['month'];
                            $aDataServices[$val['id_duty_row']]['real_paid'] = $oOrders->getMaxRealPaid($val['id_duty_row']);
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
                    if ($arr_data['max_month'] < $arr_data['real_paid']) {
                        $hasFuture = true;
                        //throw new Exception("Има фактура с платени задължения за по-късен период!", DBAPI_ERR_INVALID_PARAM);
                    }
                }

                if ( $hasFuture ) {
                    if (isset($_SESSION['userdata']['id_profile']) && $_SESSION['userdata']['id_profile'] == 1) {
                        $oResponse->setAlert("Документа има платени задължения за по-късен период!!! Да се прегледат падежите");
                    } else {
                        throw new Exception("Има фактура с платени задължения за по-късен период!", DBAPI_ERR_INVALID_PARAM);
                    }
                }

                foreach ($aDataServices as $id_service => $arr_data) {
                    if ($arr_data['real_paid'] > "0000-00-00") {
                        list($y, $m, $d) = explode("-", $arr_data['real_paid']);

                        if ($d == 1) {
                            $newDate = date("Y-m-d", mktime(0, 0, 0, $m - 1, $d, $y));
                        } else {
                            $newDate = $arr_data['real_paid'];
                        }

                        if ( !$hasFuture ) {
                            $db_sod->Execute("UPDATE {$db_name_sod}.objects_services SET real_paid = '{$newDate}' WHERE id = {$id_service} LIMIT 1");
                        }
                    }
                }


            } else {
                $aOrderRow = $oOrderRow->getByIDOrder($nID);
            }

            // Следващ номер за ордер
            $oRes           = $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
            $nLastOrder     = !empty($oRes->fields['last_num_order']) ? $oRes->fields['last_num_order'] + 1 : 0;

            // НАЧАЛНА наличност по сметка
            $oRes           = $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1");
            $nAccState      = !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;

            if ($doc_type == "buy") {
                $sTableDoc  = PREFIX_BUY_DOCS . $sSufixTableDoc;

                if ($order_type == "earning") {
                    $paid_sum   = $nAccState + $nSum;
                    $db_finance->Execute("UPDATE {$db_name_finance}.$sTableDoc SET orders_sum = orders_sum - '{$nSum}' WHERE id = {$doc_id} LIMIT 1");
                } else {
                    $paid_sum   = $nAccState - $nSum;
                    $db_finance->Execute("UPDATE {$db_name_finance}.$sTableDoc SET orders_sum = orders_sum + '{$nSum}' WHERE id = {$doc_id} LIMIT 1");
                }
            } else {
                $sTableDoc      = PREFIX_SALES_DOCS . $sSufixTableDoc;

                if ($order_type == "earning") {
                    $paid_sum   = $nAccState + $nSum;
                    $db_finance->Execute("UPDATE {$db_name_finance}.$sTableDoc SET orders_sum = orders_sum + '{$nSum}' WHERE id = {$doc_id} LIMIT 1");
                } else {
                    $paid_sum   = $nAccState - $nSum;
                    $db_finance->Execute("UPDATE {$db_name_finance}.$sTableDoc SET orders_sum = orders_sum - '{$nSum}' WHERE id = {$doc_id} LIMIT 1");
                }
            }

            $aDataOrder                 = array();
            $aDataOrder['id']           = $nID;
            $aDataOrder['order_status'] = "canceled";

            $oOrders->update($aDataOrder);

            $aDataOrder                     = array();
            $aDataOrder['id']               = 0;
            $aDataOrder['num']              = $nLastOrder;
            $aDataOrder['order_type']       = $order_type;
            $aDataOrder['order_status']     = "opposite";
            $aDataOrder['id_transfer']      = 0;
            $aDataOrder['order_date']       = time();
            $aDataOrder['order_sum']        = $nSum;
            $aDataOrder['account_type']     = $account_type;
            $aDataOrder['id_person']        = $nIDUser;
            $aDataOrder['account_sum']      = $paid_sum;
            $aDataOrder['bank_account_id']  = $nIDAccount;
            $aDataOrder['doc_id']           = $doc_id;
            $aDataOrder['doc_type']         = $doc_type;
            $aDataOrder['note']             = "Анулиране на номер " . $doc_num;
            $aDataOrder['created_user']     = $nIDUser;
            $aDataOrder['created_time']     = time();
            $aDataOrder['updated_user']     = $nIDUser;
            $aDataOrder['updated_time']     = time();

            $oOrders->update($aDataOrder);

            $db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");


            $nIDOrder = $aDataOrder['id'];

            foreach ( $aOrderRow as $val ) {
                $nIDFirm        = $oFirms->getFirmByOffice($val['id_office']);
                $isDDS          = isset($val['is_dds'])         ? $val['is_dds']            : 0;
                $nSumRow        = isset($val['paid_sum'])       ? $val['paid_sum']          : 0;
                $nIDRow         = isset($val['id_doc_row'])     ? $val['id_doc_row']        : 0;
                $nIDDirection   = isset($val['id_direction'])   ? $val['id_direction']      : 0;
                $aDocRow        = array();
                $state          = 0;
                $tRow       = PREFIX_BUY_DOCS_ROWS . substr($nIDRow, 0, 6);


                $aSaldo         = $oSaldo->getSaldoByFirm($nIDFirm, $isDDS);
                $nIDSaldo       = !empty($aSaldo) ? $aSaldo['id'] : 0;
                $nCurrentSaldo  = 0;
                $nAccountState  = 0;

                // Салдо на фирмата с изчакване!!!
                if (!empty($nIDSaldo)) {
                    $oRes = $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
                    $nCurrentSaldo = !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
                } else {
                    throw new Exception("Неизвестно салдо по фирма!", DBAPI_ERR_INVALID_PARAM);
                }

                // Наличност по сметка
                if (!empty($nIDAccount)) {
                    $oRes = $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1 FOR UPDATE");
                    $nAccountState = !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;
                } else {
                    throw new Exception("Неизвестнa сметка!", DBAPI_ERR_INVALID_PARAM);
                }

                if ($doc_type == "buy") {
                    $state = sprintf("%01.2f", $nAccountState + $nSumRow);

                    if (empty($nIDTran)) {
                        $saldo = sprintf("%01.2f", $nCurrentSaldo + $nSumRow);
                        $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum + '{$nSumRow}' WHERE id = {$nIDSaldo} LIMIT 1");
                    } else {
                        $saldo = sprintf("%01.2f", $nCurrentSaldo);
                    }

                    $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum + '{$nSumRow}' WHERE id_bank_account = {$nIDAccount} ");
                    $db_finance->Execute("UPDATE {$db_name_finance}.{$tRow} SET paid_sum = paid_sum - '{$nSumRow}', updated_user = {$nIDUser}, updated_time = NOW() WHERE id = {$nIDRow} LIMIT 1");
                } else {
                    $state = sprintf("%01.2f", $nAccountState - $nSumRow);

                    if (empty($nIDTran)) {
                        $saldo = sprintf("%01.2f", $nCurrentSaldo - $nSumRow);
                        $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum - '{$nSumRow}' WHERE id = {$nIDSaldo} LIMIT 1");
                    } else {
                        $saldo = sprintf("%01.2f", $nCurrentSaldo);
                    }

                    $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum - '{$nSumRow}' WHERE id_bank_account = {$nIDAccount} ");
                    $db_finance->Execute("UPDATE {$db_name_finance}.{$tRow} SET paid_sum = paid_sum - '{$nSumRow}', updated_user = {$nIDUser}, updated_time = NOW() WHERE id = {$nIDRow} LIMIT 1");
                }

                if ($saldo < 0) {
                    throw new Exception("Недостатъчно салдо по фирма!", DBAPI_ERR_INVALID_PARAM);
                }

                if ($state < 0) {
                    throw new Exception("Недостатъчна наличност по сметка!", DBAPI_ERR_INVALID_PARAM);
                }

                $val['id']              = 0;
                $val['id_order']        = $nIDOrder;
                $val['saldo_state']     = $saldo;
                $val['account_state']   = $state;
                $val['paid_sum']        = $nSumRow * -1;

                $oOrderRow->update($val);


            }


            $db_finance->CompleteTrans();
            $db_system->CompleteTrans();
            $db_sod->CompleteTrans();
        } catch (Exception $e) {
            $sMessage = $e->getMessage();

            $db_finance->FailTrans();
            $db_system->FailTrans();
            $db_sod->FailTrans();

            if ( $this->withThrown ) {
                throw new Exception("Грешка: " . $sMessage, DBAPI_ERR_FAILED_TRANS);
            }
        }

        $oResponse->printResponse();
    }

    public function annulmentBuyDocument($nID, $docType = "sale") {
        global $db_finance, $db_system, $db_name_finance, $db_name_system, $db_sod;

        $nIDUser 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;

        $oOrders		= new DBOrders();
        $aOrder         = array();

        if ( !$this->isValidID($nID) ) {
            $this->makeError("Невалидно ID!");
        }

        if ( $docType != "sale" && $docType != "buy" ) {
            $this->makeError("Невалидно тип на документа!");
        }

		$sQuery = "(SELECT id, doc_id FROM <%tablename%> WHERE doc_type = '{$docType}' AND doc_id = {$nID}) ";
		$aData 	= SQL_union_search($db_finance, $sQuery, "orders_", "______", "id", "DESC");

        foreach ( $aData as $val ) {
            $nIDOrder = $val['id'];

            if ( !$this->isValidID($nIDOrder) ) {
                continue;
            }

            $sTableName = PREFIX_BUY_DOCS.substr($nIDOrder, 0, 6);

            $oOrders->getRecord( $nID, $aOrder );
        }

        return $aData;
    }

    private function initalize() {
        $this->getCurentUser();
    }
}
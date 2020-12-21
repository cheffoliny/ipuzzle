<?php

/**
 * Created by PhpStorm.
 * User: adm
 * Date: 17.6.2020 г.
 * Time: 14:57
 */
class NEWDBFinanceOperations extends DBMonthTable {
    private $currentUser    = 0;
    public $withUser        = true;
    public $massPay         = false;
    private $alerts         = [];
    private $error          = "";


    function __construct() {
        global $db_name_finance, $db_finance;

        parent::__construct( $db_name_finance, PREFIX_ORDERS_ROWS, $db_finance );
    }

    private function getCurrentUser() {
        $this->currentUser = $this->withUser ? (isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0) : 0;
    }

    private function setAlert($message) {
        if ( !empty($message) ) {
            $this->alerts[] = $message;
        }
    }

    public function getAlerts() {
        return $this->alerts;
    }

    public function setError($message) {
        if ( !empty($message) ) {
            $this->error = $message;

            return;
        }
    }

    public function getError() {
        return $this->error;
    }

    /**
     *	Функцията проверява за валидност ID от месечна таблица (YYYYMM + 7) = 13
     *
     *	@name isValidID
     *	@param int ID ID на записа който ще се валидира
     *	@return bool резултат от валидацията
     */

    function isValidID( $nID ) {
        return preg_match("/^\d{13}$/", $nID);
    }

    public function getMaxRealPaid($idService) {
        global $db_sod, $db_name_sod;

        $oService = new DBBase2($db_sod, 'objects_services');

        $sQuery = "SELECT real_paid FROM {$db_name_sod}.objects_services WHERE id = {$idService}";

        return $oService->selectOne($sQuery);
    }

    public function getOrderByID($nIDOrder, $nIDDocument) {
        global $db_name_finance;

        if ( !$this->isValidID($nIDOrder) || !$this->isValidID($nIDDocument) ) {
            return $this->setError("Невалиден документ!");
        } else {
            $sTableOrder = PREFIX_ORDERS_ROWS.substr($nIDOrder, 0, 6);
            $sTableDocument = PREFIX_SALES_DOCS_ROWS.substr($nIDDocument, 0, 6);
        }

        $sQuery = "
            SELECT
                o.*,
                IF(doc.id_duty_row IS NULL, 0, doc.id_duty_row) as id_duty_row
            FROM {$db_name_finance}.{$sTableOrder} o
            LEFT JOIN {$db_name_finance}.{$sTableDocument} doc ON doc.id = o.id_doc_row
            WHERE o.id_order = {$nIDOrder}
        ";

        return $this->select2($sQuery);
    }

    public function annulment($nID) {
        global $db_name_system, $db_name_finance, $db_system, $db_finance, $db_sod, $db_name_sod;

        if ( !$this->isValidID($nID) ) {
            return $this->setError("Невалиден документ!");
        }

        $this->getCurrentUser();

        $oOrders 	    = new DBOrders();
        $oOrderRow	    = new DBOrdersRows();
        $oBalance		= new DBSaldo();
        $oFirms		    = new DBFirms();
        $oSales		    = new DBSalesDocsRows();
        $oBuys		    = new DBBuyDocsRows();
        $oFunds         = new DBFunds();

        $nIDUser	    = $this->currentUser;
        $aOrder 	    = [];
        $aOrderRows      = [];
        $aDataServices  = [];
        $aZeroDutyRows = [];
        $aFundsBalance    = [];
        $aStartBalance    = [];
        $aMonthFundBalance = [];
        $aTotalFundBalance = [];
        $sSuffixTableDoc = "";

        $nResult = $oOrders->getRecord($nID, $aOrder);

        if ( $nResult != DBAPI_ERR_SUCCESS ) {
            return $this->setError("Не може да бъдат извлечени данните за ордера!");
        }

        $nIDAccount 	= $aOrder['bank_account_id'] ?? 0;
        $nSum 			= $aOrder['order_sum'] ?? 0;
        $account_type 	= $aOrder['account_type'] ?? "cash";
        $documentId 	= $aOrder['doc_id'] ?? 0;
        $doc_type 		= $aOrder['doc_type'] ?? "sale";
        $order_status	= $aOrder['order_status'] ?? "active";
        $doc_num		= $aOrder['num'] ?? 0;
        $order_type 	= $aOrder['order_type'] == "earning" ? "expense" : "earning";

        // Пълно право за редакция
        $docGrantRight = in_array('sale_doc_grant', $_SESSION['userdata']['access_right_levels']);

        if ( $order_status != "active" ) {
            return $this->setError("Ордера не подлежи на промяна!!!");
        }

        if ( empty($nIDAccount) ) {
            return $this->setError("Банковата сметка не може да бъде намерена!!!");
        }

        if ( !$this->isValidID($documentId) ) {
            return $this->setError("Невалиден документ!");
        } else {
            $sSuffixTableDoc	= substr($documentId, 0, 6);
        }

        if ($doc_type == "sale") {
            $nIDTran = $oSales->checkForTransfer($documentId);
        } else {
            $nIDTran = $oBuys->checkForTransfer($documentId);
        }

        //$nIDFundOperation  = $oFunds->getInvoiceOperation();

        $db_finance->StartTrans();
        $db_system->StartTrans();
        $db_sod->StartTrans();

        $hasFuture = false;

        try {
            if ($doc_type == "sale") {
                $aOrderRows = $this->getOrderByID($nID, $documentId);

                if ( isset($aOrderRows['error']) && !empty($aOrderRows['error']) ) {
                    throw new Exception($aOrderRows['error'], DBAPI_ERR_FAILED_TRANS);
                }

                // Проверка и връщане на падежите
                foreach ($aOrderRows as $val) {
                    if ($val['type'] == "month" && !empty($val['id_duty_row'])) {
                        if ( !isset($aDataServices[$val['id_duty_row']]) ) {
                            $aDataServices[$val['id_duty_row']] = [];
                            $aDataServices[$val['id_duty_row']]['max_month'] = $val['month'];
                            $aDataServices[$val['id_duty_row']]['min_month'] = $val['month'];
                            $aDataServices[$val['id_duty_row']]['real_paid'] = $this->getMaxRealPaid($val['id_duty_row']);
                        } else {
                            if ( $val['month'] > $aDataServices[$val['id_duty_row']]['max_month'] ) {
                                $aDataServices[$val['id_duty_row']]['max_month'] = $val['month'];
                            }

                            if ( $val['month'] < $aDataServices[$val['id_duty_row']]['min_month'] ) {
                                $aDataServices[$val['id_duty_row']]['min_month'] = $val['month'];
                            }
                        }

                        // Zero duty
                        if ( !isset($aZeroDutyRows[$val['id_object']]) ) {
                            $aZeroDutyRows[$val['id_object']] = $val['month'];
                        } else {
                            if ( $val['month'] < $aZeroDutyRows[$val['id_object']] ) {
                                $aZeroDutyRows[$val['id_object']] = $val['month'];
                            }
                        }
                    }
                }

                foreach ($aDataServices as $arr_data) {
                    if ($arr_data['max_month'] < $arr_data['real_paid']) {
                        $hasFuture = true;
                    }
                }

                if ( $hasFuture ) {
                    if ( $docGrantRight ) {
                        $this->setAlert("Документа има фактурирани задължения за по-късен период! Да се прегледат падежите");
                    } else {
                        throw new Exception("Документа има фактурирани задължения за по-късен период!", DBAPI_ERR_FAILED_TRANS);
                    }
                }

                foreach ($aDataServices as $id_service => $arr_data) {
                    if ( $arr_data['real_paid'] > "0000-00-00" ) {
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

                // zero duty
                foreach ( $aZeroDutyRows as $nIDObject => $minMonth ) {
                    $db_sod->Execute("UPDATE {$db_name_sod}.objects_services SET real_paid = '{$minMonth}' WHERE id_object = {$nIDObject} and total_sum = 0 and to_arc = 0");
                }
            } else {
                $aOrderRows = $oOrderRow->getByIDOrder($nID);
            }

            // Следващ номер за ордер
            $oRes           = $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
            $nLastOrder     = !empty($oRes->fields['last_num_order']) ? $oRes->fields['last_num_order'] + 1 : 0;

            // НАЧАЛНА наличност по сметка
            $oRes           = $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1");
            $nAccState      = !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;

            if ($doc_type == "buy") {
                $sTableDoc  = PREFIX_BUY_DOCS . $sSuffixTableDoc;

                //if ($order_type == "earning") {
                    $paid_sum   = $nAccState + $nSum;
                    $db_finance->Execute("UPDATE {$db_name_finance}.$sTableDoc SET orders_sum = orders_sum - '{$nSum}' WHERE id = {$documentId} LIMIT 1");
                //} else {
                //    $paid_sum   = $nAccState - $nSum;
                //    $db_finance->Execute("UPDATE {$db_name_finance}.$sTableDoc SET orders_sum = orders_sum + '{$nSum}' WHERE id = {$documentId} LIMIT 1");
                //}
            } else {
                $sTableDoc      = PREFIX_SALES_DOCS . $sSuffixTableDoc;

                if ($order_type == "earning") {
                    $paid_sum   = $nAccState + $nSum;
                    $db_finance->Execute("UPDATE {$db_name_finance}.$sTableDoc SET orders_sum = orders_sum + '{$nSum}' WHERE id = {$documentId} LIMIT 1");
                } else {
                    $paid_sum   = $nAccState - $nSum;
                    $db_finance->Execute("UPDATE {$db_name_finance}.$sTableDoc SET orders_sum = orders_sum - '{$nSum}' WHERE id = {$documentId} LIMIT 1");
                }
            }

            $aDataOrder                 = [];
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
            $aDataOrder['doc_id']           = $documentId;
            $aDataOrder['doc_type']         = $doc_type;
            $aDataOrder['note']             = "Анулиране на номер " . $doc_num;
            $aDataOrder['created_user']     = $nIDUser;
            $aDataOrder['created_time']     = time();
            $aDataOrder['updated_user']     = $nIDUser;
            $aDataOrder['updated_time']     = time();
            
            $oOrders->update($aDataOrder);

            $db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");


            $nIDOrder = $aDataOrder['id'];

            $positive = [];
            $negative = [];

            foreach ( $aOrderRows as $val ) {
                if ( $val['paid_sum'] >= 0 ) {
                    $positive[] = $val;
                } else {
                    $negative[] = $val;
                }
            }

            $aOrderRows = array_merge($negative, $positive);

            foreach ( $aOrderRows as $val ) {
                $nIDFirm        = $oFirms->getFirmByOffice($val['id_office']);
                $isDDS          = isset($val['is_dds'])         ? $val['is_dds']            : 0;
                $nSumRow        = isset($val['paid_sum'])       ? $val['paid_sum']          : 0;
                $nIDRow         = isset($val['id_doc_row'])     ? $val['id_doc_row']        : 0;
                $nIDDirection   = isset($val['id_direction'])   ? $val['id_direction']      : 0;

                if ($doc_type == "sale") {
                    $tRow       = PREFIX_SALES_DOCS_ROWS . substr($nIDRow, 0, 6);

                    // Фондове
                    if ( empty($nIDTran) && empty($isDDS) && !empty($nSumRow) ) {
                        $nCheckObject       = $oFunds->checkFormulaForObject($val['id_object']);
                        $nCheckFirm         = $oFunds->checkFormulaForFirm($nIDFirm);

                        if ($nCheckFirm == 1) {
                            throw new Exception("Некоректна схема за фондовете към фирма [{$nIDFirm}]!", DBAPI_ERR_FAILED_TRANS);
                        } elseif ($nCheckFirm === 0) {
                            throw new Exception("Липсва схема за фондовете към фирма [{$nIDFirm}]!", DBAPI_ERR_FAILED_TRANS);
                        }

                        if ( $nCheckObject == 2 ) {
                            $nFirmFormula = $oFunds->getFirmByFormulaForObject($val['id_office']);
                            //ob_toFile($nFirmFormula, "formula.txt");
                            if ( $nFirmFormula == $nIDFirm ) {
                                $aFundsData = $oFunds->getFundFormulaForObject($val['id_office']);
                            } else {
                                $aFundsData = $oFunds->getFundFormulaForFirm($nIDFirm);
                            }
                        } else {
                            $aFundsData = $oFunds->getFundFormulaForFirm($nIDFirm);
                        }

                        $nTotalPercent = array_sum($aFundsData);

                        if ($nTotalPercent != 100) {
                            throw new Exception("Некоректна схема за фондовете към фирма или обект!", DBAPI_ERR_FAILED_TRANS);
                        }

                        foreach ( $aFundsData as $nIDDirectionType => $nSumPercent ) {
                            if ( !isset($aFundsBalance[$nIDDirectionType]) ) {
                                $aFundsBalance[$nIDDirectionType] = $oFunds->getFundSaldoById($nIDDirectionType);
                                $aStartBalance[$nIDDirectionType] = $aFundsBalance[$nIDDirectionType];
                            }

                            $nSumAmount = $nSumRow * $nSumPercent / 100;
                            $aFundsBalance[$nIDDirectionType] -= $nSumAmount;

                            list($y, $m) = explode("-", $val['month']);

                            if ( date("Y-m-01") > $y . "-" . $m . "-01" ) {
                                list($y, $m) = explode("-", date("Y-m"));
                            }

                            $month = $y . "-" . $m . "-01";

                            // Фондове - месечни - НОВО!!!
                            if ( !isset($aMonthFundBalance[$nIDDirectionType][$month]) ) {
                                $fundSaldo = $oFunds->getSaldoByMonthType($month, $nIDDirectionType);

                                $aMonthFundBalance[$nIDDirectionType][$month] = ['id' => $fundSaldo['id'] ?? 0, 'sum' => $nSumAmount];
                            } else {
                                $aMonthFundBalance[$nIDDirectionType][$month]['sum'] += $nSumAmount;
                            }

                            // Фондове - тотали - НОВО!!!
                            if ( !isset($aTotalFundBalance[$nIDDirectionType]) ) {
                                $aTotalFundBalance[$nIDDirectionType] = $nSumAmount;
                            } else {
                                $aTotalFundBalance[$nIDDirectionType] += $nSumAmount;
                            }
                        }
                    }
                } else {
                    $tRow       = PREFIX_BUY_DOCS_ROWS . substr($nIDRow, 0, 6);

                    // Фондове
                    if ( !empty($nIDDirection) && empty($nIDTran) && empty($isDDS) && !empty($nSumRow) ) {
                        if ( !isset($aFundsBalance[$nIDDirection]) ) {
                            //$oFunds->moveCurrentFunds($nIDDirection);
                            $aFundsBalance[$nIDDirection] = $oFunds->getFundSaldoById($nIDDirection);
                        }

                        $y = date("Y");
                        $m = date("m");
                        /*
                        $oFundsBalance 	= new DBBase2($db_finance, "funds_saldo");
                        //$oFundsDaily    = new DBBase2($db_finance, "funds_daily");

                        // Фондове - месечни
                        $aMonFundBalance                          = $oFunds->getSaldoByMonthType($y."-".$m."-01", $nIDDirection);
                        $aMonFundBalance["saldo"]                 = $aMonFundBalance["saldo"] + $nSumRow;
                        $aMonFundBalance["month"]                 = $y."-".$m."-01";
                        $aMonFundBalance["id_direction_type"]     = $nIDDirection;

                        $oFundsBalance->update($aMonFundBalance);
                        /*
                        // Фондове - Дневни
                        $aDailyFunds = $oFunds->getDailyRecord($nIDDirection);

                        if ( empty($aDailyFunds) ) {
                            $aDailyFunds['id_direction_type']   = $nIDDirection;
                            $aDailyFunds['to_date']             = time();
                            $aDailyFunds['start_saldo']         = $aFundsBalance[$nIDDirection];
                            $aDailyFunds['end_saldo']           = $aFundsBalance[$nIDDirection] + $nSumRow;
                        } else {
                            $aDailyFunds['end_saldo']           = $aDailyFunds['end_saldo'] + $nSumRow;
                        }

                        $oFundsDaily->update($aDailyFunds);
                        */
                        // Фондове - Totals
                        $db_sod->Execute("UPDATE {$db_name_sod}.directions_type SET saldo = saldo + '{$nSumRow}' WHERE id = {$nIDDirection} LIMIT 1");
                    }
                }

                $aBalance         = $oBalance->getSaldoByFirm($nIDFirm, $isDDS);
                $nIDBalance       = !empty($aBalance) ? $aBalance['id'] : 0;
                $nCurrentBalance  = 0;
                $nAccountState  = 0;

                // Салдо на фирмата с изчакване!!!
                if (!empty($nIDBalance)) {
                    $oRes = $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDBalance} LIMIT 1 FOR UPDATE");
                    $nCurrentBalance = !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
                } else {
                    throw new Exception("Неизвестно салдо по фирма!", DBAPI_ERR_FAILED_TRANS);
                }

                // Наличност по сметка
                if (!empty($nIDAccount)) {
                    $oRes = $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1 FOR UPDATE");
                    $nAccountState = !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;
                } else {
                    throw new Exception("Неизвестна сметка!", DBAPI_ERR_FAILED_TRANS);
                }

                if ($doc_type == "buy") {
                    $state = sprintf("%01.2f", $nAccountState + $nSumRow);

                    if (empty($nIDTran)) {
                        $saldo = sprintf("%01.2f", $nCurrentBalance + $nSumRow);
                        $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum + '{$nSumRow}' WHERE id = {$nIDBalance} LIMIT 1");
                    } else {
                        $saldo = sprintf("%01.2f", $nCurrentBalance);
                    }

                    $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum + '{$nSumRow}' WHERE id_bank_account = {$nIDAccount} ");
                    $db_finance->Execute("UPDATE {$db_name_finance}.{$tRow} SET paid_sum = paid_sum - '{$nSumRow}', updated_user = {$nIDUser}, updated_time = NOW() WHERE id = {$nIDRow} LIMIT 1");
                } else {
                    $state = sprintf("%01.2f", $nAccountState - $nSumRow);

                    if (empty($nIDTran)) {
                        $saldo = sprintf("%01.2f", $nCurrentBalance - $nSumRow);
                        $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum - '{$nSumRow}' WHERE id = {$nIDBalance} LIMIT 1");
                    } else {
                        $saldo = sprintf("%01.2f", $nCurrentBalance);
                    }

                    $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum - '{$nSumRow}' WHERE id_bank_account = {$nIDAccount} ");
                    $db_finance->Execute("UPDATE {$db_name_finance}.{$tRow} SET paid_sum = paid_sum - '{$nSumRow}', updated_user = {$nIDUser}, updated_time = NOW() WHERE id = {$nIDRow} LIMIT 1");
                }

                if ($saldo < 0) {
                    throw new Exception("Недостатъчно салдо по фирма!", DBAPI_ERR_FAILED_TRANS);
                }

                if ($state < 0) {
                    throw new Exception("Недостатъчна наличност по сметка!", DBAPI_ERR_FAILED_TRANS);
                }

                $val['id']              = 0;
                $val['id_order']        = $nIDOrder;
                $val['saldo_state']     = $saldo;
                $val['account_state']   = $state;
                $val['paid_sum']        = $nSumRow * -1;

                $oOrderRow->update($val);
            }

            // ФОНДОВЕ
            if ($doc_type == "sale") {
                /*
                $oFundsDaily = new DBBase2($db_finance, "funds_daily");
                $oFundsBalance = new DBBase2($db_finance, "funds_saldo");


                // Фондове - месечни
                foreach ( $aMonthFundBalance as $nIDDirection => $aMonth ) {
                    foreach ( $aMonth as $month => $aData ) {
                        if ( !empty($aData['id']) ) {
                            $db_finance->Execute("UPDATE {$db_name_finance}.funds_saldo SET saldo = saldo - '{$aData['sum']}' WHERE id = {$aData['id']} ");
                        } else {
                            $aMonFundBalance                      = [];
                            $aMonFundBalance["saldo"]             = 0 - $aData['sum'];
                            $aMonFundBalance["month"]             = $month;
                            $aMonFundBalance["id_direction_type"] = $nIDDirection;

                            $oFundsBalance->update($aMonFundBalance);
                        }
                    }

                }

                // Дневни фондове
                foreach ($aStartBalance as $nsld => $nstns) {
                    $nIDDailyRecord = $oFunds->getDailyRecordIdByFund($nsld);

                    $aDailyFunds = array();
                    $aDailyFunds['id'] = $nIDDailyRecord;

                    if (empty($nIDDailyRecord)) {
                        $aDailyFunds['id_direction_type'] = $nsld;
                        $aDailyFunds['to_date'] = time();
                        $aDailyFunds['start_saldo'] = $nstns;
                        $aDailyFunds['end_saldo'] = $aFundsBalance[$nsld];
                    } else {
                        $aDailyFunds['end_saldo'] = $aFundsBalance[$nsld];
                    }

                    $oFundsDaily->update($aDailyFunds);
                }
                */
                // Фондове - тотали
                foreach ( $aTotalFundBalance as $nIDDirection => $nSumAmount ) {
                    $db_sod->Execute("UPDATE {$db_name_sod}.directions_type SET saldo = saldo - '{$nSumAmount}' WHERE id = {$nIDDirection} LIMIT 1");
                }
            }

            $db_finance->CompleteTrans();
            $db_system->CompleteTrans();
            $db_sod->CompleteTrans();
        } catch (Exception $e) {
            $sMessage = $e->getMessage();

            $db_finance->FailTrans();
            $db_system->FailTrans();
            $db_sod->FailTrans();

            return $this->setError($sMessage);
        }
    }

    private function getBuyDocRows($nID) {
        global $db_finance, $db_name_finance, $db_name_sod;

        if ( !$this->isValidID($nID) ) {
            return $this->setError("Невалиден документ!");
        }

        $sTable	= "buy_docs_rows_" . substr($nID, 0, 6);        //PREFIX_BUY_DOCS_ROWS
        $ob = new DBBase2($db_finance, $sTable);

        $sQuery = "
            SELECT
                sd.*,
                ofc.id_firm,
                ofc.name as region,
                frm.name as firm,
                o.name as object
            FROM {$db_name_finance}.{$sTable} sd
            LEFT JOIN {$db_name_sod}.offices ofc ON ofc.id = sd.id_office
            LEFT JOIN {$db_name_sod}.firms frm ON frm.id = ofc.id_firm
            LEFT JOIN {$db_name_sod}.objects o ON o.id = sd.id_object
            WHERE sd.id_buy_doc = {$nID}
      
        ";

        return $ob->select($sQuery);
    }

    public function makeExpenseOrder($nIDDocument, $nIDAccount, $orderSum = 0 ) {
        global $db_sod, $db_system, $db_finance, $db_name_system, $db_name_sod, $db_name_finance, $mname;

        $this->getCurrentUser();

        // Валидации
        if ( empty($nIDDocument) || !$this->isValidID($nIDDocument) ) {
            return $this->setError("Невалиден документ!");
        }

        if ( empty($nIDAccount) || !is_numeric($nIDAccount) ) {
            return $this->setError("Изберете валидна сметка!");
        }

        $oCashier       = new DBBase2($db_finance, "cashier");
        //$oFundsBalance 	= new DBBase2($db_finance, "funds_saldo");
        //$oFundsDaily    = new DBBase2($db_finance, "funds_daily");
        $oBuyDocRows	= new DBBuyDocsRows();
        $oBuyDoc		= new DBBuyDocs();
        $oBank			= new DBBankAccounts();
        //$oFunds         = new DBFunds();
        $oFirms 		= new DBFirms();
        $oBalance		= new DBSaldo();

        $oOrders        = new DBMonthTable($db_name_finance, PREFIX_ORDERS, $db_finance);
        $oOrderRows     = new DBMonthTable($db_name_finance, PREFIX_ORDERS_ROWS, $db_finance);

        $nIDTran	    = $oBuyDocRows->checkForTransfer( $nIDDocument );
        $nIDTran2	    = $oBuyDocRows->checkForTransfer( $nIDDocument, 1 );

        if ( !empty($nIDTran) && !empty($nIDTran2) ) {
            return $this->setError("В документа участват комбинации от услуги и ТРАНСФЕР!");
        }

        $aDocument 		= $oBuyDoc->getDoc($nIDDocument);

        if ( isset($aDocument['doc_status']) && $aDocument['doc_status'] == "canceled" ) {
            return $this->setError("Документа е анулиран!");
        }

        $sTypeBank		= $oBank->getTypeAccoutById($nIDAccount);
        $aDocumentRows	= $this->getBuyDocRows($nIDDocument);

        $nPaidSum       = 0;
        $aFundBalance   = [];
        $unpaidTax = 0;
        $unpaidSum = 0;
        $negativeSum = 0;
        $positiveSum = 0;
        $hasUnpaidRows = false;

        foreach ( $aDocumentRows as $row ) {
            if ( abs($row['total_sum'] - $row['paid_sum']) > 0 || $row['paid_date'] == "0000-00-00 00:00:00" ) {
                $hasUnpaidRows = true;
            }

            $us = ($row['total_sum'] - $row['paid_sum']);

            if ( $row['is_dds'] == 1 ) {
                $unpaidTax = $us;
            } else {
                $unpaidSum += $us;

                if ( $us < 0 ) {
                    $negativeSum += $us;
                } else {
                    $positiveSum += $us;
                }
            }
        }
        
        // Няма нищо за плащане!
        if ( !$hasUnpaidRows ) {
            return $this->setError("Няма намерени редове за плащане");
        }
        
        $unpaidTotalSum = $unpaidSum + $unpaidTax;

        if ( $unpaidTotalSum != $orderSum ) {
            if ( $unpaidTotalSum > 0 ) {
                if ( $orderSum > $unpaidTotalSum ) {
                    $orderSum = $unpaidTotalSum;
                } elseif ( $orderSum < 0 ) {
                    return $this->setError("Не може да се издаде ордер с отрицателен знак към този вид документ");
                }
            } else {
                
                if ( $unpaidTotalSum == 0 ) {
                    $orderSum = $unpaidTotalSum;
                } elseif ( $orderSum < $unpaidTotalSum ) {
                    $orderSum = $unpaidTotalSum;
                } elseif ( $orderSum > 0 ) {
                    return $this->setError("Не може да се издаде ордер с положителен знак към този вид документ");
                }
            }
        }
        

        if ( $unpaidSum >= 0 ) {
            //$orderSum += ($negativeSum * -1);

            if ( $orderSum < $unpaidTax ) {
                return $this->setError("Сумата за плащане е по-малка от дължимото ДДС!");
            }
        } else {
            if ( $orderSum > $unpaidTax ) {
                return $this->setError("Сумата за плащане е по-малка от дължимото ДДС!");
            }
        }
        
        // Схема за разпределение
        if ( abs($unpaidSum - ($orderSum - $unpaidTax)) > 0.03 ) {
            $nCoefficient = ($orderSum - $unpaidTax) / $unpaidSum;
        } else {
            $nCoefficient = 1;
        }

        $sBuyName 	    = PREFIX_BUY_DOCS.substr($nIDDocument, 0, 6);
        $sRowsName	    = PREFIX_BUY_DOCS_ROWS.substr($nIDDocument, 0, 6);

        // Фондове - проверка за таблици
       // $oFunds->checkMonthTableByDoc($nIDDocument);

        $db_finance->StartTrans();
        $db_system->StartTrans();
        $db_sod->StartTrans();

        try {
            // Следващ номер за ордер
            $oRes       = $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
            $nLastOrder = !empty($oRes->fields['last_num_order'])   ? $oRes->fields['last_num_order'] + 1   : 0;

            // НАЧАЛНА наличност по сметка
            $oRes       = $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1");
            $nAccState  = !empty($oRes->fields['current_sum'])      ? $oRes->fields['current_sum']          : 0;

            $nEin       = isset($aDocument['client_ein'])           ? $aDocument['client_ein']              : 0;
            $nIDClient  = isset($aDocument['id_deliverer'])         ? $aDocument['id_deliverer']            : 0;
            $nDocNum    = isset($aDocument['doc_num'])              ? $aDocument['doc_num']                 : 0;

            $aFirm      = $oFirms->getDDSFirmByEIN($nEin);
            $nIDFirm    = isset($aFirm['id'])                       ? $aFirm['id']                          : 0;

            $aDataOrder                     = array();
            $aDataOrder['id']				= 0;
            $aDataOrder['num']				= $nLastOrder;
            $aDataOrder['doc_num']			= $nDocNum;
            $aDataOrder['order_type'] 		= $orderSum >= 0 ? "expense" : "earning";
            $aDataOrder['id_transfer']		= 0;
            $aDataOrder['id_contragent']	= $nIDClient;
            $aDataOrder['id_doc_firm']		= $nIDFirm;
            $aDataOrder['order_date']		= time();
            $aDataOrder['order_sum']		= $orderSum;
            $aDataOrder['account_type']		= $sTypeBank;
            $aDataOrder['id_person']	    = $this->currentUser;
            $aDataOrder['bank_account_id']	= $nIDAccount;	//isset($aParams['cbAccount']) ? $aParams['cbAccount'] : 0;
            $aDataOrder['doc_id']			= $nIDDocument;
            $aDataOrder['doc_type']			= "buy";
            $aDataOrder['note']				= $this->massPay ? "Групово валидиране!" : "";        // TODO:
            $aDataOrder['created_user']		= $this->currentUser;
            $aDataOrder['created_time']		= time();
            $aDataOrder['updated_user']		= $this->currentUser;
            $aDataOrder['updated_time']		= time();
            if ( ($aDataOrder['order_type'] == "expense") || ($aDataOrder['order_type'] == "earning" && $aDataOrder['doc_type']	= "buy") ) {
                $aDataOrder['account_sum']	= $nAccState - $orderSum;
            } else {
                $aDataOrder['account_sum']	= $nAccState;
            }

            $oOrders->update($aDataOrder);

            // Вдигаме номер за следващ ордер
            $db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");

            $nIDOrder                   = $aDataOrder['id'];

            $currentOrderSum = $orderSum - $unpaidTax;

            // Касиер - id_cache_default - последна смет по подразбиране...
            $aCashier = $oBuyDoc->getCashierByIDPerson();
            $aCashier['id_cash_default'] = $nIDAccount;

            if ( isset($aCashier['id']) && !empty($aCashier['id']) ) {
                $oCashier->update($aCashier);
            }

            // Ордери - разбивка!
            foreach ( $aDocumentRows as $row ) {
                if ( abs($row['total_sum'] - $row['paid_sum']) > 0 || $row['paid_date'] == "0000-00-00 00:00:00" ) {
                    $currentRowSum      = $row['total_sum'] - $row['paid_sum'];

                    if ( $row['is_dds'] != 1 ) {
                        $currentRowSum *= $nCoefficient;
                    }

                    $aBalance			= $oBalance->getSaldoByFirm($row['id_firm'] ?? 0 , (int) $row['is_dds'] > 0);
                    $nIDBalance		    = 0;

                    if ( !empty($aBalance) ) {
                        $nIDBalance 	    = $aBalance['id'];
                    }

                    // Салдо на фирмата с изчакване!!!
                    if ( !empty($nIDBalance) ) {
                        $oRes           = $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDBalance} LIMIT 1 FOR UPDATE");
                        $nCurrentBalance  = !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
                    } else {
                        throw new Exception("Неизвестно салдо по фирма.!", DBAPI_ERR_INVALID_PARAM);
                    }

                    if ( $nAccState < $currentRowSum ) {
                        throw new Exception("Нямате достатъчно наличност по сметката!", DBAPI_ERR_INVALID_PARAM);
                    }

                    $aDataRows								= array();
                    $aDataRows['id']						= 0;
                    $aDataRows['id_order']					= $nIDOrder;
                    $aDataRows['id_doc_row']				= $row['id'];
                    $aDataRows['id_office']					= $row['id_office'];
                    $aDataRows['id_object']					= $row['id_object'];
                    $aDataRows['id_service']				= 0;
                    $aDataRows['id_direction']				= $row['id_direction'];
                    $aDataRows['id_nomenclature_earning']	= 0;
                    $aDataRows['id_nomenclature_expense']	= $row['id_nomenclature_expense'];
                    $aDataRows['id_saldo']					= $nIDBalance;
                    $aDataRows['id_bank']					= $nIDAccount;
                    $aDataRows['saldo_state']				= !empty($nIDTran) ? $nCurrentBalance : ($nCurrentBalance - $currentRowSum);
                    $aDataRows['account_state']				= $nAccState - $currentRowSum;
                    $aDataRows['month']						= $row['is_dds'] == 1 ? $row['month'] : substr($row['month'], 0, 7) . "-01";
                    $aDataRows['type']						= "free";
                    $aDataRows['paid_sum']					= $currentRowSum;
                    $aDataRows['is_dds']					= $row['is_dds'];
                    $aDataRows['updated_user']              = $this->currentUser;
                    $aDataRows['updated_time']              = time();

                    $oOrderRows->update($aDataRows);

                    $db_finance->Execute("UPDATE {$db_name_finance}.$sRowsName SET paid_sum = paid_sum + '{$currentRowSum}', paid_date = NOW() WHERE id = '{$row['id']}' ");

                    
                    $sFirmName	        =  $aFirm['name'] ?: "";
                    
                    // Салда на фирмите
                    if ( empty($nIDTran) ) {

                        if ( $nCurrentBalance < $currentRowSum ) {
                            throw new Exception("Надхвърлено е салдото на фирма {$sFirmName}!", DBAPI_ERR_INVALID_PARAM);
                        }

                        $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum - '{$currentRowSum}' WHERE id = {$nIDBalance} LIMIT 1");
                    }

                    $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum - '{$currentRowSum}' WHERE id_bank_account = {$nIDAccount} ");

                    $nAccState  -= $currentRowSum;
                    $nPaidSum   += $currentRowSum;

                    // Фондове
                    if ( empty($nIDTran) && $currentRowSum != 0 && $row['is_dds'] == 0 ) {
                        
                        $nIDDirect = $row['id_direction'] ?? 0;
                        /*

                        if ( !isset($aFundBalance[$nIDDirect]) ) {
                            $oFunds->moveCurrentFunds($nIDDirect);
                            $aFundBalance[$nIDDirect] = $oFunds->getFundSaldoById($nIDDirect);
                        }

                        $y = date("Y");
                        $m = date("m");

                        
                        $aMonFundBalance                          = $oFunds->getSaldoByMonthType($y."-".$m."-01", $nIDDirect);
                        $aMonFundBalance["saldo"]                 = $aMonFundBalance["saldo"] - $currentRowSum;
                        $aMonFundBalance["month"]                 = $y."-".$m."-01";
                        $aMonFundBalance["id_direction_type"]     = $nIDDirect;

                        if ( $aMonFundBalance["saldo"] < 0 ) {
                            $today = $mname[$m]." ".$y;
                            throw new Exception("Нямате достатъчно наличност по фонд {$nIDDirect} за {$today}!", DBAPI_ERR_INVALID_PARAM);
                        }

                        $oFundsBalance->update($aMonFundBalance);

                        
                        $aDailyFunds = $oFunds->getDailyRecord($nIDDirect);

                        if ( empty($aDailyFunds) ) {
                            $aDailyFunds['id_direction_type']   = $nIDDirect;
                            $aDailyFunds['to_date']             = time();
                            $aDailyFunds['start_saldo']         = $aFundBalance[$nIDDirect];
                            $aDailyFunds['end_saldo']           = $aFundBalance[$nIDDirect] - $currentRowSum;
                        } else {
                            $aDailyFunds['end_saldo']           = $aDailyFunds['end_saldo'] - $currentRowSum;
                        }

                        $oFundsDaily->update($aDailyFunds); */

                        // Totals
                        $db_sod->Execute("UPDATE {$db_name_sod}.directions_type SET saldo = saldo - '{$currentRowSum}' WHERE id = {$nIDDirect} LIMIT 1");
                    }
                }
            }

            if ( sprintf("%01.2f", $nPaidSum) == "0.00" ) {
                throw new Exception("Документа е с нулева сума!", DBAPI_ERR_FAILED_TRANS);
            }

            $db_finance->Execute("UPDATE {$db_name_finance}.{$sBuyName} SET last_order_id = '{$nIDOrder}', last_order_time = NOW(), orders_sum = orders_sum + '{$nPaidSum}', updated_user = {$this->currentUser}, updated_time = NOW(), paid_type = '{$sTypeBank}' WHERE id = '{$nIDDocument}'");

            $db_finance->CompleteTrans();
            $db_system->CompleteTrans();
            $db_sod->CompleteTrans();
        } catch (Exception $e) {
            $db_finance->FailTrans();
            $db_system->FailTrans();
            $db_sod->FailTrans();

            $sMessage = $e->getMessage();

            if ( $sMessage == "" ) {
                $sMessage = "Системна грешка. Свържете се с администратор!";
            }

            return $this->setError($sMessage);
        }
    }

    public function makeOrder($nIDDocument, $nIDAccount, $orderSum = 0 ) {
        global $db_sod, $db_system, $db_finance, $db_name_system, $db_name_sod, $db_name_finance;

        $oFirms 		= new DBFirms();
        $oOrders		= new DBOrders();
        $oOrderRows		= new DBOrdersRows();
        $oSaleDocRows	= new DBSalesDocsRows();
        $oSaleDoc		= new DBSalesDocs();
        $oServices		= new DBObjectServices();
        $oObject		= new DBObjects();
        $oOffices		= new DBOffices();
        $oBalance		= new DBSaldo();
        $oBank			= new DBBankAccounts();
        //$oFunds         = new DBFunds();
        $sales          = new NEWDBSalesDocs();
        //$oFundsBalance  = new DBBase2($db_finance, "funds_saldo");
        //$oFundsDaily    = new DBBase2($db_finance, "funds_daily");
        $oCashier    = new DBBase2($db_finance, "cashier");
        $oClients       = new DBClients();

        $isZeroDuty     = false;
        $nTotalSum      = 0;
        $nPaidSum       = 0;
        $nIDOrder       = 0;
        $nRealPayedSum  = 0;
        $nTypePayment   = 1;

        $aFundsBalance    = array();
        $aStartBalance    = array();
        $aMonthFundBalance = [];
        $aTotalFundBalance = [];

//        $nIDSMSFirm 	= 0;
//        $nIDSMSOffice	= 0;

        $this->getCurrentUser();

        // Валидации
        if ( empty($nIDDocument) || !$this->isValidID($nIDDocument) ) {
            return $this->setError("Невалиден документ!");
        }

        $is_credit = $oSaleDocRows->checkForCredit($nIDDocument);

        if ( $is_credit && $this->massPay ) {
            return;
        }

        if ( empty($nIDAccount) || !is_numeric($nIDAccount) ) {
            return $this->setError("Изберете валидна сметка!");
        }

        $nIDTran	    = $oSaleDocRows->checkForTransfer($nIDDocument);
        $nIDTran2	    = $oSaleDocRows->checkForTransfer($nIDDocument, 1);

        if ( !empty($nIDTran) && !empty($nIDTran2) ) {
            return $this->setError("В документа участват комбинации от услуги и ТРАНСФЕР!!!");
        }

        $sTypeBank		= $oBank->getTypeAccoutById($nIDAccount);
        $aDocument 		= $oSaleDoc->getDoc($nIDDocument);
        $aDocumentRows	= $oSaleDocRows->getRowsByDoc($nIDDocument);


        if ( isset($aDocument['doc_status']) && $aDocument['doc_status'] == "canceled" ) {
            return $this->setError("Документа е анулиран!");
        }

        if ( $aDocument['doc_type'] == "kreditno izvestie" ) {
            $orderSum *= -1;
        }

        // ДДС
        $aDDS 		        = $oSaleDocRows->getDDSByDoc($nIDDocument);
        $nDDSUnpaid	    = isset($aDDS[0]['paid_sum']) 	? $aDDS[0]['total_sum'] - $aDDS[0]['paid_sum'] 	: 0;
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
            /*
            if ( !$this->massPay ) {
                continue;
            }
            */

            $nTotalSum 	+= $val['total_sum'];
            $nPaidSum	+= $val['paid_sum'];

            if ( $val['total_sum'] != $val['paid_sum'] ) {
                $isZeroDuty = true;
            }
        }

        $nUnpaidSum            = ($nTotalSum - $nPaidSum);

        if ( $nDDSType != 2 ) {
            $nUnpaidSum += $nDDSUnpaid;
        }

        $nUnpaidSumWithoutDDS  = ($nTotalSum - $nPaidSum);

        if ( $this->massPay ) {
            $orderSum = $nUnpaidSum;
        }

        if ( abs($orderSum) < 0.10 ) {
            $orderSum = $nUnpaidSum;
        }

        // Плащаме всичко
        //if ( $this->massPay ) {
        //    $orderSum = $nUnpaidSum;
        //}

        // Имаме частично плащане
        if ( abs($orderSum - $nUnpaidSum) > 0.03 ) {
            $isZeroDuty         = false;
            $hasFullyPayment    = false;
            $nTypePayment       = 2;
        }

        if ( !$hasFullyPayment && ($checkDDS || $nIDTran) ) {
            return $this->setError("Не е възможно пропорционално\nплащане!");
        }

        $db_finance->StartTrans();
        $db_system->StartTrans();
        $db_sod->StartTrans();

        try {
            // Създаваме ордер!
            if ($isZeroDuty || $orderSum != 0) {
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

                // Касиер - id_cache_default - последна смет по подразбиране...
                $aCashier = $sales->getCashierByIDPerson();
                $aCashier['id_cash_default'] = $nIDAccount;

                if ( isset($aCashier['id']) && !empty($aCashier['id']) ) {
                    $oCashier->update($aCashier);
                }

                // Опис
                // Плащаме първо ДДС
                if ( $nDDSUnpaid != 0 ) {
                    $isDDS          = $nDDSType;
                    $nIDOffice      = isset($aFirm['id_office_dds']) ? $aFirm['id_office_dds'] : 0;
                    $nIDFirm        = $oFirms->getFirmByOffice($nIDOffice);
                    $sFirm          = $oOffices->getFirmNameByIDOffice($nIDOffice);

                    // Проверка за пълна наличност
                    if (abs($orderSum) >= abs($nDDSUnpaid)) {
                        $currentRowSum  = $nDDSUnpaid;
                        $orderSum   -= $nDDSUnpaid;
                    } else {
                        $currentRowSum  = $orderSum;
                        $orderSum   = 0;
                    }

                    $aSaldo             = $oBalance->getSaldoByFirm($nIDFirm, 1);
                    $nIDSaldo           = 0;

                    if ( !empty($aSaldo) ) {
                        $nIDSaldo       = $aSaldo['id'];
                    }

                    // Салдо на фирмата с изчакване!!!
                    if ( !empty($nIDSaldo) ) {
                        $oRes           = $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
                        $nCurrentSaldo  = !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
                    } else {
                        throw new Exception("Неизвестно салдо по фирма..!", DBAPI_ERR_FAILED_TRANS);
                    }

                    // Наличност по сметка
                    if (!empty($nIDAccount)) {
                        $oRes           = $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1 FOR UPDATE");
                        $nAccountState  = !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;
                    } else {
                        throw new Exception("Неизвестна сметка!", DBAPI_ERR_FAILED_TRANS);
                    }

                    if (($nAccountState + $currentRowSum) < 0) {
                        throw new Exception("Нямате достатъчно наличност по сметката!", DBAPI_ERR_FAILED_TRANS);
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
                            throw new Exception("Недостатъчно салдо по фирма {$sFirm}!", DBAPI_ERR_FAILED_TRANS);
                        }

                        $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum + '{$currentRowSum}' WHERE id = {$nIDSaldo} LIMIT 1");
                    }

                    $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum + '{$currentRowSum}' WHERE id_bank_account = {$nIDAccount} ");
                }

                // Схема за разпределение
                if ( $aDocument['doc_type'] == "kreditno izvestie" ) {
                    if ($nTypePayment == 2 && $orderSum >= $nUnpaidSumWithoutDDS) {
                        $nCoefficient = $orderSum / $nUnpaidSumWithoutDDS;
                    } else {
                        $nCoefficient = 1;
                    }
                } else {
                    if ($nTypePayment == 2 && $orderSum <= $nUnpaidSumWithoutDDS) {
                        $nCoefficient = $orderSum / $nUnpaidSumWithoutDDS;
                    } else {
                        $nCoefficient = 1;
                    }
                }

                $aFirmOffices = [];
                $aDocumentServices = [];

                // Опис
                foreach ($aDocumentRows as $row) {
                    if ( $row['is_dds'] != 0 ) {    //!$this->massPay  ||
                        continue;
                    }

                    if ($row['total_sum'] - $row['paid_sum'] != 0) {
                        $nIDObject      = $row['id_object'];

                        if ( !isset($row['id_office']) || empty($row['id_office']) ) {
                            throw new Exception("Има услуга без направление!", DBAPI_ERR_FAILED_TRANS);
                        } else {
                            $nIDOffice  = $row['id_office'];
                        }

                        // Фирма
                        if ( !isset($aFirmOffices[$row['id_office']]) ) {
                            $idfrm          = $oFirms->getFirmByOffice($row['id_office']);
                           // $nCheckObject   = $oFunds->checkFormulaForObject($row['id_office']);
                           // $nCheckFirm     = $oFunds->checkFormulaForFirm($idfrm);

//                            if ($nCheckObject == 2) {
//                                $nFirmFormula = $oFunds->getFirmByFormulaForObject($nIDObject);
//
//                                if ( $nFirmFormula == $idfrm ) {
//                                    $aFundsData = $oFunds->getFundFormulaForObject($nIDObject);
//                                } else {
//                                    $aFundsData = $oFunds->getFundFormulaForFirm($idfrm);
//                                }
//                            } else {
//                                $aFundsData = $oFunds->getFundFormulaForFirm($idfrm);
//                            }

//                            $aFirmOffices[$row['id_office']] = [
//                                'id_firm' => $idfrm,
//                                'firm_name' => $oOffices->getFirmNameByIDOffice($row['id_office']),
//                                'id_saldo' => $oBalance->getSaldoByFirm($idfrm, 0),
//                                'nCheckObject' => $nCheckObject,
//                                'nCheckFirm' => $nCheckFirm,
//                                'aFundsData' => $aFundsData
//                            ];
                        }

                        // услуги
                        if ( !isset($aDocumentServices[$row['id_service']]) ) {
                            $aDocumentServices[$row['id_service']] = $oServices->getService($row['id_service']);
                        }

                        //$nIDFirm        = $oFirms->getFirmByOffice($row['id_office']); //isset($aFirm['id']) ? $aFirm['id'] : 0;
                        //$sFirm          = $oOffices->getFirmNameByIDOffice($row['id_office']);

                        $nIDFirm        = $aFirmOffices[$row['id_office']]['id_firm'];
                        $sFirm          = $aFirmOffices[$row['id_office']]['firm_name'];

                        $nIDService     = $row['id_service'];
                        $nIDDuty        = $row['id_duty'];

                        //$aService       = $oServices->getService($row['id_service']);
                        $nIDEarning     = isset($aDocumentServices[$row['id_service']]['id_earning']) ? $aDocumentServices[$row['id_service']]['id_earning'] : 0;
                        //$nIDEarning     = isset($aService['id_earning']) ? $aService['id_earning'] : 0;

                        $tSum           = $row['total_sum'] - $row['paid_sum'];
                        $sMonth         = substr($row['month'], 0, 7) . "-01";

                        $currentRowSum  = $tSum * $nCoefficient;
                        $nCurrentSaldo  = 0;
                        $nAccountState  = 0;
                        $nIDSaldo        = $aFirmOffices[$row['id_office']]['id_saldo']['id'] ?? 0;

                        // Салдо на фирмата с изчакване!!!
                        if (!empty($nIDSaldo)) {
                            $oRes           = $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
                            $nCurrentSaldo  = !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
                        } else {
                            throw new Exception("Неизвестно салдо по фирма...!", DBAPI_ERR_FAILED_TRANS);
                        }

                        // Наличност по сметка
                        if (!empty($nIDAccount)) {
                            $oRes = $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1 FOR UPDATE");
                            $nAccountState = !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;
                        } else {
                            throw new Exception("Неизвестнa сметка!", DBAPI_ERR_FAILED_TRANS);
                        }

                        if (($nAccountState + $currentRowSum) < 0) {
                            throw new Exception("Нямате достатъчно наличност по сметката!", DBAPI_ERR_FAILED_TRANS);
                        }

//                        if ( $nIDFirm == 2 && $nIDOffice != 72 ) {
//                            $nIDSMSFirm 	= $nIDFirm;
//                            $nIDSMSOffice 	= $nIDOffice;
//                        }

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
                                throw new Exception("Недостатъчно салдо по фирма {$sFirm}!", DBAPI_ERR_FAILED_TRANS);
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

                            // Някакви статистики...

                            if (isset($aServiceData['real_paid']) && abs($tSum - $currentRowSum) < 0.09) {
                                // Ъпдейтвам нещо си...
                                $sQuery = "
                                UPDATE {$db_name_sod}.statistics_rows_unpaid
                                SET sum_paid = sum_paid + '{$currentRowSum}', id_office = {$nObjOffc}, is_paid = 1
                                WHERE id_object = {$nIDObject}
                                    AND id_service = {$nIDDuty}
                                    AND stat_month = '{$sMonth}'
										";

                                $db_sod->Execute($sQuery);
                            } else {
                                // Ъпдейтвам нещо си...
                                $sQuery = "
                                UPDATE {$db_name_sod}.statistics_rows_unpaid
                                SET sum_paid = sum_paid + '{$currentRowSum}', id_office = {$nObjOffc}, is_paid = 0
                                WHERE id_object = {$nIDObject}
                                    AND id_service = {$nIDDuty}
                                    AND stat_month = '{$sMonth}'
										";
                                $db_sod->Execute($sQuery);
                            }
                        }

                        // Фондове
                        if (empty($row['is_dds']) && empty($nIDTran) && $currentRowSum != 0) {   // Трансфери?
                            $nCheckFirm         = $aFirmOffices[$row['id_office']]['nCheckFirm'];
                            $aFundsData         = $aFirmOffices[$row['id_office']]['aFundsData'];
                            $real_sum           = $currentRowSum;

                            if ($nCheckFirm == 1) {
                                throw new Exception("Некоректна схема за фондовете към фирма [{$nIDFirm}]!", DBAPI_ERR_FAILED_TRANS);
                            } elseif ($nCheckFirm === 0) {
                                throw new Exception("Липсва схема за фондовете към фирма [{$nIDFirm}]!", DBAPI_ERR_FAILED_TRANS);
                            }

                            $nTotalPercent = array_sum($aFundsData);

                            if ($nTotalPercent != 100) {
                                throw new Exception("Некоректна схема за фондовете към фирма или обект!", DBAPI_ERR_FAILED_TRANS);
                            }

                            foreach ($aFundsData as $nIDDirectionType => $nSumPercent) {
                                if ( !isset($aFundsBalance[$nIDDirectionType]) ) {
                                    $aFundsBalance[$nIDDirectionType] = $oFunds->getFundSaldoById($nIDDirectionType);
                                    $aStartBalance[$nIDDirectionType] = $aFundsBalance[$nIDDirectionType];
                                }

                                $nSumAmount = $real_sum * $nSumPercent / 100;
                                $aFundsBalance[$nIDDirectionType] += $nSumAmount;

                                list($y, $m) = explode("-", $aDataRows['month']);

                                $month = $y . "-" . $m . "-01";

                                // Фондове - месечни - НОВО!!!
                                if ( !isset($aMonthFundBalance[$nIDDirectionType][$month]) ) {
                                    $fundSaldo = $oFunds->getSaldoByMonthType($month, $nIDDirectionType);

                                    $aMonthFundBalance[$nIDDirectionType][$month] = ['id' => $fundSaldo['id'] ?? 0, 'sum' => $nSumAmount];
                                } else {
                                    $aMonthFundBalance[$nIDDirectionType][$month]['sum'] += $nSumAmount;
                                }

                                // Фондове - тотали - НОВО!!!
                                if ( !isset($aTotalFundBalance[$nIDDirectionType]) ) {
                                    $aTotalFundBalance[$nIDDirectionType] = $nSumAmount;
                                } else {
                                    $aTotalFundBalance[$nIDDirectionType] += $nSumAmount;
                                }
                            }
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
                $db_finance->Execute("UPDATE {$db_name_finance}.{$sSaleName} SET orders_sum = orders_sum + '{$nRealPayedSum}', last_order_id = '{$nIDOrder}', last_order_time = NOW(), paid_type = '{$sTypeBank}', id_bank_account = '{$nIDAccount}'  WHERE id = '{$nIDDocument}'");

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
                /*
                // Фондове - месечни 
                foreach ( $aMonthFundBalance as $nIDDirection => $aMonth ) {
                    foreach ( $aMonth as $month => $aData ) {
                        // throw new Exception(ArrayToString($aData), DBAPI_ERR_INVALID_PARAM);
                        if ( !empty($aData['id']) ) {
                            $db_finance->Execute("UPDATE {$db_name_finance}.funds_saldo SET saldo = saldo + '{$aData['sum']}' WHERE id = {$aData['id']} ");
                        } else {
                            $aMonFundSaldo                      = [];
                            $aMonFundSaldo["saldo"]             = $aData['sum'];
                            $aMonFundSaldo["month"]             = $month;
                            $aMonFundSaldo["id_direction_type"] = $nIDDirection;

                            $oFundsBalance->update($aMonFundSaldo);
                        }
                    }

                }
                /*
                // Дневни фондове
                foreach ($aStartBalance as $nsld => $nstns) {
                    $nIDDailyRecord = $oFunds->getDailyRecordIdByFund($nsld);

                    $aDailyFunds = array();
                    $aDailyFunds['id'] = $nIDDailyRecord;

                    if (empty($nIDDailyRecord)) {
                        $aDailyFunds['id_direction_type'] = $nsld;
                        $aDailyFunds['to_date'] = time();
                        $aDailyFunds['start_saldo'] = $nstns;
                        $aDailyFunds['end_saldo'] = $aFundsBalance[$nsld];
                    } else {
                        $aDailyFunds['end_saldo'] = $aFundsBalance[$nsld];
                    }

                    $oFundsDaily->update($aDailyFunds);
                }
                */
                // Фондове - тотали
                foreach ( $aTotalFundBalance as $nIDDirection => $nSumAmount ) {
                    $db_sod->Execute("UPDATE {$db_name_sod}.directions_type SET saldo = saldo + '{$nSumAmount}' WHERE id = {$nIDDirection} LIMIT 1");
                }

                // TODO:
                // СМС известяване за нова фактура
                // if ( !$nIDTran && ($nIDSMSFirm == 2 && $nIDSMSOffice != 72) && $aDocument['doc_type'] == "faktura" && ($aDocument['epay_provider'] == 0 && $aDocument['id_bank_epayment'] == 0) ) {
                if ( !$nIDTran && defined('SMS_FOR_PAYMENT') &&  SMS_FOR_PAYMENT == 1 && $aDocument['doc_type'] == "faktura" && ($aDocument['epay_provider'] == 0 && $aDocument['id_bank_epayment'] == 0) ) {
                        if ( abs($nRealPayedSum - $nUnpaidSum) < 0.02 ) {
                        $aTarget = $oClients->getByID($aDocument['id_client']);
                        $ntarget = isset($aTarget['sms_phone']) ? $aTarget['sms_phone'] : 0;

                        if ( !empty($ntarget) ) {
                            $oSMS                       = new DBBase2($db_system, "notifications");

                            $aPar                       = array();
                            $aPar['pay_time']           = date("d.m.Y");
                            $aPar['id_client']          = isset($aDocument['id_wf']) ? $aDocument['id_wf']    : 0;
                            $aPar['id_invoice']         = isset($aDocument['doc_num'])   ? $aDocument['doc_num']      : 0;
                            $aPar['pay_sum']            = sprintf("%01.2f", $nDocAmount);

                            $aSMS                       = array();
                            $aSMS['id_event']           = 9;
                            $aSMS['channel']            = "sms";
                            $aSMS['send_after']         = time();   //date("H") < 9 ? mktime(9, 0, 0, date("m"), date("d"), date("Y")) : time();
                            $aSMS['target']             = $ntarget;
                            $aSMS['id_client']          = $aDocument['id_client'];
                            $aSMS['additional_params']  = json_encode($aPar);

                            $oSMS->update($aSMS);
                        }
                    }
                }
            }

            $db_finance->CompleteTrans();
            $db_system->CompleteTrans();
            $db_sod->CompleteTrans();
        } catch (Exception $e) {
            $sMessage = $e->getMessage();

            $db_finance->FailTrans();
            $db_system->FailTrans();
            $db_sod->FailTrans();

            return $this->setError($sMessage);
        }

        return $nIDOrder;
    }
}
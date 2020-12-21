<?php
	error_reporting(0);

	global $db_sod, $db_personnel, $db_name_personnel, $db_name_sod;
	
	// Дефиниции
    $nIDAccount = 7;

	// Предефиниране на основните пътища
	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
	set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../');
	
	// Вмъкване на критични ресурси
	require_once ("../config/function.autoload.php");
	require_once ("../include/adodb/adodb-exceptions.inc.php");
	require_once ("../config/connect.inc.php");
	require_once ("../include/general.inc.php");

    //ob_toFile(date("Y-m-d H:i:s") . " => " . $_SERVER['REQUEST_METHOD'], "deb.txt");
    //ob_toFile($_REQUEST, "deb.txt");


	if ( isset($_REQUEST['invoices']) && !empty($_REQUEST['invoices']) && isset($_REQUEST['hash']) && $_REQUEST['hash'] == "575312c198ca6198defd0ec6b8e39c0b" ) {
        //$oDBSaleDocs 	    = new DBSalesDocs();
        $oSaleDocRows       = new DBSalesDocsRows();
        $oPay 			    = new DBFinanceOperations();
        $oPay->massPay 	    = false;
        $oPay->withThrown 	= false;

		$invoices 		= explode(',', $_REQUEST['invoices']);

		foreach ( $invoices as $nID ) {
            if (empty($nID)) {
                continue;
            }

            //$aSaleDoc       = [];
            $forPay = [];

            //$oDBSaleDocs->getRecord($nID, $aSaleDoc);

            $is_credit = $oSaleDocRows->checkForCredit($nID);
            $aSaleRows = $oSaleDocRows->getRowsByDoc($nID);
            $aDDS = $oSaleDocRows->getDDSByDoc($nID);

            $nDDSUnpayed = isset($aDDS[0]['paid_sum']) ? $aDDS[0]['total_sum'] - $aDDS[0]['paid_sum'] : 0;

            $nTotalSum = 0;
            $nPaidSum = 0;
            $nValidateSum = 0;

            if ($is_credit) {
                // Списък с описа
                foreach ($aSaleRows as $key => $val) {
                    $is_row_credit = $val['type'] == "credit";
                    $is_disable_payed = false;

                    if ($is_row_credit) {
                        $aPayMon = explode("-", $val['month']);
                        $month = intval($aPayMon[1]);
                        $year = intval($aPayMon[0]);

                        $ttime = mktime(0, 0, 0, $month, 1, $year);
                        $ttime_now = mktime(0, 0, 0, date("m"), 1, date("Y"));

                        $is_disable_payed = $ttime > $ttime_now;
                    }

                    if ($val['payed'] == 0 && $is_row_credit && !$is_disable_payed) {
                        $forPay[] = $val['id'];

                        $nTotalSum += $val['total_sum'];
                        $nPaidSum += $val['paid_sum'];
                    }
                }

                $nValidateSum = ($nTotalSum - $nPaidSum);
            } else {
                // Списък с описа
                foreach ($aSaleRows as $key => $val) {
                    //if ( $val['payed'] == 0 ) {
                    //$forPay[]   = $val['id'];

                    $nTotalSum += $val['total_sum'];
                    $nPaidSum += $val['paid_sum'];
                    //}
                }

                $nValidateSum = ($nTotalSum - $nPaidSum);
            }

            $nValidateSum += $nDDSUnpayed;

            //if ( isset($aSaleDoc['id_bank_epayment']) && ($aSaleDoc['id_bank_epayment'] != 0) ) {
            $oPay->makeOrder($nID, $nIDAccount, $nValidateSum, $forPay);
            //}
        }
	}
?>

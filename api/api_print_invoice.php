<?php
    /**
     * Created by PhpStorm.
     * User: adm
     * Date: 10.9.2020 г.
     * Time: 12:19
     */

    /**
     * @param array $ignoredKeys
     * @return bool
     */
    function checkProtectedData($ignoredKeys = []) {
        $aParams = $_REQUEST;

        $ignoredKeys[] = "_key";
        $ignoredKeys[] = "v";

        foreach ($ignoredKeys as $sKey) {
            unset($aParams[$sKey]);
        }

        $sHashKey = $aParams['_key'] ?? "";


        foreach($aParams as $k=>$v) {
            $aParams[$k] = (string)$v;
        }

        ksort($aParams);

        return sha1('ju123j12-309123k10230-123kk=0132lcc-sdcldkakskdsdf=-0sd0-flksdf-03003lll' . serialize($aParams)) === $sHashKey;
    }

    /**
     * @param array $ignoredKeys
     * @return string
     */
    function generateProtectedData($ignoredKeys = []) {
        $aParams = $_REQUEST;

        $ignoredKeys[] = "_key";
        $ignoredKeys[] = "v";

        foreach ($ignoredKeys as $sKey) {
            unset($aParams[$sKey]);
        }

        foreach($aParams as $k=>$v) {
            $aParams[$k] = (string)$v;
        }

        ksort($aParams);

        return sha1('ju123j12-309123k10230-123kk=0132lcc-sdcldkakskdsdf=-0sd0-flksdf-03003lll' . serialize($aParams));
    }

    require_once ("../config/session.inc.php");
    require_once ('../config/function.autoload.php');

    require_once ("config/connect.inc.php");
    require_once ("include/general.inc.php");
    require_once ("include/validate.inc.php"); // валидации във формите

    global $db_finance, $db_name_finance;

    $oSaleDocs	= new NEWDBSalesDocs();
    $aParams	= Params::getAll();

    $nID 			= $_REQUEST['id'] ?? 0;
    $key			= $_REQUEST['_key'] ?? 0;

    if ( !$oSaleDocs->isValidID($nID) ) {
        http_response_code(400);

        echo "Невалиден документ!";
        die();
    }

    $document = $oSaleDocs->getDocData($nID);

    $version = (int) ($aParams['v'] ?? ($document['version'] ?? 2));

    if ( $version != 1 ) {
        require_once("pdf/pdf_invoice.php");

        $pdf = new InvoicePDF("P");

        try {
            $pdf->PrintReport($nID, '', $document['view_type'], 0);
        } catch (Exception $e) {
            http_response_code(400);

            echo $e->getMessage();
            die();
        }
    } else {
        require_once("pdf/pdf_sale_doc.php");

        $pdf = new SaleDocPDF("P");

        try {
            $pdf->PrintReport($nID, '', $document['view_type'], 0);
        } catch (Exception $e) {
            http_response_code(400);

            echo $e->getMessage();
            die();
        }
    }

    //var_dump($version); die();

    $document['gen_pdf']        = 1;
    $document['gen_pdf_date']   = time();

    if ( !empty($key) ) {
        $document['is_user_print']      = 1;
        $document['user_print_date']    = time();
    }

    $oDocument = new DBMonthTable($db_name_finance,PREFIX_SALES_DOCS, $db_finance);
    //$oDocument->update($document);



    //09.05.2016 - Този метод се използва за разпечатване на документи от telepol.com и mytelepol.
    //Трябва да може да се разпечатват и анулирани документи, затова в условието се добавя проверка
    //за наличие на параметър _key - Този ключ се предава от двата сайта.
    //if ( ($aSaleDoc['doc_status'] == 'final') || isset( $aParams['_key'] )) {



    //}
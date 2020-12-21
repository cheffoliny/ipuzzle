<?php
    global $db_sod, $db_finance, $db_system, $db_name_sod, $db_name_finance, $db_host, $db_user, $db_pass;

    $aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );
    set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../' );

    require_once("../config/function.autoload.php");
    require_once("../include/adodb/adodb-exceptions.inc.php");
    require_once("../config/connect.inc.php");
    require_once("../include/general.inc.php");

    set_time_limit(0);
    ini_set('memory_limit', '3096M');

    $dsn = "mysql:dbname={$db_name_finance};host={$db_host}";

    try {
        $dbh = new PDO($dsn, $db_user, $db_pass);
        $dbh->exec("set names utf8");
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
        die();
    }

    $oDocRows = new DBMonthTable($db_name_finance,PREFIX_SALES_DOCS_ROWS, $db_finance);

    $tables = SQL_get_tables($db_finance, PREFIX_SALES_DOCS_ROWS, "______", "ASC");

    // Структура
    foreach ( $tables as $table ) {
        $db_finance->StartTrans();

        try {
            $alter = "
          
            ";

            $db_finance->Execute($alter);

            $baseTable = PREFIX_SALES_DOCS . substr($table, -6);

            $alter = "
          
            ";

            $db_finance->Execute($alter);

            $db_finance->CompleteTrans();
        } catch (ADODB_Exception $e) {
            $db_finance->FailTrans();
            echo $e->getMessage();
        }
    }

    $key = array_search(PREFIX_SALES_DOCS_ROWS . "origin", $tables);

    if ( false !== $key ) {
        unset($tables[$key]);
    }

    unset($table);

    // Данни
    foreach ( $tables as $table ) {
        $time_start = microtime(true);

        try {
            $dbh->beginTransaction();

            //if ($table == PREFIX_SALES_DOCS_ROWS . "202005") {
                $baseTable = PREFIX_SALES_DOCS . substr($table, -6);
                // Todo: object_name при отстъпки - да се взима от обектите
                $sQuery = "
                SELECT
                    sdr.id,
                    sdr.service_name as v1_service_name,
                    sdr.object_name as v1_object_name,

                    CASE
                        WHEN sdr.`type` = 'month' AND sdr.id_object != 0 THEN IF( LENGTH(os.service_name), os.service_name, sdr.service_name ) 
                        WHEN sdr.`type` = 'month' AND sdr.id_object = 0 AND sdr.is_dds = 1 THEN 'ДДС'
                        WHEN sdr.`type` = 'free' AND sdr.id_object != 0 AND sdr.total_sum <= 0 THEN CONCAT(c.name, ' [ ', os.service_name, ' ]')
                        WHEN sdr.`type` = 'free' AND sdr.id_object != 0 AND sdr.total_sum > 0 THEN CONCAT('Корекция [ ', os.service_name, ' ]')
                        WHEN sdr.`type` = 'free' AND sdr.id_object = 0 THEN sdr.service_name
                        WHEN sdr.`type` = 'single' THEN sdr.service_name
                    END AS 'service_name',

                    IF (sdr.`type` = 'free' AND sdr.id_object != 0, IF (LENGTH(TRIM(o.invoice_name)), o.invoice_name, o.name), sdr.object_name) AS 'object_name',   

                    CASE 
                        WHEN sdr.`type` = 'month' AND sdr.id_object != 0 AND ns.for_smartsot = 1 THEN CONCAT('Смарт СОТ: за м. ', ' ', DATE_FORMAT(sdr.month,'%m.%Y'), ' г.')
                        WHEN sdr.`type` = 'month' AND sdr.id_object != 0 AND ns.for_smartsot = 0 THEN CONCAT(ns.name, ': за м. ', DATE_FORMAT(sdr.month,'%m.%Y'), ' г.')
                        WHEN sdr.`type` = 'free' AND sdr.id_object != 0 AND nsc.for_smartsot = 1 THEN IF (sdr.total_sum <= 0, CONCAT(c.name, ' [ Смарт СОТ ]'), 'Корекция [ Смарт СОТ ]')
                        WHEN sdr.`type` = 'free' AND sdr.id_object != 0 AND nsc.for_smartsot = 0 THEN IF (sdr.total_sum <= 0, CONCAT(c.name, ' [ ', os.service_name, ' ]'), CONCAT('Корекция [ ', os.service_name, ' ]'))
                        WHEN sdr.is_dds = 1 OR sdr.`type` = 'single' THEN ''
                    END AS 'view_type_detail',

                    CASE 
                        WHEN sdr.`type` = 'month' AND sdr.id_object != 0 AND ns.for_smartsot = 1 THEN 'Смарт СОТ'
                        WHEN sdr.`type` = 'month' AND sdr.id_object != 0 AND ns.for_smartsot = 0 THEN ns.name
                        WHEN sdr.`type` = 'free' AND sdr.id_object != 0 THEN 'Отстъпка'
                        WHEN sdr.is_dds = 1 OR sdr.`type` = 'single' THEN ''
                    END AS 'view_type_by_services',

                    IF( sdr.`type` = 'free' AND sdr.id_object != 0, nsc.for_smartsot, IF ( ns.for_smartsot, ns.for_smartsot, 0 ) ) as for_smartsot,

                    IF ( ns.vat_tax, ns.vat_tax, 20 ) AS vat,

                    sdr.total_sum * 1.2 AS total_sum_with_dds 

                    FROM {$db_name_finance}.{$table} sdr
                    
                    LEFT JOIN {$db_name_sod}.objects_services os ON sdr.id_duty_row = os.id
                    LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON sdr.id_service = ns.id
                    LEFT JOIN {$db_name_finance}.nomenclatures_services nsc ON os.id_service = nsc.id
                    LEFT JOIN {$db_name_finance}.concession c ON sdr.id_service = c.id_service
                    LEFT JOIN {$db_name_sod}.objects o ON sdr.id_object = o.id
                ";

                $data = $oDocRows->selectAssoc2($sQuery);

                $stm = "
                    UPDATE {$db_name_finance}.{$table} 
                    SET service_name = :service_name, 
                        object_name = :object_name, 
                        v1_service_name = :v1_service_name, 
                        v1_object_name = :v1_object_name, 
                        view_type_detail = :view_type_detail, 
                        view_type_by_services = :view_type_by_services, 
                        vat = :vat, 
                        for_smartsot = :for_smartsot, 
                        total_sum_with_dds = :total_sum_with_dds
                    WHERE id = :id                    
                ";

                $q = $dbh->prepare($stm);

                foreach ($data as $key => $dataRows) {
                    $q->execute([
                            ':service_name' => $dataRows['service_name'],
                            ':object_name' => $dataRows['object_name'],
                            ':v1_service_name' => $dataRows['v1_service_name'],
                            ':v1_object_name' => $dataRows['v1_object_name'],
                            ':view_type_detail' => $dataRows['view_type_detail'],
                            ':view_type_by_services' => $dataRows['view_type_by_services'],
                            ':vat' => $dataRows['vat'],
                            ':for_smartsot' => $dataRows['for_smartsot'],
                            ':total_sum_with_dds' => $dataRows['total_sum_with_dds'],
                            ':id' => $key]
                    );
                }
            //}

            $time_end = microtime(true);
            $execution_time = ($time_end - $time_start);
            echo $table . " done. Total time: " . $execution_time . "\n";

            $dbh->commit();
        } catch (Exception $ex) {
            $dbh->rollBack();
            echo $ex->getMessage();
        }
    }
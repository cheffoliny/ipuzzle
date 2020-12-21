<?php

/**
 * Created by PhpStorm.
 * User: adm
 * Date: 8.9.2020 г.
 * Time: 17:18
 */

if ( !isset($_SESSION) ) {
    session_start();
}

if ( !defined('PREFIX_SALES_DOCS') ) {
    define('PREFIX_SALES_DOCS',		    'sales_docs_');
    define('PREFIX_SALES_DOCS_ROWS',    'sales_docs_rows_');
}

class NEWDBSalesDocRows extends DBMonthTable {
    function __construct() {
        global $db_name_finance, $db_finance;

        parent::__construct($db_name_finance,PREFIX_SALES_DOCS,$db_finance);
    }

    public function getUnpaidDocsByClient($nIDClient, $date) {
        global $db_name_finance;

        if ( isset($_SESSION['userdata']['check_id_client_old_request']) && $_SESSION['userdata']['check_id_client_old_request'] == $nIDClient ) {
            return;
        } else {
            $_SESSION['userdata']['check_id_client_old_request'] = $nIDClient;
        }

        $sQuery = "
                SELECT
                    sd.id as id
                FROM {$db_name_finance}.".PREFIX_SALES_DOCS."<yearmonth> sd
                JOIN {$db_name_finance}.".PREFIX_SALES_DOCS_ROWS."<yearmonth> sdr ON sd.id = sdr.id_sale_doc
                WHERE 1
                    AND sd.doc_status = 'final'
                    AND sdr.month <= DATE_FORMAT(LAST_DAY('{$date}' - INTERVAL 1 MONTH), '%Y-%m-%d')
                    AND sd.total_sum > sd.orders_sum
                    AND sd.id_client = {$nIDClient}
                GROUP BY sd.id
            ";

        $this->makeUnionSelect($sQuery, 0, strtotime("first day of {$date} previous month"));

        $sQuery2 = "
                SELECT
                    GROUP_CONCAT(t.id) as id
                FROM ( ". $sQuery ." ) AS t
            ";

        return $this->selectOne2($sQuery2);
    }

    public function getObjectRemainingPriceForMonth($nIDObject, $nTimeStamp) {
        global $db_finance, $db_name_finance;

        if ( empty($nIDObject) || !is_numeric($nIDObject) ) {
            return [];
        }

        if ( empty($nTimeStamp) || !is_numeric($nTimeStamp) ) {
            return [];
        }

        $sDate 		= date("Ym", $nTimeStamp);

        $sQuery = "
				SELECT
					SUM(sr.total_sum - sr.paid_sum) as rem
				FROM {$db_name_finance}.<%tablename%> s
				LEFT JOIN {$db_name_finance}.<%tablename_rows%> sr ON sr.id_sale_doc = s.id
				WHERE sr.id_object = {$nIDObject}
					AND DATE_FORMAT(sr.month, '%Y%m') <= '{$sDate}'
					AND s.doc_type = 'faktura'
					AND s.doc_status = 'final'
			";

        $u_query = SQL_union_search($db_finance, $sQuery, PREFIX_SALES_DOCS, "______", "rem", "DESC", 1, 6, [], true);

        return $this->selectOne2($u_query);
    }
}
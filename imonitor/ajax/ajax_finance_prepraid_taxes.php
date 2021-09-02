<?php

define('INCLUDE_CHECK',true);

require_once( "../config/session.inc.php"	);
require_once( "../config/connect.inc.php"	);
require_once( "../config/dictionar.inc.php" );

if( isset($_SESSION['mid']) ):

    ob_start();

    function handle_drop($errno, $errstr, $errfile, $errline){
        if( $errno == E_WARNING ){
            echo 'Проблем с връзката! Проверете всички връзки и опитайте да обновите страницата!';
            $ob = ob_get_clean();
            header("HTTP/1.0 500 Internal server error");
            echo $ob;

        }
    }

    set_error_handler('handle_drop');

    $c = 0;
    $sub_total = 0;
    $total_sum = 0;
    $strTable = '';
    $strReturn = '';

    $aQuery     = "
                SELECT
                    ROUND( SUM( os.total_sum + 0 )/1.2, 0 ) AS 'tSum'   ,
                    DATE_FORMAT( os.last_paid , '%m.%Y'   ) AS 'rMonth',
                    DATE_FORMAT( os.real_paid , '%Y.%m'   ) AS 'pMonth',
                    (SELECT sum(current_sum) FROM finance.account_states WHERE id_bank_account IN(30,42) ) AS 'iBank'
                FROM objects o
                LEFT JOIN objects_services os ON. o.id = os.id_object AND os.to_arc = 0
                WHERE
                    o.id_status IN( 1, 14 ) AND o.id_office IN($strCurrentOffice) AND os.real_paid > NOW()
                GROUP BY pMonth
                ORDER BY pMonth DESC";

    $aResult	=	mysqli_query( $db_sod, $aQuery	) or die( "Error: ".$aQuery );
    $aRows	    =	mysqli_num_rows( $aResult		);

    if( !$aRows ) {
        echo "<li class='callout callout-success'>
                <h5><i class='fa fa-smile-o'></i> Няма обекти без такси и клиент! </h5>
              </li>";
    }

    for( $c = 0; $c < $aRows; $c++ ) {

        $aRow = mysqli_fetch_assoc( $aResult );


        $iBank   = isset( $aRow['iBank'] ) ? $aRow['iBank'] : '000.00';
        $tSum    = isset( $aRow['tSum'] ) ? $aRow['tSum'] : '000.00';
        $rMonth  = isset( $aRow['rMonth'] ) ? $aRow['rMonth'] : '- - : - - &nbsp; - -.- - .- - - -';

        $oName	 = $aRow['oNum'	]." ". $aRow['oName'	] ;
        $shortName  =  mb_substr( $oName, 0, 30, 'utf-8' );

        $sub_total += $tSum;

        $strTable .= "<li>
                <i class='fa fa-paypal'></i> &nbsp; ". $rMonth ."  &nbsp;
                <span class='handle pull-right' style='color: #cc0000;' title='Такса'>
                    <i class='fa fa-money on fa-text-width'></i> &nbsp; ". $sub_total ." &nbsp;
                </span>
            </li>";
        $strReturn = $sub_total.",".$strReturn;

        $total_sum += $sub_total;
    }

    echo $strTable.
        "<li class='bg-red disabled color-palette'>
            <strong class='handle pull-right'> <i class='fa fa-money'></i> ".$iBank." / ".$total_sum." </strong>
            <strong class='handle'>НАЛИЧНОСТ / ОБЩО СУМА СЪБРАНА ПРЕДИ ПАДЕЖ :</strong>
         </li>".
        "<li><a><i class='fa fa-clock-o'></i> ".date ( 'H:i:s d.m.Y' )." / ".$total_sum."</a></li>";

endif;

?>
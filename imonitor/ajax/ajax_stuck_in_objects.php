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
    $sInterval = 60; /* Интервал за периода */
    $sRepeat = 10; ; /* Брой повторения за периода */
    $strMonth   = date('Ym');

    $tQuery	=	"
                SELECT
                    COUNT( a.id )         AS cnt  ,
                    o.end_service_status  AS sStat,
                    o.num                 AS oNum ,
                    o.name                AS oName
                FROM archiv_".$strMonth." a
                LEFT JOIN messages m ON a.id_msg = m.id AND m.to_arc = 0
                LEFT JOIN objects o ON o.id = m.id_obj AND o.id_status != 4
                WHERE
                    a.msg_time >= DATE_ADD( NOW(), INTERVAL -$sInterval MINUTE ) AND a.msg_time > o.end_service_status
                GROUP BY a.num, o.id
                HAVING cnt > $sRepeat
                ORDER BY cnt DESC
                ";

    $tResult	=	mysqli_query( $db_sod, $tQuery	) or die( "Error: ".$tQuery );
    $tRows	    =	mysqli_num_rows( $tResult		);

    if( !$tRows ) {
        echo "<li class='callout callout-success'>
                <h5><i class='fa fa-smile-o'></i> Няма обекти с повече от $sRepeat сигнала за последните $sInterval минути! </h5>
              </li>";
    }

    for( $c =0; $c <= $x_cols; $c++ ) {

        $tRow = mysqli_fetch_assoc( $tResult);

        $cnt     = isset( $tRow['cnt'    ] ) ? $tRow['cnt'     ] : 0;
        $oName	= $tRow['oNum'	]." ". $tRow['oName'	] ;

        echo "<li>
                ". $oName ."
                <span class='handle' style='float: right;'>
                    <small class='label $strColor'><i class='fa fa-clock-o'></i> ". $cnt ." </small>
                </span>
            </li>";

    }

    echo "<li><a><i class='fa fa-clock-o'></i> ".date ( 'H:i:s d.m.Y' )."</a></li>";

endif;

?>
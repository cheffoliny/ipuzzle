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
    $time_interval = 30; /* */

    $tQuery	=	"
                SELECT
                    (
                       SELECT COUNT(id) FROM work_card_movement
                       WHERE  end_time >= DATE_ADD( NOW(), INTERVAL -$time_interval day )
                    ) AS 'tCount',
                    COUNT(wcm.id) AS 'aCount',
                    s.msg_al      AS 'mName'
                 FROM work_card_movement wcm
                 JOIN signals s ON s.id = wcm.alarm_type

                 WHERE
                     wcm.end_time >= DATE_ADD( NOW(), INTERVAL -$time_interval day )
                 GROUP BY s.id
                 ORDER BY aCount DESC";

    $tResult	=	mysqli_query( $db_sod, $tQuery	) or die( "Error: ".$tQuery );
    $tRows	    =	mysqli_num_rows( $tResult		);

    for( $c =0; $c < $tRows; $c++ ) {

        $tRow = mysqli_fetch_assoc( $tResult );

        $tCount = isset( $tRow['tCount'] ) ? $tRow['tCount' ] : 0;
        $aCount = isset( $tRow['aCount'] ) ? $tRow['aCount' ] : 0;
        $mName  = isset( $tRow['mName'  ] ) ? $tRow['mName' ] : 0;

        $strPercent = round($aCount /( $tCount / 100 ), 0);

        echo '
            <div class="clearfix">
                <span class="pull-left">'.$mName.'</span>
                <small class="pull-right">'.$aCount.' / '.$tCount.'</small>
            </div>
            <div class="progress xs">
                <div class="progress-bar progress-bar-blue" style="width:'.$strPercent.'%"></div>
            </div>';

    }

endif;

?>
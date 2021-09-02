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

    $tQuery	=	"
                SELECT
                    (
                      SELECT COUNT(id) FROM work_card_movement
                      WHERE     end_time != '0000-00-00 00:00:00'
                            AND end_time >= DATE_ADD( NOW(), INTERVAL -30 day )
                    ) AS 'tCount',
                    COUNT(wcm.id) AS 'aCount' ,
                    ar.name       AS 'rName'  ,
                    ar.is_patrul  AS 'aPatrul'
                FROM work_card_movement wcm
                JOIN alarm_reasons ar ON ar.id = wcm.id_alarm_reasons

                WHERE
                    wcm.end_time != '0000-00-00 00:00:00' AND wcm.end_time >= DATE_ADD( NOW(), INTERVAL -30 day )
                GROUP BY wcm.id_alarm_reasons
                HAVING aCount > 5
                ORDER BY ar.is_patrul DESC, tCount ASC ";

    $tResult	=	mysqli_query( $db_sod, $tQuery	) or die( "Error: ".$tQuery );
    $tRows	    =	mysqli_num_rows( $tResult		);

    for( $c =0; $c < $tRows; $c++ ) {

        $tRow = mysqli_fetch_assoc( $tResult );

        $tCount = isset( $tRow['tCount'] ) ? $tRow['tCount' ] : 0;
        $aCount = isset( $tRow['aCount'] ) ? $tRow['aCount' ] : 0;
        $rName  = isset( $tRow['rName'  ] ) ? $tRow['rName'  ] : 0;
        $aPatrul = isset( $tRow['aPatrul'] ) ? $tRow['aPatrul'] : 0;

        $strPercent = round($aCount /( $tCount / 100 ), 0);

        if( $aPatrul == 1 ) { $strClass = 'progress-bar progress-bar-blue'; }
        else { $strClass = 'progress-bar progress-bar-red'; }
        echo '
            <div class="clearfix">
                <span class="pull-left">'.$rName.'</span>
                <small class="pull-right">'.$aCount.' / '.$tCount.'</small>
            </div>
            <div class="progress xs">
                <div class="'.$strClass.'" style="width:'.$strPercent.'%"></div>
            </div>';

    }

endif;

?>
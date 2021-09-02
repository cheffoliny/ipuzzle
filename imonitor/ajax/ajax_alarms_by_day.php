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
    $i = 1; /* Counter by chart rows */
    $a = 0; /* Counter by chart abcis */

    /*  Стойностите по X, Y */
    $x      = 20; // стъпка по X - constant-a
    $x_cols = 25; // брой колони - При промяна да се смени и ширината на колоните и стъпката
    $x_base = 5;
    $x_start= 5;
    $y_base = 261;
    $y      = 60;
    $y_rows = 5;

    $col_width = 16;

    $abcis = '';
    $cols  = '';
    $cols_v= '';
    $chart = '<text x="-5" y="'.$y_base.'">0</text>
              <path fill="none" stroke="#aaaaaa" d="M0,'.$y_base.'H610" stroke-width="0.1"></path>';


    $tQuery	=	"
                SELECT
                    ( SELECT
                            CAST(COUNT(wcm1.id) AS decimal) AS 'maxC'
                        FROM work_card_movement wcm1
                        WHERE wcm1.end_time >= DATE_ADD( NOW(), INTERVAL -$x_cols day )
                        GROUP BY SUBSTR(wcm1.end_time, 1, 10)
                        ORDER BY maxC DESC LIMIT 1
                    ) AS mC,
                    SUM( IF(ar.is_patrul = 1, 1, 0) ) AS 'visited',
                    COUNT(wcm.id) AS 'aCount',
                    DATE_FORMAT(wcm.end_time, '%d.%m.%Y' ) AS 'dat_group',
                    DATE_FORMAT(wcm.end_time, '%d.%m'    ) AS 'd_m',
                    GROUP_CONCAT( ' \n ', TIME_FORMAT( wcm.end_time, '%H:%i:%s' ), ' [', ar.name,'] ', wcm.obj_name ORDER BY wcm.end_time ) AS 'rTime',
                    wcm.id_object AS 'oId'  ,
                    o.num         AS 'oNum' ,
                    o.name        AS 'oName',
                    ar.name       AS 'rName'
                FROM work_card_movement wcm
                JOIN alarm_reasons ar ON ar.id = wcm.id_alarm_reasons
                LEFT JOIN objects o ON o.id = wcm.id_object
                WHERE
                    wcm.end_time >= DATE_ADD( NOW(), INTERVAL -$x_cols day )
                GROUP BY dat_group
                ORDER BY wcm.end_time ASC";

    $tResult	=	mysqli_query( $db_sod, $tQuery	) or die( "Error: ".$tQuery );
    $tRows	    =	mysqli_num_rows( $tResult		);

    for( $c =0; $c <= $x_cols; $c++ ) {

        $tRow = mysqli_fetch_assoc( $tResult);

        $mC         = isset( $tRow['mC'     ] ) ? $tRow['mC'     ] : 0;
        $visited    = isset( $tRow['visited'] ) ? $tRow['visited'] : 0;
        $aCount     = isset( $tRow['aCount' ] ) ? $tRow['aCount' ] : 0;
        $d_m        = isset( $tRow['d_m'    ] ) ? $tRow['d_m'    ] : 0;
        $dat_group  = isset( $tRow['dat_group'] ) ? $tRow['dat_group'] : 0;
        $rTime      = isset( $tRow['rTime'  ] ) ? $tRow['rTime' ] : 0;
        $rName      = isset( $tRow['rName'  ] ) ? $tRow['rName' ] : 0;
        $oName	    = $tRow['oNum'	]." ". $tRow['oName'	] ;

        if( $c == 0 ) {

            $mCount =  ( round( $mC, -1 ) + 5 )/4; // Max value for Y
            $coefY = $y_base / round( $mC + 5 , -1 );

            for ($i = 1; $i <= $y_rows; $i++) {
                $y_base = $y_base - $y;

                $mC1 = $mCount * $i;
                $chart .= '<text x="-10" y="' . $y_base . '">' . round( $mC1, 0 ) . '</text>
                           <path fill="none" stroke="#aaaaaa" d="M4,' . $y_base . 'H610" stroke-width="0.1"></path>';
            }

        }
        if( $c == 0 || $c == 5 || $c == 10 || $c == 15 || $c == 20 || $c == 25 ) {
            $x_value = str_pad($d_m, 2, "0", STR_PAD_LEFT);
        } else {
            $x_value = '';
        }
        $abcis .= '<text x="'. $x_base .'" y="273" font-size="9px" >'.$x_value. '</text>';


        // height = ( max_value - y )
        $y_col_value    = ($aCount * $coefY) - 3;
        $y_col_value2   = ($visited * $coefY) - 2;

        $y_height_value  = 260 - $y_col_value;
        $y_height_value2  = 260 - $y_col_value2;

        if( $y_col_value < 0 ) $y_col_value = 0;
        if( $y_col_value2 < 0 )  $y_col_value2 = 0;

        $cols .= '<rect x="'.$x_base.'" y="'.$y_height_value.'" height="'.$y_col_value.'" width="'.$col_width.'" r="0" rx="0" ry="0" fill="#f56954" >
                    <title> Общо:'.$aCount.' / Посетени:'.$visited.' - '.$dat_group.' '.$rTime.'</title></rect>';
        $cols_v.= '<rect x="'.$x_base.'" y="'.$y_height_value2.'" height="'.$y_col_value2.'" width="'.$col_width.'" r="0" rx="0" ry="0" fill="#3c8dbc" >
                    <title> Общо:'.$aCount.' / Посетени:'.$visited.' - '.$dat_group.' '.$rTime.'</title></rect>';


        $x_base = $x_base + $x;

    }
    $chart .= $abcis;
    $chart .= $cols.$cols_v;

    echo $chart;

endif;

?>




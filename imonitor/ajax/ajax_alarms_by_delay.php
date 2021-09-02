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

    $time_delay   = isset( $_GET['time_delay'] ) ? $_GET['time_delay'] : 'end';
    $time_interval = isset( $_GET['time_interval'] ) ? $_GET['time_interval'] : 2;

    $c = 0;
    $system_Delay= 30;  /* Base time for send alarm(in seconds) */
    $patrol_Delay= 30;  /* Base time for receive alarm(in seconds) */
    $str_class = '';

    switch ( $time_delay ) {

        case 'send':
            $str_where = " TIME_TO_SEC( TIMEDIFF( wcm.send_time, wcm.alarm_time ) ) > $system_Delay ";
            break;

        case 'start':
            $str_where = " TIME_TO_SEC( TIMEDIFF( wcm.start_time, wcm.send_time ) ) > $patrol_Delay ";
            break;

        default:
            $str_where = " TIME_TO_SEC( TIMEDIFF( wcm.end_time, wcm.send_time	) ) > ( o.reaction_time_difficult * 60 ) ";
    }

    $tQuery	=	"
                SELECT
                    TIME_TO_SEC( TIMEDIFF( wcm.end_time, wcm.send_time		) )	AS 'aDiffSec',
                    TIME_TO_SEC( TIMEDIFF( wcm.start_time, wcm.send_time	) )	AS 'pDiffSec',
                    TIME_TO_SEC( TIMEDIFF( wcm.send_time, wcm.alarm_time	) )	AS 'sDiffSec',
                    TIMEDIFF( wcm.end_time, wcm.alarm_time 	 )	AS 'aDiff'  ,
                    TIMEDIFF( wcm.start_time, wcm.alarm_time )	AS 'pDiff'  ,
                    TIMEDIFF( wcm.send_time, wcm.alarm_time )	AS 'sDiff'  ,
                    wcm.obj_name								AS	'oName' ,
                    DATE_FORMAT( wcm.alarm_time , '%H:%i:%s %d.%m.%Y' )	AS	'aTime'	,
                    DATE_FORMAT( wcm.send_time  , '%H:%i:%s %d.%m.%Y' ) AS	'sysTime',
                    DATE_FORMAT( wcm.start_time , '%H:%i:%s %d.%m.%Y' ) AS	'sTime' ,
                    DATE_FORMAT( wcm.end_time   , '%H:%i:%s %d.%m.%Y' ) AS	'eTime' ,
                    DATE_FORMAT( wcm.reason_time, '%H:%i:%s %d.%m.%Y' ) AS	'rTime' ,
                    ( o.reaction_time_difficult * 60 )          AS  'oReact',
                    o.distance                                  AS  'oDistance'
                FROM work_card_movement wcm
                JOIN alarm_reasons ar ON wcm.id_alarm_reasons = ar.id AND ar.is_patrul = 0
                LEFT JOIN objects o ON o.id = wcm.id_object
                WHERE
                    wcm.end_time >= DATE_ADD( NOW(), INTERVAL -".$time_interval." DAY ) AND ";
    $tQuery	.=  $str_where;

    $tQuery	.=  " ORDER BY	aDiffSec DESC ";

    $tResult	=	mysqli_query( $db_sod, $tQuery	) or die( "Error: ".$tQuery );
    $tRows	    =	mysqli_num_rows( $tResult		);

    $strTable = '<tr>
                    <th style="width:70%">Обект</th>
                    <th>Система </th>
                    <th><span data-toggle="tooltip" title="Допустимо закъснение : '.$patrol_Delay.'сек.">Патрул</span> </th>
                    <th>Общо    </th>
                </tr>';

    if( !$tRows ) {

    $strTable .= "<tr>
                <th colspan='4' class='alert alert-success alert-dismissable'>
                    <h5><i class='fa fa-smile-o'></i>
                        За последните ".$time_interval." дни всички аларми са отработени в нормата!
                    </h5>
                </th>
             </tr>";

    }

    for( $c =0; $c < $tRows; $c++ ) {
        $tRow = mysqli_fetch_assoc( $tResult );

        $aDiffSec  = isset( $tRow['aDiffSec' ] ) ? $tRow['aDiffSec'  ] : 0;
        $pDiffSec  = isset( $tRow['pDiffSec' ] ) ? $tRow['pDiffSec'  ] : 0;
        $aDiff  = isset( $tRow['aDiff'    ] ) ? $tRow['aDiff'     ] : 0;
        $pDiff  = isset( $tRow['pDiff'    ] ) ? $tRow['pDiff'     ] : 0;
        $sDiff  = isset( $tRow['sDiff'    ] ) ? $tRow['sDiff'     ] : 0;
        $oName	= isset( $tRow['oName'    ] ) ? $tRow['oName'     ] : '';
        $aTime  = isset( $tRow['aTime'    ] ) ? $tRow['aTime'     ] : 0;
        $sysTime= isset( $tRow['sysTime'  ] ) ? $tRow['sysTime'   ] : 0;
        $sTime  = isset( $tRow['sTime'    ] ) ? $tRow['sTime'     ] : 0;
        $eTime  = isset( $tRow['eTime'    ] ) ? $tRow['eTime'     ] : 0;
        $rTime  = isset( $tRow['rTime'    ] ) ? $tRow['rTime'     ] : 0;
        $oReact = isset( $tRow['oReact'   ] ) ? $tRow['oReact'    ] : 0;
        $oDistance = isset( $tRow['oDistance'   ] ) ? $tRow['oDistance'    ] : 0;

        $strTitle = "&nbsp;&nbsp;&nbsp;&nbsp;Aларма: ".$aTime."
                                        Oповестен: ".$sysTime."&nbsp;
                                        Приемане: ".$sTime."
                                  &nbsp;На обекта: ".$eTime."
                                        --------------------------------------
                                        Дистанция до обекта: ".$oDistance."км. &nbsp;
                                        Норма за реакция: ". $oReact."мин.
                                        Време за реакция: ".$aDiffSec."мин.";
        if( $oReact >= $aDiffSec ) {
            $str_class = "red";
        } else {
            $str_class = "yellow";
        }

        $strTable .= '
              <tr>
                <td><span data-toggle="tooltip" title="'.$strTitle.'"><i class="fa fa-cab text-blue"></i></span> &nbsp; '.mb_substr($oName,0,35,'UTF-8').' </td>
                <td><span class="badge bg-'.$str_class.'">'.$sDiff.'</span></td>
                <td><span class="badge bg-'.$str_class.'">'.$pDiff.'</span></td>
                <td><span data-toggle="tooltip" title="'.$strTitle.'" class="badge bg-'.$str_class.'">'.$aDiff.'</span></td>
              </tr>';

    }
    $strTable .= "<tr><td colspan='4' class='footer'><a>".date ( 'H:i:s d.m.Y' )."</a></td></tr>";
    echo $strTable;

endif;

?>
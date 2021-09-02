<?php

define('INCLUDE_CHECK',true);

require_once( "../config/session.inc.php"	);
require_once( "../config/output_func.php"   );
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
    $today = date('d.m.Y');

    $start_date     = isset( $_GET['start_date'     ] ) ? $_GET['start_date'    ] : mktime(0, 0, 0, date("d")-1, date("m"), date("Y"));
    $end_date       = isset( $_GET['end_date'       ] ) ? $_GET['end_date'      ] : $today;
    $count_alarms   = isset( $_GET['count_alarms'   ] ) ? $_GET['count_alarms'  ] : 1;
    $type_alarms    = isset( $_GET['type_alarms'    ] ) ? $_GET['type_alarms'   ] : 'real';
    $diff_interval  = isset( $_GET['diff_interval'  ] ) ? $_GET['diff_interval' ] : 2;
    $office         = isset( $_GET['office'         ] ) ? $_GET['office'        ] : 0;
    //$buff           = 0; // var for graph counter

    $today_unix   = strtotime( $today     );
    $end_unix     = strtotime( $end_date  );
    $start_dt     = dotted_date_to_datetime( $start_date, 0 );
    $end_dt       = dotted_date_to_datetime( $end_date  , 1 );

    $str_where  = "  wcm.end_time BETWEEN '".$start_dt."' AND '".$end_dt."' ";
    $str_where_s = "  DATE_FORMAT( wcm.end_time, '%Y-%m-%d' ) = ( DATE_FORMAT('".$end_dt."', '%Y-%m-%d') - INTERVAL ";
    $str_where_e = " DAY ) ";

    if( $today_unix != $end_unix ) { $count_alarms = 1; }

    if( $office != 0 ) {
        $str_where .= " AND o.id_office = " . $office . " ";
    }

    if( $type_alarms == 'tech' ) {
        $str_where  .= " AND wcm.alarm_type NOT IN(1,2,3,4) ";
        $str_where_e.= " AND wcm.alarm_type NOT IN(1,2,3,4) ";
    } elseif($type_alarms == 'real') {
        $str_where  .= " AND wcm.alarm_type IN(1,2,3,4) ";
        $str_where_e.= " AND wcm.alarm_type IN(1,2,3,4) ";
    } elseif($type_alarms == 'visited') {
        $str_where  .= " AND UNIX_TIMESTAMP(send_time) > 0 AND ar.is_patrul =  1 ";
        $str_where_e.= " AND UNIX_TIMESTAMP(send_time) > 0 AND ar.is_patrul =  1 ";
    }


//echo $time_interval."<br/>";
    $str_class = '';

    $tQuery	=	"
                SELECT
                    DATE_FORMAT( wcm.alarm_time, '%d.%m %H:%i:%s' ) AS 'aTime'  ,
                    TIMEDIFF( wcm.send_time, wcm.alarm_time )       AS 'tTime'  ,
                    TIMEDIFF( wcm.start_time, wcm.send_time )       AS 'sTime'  ,
                    TIMEDIFF( wcm.end_time, wcm.send_time )         AS 'eTime'  ,
                    TIMEDIFF( wcm.reason_time, wcm.send_time )      AS 'rTime'  ,
                    s.msg_al        AS 'Messg'  ,
                    ar.name         AS 'Reason' , 
                    ar.is_patrul    AS 'iPatrul', 
                    o.distance      AS 'oKM'    , 
                    wcm.obj_name    AS 'oName'  , 
                    wcm.id_object   AS 'oId'    ,
                    ( SELECT CONCAT( p.fname, ' ', p.lname ) FROM personnel.personnel p WHERE p.id = wcm.start_user ) AS 'sPerson',
                    ( SELECT CONCAT( p.fname, ' ', p.lname ) FROM personnel.personnel p WHERE p.id = wcm.end_user   ) AS 'ePerson',
                    ( SELECT CONCAT( p.fname, ' ', p.lname ) FROM personnel.personnel p WHERE p.id = wcm.reason_user ) AS 'rPerson'
                FROM work_card_movement wcm
                LEFT JOIN signals s ON s.id = wcm.alarm_type
                LEFT JOIN objects o ON o.id = wcm.id_object
                LEFT JOIN alarm_reasons ar ON ar.id = wcm.id_alarm_reasons
                WHERE
                    ";
    $tQuery	.=  $str_where;

    $tQuery	.=  " ORDER BY wcm.id DESC";
//echo $tQuery;
    $tResult	=	mysqli_query( $db_sod, $tQuery	) or die( "Error: ".$tQuery );
    $tRows	    =	mysqli_num_rows( $tResult		);


    $strTable = '<tr>
                    <th>Обект</th>
                    <th>Вр.Аларма</th>
                    <th>Изпращане</th>
                    <th>Аларма</th>
                    <th>Тръгване</th>
                    <th>Пристигане</th>
                    <th>Причина</th>
                    <th>Причина</th>
                </tr>';

    for( $c =0; $c < $tRows; $c++ ) {

        $tRow = mysqli_fetch_assoc( $tResult );

        $aTime  = isset( $tRow['aTime'] ) ? $tRow['aTime'] : 0;
        $tTime  = isset( $tRow['tTime'] ) ? $tRow['tTime'] : 0;
        $sTime  = isset( $tRow['sTime'] ) ? $tRow['sTime'] : 0;
        $eTime  = isset( $tRow['eTime'] ) ? $tRow['eTime'] : 0;
        $rTime  = isset( $tRow['rTime'] ) ? $tRow['rTime'] : 0;
        $Messg  = isset( $tRow['Messg' ] ) ? $tRow['Messg' ] : 0;
        $Reason = isset( $tRow['Reason'] ) ? $tRow['Reason'] : 0;
        $iPatrul= isset( $tRow['iPatrul']) ? $tRow['iPatrul']: 0;
        $oName  = isset( $tRow['oName' ] ) ? $tRow['oName' ] : 0;
        $oId    = isset( $tRow['oId'   ] ) ? $tRow['oId'   ] : 0;
        $oKM    = isset( $tRow['oKM'   ] ) ? $tRow['oKM'   ] : 0;
        $sPerson = isset( $tRow['sPerson'] ) ? $tRow['sPerson'] : 0;
        $ePerson = isset( $tRow['ePerson'] ) ? $tRow['ePerson'] : 0;
        $rPerson = isset( $tRow['rPerson'] ) ? $tRow['rPerson'] : 0;

//        if( $aCount >= $count_alarms * 1.5 ) {
//            $str_class = "red";
//        } else {
//            $str_class = "yellow";
//        }

        $object_distance = $oKM * 2;

        list( $send_delay,   $send_seconds   ) = strTimeToTimeSeconds($tTime);
        $send_class = "";
        if( $send_seconds > 60 && $send_seconds < 120 ) { $send_class = "bg-yellow"; }
        elseif( $send_seconds >= 120 ) { $send_class = "bg-red"; }

        list( $start_delay,  $start_seconds  ) = strTimeToTimeSeconds($sTime);
        $start_class = "";
        if( $start_seconds > 15 && $start_seconds <= 30 ) { $start_class = "bg-yellow"; }
        elseif( $start_seconds > 30 ) { $start_class = "bg-red"; }

        list( $object_delay, $object_seconds ) = strTimeToTimeSeconds($eTime);
        list( $reason_delay, $reason_seconds ) = strTimeToTimeSeconds($rTime);

        $row_class  = "bg-red disabled color-palette";
        if( $iPatrul == 1 ) {
            $row_class  = "bg-light-blue disabled color-palette";
            $total_visited   = $total_visited   + 1;
        }
        $object_class = "";
        if( $object_seconds > 300 ) { $object_class = "bg-red"; }


        $strTable .= "<tr>
                        <td class='text-left'>".$oName."</td>
                        <td class='text-center'>".$aTime."                  </td>
                        <td><p class='".$send_class."'>+".$send_delay."</p></td>
                        <td class='text-left'>".$Messg."</td>
                        <td>
                            <span data-toggle='tooltip' title='' class='badge ".$start_class."'  data-original-title='".$sPerson."'>+".$start_delay." </span> 
                            ".mb_substr($sPerson,0,7,'UTF-8')."...
                        </td>
                        <td>
                            <span data-toggle='tooltip' title='' class='badge ".$object_class."'  data-original-title='".$ePerson."'>+".$object_delay." </span>
                            ".mb_substr($ePerson,0,7,'UTF-8')."...
                        </td>
                        <td>
                            <span data-toggle='tooltip' title='' class='badge ".$object_class."'  data-original-title='".$rPerson."'>+".$reason_delay." </span>
                            ".mb_substr($rPerson,0,7,'UTF-8')."...
                        </td>
                        <td class='text-left ".$row_class."'>".mb_substr($Reason,0,12,'UTF-8')."...</td>
                    </tr>";


        $total_distance  = $total_distance  + $object_distance;
        $total_alarms    = $total_alarms    + 1;

    }

    echo '<tr class="bg-light-blue color-palette">
            <th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th>
            <th>КМ по норма:'.$total_distance.' км</th>
            <th>Посетени:'.$total_visited.'</th>
            <th>Общо аларми:'.$total_alarms.'</th>
          </tr>';

    echo $strTable;


//    echo '<script type="text/javascript">
//            $(document).ready(function() {
//                drawDocSparklines();
//            });
//          </script>';

endif;

?>
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
                    GROUP_CONCAT(
                        CONCAT(
                            DATE_FORMAT( wcm.alarm_time, '%d.%m $ %H:%i:%s' ),
                            '$',
                            TIMEDIFF( wcm.send_time, wcm.alarm_time ),
                            '$',
                            TIMEDIFF( wcm.start_time, wcm.send_time ),
                            '$',
                            TIMEDIFF( wcm.end_time, wcm.send_time ),
                            '$',
                            TIMEDIFF( wcm.reason_time, wcm.send_time ),
                            '$',
                            s.msg_al,
                            '$' ,
                            REPLACE( REPLACE( ar.name, ',', ' ' ), '[ ]', '' ),
                            '$' ,
                            ar.is_patrul
                       )
                    ) AS 'oHint' ,
                    o.distance    AS 'oKM'    ,
                    COUNT(wcm.id) AS 'aCount' ,
                    wcm.obj_name  AS 'oName'  ,
                    wcm.id_object AS 'oId'
                FROM work_card_movement wcm
                LEFT outer JOIN signals s ON s.id = wcm.alarm_type
                LEFT outer JOIN objects o ON o.id = wcm.id_object
                LEFT outer JOIN alarm_reasons ar ON ar.id = wcm.id_alarm_reasons
                WHERE
                    ";
    $tQuery	.=  $str_where;

    $tQuery	.=  " GROUP BY wcm.id_object
                HAVING aCount >= $count_alarms
                ORDER BY wcm.id DESC, aCount DESC";

    $tResult	=	mysqli_query( $db_sod, $tQuery	) or die( "Error: ".$tQuery );
    $tRows	    =	mysqli_num_rows( $tResult		);


    $strTable = '<tr>
                    <th style="width:70%">Обект</th>
                    <th>% от всичко</th>
                    <th style="width: 30px"><i class="fa fa-cab"></i></th>
                    <th style="width: 30px" colspan="2">Бр.</th>
                </tr>';

    for( $c =0; $c < $tRows; $c++ ) {

        $tRow = mysqli_fetch_assoc( $tResult );

        $aCount = isset( $tRow['aCount'] ) ? $tRow['aCount'] : 0;
        $oName  = isset( $tRow['oName' ] ) ? $tRow['oName' ] : 0;
        $oHint  = isset( $tRow['oHint' ] ) ? $tRow['oHint' ] : 0;
        $oId    = isset( $tRow['oId'   ] ) ? $tRow['oId'   ] : 0;
        $oKM    = isset( $tRow['oKM'   ] ) ? $tRow['oKM'   ] : 0;
        $tGraph = '';

        if( $aCount >= $count_alarms * 1.5 ) {
            $str_class = "red";
        } else {
            $str_class = "yellow";
        }

        $i      = 0;
        $vAlarms= 0;
        $nAlarms= 0;

        for( $i = 0; $i <= $diff_interval; $i++ ) {

            $iQuery = "SELECT SUM( IF( ar.is_patrul = 1 , 1, 0 ) ) AS Cn,
                              SUM( IF( ar.is_patrul!= 1 , 1, 0 ) ) AS CnNo,
                              DATE_FORMAT( end_time, '%Y-%m-%d' )  AS dd
                        FROM work_card_movement wcm
                        LEFT JOIN alarm_reasons ar ON ar.id = wcm.id_alarm_reasons AND ar.to_arc = 0
                        WHERE
                              id_object = $oId AND
                    ";
            $iQuery	.=  $str_where_s.$i.$str_where_e;

            $iQuery	.=  "LIMIT 1";

            $iResult= mysqli_query( $db_sod, $iQuery );
            while( $iRow = mysqli_fetch_assoc( $iResult ) ) {

                $iCn = isset( $iRow['Cn'  ] ) ? $iRow['Cn'  ] : 0;
                $nCn = isset( $iRow['CnNo'] ) ? $iRow['CnNo'] : 0;
                $tGraph = ','.$iCn.':'.$nCn.$tGraph;

                $vAlarms = $vAlarms + $iCn;
                $nAlarms = $nAlarms + $nCn;
            }

        }

        $object_distance = $oKM * $vAlarms * 2;

        $strModal   = "modal".$oId;
        $oHintArray = explode(",", htmlentities( htmlspecialchars( strip_tags( $oHint ) ) ) );
        $tableModal = "<div class='row text-center table-condensed header '><h5><b>".$oName."</b></h5></div>
                        <div class='row text-center table-condensed'>
                                <div class='col-sm-3 col-md-3'>Аларма</div>
                                <div class='col-sm-1 col-md-1'><p>ИЗПР.</p></div>
                                <div class='col-sm-2 col-md-2 text-left'>Сигнал</div>
                                <div class='col-sm-1 col-md-1'><p>ТРЪГВ.</p></div>
                                <div class='col-sm-1 col-md-1'><p>ПРИСТ.</p></div>
                                <div class='col-sm-1 col-md-4'>ПРИЧИНА</div>
                            </div>";
        $t = 0;

        for( $t = 0; $t < $aCount; $t++ ) {
            $oHintArrayValue = explode( "$" , $oHintArray[$t] );

            list( $send_delay,   $send_seconds   ) = strTimeToTimeSeconds($oHintArrayValue[2]);
            $send_class = "";
            if( $send_seconds > 60 && $send_seconds < 120 ) { $send_class = "text-yellow"; }
            elseif( $send_seconds >= 120 ) { $send_class = "text-red"; }

            list( $start_delay,  $start_seconds  ) = strTimeToTimeSeconds($oHintArrayValue[3]);
            $start_class = "";
            if( $start_seconds > 15 && $start_seconds <= 30 ) { $start_class = "text-yellow"; }
            elseif( $start_seconds > 30 ) { $start_class = "text-red"; }

            list( $object_delay, $object_seconds ) = strTimeToTimeSeconds($oHintArrayValue[4]);
            list( $reason_delay, $reason_seconds ) = strTimeToTimeSeconds($oHintArrayValue[5]);

            $row_class  = "bg-red disabled color-palette";
            if( $oHintArrayValue[8] == 1 ) {
                $row_class  = "bg-light-blue disabled color-palette";
            }
            $object_class = "";
            if( $object_seconds > 300 ) { $object_class = "text-red"; }


            $tableModal .= "<div class='row text-center table-condensed'>
                                <div class='col-sm-3 col-md-3'>".$oHintArrayValue[0]." ".$oHintArrayValue[1]."</div>
                                <div class='col-sm-1 col-md-1'><p class='".$send_class."'>+".$send_delay."</p></div>
                                <div class='col-sm-2 col-md-2 text-left'>".mb_substr($oHintArrayValue[6],0,7,'UTF-8')."...</div>
                                <div class='col-sm-1 col-md-1'><p class='".$start_class."'>+".$start_delay."</p></div>
                                <div class='col-sm-1 col-md-1'><p class='".$object_class."'>+".$object_delay."</p></div>
                                <div class='col-sm-1 col-md-1'>+".$reason_delay."</div>
                                <div class='col-sm-3 col-md-3 text-left ".$row_class."'>".mb_substr($oHintArrayValue[7],0,12,'UTF-8')."...</div>
                            </div>";
        }

        echo '<div class="modal fade" id="'.$strModal.'" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body table-responsive">'.$tableModal.'</div>
                        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"> X </button>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>';

        $strTable .= '
               <tr>
                <td title="'.$oHint.'">
                    <a onclick="$(\'#'.$strModal.'\').appendTo(\'body\');"       data-toggle="modal" href="#" data-target="#'.$strModal.'"       class="head">&nbsp;&nbsp;<i class="fa fa-home fa-lg"></i> &nbsp; '.$oName.' </a></td>
                <td>
                     <p><span class="sparkbar">'.$tGraph.'</span></p>
                </td>
                <td><span class="badge bg-green" title="'.$oHint.'">'.$object_distance.' км</span></td>
                <td><span class="badge bg-blue" title="'.$oHint.'">'.$vAlarms.'</span></td>
                <td><span class="badge bg-'.$str_class.'" title="'.$oHint.'">'.$nAlarms.'</span></td>
              </tr>';

        $total_distance  = $total_distance  + $object_distance;
        $total_visited   = $total_visited   + $vAlarms;
        $total_alarms    = $total_alarms    + $nAlarms;

    }

    echo '<tr class="bg-light-blue color-palette">
            <th colspan="2"></th>
            <th>'.$total_distance.' км</th>
            <th>'.$total_visited.'</th>
            <th>'.$total_alarms.'</th>
          </tr>';

    echo $strTable;


//    echo '<script type="text/javascript">
//            $(document).ready(function() {
//                drawDocSparklines();
//            });
//          </script>';
    echo '<img src="dist/img/1px.png" onload="drawDocSparklines();"/>';

endif;

?>
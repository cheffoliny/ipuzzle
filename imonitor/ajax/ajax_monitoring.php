<?php

define('INCLUDE_CHECK',true);

require_once( "../config/session.inc.php"	);
require_once( "../config/connect.inc.php"	);
require_once( "../config/output_func.php"   );
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

    $oNum   = isset( $_GET['num' ] ) ? $_GET['num' ] : 0;
    $play   = isset( $_GET['play'] ) ? $_GET['play'] : 0;
    $test11     = isset( $_GET['test11'] ) ? $_GET['test11'] : 0;
    $test14     = isset( $_GET['test14'] ) ? $_GET['test14'] : 0;
    $receiver   = isset( $_GET['receiver'] ) ? $_GET['receiver'] : 0;

    $tQuery	=	"
                    SELECT
                            o.id                    AS 'oId'        ,
                            o.num                   AS 'oNum'       ,
                            o.name      	        AS 'oName'      ,
                            a.msg					AS	'Msg'       ,
                            a.pass					AS	'mPass'     ,
                            ( a.alarm + 0 )			AS	'mAlarm'    ,
                            CONCAT( a.status, ' / P:', m.part, ' /UZ: ', m.zone )				AS	'mStatus'   ,
                            ( s.play_alarm + 0 )    AS  'sPlay'     ,
                            s.ico                   AS  'sIco'      ,
                            s.id                    AS  'sId'       ,
                            DATE_FORMAT( a.msg_time, '%H:%i:%s %d.%m.%Y' )					AS  'mTime',
                            TIMEDIFF( a.response, a.msg_time )                              AS  'dTime'

                    FROM archiv_". $strCurrentMonth ." a
                    LEFT JOIN messages m ON m.id = a.id_msg
                    LEFT JOIN objects  o ON o.id = m.id_obj
                    LEFT JOIN signals  s ON s.id = m.id_sig

                    WHERE
                        1 AND o.id_status != 4 ";
    if( $test11 <> 0 ) {
        $tQuery .= "     AND CASE WHEN m.id_sig IN(11) THEN a.alarm = 1 ELSE a.alarm IN(0,1) END ";
    }
    if( $test14 <> 0 ) {
        $tQuery .= "     AND CASE WHEN m.id_sig IN(14) THEN a.alarm = 1 ELSE a.alarm IN(0,1) END ";
    }
    if( $oNum <> 0 ) {
        $tQuery	.=	"     AND o.num = '".$oNum."' ";
    }
    if( $play <> 0 ) {
        $tQuery	.=	"     AND s.play_alarm = '".$play."' ";
    }
    if( $receiver <> 0 ) {
        $tQuery	.=	"     AND a.id_receiver = ".$receiver." ";
    }


    $tQuery	.=	"
                    ORDER BY a.id DESC
                    LIMIT 110";

    $tResult	=	mysqli_query( $db_sod, $tQuery	) or die( "Error: ".$tQuery );
    $tRows	    =	mysqli_num_rows( $tResult		);

    if( !$tRows ) {
        echo "<li style='text-align: center;'>
                    <small class='badge bg-green'>
                        &nbsp; <i class='fa fa-smile-o'></i>
                        &nbsp; Няма сигнали! Вероятна причина е смяната на месец...
                        &nbsp;
                    </small>
                  </li>";
    }

    while( $tRow = mysqli_fetch_assoc( $tResult ) ) {

        $oID    = isset( $tRow['оId'    ] ) ? $tRow['оId'	 ] : 0;
        $sID    = isset( $tRow['sId'    ] ) ? $tRow['sId'	 ] : 0;
        $mTime  = isset( $tRow['mTime'  ] ) ? $tRow['mTime'  ] : 0;
        $dTime  = isset( $tRow['dTime'  ] ) ? $tRow['dTime'  ] : 0;
        $mPass  = isset( $tRow['mPass'  ] ) ? $tRow['mPass'  ] : 0;
        $mAlarm = isset( $tRow['mAlarm' ] ) ? $tRow['mAlarm' ] : 0;
        $mStatus= isset( $tRow['mStatus'] ) ? $tRow['mStatus'] : 0;
        $sPlay  = isset( $tRow['sPlay'  ] ) ? $tRow['sPlay'  ] : 0;
        $mMsg   = isset( $tRow['Msg'    ] ) ? $tRow['Msg'    ] : 0;
        $sIco   = isset( $tRow['sIco'   ] ) ? $tRow['sIco'   ] : 0;
        $oName	= $tRow['oNum'	]." - ". $tRow['oName'	] ;

        list( $delay_time,   $delay_seconds   ) = strTimeToTimeSeconds($dTime);

        $strColor   = "label-default";
        $strBgColor = "";

        if( $sPlay == 1 && $mAlarm == 1 ){
//            $mIco = "<i class='".$sIco."' style='color: #f0ad4e;'></i>";
            $strColor   = "label-warning";
            $strBgColor = "background: #f1e7bc";
        } else if(  $sPlay == 2 && $mAlarm == 1 ) {
//            $mIco = "<i class='".$sIco."' style='color: red'></i>";
            $strColor   = "label-danger";
            $strBgColor = "background: #dFb5b4";
        } else {
//            $mIco = "<i class='".$sIco."'></i>";
        }

        echo "<tr style='$strBgColor;'>
                <td>
                    <a style='overflow-x: hidden; white-space: nowrap;'>". $oName ."</a>
                </td>
                <td>
                    <a style='overflow-x: hidden; white-space: nowrap;'>".mb_substr($mTime,9,10,'UTF-8') ." &nbsp;&nbsp; ".mb_substr($mTime,0,8,'UTF-8') ." (+".$delay_time.")</a>
                </td>
                <td>
                    <i class='".$sIco."'></i>
                </td>
                <td>
                    <small class='label $strColor pull-left' style='margin-top: 3px;'> ". $mStatus ."</small>
                </td>
                <td>
                    &nbsp; ". $mMsg ."       
                </td>
                <td>
                    <small class='label $strColor pull-right' style='margin-top: 3px;'><i class='fa fa-signal'></i> &nbsp;". $mPass ." </small>
                </td>                                    
                
                    
                    
              </tr>";
    }

endif;

?>
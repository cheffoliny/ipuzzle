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

    $idSignals   = isset( $_GET['vID'    ] ) ? $_GET['vID'   ] : 0;
    $strSigFlag  = isset( $_GET['sFlag'  ] ) ? $_GET['sFlag' ] : 0;

    $BR = 1;
    $c = 0;
    $strMonth   = date('Ym');
    $strColor   = "label-primary";


    if( $strSigFlag == 0 ) {

        $rQuery = "
                    SELECT
                        COUNT(a.id)     AS 'BR'    ,
                        o.id            AS 'oId'   ,
                        o.num           AS 'oNum'  ,
                        o.name          AS 'oName' ,
                        MAX(a.msg_time) AS 'mTime',
                        s.ico           AS 'sIco'
                    FROM archiv_" . $strMonth . " a
                    LEFT JOIN messages m ON m.id = a.id_msg AND m.to_arc = 0
                    LEFT JOIN objects  o ON o.id = m.id_obj AND o.id_status != 4
                    LEFT JOIN signals s ON s.id = m.id_sig
                    WHERE 
                        1 AND o.id_status != 4 AND s.id IN(" . $idSignals . ") AND a.alarm = 1
                    GROUP BY o.id
                    HAVING BR > " . $BR . " 
                    
                    ORDER BY BR DESC LIMIT 110 ";

        $rResult = mysqli_query($db_sod, $rQuery) or die("Error: " . $rQuery);
        $rRows = mysqli_num_rows($rResult);

        if (!$rRows) {
            echo "<li class='callout callout-success'>
                    <h5><i class='fa fa-smile-o'></i> Няма намерени обекти с избрания вид сигнал!</h5>
                  </li>";
        }


        while ($rRow = mysqli_fetch_assoc($rResult)) {

            $oID = isset($rRow['оId']) ? $rRow['оId'] : 0;
            $mTime = isset($rRow['mTime']) ? $rRow['mTime'] : 0;
            $cBR = isset($rRow['BR']) ? $rRow['BR'] : 0;
            $oName = $rRow['oNum'] . " - " . $rRow['oName'];
            $sIco  = isset( $rRow['sIco'  ] ) ? $rRow['sIco'  ] : 0;

            if( $c == 0 ) {
                echo "<li class='text-right'><i class='".$sIco." fa-lg'></i></li>";
            }
            echo "<li>
                    " . $oName . "
                    <span class='handle' style='float: right;'>
                        <small class='label $strColor'> <i class='fa fa-signal'></i> &nbsp; " . $cBR . " </small>
                    </span>
                </li>";
            $c++;
        }

    } else {


        $rQuery = "
                    SELECT
                        COUNT(a.id)     AS 'BR'   ,
                        DATE_FORMAT(a.msg_time, '%d.%m.%Y') AS  'mTime',
                        s.ico           AS 'sIco'
                    FROM archiv_" . $strMonth . " a
                    LEFT JOIN messages m ON m.id = a.id_msg AND m.to_arc = 0
                    LEFT JOIN objects  o ON o.id = m.id_obj AND o.id_status != 4
                    LEFT JOIN signals s ON s.id = m.id_sig
                    WHERE 
                        1 AND o.id_status != 4 AND s.id IN(" . $idSignals . ") AND a.alarm = 1
                    GROUP BY day(a.msg_time)
                    HAVING BR > " . $BR . " 
                    
                    ORDER BY mTime ASC ";

        $rResult = mysqli_query($db_sod, $rQuery) or die("Error: " . $rQuery);
        $rRows = mysqli_num_rows($rResult);

        if (!$rRows) {
            echo "<li class='callout callout-success'>
                    <h5><i class='fa fa-smile-o'></i> Избрания вид сигнал не присъства в архива!</h5>
                  </li>";
        }


        while ($rRow = mysqli_fetch_assoc($rResult)) {

            $mTime = isset($rRow['mTime']) ? $rRow['mTime'] : 0;
            $cBR   = isset($rRow['BR']   ) ? $rRow['BR'   ] : 0;
            $sIco  = isset( $rRow['sIco'  ] ) ? $rRow['sIco'  ] : 0;

            if( $c == 0 ) {
                echo "<li class='text-right'><i class='".$sIco." fa-lg'></i></li>";
            }
            echo "<li>
                    " . $mTime . "
                    <span class='handle' style='float: right;'>
                        <small class='label $strColor'> <i class='fa fa-signal'></i> &nbsp; " . $cBR . " </small>
                    </span>
                </li>";
            $c++;
        }


    }

    echo "<li><a><i class='fa fa-clock-o'></i> ".date ( 'H:i:s d.m.Y' )."</a></li>";
endif;

?>

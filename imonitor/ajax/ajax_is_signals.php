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
    $idSignals   = isset( $_GET['vID'    ] ) ? $_GET['vID'   ] : '1,2,3,4';
    $strSigFlag  = isset( $_GET['sFlag'  ] ) ? $_GET['sFlag' ] : 0;

    $tQuery	=	"
                        SELECT
                            o.id          AS 'oId' ,
                            o.num         AS 'oNum' ,
                            o.name        AS 'oName' ,
                            ( s.play_alarm + 0 ) AS 'sPlay' ,
                            m.time_al     AS 'mTime',
                            m.flag        AS 'mAlarm',
                            s.ico         AS 'sIco',
                            ( SELECT GROUP_CONCAT(' $$ ', ico,' ## ') FROM signals WHERE id IN(".$idSignals.") ) AS strIcons
                        FROM messages m
                        LEFT JOIN objects o ON o.id = m.id_obj
                        LEFT JOIN offices off ON off.id = o.id_office AND off.to_arc = 0
                        LEFT JOIN signals s ON s.id = m.id_sig
                        WHERE
                        1 AND o.id_status != 4 AND m.to_arc = 0 AND off.id_firm = 1 AND s.id IN(".$idSignals.") ";
    if( $strSigFlag != 0 ) {
        $tQuery	.=	" AND m.flag = 1 ";
    }
    $tQuery	.=	"
                        GROUP BY o.id
                        ORDER BY o.num DESC LIMIT 110";

    $tResult	=	mysqli_query( $db_sod, $tQuery	) or die( "Error: ".$tQuery );
    $tRows	    =	mysqli_num_rows( $tResult		);

    if( !$tRows ) {
        echo "<li class='callout callout-success'>
                <h5><i class='fa fa-smile-o'></i> Няма намерени обекти с избрания вид сигнал!</h5>
              </li>";
    }

    while( $tRow = mysqli_fetch_assoc( $tResult ) ) {

        $oID    = isset( $tRow['оId'    ] ) ? $tRow['оId'	 ] : 0;
        $sIco   = isset( $tRow['sIco'   ] ) ? $tRow['sIco'   ] : 0;
        $mTime  = isset( $tRow['mTime'  ] ) ? $tRow['mTime'  ] : 0;
        $sPlay  = isset( $tRow['sPlay'  ] ) ? $tRow['sPlay'  ] : 0;
        $mAlarm  = isset( $tRow['mAlarm'  ] ) ? $tRow['mAlarm'  ] : 0;
        $strIcons  = isset( $tRow['strIcons'  ] ) ? $tRow['strIcons'  ] : 0;
        $oName	= $tRow['oNum'	]." - ". $tRow['oName'	] ;

        $strIcons = str_replace(" $$ ","<i class='",$strIcons);
        $strIcons = str_replace(" ## "," fa-lg' ></i>",$strIcons);
        $strIcons = str_replace(","," &nbsp; ",$strIcons);

        $strColor   = "label-primary";
        $strBgColor = "";

        if( $sPlay == 1 && $mAlarm == 1 ){
            $mIco = "<i class='".$sIco."' style='color: #f0ad4e;'></i>";
            $strColor   = "label-warning";
            $strBgColor = "background: #f1e7bc";
        } else if(  $sPlay == 2 && $mAlarm == 1 ) {
            $mIco = "<i class='".$sIco."' style='color: red'></i>";
            $strColor   = "label-danger";
            $strBgColor = "background: #dFb5b4";
        } else {
            $mIco = "<i class='".$sIco."'></i>";
        }

        if( $c == 0 ) {
            echo "<li class='col-sm-12 col-xs-12 text-right' style='display: inline-block;'><a>".$strIcons."</a></li>";
        }

        echo "<li class='col-sm-12 col-xs-12' style='$strBgColor; display: inline-block;'>
                <a class='col-sm-2 col-xs-4' style='overflow-x: hidden; white-space: nowrap;'> &nbsp; ".$mIco." &nbsp; </a>
                <span class='col-sm-4 col-xs-8' style='overflow-x: hidden; float: right;'>

                     <small class='label $strColor pull-left' style='margin-top: 3px;'> ". $mStatus ."</small>
                    &nbsp; ". $mTime ."
                    <small class='label $strColor pull-right' style='margin-top: 3px;'><i class='fa fa-signal'></i> &nbsp;". $mPass ." </small>
                </span>
                <a class='col-sm-5 col-xs-12' style='overflow-x: hidden; white-space: nowrap;'>". $oName ."</a>
            </li>";
        $c++;
    }

    echo "<li><a><i class='fa fa-clock-o'></i> ".date ( 'H:i:s d.m.Y' )."</a></li>";

endif;

?>
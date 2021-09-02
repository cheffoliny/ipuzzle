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

    $sID  = isset( $_GET['sID']) ? $_GET['sID']: 0;
    if( $sID != 0 ) {
        $sQuery	 = "UPDATE work_card_movement SET reason_time = NOW(), id_alarm_reasons = 11, send_time = '0000-00-00 00:00:00', note = 'monitoring' WHERE alarm_type = '".$sID."' AND id_alarm_reasons = 0 AND start_time = '0000-00-00 00:00:00' ";
        $sResult =	mysqli_query( $db_sod, $sQuery ) OR die( "".$sQuery );
        $sID = 0;
    }

    $tQuery	=	"
                SELECT
                        s.id            AS 'sID'  ,
                        DATE_ADD( NOW(), INTERVAL -24 HOUR),
                        s.msg_al	    AS 'sName',
                        s.ico   	    AS 'sIco' ,
                        COUNT( wcm.id ) AS 'aCount',
                        IF( wcm.start_time > 0, 1, 0 ) AS 'rCount'
                FROM work_card_movement wcm
                JOIN signals s ON s.id = wcm.alarm_type 
                WHERE
                    wcm.id_alarm_reasons = 0 AND wcm.alarm_time > DATE_ADD( NOW(), INTERVAL -24 HOUR)
                
                GROUP BY s.id";

    $tResult	=	mysqli_query( $db_sod, $tQuery	) or die( "Error: ".$tQuery );
    $tRows	    =	mysqli_num_rows( $tResult		);

    $strContent = '';
    $aC = 0;
    $t  = 0;

    for( $t = 0; $t < $tRows; $t++ ) {

        $tRow = mysqli_fetch_assoc( $tResult );

        $sID	        = isset( $tRow['sID']   ) ? $tRow['sID']    : 0;
        $sName	        = isset( $tRow['sName'] ) ? $tRow['sName']  : '';
        $sIco	        = isset( $tRow['sIco']  ) ? $tRow['sIco']   : '';
        $aCount	        = isset( $tRow['aCount']) ? $tRow['aCount'] : 0;
        $rCount	        = isset( $tRow['rCount']) ? $tRow['rCount'] : 0;

        if( $aCount == $rCount ){
            $strColor   = "btn btn-warning";
        } else {
            $strColor = "btn btn-danger";
        }

        $strContent .=
            '<li class="br-gray">
                <a href="#myModal" data-toggle="modal" data-code="'.$sID.'" data-signal="'.$sName.'" class="name">
                    <div class="pull-left">
                        <button class="'.$strColor.'"><i class="'.$sIco.'" style="color: #fff !important;"></i></button>
                    </div>
                    <h5>
                        &nbsp;&nbsp; '.$sName.'
                        <span class="pull-right">
                            <small class="label label-danger">'.$aCount.'</small> /
                            <small class="badge label-warning">'.$rCount.'</small>
                        </span>
                    </h5>
               </a>
            </li>';

        $aC = $aC + $aCount;
    }

    echo '          
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
             <i class="fa fa-bell-o"></i>
             <span class="label label-danger">'.$aC.'</span>
          </a>
          <ul class="dropdown-menu">
            <li class="header alert-danger">'.$aC.' активни аларми </li>
            <li>
                <ul class="menu">
                    '.$strContent.'
                </ul>
            </li>
            <li class="footer"><a href="#">'.date ( 'H:i:s d.m.Y' ).'</a></li>
          </ul>';

endif;

?>
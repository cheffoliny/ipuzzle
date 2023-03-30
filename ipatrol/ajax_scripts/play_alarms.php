<?php

define('INCLUDE_CHECK',true);

require_once( "../config/connect.php"	    );
require_once( "../config/session.inc.php"	);
require_once( "../include/functions.php"    );

if($_SESSION['id']):
	// alarm_time- време на алармата;
    // send_time    - време на подаване на алармата;
	// start_time   - време на приемане на алармата;
	// end_time     - време за пристигане на обекта;
	// reason_time  - време за позочване на причина;

	ob_start();
	
	function handle_drop($errno, $errstr, $errfile, $errline){
		if( $errno == E_WARNING ){
			echo 'Проблем с връзката - play! Проверете всички връзки и опитайте да обновите страницата!';
			$ob = ob_get_clean();
			header("HTTP/1.0 500 Internal server error");
			echo $ob;
				
		}
	}

	set_error_handler('handle_drop');

	//if(!defined('INCLUDE_CHECK')) die('You are not allowed to execute this file directly');
    if( isset( $_GET['do']) && $_GET['do'] == 'stop'   ) {
        update_alert_time( $_SESSION['uid'] );
        $play   = 0;
        $player_show = "";
    }
    $play	= isset( $_GET['play'] ) ? $_GET['play'] : 0;
    if( $play == 0 ) $player_show = "";
    else $player_show = "<audio id='player' autoplay loop ><source src='beep.mp3'></audio>";

    $nQuery	=	"SELECT id FROM work_card_movement wcm WHERE send_time >= ( SELECT alert_time FROM work_card_person_alert WHERE id_person = ". $_SESSION['id'] ." ) AND start_time = '0000-00-00 00:00:00' LIMIT 1";
    $nResult	=	mysqli_query( $db_sod, $nQuery 	) or die( "Error: play" );
    $num_aRows	=	mysqli_num_rows( $nResult		);


    if( !$num_aRows ) { // It's not alarms to play
        if( $play == 1 ) {
            echo    "<img src='images/onload.jpg' id='play_stop' onload=\"IntelliSOD.stopSound();\" style='width: 0px; height: 0px;' />";
            $player_show = "";
        }
        echo        "<input type='hidden' id='play' name='play' value='0' />";
    }

    while( $nRow = mysqli_fetch_assoc( $nResult ) ) {

        if( $play == 0 ) {
            echo    "<img src='images/onload.jpg' id='play_start' onload=\"IntelliSOD.playSound('beep',true);\" style='width: 0px; height: 0px;' />";
            $player_show = "<audio id='player' autoplay loop ><source src='beep.mp3'></audio>";
        }
        echo        "<input type='hidden' id='play' name='play' value='1' />";
        echo        "<li class='btn btn-xs btn-danger' id='play_stop' onclick=\"IntelliSOD.stopSound(); loadXMLDoc('./ajax_scripts/play_alarms.php?do=stop&play=0', 'play_alarms', 'stop'); return false;\">
                        <br />СПРИ ЗВУКА<br />
                     </li>";
        echo $player_show;

    }


endif;

?>
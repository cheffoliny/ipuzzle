<?php

define('INCLUDE_CHECK',true);

require_once( "../config/session.inc.php"	);
require_once( "../config/connect.inc.php"	);
require_once( "../config/output_func.php"	);

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
        $sQuery	 = "UPDATE signals SET
                      play_alarm = CASE
                                    WHEN play_alarm = 1 THEN 2
                                    WHEN play_alarm = 2 THEN 1
                                   END
                        WHERE  id = ". $sID." ";
        $sResult =	mysqli_query( $db_sod, $sQuery ) OR die( "".$sQuery );
        $sID = 0;
    }

    get_signals(0,1);

endif;

?>
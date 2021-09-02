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

    $tQuery	=	"
                SELECT
						o.id              AS 'oID'  ,
						o.num             AS 'oNum' ,
						o.name            AS 'oName',
						m.msg_rest        AS 'mRest',
						o.work_time_alert AS 'wTime'
				FROM objects o
				JOIN messages m ON m.id_obj = o.id AND m.to_arc = 0 AND m.flag = 1 AND m.id_cid BETWEEN 400 AND 500
				WHERE
                    o.id_status IN( 1, 14 )
					AND CONCAT( DATE_FORMAT(NOW(),'%Y-%m-%d '), o.work_time_alert ) < NOW()
					AND o.work_time_alert != '00:00:00'
				GROUP BY o.id";

    $tResult	=	mysqli_query( $db_sod, $tQuery	) or die( "Error: ".$tQuery );
    $tRows	    =	mysqli_num_rows( $tResult		);


        echo '<a href="#" class="dropdown-toggle" data-toggle="dropdown">
                 <i class="fa fa-unlock"></i>
                 <span class="label label-danger">'.$tRows.'</span>
              </a>
              <ul class="dropdown-menu">
                <li class="header"><a><span class="badge bg-red">'.$tRows.'</span> Отворени обекти </a></li>
                <li>
                    <ul class="menu">';

    while( $tRow = mysqli_fetch_assoc( $tResult ) ) {

        $oID	        = isset( $tRow['oID'  ] ) ? $tRow['oID' ] : 0 ;
        $oNum	        = isset( $tRow['oNum' ] ) ? $tRow['oNum' ] : 0 ;
        $oName	        = isset( $tRow['oName'] ) ? $tRow['oName'] : '';
        $mRest	        = isset( $tRow['mRest']) ? $tRow['mRest']: '';
        $wTime	        = isset( $tRow['wTime']) ? $tRow['wTime']: '';

        $sName	        = $tRow['oNum'	]." - ". $tRow['oName'	] ;
        $shortName      = mb_substr( $sName, 0, 30, 'utf-8' );

        echo '
            <li title="'.$oNum.' - '.$oName.'"><!-- Task item -->
                <a href="#" class="name">
                    <h5 class="text-black">
                        '.$shortName.'
                    </h5>
                    <h5>
                        <small class="pull-left text-light-blue"> '.$mRest.' </small>
                        <small class="pull-right text-red"><i class="fa fa-clock-o"></i> '.$wTime.'</small>
                    </h5>
                </a>
            </li>
        ';
    }

    echo '    </ul>
            </li>
            <li class="footer"><a href="#">'.date ( 'H:i:s d.m.Y' ).'</a></li>
          </ul>';

endif;

?>
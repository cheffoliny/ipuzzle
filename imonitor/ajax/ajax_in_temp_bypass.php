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
                        o.num       AS oNum   ,
                        o.name      AS oName  ,
                        m.time_al	AS mTime  ,
                        m.flag      AS mFlag  ,
                        DATE_FORMAT( m.time_al, '%H:%i %d.%m.%Y' ) AS sTime,
                        CONCAT( p.fname, ' ', p.lname ) AS sUser
                  FROM objects o
                  JOIN messages m ON m.id_obj = o.id AND m.to_arc = 0 AND m.id_sig = 104
                  LEFT JOIN personnel.personnel p ON p.id = m.updated_user
                  WHERE
                        o.service_status = 0 AND o.id_status != 4
                  ORDER BY m.time_al DESC";

    $tResult	=	mysqli_query( $db_sod, $tQuery	) or die( "Error: ".$tQuery );
    $tRows	    =	mysqli_num_rows( $tResult		);


        echo '<a href="#" class="dropdown-toggle" data-toggle="dropdown">
                 <i class="fa fa-ban"></i>
                 <span class="label label-danger">'.$tRows.'</span>
              </a>
              <ul class="dropdown-menu">
                <li class="header"><a><span class="badge bg-red">'.$tRows.'</span> обекта с байпас</a></li>
                <li>
                    <ul class="menu">';

    while( $tRow = mysqli_fetch_assoc( $tResult ) ) {

        $sTime  = isset( $tRow['sTime'  ] ) ? $tRow['sTime'	 ] : 0;
        $sUser   = isset( $tRow['sUser' ] ) ? $tRow['sUser'  ] : 0;
        $mFlag  = isset( $tRow['mFlag'  ] ) ? $tRow['mFlag'  ] : 0;
        $mTime  = isset( $tRow['mTime'  ] ) ? $tRow['mTime'  ] : 0;
        $oName	= $tRow['oNum'	]." ". $tRow['oName'	] ;

        if( $mFlag <> 0 ) { $strFlag = '<i class="fa fa-flag text-red"></i>'; }
        else { $strFlag = '<i class="fa fa-flag-o"></i>'; }
        echo '
            <li><!-- Task item -->
                <a href="#" class="name">

                    <h6>
                        '.$oName.'
                    </h6>
                    <h5>
                        <small class="pull-left text-light-blue">'.$strFlag.' '.$sUser.'</small>
                        <small class="pull-right text-red"><i class="fa fa-clock-o"></i> '.$mTime.'</small>
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
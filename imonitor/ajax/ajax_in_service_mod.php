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
                        o.num                     AS oNum ,
                        o.name                    AS oName,
                        o.service_status          AS sStat,
                        o.set_service_status_user AS uId  ,
                        DATE_FORMAT( o.service_status_time, '%H:%i %d.%m.%Y' )     AS sTime,
                        CONCAT( p.fname, ' ', p.lname ) AS sUser,
                        ( SELECT COUNT(*) FROM objects WHERE id_status IN(1) AND id_office IN(66,67) AND is_sod =1 ) AS oCount
                  FROM objects o
                  LEFT JOIN personnel.personnel p ON p.id = o.set_service_status_user
                  WHERE
                        o.service_status = 1 AND o.id_status != 4
                  ORDER BY o.service_status_time ASC LIMIT 20";

    $tResult	=	mysqli_query( $db_sod, $tQuery	) or die( "Error: ".$tQuery );
    $tRows	    =	mysqli_num_rows( $tResult		);
    $c = 1;

        echo '<a href="#" class="dropdown-toggle" data-toggle="dropdown">
                 <i class="fa fa-wrench"></i>
                 <span class="label label-danger">'.$tRows.'</span>
              </a>
              <ul class="dropdown-menu">
                <li class="header bg-light-blue"><span class="badge bg-red">'.$tRows.'</span> обекта в сервизен</li>
                <li>
                    <ul class="menu">';

    while( $tRow = mysqli_fetch_assoc( $tResult ) ) {

        $uId    = isset( $tRow['uId'    ] ) ? $tRow['uId'	 ] : 0;
        $sUser  = isset( $tRow['sUser'  ] ) ? $tRow['sUser'  ] : 0;
        $sStat  = isset( $tRow['sStat'  ] ) ? $tRow['sStat'  ] : 0;
        $sTime  = isset( $tRow['sTime'  ] ) ? $tRow['sTime'  ] : 0;
        $oCount = isset( $tRow['oCount' ] ) ? $tRow['oCount' ] : 0;
        $oName	= $tRow['oNum'	]." ". $tRow['oName'	] ;

        if( $c == 0 )
        echo '  <li class="header text-blue text-center"><small>'.$oCount.' охранявани обекта</small></li>';

        echo '  <li class="br-gray"><!-- Task item -->
                    <a href="#" class="name">
                        <div class="pull-left">
                            <button class="btn btn-warning"><i class="fa fa-home"></i></button>
                        </div>
                        <h4>
                            '.$oName.'
                        </h4>
                        <h5>
                            <small class="pull-left text-light-blue"> &nbsp;&nbsp;&nbsp;&nbsp; '.$sUser.'</small>
                            <small class="pull-right text-red"><i class="fa fa-clock-o"></i> '.$sTime.'</small>
                        </h5>
                    </a>
                </li>';
            $c = $c++;
    //    $strColor   = "label-primary";
    //    $strBgColor = "";
    //
    //    if( $sPlay == 1 && $mAlarm == 1 ){
    //        $mIco = "<i class='".$sIco."' style='color: #f0ad4e;'></i>";
    //        $strColor   = "label-warning";
    //        $strBgColor = "background: #f1e7bc";
    //    } else if(  $sPlay == 2 && $mAlarm == 1 ) {
    //        $mIco = "<i class='".$sIco."' style='color: red'></i>";
    //        $strColor   = "label-danger";
    //        $strBgColor = "background: #dFb5b4";
    //    } else {
    //        $mIco = "<i class='".$sIco."'></i>";
    //    }

    //    echo "<li class='col-sm-12' style='$strBgColor; display: inline-block;'>
    //            <a class='col-sm-2 col-xs-4' style='overflow-x: hidden; white-space: nowrap;'> &nbsp; ".$mIco." &nbsp; </a>
    //            <span class='col-sm-4 col-xs-8' style='overflow-x: hidden; float: right;'>
    //
    //                 <small class='label $strColor pull-left' style='margin-top: 3px;'> ". $mStatus ."</small>
    //                &nbsp; ". $mTime ."
    //                <small class='label $strColor pull-right' style='margin-top: 3px;'><i class='fa fa-signal'></i> &nbsp;". $mPass ." </small>
    //            </span>
    //            <a class='col-sm-5 col-xs-12' style='overflow-x: hidden; white-space: nowrap;'>". $oName ."</a>
    //        </li>";
    }

    echo '    </ul>
            </li>
            <li class="footer"><a href="#">'.date ( 'H:i:s d.m.Y' ).'</a></li>
          </ul>';

endif;

?>
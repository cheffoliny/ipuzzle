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

    $sig   = isset( $_GET['sig'] ) ? $_GET['sig'] : 0;

    if( $sig == 301 ) {
        $str_m_m1 ="  ( TIME_TO_SEC(TIMEDIFF(NOW(),m.time_al)) + 0 )  AS 'tSec',
                      TIMEDIFF(NOW(),m.time_al)                       AS 'tDiff',
                      DATE_FORMAT( m.time_al, '%H:%i %d.%m.%Y' )      AS 'mTime' ";
        $str_where =" LEFT JOIN messages m ON ( m.id_obj = o.id AND ( m.id_sig IN(7,22) OR m.id_cid IN(301,314,309) ) AND m.to_arc = 0 AND m.flag =  1) ";
        $str_having=" HAVING ( tSec IS NOT NULL ) ";
    } elseif ( $sig == 302 ){
        $str_m_m1 = " ( TIME_TO_SEC(TIMEDIFF(NOW(),m1.time_al)) + 0 ) AS 'bSec',
                      TIMEDIFF(NOW(),m1.time_al)                      AS 'bDiff',
                      DATE_FORMAT( m1.time_al, '%H:%i %d.%m.%Y' )     AS 'bTime' ";
        $str_where= " LEFT JOIN messages m1 ON ( m1.id_obj = o.id AND ( m1.id_sig IN(8) OR m1.id_cid IN(302,311,315) ) AND m1.to_arc = 0 AND m1.flag = 1 )  ";
        $str_having=" HAVING ( bSec IS NOT NULL ) ";
    } else {
        $str_m_m1 = " ( TIME_TO_SEC(TIMEDIFF(NOW(),m.time_al)) + 0 )  AS 'tSec',
                      TIMEDIFF(NOW(),m.time_al)                       AS 'tDiff',
                      DATE_FORMAT( m.time_al, '%H:%i %d.%m.%Y' )      AS 'mTime',
                      ( TIME_TO_SEC(TIMEDIFF(NOW(),m1.time_al)) + 0 ) AS 'bSec',
                      TIMEDIFF(NOW(),m1.time_al)                      AS 'bDiff',
                      DATE_FORMAT( m1.time_al, '%H:%i %d.%m.%Y' )     AS 'bTime' ";
        $str_where= " LEFT JOIN messages m ON ( m.id_obj = o.id AND ( m.id_sig IN(7,22) OR m.id_cid IN(301,314,309) ) AND m.to_arc = 0 AND m.flag =  1)
                      LEFT JOIN messages m1 ON ( m1.id_obj = o.id AND ( m1.id_sig IN(8) OR m1.id_cid IN(302,311,315) ) AND m1.to_arc = 0 AND m1.flag = 1 )  ";
        $str_having=" HAVING ( tSec IS NOT NULL OR bSec IS NOT NULL ) ";
    }

    $tQuery	=	"
                SELECT
                      $str_m_m1                          ,
                      o.id                    AS 'oId'  ,
                      o.num                   AS 'oNum' ,
                      o.name      	          AS 'oName'
                FROM objects o ";

    $tQuery	.=  $str_where;

    $tQuery	.=" WHERE	1
                        AND o.id_status != 4
                GROUP BY o.id
                $str_having
                ORDER BY 'tDiff' DESC";

    $tResult	=	mysqli_query( $db_sod, $tQuery	) or die( "Error: ".$tQuery );
    $tRows	    =	mysqli_num_rows( $tResult		);

    if( !$tRows ) {
        echo "<li class='callout callout-success'>
                <h5><i class='fa fa-smile-o'></i> Няма обекти без ~220V! </h5>
              </li>";

    }

    while( $tRow = mysqli_fetch_assoc( $tResult ) ) {

        $oID    = isset( $tRow['оId'  ] ) ? $tRow['оId'	 ] : 0;
        $mTime  = isset( $tRow['mTime'] ) ? $tRow['mTime'] : '- - : - - &nbsp; - -.- - .- - - -';
        $tDiff  = isset( $tRow['tDiff'] ) ? $tRow['tDiff'] : 0;
        $tSec   = isset( $tRow['tSec' ] ) ? $tRow['tSec' ] : 0;
        $bTime  = isset( $tRow['bTime'] ) ? $tRow['bTime'] : '- - : - - &nbsp; - -.- - .- - - -';
        $bDiff  = isset( $tRow['bDiff'] ) ? $tRow['bDiff'] : $tDiff;
        $bSec   = isset( $tRow['bSec' ] ) ? $tRow['bSec' ] : 0;
        $oName	= $tRow['oNum'	]." - ". $tRow['oName'	] ;
        $shortName =  mb_substr( $oName, 0, 30, 'utf-8' );
        if( $bTime != '- - : - - &nbsp; - -.- - .- - - -' ){
            $strColor   = "label-danger";
        } else {
            $strColor   = "label-warning";
        }

        echo "<li title='".$oName."'>
                <span class='handle'>
                    <small class='label $strColor'><i class='fa fa-flash'></i>&nbsp;". $mTime ."</small>&nbsp;<small class='label $strColor'><i class='fa fa-battery-2'></i>&nbsp;". $bTime ."</small>
                    ". $shortName ."
                </span>
                <span class='handle' style='float: right;'>
                    <small class='label $strColor'><i class='fa fa-clock-o'></i> ". $bDiff ." </small>
                </span>
            </li>";
    }

    echo "<li><a><i class='fa fa-clock-o'></i> ".date ( 'H:i:s d.m.Y' )."</a></li>";

endif;

?>
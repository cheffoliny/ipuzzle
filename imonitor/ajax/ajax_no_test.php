<?php

define('INCLUDE_CHECK',true);

require_once( "../config/session.inc.php"	);
require_once( "../config/output_func.php"   );
require_once( "../config/connect.inc.php"	);
require_once( "../config/dictionar.inc.php" );


if( isset($_SESSION['mid']) ):

    ob_start();

    function handle_drop($errno, $errstr, $errfile, $errline){
        if( $errno == E_WARNING ){
//            echo 'Проблем с връзката! Проверете всички връзки и опитайте да обновите страницата!';
//            $ob = ob_get_clean();
//            header("HTTP/1.0 500 Internal server error");
//            echo $ob;

        }
    }

    set_error_handler('handle_drop');

    $sig   = isset( $_GET['sig'] ) ? $_GET['sig'] : 0;
    if( $sig == 0 ) {
        $str_where =" JOIN messages m ON ( m.id_obj = o.id AND m.id_cid IN(602,611,350) AND flag = 1 AND m.to_arc = 0 ) ";
    } else {
        $str_where =" JOIN messages m ON ( m.id_obj = o.id AND m.id_cid IN(".$sig.") AND flag = 1 AND m.to_arc = 0 ) ";
    }

    $tQuery	=	"
                SELECT
						o.id                      AS 'oId'  ,
						o.num                     AS 'oNum' ,
						o.name      	          AS 'oName',
						( TIME_TO_SEC( TIMEDIFF( NOW(), MIN(m.time_al) ) ) + 0 )  AS 'tSec',
						COALESCE( (SELECT TIME_TO_SEC( TIMEDIFF( NOW(),m1.time_al ) ) FROM messages m1 WHERE m1.flag = 1 AND m1.id_cid = 350 AND m1.id_obj = o.id AND m1.to_arc = 0 LIMIT 1 ), '- - : - - &nbsp; - -.- - .- - - -' ) AS 'tDiff',
						COALESCE( (SELECT DATE_FORMAT( m2.time_al, '%H:%i %d.%m.%Y' ) FROM messages m2 WHERE m2.flag = 1 AND m2.id_cid = 602 AND m2.id_obj = o.id AND m2.to_arc = 0 LIMIT 1 ), '- - : - - &nbsp; - -.- - .- - - -' ) AS 'mTime',
						COALESCE( (SELECT DATE_FORMAT( m3.time_al, '%H:%i %d.%m.%Y' ) FROM messages m3 WHERE m3.flag = 1 AND m3.id_cid = 611 AND m3.id_obj = o.id AND m3.to_arc = 0 LIMIT 1 ), '- - : - - &nbsp; - -.- - .- - - -' ) AS 'kTime',
						COALESCE( (SELECT DATE_FORMAT( m4.time_al, '%H:%i %d.%m.%Y' ) FROM messages m4 WHERE m4.flag = 1 AND m4.id_cid = 350 AND m4.id_obj = o.id AND m4.to_arc = 0 LIMIT 1 ), '- - : - - &nbsp; - -.- - .- - - -' ) AS 'oTime'
				FROM objects o ";

    $tQuery	.=  $str_where;

    $tQuery	.=" WHERE	1
						AND o.id_status != 4
                        AND m.flag      =  1
				GROUP BY o.id

				ORDER BY 'tDiff' DESC";

    $tResult	=	mysqli_query( $db_sod, $tQuery	) or die( "Error: ".$tQuery );
    $tRows	    =	mysqli_num_rows( $tResult		);

    if( !$tRows ) {
        echo "<li class='callout callout-success'>
                <h5><i class='fa fa-smile-o'></i> Няма обекти без тест! </h5>
              </li>";
    }

    $c = 1;
    while( $tRow = mysqli_fetch_assoc( $tResult ) ) {

        $oID    = isset( $tRow['оId'  ] ) ? $tRow['оId'	 ] : 0;
        $mTime  = isset( $tRow['mTime'] ) ? $tRow['mTime'] : '- - : - - &nbsp; - -.- - .- - - -';
        $kTime  = isset( $tRow['kTime'] ) ? $tRow['kTime'] : '- - : - - &nbsp; - -.- - .- - - -';
        $oTime  = isset( $tRow['oTime'] ) ? $tRow['oTime'] : '- - : - - &nbsp; - -.- - .- - - -';
        $tDiff  = isset( $tRow['tDiff'] ) ? $tRow['tDiff'] : 0;
        $tSec   = isset( $tRow['tSec' ] ) ? $tRow['tSec' ] : 0;
        $oName	= $tRow['oNum'	]." ". $tRow['oName'	] ;
        $shortName =  mb_substr( $oName, 0, 30, 'utf-8' );

        if( $tSec > 7200 && $tSec < 36000 ){
            $strColor   = "label-warning";
        } else {
            $strColor   = "label-danger";
        }

        echo "<li title='".$oName."'>
                <span class='label label-default'>". $c .". &nbsp;</span> 
                <span class='handle' style='color: #cc0000;' title='ОТПАДНАЛА GPRS ВРЪЗКА / 350'>
                    <i class='fa fa-eye-slash text-danger'></i> &nbsp; ". $oTime ."
                </span>
                <span class='handle' style='color: #cc0000;' title='НЯМА ТЕСТ (Gprs) / 611'>
                    <i class='fa fa-text-width text-danger'></i> &nbsp; ". $kTime ."
                </span>
                <span class='handle' style='color: #cc0000;' title='НЯМА ТЕСТ (панел ) / 602'>
                    <i class='fa fa-ban on fa-text-width'></i> &nbsp; ". $mTime ."
                </span>
                &nbsp;". $shortName ."
                <span class='handle' style='float: right;'>
                    <small class='label $strColor'><i class='fa fa-clock-o'></i> ". time_elapsed($tDiff) ." </small>
                </span>
            </li>";
        $c++;
    }

    echo "<li><a><i class='fa fa-clock-o'></i> ".date ( 'H:i:s d.m.Y' )."</a></li>";

endif;

?>
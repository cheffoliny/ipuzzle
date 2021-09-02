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

    $aQuery     = "
                SELECT
                  o.num             AS 'oNum' ,
                  o.name            AS 'oName',
                  c.name			AS 'cName',
                  SUM(os.total_sum) AS 'oTax' ,
                  DATE_FORMAT( o.start, '%d.%m.%Y' ) AS 'oStart',
                  	( SELECT
                            ROUND( sum( n.last_price * ss.count ), 2 ) AS s
                        FROM storage.states ss
                        JOIN storage.nomenclatures n ON n.id = ss.id_nomenclature
                        WHERE
                                ss.id_storage = o.id AND
                                ss.storage_type = 'object' AND
                                ss.to_arc = 0
                    )	AS 'tPrice'
                FROM objects o
                LEFT JOIN clients_objects  co ON co.id_object = o.id AND co.to_arc = 0
                LEFT JOIN clients c ON co.id_client = c.id
                LEFT JOIN objects_services os ON os.id_object = o.id AND os.to_arc = 0
                WHERE
                   o.id_status IN( 1, 14 ) AND o.id_office IN( 66 )
                GROUP BY o.id
                HAVING oTax IS NULL OR cName IS NULL
                ORDER BY o.start ASC ";

    $aResult	=	mysqli_query( $db_sod, $aQuery	) or die( "Error: ".$aQuery );
    $aRows	    =	mysqli_num_rows( $aResult		);

    if( !$aRows ) {
        echo "<li class='callout callout-success'>
                <h5><i class='fa fa-smile-o'></i> Няма обекти без такси и клиент! </h5>
              </li>";
    }

    for( $c = 0; $c < $aRows; $c++ ) {

        $aRow = mysqli_fetch_assoc( $aResult );


        $tPrice  = isset( $aRow['tPrice'] ) ? $aRow['tPrice'] : '000.00';
        $oTax    = isset( $aRow['оTax'  ] ) ? $aRow['оTax'	] : '000.00';
        $cName	 = isset( $aRow['cName'	] ) ? $aRow['cName'	] : '- - - - - - - - - - - - - - - ';
        $oStart  = isset( $aRow['oStart'] ) ? $aRow['oStart'] : '- - : - - &nbsp; - -.- - .- - - -';
        $oName	 = $aRow['oNum'	]." ". $aRow['oName'	] ;

        $shortClient=  mb_substr( $cName, 0, 30, 'utf-8' );
        $shortName  =  mb_substr( $oName, 0, 30, 'utf-8' );


        echo "<li title='".$oName."'>
                ". $shortName ."

                <span class='handle pull-right' style='color: #cc0000;' title='ВЪВЕДЕН НА:'>
                    <i class='fa fa-play text-danger'></i> &nbsp; ". $oStart ."  &nbsp;
                </span>
                 <span class='handle pull-right' style='color: #cc0000;' title='Такса'>
                    <i class='fa fa-dropbox on fa-text-width'></i> &nbsp; ". $tPrice ." &nbsp;
                </span>
                <span class='handle pull-right' style='color: #cc0000;' title='Такса'>
                    <i class='fa fa-euro on fa-text-width'></i> &nbsp; ". $oTax ." &nbsp;
                </span>
                <span class='handle pull-right' title='Клиент'>
                    ". $shortClient ."
                </span>
            </li>";
    }

    echo "<li><a><i class='fa fa-clock-o'></i> ".date ( 'H:i:s d.m.Y' )."</a></li>";

endif;
?>
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

    $threshold_level   = 30;
    $hour_time_back    = 48;
    $allowable_number  = 3;

    $tQuery	=	"
                SELECT
                        o.id          AS 'oId'  ,
                        o.num         AS 'oNum' ,
                        o.name        AS 'oName',
                        COUNT( a.id ) AS 'sC' ,
                        SUM( IF( (a.pass < $threshold_level AND a.pass > 0 ), 1, 0 ) ) AS 'c'    ,
                        GROUP_CONCAT(
                                CONCAT(
                                        a.num                                         ,
                                        '$' ,
                                        REPLACE( REPLACE( a.msg, ',', ' ' ), '[ ]', '' ),
                                        '$' ,
                                        DATE_FORMAT( a.msg_time, '%H:%i:%s %d.%m.%Y' ),
                                        '$' ,
                                        a.pass
                                        ) ORDER BY a.msg_time DESC 
                                    ) AS aMsg
                FROM objects o
                LEFT JOIN messages m ON ( m.id_obj = o.id )
                LEFT JOIN archiv_".$strCurrentMonth." a ON ( a.id_msg = m.id )

                WHERE
                        o.id_status != 4 AND a.msg_time > DATE_ADD( NOW(), INTERVAL -".$hour_time_back." HOUR )
                GROUP BY o.id
                HAVING c > ".$allowable_number."
                ORDER BY  'c' DESC";

    $tResult	=	mysqli_query( $db_sod, $tQuery	) or die( "Error: ".$tQuery );
    $tRows	    =	mysqli_num_rows( $tResult		);

    if( !$tRows ) {
        echo "<li class='callout callout-success'>
                <h5><i class='fa fa-smile-o'></i> Няма обекти с ниво на сигнала под ".$threshold_level."%! </h5>
              </li>";

    }


    while( $tRow = mysqli_fetch_assoc( $tResult ) ) {

        $oID    = isset( $tRow['oId'    ] ) ? $tRow['oId'   ] : 0;
        $oCount = isset( $tRow['c'      ] ) ? $tRow['c'	    ] : 0;
        $sC     = isset( $tRow['sC'     ] ) ? $tRow['sC'    ] : 0;
        $aMsg   = isset( $tRow['aMsg'   ] ) ? $tRow['aMsg'  ] : 0;
        $oName	= $tRow['oNum'	]." - ". $tRow['oName'	] ;
        $strColor   = "label-warning";

        if( $oCount > $allowable_number + 1  ){
            $strColor   = "label-danger";
        }

        if( $oCount > $allowable_number  ){

            $strModal   = "modal".$oID;
            $tableModal = "table".$oID;
            $oHintArray = explode(",", htmlentities( htmlspecialchars( strip_tags( $aMsg ) ) ) );

            $tableModal = "<div class='row text-center table-condensed header '><h5><b>".$oName."</b></h5></div>
                        <div class='row text-center table-condensed'>
                            <div class='col-sm-3 col-md-3'>Време</div>
                            <div class='col-sm-1 col-md-1'>N:</div>
                            <div class='col-sm-6 col-md-6'><p>Сигнал</p></div>
                            <div class='col-sm-2 col-md-2 text-left'>Ниво</div>
                        </div>";

            $oCount = str_replace(" ","&nbsp;",str_pad( $oCount, 7, " ", STR_PAD_LEFT ));

            echo "<li>
                    &nbsp; ". $oName ."
                    <span class='handle' style='float: right;' >
                    <a onclick=\"$('#".$strModal."').appendTo(body);\"   data-toggle='modal' href='#' data-target='#".$strModal."'  class='head'>
                        <small class='label $strColor'>&nbsp;<i class='fa fa-cog'></i>". $oCount ."</small>
                    </a>
                    </span>
                </li>";

            $t = 0;
            for( $t = 0; $t < $sC; $t++ ) {
                $oHintArrayValue = explode( "$" , $oHintArray[$t] );
                $object_class = "";
                if( $oHintArrayValue[3] < $threshold_level ) { $object_class = "label label-danger"; }

                $tableModal .= "<div class='row text-center table-condensed'>
                                    <div class='col-sm-3 col-md-3'>".$oHintArrayValue[2]."</div>
                                    <div class='col-sm-1 col-md-1'>".$oHintArrayValue[0]."</p></div>
                                    <div class='col-sm-6 col-md-6'>".$oHintArrayValue[1]."</p></div>
                                    <div class='col-sm-2 col-md-2 text-left'><small class='$object_class'>".$oHintArrayValue[3]."</small></div>
                                </div>";
            }
            echo '<div class="modal fade" id="'.$strModal.'" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body table-responsive">'.$tableModal.'</div>
                        <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"> X </button>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>';
        }


    }


    echo "<li><a><i class='fa fa-clock-o'></i> ".date ( 'H:i:s d.m.Y' )."</a> <a class='pull-right text-red'>С повече от ".$allowable_number." сигнала за последните ".$hour_time_back." ч. с ниво под ".$threshold_level."%</a></li>";

endif;

?>
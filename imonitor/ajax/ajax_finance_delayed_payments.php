<?php

if(!defined('INCLUDE_CHECK')) die('Операцията не е позволена');

require_once( "./config/session.inc.php"	);
require_once( "./config/connect.inc.php"	);
require_once( "./config/dictionar.inc.php" );

if( isset($_SESSION['mid']) ):

    $c = 0;
    $sub_total  = 0;
    $total_sum  = 0;
    $total_count= 0;
    $sub_count  = 0;
    $strReturn  = '';

    $aQuery     = "
                SELECT
                     TIMESTAMPDIFF(
                                  MONTH,
                                  MIN(
                                        IF(
                                            os.real_paid != '0000-00-00',
                                            os.real_paid,
                                            os.start_date
                                        )
                                  ),
                                  DATE_FORMAT(NOW(), '%Y-%m-01' )
                                )  AS 'cMonth',
                     MIN( IF( os.real_paid != '0000-00-00', os.real_paid, os.start_date ) ) AS 'pMonth'
                FROM objects o
                JOIN objects_services os ON o.id = os.id_object AND os.to_arc = 0
                WHERE
                     o.id_status IN( 1, 14 ) AND
                     o.id_office IN( 66 )    AND
                     (
                        ( os.real_paid != '0000-00-00' AND os.real_paid < DATE_FORMAT( NOW(), '%Y-%m-01' ) )
                        OR
                        ( os.real_paid = '0000-00-00' AND DATE_FORMAT( os.start_date, '%Y%m' ) < DATE_FORMAT( NOW(), '%Y%m' ) )
                     )
                ORDER BY pMonth ASC";

    $aResult	=	mysqli_query( $db_sod, $aQuery	) or die( "Error: ".$aQuery );
    $aRows	    =	mysqli_num_rows( $aResult		);

    if( !$aRows ) {
        echo "<li class='callout callout-success'>
                <h5><i class='fa fa-smile-o'></i> Няма закъснели плащания! </h5>
              </li>";
    }

    while( $aRows = mysqli_fetch_assoc( $aResult ) ) {

        $cMonth = isset( $aRows['cMonth'] ) ? $aRows['cMonth'] : 0;
        $pMonth = isset( $aRows['pMonth'] ) ? $aRows['pMonth'] : '2016-01-01';

        for( $c = 0; $c < $cMonth; $c++ ) {


            $strModal = "modal".$c;
            $tableModal = "<div id='".$c."' class='row text-center table-condensed header '><h5><b>" . $mPaid . "</b></h5></div>";

            $mQuery ="SELECT
                            COALESCE(
                                GROUP_CONCAT(
                                      o.num                                           , '#',
                                      REPLACE( REPLACE( o.name, ',', ' ' ), '$', '' ) , '#',
                                      DATE_FORMAT( os.last_paid, '%m.%Y' )            , '#',
                                      DATE_FORMAT( os.real_paid, '%m.%Y' )            , '#',
                                      os.total_sum
                                ),
                                ''
                            )     AS 'oNum',
                            COALESCE( COUNT( o.id ), 0 ) AS 'oCount',
                            COALESCE( ROUND( SUM( os.total_sum / 1.2 ), 2 ), 0 ) AS 'tSum',
                            DATE_FORMAT( COALESCE( DATE_ADD(os.real_paid, INTERVAL + 1 MONTH), '".$pMonth."' ), '%m.%Y' ) AS 'mPaid'
                      FROM objects o
                      JOIN objects_services os ON o.id = os.id_object AND os.to_arc = 0
                      WHERE
                            o.id_status IN( 1, 14 ) AND
                            o.id_office IN( 66 )    AND
                            (
                                ( os.real_paid = '".$pMonth."' )
                                OR
                                ( os.real_paid = '0000-00-00' AND DATE_FORMAT( os.start_date, '%Y%m' ) = DATE_FORMAT( DATE_ADD( '".$pMonth."', INTERVAL + 1 MONTH ), '%Y%m' ) )
                            )
                      ORDER BY mPaid ASC";

            $mResult	=	mysqli_query( $db_sod, $mQuery	) or die( "Error: ".$mQuery );
//echo $mQuery;
            while( $mRow = mysqli_fetch_assoc( $mResult ) ) {

                $oNum   = isset($mRow['oNum'    ]) ? $mRow['oNum'   ] : '0';
                $oCount = isset($mRow['oCount'  ]) ? $mRow['oCount' ] : '0';
                $tSum   = isset($mRow['tSum'    ]) ? $mRow['tSum'   ] : '000.00';
                $mPaid  = isset($mRow['mPaid'   ]) ? $mRow['mPaid'  ] : date( "m.Y", strtotime( $pMonth." +1 month" ) );


                $pMonth = date( "Y-m-d", strtotime( $pMonth." +1 month" ) );
                $sub_total += $tSum;
                $sub_count += $oCount;
//

            }

            echo '<div class="modal fade" id="' . $strModal . '" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-body table-responsive">';
            if( $oCount > 0 ) {

                $oHintArray = explode( ",", stripslashes( $oNum ) );
                $t = 0;

                for ($t = 0; $t < $oCount; $t++) {

                    $oHintArrayValue = explode("#", $oHintArray[$t]);
                    echo  "<div class='row text-center table-condensed'>
                                            <div class='col-sm-1 col-md-1'>" . $oHintArrayValue[0] . "</div>
                                            <div class='col-sm-5 col-md-5 text-left'>" . $oHintArrayValue[1] . "</div>
                                            <div class='col-sm-2 col-md-2'><p>" . $oHintArrayValue[2] . "</p></div>
                                            <div class='col-sm-2 col-md-2'><p>" . $oHintArrayValue[3] . "</p></div>
                                            <div class='col-sm-2 col-md-2'><p>" . $oHintArrayValue[4] . "</p></div>
                                        </div>";
                }

            }
            // . $tableModal.$c .

            echo '</div>
                            <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"> X </button>
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>';


            $total_sum += $sub_total;
            $total_count += $sub_count;

            $strReturn.=    '
                        <div class="col-xs-3 col-md-2 text-center" style="border-right: 1px solid #f4f4f4" title="' . $oNum . '">
                            <div class="knob-label" title="Неплатени такси: '. $total_count .' / Несъбрани суми: '. $total_sum .'">
                            <a onclick="$(\'#'.$strModal.'\').appendTo(\'body\');"       data-toggle="modal" href="#" data-target="#'.$strModal.'"       class="head">
                            <b>' . substr($pMonth, 5, 2).".".substr($pMonth, 0, 4) . '</b></a></div>
                            <input type="text" class="knob" data-readonly="true" value="' . $sub_total . '" data-width="80" data-height="80" data-fgColor="#39CCCC" data-step="1" data-max="15000" data-min="0" data-displayPrevious=true  data-thickness=".35" />
                            <div class="knob-label text-red" title="За месец ' . $pMonth . ': Брой неплатили: ' . $sub_count . ', Неплатена сума: ' . $tSum . '"><b>' . $oCount . ' / ' . $sub_count . '</b> бр.</div>
                            <div class="knob-label text-red" title="За месец ' . $pMonth . ': Брой неплатили: ' . $sub_count . ', Неплатена сума: ' . $tSum . '"><b>' . $tSum   . ' / ' . $sub_total . '</b> лв.</div>
                        </div>';

//            echo $pMonth." / ";

        }

    }

        $strReturn.= '
                <div class="knob-label"><b>Общо до '.date("m.Y").'</b></div>
                <div class="col-xs-2 text-center" style="border-right: 1px solid #f4f4f4" title="'.$oNum.'">
                    <input type="text" class="knob" data-readonly="true" value="'.$total_sum.'" data-width="80" data-height="80" data-fgcolor="#ff3939" data-step="1" data-max="15000" data-min="0" data-displayPrevious=true data-thickness=".35">
                    <div class="knob-label" title="За месец '.date("m.Y").': Брой неплатили: '.$total_count.', Неплатена сума: '.$total_sum.'"><b> '.$total_count.' / '.$total_sum.' </b></div>

                </div>';

        echo $strReturn;

endif;



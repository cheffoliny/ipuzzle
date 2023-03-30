<?php

define('INCLUDE_CHECK',true);

require_once './config/connect.php';
require_once './config/session.inc.php';
require_once './include/functions.php';


if($_SESSION['id']):

echo '<section class="content">';

		/*********/
		$aQuery	=	"
                SELECT
						o.id AS 'oID', o.num AS 'oNum', o.name AS 'oName', m.msg_rest AS 'mRest', o.work_time_alert AS 'wTime'
				FROM objects o
				JOIN messages m ON m.id_obj = o.id AND m.to_arc = 0 AND m.flag = 1 AND m.id_cid BETWEEN 400 AND 500
				WHERE
					o.id_status IN( 1, 14 )
					AND CONCAT( DATE_FORMAT(NOW(),'%Y-%m-%d '), o.work_time_alert ) < NOW()
					AND o.work_time_alert != '00:00:00'
				GROUP BY o.id ";

		$aResult	=	mysqli_query( $db_sod,  $aQuery );
		$num_aRows	=	mysqli_num_rows( $aResult		);

		if( !$num_aRows ) {
			echo '<div class="page-header"><h4>Няма отворени обекти след работно време!</h4></div>';
		}

		echo '<table class="table table-condensed" style="border: 0px solid #fefefe;">';

		while( $aRow = mysqli_fetch_assoc( $aResult ) ) {

			$oID	        = isset( $aRow['oID'  ] ) ? $aRow['oID' ] : 0 ;
            $oNum	        = isset( $aRow['oNum' ] ) ? $aRow['oNum' ] : 0 ;
            $oName	        = isset( $aRow['oName'] ) ? $aRow['oName'] : '';
			$mRest	        = isset( $aRow['mRest']) ? $aRow['mRest']: '';
			$wTime	        = isset( $aRow['wTime']) ? $aRow['wTime']: '';

			$strModal = "myModal".$oID;

			echo '<tr>
				  	<td>
				  		<i class="fa fa-home"></i><a onclick="$(\'#'.$strModal.'\').appendTo(\'body\');" data-toggle="modal" href="#" data-target="#'.$strModal.'" class="head">  '.$oNum.' - '. $oName .' </a>
				    </td>
                    <td style="width: 30%;">
                    	<div class="col-lg-12 col-xs-12">'. $mRest .'</div>
				 	</td>
				 	<td style="width: 25%;" class="bg-red">
                    	'. $wTime .'
				 	</td>
				  </tr>';

			//=============================================

			echo '<div class="modal fade" id="'.$strModal.'" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body">';
								get_object_faces( $oID );
			echo '          </div>
                            <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"> X </button>
                        </div>
                    </div>
                  </div>';

			//=============================================

		}

		echo '</table>';

echo '</section>'; // End section content

endif;

?>
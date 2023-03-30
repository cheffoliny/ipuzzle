<?php

// alarm_time - време на алармата;
// start_time - време на подаване на алармата;
// receive_time - време на получаване/оповестяване на патрул/ за алармата;
// end_time - време за пристигане на обекта;
// alarm_reason_time - време за позочване на причина;
define('INCLUDE_CHECK',true);
#if(!defined('INCLUDE_CHECK')) die('You are not allowed to execute this file directly');
#die('You are not allowed to execute this file directly');


require_once './config/connect.php';
require_once './config/session.inc.php';
require_once './include/functions_get_alarms.php';
require_once './include/functions.php';

if($_SESSION['id']):

    echo '<section class="content">';

    //$DBPersonnel = " ". $db_personnel.".personnel ";
	$aID	        = isset( $_GET['aID'  	        ] ) ? $_GET['aID'           ] : 0 ;
	$alarm_status	= isset( $_GET['alarm_status'	] ) ? $_GET['alarm_status'	] : '';
	$alarm_reason	= isset( $_GET['alarm_reason'	] ) ? $_GET['alarm_reason'	] : 0 ;
	$alarm_reason2	= isset( $_GET['alarm_reason2'	] ) ? $_GET['alarm_reason2'	] : 0 ;
	$idUser			= isset( $_SESSION['uid'  		] ) ? $_SESSION['uid'		] : 0 ;
    $gName = '';
    $oName = '';
    $rName = '';

	if( isset( $aID ) && $aID != 0 ) :

        /* ********/
		if( isset( $alarm_status ) && $alarm_status != '' ) :
			if( $alarm_reason == 0 ) {
				$alarm_reason = $alarm_reason2;
			}
			update_alarm_status( $aID, $alarm_status, $idUser, $alarm_reason );

		else :
		//	echo "Вътрееее";
		endif;

		/*********/
		$aQuery	=	"
                SELECT
					DATE_FORMAT( swkm.alarm_time	, '%d.%m.%Y %H:%i:%s' ) 	AS aTime,
					DATE_FORMAT( swkm.send_time	    , '%d.%m.%Y %H:%i:%s' ) 	AS sTime,
					DATE_FORMAT( swkm.start_time	, '%d.%m.%Y %H:%i:%s' ) 	AS gTime,
					DATE_FORMAT( swkm.end_time      , '%d.%m.%Y %H:%i:%s' ) 	AS oTime,
					DATE_FORMAT( swkm.reason_time	, '%d.%m.%Y %H:%i:%s' ) 	AS rTime,
					IF( swkm.start_time != '0000-00-00 00:00:00', TIME_FORMAT( TIMEDIFF( swkm.start_time , swkm.send_time ), '%H%i%s'), 0 )  AS timeToStart  ,
					IF( swkm.end_time   != '0000-00-00 00:00:00', TIME_FORMAT( TIMEDIFF( swkm.end_time   , swkm.send_time ), '%H%i%s'), 0 )  AS timeToObject ,
					IF( swkm.reason_time!= '0000-00-00 00:00:00', TIME_FORMAT( TIMEDIFF( swkm.reason_time, swkm.send_time ), '%H%i%s'), 0 )  AS timeToEnd    ,
					swkm.start_user         AS gUser    ,
					swkm.end_user           AS oUser    ,
					swkm.reason_user        AS rUser    ,
					swkm.alarm_time			AS zTime	,
					swkm.id				    AS aID		,
					swkm.obj_name 		    AS oName	,
					swkm.id_archiv_alarm    AS sID      ,
					o.id                    AS oID      ,
					o.id_receivers		    AS oRec		,
					o.num                   AS oNum     ,
					o.geo_lat			    AS oLat		,
					o.geo_lan			    AS oLan		,
					o.address			    AS oAddr	,
					o.place				    AS oPlace	,
					o.operativ_info		    AS oInfo

				FROM work_card_movement swkm
				LEFT JOIN objects o ON o.id = swkm.id_object

				WHERE
				swkm.id		= ".$aID ." ";

//echo $aQuery;
		$aResult	=	mysqli_query( $db_sod,  $aQuery );
		$num_aRows	=	mysqli_num_rows( $aResult		);


		if( !$num_aRows ) {
			echo '<div class="page-header"><h4>Няма намерена активна аларма. Проверете в архива!</h4></div>';
		}


		while( $aRow = mysqli_fetch_assoc( $aResult ) ) {

			$aID        	= isset( $aRow['aID'  ] ) ? $aRow['aID'	 ] : 0 ;
            $sID        	= isset( $aRow['sID'  ] ) ? $aRow['sID'	 ] : 0 ;
            $oNum	        = isset( $aRow['oNum' ] ) ? $aRow['oNum' ] : 0 ;
			$zTime	        = isset( $aRow['zTime'] ) ? $aRow['zTime'] : 'NOW()'; // Аларма
			$aTime	        = isset( $aRow['aTime'] ) ? $aRow['aTime'] : '00.00.0000 00:00:00'; // Аларма
			$sTime	        = isset( $aRow['sTime'] ) ? $aRow['sTime'] : '00.00.0000 00:00:00'; // Изпратена
			$gTime	        = isset( $aRow['gTime'] ) ? $aRow['gTime'] : '00.00.0000 00:00:00'; // Приета
			$oTime	        = isset( $aRow['oTime'] ) ? $aRow['oTime'] : '00.00.0000 00:00:00'; // На обекта
			$rTime	        = isset( $aRow['rTime'] ) ? $aRow['rTime'] : '00.00.0000 00:00:00'; // Причина
            $timeToStart    = isset( $aRow['timeToStart'    ] ) ? $aRow['timeToStart'   ] : 0;
            $timeToObject   = isset( $aRow['timeToObject'   ] ) ? $aRow['timeToObject'  ] : 0;
            $timeToEnd      = isset( $aRow['timeToEnd'      ] ) ? $aRow['timeToEnd'     ] : 0;
            $oID	        = isset( $aRow['oID'  ] ) ? $aRow['oID'  ] : 0;
            $oRec	        = isset( $aRow['oRec' ] ) ? $aRow['oRec' ] : 0;
            $dName	        = isset( $aRow['oName'] ) ? stripslashes($aRow['oName']) : '';
			$oLat	        = isset( $aRow['oLat' ] ) ? $aRow['oLat' ] : 0;
			$oLan	        = isset( $aRow['oLan' ] ) ? $aRow['oLan' ] : 0;
			$dInfo	        = isset( $aRow['oInfo'] ) ? $aRow['oInfo'] : '';
			$dAddr	        = isset( $aRow['oAddr'] ) ? stripslashes($aRow['oAddr'] ) : '';
			$dPlace	        = isset( $aRow['oPlace']) ? stripslashes($aRow['oPlace']) : '';
            $gUser	        = isset( $aRow['gUser'] ) ? stripslashes($aRow['gUser'] ): 0;
            $oUser	        = isset( $aRow['oUser'] ) ? stripslashes($aRow['oUser'] ): 0;
            $rUser	        = isset( $aRow['rUser'] ) ? stripslashes($aRow['rUser'] ): 0;
			$startClass 	= 'bg-gray';
			$OnObjectClass 	= 'bg-gray';
			$reasonClass	= 'bg-gray';
			$aLinkGet		= '';
            $aLinkOnObject	= '';
			$aLinkReason	= '';
			$diffReceive	= 0;
			$diffOnObject	= 0;
			$diffReason		= 0;

            if( $gUser != '0' ) { $gName = getPersonNameByID( $gUser ); }
            if( $oUser != '0' ) { $oName = getPersonNameByID( $oUser ); }
            if( $rUser != '0' ) { $rName = getPersonNameByID( $rUser ); }

			/* Ако не е ПРИЕТА */
			if( isset( $gTime ) && $gTime == '00.00.0000 00:00:00' ) :
				$startClass 	= 'bg-red' ;
				$aLinkGet 	= ' onclick="loadXMLDoc(\'action.php?action=home&alarm_status=start_time&aID='. $aID .'\', \'main\', \'home\'); return false;" style="cursor:pointer" ';
			/* На обекта */
			elseif( isset( $oTime ) && $oTime == '00.00.0000 00:00:00' ) :
				$OnObjectClass	= 'bg-yellow';
				$aLinkOnObject 	= ' onclick="loadXMLDoc(\'action.php?action=home&alarm_status=end_time&aID='. $aID .'\', \'main\', \'home\'); return false;" style="cursor:pointer" ';
			/* Причина */
			elseif( isset( $rTime ) && $rTime == '00.00.0000 00:00:00' ) :
				$reasonClass 	= 'bg-green';
                //$aLinkOnObject 	= ' onclick="loadXMLDoc(\'action.php?action=home&alarm_status=reason_time&aID='. $aID .'\', \'main\', \'home\'); return false;" style="cursor:pointer" ';
			endif;


			/* Време за приемане на алармата */
			if( isset( $timeToStart ) && $timeToStart != '0' ) :
                if( substr( $timeToStart, 0, 2 ) != '00' ) {
                    $sH = substr( $timeToStart, 0, 2 ).":";
                } else { $sH = ''; }
                $sM = substr( $timeToStart, 2, 2 ).":";
                $sS = substr( $timeToStart, 4, 2 )."";

				if( $sM > 5 ):
				    $diffReceive = "<span class='badge pull-right bg-red'    >".$sH." ".$sM." ".$sS."</span>";
				else :
				    $diffReceive = "<span class='badge pull-right bg-green'  >".$sH." ".$sM." ".$sS."</span>";
				endif;
			endif;

			if( isset( $timeToObject ) && $timeToObject != '0' ) :
                if( substr( $timeToObject, 0, 2 ) != '00' ) {
                    $oH = substr( $timeToObject, 0, 2 ).":";
                } else { $oH = ''; }
                $oM = substr( $timeToObject, 2, 2 ).":";
                $oS = substr( $timeToObject, 4, 2 )."";

                if( $oM > 5 ):
				    $diffOnObject = "<span class='badge pull-right bg-red'   >".$oH." ".$oM." ".$oS."</span>";
				else :
				    $diffOnObject = "<span class='badge pull-right bg-green' >".$oH." ".$oM." ".$oS."</span>";
				endif;
			endif;

			if( isset( $timeToEnd ) && $timeToEnd != '0' ) :
                if( substr( $timeToEnd, 0, 2 ) != '00' ) {
                    $eH = substr( $timeToEnd, 0, 2 ).":";
                } else { $eH = ''; }
                $eM = substr( $timeToEnd, 2, 2 ).":";
                $eS = substr( $timeToEnd, 4, 2 )."";

                if( $eM > 5 ):
					$diffReason = "<span class='badge pull-right bg-red'   >".$eH." ".$eM." ".$eS."</span>";
				else :
					$diffReason = "<span class='badge pull-right bg-green' >".$eH." ".$eM." ".$eS."</span>";
				endif;
			endif;


            echo '  <div class="row">

                        <div class="col-lg-3 col-xs-3">
                            <div class="small-box '.$startClass.'" '. $aLinkGet .'>
                                <div class="inner">
                                    <p>ПРИЕМАМ '. $diffReceive  .'</p>
                                </div>
                                <a href="#" class="small-box-footer pull-right">
                                   '. $gName .' &nbsp; [ '. substr( $gTime, 10, 10 ) .' ]
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-xs-3">
                            <div class="small-box '. $OnObjectClass .'" '. $aLinkOnObject .'>
                                <div class="inner">
                                    <p>НА ОБЕКТА '. $diffOnObject .'<br /></p>
                                </div>
                                <a href="#" class="small-box-footer pull-right">
                                   '. $oName .' &nbsp; [ '. substr( $oTime, 10, 10 )  .' ]
                                </a>
                            </div>
                        </div>';

                        if( $oTime != '00.00.0000 00:00:00' && $rTime == '00.00.0000 00:00:00' ):
                            //echo $oTime." / ".$rTime;
                            echo '<form id="details" onsubmit="submitForm( \'details\',\'action.php?action=home&alarm_status=reason_time&aID='. $aID .'\', \'main\', \'home\' ); return false;" method="GET">
                                    <div class="col-lg-3 col-xs-3">
                                        <div class="small-box">
                                            <div class="inner" style="padding: 0px; margin: 0 2px;">
                                                <select id="alarm_reason" name="alarm_reason" style="background-color: #3c8dbc;" >
                                                    <option value="0">- С РЕАКЦИЯ -</option>';
                                                    get_alarm_reasons(); /* SELECT на прчините */
                            echo '              </select>
              									<select id="alarm_reason2" name="alarm_reason2" style="background-color: #f56954;">
                                                    <option value="0">- БЕЗ РЕАКЦИЯ -</option>';
													get_alarm_reasons2(); /* SELECT на прчините */
							echo '              </select>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="col-lg-3 col-xs-3" type="submit" onclick="return checkReason(); this.getElementById(\'details\').submit()" style="background: inherit; border: 0;">
                                        <div class="small-box bg-green">
                                            <div class="inner">
                                                <p>ПРИКЛЮЧИ</p>
                                                <p>'. $rName .'</p>
                                            </div>
                                            <a href="#" class="small-box-footer pull-right">
                                                &nbsp
                                            </a>
                                        </div>
                                    </button>
                                 </form>';

                        else:
                            echo '<div class="col-lg-3 col-xs-3">
                                        <div class="small-box bg-gray">
                                            <div class="inner">
                                               <p>ПРИЧИНИ</p>
                                            </div>
                                            <a href="#" class="small-box-footer pull-right">
                                                &nbsp;
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-xs-3">
                                        <div class="small-box bg-gray">
                                            <div class="inner">
                                                <p>ПРИКЛЮЧИ '.$diffReason.'</p>
                                            </div>
                                            <a href="#" class="small-box-footer pull-right">
                                                '. $rName .' &nbsp; [ '. substr( $rTime, 10, 10 )  .' ]
                                            </a>
                                        </div>
                                    </div>';
                        endif;
            echo '</div>';
// Start Modal archiv
            $strModalArchiv = "archivModal".date( 'His' );

            echo '<div class="modal fade" data-refresh="true" id="'.$strModalArchiv.'" role="dialog" aria-labelledby="archivModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body">';
            echo '            <table>';
            get_object_archiv( $oRec, $sID, $oNum, $zTime, 720, 20, $oLan, $oLat  );
            echo '            </table>';
            echo '          </div>
                            <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"> X </button>
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>';
// End Modal archiv

            $strModal = "myModal".$oID;

            echo '<div class="modal fade" data-refresh="true" id="'.$strModal.'" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body">';
                            get_object_faces( $oID );
        echo '              </div>
                            <button type="button" class="btn btn-danger pull-right" data-dismiss="modal"> X </button>
                        </div>
                        <!-- /.modal-content -->
                    </div>
                    <!-- /.modal-dialog -->
                </div>
                    <!-- /.modal -->

                    ';

			echo '<table class="table table-condensed" style="border: 0px solid #fefefe;">
			        <tr>
				        <td colspan="2" class="bg-gray">
				            <h4>
				                <a onclick="$(\'#'.$strModal.'\').appendTo(\'body\');"       data-toggle="modal" href="#" data-target="#'.$strModal.'"       class="head">&nbsp;&nbsp;<i class="fa fa-home fa-lg"></i>&nbsp;&nbsp;'. $dName .' </a>
				                <a onclick="$(\'#'.$strModalArchiv.'\').appendTo(\'body\');" data-toggle="modal" href="#" data-target="#'.$strModalArchiv.'" class="head pull-right"><i class="fa fa-book fa-lg"></i>&nbsp;&nbsp;</a>
				            </h4>
				        </td>
                        <td rowspan="3" style="width: 25%;">
                            <div class="col-lg-12 col-xs-12">'. $dInfo .'</div>
				 	    </td>
				  </tr>
			      <tr>
					<td style="width: 25%;" >
				    	&nbsp;&nbsp;<i class="fa fa-map-marker fa-lg"></i><b>&nbsp;&nbsp;АДРЕС: </b>
				    </td>
				    <td>
				    	'. $dAddr .'
				    </td>
				 </tr>';
			
			echo '<tr>
				    <td style="width: 25%;" >
				    	&nbsp;&nbsp;<i class="fa fa-map-marker fa-lg"></i><b>&nbsp;&nbsp;ОРИЕНТИР: </b>
				    </td>
				    <td>
				    	'. $dPlace .'
				    </td>
				  </tr>';

                  get_object_archiv( $oRec, $sID, $oNum, $zTime, 3, 10, $oLan, $oLat  );

            echo '</table>';

        }
		
	endif;

echo '</section>'; // End section content

endif;
	
?>
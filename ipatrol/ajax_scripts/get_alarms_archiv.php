<?php
define('INCLUDE_CHECK',true);

require_once '../config/connect.php';
require_once '../config/session.inc.php';

if($_SESSION['id']):	

	$period	= isset( $_GET['period'  ] ) ? $_GET['period'	 ] : 'day' ;
	
	$aQuery = "SELECT
					DATE_FORMAT( swkm.alarm_time	, '%d.%m.%Y %H:%i:%s' ) 	AS aTime,
					DATE_FORMAT( swkm.start_time	, '%d.%m.%Y %H:%i:%s' ) 	AS sTime,
					DATE_FORMAT( swkm.start_time	, '%d.%m.%Y %H:%i:%s' ) 	AS gTime,
					DATE_FORMAT( swkm.end_time      , '%d.%m.%Y %H:%i:%s' ) 	AS oTime,
					DATE_FORMAT( swkm.reason_time	, '%d.%m.%Y %H:%i:%s' ) 	AS rTime,
					swkm.id				AS aID	,
					swkm.obj_name 		AS oName,
					o.address			AS oAddr,
					o.operativ_info		AS oInfo
					
				FROM work_card_movement swkm
				LEFT JOIN objects o ON o.id = swkm.id_object
				
				WHERE
				swkm.reason_time	!= '0000-00-00 00:00:00' AND
				swkm.start_time > DATE_ADD( NOW(), INTERVAL -1 ". $period ." )
				
				ORDER BY swkm.id DESC
			 ";
								
	$aResult = mysqli_query( $db_sod, $aQuery ) or die( "Error: ". $aQuery );
	
	echo '
	<table id="ui-widget" class="ui-widget">
	<tr style="background-color: #060606;">
		<th id="column_archiv1" class="ui-widget-header" >&nbsp;	</th>
		<th id="column_archiv2" class="ui-widget-header">Подаден	</th>
		<th id="column_archiv3" class="ui-widget-header">Обект		</th>
		<th id="column_archiv2" class="ui-widget-header">Приет		</th>
		<th id="column_archiv2" class="ui-widget-header">На обекта	</th>
		<th id="column_archiv2" class="ui-widget-header">Причина	</th>
	</tr>';
	
	
	while( $aRow = mysqli_fetch_assoc( $aResult ) ) {
	
		$aID	= isset( $aRow['aID'  ] ) ? $aRow['aID'	 ] : 0 ;
		$aTime	= isset( $aRow['aTime'] ) ? $aRow['aTime'] : ''; // Аларма
		$sTime	= isset( $aRow['sTime'] ) ? $aRow['sTime'] : ''; // Изпратена
		$gTime	= isset( $aRow['gTime'] ) ? $aRow['gTime'] : ''; // Приета
		$oTime	= isset( $aRow['oTime'] ) ? $aRow['oTime'] : ''; // На обекта
		$rTime	= isset( $aRow['rTime'] ) ? $aRow['rTime'] : ''; // Причина
		$dName	= isset( $aRow['oName'] ) ? $aRow['oName'] : '';
		$dInfo	= isset( $aRow['oInfo'] ) ? $aRow['oInfo'] : '';
		$dAddr	= isset( $aRow['oAddr'] ) ? $aRow['oAddr'] : '';
		
	echo '<tr>
		  	<td class="tdleft">ico</td>
		    <td class="tdcenter"    >'. $sTime .'</td>
		    <td class="tdleft"	    >'. $dName .'</td>
		    <td class="tdcenter"	>'. $gTime .'</td>
		    <td class="tdcenter"	>'. $oTime .'</td>
		    <td class="tdcenter"	>'. $rTime .'</td>
		  </tr>
		';
	}

	
	echo '
	</table>';
	
endif;

?>
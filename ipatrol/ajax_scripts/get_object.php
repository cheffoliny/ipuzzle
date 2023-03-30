<?php

#header("Content-type: text/html; charset=cp1251");
header("Pragma: no-cache");

define('INCLUDE_CHECK',true);

require_once '../config/connect.php';
require_once '../config/session.inc.php';

$oNum	= isset( $_GET['oNum'	] ) ? $_GET['oNum'	] : '';
$patrul	= isset( $_GET['patrul'	] ) ? $_GET['patrul'] : 0;
$oID	= isset( $_GET['oID'	] ) ? $_GET['oID'	] : '';
$mTime	= isset( $_GET['mTime'	] ) ? $_GET['mTime' ] : '';
$cName	= isset( $_GET['cName'	] ) ? $_GET['cName' ] : '';
$mTable = "archiv_".date('Ym');
//echo $mTime;

if($_SESSION['id']):


	if( isset( $patrul ) && $patrul == 0 ) :
		
	
		$oQuery ="
				
				SELECT 
				DATE_FORMAT( mt.msg_time, '%d.%m.%Y %H:%i:%s' ) 	AS aTime,
				mt.msg_time	AS	mTime,
				o.id	AS	oID	    ,
				o.num	AS	oNum	, 
				o.name	AS	oName	, 
				mt.msg	AS	nMsg
				
				 
				FROM objects o
				LEFT JOIN messages m ON o.id = m.id_obj
				LEFT JOIN signals s  ON s.id = m.id_sig
				LEFT JOIN $mTable mt ON m.id = mt.id_msg
				
				WHERE o.num = '". $oNum ."' AND o.id_status != 2 AND s.play_alarm = 2
	
				ORDER BY mt.id DESC
				LIMIT 5 ";
		
		$oResult = mysqli_query( $db_sod, $oQuery ) or die( "Error: ".$oQuery );
		//echo $oQuery;
		echo "<table border='1' style='width: 100%; border-color: #333333;'>
				<tr>
					<th>T</th>
					<th>Msg</th>
					<th>N:</th>
					<th>Object</th>
					<th>Patrul</th>
				</tr>";
		
		while( $oRow = mysqli_fetch_assoc( $oResult ) ) {
			
		$oID	= $oRow['oID'];
		$cName	= $oRow['oNum'	]." - ". $oRow['oName'	] ;
		$mTime	= isset( $oRow['mTime'] ) ? $oRow['mTime'] : 'NOW()';
		
		  echo "<tr>";
		  echo "<td> " . $oRow['aTime']	. " </td>";
		  echo "<td> " . $oRow['nMsg' ]  . " </td>";
		  echo "<td> " . $oRow['oNum' ]	. " </td>";
		  echo "<td> " . $oRow['oName']  . " </td>";
		  echo "<td> <a style='font-size: 12px; color: #00cc00;' href='./ajax_scripts/get_object.php?patrul=1&oID=".$oID."&cName=".$cName."&mTime=".$mTime."'>[ 1 ]</а> 		   </td>";
		  echo "</tr>";
		  
		}
		
		echo "</table>";
		
	//	mysql_close($con);
	
	elseif( isset( $patrul ) && $patrul != 0 ) :
		
		$aQuery  = "INSERT INTO sod.work_card_movement ( id_office, id_work_card, id_patrul, id_object, alarm_time, start_time, obj_name )
											 VALUES ( 1, 1, '".$patrul."', ".$oID.", ".$mTime.", NOW(), '".$cName."' )";
        //echo $aQuery;
        $aResult = mysqli_query( $db_sod, $aQuery ) or die( "Error: ".$aQuery );
		echo "<div style='height: 100%; width: 100%; background-color: #111111; text-align: center; color: #00cc00; font-size: 18px; padding: 40px 0 0 0;'>". iconv( 'utf-8', 'cp1251', ' АЛАРМАТА Е ДОБАВЕНА УСПЕШНО!' ) ." </div>";
		
	endif;

	
endif;
?>
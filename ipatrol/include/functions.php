<?php

if(!defined('INCLUDE_CHECK')) die('You are not allowed to execute this file directly');


function checkEmail($str)
{
	return preg_match("/^[\.A-z0-9_\-\+]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $str);
}


function send_mail($from,$to,$subject,$body)
{
	$headers = '';
	$headers .= "From: $from\n";
	$headers .= "Reply-to: $from\n";
	$headers .= "Return-Path: $from\n";
	$headers .= "Message-ID: <" . md5(uniqid(time())) . "@" . $_SERVER['SERVER_NAME'] . ">\n";
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Date: " . date('r', time()) . "\n";

	mail($to,$subject,$body,$headers);
}


function get_max_id_archiv () {

        global $db_sod;
        $mTable = "archiv_".date('Ym');

        $aQuery = " SELECT id FROM ".$mTable." WHERE 1 ORDER BY id DESC LIMIT 1";
        $aResult = mysqli_query( $db_sod, $aQuery ) or die( print "ВЪЗНИКНА ГРЕШКА ПРИ ОПИТ ЗА ЗАПИС! ОПИТАЙТЕ ПО–КЪСНО!".$aQuery );

        while( $aRow = mysqli_fetch_assoc( $aResult ) ) {
            $aID	= isset( $aRow['id'] ) ? $aRow['id'] : 0 ;
        }

        return array( $aID , $mTable );

}

function update_alert_time( $person )
{
    global $db_sod;

    $aQuery  = "INSERT INTO work_card_person_alert ( id_person, alert_time ) VALUES ( $person, NOW() ) ON DUPLICATE KEY UPDATE alert_time = NOW() ";
    $aResult = mysqli_query( $db_sod, $aQuery ) or die( print "ВЪЗНИКНА ГРЕШКА ПРИ ОПИТ ЗА ЗАПИС! ОПИТАЙТЕ ПО–КЪСНО!".$aQuery );
}

/*
 * Намираме всички събития на обекта възникнали след подадената аларма.
 */


function getCountOpenedObjects()
{
    global $db_sod, $aReturn;

    $aQuery  = "
                SELECT
                    COUNT(DISTINCT(o.id)) AS 'BR'
                FROM objects o
                JOIN messages m ON m.id_obj = o.id AND m.to_arc = 0 AND m.flag = 1 AND m.id_cid BETWEEN 400 AND 500
                WHERE
                    o.id_status IN( 1, 14 )
                    AND CONCAT( DATE_FORMAT(NOW(),'%Y-%m-%d '), o.work_time_alert ) < NOW()
                    AND o.work_time_alert != '00:00:00'
                GROUP BY o.id_status ";
    $aResult = mysqli_query( $db_sod, $aQuery ) or die( print "ВЪЗНИКНА ГРЕШКА ПРИ ОПИТ ЗА ЗАПИС! ОПИТАЙТЕ ПО–КЪСНО!".$aQuery );

    while( $aRow = mysqli_fetch_assoc( $aResult ) ) {
        $aBR	= isset( $aRow['BR'] ) ? $aRow['BR'] : 0 ;

        if( $aBR != 0 ) $aReturn = "<span class='badge bg-red'> ".$aBR." </span>";

    }

    echo $aReturn;
}

/*
 * $zTime - Time of alarm
 * $ListSize - Time interval - Back from alarm
 * $ListLimit - row limit for result
 *
 */

function get_object_archiv( $oRec, $sID, $oNum, $zTime, $ListSize, $ListLimit, $oLan, $oLat ) {

    global $db_sod;
    $archiv_rows = "";
    $mTable = "archiv_".date('Ym');

    $oQuery = "
            SELECT id, msg_time, num, msg, alarm 
            FROM ".$mTable." 
            WHERE 
                    num = '".$oNum."' 
                AND id_receiver = '".$oRec."' 
                AND ( status NOT IN(602,611) OR ( status IN(602,611) AND alarm = 1 ) ) 
                AND msg_time > DATE_ADD( '".$zTime."', INTERVAL -".$ListSize." minute ) 
            ORDER BY id DESC LIMIT ".$ListLimit." ";
    $oResult= mysqli_query( $db_sod, $oQuery ) OR die( "".$oQuery );
//echo $oQuery;
    while( $oRow = mysqli_fetch_assoc( $oResult ) ) {

        $mID	= isset( $oRow['id'		 ] ) ? $oRow['id'		 ] : 0 ;
        $mTime	= isset( $oRow['msg_time'] ) ? $oRow['msg_time'  ] : 0 ;
        $msg	= isset( $oRow['msg'     ] ) ? $oRow['msg'       ] : '';
        $alarm	= isset( $oRow['alarm'   ] ) ? $oRow['alarm'     ] : 0;

        $background	=	'';
        if( $mID == $sID ) {
            $background = ' style="background-color: #ee5555;" ';
        }

        $archiv_rows .= '<tr>
                            <td class="ui-widget-header" '.$background.' >
                                &nbsp; '. $mTime .' &nbsp;
                            </td>
                            <td class="ui-widget-header" colspan="2" '.$background.'>
                                &nbsp; &nbsp;'. $msg .'
                            </td>
                         </tr>';
    }

    echo $archiv_rows;
}


function get_object_faces( $oID ) {

    global $db_sod;

    $rows_faces = "";

    $oQuery = "SELECT name, phone, post  FROM faces WHERE id_obj = '".$oID."' AND to_arc = 0 ";
    $oResult= mysqli_query( $db_sod, $oQuery ) OR die( "".$oQuery );

    while( $oRow = mysqli_fetch_assoc( $oResult ) ) {

        $strName	= isset( $oRow['name'   ] ) ? $oRow['name'  ] : '';
        $strPhone	= isset( $oRow['phone'  ] ) ? $oRow['phone' ] : '';
        $strPost	= isset( $oRow['post'   ] ) ? $oRow['post'  ] : '';

        $rows_faces .= '<div style="font-size: 16px; color: #fefefe;">'. $strName .' ( '.$strPost.' ) - '. $strPhone .'</div>';

    }

    echo $rows_faces;
}

?>
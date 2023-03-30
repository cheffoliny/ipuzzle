<?php

if(!defined('INCLUDE_CHECK')) die('You are not allowed to execute this file directly');

/*
 * Get alarm Reasons
*/
function get_alarm_reasons()
{
	
	global $db_sod;
	
	$aQuery  = "SELECT id, name, is_patrul FROM alarm_reasons WHERE to_arc = 0 AND is_patrul = 1 ORDER BY is_patrul DESC,id ASC";
	$aResult = mysqli_query( $db_sod, $aQuery ) or die( print "ВЪЗНИКНА ГРЕШКА! ОПИТАЙТЕ ПО–КЪСНО!" );
	$n_aRows = mysqli_num_rows( $aResult );
	
    $gSelect = '';

	for ( $m = 0; $m < $n_aRows; $m++ ) {
	
		$aRow	= mysqli_fetch_assoc( $aResult );
		$rID	= $aRow['id'];
		$rName	= $aRow['name'];
		$rPatrul= $aRow['is_patrul'];

		if ( $rPatrul == 1 ) {
			$gSelect .= "<option value=". $rID ." > ".$rName ." </option>";
		} else {
			$gSelect .= "<option value=". $rID ." > ".$rName ." </option>";
		}
	
	}

	echo $gSelect;
	
}

function get_alarm_reasons2()
{

	global $db_sod;

	$aQuery  = "SELECT id, name, is_patrul FROM alarm_reasons WHERE to_arc = 0 AND is_patrul = 0 ORDER BY is_patrul DESC,id ASC";
	$aResult = mysqli_query( $db_sod, $aQuery ) or die( print "ВЪЗНИКНА ГРЕШКА! ОПИТАЙТЕ ПО–КЪСНО!" );
	$n_aRows = mysqli_num_rows( $aResult );

	$gSelect = '';

	for ( $m = 0; $m < $n_aRows; $m++ ) {

		$aRow	= mysqli_fetch_assoc( $aResult );
		$rID	= $aRow['id'];
		$rName	= $aRow['name'];
		$rPatrul= $aRow['is_patrul'];

		if ( $rPatrul == 1 ) {
			$gSelect .= "<option value=". $rID ." > ".$rName ." </option>";
		} else {
			$gSelect .= "<option value=". $rID ." > ".$rName ." </option>";
		}

	}

	echo $gSelect;

}

function update_alarm_status( $aID, $alarm_status, $idUser, $alarm_reason ) {
	
	global $db_sod;

	$alarm_status_user = substr_replace( $alarm_status, "_user", -5 );
	$aQuery  = "UPDATE work_card_movement SET ". $alarm_status ." = NOW(), ". $alarm_status_user."=". $idUser .", id_alarm_reasons = '". $alarm_reason ."', updated_user = ". $idUser ." WHERE id = ". $aID ." AND ". $alarm_status_user ." = 0 ";
    $aResult = mysqli_query( $db_sod, $aQuery ) or die( print "ВЪЗНИКНА ГРЕШКА ПРИ ОПИТ ЗА ЗАПИС! ОПИТАЙТЕ ПО–КЪСНО!" );

}


function getPersonNameByID( $pID ) {

    global $db_personnel;

    $aQuery  = "SELECT CONCAT( fname, ' ', lname ) AS pName FROM personnel WHERE id = ". $pID ." ";
    $aResult = mysqli_query( $db_personnel, $aQuery ) or die( print "ГРЕШКА...! ОПИТАЙТЕ ПО–КЪСНО!" );

    while( $aRow = mysqli_fetch_assoc( $aResult ) ) {

        $strName	= isset( $aRow['pName'] ) ? $aRow['pName'] : '';

    }

    return $strName;

}

?>
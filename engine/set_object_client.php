<?php
	//$oSalary = New DBBase( $db_personnel, 'salary' );
//	if ( !empty($_GET['id']) ) {
//		$arr = explode(',', $_GET['id']);
//		$nID = $arr[0];
//		$firm = $arr[1];
//	} else {
//		$nID = 0;
//		$firm = 0;
//	}
	
//	$id_person = ! empty( $_GET['id_person'] ) ? $_GET['id_person'] : 0;
//	$month = ! empty( $_GET['month'] ) ? $_GET['month'] : 0;
//	$year = ! empty( $_GET['year'] ) ? $_GET['year'] : 0;
//	$type = isset( $_GET['type'] ) ? $_GET['type'] : 0;
//	$id_office = 0; $id_object = 0;
//	
//	if ( strlen($month) < 2 ) $month = "0".$month;
//	
//	if ( $nID > 0 ) {
//		$oSalary->getRecord( $nID, $aData );
//		isset( $aData['id_office'] ) ? $id_office = $aData['id_office'] : $id_office = 0;
//		isset( $aData['id_object'] ) ? $id_object = $aData['id_object'] : $id_object = 0;
//		isset( $aData['is_earning'] ) ? $type = $aData['is_earning'] : $type = 0;
//	}

	$nID = isset($_GET['nID']) && is_numeric($_GET['nID']) ? $_GET['nID'] : 0;
	
	$template->assign('id', $nID );
//	$template->assign('id_person', $id_person );
//	$template->assign('id_office', $id_office );
//	$template->assign('id_object', $id_object );
//	$template->assign('id_firm', $firm );
//	$template->assign('month', $month );
//	$template->assign('year', $year );
//	$template->assign('type', $type );
?>
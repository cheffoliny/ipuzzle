<?php
	$oSalary = New DBBase( $db_personnel, 'salary' );
	if ( !empty($_GET['id']) ) {
		$arr = explode(',', $_GET['id']);
		$nID = $arr[0];
		$firm = $arr[1];
	} else {
		$nID = 0;
		$firm = 0;
	}
	
	$id_person 	= isset($_GET['id_person']) && !empty($_GET['id_person'] ) 	? $_GET['id_person'] 	: 0;
	$month 		= isset($_GET['month']) 	&& !empty($_GET['month'] ) 		? $_GET['month'] 		: 0;
	$year 		= isset($_GET['year']) 		&& !empty($_GET['year'] ) 		? $_GET['year'] 		: 0;
	$type 		= isset($_GET['type']) 		&& !empty($_GET['type'] ) 		? $_GET['type'] 		: 0;
	$refresh 	= isset($_GET['refresh']) 	&& !empty($_GET['refresh'] ) 	? $_GET['refresh'] 		: 0;
	$office 	= isset($_GET['office']) 	&& !empty($_GET['office'] ) 	? $_GET['office'] 		: 0;
	$firm 		= isset($_GET['firm']) 		&& !empty($_GET['firm'] ) 		? $_GET['firm'] 		: 0;
	$codeto		= isset($_GET['code']) 		&& !empty($_GET['code'] ) 		? $_GET['code'] 		: "";
	
	$id_office 	= 0; 
	$id_object 	= 0;
	
	if ( strlen($month) < 2 ) $month = "0".$month;
	
	if ( $nID > 0 ) {
		$oSalary->getRecord( $nID, $aData );
		isset( $aData['id_office'] ) ? $id_office = $aData['id_office'] : $id_office = 0;
		isset( $aData['id_object'] ) ? $id_object = $aData['id_object'] : $id_object = 0;
		isset( $aData['is_earning'] ) ? $type = $aData['is_earning'] : $type = 0;
	}
	
	$template->assign('id', 		$nID );
	$template->assign('id_person', 	$id_person );
	$template->assign('id_office', 	$id_office );
	$template->assign('id_object', 	$id_object );
	$template->assign('id_firm', 	$firm );
	$template->assign('month', 		$month );
	$template->assign('year', 		$year );
	$template->assign('type', 		$type );
	$template->assign("refresh", 	$refresh);
	$template->assign("office", 	$office);
	$template->assign("firm", 		$firm);
	$template->assign("codeto", 	$codeto);
?>
<?php
	$nDate = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
	
	$sMonth = isset($_GET['sMonth']) ? $_GET['sMonth'] : '';
	$sYear = isset($_GET['sYear']) ? $_GET['sYear'] : '';
	
	if ( empty($sMonth) ) {
		$template->assign("year", date('Y', $nDate) );
		$template->assign("month", date('m', $nDate) );
	} else {
		$template->assign("year", $sYear );
		$template->assign("month", $sMonth );	
	}
	
	//$nID = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
	$nID = isset($_GET['nID']) ? $_GET['nID'] : $_SESSION['userdata']['id_person'];
	$nIDLimitCard = isset($_GET['id_limit_card']) ? $_GET['id_limit_card'] : '0';
	
	$template->assign("nID", $nID);	
	$template->assign('nIDLimitCard',$nIDLimitCard);
	
	$oPerson = New DBBase( $db_personnel, 'personnel' );
	$data = array();
	
	if( $nResult = $oPerson->getRecordByField( $nID, 'id', $data ) != DBAPI_ERR_SUCCESS ) {
		return $nResult;
	}
	
	if ( !empty($data) ) {
		$person_name = 	$data['fname']." ".$data['mname']." ".$data['lname'];
		$template->assign("person_name", $person_name);
	}				
?>
<?php
$nID 		= !empty( $_GET['id'] ) 		? $_GET['id'] 		: 0;
$nIDObj 	= !empty( $_GET['idOldObj'] ) 	? $_GET['idOldObj'] : 0;
$bDis 	    = !empty( $_GET['dis'] ) 	    ? $_GET['dis']      : 0;
$right_edit = false;

if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
    if ( in_array( 'tech_planning_edit', $_SESSION['userdata']['access_right_levels']) ) {
        $right_edit = true;
    }
}

$nHaveChangePrior = 0;
if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
    if ( in_array( 'tech_planning', $_SESSION['userdata']['access_right_levels']) ) {
        $nHaveChangePrior = true;
    }
}

$nSetLimitTime = 0;
if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
    if ( in_array( 'tech_request_time_limit', $_SESSION['userdata']['access_right_levels']) ) {
        $nSetLimitTime = 1;
    }
}

$oDBTechRequests = new DBTechRequests();

$nType = $oDBTechRequests->select("
        SELECT
            tt.id,
            tt.description
        FROM tech_timing as tt
    ");

$aTechRequest = array();
if($nID) {
    $aTechRequest = $oDBTechRequests->getRecord($nID);
}

$template->assign("nID", 		$nID);
$template->assign("nIDObj", 	$nIDObj);
$template->assign("nType", 	    $nType);
$template->assign('right_edit', $right_edit);
$template->assign('aTechRequest', $aTechRequest);
//$template->assign('nHaveChangePrior', $nHaveChangePrior);
$template->assign('sRequestTime', date('H:i'));
$template->assign('sRequestDate', date('d.m.Y'));
$template->assign('bDis', $bDis);
$template->assign('nSetLimitTime', $nSetLimitTime);

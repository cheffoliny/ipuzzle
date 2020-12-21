<?php
	global $template;

	$view_object = false;
	$edit_object = false;
	
	$edit = array();
	$view = array();

	$nID = !empty($_GET['nID']) ? $_GET['nID'] : 0;

    $oObject = new DBObjectDuty();
    $oDBObjects = new DBObjects();
	$aObject = $oObject->getObjectName( $nID );

    if(isset($_GET['nID'])) {
        $nIDObj = $_GET['nID'];
        $isInService = $oDBObjects->getServiceStatus($nIDObj);
        $template->assign('isService', $isInService);
    }

	if ( !isset($aObject['num']) ) {
		$nID = 0;
	}
	
	$isSOD = isset($aObject['is_sod']) ? $aObject['is_sod'] : 0;
	$isFO = isset($aObject['is_fo']) ? $aObject['is_fo'] : 0;

    require_once('engine/object_tabs_rights.php');

    $bEditStatuses = in_array('edit_statuses',$_SESSION['userdata']['access_right_levels']) ? true : false;
    $template->assign('bEditStatuses',$bEditStatuses);


    $template->assign("edit", $edit);
	$template->assign("view", $view);
	$template->assign("cnt", count($view));
	

	$template->assign("edit_object", $edit_object);
	$template->assign("view_object", $view_object);
	
	$template->assign( "nID", $nID );
	$template->assign( "object", @$aObject['object'] );
	$template->assign( "num", @$aObject['num'] );
	$template->assign( "isSOD", $isSOD );
	$template->assign( "isFO", $isFO );
?>
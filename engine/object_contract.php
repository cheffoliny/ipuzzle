<?php
	global $template;

	$view_object = false;
	$edit_object = false;
	
	$edit = array();
	$view = array();

	$nID = !empty($_GET['nID']) ? $_GET['nID'] : 0;
	$mobile = !empty($_GET['mobile']) ? $_GET['mobile'] : 0;
	$template->assign("mobile", $mobile);

	$oObject = new DBObjectDuty();
	$aObject = $oObject->getObjectName( $nID );
	$aOld = $oObject->getObjectOLD( $nID );
	
	if ( !isset($aObject['num']) ) {
		$nID = 0;
	}
	
	$isSOD = isset($aObject['is_sod']) ? $aObject['is_sod'] : 0;
	$isFO = isset($aObject['is_fo']) ? $aObject['is_fo'] : 0;

	require_once('engine/object_tabs_rights.php');

	$template->assign("edit", $edit);
	$template->assign("view", $view);
	$template->assign("cnt", count($view));		
	
	$old = isset($aOld['id_oldobj']) && !empty($aOld['id_oldobj']) ? $aOld['id_oldobj'] : -1;
	
	$template->assign("edit_object", $edit_object);
	$template->assign("view_object", $view_object);

	$template->assign( "nID", $nID );
	$template->assign( "object", @$aObject['object'] );
	$template->assign( "num", @$aObject['num'] );
	$template->assign( "isSOD", $isSOD );
	$template->assign( "isFO", $isFO );
	$template->assign( "objOld", $old );	
?>
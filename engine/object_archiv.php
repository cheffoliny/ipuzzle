<?php
	global $template;

	$view_object = false;
	$edit_object = false;
	$edit		 = array();
	$view		 = array();

	$oObject	 = new DBObjectDuty();
	
	//$aOld = $oObject->getObjectOLD( $nID );
	
	$visited = 0;
	if ( isset($_GET['sAlarmType']) ) {
		$visited = $_GET['sAlarmType'] == "visited" ? 1 : 0;
	}
	
	if ( isset($_GET['oldOD']) ) {
		$aOld = !empty($_GET['oldOD']) ? $_GET['oldOD'] : 0;
		$nIDTmp = $oObject->getObjectNew( $aOld );
		$nID = isset($nIDTmp['id']) ? $nIDTmp['id'] : 0;

		if ( empty($nID) ) {
			print("<script>alert('Обекта от PowerLink не може да бъде намерен в Теленет!'); window.close();</script>");
			exit;
		}
	} else {
		$nID = !empty($_GET['nID']) ? $_GET['nID'] : 0;
		$aOld = $oObject->getObjectOLD( $nID );
	}

	$aObject = $oObject->getObjectName( $nID );
	//debug($aObject);
	//$nID = !empty($_GET['nID']) ? $_GET['nID'] : 0;
	$mobile = !empty($_GET['mobile']) ? $_GET['mobile'] : 0;
	$template->assign("mobile", $mobile);
	
	if ( !isset($aObject['num']) ) {
		$nID = 0;
	}
	
	$isSOD = isset($aObject['is_sod']) ? $aObject['is_sod'] : 0;
	$isFO = isset($aObject['is_fo']) ? $aObject['is_fo'] : 0;
	
	require_once('engine/object_tabs_rights.php');

	$date_now = date("d.m.Y");
	$time_now = date("H:i");

	$date_first = "01.".date("m.Y");
	
	
	
	/* Stanislav - Pri vikane ot "Rabotna karta - Dvijenie" setva datite ot neq spravka */
	
	$sDateFrom = isset($_GET['sDateFrom']) ? $_GET['sDateFrom'] : '';
	$sDateTo = isset($_GET['sDateTo']) ? $_GET['sDateTo'] : '';
	
	if(!empty($sDateFrom) && !empty($sDateTo)) {
		$date_first = $sDateFrom;
		$date_now = $sDateTo;
		$time_now = "23:59";
	}
	
	//----------------------------------------------------------------------------
	
	$template->assign("date_now", $date_now);
	$template->assign("time_now", $time_now);
	$template->assign("date_first", $date_first);	
	
	$template->assign("edit", $edit);
	$template->assign("view", $view);
	$template->assign("cnt", count($view));
	
	$old = isset($aOld['id_oldobj']) && !empty($aOld['id_oldobj']) ? $aOld['id_oldobj'] : -1;	
	
	$template->assign( "edit_object", $edit_object );
	$template->assign( "view_object", $view_object );

	$template->assign( "nID", $nID );
	$template->assign( "object", @$aObject['object'] );
	$template->assign( "num", @$aObject['num'] );
	$template->assign( "isSOD", $isSOD );
	$template->assign( "isFO", $isFO );
//	$template->assign( "objOld", $old );
	$template->assign( "visited", $visited );	
?>
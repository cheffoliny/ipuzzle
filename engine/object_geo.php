<?php
	global $template;
	
	$nID    = isset($_GET['nID'])? $_GET['nID']: 0;
	$mobile = !empty($_GET['mobile']) ? $_GET['mobile'] : 0;
	
	if(!empty($nID)) {
		$oDBObjects = new DBObjects();
		
		$aObject = $oDBObjects->getRecord($nID);
	}

	$isSOD  = isset($aObject['is_sod']) ? $aObject['is_sod'] : 0;
	$isFO   = isset($aObject['is_fo']) ? $aObject['is_fo'] : 0;
	
    $pov    = isset($aObject['geo_pov']) && !empty($aObject['geo_pov']) ? $aObject['geo_pov'] : json_encode(array());
    $pov    = json_encode(json_decode($pov, true));
    //var_dump($pov);
    //	$pov    = json_encode(json_decode($pov));
	//var_dump(json_encode(json_decode($pov)));
	require_once('engine/object_tabs_rights.php');

	$template->assign("edit", $edit);
	$template->assign("view", $view);
	$template->assign("cnt", count($view));	
	$template->assign("mobile", $mobile);

    $template->assign("nID", $nID);

    $template->assign( "object",    $aObject['object'] );
    $template->assign( "num",       $aObject['num'] );

    $template->assign('pov',$pov);
	$template->assign('aObject',$aObject);
	/* added by Me 25.09.2013 */
	$template->assign( "isSOD", $isSOD );
	$template->assign( "isFO", $isFO );
?>
<?php
	$nID = !empty( $_GET['nID'] ) ? $_GET['nID'] : 0;
	
	$oLock = new DBTechLimitCards();
	$aLock = array();
	$aWork = array();
	
	$aLock = $oLock->getStatus($nID);
	$aWork = $oLock->getWorkStatus($nID);

	//debug($aLock);
	
	if ( isset($aWork[0]['nTime']) && $aWork[0]['nTime'] > 9999 ) {
		$template->assign('work', 1 );
	} else {
		$template->assign('work', 0 );
	}
	
	if ( isset($aWork[0]['person']) ) {
		$template->assign('person', $aWork[0]['person'] );
	}

	if ( isset($aWork[0]['type']) ) {
		$template->assign('type', $aWork[0]['type'] );
	}
	
	$template->assign('lock', $aLock );
	$template->assign('nNum', zero_padding( $nID ) );
	$template->assign('nID', $nID );
?>
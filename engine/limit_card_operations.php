<?php
	$nID = ! empty( $_GET['nID'] ) ? $_GET['nID'] : 0;

	$oLock = new DBTechLimitCards();
	$aLock = array();
	
	$aLock = $oLock->getStatus($nID);
	$aWork = $oLock->getWorkStatus($nID);
	
	$template->assign('lock', $aLock );

	if ( isset($aWork[0]['nTime']) && $aWork[0]['nTime'] > 9999 ) {
		$template->assign('work', 1 );
	} else {
		$template->assign('work', 0 );
	}

	$template->assign('nNum', zero_padding( $nID ) );
	$template->assign('nID', $nID );

?>
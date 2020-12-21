<?php
	$nID = !empty( $_GET['nID'] ) ? $_GET['nID'] : 0;
	
	$oLock = new DBTechLimitCards();
	$aLock = array();
	
	$aLock = $oLock->getStatus($nID);
	$template->assign('lock', $aLock );
	
	$template->assign('nNum', zero_padding( $nID ) );
	$template->assign('nID', $nID );
?>
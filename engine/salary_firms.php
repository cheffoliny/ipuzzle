<?php
	$nDate = mktime(0, 0, 0, date("m")- 1, date("d"), date("Y"));
	
	$sMonth = isset($_GET['sMonth']) ? $_GET['sMonth'] : '';
	$sYear = isset($_GET['sYear']) ? $_GET['sYear'] : '';
	$nIDFirmFrom = isset($_GET['nIDFirmFrom']) ? $_GET['nIDFirmFrom'] : '';
	$nIDFirmTo = isset($_GET['nIDFirmTo']) ? $_GET['nIDFirmTo'] : '';
	
	$template->assign( 'nIDSelectFirmFrom', $nIDFirmFrom );
	$template->assign( 'nIDSelectFirmTo', $nIDFirmTo );
	
	
	if(empty($sMonth)) {
		$template->assign( 'year', date('Y', $nDate) );
		$template->assign( 'month', date('m', $nDate) );
	} else {
		$template->assign( 'year', $sYear );
		$template->assign( 'month', $sMonth );	
	}
?>
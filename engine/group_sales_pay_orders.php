<?php
	$sIDs 	= isset($_GET['id']) ? $_GET['id'] : "";
	$sBank 	= isset($_GET['bank']) ? $_GET['bank'] : "";
	
	$aData 	= array();
	$aIDs 	= array();
	$aData 	= explode(";;", $sIDs);
	
	foreach ( $aData as $val ) {
		$t = substr($val, 4, strlen($val) -5);	
		$aIDs[$t] = $t;
	}
	
	unset($val);
	
	if ( isset($_SESSION['sales_rows']) && !empty($_SESSION['sales_rows']) ) {
		foreach ( $_SESSION['sales_rows'] as $val ) {
			$aIDs[$val] = $val;
		}
	}

	$str = !empty($aIDs) ? implode(",", $aIDs) : "";

	$template->assign("sIDs",  $str);
	$template->assign("sBank", $sBank);
?>
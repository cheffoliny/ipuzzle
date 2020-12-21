<?php
	$nID = !empty( $_GET['nID'] ) ? $_GET['nID'] : 0;
	$nIDObj = !empty($_GET['nIDObj']) ? $_GET['nIDObj'] : 0;

	$template->assign("nIDObj", $nIDObj);
	$template->assign("nID", $nID);
?>
<?php
	$nID = !empty( $_GET['id'] ) ? $_GET['id'] : 0;
	$nIDObj = !empty( $_GET['obj'] ) ? $_GET['obj'] : 0;
	
	$template->assign("nID", $nID);
	$template->assign("nIDObj", $nIDObj);
?>
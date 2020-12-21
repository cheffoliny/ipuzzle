<?php

	$nID = !empty( $_GET['id'] ) ? $_GET['id'] : 0;
	$nType = !empty( $_GET['type'] ) ? $_GET['type'] : 0;
	
	$template->assign("nID", $nID);
	$template->assign("nType", $nType);
	
?>
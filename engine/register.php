<?php

	$nID = isset( $_GET['nID'] )? $_GET['nID'] : 0;
	
	$template->assign("nID",$nID);

	$nIDService = isset( $_GET['nIDService'] )? $_GET['nIDService'] : 0;
	$template->assign("nIDService",$nIDService);
	 	
?>
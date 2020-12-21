<?php
	$nID = !empty( $_GET['id'] ) && is_numeric( $_GET['id'] ) ? $_GET['id'] : 0;
	
	$template->assign("nID", $nID);
?>
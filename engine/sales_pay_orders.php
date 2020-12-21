<?php
	$sIDs 	= isset($_GET['id']) ? $_GET['id'] : "";
	$nDDS 	= isset($_GET['simplify']) ? $_GET['simplify'] : 0;
	
	$template->assign("sIDs", $sIDs);
	$template->assign("nDDS", $nDDS);
?>
<?php
	$nID = !empty( $_GET['nID'] ) ? $_GET['nID'] : 0;
	$nIDCard = !empty( $_GET['nIDCard'] ) ? $_GET['nIDCard'] : 0;
	
	$template->assign("nID", $nID);
	$template->assign("nIDCard", $nIDCard);
?>
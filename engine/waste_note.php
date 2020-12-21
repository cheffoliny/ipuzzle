<?php

	$nID = isset($_GET['id'])? $_GET['id']:'0';
	$nIDPPP = isset($_GET['id_ppp'])? $_GET['id_ppp']:'0';
	
	$template->assign("nID",$nID);
	$template->assign("nIDPPP",$nIDPPP);
?>
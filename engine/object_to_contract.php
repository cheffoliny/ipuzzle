<?php
	$nID = !empty( $_GET['id'] ) ? $_GET['id'] : 0;
	
	$oDBContracts = new DBContracts();
	$nNumContract = $oDBContracts -> getNumByID($nID);
	
	$template->assign("nNum",$nNumContract);
	$template->assign("nID", $nID);
?>
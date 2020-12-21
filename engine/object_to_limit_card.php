<?php
	$nID = !empty( $_GET['id'] ) ? $_GET['id'] : 0;
	$nIDLimitCard = !empty( $_GET['id_limit_card'] ) ? $_GET['id_limit_card'] : 0;
	
	$oDBContracts = new DBContracts();
	$nNumContract = $oDBContracts -> getNumByID($nID);
	
	$template->assign("nNum",$nNumContract);
	$template->assign("nID", $nID);
	$template->assign("nIDLimitCard",$nIDLimitCard);
?>
<?php

	$nIDLimitCard = isset($_GET['id_limit_card']) ? $_GET['id_limit_card'] : 0;
	
	$nIDLogPerson = !empty($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : '0';
 

	
	$template->assign('nIDLogPerson',$nIDLogPerson);

	if(!empty($nIDLimitCard)) {
		$oDBTechLimitCards = new DBTechLimitCards();
		$oDBTechRequests = new DBTechRequests();
		
		
		$aLimitCard = $oDBTechLimitCards -> getRecord($nIDLimitCard);
		$aRequest = $oDBTechRequests ->getRecord($aLimitCard['id_request']);
		
		$template->assign('nIDLimitCard',$nIDLimitCard);
		$template->assign('sLimitCardType',$aLimitCard['type']);
		$template->assign('sTechRequstType',$aRequest['tech_type']);
		$template->assign('sRealStart',$aLimitCard['real_start']);
		$template->assign('sRealEnd',$aLimitCard['real_end']);
	}
?>
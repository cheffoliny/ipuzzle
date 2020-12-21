<?php

	$nIDLimitCard = isset($_GET['id_limit_card']) ? $_GET['id_limit_card'] : '0';
	$nIDLogPerson = !empty($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : '0';
	
	$template->assign('nIDLimitCard',$nIDLimitCard);
	$template->assign('nIDLogPerson',$nIDLogPerson);
	
	if( !empty($nIDLimitCard) ) {
		
		$oDBTechLimitCards = new DBTechLimitCards();
		$oDBTechRequests = new DBTechRequests();		
			
		$nIDObject = $oDBTechLimitCards->getIDObject($nIDLimitCard);
		
		if( !empty($nIDObject ) ) {
			$aLimitCard = $oDBTechLimitCards->getInfoForPersonCard($nIDLimitCard);

		} else {
			$aLimitCard = $oDBTechLimitCards->getInfoForPersonCard2($nIDLimitCard);	
		}
		
		$aLimitCard['real_start'] = $aLimitCard['real_start'] != '00:00 00.00.0000' ? $aLimitCard['real_start'] : '';
		$aLimitCard['real_end'] = $aLimitCard['real_end'] != '00:00 00.00.0000' ? $aLimitCard['real_end'] : '';
		
		$aRequest = $oDBTechRequests -> getInfoForPersonCard($aLimitCard['id_request']);

		$nIDContract = !empty($aRequest['id_contract']) ? $aRequest['id_contract'] : 0;
		
		//print($nIDContract);
		
		if(!empty($nIDContract)) {
			$oDBContracts = new DBContracts();
			$aContract = $oDBContracts->getInfoForPersonCard($nIDContract);
			
			//Информация по електронния договор
			$template->assign('contract_num',$aContract['contract_num']);
			$template->assign('contract_date',$aContract['contract_date']);
			$template->assign('sContractNumAndData',zero_padding($aContract['contract_num']).' / '.$aContract['contract_date']);
			$template->assign('contract_rs',$aContract['rs_name'].' '.$aContract['rs_mobile']);
			
		} else {
			
			//Информация по Задачата
			$template->assign('id_request',$aLimitCard['id_request']);
			$template->assign('sRequestNumAndDate',zero_padding($aLimitCard['id_request']).' / '.$aRequest['created_time']);
			$template->assign('request_date',$aRequest['created_time']);
			$template->assign('sHoldupReason',$aRequest['holdup_reason']);
			$template->assign('sRequstInfo',$aRequest['note']);
		}
		
		//Информация по лимитната карта
		
		$template->assign('num','Лимитна карта № '.$aLimitCard['id']);
		$template->assign('type',$aLimitCard['type']);
		if(!empty($aLimitCard['id_object'])) {
			$template->assign('nIDObject',$aLimitCard['id_object']);
			$template->assign('nNumObject',$aLimitCard['num_object']);
		}
		//$template->assign('sObjName',$aLimitCard['obj_name']);
		//$template->assign('sObjAddress',$aLimitCard['obj_address']);
		//$template->assign('sPhone',$aLimitCard['phone']);
		//$template->assign('sMOL',$aLimitCard['face_name']." ".$aLimitCard['face_phone']);
		$template->assign('planned_start',$aLimitCard['planned_start']);
		$template->assign('planned_end',$aLimitCard['planned_end']);
		$template->assign('real_start',$aLimitCard['real_start']);
		$template->assign('real_end',$aLimitCard['real_end']);
		$template->assign('nIDContract',$nIDContract);
	}

?>
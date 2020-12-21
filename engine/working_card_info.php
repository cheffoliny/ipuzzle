<?php
	$right_edit = false;

	$aCurrentCard = array();
	$oCurrentCard = new DBWorkCard();
	
	$nIDCard = !empty( $_GET['nIDCard'] )? $_GET['nIDCard'] : 0;
	$entered = isset($_GET['entered']) && !empty( $_GET['entered'] )? $_GET['entered'] : 0;
	
	if ( empty($nIDCard) ) {
		$nIDCard = $oCurrentCard->getLastActiveWorkCard();
	}	
	
	if( !empty( $_GET['type'] ) && ($_GET['type'] == 'new') && empty($nIDCard) ) {
		$aCurrentCard['id_user'] 	= $_SESSION['userdata']['id_person'];
		$aCurrentCard['start_time'] = time();
		$aCurrentCard['id'] = 0;
		
		$oCurrentCard->update( $aCurrentCard );
		
		$aCurrentCard['num'] = $aCurrentCard['id'];
		
		$oCurrentCard->update( $aCurrentCard );
		
		$nIDCard = $aCurrentCard['id'];
		
	} else if ( empty($nIDCard) && empty($entered) ) {
		//print("<script>alert(window.location);</script>");
		print("<script>window.location='page.php?page=working_cards';</script>");
	}
	
	$test = $oCurrentCard->getWorkCardInfo( $nIDCard );
	
	$locked = isset($test['locked']) && empty($test['locked']) ? 0 : 1;
	$_GET['nIDCard'] = $nIDCard;
	
	$aData = array();
	$aData = $oCurrentCard->getWorkCardInfo( $nIDCard );
	$sDispatcher = isset($aData['dispatcher']) ? $aData['dispatcher'] : '';
	$sFrom		 = isset($aData['startTime']) ? $aData['startTime'] : '';
	$sTo 		= 	isset($aData['endTime']) ? $aData['endTime'] : '';
	   
	$logged = $_SESSION['userdata']['id_person'];
	$owner = isset($aData['id_user']) ? $aData['id_user'] : '0';
	
	if ($logged == $owner ) {
		$right_edit = true;
	}
	
	if ( in_array('work_card_close', $_SESSION['userdata']['access_right_levels']) && empty($sTo) ) {
		$right_close = true;
	} else {
		$right_close = false;
	}
	
	$template->assign('nIDCard', $nIDCard );
	$template->assign('sDispName',$sDispatcher);
	$template->assign('sFrom',$sFrom);
	$template->assign('sTo',$sTo);
	$template->assign('locked', $locked );
	$template->assign('right_edit', $right_edit );
	$template->assign('right_close',$right_close);
	
?>
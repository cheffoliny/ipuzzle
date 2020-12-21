<?php
	$nIDCard = !empty( $_GET['nIDCard'] ) ? $_GET['nIDCard'] : 0;

	$right_edit = false;
	
	$aCurrentCard = array();
	$oCurrentCard = new DBWorkCard();
	
	if ( empty($nIDCard) ) {
		$nIDCard = $oCurrentCard->getLastUncompliteWorkCard();
	}
	
	$aData = array();
	$aData = $oCurrentCard->getWorkCardInfo( $nIDCard );

	$logged = $_SESSION['userdata']['id_person'];
	$owner = isset($aData['id_user']) ? $aData['id_user'] : 0;
		
	if ($logged == $owner ) {
		$right_edit = true;
	}

	$aCurrentCard = $oCurrentCard->getWorkCardInfo( $nIDCard );
	$locked = isset($aCurrentCard['locked']) && empty($aCurrentCard['locked']) ? 0 : 1;
	
	$template->assign('locked', $locked );
	$template->assign('nIDCard', $nIDCard );
	$template->assign('right_edit', $right_edit );
?>
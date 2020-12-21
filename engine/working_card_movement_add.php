<?php
	$nIDCard = !empty( $_GET['nIDCard'] ) ? $_GET['nIDCard'] : 0;
	
	$right_edit = false;
	
	if (!empty($_SESSION['userdata']['access_right_levels'])) {
		if ( in_array('working_card_movement', $_SESSION['userdata']['access_right_levels']) ) {
			$right_edit = true;
		}
	}

	$aCurrentCard = array();
	$oCurrentCard = new DBWorkCard();
	
	if (empty($nIDCard)) {
		$nIDCard = $oCurrentCard->getLastUncompliteWorkCard();
	}

	$aCurrentCard = $oCurrentCard->getWorkCardInfo( $nIDCard );
	$locked = isset($aCurrentCard['locked']) && empty($aCurrentCard['locked']) ? 0 : 1;
	
	$template->assign('locked', $locked );		
	$template->assign('right_edit', $right_edit );
	$template->assign('nIDCard', $nIDCard );
?>
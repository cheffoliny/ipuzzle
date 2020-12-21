<?php

	$aCurrentCard = array();
	$oCurrentCard = new DBWorkCard();
	
	$nIDCard = !empty( $_GET['nIDCard'] )? $_GET['nIDCard'] : 0;
	
	if( empty( $nIDCard ) )
	{
//		$nIDCard = $oCurrentCard->getLastActiveWorkCard();
		$nIDCard = $oCurrentCard->getLastUncompliteWorkCard();
	}
	
	if( !empty( $_GET['type'] ) && ($_GET['type'] == 'new') && empty( $nIDCard ) )
	{
		$aCurrentCard['id_user'] 	= $_SESSION['userdata']['id_person'];
		$aCurrentCard['start_time'] = time();
		$aCurrentCard['id'] = 0;
		
		$oCurrentCard->update( $aCurrentCard );
		
		$aCurrentCard['num'] = $aCurrentCard['id'];
		
		$oCurrentCard->update( $aCurrentCard );
		
		$nIDCard = $aCurrentCard['id'];
	}
	
	$_GET['nIDCard'] = $nIDCard;
	$template->assign('nIDCard', $nIDCard );

?>
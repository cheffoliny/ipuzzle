<?php
	$nIDCard = !empty( $_GET['nIDCard'] ) ? $_GET['nIDCard'] : 0;
	$nID = !empty( $_GET['nID'] ) ? $_GET['nID'] : 0;

	$right_edit = false;
	
	if (!empty($_SESSION['userdata']['access_right_levels'])) {
		if ( in_array('working_card_patrol', $_SESSION['userdata']['access_right_levels']) ) {
			$right_edit = true;
		}
	}
	
	$template->assign('right_edit', $right_edit );	
	$template->assign('nIDCard', $nIDCard );
	$template->assign('nID', $nID );
?>
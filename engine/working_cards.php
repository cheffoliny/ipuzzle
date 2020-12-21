<?php
	$right_edit = false;

	$nIDCard = !empty( $_GET['nIDCard'] ) ? $_GET['nIDCard'] : 0;
	
	if (!empty($_SESSION['userdata']['access_right_levels'])) {
		if ( in_array('edit_working_cards', $_SESSION['userdata']['access_right_levels']) ) {
			$right_edit = true;
		}
	}
	
	$nSevenDaysBefore = strtotime("-7 days");
	$sSevenDaysBefore = date("d.m.Y",$nSevenDaysBefore);
	
	$template->assign('sSevenDaysBefore',$sSevenDaysBefore);
	$template->assign('nIDCard', $nIDCard );
	$template->assign('right_edit', $right_edit );
?>
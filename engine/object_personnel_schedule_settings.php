<?php
	$right_edit = false;
	
	if (!empty($_SESSION['userdata']['access_right_levels'])) {
		if ( in_array('object_personnel_schedule_settings', $_SESSION['userdata']['access_right_levels']) ) {
			$right_edit = true;
		}
	}
	
	$template->assign('right_edit', $right_edit );
?>
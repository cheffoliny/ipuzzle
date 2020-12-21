<?php
	$auto_schedule = false;

	if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
		// Бутон АВТОМАТИЧНИ смени
		if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
			if ( in_array('auto_schedule', $_SESSION['userdata']['access_right_levels']) ) {
				$auto_schedule = true;
			}
		}
	}
	
	$template->assign("auto_schedule", $auto_schedule);
?>
<?php
	$right_access = false;
	
	if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
		// Достъп до справката
		if ( in_array("budget", $_SESSION['userdata']['access_right_levels']) ) {
			$right_access = true;
		}
	}
	
	$template->assign("right_access", $right_access);
?>
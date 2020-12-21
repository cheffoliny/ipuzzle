<?php
	$rights = false;
	
	if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
		if ( in_array('books', $_SESSION['userdata']['access_right_levels']) ) {
			$rights = true;
		}		
	}
	
	$template->assign("rights", $rights);
?>
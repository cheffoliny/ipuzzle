<?php
	$invoicement = false;
	
	if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
		if ( in_array('invoicement', $_SESSION['userdata']['access_right_levels']) ) {
			$invoicement = true;
		}		
	}
	
	$template->assign("invoicement", $invoicement);
?>
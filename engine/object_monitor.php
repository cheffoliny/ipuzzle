<?php
	$monitor_view = false;
	
	if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
		if ( in_array('monitor_view', $_SESSION['userdata']['access_right_levels']) ) {
			$monitor_view = true;
		}
	}
	
	$nID = !empty($_GET['id']) ? $_GET['id'] : 0;
	
	$template->assign("nID", $nID);
	$template->assign( 'monitor_view', $monitor_view );
?>
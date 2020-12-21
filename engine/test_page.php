<?php
	$monitor_view = false;
	
	if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
		if ( in_array('monitor_view', $_SESSION['userdata']['access_right_levels']) ) {
			$monitor_view = true;
		}
	}

	$nID = !empty($_GET['nID']) ? $_GET['nID'] : 0;
	
	$template->assign("monitor_view", $monitor_view);

	$template->assign("nID", $nID);
	$template->assign( "object", @$aObject['object'] );
?>
<?php
	$right_edit = false;
	
	if (!empty($_SESSION['userdata']['access_right_levels'])) {
		if ( in_array('schedule_hours', $_SESSION['userdata']['access_right_levels']) ) {
			$right_edit = true;
		}
	}
	
	$nIDFirm   = isset($_GET['nIDFirm'])   && is_numeric($_GET['nIDFirm'])   ? $_GET['nIDFirm']   : 0;
	$nIDOffice = isset($_GET['nIDOffice']) && is_numeric($_GET['nIDOffice']) ? $_GET['nIDOffice'] : 0;
	$nIDObject = isset($_GET['nIDObject']) && is_numeric($_GET['nIDObject']) ? $_GET['nIDObject'] : 0;
	
	$template->assign('right_edit', $right_edit );
	
	$template->assign('nIDFirm'   , $nIDFirm );
	$template->assign('nIDOffice' , $nIDOffice );
	$template->assign('nIDObject' , $nIDObject );
?>
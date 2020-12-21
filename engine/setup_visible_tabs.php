<?php
	$right_private = false;
	$right_office = false;

	if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
		if ( in_array('personInfo_view', $_SESSION['userdata']['access_right_levels']) ) {
			$right_private = true;
		}
	}

	if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
		if ( in_array('person_data_view', $_SESSION['userdata']['access_right_levels']) ) {
			$right_office = true;
		}
	}
	
	$nID = ! empty( $_GET['id'] ) ? $_GET['id'] : 0;
	$template->assign('nID', $nID );
	$template->assign('right_private', $right_private );
	$template->assign('right_office', $right_office );
?>
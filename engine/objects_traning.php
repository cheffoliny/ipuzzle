<?php

	if ( !isset($_POST['nIDOffice']) ) {
		$office = -1;
	} else {
		$office = $_POST['nIDOffice'];
	}

	$template->assign('office',$office);
?>

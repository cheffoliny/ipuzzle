<?php

	$nID = 		!empty( $_GET['id'] ) ? $_GET['id'] : 0;
	$nIDCity = 	!empty( $_GET['id_city'] ) ? $_GET['id_city'] : 0;
	
	$template->assign( "nID", $nID );
	$template->assign( "nIDCity", $nIDCity );
	
?>
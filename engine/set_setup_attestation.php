<?php

	$nID = 			!empty( $_GET['id'] ) ? $_GET['id'] : 0;
	$nIDPerson = 	!empty( $_GET['person'] ) ? $_GET['person'] : 0;
	
	$template->assign( "nID", $nID );
	$template->assign( "nIDPerson", $nIDPerson );
	
	
	
	$first_day_of_month = date("\\01.m.Y");
	
	$template->assign( "first_day_of_month", $first_day_of_month);

?>
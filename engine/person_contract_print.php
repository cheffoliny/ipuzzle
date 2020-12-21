<?php

	$nType = 	isset( $_GET['type'] ) 	? 	$_GET['type'] 	: 0;
	$nID = 		isset( $_GET['id'] ) 	? 	$_GET['id'] 	: 0;
	
	$template->assign( "nType", $nType );
	$template->assign( "nID", $nID );

?>
<?php

	$nID = isset( $_GET['id'] ) ? $_GET['id'] : 0;
	
	$template->assign( "nID", $nID );

?>
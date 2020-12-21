<?php
	$nID = !empty( $_GET['nID'] ) ? $_GET['nID'] : 0;
	
	$template->assign( "nID", $nID );
?>
<?php

	$nID = 			! empty( $_GET['id'] ) 			? $_GET['id'] 			: 0;
	$nIDRequest = 	! empty( $_GET['id_request'] ) 	? $_GET['id_request'] 	: 0;
	
	$template->assign('nID', $nID );
	$template->assign('nIDRequest', $nIDRequest );

?>
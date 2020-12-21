<?php

	$nID = 			!empty( $_GET['id'] ) 			? $_GET['id'] 			: 0;
	$nIDPPP = 		!empty( $_GET['id_ppp'] ) 		? $_GET['id_ppp'] 		: 0;
	$nCallerID = 	!empty( $_GET['id_caller'] )	? $_GET['id_caller']	: 0;
	
	$template->assign( 'nID', $nID );
	$template->assign( 'nIDPPP', $nIDPPP );
	$template->assign( 'nCallerID', $nCallerID );

?>
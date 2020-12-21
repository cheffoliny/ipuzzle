<?php

	$nID = !empty( $_GET['id'] ) ? $_GET['id'] : '';
	$nIDObject = !empty( $_GET['id_object'] ) ? $_GET['id_object'] : 0;
	$nSetStorage = !empty( $_GET['setstorage'] ) ? $_GET['setstorage'] : 0;
	$nIDLimitCard = !empty( $_GET['id_limit_card'] ) ? $_GET['id_limit_card'] : 0;
	
	$template->assign( 'nID', $nID );
	$template->assign( 'nIDObject', $nIDObject );
	$template->assign( 'nSetStorage', $nSetStorage );
	$template->assign( 'nIDLimitCard', $nIDLimitCard );

?>
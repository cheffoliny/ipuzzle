<?php

	$nID = !empty( $_GET['id'] ) ? $_GET['id'] : 0;
	$nIDFirm = !empty( $_GET['id_f'] ) ? $_GET['id_f'] : 0;
	
	$template->assign( 'id', $nID );
	$template->assign( 'id_f', $nIDFirm );

?>
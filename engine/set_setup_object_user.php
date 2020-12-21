<?php

$nID       =    !empty( $_GET['nID'] ) ? $_GET['nID'] : 0;
$nIDObject = 	!empty( $_GET['nIDObject'] ) ? $_GET['nIDObject'] : 0;

$template->assign( "nID", $nID );
$template->assign( "nIDObject", $nIDObject );

?>
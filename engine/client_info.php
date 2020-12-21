<?php
//    global $template;

	$nID    = !empty( $_GET['id']   ) ? $_GET['id']     : 0;

    $oDBClients = new DBClients();
    $cName = $oDBClients->getClientName( $nID );

	$template->assign( "nID", $nID );
    $template->assign( "client", $cName['client'] );

?>
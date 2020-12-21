<?php

	$nID = !empty( $_GET['id'] ) ? $_GET['id'] : 0;
	
	$oTechTiming = new DBTechTiming();
	$sType = $oTechTiming->getType( $nID, false );
	$sLatinType = $oTechTiming->getType( $nID, true );
	
	$template->assign( 'nID', $nID );
	$template->assign( 'sType', $sType );
	$template->assign( 'sLatinType', $sLatinType );

?>
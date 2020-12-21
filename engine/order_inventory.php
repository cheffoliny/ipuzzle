<?php
	$nID 		= !empty( $_GET['id'] ) ? $_GET['id'] : 0;
	
	$oOrders 	= new DBOrders();
	$sType 		= "";
	$nNum 		= 0;
	$sOrderDate = "";
	$sDocType 	= "";
	$nIDDoc 	= 0;
	$nOrderSum 	= 0;
	$aOrder 	= array();
	$grant_right= false;
		
	if ( !empty($nID) ) {
		$oOrders->getRecord( $nID, $aOrder );
		
		if ( !empty($aOrder) ) {
			$sType 		= ( $aOrder['doc_type'] == "buy" ) 	? "Разходен" 				: "Приходен";
			$status 	= isset($aOrder['order_status'])	? $aOrder['order_status'] 	: "active";
			$nNum 		= LPAD( $aOrder['num'], 10, 0 );
			$sOrderDate = date( "d.m.Y H:i:s", strtotime($aOrder['order_date']) );
			$sDocType 	= $aOrder['doc_type'];
			$nIDDoc 	= $aOrder['doc_id'];
		}
	}
	
	// Права за достъп
	if ( in_array('orders_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
		$grant_right 	= true;
	}	
	
	$template->assign( "nID", 			$nID );
	$template->assign( "sDocType", 		$sDocType );
	$template->assign( "nIDDoc", 		$nIDDoc );
	$template->assign( "sOrderType", 	$sType );
	$template->assign( "nOrderNum", 	$nNum );
	$template->assign( "sOrderDate",	$sOrderDate );
	$template->assign( "grant_right",	$grant_right );
	$template->assign( "status",		$status );
?>
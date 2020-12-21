<?php
	$oOrders 	= new DBOrders();
	$oSalesDocs = new DBSalesDocs();
	$oBuyDocs 	= new DBBuyDocs();
	$oSystem 	= new DBSystem();
	// Pavel
	$aOrder		= array();
	$sDocType	= "sale";
	$nIDDoc		= 0;
	$grant_right= false;
	// pavel
	
	$nID = isset( $_GET['id'] ) ? $_GET['id'] : 0;
	
	// Pavel
	$oOrders->getRecord($nID, $aOrder);
	
	if ( !empty($aOrder) ) {
		$sDocType 	= isset($aOrder['doc_type']) 		? $aOrder['doc_type'] 		: "sale";
		$nIDDoc 	= isset($aOrder['doc_id']) 			? $aOrder['doc_id'] 		: 0;
		$status 	= isset($aOrder['order_status'])	? $aOrder['order_status'] 	: "active";
	}
	
	//Caption Info Initialize
	$sOrderTypeCaption = ( $sDocType == "buy" ) ? "Разходен" : "Приходен";
	
	$aSystemData = array();
	$aSystemData = $oSystem->getRow();
	$nOrderNum = isset( $aSystemData['last_num_order'] ) ? $aSystemData['last_num_order'] + 1 : 0;
	$nOrderNum = LPAD( $nOrderNum, 10, 0 );
	
	$sOrderDate = '( Непотвърден )';
	//End Caption Info Initialize
	
	if( !empty( $nID ) )
	{
		$aOrder = array();
		
		$oOrders->getRecord( $nID, $aOrder );
		
		if( !empty( $aOrder ) )
		{
			$sOrderType 	= $aOrder['order_type'];
			$sDocType 		= $aOrder['doc_type'];
			$nIDDoc 		= $aOrder['doc_id'];
			$sAccountType 	= $aOrder['account_type'];
			
			//Caption Info
			$nOrderNum = LPAD( $aOrder['num'], 10, 0 );
			
			$sOrderTypeCaption = ( $aOrder['doc_type'] == "buy" ) ? "Разходен" : "Приходен";
			
			$sOrderDate = date( "d.m.Y H:i:s", strtotime($aOrder['order_date']) );
			//End Caption Info
		}
	}
	else
	{
		//Get Document Information
		$aDocument = array();
		switch( $sDocType )
		{
			case "sale":
				$oSalesDocs->getRecord( $nIDDoc, $aDocument );
				break;
			
			case "buy":
				$oBuyDocs->getRecord( $nIDDoc, $aDocument );
				break;
		}
		
		$sAccountType = ( isset( $aDocument['paid_type'] ) && $aDocument['paid_type'] == "cash" ) ? "person" : "bank";
		//End Get Document Information
	}
	
	//Right To See
	$bView = false;
	
	if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
	{
		if( in_array( 'view_firm_balances', $_SESSION['userdata']['access_right_levels'] ) )
		{
			$bView = true;
		}
	}
	//End Right To See
	
	// Права за достъп
	if ( in_array('orders_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
		$grant_right 	= true;
	}	
	
	$template->assign( "nID", 			$nID );
	$template->assign( "sOrderType", 	$sDocType );
	$template->assign( "sDocType", 		$sDocType );
	$template->assign( "nIDDoc", 		$nIDDoc );
	$template->assign( "sAccountType", 	$sAccountType );
	$template->assign( "sOrderTypeCaption", $sOrderTypeCaption );
	$template->assign( "nOrderNum", 	$nOrderNum );
	$template->assign( "sOrderDate", 	$sOrderDate );
	$template->assign( "bView", 		$bView );
	$template->assign( "grant_right",	$grant_right );
	$template->assign( "status",		$status );
?>
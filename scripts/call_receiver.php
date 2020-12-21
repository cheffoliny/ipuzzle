<?php

	$nErrorReporting = error_reporting( 0 );
	
	require_once( "../config/session.inc.php" );
	require_once( "config/function.autoload.php" );
	require_once( "config/connect.inc.php" );
	
	if( !isset( $_SESSION['userdata'] ) )
	{
		error_reporting( $nErrorReporting );
		echo "";
		die();
	}
	
	$nIDPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
	
	if( empty( $nIDPerson ) )
	{
		error_reporting( $nErrorReporting );
		echo "";
		die();
	}
	
	$right_edit = false;
	if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
		if( in_array( 'call_receiver', $_SESSION['userdata']['access_right_levels'] ) )
		{
			$right_edit = true;
		}
	
	if( !$right_edit )
	{
		error_reporting( $nErrorReporting );
		echo "";
		die();
	}
	
	$oDBPhones = new DBPhones();
	
	$aCallerID = $oDBPhones->checkUnprocessed( $nIDPerson );
	
	if( !empty( $aCallerID ) && isset( $aCallerID['caller_id'] ) && isset( $aCallerID['id'] ) )
	{
		$aData = $oDBPhones->errorProofGetRecord( $aCallerID['id'] );
		if( !empty( $aData ) )
		{
			$aData['eol_processed'] = 1;
			$nResult = $oDBPhones->update( $aData );
			//if( $nResult != DBAPI_ERR_SUCCESS ) { error_reporting( $nErrorReporting ); echo ""; die(); }
		}
		else { error_reporting( $nErrorReporting ); echo ""; die(); }
		
		$sCallerInfo = implode( "|", $aCallerID );
		
		error_reporting( $nErrorReporting );
		echo $sCallerInfo;
	}
	else
	{
		error_reporting( $nErrorReporting );
		echo "";
	}

?>
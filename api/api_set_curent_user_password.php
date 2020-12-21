<?php
	
	$oAccess = New DBBase( $db_system, 'access_account' );
	
	$nRequestUser = $_SESSION['userdata']['id'];
	
	if( empty( $nRequestUser ) )
	{
		$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Грешка при определяне на потребителя!", __FILE__, __LINE__ );
		print( $oResponse->toXML() );
		exit;
	}
	
	if( empty( $aParams['password'] ) || empty( $aParams['new_password'] ) )
	{
		$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Не сте въвели парола!", __FILE__, __LINE__ );
		print( $oResponse->toXML() );
		exit;
	}
	
	$oAccess->getRecord( $nRequestUser, $aUserData );
	if( !empty( $aUserData ) && isset( $aUserData['password'] ) )
	{
		if( MD5( $aParams['password'] ) != $aUserData['password'] )
		{
			$oResponse->setFormElement( "form1", "password", array( "value" => "" ), "" );
			
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Неправилна парола!", __FILE__, __LINE__ );
			print( $oResponse->toXML() );
			exit;
		}
	}
	
	if( $aParams['new_password'] != $aParams['confirm_password'] )
	{
		$oResponse->setFormElement( "form1", "new_password", array( "value" => "" ), "" );
		$oResponse->setFormElement( "form1", "confirm_password", array( "value" => "" ), "" );
		
		$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Паролите се различават!", __FILE__, __LINE__ );
		print( $oResponse->toXML() );
		exit;
	}
	
	$aPerson = array();
	$aPerson['id'] 			= $nRequestUser;
	$aPerson['password'] 	= MD5( $aParams['new_password'] );
	
	if( $nResult = $oAccess->update( $aPerson ) != DBAPI_ERR_SUCCESS )
	{
		$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
		return $nResult;
	}
	
	print( $oResponse->toXML() );

?>
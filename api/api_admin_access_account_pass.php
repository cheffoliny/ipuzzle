<?php		
	$oAccess = New DBBase( $db_system, 'access_account' );
	
	if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
		if (in_array('access_levels_edit', $_SESSION['userdata']['access_right_levels'])) {
			$right_edit = true;
		}
	}
	
	if ( $right_edit ) {
		if ( empty($aParams['id']) ) {
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Грешка при определянето на клиента!!!", __FILE__, __LINE__ );
			print( $oResponse->toXML() );
			exit;
		}

		if ( empty($aParams['password']) || ($aParams['password'] != $aParams['confirm_password']) ) {
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Не сте въвели парола или паролите се различават!", __FILE__, __LINE__ );
			print( $oResponse->toXML() );
			exit;
		}
		
		$aPerson = array();
		$aPerson['id'] 			= $aParams['id'];
		$aPerson['password'] 	= MD5($aParams['password']);

		if( $nResult = $oAccess->update( $aPerson ) != DBAPI_ERR_SUCCESS ) {
			$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
			return $nResult;
		}

		print( $oResponse->toXML() );
	}
?>
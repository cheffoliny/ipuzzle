<?php
	$oAccess = New DBBase( $db_system, 'access_account' );
	$oPerson = New DBBase( $db_personnel, 'personnel' );

	$id = isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : 0;
	$aData = array();
	
	if ( $id > 0 ) {
		if( $nResult = $oAccess->getRecord( $id, $aData ) != DBAPI_ERR_SUCCESS ) {
			$oResponse->setError( $nResult, "Проблем при комуникацията!", __FILE__, __LINE__ );
			return $nResult;
		}
		
		if ( isset($aData['id_person']) && !empty($aData['id_person']) ) {
			if( $nResult = $oPerson->getRecord( $aData['id_person'], $aPerson ) != DBAPI_ERR_SUCCESS ) {
				$oResponse->setError( $nResult, "Проблем при комуникацията!", __FILE__, __LINE__ );
				return $nResult;
			}
			
			if( isset($aPerson['fname']) ) {
				$aData['name']= $aPerson['fname']." ".$aPerson['mname']." ".$aPerson['lname'];
			}
		}
		$template->assign("data",$aData);
	}
?>
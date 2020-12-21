<?php

	$oOrders = New DBBase( $db_personnel, 'client_orders' );

	switch( $aParams['api_action'])
	{
		case "save" : 
			saveOrder( $aParams );
		break;
		
		default : 
			loadOrder( $aParams['id'] );
		break;
	}
	
	function saveOrder( $aParams )
	{
		global $oOrders, $oResponse;
		
		if( $aParams['type'] == 'констатация' )
			if( empty( $aParams['valuation'] ) )
			{
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето Оценка!", __FILE__, __LINE__ );
				return DBAPI_ERR_INVALID_PARAM;
			}

		if( empty( $aParams['attitude'] ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето Становище!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		
		$aOrder = array();
		$aOrder['id'] 			= $aParams['id'];
		$aOrder['id_person'] 	= $aParams['id_person'];
		$aOrder['type'] 		= $aParams['type'];
		$aOrder['valuation'] 	= $aParams['valuation'];
		$aOrder['attitude'] 	= $aParams['attitude'];

		if( isset( $aParams['percent'] ) )
		{
			$aOrder['percent']				= $aParams['percent'];
		}

		if( isset( $aParams['date_start'] ) )
		{
			$aOrder['start_time']			= jsDateToMySQLDate( $aParams['date_start']);
		}

		if( isset( $aParams['date_end'] ) )
		{
			$aOrder['end_time']				= jsDateToMySQLDate( $aParams['date_end'] );
		}
		
	 	
			
		if( $nResult = $oOrders->update( $aOrder ) != DBAPI_ERR_SUCCESS )
		{
			$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
			return $nResult;
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	function loadOrder( $nID )
	{
		global $oOrders, $oResponse;
		
		$id = (int) $nID;
		
		if ( $id > 0 )
		{
			// Редакция
			$aData = array();
			
			// Проверка на правата за достъп
			$personnel_edit = false; // Право на редактиране на информацията за служител
		
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
			{
				if( in_array( 'client_orders_edit', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$personnel_edit = true;
				}
			}
			
			if( $nResult = $oOrders->getRecord( $id, $aData ) != DBAPI_ERR_SUCCESS )
			{
				$oResponse->setError( $nResult, "Проблем при комуникацията!", __FILE__, __LINE__ );
				return $nResult;
			}
			
			$oResponse->setFormElement( 'form1', 'type', 		array(),	$aData['type'] );
			$oResponse->setFormElement( 'form1', 'valuation', 	array(),	$aData['valuation'] );
			$oResponse->setFormElement( 'form1', 'attitude', 	array(),	$aData['attitude'] );
			$oResponse->setFormElement( 'form1', 'percent', 	array(),	$aData['percent'] );
			$oResponse->setFormElement( 'form1', 'date_start', 	array(),	mysqlDateToJsDate( $aData['start_time'] ) );
			$oResponse->setFormElement( 'form1', 'date_end', 	array(),	mysqlDateToJsDate( $aData['end_time'] ) );
			
			
			if( $personnel_edit == false )
			{
				$oResponse->setFormElementAttribute( 'form1', 'type', 		'disabled', 'disabled' );
				$oResponse->setFormElementAttribute( 'form1', 'valuation', 	'disabled', 'disabled' );
				$oResponse->setFormElementAttribute( 'form1', 'attitude', 	'disabled', 'disabled' );
				$oResponse->setFormElementAttribute( 'form1', 'percent', 	'disabled', 'disabled' );
				$oResponse->setFormElementAttribute( 'form1', 'date_start', 'disabled', 'disabled' );
				$oResponse->setFormElementAttribute( 'form1', 'date_end', 	'disabled', 'disabled' );
			}
			//debug($aData);
		}
			
		return DBAPI_ERR_SUCCESS;
	}
	
	print( $oResponse->toXML() );

?>
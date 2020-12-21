<?php

	$oOrders = New DBBase( $db_personnel, 'client_orders' );

	class MyHandler
		extends APIHandler 
		{
			
			function setFields($aParams)
			{
				global $oResponse;
			}			
			
			function Handler( $aParams )
			{
				global $oResponse;
				$aData = array();
				
				// Проверка на правата за достъп
				$personnel_edit = false; // Право на редактиране на информацията за служител
				$personnel_view	= false; // Право за показване на служителите
			
				if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
				{
					if( in_array( 'client_orders_edit', $_SESSION['userdata']['access_right_levels'] ) )
					{
						$personnel_view = true;
						$personnel_edit = true;
					}
					
					if( in_array( 'client_orders_view', $_SESSION['userdata']['access_right_levels'] ) )
						$personnel_view = true;
				}
				switch( $aParams['api_action'] )
				{
					case 'delete' : 
						$nID = (int) $aParams['to_del'];
						if( $nResult = $this->delOrder( $nID ) != DBAPI_ERR_SUCCESS )
						{
							$oResponse->setError( $nResult, "Проблем при премахването на записа!", __FILE__, __LINE__ );
							print( $oResponse->toXML() );
							return $nResult;
						}
						$aParams['api_action'] = 'result';
					break;
					
					default : 
						if ( $nResult = $this->loadOrder( $aParams['id'], $aOrders ) != DBAPI_ERR_SUCCESS )
						{
							$oResponse->setError( $nResult, "Проблем при извличане на данните!", __FILE__, __LINE__ );
							print( $oResponse->toXML() );
							return $nResult;
						}
						
						$aData = current($aData);
						
						$oResponse->setField( 'created_time', 	'време на атестацията', 			'' );
						$oResponse->setField( 'type', 			'тип', 								'' );
						$oResponse->setField( 'valuation',		'оценка', 							'' );
						$oResponse->setField( 'attitude',		'становище', 						'' );
						$oResponse->setField( 'created_user',	'служител направил атестацията', 	'' );
						$oResponse->setField( 'updated_user', 	'...', 								'', 'images/dots.gif' );
						if( $personnel_view == true )
						{
							if( $personnel_edit == true )
							{
								$oResponse->setField( '', '', 'Изтрий', "images/cancel.gif", 'delOrder', '' );
							}
							$oResponse->setFieldLink( 'type',	'openOrder' );
						}
						

						print( $oResponse->toXML() );
					break;
				}
			}
			
			function loadOrder( $nID, &$aData )
			{
				global $db, $oResponse, $oOrders;

				$id = (int) $nID;
				
				if ( $id > 0 )
				{
					$aData = array();
					$aWhere = array();
					
					$aWhere[] = sprintf(" t.id_person = '%d' ", $id );
					$aWhere[] = sprintf(" t.to_arc = 0 " );

					$sQuery = sprintf(" 
						SELECT 
							t.id as _id,
							t.id as id,
							DATE_FORMAT(t.created_time, '%%d.%%m.%%Y') as created_time,
							t.type,
							t.valuation,
							t.attitude,
							CONCAT(up.fname, ' ', up.mname, ' ', up.lname) AS created_user,
							CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' (', DATE_FORMAT(t.updated_time,'%%d.%%m.%%y %%H:%%i:%%s'), ')') AS updated_user
						FROM 
							%s t 
						LEFT JOIN personnel as up on up.id = t.created_user
						", 
						$oOrders->_sTableName
					);
				
					return $oOrders->getReport( $aData, $sQuery, $aWhere );
				}
								
				return DBAPI_ERR_SUCCESS;
			}

			function delOrder( $nID )
			{
				global $db, $oResponse, $oOrders;
				
				$nID = (int) $nID;
				if( $nResult = $oOrders->toARC( $nID ) != DBAPI_ERR_SUCCESS )
				{
					$oResponse->setError( $nReseul, "Проблем при премахването на записа!", __FILE__, __LINE__ );
				}
				
				return DBAPI_ERR_SUCCESS;
			}
		}

	$oHandler = new MyHandler( $oOrders, 'created_user', 'client_orders', 'Атестации' );
	$oHandler->Handler( $aParams );

?>
<?
	$oApplication = New DBBase( $db_personnel, 'person_leaves' );
	
	class MyHandler 
		extends APIHandler {
			
			function setFields($aParams) {
				global $oResponse;
			}
			
			function Handler( $aParams ) {
				global $oResponse;
				$aData = array();
				//APILog::log(0, $aParams);
				
				switch( $aParams['api_action']) {
					case "save" :
						$this->saveApplication( $aParams );
					break;
					default :
						if ( $nResult = $this->loadApplication( $aParams, $aData ) != DBAPI_ERR_SUCCESS ) {
							$oResponse->setError( $nResult, "Проблем при извличане на данните!", __FILE__, __LINE__ );
							print( $oResponse->toXML() );
							return $nResult;
						}
						
						$aData = current($aData);
						//APILog::log(0, $aData);
						
						$oResponse->setFormElement('form1', 'id',				array(), $aData['_id']);
						$oResponse->setFormElement('form1', 'leave_types',		array(), $aData['leave_types']);
						$oResponse->setFormElement('form1', 'year',				array(), $aData['year']);
						$oResponse->setFormElement('form1', 'date',				array(), $aData['date']);
						$oResponse->setFormElement('form1', 'leave_from',		array(), $aData['leave_from']);
						$oResponse->setFormElement('form1', 'leave_to',			array(), $aData['leave_to']);
						$oResponse->setFormElement('form1', 'info',				array(), $aData['info']);
						$oResponse->setFormElement('form1', 'application_days',	array(), $aData['application_days']);
						
						print( $oResponse->toXML() );
					break;
				}
			}
			
			function saveApplication( $aParams ) {
				global $oApplication, $oResponse;
				
				if( empty( $aParams['id_person'] ) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Служитела не е вкаран в системата!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}
				
				//APILog::log(0, $aParams);
				$aApplication = array();
				$aApplication['id']					= $aParams['id'];
				$aApplication['id_person']			= $aParams['id_person'];
				$aApplication['leave_types'] 		= $aParams['leave_types'];
				$aApplication['year']				= $aParams['year'];
				$aApplication['date']				= jsDateToTimestamp( $aParams['date'] );
				$aApplication['leave_from']			= jsDateToTimestamp( $aParams['leave_from'] );
				$aApplication['leave_to']			= jsDateToTimestamp( $aParams['leave_to'] );
				$aApplication['info'] 				= $aParams['info'];
				$aApplication['application_days'] 	= $aParams['application_days'];
				$aApplication['type'] 				= "application";

				if( $nResult = $oApplication->update( $aApplication ) != DBAPI_ERR_SUCCESS ) {
					$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return $nResult;
				}
				
				return DBAPI_ERR_SUCCESS;
			}
			
			function loadApplication( $aParams, &$aData ) {
				global $oResponse;

				$id = (int) $aParams['id'];
				
				if ( $id > 0 ) {
					$aData = array();
					$aWhere = array();
					
					$aWhere[] = sprintf(" t.id = '%d' ", $id );
					$aWhere[] = sprintf(" t.to_arc = 0 " );
					
					$sQuery = sprintf(" 
						SELECT 
							t.id,
							t.id as _id, 
							t.id_person,
							t.leave_types,
							t.year,
							DATE_FORMAT(t.date, '%%d.%%m.%%Y') as date,
							DATE_FORMAT(t.leave_from, '%%d.%%m.%%Y') as leave_from,
							DATE_FORMAT(t.leave_to, '%%d.%%m.%%Y') as leave_to,
							t.info,
							t.application_days
						FROM 
							%s t 
						LEFT JOIN personnel as up on up.id = t.updated_user
						", 
						$this->_oBase->_sTableName
					);
					
					return $this->_oBase->getResult( $aData, $sQuery, $aWhere );
				}
			}
		}
	
	$oHandler = new MyHandler( $oApplication, 'id', 'application', 'Отпуски' );
	$oHandler->Handler( $aParams );
?>
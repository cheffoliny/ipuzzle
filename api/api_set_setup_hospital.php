<?php
	$oHospital = New DBBase( $db_personnel, 'person_leaves' );

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
						$this->saveHospital( $aParams );
					break;					
					default :			
						if ( $nResult = $this->loadHospital( $aParams, $aData ) != DBAPI_ERR_SUCCESS ) {
							$oResponse->setError( $nResult, "Проблем при извличане на данните!", __FILE__, __LINE__ );
							print( $oResponse->toXML() );
							return $nResult;
						}
						
						$aData = current($aData);
						//APILog::log(0, $aData);
						
						$oResponse->setFormElement('form1', 'id',				array(), $aData['_id']);
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
			
			function saveHospital( $aParams )
			{
				global $oHospital, $oResponse;
				
				$oDBPersonnel 		= new DBPersonnel();
				$oDBSalaryEarning 	= new DBSalaryEarning();
				$oDBSalary 			= new DBSalary();
				$oDBHolidays 		= new DBHolidays();
				$oDBObjectDuty		= new DBObjectDuty();
				
				if( empty( $aParams['id_person'] ) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Служитела не е вкаран в системата!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}
				
				//APILog::log(0, $aParams);
				$aHospital = array();
				$aHospital['id']				= $aParams['id'];
				$aHospital['id_person']			= $aParams['id_person'];
				$aHospital['leave_types'] 		= "other";
				$aHospital['year']				= $aParams['year'];
				$aHospital['date']				= jsDateToTimestamp( $aParams['date'] );
				$aHospital['leave_from']		= jsDateToMySQLDate( $aParams['leave_from'] ) . " 00:00:00";
				$aHospital['leave_to']			= jsDateToMySQLDate( $aParams['leave_to'] ) . " 23:59:59";
				$aHospital['res_leave_from']	= $aHospital['leave_from'];
				$aHospital['res_leave_to']		= $aHospital['leave_to'];
				$aHospital['info'] 				= $aParams['info'];
				$aHospital['application_days'] 	= $oDBHolidays->getWorkdaysInPeriod( jsDateToMySQLDate( $aParams['leave_from'] ), jsDateToMySQLDate( $aParams['leave_to'] ) );
				$aHospital['type'] 				= "hospital";
				$aHospital['is_confirm']		= 1;
				if( empty( $aParams['id'] ) )
				{
					$aHospital['confirm_time'] 	= jsDateToTimestamp( $aParams['date'] );
					$aHospital['confirm_user'] 	= isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
				}
				
				//Fix Schedules
				$aObjectsWithShifts = $oDBObjectDuty->getObjectsForPersonShifts( $aHospital['id_person'], $aHospital['leave_from'], $aHospital['leave_to'] );
				
				if( !empty( $aObjectsWithShifts ) )
				{
					$sErrorMessage = "Служителя има смени в следните обекти:";
					foreach( $aObjectsWithShifts as $aObjectsNames )
					{
						$sErrorMessage .= "\n " . $aObjectsNames['name'];
					}
					
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, $sErrorMessage, __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}
				//End Fix Schedules
				
				if( $nResult = $oHospital->update( $aHospital ) != DBAPI_ERR_SUCCESS ) {
					$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return $nResult;
				}
				
				//Add Earning
				$aPersonOffice 			= $oDBPersonnel->getPersonnelOffice( $aParams['id_person'] );
				$aCodeHospitalEarning 	= $oDBSalaryEarning->getHospitalEarning();
				
				$aDate = explode( ".", ( !empty( $aParams['leave_from'] ) ? $aParams['leave_from'] : $aParams['date'] ) );
				if( isset( $aDate[1] ) && isset( $aDate[2] ) )
				{
					$nYearMonth = ( int ) ( $aDate[2] . $aDate[1] );
				}
				else
				{
					$nYearMonth = ( int ) ( date( "Ym" ) );
				}
				
				$nIDSalaryRow = $oDBSalary->getSalaryRowByApplication( $aHospital['id'] );
				
				$aDataSalary = array();
				$aDataSalary['id'] 				= $nIDSalaryRow;
				$aDataSalary['id_person'] 		= $aParams['id_person'];
				$aDataSalary['id_office'] 		= ( !empty( $aPersonOffice ) && isset( $aPersonOffice['id_office'] ) ) ? $aPersonOffice['id_office'] : 0;
				$aDataSalary['month'] 			= $nYearMonth;
				$aDataSalary['is_earning'] 		= 1;
				$aDataSalary['sum'] 			= 0;
				$aDataSalary['count'] 			= 1;
				$aDataSalary['id_application'] 	= $aHospital['id'];
				$aDataSalary['last_paid_date'] 	= jsDateToTimestamp( $aParams['leave_from'] );
				
				$aDataSalary['code'] 		= isset( $aCodeHospitalEarning['code'] ) ? $aCodeHospitalEarning['code'] : "";
				$aDataSalary['description'] = "Болничен {$aHospital['application_days']} дни";
				
				$nResult = $oDBSalary->update( $aDataSalary );
				if( $nResult != DBAPI_ERR_SUCCESS )
				{
					$oResponse->setError( $nResult, "Грешка при нанасяне на наработка!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return $nResult;
				}
				//End Add Earning
				
				return DBAPI_ERR_SUCCESS;
			}
			
			function loadHospital( $aParams, &$aData ) {
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

	$oHandler = new MyHandler( $oHospital, 'id', 'hospital', 'Болничен' );
	$oHandler->Handler( $aParams );	
?>

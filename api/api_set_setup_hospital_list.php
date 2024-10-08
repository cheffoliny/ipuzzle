<?php
	$oHospital = new DBBase( $db_personnel, 'person_leaves' );
	
	class MyHandler 
		extends APIHandler {
			
			function setFields($aParams) {
				global $oResponse;
					
			}			

			function Handler( $aParams ) {
				global $oResponse;
				$aData = array();

				switch( $aParams['api_action'] ) {
					case 'delete' : 
						$nID = (int) $aParams['id'];
						
						//Delete Salary Rows
						$oDBSalary = new DBSalary();
						$nResult = $oDBSalary->deleteSalaryRowsByApplication( $nID );
						if( $nResult != DBAPI_ERR_SUCCESS )
						{
							$oResponse->setError( $nResult, "Проблем при коригиране на работна заплата!", __FILE__, __LINE__ );
							print( $oResponse->toXML() );
						}
						//End Delete Salary Rows
						
						if ( $nResult = $this->_oBase->toARC( $nID ) != DBAPI_ERR_SUCCESS ) {
							$oResponse->setError( $nResult, "Проблем при премахването на записа!", __FILE__, __LINE__ );
							print( $oResponse->toXML() );
						}
						
						$aParams['api_action'] = 'result';
					break;
					
					default :			
						if ( $nResult = $this->getReport( $aParams ) != DBAPI_ERR_SUCCESS ) {
							$oResponse->setError( $nResult, "Проблем при извличане на данните!", __FILE__, __LINE__ );
							print( $oResponse->toXML() );
							return $nResult;
						}
						
						$oResponse->setField( 'year', 					'година', 		'Сортирай по година' );
						$oResponse->setField( 'date', 					'дата', 		'Сортирай по дата' );
						$oResponse->setField( 'leave_from', 			'от дата', 		'Сортирай по дата' );
						$oResponse->setField( 'leave_to', 				'до дата', 		'Сортирай по дата' );
						$oResponse->setField( 'info', 					'информация', 	'Сортирай по информация'  );
						$oResponse->setField( 'application_days', 		'раб. дни', 	'Сортирай по дни', NULL, NULL, NULL, array('DATA_FORMAT' => DF_NUMBER) );
						$oResponse->setField( 'updated_user', 			'...', 			'Сортиране по последно редактирал', 'images/dots.gif' );
						$oResponse->setField( '', 						'',  			'Изтрий', "images/cancel.gif", "delApplication", '');
						$oResponse->setFIeldLink( 'leave_types',		'setHospital' );
						$oResponse->setFIeldLink( 'date',				'setHospital' );
						$oResponse->setFIeldLink( 'year',				'setHospital' );
						$oResponse->setFIeldLink( 'leave_types',		'setHospital' );
						$oResponse->setFIeldLink( 'leave_from',			'setHospital' );
						$oResponse->setFIeldLink( 'leave_to',			'setHospital' );
						
						print( $oResponse->toXML() );
					break;
				}
			}
	
			function getReport( $aParams ) {
				global $oResponse;
				$aWhere = array();
				
				$id_person = (int) !empty( $aParams['id_person'] ) ? $aParams['id_person'] : 0;
				$nYear = (int) !empty($aParams['year']) ? $aParams['year'] : 0;
				
				if ( !empty($aParams['year']) ) {
					$nYear = (int) $aParams['year'];
					
					if( ($nYear < 2000) || ($nYear > 2050)) {
						$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Невалидна стойност за година!");
						return DBAPI_ERR_INVALID_PARAM; 
					}
				} else $nYear = 0;
				
				$aWhere[] = sprintf(" t.id_person = %d", $id_person);
				$aWhere[] = sprintf(" t.type = '%s'", "hospital");
				$aWhere[] = " t.to_arc=0 ";

				if ( !empty($nYear) ) {
					$aWhere[] = sprintf(" t.year = %d", $nYear);
				}
						
				if ( empty($aParams['sfield']) ) {
					$aParams['sfield'] = "year";
				}
									
				$sQuery = sprintf("
					SELECT 
						SQL_CALC_FOUND_ROWS
						t.id as _id, 
						t.id as id,
						t.year as year,
						DATE_FORMAT(t.date, '%%d.%%m.%%Y') as date,
						DATE_FORMAT(t.leave_from, '%%d.%%m.%%Y') as leave_from,
						DATE_FORMAT(t.leave_to, '%%d.%%m.%%Y') as leave_to,
						IF (LENGTH(t.info) > 30, CONCAT(LEFT(t.info, 20), '...'), t.info) as info,
						t.application_days,
						CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' (', DATE_FORMAT(t.updated_time,'%%d.%%m.%%y %%H:%%i:%%s'), ')') AS updated_user
					FROM 
						%s t 
					LEFT JOIN personnel as up on up.id = t. updated_user
					", 
					$this->_oBase->_sTableName
				);
				//if ( t.is_earning, ear.name, expe.name) as name
				
				//echo $sQuery;
				return $this->_oBase->getReport( $aParams, $sQuery, $aWhere );
			}
		}

	$oHandler = new MyHandler( $oHospital, 'year', 'hospital', 'Болничен' );
		
	$oHandler->Handler( $aParams );
	//debug($oResponse);
?>
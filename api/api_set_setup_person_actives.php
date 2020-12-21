<?php
	$oPPP = new DBBase( $db_personnel, 'ppp' );
	
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
						echo $nID;
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
//						
//						$aData = current($aData);
//						//APILog::log(0, $aData);
//						
						$oResponse->setField( 'num', 				'номер на ППП', 'Сортирай по ППП' );
						$oResponse->setField( 'created_time', 		'дата', 		'Сортирай по дата' );
						$oResponse->setField( 'type', 				'тип',			'Сортирай по тип' );
						$oResponse->setField( 'created_user', 		'създаден от', 	'Сортирай по създал' );
						
						print( $oResponse->toXML() );
					break;
				}
			}
			
			function getReport( $aParams ) {
				global $oResponse;
				$aWhere = array();
				
				$id_person = (int) !empty( $aParams['id_person'] ) ? $aParams['id_person'] : 0;
				
				$aWhere[] = sprintf(" t.id_person = %d", $id_person);
				$aWhere[] = " t.to_arc=0 ";
				
				if ( empty($aParams['sfield']) ) {
					$aParams['sfield'] = "created_time";
				}
				
				$sQuery = sprintf("
					SELECT 
						SQL_CALC_FOUND_ROWS
						t.id as _id, 
						t.id as id,
						t.num as num,
						CASE t.ppp_type
							WHEN 'in' THEN 'зачисляване '
							WHEN 'out'  THEN 'отчисляване ' 
						END as type,
						DATE_FORMAT(t.created_time, '%%d.%%m.%%Y') as created_time,
						CONCAT_WS(' ', up.fname, up.mname, up.lname) as created_user
					FROM 
						%s t 
					LEFT JOIN personnel as up on up.id = t.created_user
					", 
					$this->_oBase->_sTableName
				);
				
				//echo $sQuery;
				return $this->_oBase->getReport( $aParams, $sQuery, $aWhere );
			}
		}

	$oHandler = new MyHandler( $oPPP, 'created_time', 'ppp', 'ППП' );
		
	$oHandler->Handler( $aParams );
	//debug($oResponse);
?>
<?php
	$oSalary = new DBBase( $db_personnel, 'salary' );
		
	class MyHandler 
		extends APIHandler {
			
			function setFields($aParams) {
				global $oResponse;
				
				$act = isset($aParams['sAct']) ? (int) $aParams['sAct'] : 0;
				
				//debug($oResponse->oResult->aData);
				
				$oResponse->setField( 'code', 			'код', 			'Сортирай по код'  );
				$oResponse->setField( 'name', 			'описание',		'Сортирай по описание' );
				$oResponse->setField( 'firm', 			'към фирма', 	'Сортирай по фирма'  );
				$oResponse->setField( 'region', 		'регион', 		'Сортирай по регион'  );
				$oResponse->setField( 'objectsa', 		'обект', 		'Сортирай по обект'  );
				$oResponse->setField( 'sum', 			'стойност', 	'Сортирай по стойност', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY) );
				$oResponse->setField( 'count', 			'кол', 			'Сортирай по количество', NULL, NULL, NULL, array('DATA_FORMAT' => DF_DIGIT) );
				$oResponse->setField( 'total_sum', 		'общо', 		'Сортирай по стойност', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1) );
				$oResponse->setField( 'updated_user', 	'...', 			'Сортиране по последно редактирал', 'images/dots.gif' );
				
//				if ( in_array('person_salary_edit', $_SESSION['userdata']['access_right_levels']) ) {
//					if ( $act == 1 ) {
//						$oResponse->setField( '', '', 'Изтрий', "images/cancel.gif", "delSalary", '');
//					}
//					
//					$oResponse->setFIeldLink( 'code',		'editSalary' );
//					$oResponse->setFIeldLink( 'name',		'editSalary' );	
//				}		
			}			
			
			function getReport( $aParams ) {
				global $oResponse, $db_name_sod;
				$aWhere = array();
				
				$nID = (int) !empty( $aParams['nID'] ) ? $aParams['nID'] : 0;
				$nYear = (int) !empty($aParams['year']) ? $aParams['year'] : 0;
				$nMonth = (int) !empty($aParams['month']) ? $aParams['month'] : 0;
				
				//APILog::Log($aParams);
				$act = isset($aParams['sAct']) ? (int) $aParams['sAct'] : 0;
					
				if ( (!empty($nYear) && ( ($nYear < 2000) || ($nYear > 2050) ))  || ( !empty($nMonth) && (($nMonth < 1) || ($nMonth >12))) )
				{
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Невалидна стойност за месец, година!");
					return DBAPI_ERR_INVALID_PARAM; 
				}
				
				if ( $act == 1 ) {
					$sum = "
						t.sum as sum, 
						t.count as count,
						t.total_sum as total_sum,
					";	
				}
				
				if ( $act == 2 ) {
					$sum = "
						SUM(t.sum) as sum, 
						SUM(t.count) as count,
						SUM(t.total_sum) as total_sum,
					";	
				}

				if ( $act == 3 ) {
					$sum = "
						SUM(t.sum) as sum, 
						SUM(t.count) as count,
						SUM(t.total_sum) as total_sum,
					";	
				}
				
				$aWhere[] = sprintf(" t.id_person = %d", $nID);
				$aWhere[] = sprintf(" t.month = %d ",  $nYear * 100 + $nMonth );
				$aWhere[] = " t.to_arc=0 ";
									
				$sQuery = sprintf(" 
					SELECT 
						SQL_CALC_FOUND_ROWS 
						t.id as _id, 
						IF ( r.id_firm, CONCAT(t.id, ',', r.id_firm), CONCAT(t.id, ',0') ) as id,
						t.code as code, 
						t.month,
						r.name as region,
						f.name as firm,
						o.name as object,
						obj.name as objectsa,
						is_earning as type,
						{$sum}
						t.is_earning,
						CONCAT( f.name , ' (',r.name, ')' ) as region_name,
						CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' (', DATE_FORMAT(t.updated_time,'%%d.%%m.%%y %%H:%%i:%%s'), ')') AS updated_user,
						t.description as name,
						h.id as h1,
						h_y.id as h2
					FROM 
						%s t 
						LEFT JOIN {$db_name_sod}.offices r ON r.id = t.id_office
						LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
						LEFT JOIN {$db_name_sod}.region_objects o ON o.id = t.id_object
						LEFT JOIN {$db_name_sod}.objects obj ON obj.id = t.id_object
						LEFT JOIN personnel as up on up.id = t. updated_user
						LEFT JOIN salary_earning_types ear ON (t.code = ear.code AND t.is_earning = 1 AND ear.to_arc = 0)
						LEFT JOIN salary_expense_types expe ON (t.code = expe.code AND t.is_earning = 0 AND expe.to_arc = 0)
						LEFT JOIN {$db_name_sod}.object_duty objd ON objd.id = t.id_object_duty
						LEFT JOIN {$db_name_sod}.holidays h ON ( DAY( objd.startShift ) = h.day AND MONTH( objd.startShift ) = h.month )
						LEFT JOIN {$db_name_sod}.holidays h_y ON ( DAY( objd.endShift ) = h_y.day AND MONTH( objd.endShift ) = h_y.month )
						
					", 
					$this->_oBase->_sTableName
				);
				
				if ( $act == 1 ) {
					$aGroup = " t.id ";
				}
				
				if ( $act == 2 ) {
					$aGroup = " t.code ";
				}
				
				if ( $act == 3 ) {
					$aGroup = " t.id_object, t.code ";
				}

				//if ( t.is_earning, ear.name, expe.name) as name
				//	SUM( IF(is_earning = 1, total_sum, (total_sum * -1)) ) AS total,
				//echo $sQuery;
				
				APILog::Log(0, $sQuery);
				
				$nRowCount = $_SESSION['userdata']['row_limit'];
				$_SESSION['userdata']['row_limit'] = 200;
				
				$this->_oBase->getReport( $aParams, $sQuery, $aWhere, $aGroup );
				
				$_SESSION['userdata']['row_limit'] = $nRowCount;
				
				$total = 0;
				
				foreach ( $oResponse->oResult->aData as $key => &$val ) {
					if ( $val['is_earning'] == 0 ) {
						$val['total_sum'] = $val['total_sum'] * -1;
						$val['sum'] = $val['sum'] * -1;
					} 
					
					$total += $val['total_sum'];

					if ( !empty($val['h1']) || !empty($val['h2']) ) {
						$oResponse->setDataAttributes( $key, 'code', array('style' => 'background-color: #BEDAF8; !important;'));
						$oResponse->setDataAttributes( $key, 'name', array('style' => 'background-color: #BEDAF8; !important;'));
						$oResponse->setDataAttributes( $key, 'firm', array('style' => 'background-color: #BEDAF8; !important;'));
						$oResponse->setDataAttributes( $key, 'region', array('style' => 'background-color: #BEDAF8; !important;'));
						$oResponse->setDataAttributes( $key, 'objectsa', array('style' => 'background-color: #BEDAF8; !important;'));
						$oResponse->setDataAttributes( $key, 'sum', array('style' => 'background-color: #BEDAF8; color: red; !important;'));
						$oResponse->setDataAttributes( $key, 'count', array('style' => 'background-color: #BEDAF8; !important;'));
						$oResponse->setDataAttributes( $key, 'total_sum', array('style' => 'background-color: #BEDAF8; color: red; !important;'));
						$oResponse->setDataAttributes( $key, 'updated_user', array('style' => 'background-color: #BEDAF8; !important;'));						
					}					
				}
				
				$oResponse->addTotal('total_sum', $total);
				//debug($oResponse->oResult->aData);
			}
		}

	$oHandler = new MyHandler( $oSalary, 'name', 'salary', 'Заплати' );
		
	$oHandler->Handler( $aParams );
	//debug($oResponse);
?>
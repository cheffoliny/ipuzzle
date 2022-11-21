<?php
	require_once('include/parse_excel/reader.php');
	//require_once('include/unzip.inc.php');
	require_once('include/import.inc.php');
	
	$oSalary = new DBBase( $db_personnel, 'salary' );
	
	class MyHandler extends APIHandler
	{
		function getReport( $aParams )
		{
			global $oResponse, $db_name_sod;
			
			$aWhere = array();
			
			$aWhere[] = " t.to_arc = 0 ";
			
			if( !empty( $aParams['firm'] ) )
				if( $aParams['type'] == 1 )
					$aWhere[] = sprintf( " f.id = '%s' ", addslashes( $aParams['firm'] ) );
				else
					$aWhere[] = sprintf( " sf.id = '%s' ", addslashes( $aParams['firm'] ) );
			
			if( !empty( $aParams['region'] ) )
				if( $aParams['type'] == 1 )
					$aWhere[] = sprintf( " r.id = '%s' ", addslashes( $aParams['region'] ) );
				else
					$aWhere[] = sprintf( " sr.id = '%s' ", addslashes( $aParams['region'] ) );
			
			if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
			{
				$sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
				$aWhere[] = " r.id IN ({$sAccessable}) \n";
			}
			
			if( !empty( $aParams['region_object'] ) )
				if( $aParams['type'] == 1 )
					$aWhere[] = sprintf( " o.name = '%s' ", addslashes( $aParams['region_object'] ) );
				else
					$aWhere[] = sprintf( " so.name = '%s' ", addslashes( $aParams['region_object'] ) );
			
			if( !empty($aParams['year']) )
			{
				$nYear 	= (int) $aParams['year'];
				$nMonth = (int) $aParams['month'];
				
				if( ($nYear < 2007) || ($nYear > 2050) || ($nMonth < 1) || ($nMonth > 12) )
				{
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Невалидна стойност за месец, година!" );
					return DBAPI_ERR_INVALID_PARAM;
				}
				
				$aWhere[] = sprintf( " t.month = %d ",  $nYear * 100 + $nMonth );
			}
			
			$sQuery = sprintf( "
				SELECT SQL_CALC_FOUND_ROWS
					t.id as _id,
					t.id,
					t.id_person,
					t.month,
					p.code as person_code,
					CONCAT_WS( ' ', p.fname, p.mname, p.lname ) AS person_name,
					t.code code,
					CONCAT( sf.name,' (', sr.name, ')' ) AS firm_name,
					CONCAT( so.name,' (', so.num, ')' ) AS object_name,
					sf.code AS firm_code,
					sr.code AS region_code,
					so.num 	AS object_num,
					t.description,
					t.sum,
					t.count,
					t.total_sum,
					t.updated_time,
					CONCAT( CONCAT_WS( ' ', up.fname, up.mname, up.lname ), ' (', DATE_FORMAT( t.updated_time,'%%d.%%m.%%y %%H:%%i:%%s' ), ')' ) AS updated_user
				FROM %s t
					LEFT JOIN personnel p ON p.id = t.id_person
					LEFT JOIN {$db_name_sod}.offices r ON r.id = p.id_office
					LEFT JOIN {$db_name_sod}.objects o ON o.id = p.id_region_object
					LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
					LEFT JOIN personnel as up on up.id = t. updated_user
					LEFT JOIN {$db_name_sod}.offices sr ON sr.id = t.id_office
					LEFT JOIN {$db_name_sod}.firms sf ON sf.id = sr.id_firm
					LEFT JOIN {$db_name_sod}.objects so ON so.id = t.id_object
				", 
				$this->_oBase->_sTableName
			);
			
			$aData = array();
			if( $Result = $this->_oBase->getReport( $aParams, $sQuery, $aWhere, NULL, $aData ) != DBAPI_ERR_SUCCESS )
				return $Result;
			
			foreach( $aData as $nKey => $aValue )
			{
				$oResponse->setDataAttributes( $nKey, 'person_name', array( "id" => $aValue['id_person'] ) );
			}
			
			$sQuery = sprintf( "
				SELECT
					1,
					IF
					(
						t.is_earning,
						t.total_sum,
						-t.total_sum
					) AS total_sum
				FROM
					%s t
					LEFT JOIN personnel p ON p.id = t.id_person
					LEFT JOIN {$db_name_sod}.offices r ON r.id = p.id_office
					LEFT JOIN {$db_name_sod}.objects o ON o.id = p.id_region_object
					LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
					LEFT JOIN personnel as up on up.id = t. updated_user
					LEFT JOIN {$db_name_sod}.offices sr ON sr.id = t.id_office
					LEFT JOIN {$db_name_sod}.firms sf ON sf.id = sr.id_firm
					LEFT JOIN {$db_name_sod}.objects so ON so.id = t.id_object
				WHERE 1
				", 
				$this->_oBase->_sTableName
			);
			
			if( !empty( $aWhere ) )
			{
				foreach( $aWhere as $sCondition )
				{
					$sQuery .= " AND {$sCondition}";
				}
			}
			
			$aTotals = array();
			$objSalary = new DBSalary();
			$aTotals = $objSalary->select( $sQuery );
//			if( $Result = $this->_oBase->getResult( $aTotals, $sQuery, $aWhere ) != DBAPI_ERR_SUCCESS )
//				return $Result;
			
			$nEndTotal = 0;
			foreach( $aTotals as $aTotal )
			{
				$nEndTotal += $aTotal['total_sum'];
			}
			
			if( !empty( $aTotals ) )
				$oResponse->addTotal( 'total_sum', $nEndTotal );
			
			return DBAPI_ERR_SUCCESS;
		}
		
		function setFields( $aParams )
		{
			global $oResponse;
			
			if( $aParams['api_action'] == 'export_to_xls' )
			{
				$oResponse->setField( 'person_code', 	'код', 		'', NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_NUMBER ) );
				$oResponse->setField( 'person_name', 	'име', 		''  );
				$oResponse->setField( 'month', 			'месец', 	''  );
				$oResponse->setField( 'firm_name', 		'фирма',	''  );
				$oResponse->setField( 'firm_code', 		'[Ф] код',	''  );
				$oResponse->setField( 'region_code', 	'[Р] код',	''  );
				$oResponse->setField( 'object_name', 	'обект',	''  );
				$oResponse->setField( 'object_num', 	'номер',	''  );
				$oResponse->setField( 'code', 			'тип', 		''  );
				$oResponse->setField( 'description', 	'описание', ''  );
				$oResponse->setField( 'sum', 			'ед.цена',	'', NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_CURRENCY ) );
				$oResponse->setField( 'count', 			'кол.',		'', NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_DIGIT ) );
				$oResponse->setField( 'total_sum', 		'сума', 	'', NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
				$oResponse->setField( 'updated_user', 	'последно редактирал', '' );
			}
			else
			{
				$oResponse->setField( 'person_code', 	'код', 		'Сортирай по код служител', NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_NUMBER ) );
				$oResponse->setField( 'person_name', 	'име', 		'Сортирай по име на служител', NULL, 'personnel' );
				$oResponse->setField( 'firm_name', 		'фирма',	'Сортирай по фирма за чиято смека е наработката / удръжката' );
				$oResponse->setField( 'object_name', 	'обект',	'Сортирай по обкет за чиято смека е наработката / удръжката' );
				$oResponse->setField( 'code', 			'тип', 		'Сортирай по номенклатура на наработка / удръжка' );
				$oResponse->setField( 'description', 	'описание', 'Сортирай по описание на наработка / удръжка' );
				$oResponse->setField( 'sum', 			'ед.цена',	'Сортирай по ед. цена', NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_CURRENCY ) );
				$oResponse->setField( 'count', 			'кол.',		'Сортирай по количество', NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_DIGIT ) );
				$oResponse->setField( 'total_sum', 		'сума', 	'Сортирай по сума', NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
				$oResponse->setField( 'updated_user', 	'...', 		'Сортиране по последно редактирал', 'images/dots.gif' );
				$oResponse->setField( '', 				'',  		'', "images/cancel.gif", "deleteSalary", '' );
			}
		}
		
		function Handler( $aParams )
		{
			global $oResponse;
			
			$oFirms = new DBFirms();
			$oOffices = new DBOffices();
			
			switch ( $aParams['api_action'] )
			{
				case 'uplaod_file':
						if( !empty( $aParams['uplaoded_file_name'] ) && !empty( $aParams['uplaoded_file_type'] ) )
						{
							$aFile = array();
							$aFile['tmp_name'] 	= $aParams['uplaoded_file_name'];
							$aFile['type'] 		= $aParams['uplaoded_file_type'];
							
							$aData = array();
							$sError=GetImportedData( $aFile, $aData );
							
							$aErrorMsg = array();
							if( empty( $sError ) )
								$this->ProccessData( $aData, $aErrorMsg );
							else
								$aErrorMsg[]['msg'] = $sError;
							
							if( function_exists( 'unlink' ) )
								unlink( $aFile['tmp_name'] );
							else
								exec( "rm {$aFile['tmp_name']}" );
							
							$oResponse->setField( 'msg', "съобщение" );
							$oResponse->setField( 'row', "ред" );
							$oResponse->setField( 'col', "колона" );
							
							if( empty($aErrorMsg) )
							{
								$aErrorMsg[] = array(
									'msg' => " Операцията премина успешно! ",
									'row' => 0,
									'col' => 0
								);
							}
							
							$oResponse->setData( $aErrorMsg );
						}
						
						print( $oResponse->toXML() );
						
					break;
					
				case 'uplaod_file_gsm':
						if( !empty( $aParams['uplaoded_file_name'] ) && !empty( $aParams['uplaoded_file_type'] ) )
						{
							$aFile = array();
							$aFile['tmp_name'] 	= $aParams['uplaoded_file_name'];
							$aFile['type'] 		= $aParams['uplaoded_file_type'];
							
							$aData = array();
							
							$sError=GetImportedData( $aFile, $aData );
							
							$aErrorMsg = array();
							
							if( empty( $sError ) ) 
								$this->ProccessDataMtel( $aData, $aErrorMsg, $aParams );
							else
								$aErrorMsg[]['msg'] = $sError;
							
							if( function_exists( 'unlink' ) )
								unlink( $aFile['tmp_name'] );
							else
								exec( "rm {$aFile['tmp_name']}" );
							
							$oResponse->setField( 'msg', "съобщение" );
							$oResponse->setField( 'row', "ред" );
							$oResponse->setField( 'col', "колона" );
							
							if( empty($aErrorMsg) )
							{
								$aErrorMsg[] = array( 
									'msg' => " Операцията премина успешно! ",
									'row' => 0,
									'col' => 0
								);
							}
							
							$oResponse->setData($aErrorMsg);
						}
						
						print( $oResponse->toXML() );
						
					break;
					
				case 'delete_salary': 
						$nID = (int) $aParams['id'];
						if( $nReseul = $this->_oBase->toARC( $nID ) != DBAPI_ERR_SUCCESS )
							$oResponse->setError( $nReseul, "Проблем при премахването на записа!", __FILE__, __LINE__ );
						
						$aParams['api_action'] = 'result';
						
					break;
					
				case 'fillFirms':
						//Set Firms
						$aFirms = $oFirms->getFirms2();
						$nIDOffice = $_SESSION['userdata']['id_office'];
						
						$oResponse->setFormElement( 'form1', 'firm' );
						$oResponse->setFormElementChild( 'form1', 'firm', array( "value" => 0 ), "--- Избери ---" );
						
						foreach( $aFirms as $aFirm ) {
							$oResponse->setFormElementChild( 'form1', 'firm', array( "value" => $aFirm['id'] ), $aFirm['name'] );
						}
						
						if ( !isset($aParams['firm']) ) {
							$aParams['firm'] = $oOffices->getFirmByIDOffice( $nIDOffice );	
														
							$oResponse->setFormElementAttribute( 'form1', 'firm', 'value', $aParams['firm'] );
						} else {
							$oResponse->setFormElementAttribute( 'form1', 'firm', 'value', $aParams['firm'] );
						}
												
						if( $aParams['firm'] ) {
							$aOffices = $oOffices->getOfficesByFirm( $aParams['firm'] );
						}
						
						$oResponse->setFormElement( 'form1', 'region' );
						$oResponse->setFormElementChild( 'form1', 'region', array( "value" => 0 ), "--- Избери ---" );
						if( $aParams['firm'] )
						{
							foreach( $aOffices as $aOffice )
							{
								$oResponse->setFormElementChild( 'form1', 'region', array( "value" => $aOffice['id'] ), $aOffice['name'] );
							}
							
							if ( isset( $aParams['region'] ) ) {
								$oResponse->setFormElementAttribute( 'form1', 'region', 'value', $aParams['region'] );
							} else {
								$oResponse->setFormElementAttribute( 'form1', 'region', 'value', $nIDOffice );
							}
						}
						//End Set Offices
						
						print( $oResponse->toXML() );
						
					break;
					
				case 'fillRegions':
						//Set Offices
						if( !isset( $aParams['firm'] ) )$aParams['firm'] = 0;
						if( $aParams['firm'] )
						{
							$aOffices = $oOffices->getOfficesByFirm( $aParams['firm'] );
						}
						
						$oResponse->setFormElement( 'form1', 'region' );
						$oResponse->setFormElementChild( 'form1', 'region', array( "value" => 0 ), "--- Избери ---" );
						if( $aParams['firm'] )
						{
							foreach( $aOffices as $aOffice )
							{
								$oResponse->setFormElementChild( 'form1', 'region', array( "value" => $aOffice['id'] ), $aOffice['name'] );
							}
						}
						//End Set Offices
						
						print( $oResponse->toXML() );
						
					break;
			}
			
			APIHandler::Handler( $aParams );
			
		}
		
		function ProccessData( $aData, &$aErrorMsg )
		{
			global $oResponse, $db_personnel, $db_sod;
			
			$aHeader = array();
			for( $i=1; $i<=$aData['numCols']; $i++ )
				$aHeader[ $aData['cells'][3][$i] ] = $i;
			
			$oPersons = new DBBase( $db_personnel, 'personnel' );
			$oSalaryExpenses = new DBBase( $db_personnel, 'salary_expense_types' );
			$oSalaryEarnings = new DBBase( $db_personnel, 'salary_earning_types' );
			$oRegions = new DBBase( $db_sod, 'offices' );
			$oObjects = new DBBase( $db_sod, 'objects' );
			
			$aSalaryExpenses = array();
			if( $nResult = $oSalaryExpenses->getResult( $aSalaryExpenses, " SELECT code as code_, code FROM {$oSalaryExpenses->_sTableName}" ) != DBAPI_ERR_SUCCESS )
			{
				$aErrorMsg[] = array(
					'msg' => " Проблем при извличане на типовете оддръжки ",
					'row' => 0,
					'col' => 0
				);
				return $nResult;
			}
			
			$aSalaryEarnings = array();
			if( $nResult = $oSalaryEarnings->getResult( $aSalaryEarnings, " SELECT code as code_, code FROM {$oSalaryEarnings->_sTableName}" )  != DBAPI_ERR_SUCCESS )
			{
				$aErrorMsg[] = array(
					'msg' => " Проблем при извличане на типовете наработки ",
					'row' => 0,
					'col' => 0
				);
				return $nResult;
			}
			
			for( $i=4; $i<=$aData['numRows']; $i++ )
			{
				
				// Информация за служителя
				$nPersonCode = (int) $aData['cells'][$i][ $aHeader['код'] ];
				
				$aPersons = array();
				if( $oPersons->getResult( $aPersons, NULL, array( " code = {$nPersonCode} " ) ) != DBAPI_ERR_SUCCESS )
				{
					$aErrorMsg[] = array(
						'msg' => sprintf( " Проблем при извличане на данни за служител с код %s ", $nPersonCode ),
						'row' => $i,
						'col' => $aHeader['код']
					);
					continue;
				}
				
				if( empty($aPersons) )
				{
					$aErrorMsg[] = array(
						'msg' => sprintf( " Служител с код %s не съществува ", $nPersonCode ),
						'row' => $i,
						'col' => $aHeader['код']
					);
					continue;
				}
				
				$aPerson = current( $aPersons );
				
				// Номенклатура за  наработката / оддръжката
				$sSalaryType = trim( $aData['cells'][$i][ $aHeader['тип'] ] );
				
				if( !in_array( $sSalaryType, $aSalaryEarnings ) && !in_array( $sSalaryType, $aSalaryExpenses ) )
				{
					$aErrorMsg[] = array(
						'msg' => sprintf( " Некоректен тип на наработката / оддръжката: %s ", $sSalaryType ),
						'row' => $i,
						'col' => $aHeader['тип']
					);
					continue;
				}
				
				// Номенклатура за  наработката / оддръжката
				$nYear 	= (int) substr( $aData['cells'][$i][ $aHeader['месец'] ] , 0, 4 ) ;
				$nMonth = (int) substr( $aData['cells'][$i][ $aHeader['месец'] ] , 4, 2 ) ;
				
				if( ($nMonth < 1 ) || ($nMonth > 12) || ($nYear<2007) || ($nYear>2050) )
				{
					$aErrorMsg[] = array(
						'msg' => sprintf( " Некоректен месец за наработката / оддръжката: %s ", $aData['cells'][$i][ $aHeader['месец'] ] ),
						'row' => $i,
						'col' => $aHeader['месец']
					);
					continue;
				}
				
				// За кой регион е наработката / оддръжката
				$sRegion = trim( $aData['cells'][$i][ $aHeader['[Р] код'] ] );
				$aRegion = array();
				if( $nResult = $oRegions->getRecordByField( $sRegion, 'code', $aRegion ) != DBAPI_ERR_SUCCESS )
				{
					$aErrorMsg[] = array(
						'msg' => sprintf( " Проблем при извличане информацията за региона : %s ", $sRegion ),
						'row' => $i,
						'col' => $aHeader['[Р] код']
					);
					continue;
				}
				
				if( empty( $aRegion ) )
				{
					$aErrorMsg[] = array(
						'msg' => sprintf( " Некоректен код за регион : %s ", $sRegion ),
						'row' => $i,
						'col' => $aHeader['[Р] код']
					);
					continue;
				}
				
				// За кой обект е наработката / оддръжката
				$sObject = trim( $aData['cells'][$i][ $aHeader['номер'] ] );
				$aObject = array();
				$aObject['id'] = 0;
				if( !empty( $sObject ) )
				{
					if( $nResult = $oObjects->getRecordByField( $sObject, 'num', $aObject ) != DBAPI_ERR_SUCCESS )
					{
						$aErrorMsg[] = array(
							'msg' => sprintf( " Проблем при извличане информацията за обекта : %s ", $sObject ),
							'row' => $i,
							'col' => $aHeader['номер']
						);
						continue;
					}
				}
				
				// всички записи за служителя за конкретния месец за конкретния тип -> в архив
				$sQuery = sprintf( "
							UPDATE %s
							SET
								to_arc=1,
								updated_user = %s,
								updated_time = NOW()
							WHERE
								id_person = %s
								AND month = %s
								AND code = '%s'
								AND id_office = %d
								AND id_object = %d
						",
						$this->_oBase->_sTableName,
						$_SESSION['userdata']['id'],
						$aPerson['id'],
						$nYear*100 + $nMonth,
						$sSalaryType,
						$aRegion['id'],
						$aObject['id']
					);
				
				$oRes = $db_personnel->Execute( $sQuery );
				if( !$oRes )
				{
					APILog::Log( DBAPI_ERR_SQL_QUERY, $db_personnel->errorMsg(), __FILE__, __LINE__ );
					APILog::Log( DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__ );
					$aErrorMsg[] = array(
						'msg' => " Проблем при запазване на наработката / оддръжката",
						'row' => $i,
						'col' => 0
					);
				}
				
				// запис на реда
				$bIsEarning = in_array( $sSalaryType, $aSalaryEarnings );
				
				$aSalary = array();
				$aSalary['id_person'] 		= $aPerson['id'];
				$aSalary['month'] 			= $nYear*100 + $nMonth;
				$aSalary['code'] 			= $sSalaryType;
				$aSalary['is_earning'] 		= $bIsEarning;
				$aSalary['id_office'] 		= $aRegion['id'];
				$aSalary['id_object'] 		= $aObject['id'];
				$aSalary['sum'] 			= (double) str_replace( ",", ".", $aData['cells'][$i][ $aHeader['ед.цена'] ] );
				$aSalary['count'] 			= (double) str_replace( ",", ".", $aData['cells'][$i][ $aHeader['кол.'] ] );
				$aSalary['total_sum'] 		= $aSalary['sum'] * $aSalary['count'];
				$aSalary['created_time'] 	= time();
				$aSalary['created_user'] 	= $_SESSION['userdata']['id'];
				$aSalary['updated_time'] 	= time();
				$aSalary['updated_user'] 	= $_SESSION['userdata']['id'];
				
				if( $this->_oBase->update( $aSalary ) != DBAPI_ERR_SUCCESS )
				{
					$aErrorMsg[] = array(
						'msg' => " Проблем при запазване на наработката / оддръжката",
						'row' => $i,
						'col' => 0
					);
				}
			}
			
		}
		function ProccessDataMtel( $aData, &$aErrorMsg, $aParams )
		{
			global $oResponse, $db_personnel;
					
			$nYeraMonth = !empty( $aParams['year_month'] ) ? $aParams['year_month'] : 0;
			$nYear 	= (int) substr( $nYeraMonth , 0, 4 ) ;
			$nMonth = (int) substr( $nYeraMonth , 4, 2 ) ;
			if( ($nMonth < 1 ) || ($nMonth > 12) || ($nYear<2007) || ($nYear>2050) )
			{
				$aErrorMsg[] = array(
					'msg' => sprintf( " Некоректен месец за оддръжката: %s ", $nYeraMonth ),
					'row' => 0,
					'col' => 0
				);
				return DBAPI_ERR_INVALID_PARAM;
			}
			
			$aHeader = array();
			for( $i=1; $i<=$aData['numCols']; $i++ )
				$aHeader[ $aData['cells'][1][$i] ] = $i;
			
			$oPersons = new DBBase( $db_personnel, 'personnel' );
			$oSalaryExpenses = new DBBase( $db_personnel, 'salary_expense_types' );
			
			$aSalaryExpensesGSM = array();
			if( $nResult = $oSalaryExpenses -> getRecordByField( 1, 'is_GSM', $aSalaryExpensesGSM ) != DBAPI_ERR_SUCCESS )
			{
				$aErrorMsg[] = array(
					'msg' => " Проблем при извличане на типовете оддръжки за GSM",
					'row' => 0,
					'col' => 0
				);
				return $nResult;
			}
			
			for( $i=2; $i<=$aData['numRows']; $i++ )
			{
				
				// Телефонен номер
				$sPhoneNum = $aData['cells'][$i][ $aHeader['Телефонен номер'] ];
				if( substr( $sPhoneNum, 0, 3 ) == "359" )
					$sPhoneNum = substr( $sPhoneNum, 3 );
				
				$aPersons = array();
				$aWhere[0] = " mobile LIKE '%{$sPhoneNum}%' OR mphones LIKE '%{$sPhoneNum}%' ";
				if( $oPersons->getResult( $aPersons, NULL, $aWhere ) != DBAPI_ERR_SUCCESS )
				{
					$aErrorMsg[] = array(
						'msg' => sprintf( " Проблем при извличане на данни за служителите " ),
						'row' => $i,
						'col' => $aHeader['Телефонен номер']
					);
					continue;
				}
				
				if( empty($aPersons) )
				{
					$aErrorMsg[] = array(
						'msg' => sprintf( " Не е намерен служител с телефон %s ", $sPhoneNum ),
						'row' => $i,
						'col' => $aHeader['Телефонен номер']
					);
					continue;
				}
				
				if( count($aPersons) > 1 )
				{
					$aPersonsNames = array();
					
					foreach( $aPersons as $aPerson )
						$aPersonsNames[] = sprintf( "[%s] %s %s %s", $aPerson['code'], $aPerson['fname'], $aPerson['mname'], $aPerson['lname'] );
					
					$aErrorMsg[] = array(
						'msg' => sprintf( " телефон %s e привързан към по вече от 1 служител : %s", $sPhoneNum, implode( ", ", $aPersonsNames ) ),
						'row' => $i,
						'col' => $aHeader['Телефонен номер']
					);
					continue;
				}
				
				$aPerson = current( $aPersons );
				
				// всички записи за служителя за конкретния месец за конкретния тип -> в архив
				$sQuery = sprintf( "
							UPDATE %s
							SET
								to_arc=1,
								updated_user = %s,
								updated_time = NOW()
							WHERE
								id_person = %s
								AND month = %s
								AND code = '%s'
						",
						$this->_oBase->_sTableName,
						$_SESSION['userdata']['id'],
						$aPerson['id'],
						$nYear*100 + $nMonth,
						$aSalaryExpensesGSM['code']
					);
				
				$oRes = $db_personnel->Execute( $sQuery );
				if( !$oRes )
				{
					APILog::Log( DBAPI_ERR_SQL_QUERY, $db_personnel->errorMsg(), __FILE__, __LINE__ );
					APILog::Log( DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__ );
					$aErrorMsg[] = array(
						'msg' => " Проблем при запазване на наработката / оддръжката",
						'row' => $i,
						'col' => 0
					);
				}
				
				// Сума
				$dPrice = (double) str_replace( ",", ".", $aData['cells'][$i][ $aHeader['Такса (лв.)'] ] );
				
				// Запис на оддръжката
				$aSalary = array();
				$aSalary['id_person'] 	= $aPerson['id'];
				$aSalary['month'] 		= $nYear*100 + $nMonth;
				$aSalary['code'] 		= $aSalaryExpensesGSM['code'];
				$aSalary['description'] = sprintf( "телефон : %s", $sPhoneNum );
				$aSalary['is_earning'] 	= 0;
				$aSalary['id_office'] 	= $aPerson['id_office'];
				$aSalary['id_object'] 	= $aPerson['id_region_object'];
				$aSalary['sum'] 		= $dPrice;
				$aSalary['count'] 		= 1;
				$aSalary['total_sum'] 	= $dPrice;
				
				if( $this->_oBase->update( $aSalary ) != DBAPI_ERR_SUCCESS )
				{
					$aErrorMsg[] = array(
						'msg' => " Проблем при запазване на наработката / оддръжката",
						'row' => $i,
						'col' => 0
					);
				}
			}
		}
	}
	
	$oHandler = new MyHandler( $oSalary, 'person_code', 'sales', 'Работни заплати' );
	
	$oHandler->Handler( $aParams );
	
?>
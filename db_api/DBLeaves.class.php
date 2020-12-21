<?php
	require_once('include/db_include.inc.php');
	
	class DBLeaves {
		
		/**
		 * Проверка дали има молба за отпуск без резолюция за посочената дата.
		 *
		 * @param int $nIDPerson
		 * @param int $nDay
		 * @param int $nMonth
		 * @param int $nYear
		 * @return array
		 */
		public function isThereApplicationForDate( $nIDPerson, $nDay, $nMonth, $nYear )
		{
			global $db_personnel;
			
			$aEmptyArray = array( "leave_from" => "", "leave_to" => "", "leave_type" => "", "is_confirm" => "", "is_confirm_num" => "" );
			
			$nTime = mktime( 0, 0, 0, $nMonth, $nDay, $nYear );
			
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )return $aEmptyArray;
			
				$sQuery = "
				SELECT
					IF
					(
						is_confirm = 1,
						DATE_FORMAT( res_leave_from, '%d.%m.%Y' ),
						DATE_FORMAT( leave_from, '%d.%m.%Y' )
					) AS leave_from,
					IF
					(
						is_confirm = 1,
						DATE_FORMAT( res_leave_to, '%d.%m.%Y' ),
						DATE_FORMAT( leave_to, '%d.%m.%Y' )
					) AS leave_to,
					CASE leave_types
						WHEN 'due' THEN 'платен'
						WHEN 'unpaid' THEN 'неплатен'
						ELSE ''
					END AS leave_type,
					IF( is_confirm = 1, 'потвърден', 'непотвърден' ) AS is_confirm,
					is_confirm AS is_confirm_num
				FROM
					person_leaves
				WHERE
					to_arc = 0
					AND id_person = {$nIDPerson}
					AND type = 'application'
					AND IF
					(
						is_confirm = 1,
						UNIX_TIMESTAMP( res_leave_from ) <= {$nTime},
						UNIX_TIMESTAMP( leave_from ) <= {$nTime}
					)
					AND IF
					(
						is_confirm = 1,
						UNIX_TIMESTAMP( res_leave_to ) >= {$nTime},
						UNIX_TIMESTAMP( leave_to ) >= {$nTime}
					)
			";
			
			$oRes = $db_personnel->Execute( $sQuery );
			
			if( $oRes )
			{
				if( !$oRes->EOF )
				{
					$aData = $oRes->fields;
					
					return $aData;
				}
			}
			
			return $aEmptyArray;
		}
		
		/**
		 * Проверка дали времевия период застъпва молба за отпуск.
		 *
		 * @param int $nIDPerson
		 * @param string $sStartDateTime
		 * @param string $sEndDateTime
		 * @param int $nIDExceptionApp ( Изключение )
		 * 
		 * @return bool
		 */
		public function isThereApplication( $nIDPerson, $sStartDateTime, $sEndDateTime, $nIDExceptionApp = 0 )
		{
			global $db_personnel;
			
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )return false;
			
			$sQuery = "
				SELECT
					*
				FROM
					person_leaves
				WHERE
					to_arc = 0
					AND id_person = {$nIDPerson}
					AND type = 'application'
					AND IF
					(
						is_confirm = 0,
						NOT
						(
							(
								UNIX_TIMESTAMP( leave_from ) < UNIX_TIMESTAMP( '{$sStartDateTime}' )
								AND
								UNIX_TIMESTAMP( leave_to ) < UNIX_TIMESTAMP( '{$sStartDateTime}' )
							)
							OR
							(
								UNIX_TIMESTAMP( leave_from ) > UNIX_TIMESTAMP( '{$sEndDateTime}' )
								AND
								UNIX_TIMESTAMP( leave_to ) > UNIX_TIMESTAMP( '{$sEndDateTime}' )
							)
						),
						NOT
						(
							(
								UNIX_TIMESTAMP( res_leave_from ) < UNIX_TIMESTAMP( '{$sStartDateTime}' )
								AND
								UNIX_TIMESTAMP( res_leave_to ) < UNIX_TIMESTAMP( '{$sStartDateTime}' )
							)
							OR
							(
								UNIX_TIMESTAMP( res_leave_from ) > UNIX_TIMESTAMP( '{$sEndDateTime}' )
								AND
								UNIX_TIMESTAMP( res_leave_to ) > UNIX_TIMESTAMP( '{$sEndDateTime}' )
							)
						)
					)
			";
			
			if( !empty( $nIDExceptionApp ) && is_numeric( $nIDExceptionApp ) )
			{
				$sQuery .= "
					AND id != {$nIDExceptionApp}
				";
			}
			
			$oRes = $db_personnel->Execute( $sQuery );
			
			if( $oRes )
			{
				if( !$oRes->EOF )
				{
					$aData = $oRes->GetArray();
					
					return !empty( $aData );
				}
			}
			
			return false;
		}
		
		public function getApplication( $nID )
		{
			global $db_personnel;
			
			if( empty( $nID ) || !is_numeric( $nID ) )return array();
			
			$sQuery = "
				SELECT
					*
				FROM
					person_leaves
				WHERE
					id = {$nID}
				LIMIT 1
			";
			
			$oRes = $db_personnel->Execute( $sQuery );
			
			if( $oRes )if( !$oRes->EOF )return $oRes->fields;
			else return array();
		}
		
		public function getLeavePDFData( $nID )
		{
			global $db_personnel, $db_name_sod;
			
			if( empty( $nID ) || !is_numeric( $nID ) )
			{
				return array();
			}
			
			$sQuery = "
				SELECT
					per_lea.id AS leave_id,
					per_lea.year AS leave_year,
					per_lea.leave_num AS leave_number,
					DATE_FORMAT( per_lea.date, '%d.%m.%Y' ) AS leave_date,
					per_lea.id_person AS person_id,
					CONCAT_WS( ' ', per.fname, per.mname, per.lname ) AS person_name,
					CONCAT( SUBSTR( per.fname, 1, 1 ), '. ', per.lname ) AS person_name_short,
					pos_nc.name AS person_position,
					off.name AS person_office,
					fir.name AS person_department,
					per_lea.application_days_offer AS leave_days,
					CASE( per_lea.leave_types )
						WHEN 'due' THEN 'Платен'
						WHEN 'unpaid' THEN 'Неплатен'
						ELSE ''
					END AS leave_type,
					per_lea.leave_types AS leave_type_latin,
					IF( per_lea.leave_types = 'due', 1, 0 ) AS leave_is_due,
					DATE_FORMAT( per_lea.leave_from, '%d.%m.%Y' ) AS leave_from,
					CONCAT( cod_lea.name, ' ( ', cod_lea.clause_paragraph, ' )' ) AS code_leave_name,
					CONCAT_WS( ' ', per_sub.fname, per_sub.mname, per_sub.lname ) AS person_substitute,
					CONCAT( SUBSTR( per_sub.fname, 1, 1 ), '. ', per_sub.lname ) AS person_substitute_short,
					IF
					(
						per_lea.is_confirm = 1,
						IF( per_lea.application_days > 0, 'Да', 'Не' ),
						''
					) AS leave_resolution,
					IF( per_lea.application_days > 0, 1, 0 ) AS leave_is_allowed,
					IF
					(
						per_lea.is_confirm = 1,
						per_lea.application_days,
						''
					) AS leave_res_days,
					DATE_FORMAT( per_lea.res_leave_from, '%d.%m.%Y' ) AS leave_res_from,
					fir.jur_name AS firm_jur_name,
					fir.jur_mol AS person_head,
					per_lea.is_confirm AS is_confirm,
					DATE_FORMAT( per_lea.confirm_time, '%d.%m.%Y' ) AS confirm_date
				FROM
					person_leaves per_lea
				LEFT JOIN
					code_leave cod_lea ON cod_lea.id = per_lea.id_code_leave
				LEFT JOIN
					personnel per_sub ON per_sub.id = per_lea.id_person_substitute
				LEFT JOIN
					personnel per ON per.id = per_lea.id_person
				LEFT JOIN
					person_contract per_cont ON ( per_cont.id_person = per.id AND per_cont.to_arc = 0 )
				LEFT JOIN
					positions_nc pos_nc ON pos_nc.id = per.id_position_nc
				LEFT JOIN
					{$db_name_sod}.offices off ON off.id = per.id_office
				LEFT JOIN
					{$db_name_sod}.firms fir ON fir.id = off.id_firm
				WHERE
					per_lea.id = {$nID}
				LIMIT 1
			";
			
			$rs = $db_personnel->Execute( $sQuery );
			
			$aData = array();
			
			if( $rs )if( !$rs->EOF )$aData = $rs->fields;
			
			if( !empty( $aData ) )
			{
				$aPersonHeadNames = explode( " ", $aData['person_head'] );
				if( !empty( $aPersonHeadNames ) )
				{
					$aData['person_head_short'] = utf8_substr( $aData['person_head'], 0, 1 ) . ". " . end( $aPersonHeadNames );
				}
				
				//Remain Days
				$nDaysRemaining = $this->getRemainingLeaveDays( $aData['leave_year'], $aData['person_id'], $aData['leave_id'] );
				
				if( $aData['leave_type_latin'] != "unpaid" )
				{
					if( $aData['is_confirm'] == 1 )
					{
						$aData['remain_days'] = $nDaysRemaining - ( ( int ) $aData['leave_res_days'] );
					}
					else
					{
						$aData['remain_days'] = $nDaysRemaining - ( ( int ) $aData['leave_days'] );
					}
				}
				else
				{
					$aData['remain_days'] = $nDaysRemaining;
				}
				
				if( $aData['remain_days'] < 0 )$aData['remain_days'] = "Молбата превишава разрешения брой дни за годината!";
				else $aData['remain_days'] = "Оставащи дни за годината: " . $aData['remain_days'];
				//End Remain Days
			}
			
			return $aData;
		}
		
		function getResultExtended( $aParams, &$aPaging = array() )
		{
			global $db_personnel, $db_name_sod;
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					per_lea.id AS id,
					per_lea.leave_num AS leave_num,
					IF
					(
						per_lea.date != '0000-00-00 00:00:00',
						DATE_FORMAT( per_lea.date, '%d.%m.%Y' ),
						''
					) AS date,
					per_lea.date AS date_,
					
					CONCAT_WS( ' ', per.fname, per.mname, per.lname ) AS person_name,
					per.id AS id_person,
					per_lea.id_person_substitute AS id_person_substitute,
					
					pos_nc.name AS person_position,
					fir.name AS firm,
					off.name AS office,
					obj.name AS object,
					
					CASE( per_lea.leave_types )
						WHEN 'due' THEN 'Платен'
						WHEN 'unpaid' THEN 'Неплатен'
						ELSE ''
					END AS leave_type,
					per_lea.leave_types AS raw_leave_types,
					
					IF
					(
						per_lea.leave_from != '0000-00-00 00:00:00',
						DATE_FORMAT( per_lea.leave_from, '%d.%m.%Y' ),
						''
					) AS leave_from,
					per_lea.leave_from AS leave_from_,
					DATE_FORMAT( per_lea.leave_from, '%Y-%m-%d' ) AS raw_leave_from,
					IF
					(
						per_lea.leave_to != '0000-00-00 00:00:00',
						DATE_FORMAT( per_lea.leave_to, '%d.%m.%Y' ),
						''
					) AS leave_to,
					per_lea.leave_to AS leave_to_,
					
					per_lea.application_days_offer AS application_days,
					cod_lea.clause_paragraph AS code_leave_name,
					cod_lea.id AS id_code_leave,
					CONCAT_WS( ' ', per_cre.fname, per_cre.mname, per_cre.lname ) AS created_user,
					IF
					(
						per_lea.is_confirm,
						IF
						(
							per_lea.application_days = 0,
							'Неразрешен',
							'Потвърден'
						),
						'Непотвърден'
					) AS status,
					IF( per_cont.fix_cost != 0, 1, 0 ) AS state_salary,
					
					IF
					(
						per_lea.res_leave_from != '0000-00-00 00:00:00',
						DATE_FORMAT( per_lea.res_leave_from, '%d.%m.%Y' ),
						''
					) AS res_leave_from,
					per_lea.res_leave_from AS res_leave_from_,
					IF
					(
						per_lea.res_leave_to != '0000-00-00 00:00:00',
						DATE_FORMAT( per_lea.res_leave_to, '%d.%m.%Y' ),
						''
					) AS res_leave_to,
					per_lea.res_leave_to AS res_leave_to_,
					
					per_lea.application_days AS res_application_days,
					
					per_lea.is_confirm,
					IF
					(
						per_lea.confirm_user,
						CONCAT_WS( ' ', per_con.fname, per_con.mname, per_con.lname ),
						''
					) AS person_confirm,
					IF
					(
						per_lea.confirm_time != '0000-00-00 00:00:00',
						DATE_FORMAT( per_lea.confirm_time, '%d.%m.%Y' ),
						''
					) AS time_confirm,
					per_lea.confirm_time AS time_confirm_
				FROM
					person_leaves per_lea
				LEFT JOIN
					code_leave cod_lea ON cod_lea.id = per_lea.id_code_leave
				LEFT JOIN
					personnel per_con ON per_con.id = per_lea.confirm_user
				LEFT JOIN
					personnel per ON per.id = per_lea.id_person
				LEFT JOIN
					person_contract per_cont ON ( per_cont.id_person = per.id AND per_cont.to_arc = 0 )
				LEFT JOIN
					positions_nc pos_nc ON pos_nc.id = per.id_position_nc
				LEFT JOIN
					{$db_name_sod}.offices off ON off.id = per.id_office
				LEFT JOIN
					{$db_name_sod}.firms fir ON fir.id = off.id_firm
				LEFT JOIN
					{$db_name_sod}.objects obj ON obj.id = per.id_region_object
				LEFT JOIN
					personnel per_cre ON per_cre.id = per_lea.created_user
				WHERE
					per_lea.to_arc = 0
					AND per_lea.type = 'application'
					AND ( per_lea.leave_types = 'due' OR per_lea.leave_types = 'unpaid' )
			";
			
			if( isset( $aParams['nIDFirm'] ) && !empty( $aParams['nIDFirm'] ) )
			{
				$sQuery .= "
					AND fir.id = {$aParams['nIDFirm']}
				";
			}
			if( isset( $aParams['nIDOffice'] ) && !empty( $aParams['nIDOffice'] ) )
			{
				$sQuery .= "
					AND off.id = {$aParams['nIDOffice']}
				";
			}
			if( isset( $aParams['nIDObject'] ) && !empty( $aParams['nIDObject'] ) )
			{
				$sQuery .= "
					AND obj.id = {$aParams['nIDObject']}
				";
			}
			if( isset( $aParams['nIsConfirm'] ) && $aParams['nIsConfirm'] != 2 )
			{
				if( $aParams['nIsConfirm'] != 3 )
				{
					$sQuery .= "
						AND per_lea.is_confirm = {$aParams['nIsConfirm']}
					";
				}
				else
				{
					$sQuery .= "
						AND per_lea.is_confirm = 1
						AND per_lea.application_days = 0
					";
				}
			}
			if( isset( $aParams['sDateFrom'] ) && !empty( $aParams['sDateFrom'] ) )
			{
				$sQuery .= "
					AND UNIX_TIMESTAMP( per_lea.leave_from ) >= UNIX_TIMESTAMP( '{$aParams['sDateFrom']} 00:00:00' )
				";
			}
			if( isset( $aParams['sDateTo'] ) && !empty( $aParams['sDateTo'] ) )
			{
				$sQuery .= "
					AND UNIX_TIMESTAMP( per_lea.leave_from ) <= UNIX_TIMESTAMP( '{$aParams['sDateTo']} 23:59:59' )
				";
			}
			if( isset( $aParams['sPersonName'] ) && !empty( $aParams['sPersonName'] ) )
			{
				$sQuery .= "
					AND CONCAT_WS( ' ', per.fname, per.mname, per.lname ) LIKE '%{$aParams['sPersonName']}%'
				";
			}
			
			//Sorting
			$aColumnIndexes = array();
			$aColumnIndexes[0] = "leave_num";
			$aColumnIndexes[1] = "date_";
			$aColumnIndexes[2] = "person_name";
			$aColumnIndexes[3] = "person_position";
			$aColumnIndexes[4] = "firm";
			$aColumnIndexes[5] = "office";
			$aColumnIndexes[6] = "object";
			$aColumnIndexes[7] = "leave_type";
			$aColumnIndexes[8] = "leave_from_";
			$aColumnIndexes[9] = "leave_to_";
			$aColumnIndexes[10] = "application_days";
			$aColumnIndexes[11] = "code_leave_name";
			$aColumnIndexes[12] = "created_user";
			$aColumnIndexes[13] = "status";
			$aColumnIndexes[14] = "time_confirm_";
			$aColumnIndexes[15] = "res_leave_from_";
			$aColumnIndexes[16] = "res_leave_to_";
			$aColumnIndexes[17] = "res_application_days";
			$aColumnIndexes[18] = "person_confirm";
			
			if( !isset( $aPaging['sortCol'] ) )$aPaging['sortCol'] = 0;
			if( !isset( $aPaging['sortType'] ) )$aPaging['sortType'] = 0;
			
			$sSortField = $aColumnIndexes[$aPaging['sortCol']];
			$sSortType = $aPaging['sortType'] == 0 ? "ASC" : "DESC";
			
			$sQuery .= "
				ORDER BY {$sSortField} {$sSortType}
			";
			//End Sorting
			
			//Paging
			if( !isset( $aParams['api_action'] ) || ( $aParams['api_action'] != "export_to_pdf" && $aParams['api_action'] != "export_to_xls" ) )
			{
				if( isset( $aPaging['current_page'] ) && !empty( $aPaging['current_page'] ) )$nCurrentPage = $aPaging['current_page'];
				else
				{
					$aPaging['current_page'] = 1;
					$nCurrentPage = 1;
				}
				
				$aPaging['page_size'] = $_SESSION['userdata']['row_limit'];
				$nRowCount = $aPaging['page_size'];
				$nRowOffset = ( $nCurrentPage - 1 ) * $nRowCount;
				
				$sQuery .= sprintf( "LIMIT %s, %s", $nRowOffset , $nRowCount );
			}
			//End Paging
			
			$rs = $db_personnel->Execute( $sQuery );
			
			$aData = array();
			
			if( $rs )if( !$rs->EOF )$aData = $rs->GetArray();
			
			//Paging
			if( !isset( $aParams['api_action'] ) || ( $aParams['api_action'] != "export_to_pdf" && $aParams['api_action'] != "export_to_xls" ) )
			{
				$rs = $db_personnel->Execute( "SELECT FOUND_ROWS()" );
				
				if( !$rs || $rs->EOF )
				{
					$aPaging['max_rows'] = 0;
					$aPaging['max_pages'] = 1;
				}
				else
				{
					$aPaging['max_rows'] = current( $rs->FetchRow() );
					$aPaging['max_pages'] = ceil( $aPaging['max_rows'] / $aPaging['page_size'] );
				}
			}
			//End Paging
			
			return $aData;
		}
		
		function getRemainingLeaveDays( $nYear, $nIDPerson, $nIDAppException = 0 )
		{
			global $db_personnel;
			
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )return 0;
			
			$sQuery = "
				SELECT
					pl.year,
					(
						SELECT
							( SUM( pl2.due_days ) - SUM( pl2.application_days ) )
						FROM
							person_leaves pl2
						LEFT JOIN
							code_leave cl ON cl.id = pl2.id_code_leave
						WHERE
							pl2.to_arc = 0
							AND IF
							(
								pl2.id_code_leave != 0,
								(
									cl.to_arc = 0
									AND cl.is_due_leave = 1
								),
								1
							)
							AND
							(
								pl2.leave_types = 'due'
								OR
								pl2.leave_types = 'student'
								OR
								pl2.leave_types = 'quittance'
							)
							AND pl2.year <= pl.year
							AND pl2.id_person = '{$nIDPerson}'
			";
			
			if( !empty( $nIDAppException ) )
			{
				$sQuery .= "
							AND pl2.id != $nIDAppException
				";
			}
			
			$sQuery .= "
					) AS used_days_all
				FROM
					person_leaves pl
				WHERE
					pl.id_person = '{$nIDPerson}'
					AND pl.year = '{$nYear}'
					AND pl.type = 'leave'
					AND pl.leave_types = 'due'
					AND pl.to_arc = 0
			";
			
			$rs = $db_personnel->Execute( $sQuery );
			
			$aData = array();
			$nRemainDays = 0;
			
			if( $rs )
			{
				if( !$rs->EOF )
				{
					$aData = $rs->GetArray();
					
					foreach( $aData as $val )
					{
						$nRemainDays = $val['used_days_all'];
					}
				}
				else return 0;
				
				return $nRemainDays;
			}
			else return 0;
		}
		
		function getResult($nID, $sSortField, $nSortType, $nPage, &$oResponse) {
			global $db_personnel;
			$db->debug=true;
			
			$id = (int) $nID;
			$nDate = date( 'Y', mktime(0, 0, 0, date("m"), date("d"), date("Y")) );

			$sQuery = sprintf(" 
				SELECT SQL_CALC_FOUND_ROWS
					t.id as _id,
					t.id as id,
					t.id_person,
					t.year,
					t.due_days,
					'' as remain,
					(SELECT SUM(due_days) FROM person_leaves WHERE to_arc=0 AND type = 'leave' AND leave_types = 'due' AND year <= '%s' AND id_person = '%d') AS due,
					(SELECT SUM(application_days) FROM person_leaves WHERE to_arc=0 AND type = 'hospital' AND year = t.year AND id_person = '%d') AS hospital,
					(
						SELECT
							SUM( pl.application_days )
						FROM
							person_leaves pl
						LEFT JOIN
							code_leave cl ON cl.id = pl.id_code_leave
						WHERE
							pl.to_arc = 0
							AND pl.leave_types = 'quittance'
							AND pl.year = t.year
							AND pl.id_person = '%d'
					) AS quittances,
					(
						SELECT
							SUM( pl.application_days )
						FROM
							person_leaves pl
						LEFT JOIN
							code_leave cl ON cl.id = pl.id_code_leave
						WHERE
							pl.to_arc = 0
							AND IF
							(
								pl.id_code_leave != 0,
								(
									cl.to_arc = 0
									AND cl.is_due_leave = 1
								),
								1
							)
							AND pl.type = 'application'
							AND pl.leave_types = 'due'
							AND pl.year = t.year
							AND pl.id_person = '%d'
					) AS used_days,
					(
						SELECT
							SUM( pl.application_days_offer )
						FROM
							person_leaves pl
						WHERE
							pl.to_arc = 0
							AND pl.is_confirm = 0
							AND pl.type = 'application'
							AND pl.year = t.year
							AND pl.id_person = '%d'
					) AS unconfirm,
					(
						SELECT
							GROUP_CONCAT( CONCAT( pl.id, ' / ', DATE_FORMAT( pl.leave_from, '%%d.%%m.%%Y' ) ) SEPARATOR '\\n' )
						FROM
							person_leaves pl
						WHERE
							pl.to_arc = 0
							AND pl.is_confirm = 0
							AND pl.type = 'application'
							AND pl.year = t.year
							AND pl.id_person = '%d'
					) AS unconfirm_leaves,
					(
						SELECT
							SUM( pl.application_days )
						FROM
							person_leaves pl
						LEFT JOIN
							code_leave cl ON cl.id = pl.id_code_leave
						WHERE
							IF
							(
								pl.id_code_leave != 0,
								(
									pl.to_arc = 0
									AND cl.to_arc = 0
									AND cl.is_due_leave = 0
									AND cl.leave_type = 'paid'
									AND pl.id_person = '%d'
									AND pl.type = 'application'
									AND pl.year = t.year
								),
								0
							)
					) AS used_extra_days,
					(SELECT SUM(application_days) FROM person_leaves WHERE to_arc=0 AND type = 'application' AND leave_types = 'unpaid' AND year = t.year AND id_person = '%d') AS unpaid,
					(SELECT SUM(application_days) FROM person_leaves WHERE to_arc=0 AND type = 'application' AND leave_types = 'student' AND year = t.year AND id_person = '%d') AS student,
					(
						SELECT
							( SUM( pl.due_days ) - SUM( pl.application_days ) )
						FROM
							person_leaves pl
						LEFT JOIN
							code_leave cl ON cl.id = pl.id_code_leave
						WHERE
							pl.to_arc = 0
							AND IF
							(
								pl.id_code_leave != 0,
								(
									cl.to_arc = 0
									AND cl.is_due_leave = 1
								),
								1
							)
							AND
							(
								pl.leave_types = 'due'
								OR
								pl.leave_types = 'student'
								OR
								pl.leave_types = 'quittance'
							)
							AND pl.year <= t.year
							AND pl.id_person = '%d'
					) AS cused_alldays,
					CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' (', DATE_FORMAT(t.updated_time,'%%d.%%m.%%y %%H:%%i:%%s'), ')') AS updated_user
				FROM 
					person_leaves t 
				LEFT JOIN personnel as up on up.id = t.updated_user
				WHERE t.id_person = '%d'
					AND t.type = 'leave'
					AND t.leave_types = 'due'
					AND t.to_arc = 0
				GROUP BY t.id
				",
				$nDate, $id, $id, $id, $id, $id, $id, $id, $id, $id, $id, $id
			);
			
			//  $nDate, $id, $nDate, $id, $id, $id, $id, $id
			// (SELECT SUM(application_days) FROM person_leaves WHERE to_arc=0 AND type = 'application' AND leave_types = 'student' AND year = t.year AND id_person = '%d') AS student,
			// (SELECT SUM(application_days) FROM person_leaves WHERE to_arc=0 AND type = 'application' AND leave_types = 'due' AND year <= '%s'  AND id_person = '%d') AS used_alldays,
			// (SELECT SUM(application_days) FROM person_leaves WHERE to_arc=0 AND type = 'application' AND leave_types = 'student' AND year <= '%s'  AND id_person = '%d') AS used_allpayed,
//			APILog::Log(0, $sQuery);
			if( !empty( $sSortField ) ) {	
				$sQuery .= sprintf(
					"ORDER BY %s %s\n"
					, $sSortField
					, $nSortType == DBAPI_SORT_ASC ? "DESC" : "ASC"
				);

				$oResponse->setSort( $sSortField, $nSortType );

			}
			
			if( !empty( $nPage ) ) {	
				$nRowCount = $_SESSION['userdata']['row_limit'];
				$nRowOffset = ($nPage - 1) * $nRowCount;
				 
				$sQuery .= sprintf(
					"LIMIT %s, %s"
					, $nRowOffset
					, $nRowCount 
					);
			};
			
			// Извличане на резултата
			$rs = $db_personnel->Execute( $sQuery );
			$aData = array();
			if ( $rs ) {
				if ( !$rs->EOF ) {			
					$aData = $rs->GetArray();
					
					foreach ( $aData as &$val ) {
						if ( $val['year'] == $nDate ) {
							$val['remain'] = $val['cused_alldays']; //$val['due'] - $val['used_alldays'] - $val['used_allpayed'];
						} elseif ( $val['year'] < $nDate ) {
							$val['remain'] =  $val['cused_alldays']; //$val['due_days'] - ($val['used_days'] + $val['student']); // + $val['cused_alldays']
						} else {
							$val['remain'] = $val['due_days'] - $val['used_days'] - $val['student'];
						}
						
						$val['due_days_all'] = $val['due_days'] + $this->getRemainingLeaveDays( $val['year'] - 1, $id );
					}

					$oResponse->setData( $aData );
				}
			} else {
				return DBAPI_ERR_SQL_QUERY;
			}
			
			// Извличане на броя на записите за цялата справка
			$rs = $db_personnel->Execute("SELECT FOUND_ROWS()");
			
			if( !$rs || $rs->EOF )
				return DBAPI_ERR_SQL_QUERY;

			// Установяване на паремтрите по paging-a
			if( !empty( $nPage ) ) {	
				$nRowTotal = current( $rs->FetchRow() );;
				
				$oResponse->setPaging(
					$nRowCount,
					$nRowTotal,
					ceil($nRowOffset / $nRowCount) + 1
					);
			}
			
			return DBAPI_ERR_SUCCESS;
		}
		
		function update(&$aData) {
			global $db_personnel;
			
			// WTF ?!!
//			$id = $aData['id'];
//			
//			if( empty( $id ) )
//				$id = -1;

			$id = isset($aData['id']) && is_numeric($aData['id']) ? $aData['id'] : -1;
							
			$rs = $db_personnel->Execute("SELECT * FROM person_contract WHERE id = {$id}");
				
			if( !$rs )
				return DBAPI_ERR_SQL_QUERY;
				
			$aData['updated_user'] = $_SESSION['userdata']['id_person'];
			$aData['updated_time'] = date('Y-m-d H:i:s');	
				
			if( $id == -1 )	{
				if( !$db_personnel->Execute( $db_personnel->GetInsertSQL($rs, $aData) ) )
					return DBAPI_ERR_SQL_QUERY;
				$aData['id'] = $db_personnel->Insert_ID();	
			} else {
				if( !$db_personnel->Execute( $db_personnel->GetUpdateSQL($rs, $aData) ) )
					return DBAPI_ERR_SQL_QUERY;
			}
			
			return DBAPI_ERR_SUCCESS;
		}

		function delete( $nID ) {
			global $db_personnel;
			
			$nID = (int) $nID;
			
			if( empty( $nID ) )
				return DBAPI_ERR_INVALID_PARAM;

			$rs = $db_personnel->Execute("SELECT * FROM person_leaves WHERE id = {$nID}");
				
			if( !$rs )
				return DBAPI_ERR_SQL_QUERY;
				
			$aData = array();
			$aData['updated_user']	= $_SESSION['userdata']['id_person'];	
			$aData['updated_time']	= time();
			$aData['to_arc']		= 1;
			$db_personnel->GetUpdateSQL($rs, $aData);
			if( !$db_personnel->Execute( $db_personnel->GetUpdateSQL($rs, $aData) ) )
				return DBAPI_ERR_SQL_QUERY;
			
			return DBAPI_ERR_SUCCESS;
		}

		function getResultOnce($nID, &$oData) {
			global $db_personnel;
			$oData = array();
			
			$id = (int) $nID;
			
			$sQuery = sprintf(" 
				SELECT 
					t.id,
					t.id as _id, 
					t.id_person,
					t.type_salary,
					t.fix_cost,
					t.min_cost,
					t.insurance,
					IF ( UNIX_TIMESTAMP(t.trial_from) > 315525600, DATE_FORMAT(t.trial_from, '%%d.%%m.%%Y'), '') as trial_from,
					IF ( UNIX_TIMESTAMP(t.trial_to) > 315525600, DATE_FORMAT(t.trial_to, '%%d.%%m.%%Y'), '') as trial_to,
					t.serve,
					p.tech_support_factor as factor,
					p.shifts_factor,
					t.rate_reward,
					t.class AS class
				FROM 
					person_contract t 
				LEFT JOIN personnel as p on p.id = t.id_person
				LEFT JOIN personnel as up on up.id = t.updated_user
				WHERE t.id_person = '%d' 
					AND t.to_arc = 0
				", 
				$id
			);
			
			//APILog::log(0, $sQuery);
			$rs = $db_personnel->Execute( $sQuery );
			if ( $rs ) {
				if (!$rs->EOF) {
					$oData = $rs->fields;
				}
			} else {
				return DBAPI_ERR_SQL_QUERY;
			}

			return DBAPI_ERR_SUCCESS;		
		}
	}
?>
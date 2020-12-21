<?php

class DBSalary
	extends DBBase2 
{
		
	public function __construct() 
	{
		global $db_personnel;
		//$db_personnel->debug=true;
		
		parent::__construct($db_personnel, 'salary');
	}
	
	public function getSalaryRowByApplication( $nIDApplication )
	{
		if( empty( $nIDApplication ) || !is_numeric( $nIDApplication ) )return 0;
		
		$sQuery = "
			SELECT
				id
			FROM
				salary
			WHERE
				to_arc = 0
				AND id_application = {$nIDApplication}
			LIMIT 1
		";
		
		$aData = $this->selectOnce( $sQuery );
		
		if( !empty( $aData ) && isset( $aData['id'] ) )return $aData['id'];
		else return 0;
	}
	
	public function deleteSalaryRowsByApplication( $nIDApplication )
	{
		global $db_personnel;
		
		if( empty( $nIDApplication ) || !is_numeric( $nIDApplication ) )return DBAPI_ERR_INVALID_PARAM;
		
		$sQuery = "
			UPDATE
				salary
			SET
				to_arc = 1
			WHERE
				to_arc = 0
				AND id_application = {$nIDApplication}
		";
		
		$oRes = $db_personnel->Execute( $sQuery );
		
		if( !$oRes )return DBAPI_ERR_SQL_QUERY;
		else return DBAPI_ERR_SUCCESS;
	}
	
	public function transferMonthObjectSalaryFromDuty( $nIDObject, $nYear, $nMonth )
	{
		global $db_personnel, $db_name_sod,$db_name_personnel;
		
		$db_personnel->debug = true;
		
		if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )
			throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
		if( empty( $nYear ) || !is_numeric( $nYear ) || strlen( $nYear ) != 4  )
			throw new Exception($nYear, DBAPI_ERR_INVALID_PARAM);
			
		if( empty( $nMonth ) || !is_numeric( $nMonth ) || $nMonth < 1 || $nMonth > 12 )
			throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
		$sQuery = sprintf("
			DELETE s 
			FROM salary s
			LEFT JOIN salary_earning_types se ON ( s.code = se.code AND se.to_arc = 0 )
			WHERE 1
				AND s.id_object = {$nIDObject} 
				AND s.month = %04d%02d
				AND se.source = 'schedule'
				"
			, $nYear
			, $nMonth
			);
		
		$sQuery2 = "
			INSERT INTO salary (id_person, id_office, id_object, id_object_duty, month, code, is_earning, sum, description, count, total_sum )
			SELECT 
				od.id_person, 
				o.id_office, 
				od.id_obj, 
				od.id, 
				CONCAT( 
					DATE_FORMAT( od.startRealShift, '%Y' ), 
					DATE_FORMAT( od.startRealShift, '%m' ) 
					), 
				se.code, 
				1, 
				IF (od.stake > 0, IF (pc.rate_reward, ((od.stake*pc.rate_reward)/100), od.stake), IF (pc.rate_reward, ((os.stake*pc.rate_reward)/100), os.stake)) * p.shifts_factor AS stake, 
				IF (od.stake > 0, CONCAT('Дежурство - ', DATE_FORMAT( od.startRealShift, '%d.%m.%Y %H:%i' ), ' [', CONCAT(( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) div 3600, ':', TRUNCATE(((( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) mod 3600) / 60), 0)), ' ч.]' ), CONCAT('ОТПУСК/БОЛНИЧЕН [', DATE_FORMAT( od.startRealShift, '%d.%m.%Y %H:%i' ), ']') ) as name,
				CONCAT(( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) div 3600, '.', (( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) mod 3600) / 60),
				ROUND( ( IF (od.stake > 0, IF (pc.rate_reward, ((od.stake*pc.rate_reward)/100), od.stake), IF (pc.rate_reward, ((os.stake*pc.rate_reward)/100), os.stake)) * ( ( TIME_TO_SEC(os.duration) ) / 3600 ) * p.shifts_factor ), 2 ) 
			FROM {$db_name_sod}.object_duty od
			LEFT JOIN {$db_name_sod}.objects o ON od.id_obj = o.id
			LEFT JOIN personnel p ON p.id = od.id_person
			LEFT JOIN salary_earning_types se ON se.source = 'schedule'
			LEFT JOIN {$db_name_sod}.object_shifts os ON od.id_shift = os.id
			LEFT JOIN person_contract pc ON (pc.to_arc = 0 AND pc.id_person = od.id_person AND UNIX_TIMESTAMP((INTERVAL 1 DAY + trial_from)) <= UNIX_TIMESTAMP(od.startRealShift) AND UNIX_TIMESTAMP((INTERVAL 1 DAY + trial_to)) >= UNIX_TIMESTAMP(od.endRealShift) )
			WHERE 1
				AND od.id_shift > 0
				AND od.startRealShift > 0
				AND od.endRealShift   > 0
				AND od.id_obj = {$nIDObject}
				AND od.endShift > od.startShift
				AND YEAR( od.startRealShift ) = {$nYear}
				AND MONTH( od.startRealShift ) = {$nMonth}
				AND IF (od.stake = 0, IF (os.mode = 'leave' OR os.mode = 'sick', 1, 0) ,1) = 1
				AND os.mode != 'leave' AND os.mode != 'sick'
			";
		//AND od.stake > 0
//				CONCAT(( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) div 3600, '.', ( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) mod 3600),
			
		//				CONCAT(TRUNCATE(( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) / 3600, 0), '.', ( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) / 60) ),

		//		od.stake, 
		//		CONCAT('Дежурство - ', DATE_FORMAT( od.startRealShift, '%d.%m.%Y %H:%i' ), ' [', CONCAT(( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) div 3600, ':', TRUNCATE(((( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) mod 3600) / 60), 0)), ' ч.]' ) as name,
		//		CONCAT(( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) div 3600, '.', (( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) mod 3600) / 60),
		//		ROUND( od.stake * ( ( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) / 3600 ), 2 )


		$db_personnel->StartTrans();
		
		try 
		{
			$this->oDB->Execute( $sQuery );
			$this->oDB->Execute( $sQuery2 );
			
			$db_personnel->CompleteTrans();
		}
		catch( Exception $e )
		{
			$this->FailTrans();
			throw $e;
		}
		
	}
		
	public function objectSalaryFromDuty( $nIDObject, $nTime, $chk ) {
		global $db_personnel, $db_name_sod;
		
		$nIDs = explode(",", $chk);
		
		foreach ($nIDs as $key => &$val) {
			if ( ($key % 2) > 0 ) unset($nIDs[$key]);
		}
		
		$chk = !empty($nIDs) ? implode(",", $nIDs) : -1;
		
		//$db_personnel->debug = true;
		
		if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )
			throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);

		if( empty( $nTime ) || !is_numeric( $nTime ) )
			throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
						
		$nYear = date('Y', $nTime);
		$nMonth = date('m', $nTime);
		
		$sQuery2 = "
			INSERT INTO salary (id_person, id_office, id_object, id_object_duty, month, code, is_earning, sum, description, count, total_sum )
			SELECT 
				od.id_person, 
				o.id_office, 
				od.id_obj, 
				od.id, 
				CONCAT( 
					DATE_FORMAT( od.startRealShift, '%Y' ), 
					DATE_FORMAT( od.startRealShift, '%m' ) 
					), 
				se.code, 
				1, 
				IF (od.stake > 0, IF (pc.rate_reward, ((od.stake*pc.rate_reward)/100), od.stake), IF (pc.rate_reward, ((os.stake*pc.rate_reward)/100), os.stake)) * p.shifts_factor AS stake, 
				IF (od.stake > 0, CONCAT('Дежурство - ', DATE_FORMAT( od.startRealShift, '%d.%m.%Y %H:%i' ), ' [', ROUND((( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) / 3600), 1), ' ч.]' ), CONCAT('ОТПУСК/БОЛНИЧЕН [', DATE_FORMAT( od.startRealShift, '%d.%m.%Y %H:%i' ), ']') ) as name,
				( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) / 3600,
				ROUND( ( IF (od.stake > 0, IF (pc.rate_reward, ((od.stake*pc.rate_reward)/100), od.stake), IF (pc.rate_reward, ((os.stake*pc.rate_reward)/100), os.stake)) * ( ( TIME_TO_SEC(os.duration) ) / 3600 ) * p.shifts_factor ), 2 ) 
			FROM {$db_name_sod}.object_duty od
			LEFT JOIN {$db_name_sod}.objects o ON od.id_obj = o.id
			LEFT JOIN {$db_name_sod}.object_shifts os ON od.id_shift = os.id
			LEFT JOIN salary_earning_types se ON se.source = 'schedule'
			LEFT JOIN personnel p ON p.id = od.id_person
			LEFT JOIN person_contract pc ON (pc.to_arc=0 AND pc.id_person = od.id_person AND UNIX_TIMESTAMP((INTERVAL 1 DAY + trial_from)) <= {$nTime} AND UNIX_TIMESTAMP((INTERVAL 1 DAY + trial_to)) >= {$nTime} )
			WHERE 1
				AND IF (od.stake = 0, IF (os.mode = 'leave' OR os.mode = 'sick', 1, 0) ,1) = 1
				AND od.id_shift > 0
				AND od.startRealShift > 0
				AND UNIX_TIMESTAMP(od.endRealShift) = {$nTime}
				AND od.id_obj = {$nIDObject}
				AND od.endShift > od.startShift
				AND YEAR( od.endRealShift ) = {$nYear}
				AND (
					MONTH( od.startRealShift ) = {$nMonth}
					OR MONTH( od.endRealShift ) = {$nMonth}
				)
				AND od.id IN ({$chk})
				AND os.mode != 'leave' AND os.mode != 'sick'
		";
		
		//AND ( od.stake > 0 OR os.stake > 0 )
		$db_personnel->StartTrans();
		
		try 
		{
//			$this->oDB->Execute( $sQuery );
			$this->oDB->Execute( $sQuery2 );
			
			$db_personnel->CompleteTrans();
		}
		catch( Exception $e )
		{
			$this->FailTrans();
			throw $e;
		}
		
	}
	
	public function getPersonsEarnings($tMonth,$sIDPersons) {
		$sQuery = "
			SELECT
			s.id_person,
			SUM(if(s.is_earning,s.total_sum,0)) as earnings
			FROM salary s
			WHERE 1
				AND s.to_arc = 0
				AND s.month = {$tMonth}
				AND s.id_person IN ({$sIDPersons})
			GROUP BY s.month,s.id_person 
		";
		return $this->selectAssoc($sQuery);
	}
	
	public function getPersonEarningsFinal( $nYearMonth, $nIDPerson )
	{
		$sQuery = "
			SELECT
				SUM( IF( s.is_earning, s.total_sum, 0 ) ) - SUM( IF( s.is_earning, 0, s.total_sum ) ) AS earnings
			FROM salary s
			WHERE 1
				AND s.to_arc = 0
				AND s.month = {$nYearMonth}
				AND s.id_person = {$nIDPerson}
			GROUP BY s.month, s.id_person
		";
		
		return $this->selectOne( $sQuery );
	}
	
	public function getReport1($aData, $oResponse) {
		global $db_name_sod;
		
		$oDBAdminSalaryTotalFilter = new DBAdminSalaryTotalFilters();
		
		$nMonth = $aData['nMonth'];
		$sIDFirms = $aData['sIDFirms'];
		$sIDOffices = $aData['sIDOffices'];
		$sIDObjects = $aData['sIDObjects'];
		$nScheme = $aData['id_scheme'];
		$nPosition = $aData['id_position'];
		$nRadio = $aData['nRadio'];
		
		$nYear = substr($nMonth,0,4);
		
		$sIDOffices = !empty($sIDOffices) ? $sIDOffices : implode(',',$_SESSION['userdata']['access_right_regions']);
		
		$sQuery = " 
			SELECT SQL_CALC_FOUND_ROWS 
				t.id as _id, 
				t.id_person AS id, 
				CONCAT('     ',SUBSTRING(t.month,5),' - ',SUBSTRING(t.month,1,4)) AS month, 
				p.code as person_code,
				CONCAT_WS(' ', p.fname, p.mname, p.lname) as person_name,
				CONCAT( f.name,' (', r.name, ')' ) as person_firm_name,
				CONCAT( o.name,' (', o.num, ')' ) as person_object_name,
			";
		
		if( !empty($nScheme) ) {
			$aFilters = $oDBAdminSalaryTotalFilter->getRecord($nScheme);
			$i = 1;
			$aCodes = explode(',',$aFilters['earnings']);
			$sCodeEarnings = "'".implode("','",$aCodes)."'";
			foreach ( $aCodes as $code ) {
				if( !empty($code) ) {
					$sQuery .= " SUM(if(t.code = '{$code}',total_sum,NULL)) AS code{$i} ,\n";
					$i++;
				}
			}
			$aCodes = explode(',',$aFilters['expenses']);
			$sCodeExpenses = "'".implode("','",$aCodes)."'";
			foreach ( $aCodes as $code ) {
				if( !empty($code) ) {
					$sQuery .= " SUM(if(t.code = '{$code}',total_sum,NULL)) AS code{$i} ,\n";
					$i++;
				}
			}
			$sQuery .= "SUM(if(t.code IN ({$sCodeEarnings}),total_sum,0)) - SUM(if(t.code IN ({$sCodeExpenses}),total_sum,0)) AS ear_exp ,\n";
		}
		
		$sQuery .= "
				(	SELECT fix_cost
					FROM person_contract
					WHERE id_person = t.id_person
					AND to_arc = 0
					/**/LIMIT 1
				) AS fix_cost,
				(	SELECT min_cost
					FROM person_contract
					WHERE id_person = t.id_person
					AND to_arc = 0
					/**/LIMIT 1
				) AS min_cost,
				(	SELECT insurance
					FROM person_contract
					WHERE id_person = t.id_person
					AND to_arc = 0
					/**/LIMIT 1
				) AS insurance,
				(	SELECT CONCAT(DATE_FORMAT( trial_from, 'от %d.%m.%Yг. до' ),' ',DATE_FORMAT( trial_to, '%d.%m.%Yг.' ))
					FROM person_contract
					WHERE id_person = t.id_person
					AND to_arc = 0
					/**/LIMIT 1
				) AS trial,
				(	SELECT SUM(due_days) 
					FROM person_leaves 
					WHERE 1
						AND to_arc=0 
						AND type = 'leave' 
						AND leave_types = 'due' 
						AND year <= {$nYear} 
						AND id_person = t.id_person
				)
				-
				(	SELECT IF(SUM(application_days),SUM(application_days),0)
					FROM person_leaves 
					WHERE 1 
						AND to_arc=0 
						AND type = 'application' 
						AND (leave_types = 'due' OR leave_types = 'student') 
						AND year < {$nYear}  
						AND id_person = t.id_person
				)
				AS due_days,
				(	SELECT SUM(application_days) 
					FROM person_leaves 
					WHERE 1 
						AND to_arc=0 
						AND type = 'application' 
						AND (leave_types = 'due' OR leave_types = 'student') 
						AND year = {$nYear} 
						AND id_person = t.id_person
				) AS used_days,
				(	SELECT SUM(due_days) 
					FROM person_leaves 
					WHERE 1
						AND to_arc=0 
						AND type = 'leave' 
						AND leave_types = 'due' 
						AND year <= {$nYear} 
						AND id_person = t.id_person
				)
				-
				(	SELECT IF(SUM(application_days),SUM(application_days),0)
					FROM person_leaves 
					WHERE 1 
						AND to_arc=0 
						AND type = 'application' 
						AND (leave_types = 'due' OR leave_types = 'student') 
						AND year <= {$nYear}  
						AND id_person = t.id_person
				)
				AS remain,
				p.egn,
				SUM(if(t.is_earning = 1,t.total_sum,NULL) ) AS earnings, 
				SUM(if(t.is_earning = 0,t.total_sum,NULL) ) AS expense,
				SUM(if(t.is_earning = 1,t.total_sum,-t.total_sum) ) AS total_sum 
			FROM 
				salary t 
				LEFT JOIN personnel p ON p.id = t.id_person 
				LEFT JOIN {$db_name_sod}.offices r ON r.id = p.id_office
				LEFT JOIN {$db_name_sod}.objects o ON o.id = p.id_region_object
				LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
			WHERE 1
				AND t.to_arc=0
				AND t.month = {$nMonth}
				AND p.id_office IN ({$sIDOffices})
		";
		
		switch( $nRadio ) {
			case '1': if(!empty($sIDFirms))$sQuery .= " AND r.id_firm IN ({$sIDFirms})\n";break;
			//case '2': if(!empty($sIDOffices))$sQuery .= " AND p.id_office IN ({$sIDOffices})\n";break;
			case '3': if(!empty($sIDObjects))$sQuery .= " AND p.id_region_object IN ({$sIDObjects})\n";break;
		}
		
		if(!empty($nPosition)) {
			$sQuery .= " AND p.id_position = {$nPosition}\n";
		}
		
		$sQuery .= " GROUP BY t.month, t.id_person\n";
		
		global $db_personnel_backup;
		
		$this->getResult($sQuery, 'person_name', DBAPI_SORT_ASC, $oResponse,$db_personnel_backup);
		
		if(!empty($nScheme))
			$TotalColumn = "SUM(ear_exp) as ear_exp,";
			
		$sQuery1 = array();
		if( !empty($nScheme) ) {
			$i = 1;
			$aCodes = explode(',',$aFilters['earnings']);
			$sCodeEarnings = "'".implode("','",$aCodes)."'";
			foreach ( $aCodes as $code ) {
				if( !empty($code) ) {
					$TotalColumn .= "SUM(code{$i}) as code{$i},\n";
					$sQuery1[] = " SUM(if(t.code = '{$code}',total_sum,NULL)) AS code{$i} \n";
					$i++;
				}
			}
			$aCodes = explode(',',$aFilters['expenses']);
			$sCodeExpenses = "'".implode("','",$aCodes)."'";
			foreach ( $aCodes as $code ) {
				if( !empty($code) ) {
					$TotalColumn .= "SUM(code{$i}) as code{$i},\n";
					$sQuery1[] = " SUM(if(t.code = '{$code}',total_sum,NULL)) AS code{$i} \n";
					$i++;
				}
			}
			$sQuery1[] = "SUM(if(t.code IN ({$sCodeEarnings}),total_sum,0)) - SUM(if(t.code IN ({$sCodeExpenses}),total_sum,0)) AS ear_exp ,\n";
		}
		$sTotalQuery1 = array();
		$sTotalQuery1 = implode(",", $sQuery1);
		
		if(empty($TotalColumn))
			$TotalColumn="";
			
		$sQuery = "
			SELECT 
			SUM(fix_cost) as fix_cost,
			{$TotalColumn}
			SUM(earnings) as earnings,
			SUM(expense) as expense,
			SUM(total_sum) as total_sum,
			SUM(min_cost) as min_cost,
			SUM(due_days) as due_days,
			SUM(used_days) as used_days,
			SUM(remain) as remain
				FROM
				(
				SELECT 
				{$sTotalQuery1}
		";
		
		
		
		$sQuery .= "
				p.id,
				SUM(if(t.is_earning = 1,t.total_sum,NULL) ) AS earnings, 
				SUM(if(t.is_earning = 0,t.total_sum,NULL) ) AS expense,
				SUM(if(t.is_earning = 1,t.total_sum,-t.total_sum) ) AS total_sum,
				SUM((	SELECT fix_cost
				FROM person_contract
				WHERE id_person = t.id_person
				AND to_arc = 0
				)) / if(count(distinct t.id) < 1, 1, count(distinct t.id)) AS fix_cost,
				(	SELECT min_cost
					FROM person_contract
					WHERE id_person = t.id_person
					AND to_arc = 0
					/**/LIMIT 1
				) AS min_cost,
				(	SELECT SUM(due_days) 
					FROM person_leaves 
					WHERE 1
						AND to_arc=0 
						AND type = 'leave' 
						AND leave_types = 'due' 
						AND year <= {$nYear} 
						AND id_person = t.id_person
				)
				-
				(	SELECT IF(SUM(application_days),SUM(application_days),0)
					FROM person_leaves 
					WHERE 1 
						AND to_arc=0 
						AND type = 'application' 
						AND (leave_types = 'due' OR leave_types = 'student') 
						AND year < {$nYear}  
						AND id_person = t.id_person
				)
				AS due_days,
				(	SELECT SUM(application_days) 
					FROM person_leaves 
					WHERE 1 
						AND to_arc=0 
						AND type = 'application' 
						AND (leave_types = 'due' OR leave_types = 'student') 
						AND year = {$nYear} 
						AND id_person = t.id_person
				) AS used_days,
				(	SELECT SUM(due_days) 
					FROM person_leaves 
					WHERE 1
						AND to_arc=0 
						AND type = 'leave' 
						AND leave_types = 'due' 
						AND year <= {$nYear} 
						AND id_person = t.id_person
				)
				-
				(	SELECT IF(SUM(application_days),SUM(application_days),0)
					FROM person_leaves 
					WHERE 1 
						AND to_arc=0 
						AND type = 'application' 
						AND (leave_types = 'due' OR leave_types = 'student') 
						AND year <= {$nYear}  
						AND id_person = t.id_person
				)
				AS remain
			FROM 
				salary t 
				LEFT JOIN personnel p ON p.id = t.id_person 
				LEFT JOIN {$db_name_sod}.offices r ON r.id = p.id_office
				LEFT JOIN {$db_name_sod}.objects o ON o.id = p.id_region_object
				LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
			WHERE 1
				AND t.to_arc=0
				AND t.month = {$nMonth}
				AND p.id_office IN ({$sIDOffices})
		";

		switch( $nRadio ) {
			case '1': if(!empty($sIDFirms))$sQuery .= " AND r.id_firm IN ({$sIDFirms})\n";break;
			//case '2': if(!empty($sIDOffices))$sQuery .= " AND p.id_office IN ({$sIDOffices})\n";break;
			case '3': if(!empty($sIDObjects))$sQuery .= " AND p.id_region_object IN ({$sIDObjects})\n";break;
		}
		
		if(!empty($nPosition)) {
			$sQuery .= " AND p.id_position = {$nPosition}\n";
		}
		
		$sQuery .= "group by p.id
			) as table1";
		APILog::Log(0,$sQuery);
		$aTotals = $this->selectOnceFromDB($db_personnel_backup,$sQuery);
				
		$oResponse->addTotal('earnings', $aTotals['earnings'] );
		$oResponse->addTotal('expense', $aTotals['expense'] );
		$oResponse->addTotal('total_sum', $aTotals['total_sum'] );
		$oResponse->addTotal('fix_cost', $aTotals['fix_cost']);
		$oResponse->addTotal('min_cost', $aTotals['min_cost']);
		$oResponse->addTotal('due_days', $aTotals['due_days']);
		$oResponse->addTotal('used_days', $aTotals['used_days']);
		$oResponse->addTotal('remain', $aTotals['remain']);
		
		if(!empty($nScheme))$oResponse->addTotal('ear_exp', $aTotals['ear_exp'] );
		
		if( !empty($nScheme) ) {
			$i = 1;
			$aCodes = explode(',',$aFilters['earnings']);
			foreach ( $aCodes as $code ) {
				if( !empty($code) ) {
					$oResponse->addTotal("code{$i}", $aTotals["code{$i}"] );
					$i++;
				}
			}
			$aCodes = explode(',',$aFilters['expenses']);
			foreach ( $aCodes as $code ) {
				if( !empty($code) ) {
					$oResponse->addTotal("code{$i}", $aTotals["code{$i}"] );
					$i++;
				}
			}
		}
		
		
		
		//$oResponse->setField( 'person_code', 		'код', 		'Сортирай по код служител', NULL, NULL, NULL, array('DATA_FORMAT' => DF_NUMBER) );
		$oResponse->setField( 'person_name', 		'име', 		'Сортирай по име на служител', NULL, 'personnel'  );
		$oResponse->setField( 'person_firm_name', 	'регион',	'Сортирай по регион на назначение'  );
		$oResponse->setField( 'person_object_name', 'назначен на обект',	'Сортирай по обект на назначение'  );
		
		if(!empty($nScheme)) {
			
		if(!empty($aFilters['ear_exp']))$oResponse->setField( 'ear_exp', 	'нар - удр',	'Сортирай по наработки - удръжки'  );
		
		if ( !empty($aFilters['egn']) ) {
			$oResponse->setField( "egn", "ЕГН",	"Сортирай по ЕГН" );
		}
			
		if(!empty($aFilters['fix_salary']))$oResponse->setField( 'fix_cost', 			'фиксирина заплата', 	'Сортирай по фиксирана заплата', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
		if(!empty($aFilters['min_salary']))$oResponse->setField( 'min_cost', 			'минимална заплата', 	'Сортирай по минимална заплата', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
		if(!empty($aFilters['insurance']))$oResponse->setField( 'insurance', 		'осигорителна ставка', 	'Сортирай по осигорителна ставка', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
		if(!empty($aFilters['trial']))$oResponse->setField( 'trial', 	'пробен период',	'Сортирай по пробен период'  );
		if(!empty($aFilters['due_days']))$oResponse->setField( 'due_days', 	'полагаем отпуск',	'Сортирай по полагаем отпуск'  );
		if(!empty($aFilters['used_days']))$oResponse->setField( 'used_days', 	'използван отпуск',	'Сортирай по използван отпуск'  );
		if(!empty($aFilters['remain']))$oResponse->setField( 'remain', 	'оставащ отпуск',	'Сортирай по оставащ отпуск'  );
		
		$i = 1;
		$aCodes = explode(',',$aFilters['earnings']);
			foreach ( $aCodes as $code ) {
				if( !empty($code) ) {
					if(!empty($aTotals["code{$i}"]))
					$oResponse->setField( "code{$i}", 	 "{$code}",	"Сортирай по {$code}" , NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1  ));
					$i++;
				}
			}
		$aCodes = explode(',',$aFilters['expenses']);
			foreach ( $aCodes as $code ) {
				if( !empty($code) ) {
					if(!empty($aTotals["code{$i}"]))
					$oResponse->setField( "code{$i}", 	 "{$code}",	"Сортирай по {$code}" , NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ));
					$i++;
				}
			}
		} 	
		
		$oResponse->setField( 'earnings', 			'наработки', 	'Сортирай по наработки', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
		$oResponse->setField( 'expense', 			'удръжки', 	'Сортирай по удръжки', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
		$oResponse->setField( 'total_sum', 			'за получаване', 	'Сортирай по остатък', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
		$oResponse->setFieldLink('earnings','openSalary');
		$oResponse->setFieldLink('expense','openSalary');
		$oResponse->setFieldLink('total_sum','openSalary'); 
	}
	public function getReport2($aData, $oResponse) {
		global $db_name_sod;
		
		$nMonth = $aData['nMonth'];
		$sIDFirms = $aData['sIDFirms'];
		$sIDObjects = $aData['sIDObjects'];
		$sIDOffices = $aData['sIDOffices'];
		$nPosition = $aData['id_position'];
		$nRadio = $aData['nRadio'];
		
		
		$sIDOffices = !empty($sIDOffices) ? $sIDOffices : implode(',',$_SESSION['userdata']['access_right_regions']);
		
		$sQuery = " 
			SELECT 
				SQL_CALC_FOUND_ROWS 
				t.id as _id, 
				t.id, 
				t.month, 
				CONCAT( sf.name,' (', sr.name, ')' ) AS firm_name,
				CONCAT( so.name,' (', so.num, ')' ) AS object_name,
				sf.code as firm_code,
				sr.code as region_code,
				so.num 	as object_num,
				SUM(if(t.is_earning = 1,t.total_sum,-t.total_sum) ) AS total_sum 
				
			FROM 
				salary t 
				LEFT JOIN personnel p ON p.id = t.id_person 
				LEFT JOIN {$db_name_sod}.offices sr ON sr.id = t.id_office
				LEFT JOIN {$db_name_sod}.firms sf ON sf.id = sr.id_firm
				LEFT JOIN {$db_name_sod}.objects so ON so.id = t.id_object
			WHERE 1
				AND t.to_arc=0
				AND t.month = {$nMonth}
				AND t.id_office IN ({$sIDOffices})
		";
		
		switch( $nRadio ) {
			case '1': if(!empty($sIDFirms))$sQuery .= " AND sr.id_firm IN ({$sIDFirms})\n";break;
			//case '2': if(!empty($sIDOffices))$sQuery .= " AND t.id_office IN ({$sIDOffices})\n";break;
			case '3': if(!empty($sIDObjects))$sQuery .= " AND t.id_object IN ({$sIDObjects})\n";break;
		}
		
		
		
		if(!empty($nPosition)) {
			$sQuery .= " AND p.id_position = {$nPosition}\n";
		}
		
		$sQuery .= " GROUP BY t.month, sr.id, sf.id, so.id\n";
		
		global $db_personnel_backup;
		
		$this->getResult($sQuery, 'id', DBAPI_SORT_ASC, $oResponse,$db_personnel_backup);
		
		$sQuery = "
			SELECT 
				SUM(if(t.is_earning = 1,t.total_sum,-t.total_sum) ) AS total_sum 
			FROM 
				salary t 
				LEFT JOIN personnel p ON p.id = t.id_person 
				LEFT JOIN {$db_name_sod}.offices sr ON sr.id = t.id_office
				LEFT JOIN {$db_name_sod}.firms sf ON sf.id = sr.id_firm
				LEFT JOIN {$db_name_sod}.objects so ON so.id = t.id_object
			WHERE 1
				AND t.to_arc=0
				AND t.month = {$nMonth}
				AND t.id_office IN ({$sIDOffices})
		";
		
		switch( $nRadio ) {
			case '1': if(!empty($sIDFirms))$sQuery .= " AND sr.id_firm IN ({$sIDFirms})\n";break;
			//case '2': if(!empty($sIDOffices))$sQuery .= " AND t.id_office IN ({$sIDOffices})\n";break;
			case '3': if(!empty($sIDObjects))$sQuery .= " AND t.id_object IN ({$sIDObjects})\n";break;
		}
		
		if(!empty($nPosition)) {
			$sQuery .= " AND p.id_position = {$nPosition}\n";
		}
		
		$nTotal = $this->selectOneFromDB($db_personnel_backup, $sQuery);		
		$oResponse->addTotal('total_sum', $nTotal );
		
		$oResponse->setField( 'firm_name', 			'регион',	'Сортирай по регион за чиято смека е наработката / удръжката'  );
		$oResponse->setField( 'object_name', 		'обект',	'Сортирай по обкет за чиято смека е наработката / удръжката'  );
		$oResponse->setField( 'total_sum', 			'в брой', 	'Сортирай по сума', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
		
	}
	
	public function getReportFirms($aData,$oResponse) {
		
		global $db_name_sod,$db_personnel_backup;
		
		$oDBHolidays = new DBHolidays();
		
		$nIDFirmFrom = $aData['nIDFirmFrom'];
		$nIDFirmTo = $aData['nIDFirmTo'];
		$nMonth = $aData['nMonth'];
		$sMonthSQL = $aData['sMonthSQL'];
		
		$sQuery = "
			SELECT SQL_CALC_FOUND_ROWS
				t.id as _id, 
				t.id_person AS id, 
				CONCAT('     ',SUBSTRING(t.month,5),' - ',SUBSTRING(t.month,1,4)) AS month, 
				p.code as person_code,
				CONCAT_WS(' ', p.fname, p.mname, p.lname) as person_name,
				CONCAT( f.name,' (', r.name, ')' ) as person_firm_name,
				CONCAT( o.name,' (', o.num, ')' ) as person_object_name,
				SUM(if(t.is_earning = 1,t.total_sum,0) ) AS earnings, 
				SUM(if(t.is_earning = 0,t.total_sum,0) ) AS expense,
				SUM(if(t.is_earning = 1,t.total_sum,-t.total_sum) ) AS total_sum 
			FROM salary t
				LEFT JOIN personnel p ON p.id = t.id_person
				LEFT JOIN {$db_name_sod}.offices r ON r.id = p.id_office
				LEFT JOIN {$db_name_sod}.objects o ON o.id = p.id_region_object
				LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
				LEFT JOIN personnel as up on up.id = t. updated_user
				LEFT JOIN {$db_name_sod}.offices sr ON sr.id = t.id_office
				LEFT JOIN {$db_name_sod}.firms sf ON sf.id = sr.id_firm
				LEFT JOIN {$db_name_sod}.objects so ON so.id = t.id_object
			WHERE 1
				AND t.to_arc = 0
				AND UNIX_TIMESTAMP( p.date_from ) <= UNIX_TIMESTAMP( '{$sMonthSQL}' )
				AND t.month = {$nMonth}
				AND f.id = {$nIDFirmFrom}
				AND sf.id = {$nIDFirmTo}
			GROUP BY t.month, t.id_person
		";
		
		$this->getResult($sQuery, 'id', DBAPI_SORT_ASC, $oResponse,$db_personnel_backup);
		
		$sQuery = "
			SELECT 
				SUM(if(t.is_earning = 1,t.total_sum,0) ) AS earnings, 
				SUM(if(t.is_earning = 0,t.total_sum,0) ) AS expense,
				SUM(if(t.is_earning = 1,t.total_sum,-t.total_sum) ) AS total_sum 
			FROM salary t
				LEFT JOIN personnel p ON p.id = t.id_person
				LEFT JOIN {$db_name_sod}.offices r ON r.id = p.id_office
				LEFT JOIN {$db_name_sod}.objects o ON o.id = p.id_region_object
				LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
				LEFT JOIN personnel as up on up.id = t. updated_user
				LEFT JOIN {$db_name_sod}.offices sr ON sr.id = t.id_office
				LEFT JOIN {$db_name_sod}.firms sf ON sf.id = sr.id_firm
				LEFT JOIN {$db_name_sod}.objects so ON so.id = t.id_object
			WHERE 1
				AND t.to_arc = 0
				AND UNIX_TIMESTAMP( p.date_from ) <= UNIX_TIMESTAMP( '{$sMonthSQL}' )
				AND t.month = {$nMonth}
				AND f.id = {$nIDFirmFrom}
				AND sf.id = {$nIDFirmTo}
		";
		
		$aTotals = $this->selectOnceFromDB( $db_personnel_backup, $sQuery );
		$oResponse->addTotal( "earnings", 	$aTotals["earnings"] 	);
		$oResponse->addTotal( "expense", 	$aTotals["expense"] 	);
		$oResponse->addTotal( "total_sum", 	$aTotals["total_sum"] 	);
		
		
		$oResponse->setField( "person_code", 		"Код", 										"Сортирай по код служител", 	NULL, NULL, NULL, array("DATA_FORMAT" => DF_NUMBER) );
		$oResponse->setField( "person_name", 		"Име", 										"Сортирай по име на служител", 	NULL, "personnel"  );
		$oResponse->setField( "person_firm_name", 	"Регион",									"Сортирай по регион на назначение" );
		$oResponse->setField( "person_object_name", "Назначен на обект",						"Сортирай по обект на назначение" );
		$oResponse->setField( "earnings", 			"Наработки за {$aData['sMonth']}", 			"Сортирай по наработки", 		NULL, NULL, NULL, array("DATA_FORMAT" => DF_CURRENCY, "DATA_TOTAL" => 1 ) );
		$oResponse->setField( "expense", 			"Удръжки за {$aData['sMonth']}", 			"Сортирай по удръжки", 			NULL, NULL, NULL, array("DATA_FORMAT" => DF_CURRENCY, "DATA_TOTAL" => 1 ) );
		$oResponse->setField( "total_sum", 			"За получаване през {$aData['sMonth']}", 	"Сортирай по за получаване", 	NULL, NULL, NULL, array("DATA_FORMAT" => DF_CURRENCY, "DATA_TOTAL" => 1 ) );
		$oResponse->setField( "leave_count", 		"Отпуск за {$aData['sNextMonth']}", 		"Сортирай по отпуск", 			NULL, NULL, NULL, array("DATA_FORMAT" => DF_NUMBER, "DATA_TOTAL" => 1 ) );
		$oResponse->setField( "leave_sum", 			"Начислена сума за отпуск", 				"Сортирай по сума за отпуск", 	NULL, NULL, NULL, array("DATA_FORMAT" => DF_CURRENCY, "DATA_TOTAL" => 1 ) );
		
		$oResponse->setFieldLink( "earnings",	"openSalary" );
		$oResponse->setFieldLink( "expense",	"openSalary" );
		$oResponse->setFieldLink( "total_sum",	"openSalary" );
		
		foreach( $oResponse->oResult->aData as $nKey => &$aValue )
		{
			//Leave Count
			$sLeaveQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					SUM( sal.count ) AS leave_count
				FROM
					salary sal
				WHERE
					sal.to_arc = 0
					AND sal.month = '{$aData['nNextMonth']}'
					AND sal.code = ( SELECT code FROM salary_earning_types WHERE leave_type = 'due' )
					AND sal.id_person = {$aValue['id']}
			";
			
			$nLeaveCount = $this->selectOne( $sLeaveQuery );
			$aValue['leave_count'] = $nLeaveCount;
			
			$nWorkDays = $oDBHolidays->getWorkdaysForMonth( $aData['nLeaveYear'], $aData['nLeaveMonth'] );
			if( $nWorkDays != 0 )
			{
				$aValue['leave_sum'] = ( $aValue['leave_count'] / $nWorkDays ) * $aValue['total_sum'];
			}
			else $aValue['leave_sum'] = 0;
			//End Leave Count
		}
	}
	
	public function getReportFirms2($aData,$oResponse) {
		
		global $db_name_sod,$db_personnel_backup;
		
		$nIDFirmFrom = $aData['nIDFirmFrom'];
		$nIDFirmTo = $aData['nIDFirmTo'];
		$nMonth = $aData['nMonth'];
		$sMonthSQL = $aData['sMonthSQL'];
		
		$sQuery = "
			SELECT SQL_CALC_FOUND_ROWS
				r.id,
				CONCAT('Към [',sf.name,'] ',sr.name) AS name,
				SUM(if(t.is_earning = 1,t.total_sum,0) ) AS earnings, 
				SUM(if(t.is_earning = 0,t.total_sum,0) ) AS expense,
				SUM(if(t.is_earning = 1,t.total_sum,-t.total_sum) ) AS total_sum 
			FROM salary t
				LEFT JOIN personnel p ON p.id = t.id_person
				LEFT JOIN {$db_name_sod}.offices r ON r.id = p.id_office
				LEFT JOIN {$db_name_sod}.objects o ON o.id = p.id_region_object
				LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
				LEFT JOIN personnel as up on up.id = t. updated_user
				LEFT JOIN {$db_name_sod}.offices sr ON sr.id = t.id_office
				LEFT JOIN {$db_name_sod}.firms sf ON sf.id = sr.id_firm
				LEFT JOIN {$db_name_sod}.objects so ON so.id = t.id_object
			WHERE 1
				AND t.to_arc=0
				AND UNIX_TIMESTAMP( p.date_from ) <= UNIX_TIMESTAMP( '{$sMonthSQL}' )
				AND t.month = {$nMonth}
				AND f.id = {$nIDFirmFrom}
				AND sf.id = {$nIDFirmTo}
			GROUP BY sr.id 
		";
		
		$this->getResult($sQuery, 'name', DBAPI_SORT_ASC, $oResponse,$db_personnel_backup);
		
		
		$sQuery = "
			SELECT
				SUM(if(t.is_earning = 1,t.total_sum,0) ) AS earnings, 
				SUM(if(t.is_earning = 0,t.total_sum,0) ) AS expense,
				SUM(if(t.is_earning = 1,t.total_sum,-t.total_sum) ) AS total_sum 
			FROM salary t
				LEFT JOIN personnel p ON p.id = t.id_person
				LEFT JOIN {$db_name_sod}.offices r ON r.id = p.id_office
				LEFT JOIN {$db_name_sod}.objects o ON o.id = p.id_region_object
				LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
				LEFT JOIN personnel as up on up.id = t. updated_user
				LEFT JOIN {$db_name_sod}.offices sr ON sr.id = t.id_office
				LEFT JOIN {$db_name_sod}.firms sf ON sf.id = sr.id_firm
				LEFT JOIN {$db_name_sod}.objects so ON so.id = t.id_object
			WHERE 1
				AND t.to_arc=0
				AND UNIX_TIMESTAMP( p.date_from ) <= UNIX_TIMESTAMP( '{$sMonthSQL}' )
				AND t.month = {$nMonth}
				AND f.id = {$nIDFirmFrom}
				AND sf.id = {$nIDFirmTo}
		";
		
		$aTotals = array();
		$aTotals = $this->selectOnceFromDB($db_personnel_backup, $sQuery);
		
		$oResponse->addTotal('earnings', $aTotals['earnings'] );
		$oResponse->addTotal('expense', $aTotals['expense'] );
		$oResponse->addTotal('total_sum', $aTotals['total_sum'] );
		
		$oResponse->setField( 'name', 			'Регион', 	'Сортирай по регион' );
		$oResponse->setField( 'earnings', 		'наработки', 	'Сортирай по наработки', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
		$oResponse->setField( 'expense', 		'удръжки', 	'Сортирай по удръжки', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
		$oResponse->setField( 'total_sum', 		'за получаване', 	'Сортирай по за получаване', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
	}
	
	
	public function getByFirms($aData) {
		global $db_name_sod,$db_personnel_backup;
		
		$nIDFirmFrom = $aData['nIDFirmFrom'];
		$nIDFirmTo = $aData['nIDFirmTo'];
		$nMonth = $aData['nMonth'];
		$sMonthSQL = $aData['sMonthSQL'];
		
		$sQuery = "
			SELECT 
				SUM(if(t.is_earning = 1,t.total_sum,0) ) AS total_sum
			FROM salary t
				LEFT JOIN personnel p ON p.id = t.id_person
				LEFT JOIN {$db_name_sod}.offices r ON r.id = p.id_office
				LEFT JOIN {$db_name_sod}.objects o ON o.id = p.id_region_object
				LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
				LEFT JOIN personnel as up on up.id = t. updated_user
				LEFT JOIN {$db_name_sod}.offices sr ON sr.id = t.id_office
				LEFT JOIN {$db_name_sod}.firms sf ON sf.id = sr.id_firm
				LEFT JOIN {$db_name_sod}.objects so ON so.id = t.id_object
			WHERE 1
				AND t.to_arc=0
				AND UNIX_TIMESTAMP( p.date_from ) <= UNIX_TIMESTAMP( '{$sMonthSQL}' )
				AND t.month = {$nMonth}
				AND f.id = {$nIDFirmFrom}
				AND sf.id = {$nIDFirmTo}
		";
		
		return $this->selectOneFromDB($db_personnel_backup, $sQuery);
	}

	public function getTechEarning( $nIDPerson,$sMonth, &$sHint = "" )
	{
		$sQuery = "
			SELECT
				SUM( s.total_sum ) AS salary,
				GROUP_CONCAT(
					(
						CONCAT(
							CONVERT( s.description USING utf8 ),
							' : ',
							s.total_sum,
							CONVERT( ' лв.' USING utf8 )
						)
					)
					ORDER BY s.description
					SEPARATOR '\\n'
				) AS salary_hint
			FROM salary s 
			LEFT JOIN salary_earning_types st ON st.code = s.code
			WHERE 1
				AND s.to_arc = 0
				AND s.id_person = {$nIDPerson}
				AND s.month = {$sMonth}
				AND st.source = 'limit_card'
		";
		
		$aData =  $this->selectOnce( $sQuery );
		
		if( !empty( $aData ) )
		{
			if( isset( $aData['salary_hint'] ) )$sHint = $aData['salary_hint'];
			if( isset( $aData['salary'] ) )return $aData['salary'];
			else return 0;
		}
		else return 0;
	}
	
	public function getTechEarningForDay( $nIDPerson,$sDay) {
		$sQuery = "
			SELECT
				SUM(s.total_sum)
			FROM salary s 
			LEFT JOIN salary_earning_types st ON st.code = s.code
			WHERE 1
				AND s.id_person = {$nIDPerson}
				AND s.created_time LIKE '$sDay%'
				AND st.source = 'limit_card'
		";
		
		return $this->selectOne($sQuery);
	}
	
	public function delFixSalary($nMonth) {
		$sQuery = "
			UPDATE 
				salary
			SET to_arc = 1
			WHERE month = '{$nMonth}' AND code = '+ЩАТ'  
		";
		$this->oDB->Execute( $sQuery );
	}
	
	public function getSomeRegions($nMonth,$sIDOffices) {
		
		global $db_name_sod;
		
		$sQuery = "
			SELECT 
				GROUP_CONCAT(DISTINCT sr.id) as offices_to
			FROM salary t
				LEFT JOIN personnel p ON p.id = t.id_person
				LEFT JOIN {$db_name_sod}.offices r ON r.id = p.id_office
				LEFT JOIN {$db_name_sod}.objects o ON o.id = p.id_region_object
				LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
				LEFT JOIN personnel as up on up.id = t. updated_user
				LEFT JOIN {$db_name_sod}.offices sr ON sr.id = t.id_office
				LEFT JOIN {$db_name_sod}.firms sf ON sf.id = sr.id_firm
				LEFT JOIN {$db_name_sod}.objects so ON so.id = t.id_object
			WHERE 1
				AND t.to_arc=0
				AND t.month = {$nMonth}
				AND r.id IN ({$sIDOffices})
		";
		
		return $this->selectOne($sQuery);
	}
	
	public function getReportByRegions($aData,$oResponse) {
		
		global $db_name_sod,$db_personnel_backup;
		
		$oDBAdminSalaryTotalFilter = new DBAdminSalaryTotalFilters();
		
		$nMonth = $aData['nMonth'];
		$sIDFirms = $aData['sIDFirms'];
		$sIDOffices = $aData['sIDOffices'];
		$nPosition = $aData['id_position'];
		$nScheme = $aData['id_scheme'];
		$nRadio = $aData['nRadio'];
		
		$sIDOffices = !empty($sIDOffices) ? $sIDOffices : implode(',',$_SESSION['userdata']['access_right_regions']);
		
		$sQuery = "
			SELECT SQL_CALC_FOUND_ROWS
				r.id,
				CONCAT('[',f.name,'] ',r.name) AS name,
		";
		
		
		if( !empty($nScheme) ) {
			$aFilters = $oDBAdminSalaryTotalFilter->getRecord($nScheme);
			$i = 1;
			$aCodes = explode(',',$aFilters['earnings']);
			$sCodeEarnings = "'".implode("','",$aCodes)."'";
			foreach ( $aCodes as $code ) {
				if( !empty($code) ) {
					$sQuery .= " SUM(if(t.code = '{$code}',total_sum,NULL)) AS code{$i} ,\n";
					$i++;
				}
			}
			$aCodes = explode(',',$aFilters['expenses']);
			$sCodeExpenses = "'".implode("','",$aCodes)."'";
			foreach ( $aCodes as $code ) {
				if( !empty($code) ) {
					$sQuery .= " SUM(if(t.code = '{$code}',total_sum,NULL)) AS code{$i} ,\n";
					$i++;
				}
			}
			$sQuery .= "SUM(if(t.code IN ({$sCodeEarnings}),total_sum,0)) - SUM(if(t.code IN ({$sCodeExpenses}),total_sum,0)) AS ear_exp ,\n";
		}
		
		
		$sQuery .= "
				SUM(if(t.is_earning = 1,t.total_sum,0) ) AS earnings, 
				SUM(if(t.is_earning = 0,t.total_sum,0) ) AS expense,
				SUM(if(t.is_earning = 1,t.total_sum,-t.total_sum) ) AS total_sum 
			FROM salary t
				LEFT JOIN personnel p ON p.id = t.id_person
				LEFT JOIN {$db_name_sod}.offices r ON r.id = p.id_office
				LEFT JOIN {$db_name_sod}.objects o ON o.id = p.id_region_object
				LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
				LEFT JOIN personnel as up on up.id = t. updated_user
			WHERE 1
				AND t.to_arc=0
				AND t.month = {$nMonth}
				AND r.id IN ( {$sIDOffices} )
		";

		switch( $nRadio ) {
			case '1': if(!empty($sIDFirms))$sQuery .= " AND r.id_firm IN ({$sIDFirms})\n";break;
			//case '2': if(!empty($sIDOffices))$sQuery .= " AND r.id IN ( {$sIDOffices} )\n";break;
		}
		
		
		if(!empty($nPosition)) {
			$sQuery .= " AND p.id_position = {$nPosition}\n";
		}
		
		$sQuery .= " GROUP BY r.id\n";
		
		$this->getResult($sQuery, 'name', DBAPI_SORT_ASC, $oResponse,$db_personnel_backup);
		
		
		$sQuery = "
			SELECT
		";
		
		if( !empty($nScheme) ) {
			$i = 1;
			$aCodes = explode(',',$aFilters['earnings']);
			$sCodeEarnings = "'".implode("','",$aCodes)."'";
			foreach ( $aCodes as $code ) {
				if( !empty($code) ) {
					$sQuery .= " SUM(if(t.code = '{$code}',total_sum,NULL)) AS code{$i} ,\n";
					$i++;
				}
			}
			$aCodes = explode(',',$aFilters['expenses']);
			$sCodeExpenses = "'".implode("','",$aCodes)."'";
			foreach ( $aCodes as $code ) {
				if( !empty($code) ) {
					$sQuery .= " SUM(if(t.code = '{$code}',total_sum,NULL)) AS code{$i} ,\n";
					$i++;
				}
			}
			$sQuery .= "SUM(if(t.code IN ({$sCodeEarnings}),total_sum,0)) - SUM(if(t.code IN ({$sCodeExpenses}),total_sum,0)) AS ear_exp ,\n";
		}
		
		
		$sQuery .= "
				SUM(if(t.is_earning = 1,t.total_sum,0) ) AS earnings, 
				SUM(if(t.is_earning = 0,t.total_sum,0) ) AS expense,
				SUM(if(t.is_earning = 1,t.total_sum,-t.total_sum) ) AS total_sum 
			FROM salary t
				LEFT JOIN personnel p ON p.id = t.id_person
				LEFT JOIN {$db_name_sod}.offices r ON r.id = p.id_office
				LEFT JOIN {$db_name_sod}.objects o ON o.id = p.id_region_object
				LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
				LEFT JOIN personnel as up on up.id = t. updated_user
			WHERE 1
				AND t.to_arc=0
				AND t.month = {$nMonth}
				AND r.id IN ( {$sIDOffices} )
		";
		
		switch( $nRadio ) {
			case '1': if(!empty($sIDFirms))$sQuery .= " AND r.id_firm IN ({$sIDFirms})\n";break;
			//case '2': if(!empty($sIDOffices))$sQuery .= " AND r.id IN ( {$sIDOffices} )\n";break;
		}
		
		if(!empty($nPosition)) {
			$sQuery .= " AND p.id_position = {$nPosition}\n";
		}
		
		$aTotals = array();
		$aTotals = $this->selectOnceFromDB($db_personnel_backup, $sQuery);
		
		$oResponse->addTotal('earnings', $aTotals['earnings'] );
		$oResponse->addTotal('expense', $aTotals['expense'] );
		$oResponse->addTotal('total_sum', $aTotals['total_sum'] );
		
		$oResponse->addTotal('ear_exp', $aTotals['ear_exp'] );
		
		if( !empty($nScheme) ) {
			$i = 1;
			$aCodes = explode(',',$aFilters['earnings']);
			foreach ( $aCodes as $code ) {
				if( !empty($code) ) {
					$oResponse->addTotal("code{$i}", $aTotals["code{$i}"] );
					$i++;
				}
			}
			$aCodes = explode(',',$aFilters['expenses']);
			foreach ( $aCodes as $code ) {
				if( !empty($code) ) {
					$oResponse->addTotal("code{$i}", $aTotals["code{$i}"] );
					$i++;
				}
			}
		}
		
		
		$oResponse->setField( 'name', 			'Регион', 	'Сортирай по регион' );
		
		if(!empty($nScheme)) {
			
			if(!empty($aFilters['ear_exp']))$oResponse->setField( 'ear_exp', 	'нар - удр',	'Сортирай по наработки - удръжки'  );
				
			$i = 1;
			$aCodes = explode(',',$aFilters['earnings']);
				foreach ( $aCodes as $code ) {
					if( !empty($code) ) {
						if(!empty($aTotals["code{$i}"]))
						$oResponse->setField( "code{$i}", 	 "{$code}",	"Сортирай по {$code}" , NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1  ));
						$i++;
					}
				}
			$aCodes = explode(',',$aFilters['expenses']);
				foreach ( $aCodes as $code ) {
					if( !empty($code) ) {
						if(!empty($aTotals["code{$i}"]))
						$oResponse->setField( "code{$i}", 	 "{$code}",	"Сортирай по {$code}" , NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ));
						$i++;
					}
				}
			
		} 	
		
		
		$oResponse->setField( 'earnings', 		'наработки', 	'Сортирай по наработки', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
		$oResponse->setField( 'expense', 		'удръжки', 	'Сортирай по удръжки', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
		$oResponse->setField( 'total_sum', 		'за получаване', 	'Сортирай по за получаване', NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
	}
	
	public function getAutoSalaries($nMonth) {
		$sQuery = "
			SELECT id
			FROM salary
			WHERE 1
				AND to_arc = 0
				AND auto = 1
				AND month = '{$nMonth}'
		";
		
		return $this->select($sQuery);
	}
	
	public function getReportLeave( $aParams, &$aPaging = array() )
	{
		global $db_personnel, $db_name_sod;
		
		$oDBHolidays = new DBHolidays();
		
		$nIDLoggedPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
		
		$sQuery = "
			SELECT SQL_CALC_FOUND_ROWS
				sal.id,
				per_lea.id AS id_leave,
				CONCAT_WS( ' ', per.fname, per.mname, per.lname ) AS person_name,
				per.id AS id_person,
				fir.name AS firm,
				off.name AS office,
				CASE per_lea.leave_types
					WHEN 'due' THEN 'Платен'
					WHEN 'unpaid'  THEN 'Неплатен'
					WHEN 'student'  THEN 'Пл. Полагаем'
					WHEN 'quittance'  THEN 'Обезщетение'
					WHEN 'other'  THEN 'Друг'
				END as leave_type,
				CONCAT(
					per_lea.application_days,
					IF
					(
						per_lea.leave_from != '0000-00-00 00:00:00',
						CONCAT( ' от ', DATE_FORMAT( per_lea.res_leave_from, '%d.%m.%Y' ) ),
						''
					)
				) AS date_from,
				IF
				(
					sal.code = sal_ear_typ_hos.code,
					-1,
					ROUND( sal.count, 0 )
				) AS res_application_days,
				IF( per_cont.fix_cost != 0, 1, 0 ) AS state_salary,
				
				IF
				(
					per_lea.leave_types = 'unpaid',
					'Неплатен',
					IF
					(
						(
							SELECT
								id
							FROM
								salary_unstored sal_uns
							WHERE
								#sal_uns.id_person = {$nIDLoggedPerson} AND
								sal_uns.id_salary_row = sal.id
							ORDER BY
								id DESC
							LIMIT 1
						) IS NULL,
						sal.total_sum,
						(
							SELECT
								total_sum
							FROM
								salary_unstored sal_uns
							WHERE
								#sal_uns.id_person = {$nIDLoggedPerson} AND
								sal_uns.id_salary_row = sal.id
							ORDER BY
								id DESC
							LIMIT 1
						)
					)
				) AS total_sum,
		";
			
		if( isset( $aParams['nMonth'] ) && !empty( $aParams['nMonth'] ) )
		{
			$sMonth = substr( $aParams['nMonth'], 0, 4 ) . "-" . substr( $aParams['nMonth'], 4, 2 );
			
			$sQuery .= "
				IF
				(
					sal.code = sal_ear_typ_hos.code,
					IF( '{$sMonth}' > SUBSTR( per_lea.res_leave_from, 1, 7 ), 0, 1 ),
					IF( per_lea.leave_types = 'unpaid', 0, 1 )
				) AS allow_edit,
			";
		}
		else
		{
			$sQuery .= "
				IF( per_lea.leave_types = 'unpaid', 0, 1 ) AS allow_edit,
			";
		}
		
		$sQuery .= "
				per_lea.leave_num AS leave_num,
				DATE_FORMAT( per_lea.date, '%d.%m.%Y' ) AS date,
				per_lea.date AS date_,
				pos_nc.name AS person_position,
				obj.name AS object,
				IF
				(
					per_lea.leave_from != '0000-00-00 00:00:00',
					DATE_FORMAT( per_lea.leave_from, '%d.%m.%Y' ),
					''
				) AS leave_from,
				per_lea.leave_from AS leave_from_,
				IF
				(
					per_lea.leave_to != '0000-00-00 00:00:00',
					DATE_FORMAT( per_lea.leave_to, '%d.%m.%Y' ),
					''
				) AS leave_to,
				per_lea.leave_to AS leave_to_,
				cod_lea.clause_paragraph AS code_leave_name,
				CONCAT_WS( ' ', per_cre.fname, per_cre.mname, per_cre.lname ) AS created_user,
				IF( per_lea.is_confirm, 'Потвърден', 'Непотвърден' ) AS status,
				IF
				(
					per_lea.confirm_time != '0000-00-00 00:00:00',
					DATE_FORMAT( per_lea.confirm_time, '%d.%m.%Y' ),
					''
				) AS time_confirm,
				per_lea.confirm_time AS time_confirm_,
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
				per_lea.application_days_offer AS days_count,
				IF
				(
					per_lea.confirm_user,
					CONCAT_WS( ' ', per_con.fname, per_con.mname, per_con.lname ),
					''
				) AS person_confirm,
				
				IF
				(
					(
						SELECT
							id
						FROM
							salary_unstored sal_uns
						WHERE
							#sal_uns.id_person = {$nIDLoggedPerson} AND
							sal_uns.id_salary_row = sal.id
						ORDER BY
							id DESC
						LIMIT 1
					) IS NULL,
					0,
					1
				) AS sum_editted
			FROM
				salary sal
			LEFT JOIN
				person_leaves per_lea ON per_lea.id = sal.id_application
			LEFT JOIN
				code_leave cod_lea ON cod_lea.id = per_lea.id_code_leave
			LEFT JOIN
				personnel per ON per.id = sal.id_person
			LEFT JOIN
				positions_nc pos_nc ON pos_nc.id = per.id_position_nc
			LEFT JOIN
				person_contract per_cont ON ( per_cont.id_person = per.id AND per_cont.to_arc = 0 )
			LEFT JOIN
				personnel per_cre ON per_cre.id = per_lea.created_user
			LEFT JOIN
				personnel per_con ON per_con.id = per_lea.confirm_user
			LEFT JOIN
				{$db_name_sod}.offices off ON off.id = per.id_office
			LEFT JOIN
				{$db_name_sod}.firms fir ON fir.id = off.id_firm
			LEFT JOIN
				{$db_name_sod}.objects obj ON obj.id = per.id_region_object
			LEFT JOIN
				salary_earning_types sal_ear_typ_due ON sal_ear_typ_due.leave_type = 'due'
			LEFT JOIN
				salary_earning_types sal_ear_typ_unp ON sal_ear_typ_unp.leave_type = 'unpaid'
			LEFT JOIN
				salary_earning_types sal_ear_typ_hos ON sal_ear_typ_hos.is_hospital = 1
			LEFT JOIN
				salary_earning_types sal_ear_typ_com ON sal_ear_typ_com.is_compensation = 1
			WHERE
				sal.to_arc = 0
				AND sal.id_application != 0
				AND sal.is_earning = 1
		";
		
		if( isset( $aParams['nMode'] ) )
		{
			switch( $aParams['nMode'] )
			{
				case 0:
					$sQuery .= "
						AND ( sal.code = sal_ear_typ_due.code OR sal.code = sal_ear_typ_unp.code )
					";
					break;
				
				case 1:
					$sQuery .= "
						AND sal.code = sal_ear_typ_hos.code
					";
					break;
				
				case 2:
					$sQuery .= "
						AND sal.code = sal_ear_typ_com.code
					";
					break;
			}
		}
		
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
		if( isset( $aParams['sPersonName'] ) && !empty( $aParams['sPersonName'] ) )
		{
			$sQuery .= "
				AND CONCAT_WS( ' ', per.fname, per.mname, per.lname ) LIKE '%{$aParams['sPersonName']}%'
			";
		}
		
		//Month Filter
		if( isset( $aParams['nMonth'] ) && !empty( $aParams['nMonth'] ) )
		{
			$sMonth = substr( $aParams['nMonth'], 0, 4 ) . "-" . substr( $aParams['nMonth'], 4, 2 );
			
			$sQuery .= "
				AND IF
				(
					sal.code = sal_ear_typ_hos.code,
					( SUBSTR( per_lea.res_leave_from, 1, 7 ) <= '{$sMonth}' AND SUBSTR( per_lea.res_leave_to, 1, 7 ) >= '{$sMonth}' ),
					sal.month = {$aParams['nMonth']}
				)
			";
		}
		else
		{
			$sMonth = date( "Y-m" );
		}
		//End Month Filter
		
		if( isset( $aParams['nStateSalary'] ) && !empty( $aParams['nStateSalary'] ) )
		{
			$sQuery .= "
				AND IF( per_cont.fix_cost != 0, 1, 0 )
			";
		}
		if( isset( $aParams['nLeaveNum'] ) && !empty( $aParams['nLeaveNum'] ) )
		{
			$sQuery .= "
				AND per_lea.leave_num = {$aParams['nLeaveNum']}
			";
		}
		
		//Sorting
		$aColumnIndexes = array();
		$aColumnIndexes[0] = "leave_num";
		$aColumnIndexes[1] = "code_leave_name";
		$aColumnIndexes[2] = "person_name";
		$aColumnIndexes[3] = "person_position";
		$aColumnIndexes[4] = "firm";
		$aColumnIndexes[5] = "office";
		$aColumnIndexes[6] = "object";
		$aColumnIndexes[7] = "leave_type";
		$aColumnIndexes[8] = "date_";
		$aColumnIndexes[9] = "leave_from_";
		$aColumnIndexes[10] = "leave_to_";
		$aColumnIndexes[11] = "date_from";
		$aColumnIndexes[12] = "sal.count";
		$aColumnIndexes[13] = "created_user";
		$aColumnIndexes[14] = "status";
		$aColumnIndexes[15] = "time_confirm_";
		$aColumnIndexes[16] = "res_leave_from_";
		$aColumnIndexes[17] = "res_leave_to_";
		$aColumnIndexes[18] = "res_application_days";
		$aColumnIndexes[19] = "person_confirm";
		$aColumnIndexes[20] = "sal.total_sum";
		
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
		
		//Data Editting
		$aYearMonth = explode( "-", $sMonth );
		if( !isset( $aYearMonth[0] ) )$aYearMonth[0] = date( "Y" );
		if( !isset( $aYearMonth[1] ) )$aYearMonth[1] = date( "m" );
		$nDaysInMonth = date( "t", mktime( 0, 0, 0, $aYearMonth[1], 1, $aYearMonth[0] ) );
		
		$sBeginMonth = $sMonth . "-01";
		$sEndMonth = $sMonth . "-" . LPAD( $nDaysInMonth, 2, 0 );
		
		foreach( $aData as $nKey => &$aValue )
		{
			if( $aValue['res_application_days'] == -1 )
			{
				$aValue['res_application_days'] = $oDBHolidays->getWorkdaysInPeriod( max( $sBeginMonth, substr( $aValue['leave_from_'], 0, 10 ) ), min( $sEndMonth, substr( $aValue['leave_to_'], 0, 10 ) ) );
			}
		}
		//End Data Editting
		
		return $aData;
	}
	
	function getReportVouchers( DBResponse $oResponse, $aParams )
	{
		global $db_name_sod;
		
		$oDBHolidays 				= new DBHolidays();
		$oDBFiltersVisibleFields 	= new DBFiltersVisibleFields();
		$oDBSalaryEarningTypes		= new DBSalaryEarning();
		
		$nYear = isset( $aParams['nYear'] ) ? $aParams['nYear'] : 0;
		$nMonth = isset( $aParams['nMonth'] ) ? $aParams['nMonth'] : 0;
		
		//Current Month
		if( $nYear < 2007 || $nYear > 2050 )
			throw new Exception( "Невалидна година!", DBAPI_ERR_INVALID_PARAM );
		
		if( $nMonth < 1 || $nMonth > 12 )
			throw new Exception( "Невалиден месец!", DBAPI_ERR_INVALID_PARAM );
		
		$sYearMonth = $nYear . ( strlen( $nMonth ) < 2 ? "0" . $nMonth : $nMonth );
		//End Current Month
		
		//Previous Month
		$nMonth--;
		if( $nMonth < 1 ) { $nMonth = 12; $nYear--; }
		
		$sPrevYearMonth = $nYear . ( strlen( $nMonth ) < 2 ? "0" . $nMonth : $nMonth );
		
		$nMonth++;
		if( $nMonth > 12 ) { $nMonth = 1; $nYear++; }
		//End Previous Month
		
		//Get Earning Codes
		$aLeaveEarningDue = $oDBSalaryEarningTypes->getLeaveEarning( "due" );
		$aLeaveEarningUnp = $oDBSalaryEarningTypes->getLeaveEarning( "unpaid" );
		
		$sCodeDue = $sCodeUnp = "";
		if( isset( $aLeaveEarningDue['code'] ) )$sCodeDue = $aLeaveEarningDue['code'];
		if( isset( $aLeaveEarningUnp['code'] ) )$sCodeUnp = $aLeaveEarningUnp['code'];
		//End Get Earning Codes
		
		$sQuery = "
			SELECT SQL_CALC_FOUND_ROWS
				per.id AS id,
				CONCAT_WS( ' ', per.fname, per.mname, per.lname ) AS person_name,
				off.name AS office_name,
				CONCAT( obj.name, ' [', obj.num, ']' ) AS object_name,
				SUM(
					IF
					(
						( sal.code = '{$sCodeUnp}' AND sal.month = '{$sYearMonth}' ),
						sal.count,
						0
					)
				) AS unpaid_count,
				SUM(
					IF
					(
						( sal.code = '{$sCodeDue}' AND sal.month = '{$sYearMonth}' ),
						sal.count,
						0
					)
				) AS leave_count,
				ROUND(
					SUM(
						IF
						(
							( sal.code = '-КОРЕКЦИЯ5' AND sal.month = '{$sPrevYearMonth}' ),
							sal.total_sum,
							0
						)
					), 2
				) AS correction_five,
				ROUND(
					SUM(
						IF
						(
							( sal.code = '+ВАУЧЕРИ' AND sal.month = '{$sPrevYearMonth}' ),
							sal.total_sum,
							0
						)
					), 2
				) AS vouchers_plus,
				ROUND(
					SUM(
						IF
						(
							( sal.code = '-КОРЕКЦИЯ5' AND sal.month = '{$sYearMonth}' ),
							sal.total_sum,
							0
						)
					), 2
				) AS correction_five_c,
				ROUND(
					SUM(
						IF
						(
							( sal.code = '+ВАУЧЕРИ' AND sal.month = '{$sYearMonth}' ),
							sal.total_sum,
							0
						)
					), 2
				) AS vouchers_plus_c,
				IF( per.date_from != '0000-00-00', DATE_FORMAT( per.date_from, '%d.%m.%Y' ), '' ) AS date_from,
				IF( per.vacate_date != '0000-00-00', DATE_FORMAT( per.vacate_date, '%d.%m.%Y' ), '' ) AS vacate_date,
				per_con.min_cost AS min_cost
			FROM
				personnel per
			LEFT JOIN
				person_contract per_con ON ( per_con.id_person = per.id AND per_con.to_arc = 0 )
			LEFT JOIN
				sod.offices off ON off.id = per.id_office
			LEFT JOIN
				sod.objects obj ON obj.id = per.id_region_object
			LEFT JOIN
				salary sal ON sal.id_person = per.id
			WHERE
				per.to_arc = 0
				AND sal.to_arc = 0
				AND
				(
					(
						sal.code = '{$sCodeUnp}'
						AND sal.month = '{$sYearMonth}'
					)
					OR
					(
						sal.code = '{$sCodeDue}'
						AND sal.month = '{$sYearMonth}'
					)
					OR
					(
						sal.code = '-КОРЕКЦИЯ5'
						AND sal.month = '{$sPrevYearMonth}'
					)
					OR
					(
						sal.code = '+ВАУЧЕРИ'
						AND sal.month = '{$sPrevYearMonth}'
					)
					OR
					(
						sal.code = '-КОРЕКЦИЯ5'
						AND sal.month = '{$sYearMonth}'
					)
					OR
					(
						sal.code = '+ВАУЧЕРИ'
						AND sal.month = '{$sYearMonth}'
					)
				)
		";
		
		if( isset( $aParams['nIDFirm'] ) && !empty( $aParams['nIDFirm'] ) )
		{
			$sQuery .= "
				AND off.id_firm = {$aParams['nIDFirm']}
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
		
		$sQuery .= "
			GROUP BY per.id
		";
		
		$this->getResult( $sQuery, "person_name", DBAPI_SORT_ASC, $oResponse );
		
		$nWorkdays = $oDBHolidays->getWorkdaysForMonth( $nYear, $nMonth );
		
		//Totals
		$aAllData = $this->select( $sQuery );
		
		$nTotalV = 0;
		$aTotalVouchers = $aTotalCorrect = array( "P" => 0, "C" => 0 );
		
		foreach( $aAllData as $nK => &$aV )
		{
			$aV['workdays'] = $nWorkdays;
			
			if( $aV['correction_five'] != $aV['vouchers_plus'] )
			{
				if( $aV['workdays'] != 0 )
				{
					$aV['vouchers'] = round( ( ( $aV['correction_five'] / $aV['workdays'] ) * $aV['leave_count'] ), 2 );
				}
				else $aV['vouchers'] = 0;
			}
			else $aV['vouchers'] = 0;
			
			$nTotalV += $aV['vouchers'];
			
			$aTotalVouchers['P'] += $aV['vouchers_plus'];
			$aTotalVouchers['C'] += $aV['vouchers_plus_c'];
			$aTotalCorrect['P'] += $aV['correction_five'];
			$aTotalCorrect['C'] += $aV['correction_five_c'];
		}
		
		$oResponse->addTotal( "vouchers_plus", 		$aTotalVouchers['P'] );
		$oResponse->addTotal( "vouchers_plus_c", 	$aTotalVouchers['C'] );
		$oResponse->addTotal( "correction_five", 	$aTotalCorrect['P'] );
		$oResponse->addTotal( "correction_five_c", 	$aTotalCorrect['C'] );
		
		$oResponse->addTotal( "vouchers", $nTotalV );
		//End Totals
		
		foreach( $oResponse->oResult->aData as $nKey => &$aValue )
		{
			$aValue['workdays'] = $nWorkdays;
			
			if( $aValue['correction_five'] != $aValue['vouchers_plus'] )
			{
				if( $aValue['workdays'] != 0 )
				{
					$aValue['vouchers'] = round( ( ( $aValue['correction_five'] / $aValue['workdays'] ) * $aValue['leave_count'] ), 2 );
				}
				else $aValue['vouchers'] = 0;
			}
			else $aValue['vouchers'] = 0;
		}
		
		if( isset( $aParams['nIDScheme'] ) && !empty( $aParams['nIDScheme'] ) )
		{
			$aFields = $oDBFiltersVisibleFields->getFieldsByIDFilter( $aParams['nIDScheme'] );
			
			foreach( $aFields as $nKey => $sField )
			{
				if( $sField == "person_name" )$oResponse->setField( "person_name", "Служител", "Сортирай по Служител", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
				if( $sField == "date_from" )$oResponse->setField( "date_from", "Назначен на", "Сортирай по Дата на Назначение", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATE ) );
				if( $sField == "vacate_date" )$oResponse->setField( "vacate_date", "Напуснал на", "Сортирай по Дата на Напускане", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATE ) );
				if( $sField == "office_name" )$oResponse->setField( "office_name", "Регион", "Сортирай по Регион", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
				if( $sField == "object_name" )$oResponse->setField( "object_name", "Обект", "Сортирай по Обект", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
				if( $sField == "min_cost" )$oResponse->setField( "min_cost", "Основна по ТД", "Сортирай по Основна по ТД", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				if( $sField == "unpaid_count" )$oResponse->setField( "unpaid_count", "Непл. Отпуск", "Сортирай по Неплатен Отпуск", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_NUMBER ) );
				if( $sField == "leave_count" )$oResponse->setField( "leave_count", "Пл. Отпуск", "Сортирай по Платен Отпуск", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_NUMBER ) );
				if( $sField == "vouchers_plus" )$oResponse->setField( "vouchers_plus", "+ ВАУЧЕРИ", "Сортирай по + ВАУЧЕРИ", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				if( $sField == "correction_five" )$oResponse->setField( "correction_five", "Корекция 5", "Сортирай по Корекция 5", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				if( $sField == "vouchers_plus_c" )$oResponse->setField( "vouchers_plus_c", "+ ВАУЧЕРИ ( Текущ )", "Сортирай по + ВАУЧЕРИ", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				if( $sField == "correction_five_c" )$oResponse->setField( "correction_five_c", "Корекция 5 ( Текущ )", "Сортирай по Корекция 5", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				if( $sField == "workdays" )$oResponse->setField( "workdays", "Работни Дни", "Сортирай по Работни Дни", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_NUMBER ) );
				if( $sField == "vouchers" )$oResponse->setField( "vouchers", "+ Допълнителни", "Сортирай по Допълнителни", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			}
		}
		else
		{
			$oResponse->setField( "person_name", 		"Служител", 			"Сортирай по Служител", 			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "date_from", 			"Назначен на", 			"Сортирай по Дата на Назначение", 	NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATE ) );
			$oResponse->setField( "vacate_date", 		"Напуснал на",	 		"Сортирай по Дата на Напускане", 	NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATE ) );
			$oResponse->setField( "office_name", 		"Регион", 				"Сортирай по Регион", 				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "object_name", 		"Обект", 				"Сортирай по Обект", 				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "min_cost", 			"Основна по ТД",		"Сортирай по Основна по ТД", 		NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			$oResponse->setField( "unpaid_count", 		"Непл. Отпуск", 		"Сортирай по Неплатен Отпуск", 		NULL, NULL, NULL, array( "DATA_FORMAT" => DF_NUMBER ) );
			$oResponse->setField( "leave_count", 		"Пл. Отпуск", 			"Сортирай по Платен Отпуск", 		NULL, NULL, NULL, array( "DATA_FORMAT" => DF_NUMBER ) );
			$oResponse->setField( "vouchers_plus", 		"+ ВАУЧЕРИ", 			"Сортирай по + ВАУЧЕРИ", 			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			$oResponse->setField( "correction_five", 	"Корекция 5", 			"Сортирай по Корекция 5", 			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			$oResponse->setField( "vouchers_plus_c", 	"+ ВАУЧЕРИ ( Текущ )", 	"Сортирай по + ВАУЧЕРИ", 			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			$oResponse->setField( "correction_five_c", 	"Корекция 5 ( Текущ )", "Сортирай по Корекция 5", 			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			$oResponse->setField( "workdays", 			"Работни Дни", 			"Сортирай по Работни Дни", 			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_NUMBER ) );
			$oResponse->setField( "vouchers", 			"+ Допълнителни", 		"Сортирай по Допълнителни", 		NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
		}
		
		$oResponse->setFieldLink( "person_name", 		"openPerson" );
		$oResponse->setFieldLink( "vouchers_plus", 		"openPersonPrevMonth" );
		$oResponse->setFieldLink( "correction_five", 	"openPersonPrevMonth" );
		$oResponse->setFieldLink( "vouchers_plus_c", 	"openPerson" );
		$oResponse->setFieldLink( "correction_five_c", 	"openPerson" );
	}
	
	function getReportSalaryVouchers( DBResponse $oResponse, $aParams )
	{
		global $db_name_sod;
		
		$oDBHolidays 			= new DBHolidays();
		$oDBObjectDuty 			= new DBObjectDuty();
		$oDBSalaryEarningTypes 	= new DBSalaryEarning();
		
		$nYear = isset( $aParams['nYear'] ) ? $aParams['nYear'] : 0;
		$nMonth = isset( $aParams['nMonth'] ) ? $aParams['nMonth'] : 0;
		
		//Current Month
		if( $nYear < 2007 || $nYear > 2050 )
			throw new Exception( "Невалидна година!", DBAPI_ERR_INVALID_PARAM );
		
		if( $nMonth < 1 || $nMonth > 12 )
			throw new Exception( "Невалиден месец!", DBAPI_ERR_INVALID_PARAM );
		
		$sYearMonth = $nYear . ( strlen( $nMonth ) < 2 ? "0" . $nMonth : $nMonth );
		//End Current Month
		
		//Previous Month
		$nMonth--;
		if( $nMonth < 1 ) { $nMonth = 12; $nYear--; }
		
		$sPrevYearMonth = $nYear . ( strlen( $nMonth ) < 2 ? "0" . $nMonth : $nMonth );
		
		$nMonth++;
		if( $nMonth > 12 ) { $nMonth = 1; $nYear++; }
		//End Previous Month
		
		//Get Earning Codes
		$aLeaveEarning = $oDBSalaryEarningTypes->getLeaveEarning( "" );
		$aHospitalEarning = $oDBSalaryEarningTypes->getHospitalEarning();
		
		$sCode1 = $sCode2 = $sCodeHos = "";
		if( isset( $aLeaveEarning[0]['code'] ) )$sCode1 = $aLeaveEarning[0]['code'];
		if( isset( $aLeaveEarning[1]['code'] ) )$sCode2 = $aLeaveEarning[1]['code'];
		if( isset( $aHospitalEarning['code'] ) )$sCodeHos = $aHospitalEarning['code'];
		//End Get Earning Codes
		
		$sQuery = "
			SELECT SQL_CALC_FOUND_ROWS
				per.id AS id,
				CONCAT_WS( ' ', per.fname, per.mname, per.lname ) AS person_name,
				per_con.min_cost AS min_cost,
				per_con.class AS class,
				SUM( IF( ( sal.code = '{$sCode1}' OR sal.code = '{$sCode2}' ), sal.count, 0 ) ) AS leave_count,
				ROUND( SUM( IF( sal.code = '-КОРЕКЦИЯ5', sal.total_sum, 0 ) ), 2 ) AS correction_five,
				IF( per.date_from != '0000-00-00', per.date_from, '' ) AS date_from,
				IF( per.vacate_date != '0000-00-00', per.vacate_date, '' ) AS vacate_date,
				IF( per.date_from != '0000-00-00', DATE_FORMAT( per.date_from, '%d.%m.%Y' ), ' --- ' ) AS format_date_from,
				IF( per.vacate_date != '0000-00-00', DATE_FORMAT( per.vacate_date, '%d.%m.%Y' ), ' --- ' ) AS format_vacate_date
			FROM
				personnel per
			LEFT JOIN
				person_contract per_con ON ( per_con.id_person = per.id AND per_con.to_arc = 0 )
			LEFT JOIN
				sod.offices off ON off.id = per.id_office
			LEFT JOIN
				sod.objects obj ON obj.id = per.id_region_object
			LEFT JOIN
				salary sal ON
				(
					sal.id_person = per.id
					AND sal.to_arc = 0
					AND sal.month = '{$sYearMonth}'
				)
			LEFT JOIN
				person_leaves per_lea ON ( per_lea.id = sal.id_application AND per_lea.to_arc = 0 )
			WHERE
				per.to_arc = 0
				AND per.status = 'active'
				AND CONCAT( YEAR( per.date_from ), LPAD( MONTH( per.date_from ), 2, 0 ) ) <= {$sYearMonth}
				AND IF
				(
					per.vacate_date != '0000-00-00',
					CONCAT( YEAR( per.vacate_date ), LPAD( MONTH( per.vacate_date ), 2, 0 ) ) >= {$sYearMonth},
					1
				)
		";
		
		if( isset( $aParams['nIDFirm'] ) && !empty( $aParams['nIDFirm'] ) )
		{
			$sQuery .= "
				AND off.id_firm = {$aParams['nIDFirm']}
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
		
		$sQuery .= "
			GROUP BY per.id
		";
		
//		APILog::Log(0, $sQuery );
		
		$this->getResult( $sQuery, "person_name", DBAPI_SORT_ASC, $oResponse );
		
		
		$sQueryTotal = "
			SELECT SQL_CALC_FOUND_ROWS
				ROUND( SUM( IF( sal.code = '-КОРЕКЦИЯ5', sal.total_sum, 0 ) ), 2 ) AS correction_five
			FROM
				personnel per
			LEFT JOIN
				person_contract per_con ON ( per_con.id_person = per.id AND per_con.to_arc = 0 )
			LEFT JOIN
				sod.offices off ON off.id = per.id_office
			LEFT JOIN
				sod.objects obj ON obj.id = per.id_region_object
			LEFT JOIN
				salary sal ON
				(
					sal.id_person = per.id
					AND sal.to_arc = 0
					AND sal.month = '{$sYearMonth}'
				)
			LEFT JOIN
				person_leaves per_lea ON ( per_lea.id = sal.id_application AND per_lea.to_arc = 0 )
			WHERE
				per.to_arc = 0
				AND per.status = 'active'
				AND CONCAT( YEAR( per.date_from ), LPAD( MONTH( per.date_from ), 2, 0 ) ) <= {$sYearMonth}
				AND IF
				(
					per.vacate_date != '0000-00-00',
					CONCAT( YEAR( per.vacate_date ), LPAD( MONTH( per.vacate_date ), 2, 0 ) ) >= {$sYearMonth},
					1
				)
		";
		
		$total = $this->selectOne( $sQueryTotal );
		
//		APILog::Log( 0, $total );
		
		
		$nWorkdays = $oDBHolidays->getWorkdaysForMonth( $nYear, $nMonth );
		
		$nTotalVoucersSum = 0;
		
		foreach( $oResponse->oResult->aData as $nKey => &$aValue )
		{
			//Get Missed Days
			$aSFull = explode( "-", $aValue['date_from'] );
			$aEFull = explode( "-", $aValue['vacate_date'] );
			
			$nSYear 	= isset( $aSFull[0] ) ? $aSFull[0] : 0;
			$nSMonth 	= isset( $aSFull[1] ) ? $aSFull[1] : 0;
			$nSDay 		= isset( $aSFull[2] ) ? $aSFull[2] : 0;
			$nEYear 	= isset( $aEFull[0] ) ? $aEFull[0] : 0;
			$nEMonth 	= isset( $aEFull[1] ) ? $aEFull[1] : 0;
			$nEDay 		= isset( $aEFull[2] ) ? $aEFull[2] : 0;
			
			$nDaysSkipped = 0;
			if( $nSMonth != $nMonth || $nSYear != $nYear )$nSDay = 0;
			if( $nEMonth != $nMonth || $nEYear != $nYear )$nEDay = 0;
			
			//Adjust End Date
			if( $nEDay != 0 )
			{
				$nEDay--;
				if( $nEDay < 1 )
				{
					$nEMonth--;
					if( $nEMonth < 1 ) { $nEYear--; $nEMonth = 12; }
					$nEDay = date( "t", mktime( 0, 0, 0, $nEMonth, 1, $nEYear ) );
				}
			}
			//End Adjust End Date
			
			$nDaysSkipped = ( $nWorkdays - $oDBHolidays->getWorkdaysForMonth( $nYear, $nMonth, $nSDay, $nEDay ) );
			
			if( $nSDay != 0 || $nEDay != 0 )
			{
				$aValue['from_to'] = ( $nSDay != 0 ? $aValue['format_date_from'] : "---" ) . " / " . ( $nEDay != 0 ? $aValue['format_vacate_date'] : "---" );
			}
			else
			{
				$aValue['from_to'] = "-";
			}
			//End Get Missed Days
			
			$nVouchers = ( int ) $aValue['correction_five'];
			
//			APILog::Log( 0, "nVouchers=> ".$nVouchers );
			
			if( empty( $nVouchers ) )
			{
				//Get Hospitals
				$aValue['hospital_count'] = $this->getPersonHospitalDays( $aValue['id'], $nYear, $nMonth );
				//End Get Hospitals
				
				$aValue['clear_salary'] = ( $aValue['min_cost'] / $nWorkdays ) * ( $nWorkdays - ( int ) $aValue['leave_count'] - ( int ) $aValue['hospital_count'] - $nDaysSkipped );
				if( $aValue['clear_salary'] < 0 )$aValue['clear_salary'] = 0;
				
				$aValue['class_sum'] = ( $aValue['clear_salary'] * $aValue['class'] ) / 100;
				
				$aValue['workdays'] = $nWorkdays;
				
				$aValue['night_sum'] = $oDBObjectDuty->getPersonNightHoursSalary( $aValue['id'], $nYear, $nMonth, $aValue['min_cost'], $nWorkdays );
				$aValue['holiday_sum'] = $oDBObjectDuty->getPersonHolidayHoursSalary( $aValue['id'], $nYear, $nMonth, $aValue['min_cost'], $nWorkdays );
				
				$aValue['overall'] = $aValue['clear_salary'] + $aValue['class_sum'] + $aValue['night_sum'] + $aValue['holiday_sum'];
				
				$aValue['person_salary'] = $this->getPersonEarningsFinal( ( $nYear . LPAD( $nMonth, 2, 0 ) ), $aValue['id'] );
				
				$aValue['remains'] = $aValue['person_salary'] - $aValue['overall'];
				
				$aValue['voucher_work_days'] = 60 / $nWorkdays * ( $nWorkdays - $aValue['leave_count'] - $aValue['hospital_count'] - $nDaysSkipped );
				if( $aValue['voucher_work_days'] < 0 ) $aValue['voucher_work_days'] = 0;
				
				//Voucher Sum
				$aValue['voucher_sum'] = 0;
				if( $aValue['remains'] > 0 )
				{
					$aValue['voucher_sum'] = ( $aValue['remains'] < $aValue['voucher_work_days'] ) ? $aValue['remains'] : $aValue['voucher_work_days'];
					$aValue['voucher_sum'] -= ( $aValue['voucher_sum'] % 5 );
				}
				elseif( $aValue['remains'] <= 0 && $aValue['voucher_work_days'] > 0 ) $aValue['voucher_sum'] = 5;
				else $aValue['voucher_sum'] = 0;
				
				//-- Correct Voucher Sum
				if( $aValue['hospital_count'] >= $aValue['workdays'] ) $aValue['voucher_sum'] = 0;
				if( $aValue['leave_count'] >= $aValue['workdays'] ) $aValue['voucher_sum'] = 0;
				//-- End Correct Voucher Sum
				
				if( $aValue['voucher_sum'] < 0 ) $aValue['voucher_sum'] = 0;
				
				$aValue['voucher_sum'] = ( int ) $aValue['voucher_sum'];
				//End Voucher Sum
				
				$aValue['is_available'] = 1;
			}
			else
			{
				$aValue['hospital_count']		= "---";
				$aValue['clear_salary'] 		= "---";
				$aValue['class_sum'] 			= "---";
				$aValue['workdays'] 			= "---";
				$aValue['night_sum'] 			= "---";
				$aValue['holiday_sum'] 			= "---";
				$aValue['overall'] 				= "---";
				$aValue['person_salary'] 		= "---";
				$aValue['remains'] 				= "---";
				$aValue['voucher_work_days'] 	= "---";
				$aValue['voucher_sum'] 			= $aValue['correction_five'];
				
				$oResponse->setRowAttributes( $aValue['id'], array( "style" => "font-style: italic; color: #969696;" ) );
				
				$aValue['is_available'] = 0;
				
//				$nTotalVoucersSum += $aValue['voucher_sum'];
			}
			
			$aValue['v_sum'] = $aValue['voucher_sum'];
			
			$sDisableToSalary = ( empty( $aValue['v_sum'] ) || empty( $aValue['is_available'] ) ) ? "disabled=\"disabled\"" : "";
			$aValue['to_salary'] = "btns toSalary( {$aValue['id']} ); btnm{$sDisableToSalary}btne";
			
			if( empty( $aValue['v_sum'] ) || empty( $aValue['is_available'] ) )
			{
				$oResponse->setFormElementAttribute( "form1", "chk[" . $aValue['id'] . "]", "disabled", "disabled" );
			}
			
			$aValue['chk'] = 0;
		}
		
		$oResponse->addTotal( "voucher_sum", $total );

		
		
		//Checkboxes
		$oResponse->setField( 'chk', '', NULL, NULL, NULL, NULL, array( 'type' => 'checkbox' ) );
		$oResponse->setFieldData( 'chk', 'input', array( 'type' => 'checkbox', 'exception' => 'false' ) );
		$oResponse->setFieldAttributes( 'chk', array( 'style' => 'width: 25px;' ) );
		
		$oResponse->setFormElement( 'form1', 'sel', array(), '' );
		$oResponse->setFormElementChild( 'form1', 'sel', array( 'value' => '1' ), "--- Маркирай всички ---" );
		$oResponse->setFormElementChild( 'form1', 'sel', array( 'value' => '2' ), "--- Отмаркирай всички ---" );
		$oResponse->setFormElementChild( 'form1', 'sel', array( 'value' => '0' ), "------");
		$oResponse->setFormElementChild( 'form1', 'sel', array( 'value' => '3' ), "--- Прехвърли ---" );
		//End Checkboxes
		
		$oResponse->setField( "person_name", 		"Служител", 			"Сортирай по Служител", 				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
		$oResponse->setField( "workdays", 			"Работни Дни",			"Сортирай по Работни Дни", 				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CENTER ) );
		$oResponse->setField( "min_cost", 			"Основна по ТД",		"Сортирай по Основна по ТД", 			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
		$oResponse->setField( "class", 				"Клас",					"Сортирай по Клас", 					NULL, NULL, NULL, array( "DATA_FORMAT" => DF_PERCENT ) );
		$oResponse->setField( "leave_count", 		"Дни Отпуск",			"Сортирай по Дни Отпуск", 				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_NUMBER ) );
		$oResponse->setField( "hospital_count", 	"Дни Болничен",			"Сортирай по Дни Болничен", 			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_NUMBER ) );
		$oResponse->setField( "from_to", 			"Назначен / Напуснал",	"Сортирай по Дни Назначен / Напуснал", 	NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CENTER ) );
		$oResponse->setField( "clear_salary", 		"Чиста Заплата",		"Сортирай по Чиста Заплата", 			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
		$oResponse->setField( "class_sum", 			"Клас Сума",			"Сортирай по Клас Сума", 				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
		$oResponse->setField( "night_sum", 			"Нощен Труд Сума",		"Сортирай по Нощен Труд Сума", 			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
		$oResponse->setField( "holiday_sum", 		"Празничен Труд Сума",	"Сортирай по Празничен Труд Сума", 		NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
		$oResponse->setField( "overall", 			"Общо",					"Сортирай по Обща Сума", 				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
		$oResponse->setField( "person_salary", 		"Заработка",			"Сортирай по Заработка", 				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
		$oResponse->setField( "remains", 			"Остатък",				"Сортирай по Остатък", 					NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
		$oResponse->setField( "voucher_work_days", 	"Ваучер Работни Дни",	"Сортирай по Ваучер Работни Дни", 		NULL, NULL, NULL, array( "DATA_FORMAT" => DF_NUMBER ) );
		$oResponse->setField( "voucher_sum", 		"Ваучери Сума",			"Сортирай по Ваучери Сума", 			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
		$oResponse->setField( "v_sum",				"",						"",										NULL, NULL, NULL, array( "type" => "hidden", "style" => "visibility: hidden;" ) );
		$oResponse->setField( "is_available",		"",						"",										NULL, NULL, NULL, array( "type" => "hidden", "style" => "visibility: hidden;" ) );
		
		$oResponse->setField( "to_salary",			"",						"" );
		
		$oResponse->setFieldLink( "person_name", "openPerson" );
	}
	
	public function addVouchersToPerson( $nIDPerson, $nSum, $nYear = 0, $nMonth = 0 )
	{
		//Validation
		if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
		{
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		if( empty( $nSum ) )
		{
			return DBAPI_ERR_INVALID_PARAM;
		}
		//End Validation
		
		$oDBPersonnel = new DBPersonnel();
		
		//Params
		$nYear 	= empty( $nYear ) 	? data( "Y" ) : $nYear;
		$nMonth = empty( $nMonth ) 	? data( "m" ) : $nMonth;
		
		$aIDOffice = $oDBPersonnel->getPersonnelOffice( $nIDPerson );
		$nIDOffice = isset( $aIDOffice['id_office'] ) ? $aIDOffice['id_office'] : 0;
		$nIDObject = $oDBPersonnel->getPersonObject( $nIDPerson );
		//End Params
		
		//Data Update
		$aData = array();
		
		$aData['id'] = 0;
		$aData['id_person'] = $nIDPerson;
		$aData['id_office'] = $nIDOffice;
		$aData['id_object'] = $nIDObject;
		$aData['month'] = $nYear . LPAD( $nMonth, 2, 0 );
		$aData['code'] = "-КОРЕКЦИЯ5";
		$aData['is_earning'] = 0;
		$aData['sum'] = $nSum;
		$aData['description'] = "корекция 5";
		$aData['count'] = 1;
		$aData['total_sum'] = $nSum;
		$aData['created_time'] = date( "Y-m-d H:i:s" );
		$aData['created_user'] = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
		
		$this->update( $aData );
		//End Data Update
		
		return DBAPI_ERR_SUCCESS;
	}
	
	public function getPersonHospitalDays( $nIDPerson, $nYear, $nMonth )
	{
		if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) ) return 0;
		if( empty( $nYear ) || empty( $nMonth ) || !is_numeric( $nYear ) || !is_numeric( $nMonth ) ) return 0;
		
		$oDBHolidays = new DBHolidays();
		
		$sYearMonth = $nYear . "-" . LPAD( $nMonth, 2, 0 );
		
		$nDaysInMonth = date( "t", mktime( 0, 0, 0, $nMonth, 1, $nYear ) );
		
		$sBeginMonth 	= $nYear . "-" . LPAD( $nMonth, 2, 0 ) . "-01";
		$sEndMonth 		= $nYear . "-" . LPAD( $nMonth, 2, 0 ) . "-" . LPAD( $nDaysInMonth, 2, 0 );
		
		$sQuery = "
			SELECT
				DATE_FORMAT( per_lea.leave_from, '%Y-%m-%d' ) AS leave_from,
				DATE_FORMAT( per_lea.leave_to, '%Y-%m-%d' ) AS leave_to
			FROM
				salary sal
			LEFT JOIN
				person_leaves per_lea ON per_lea.id = sal.id_application
			WHERE
				sal.to_arc = 0
				AND per_lea.to_arc = 0
				AND per_lea.type = 'hospital'
				AND sal.id_person = {$nIDPerson}
				AND ( '$sYearMonth' >= SUBSTR( per_lea.leave_from, 1, 7 ) AND '$sYearMonth' <= SUBSTR( per_lea.leave_to, 1, 7 ) )
		";
		
		$aData = $this->select( $sQuery );
		
		$nSum = 0;
		foreach( $aData as $nKey => $aValue )
		{
			$nSum += $oDBHolidays->getWorkdaysInPeriod( max( $sBeginMonth, $aValue['leave_from'] ), min( $sEndMonth, $aValue['leave_to'] ) );
		}
		
		return $nSum;
	}
}

?>
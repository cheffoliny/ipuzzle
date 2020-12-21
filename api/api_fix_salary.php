<?php

	class ApiFixSalary {
		
		public function save(DBResponse $oResponse) {
			$nYear = Params::get('year','');
			$nMonth = Params::get('month','');
				
			if( empty($nYear) || $nYear < 2007 || $nYear > 2050 ) {
				throw new Exception("Въведете коректна година",DBAPI_ERR_INVALID_PARAM);
			}
			if( empty($nMonth) || $nMonth < 1 || $nMonth > 12 ) {
				throw new Exception("Въведете коректен месец",DBAPI_ERR_INVALID_PARAM);
			}
			
			$tMonth = mktime(0,0,0,$nMonth,0,$nYear);
			$tMonthNext = mktime(0,0,0,$nMonth+1,0,$nYear);
			
			if( $nMonth < 10 ) {
				$nMonth = "0".$nMonth;
			}
			$nMonth = $nYear.$nMonth;
			
			
			$oDBSalary = new DBSalary();
			$oDBPersonContract = new DBPersonContract();
			
			$aPersonsFix = array();
			$aPersonsMin = array();
			$aPersonsEarnings = array();
			$aIDPersons = array();
			$sIDPersons = '';
			
			
			$oDBSalary->delFixSalary($nMonth);
			
			$aPersonsFix = $oDBPersonContract->getPersonsWithFix($tMonth,$tMonthNext); 
			
			foreach ( $aPersonsFix AS $value ) {
				$aData = array();
				$aData['id_person'] = $value['id_person'];
				$aData['id_office'] = $value['id_office'];
				$aData['month'] = $nMonth;
				$aData['code'] = '+ЩАТ';
				$aData['is_earning'] = 1;
				$aData['sum'] = $value['cost'];
				$aData['description'] = 'Щатна заплата';
				$aData['count'] = 1;
				$aData['total_sum'] = $value['cost'];
				
				$oDBSalary->update($aData);
			}
			
			$aPersonsMin = $oDBPersonContract->getPersonsWithMin($tMonth,$tMonthNext);
			
			foreach ($aPersonsMin as $key => $value ) {
				$aIDPersons[] = $value['id_person'];
			}
			
			$sIDPersons = implode(',',$aIDPersons);
			
			APILog::Log(0,$aPersonsMin);
			APILog::Log(0,$sIDPersons);
			
			$aPersonsEarnings = $oDBSalary->getPersonsEarnings($nMonth,$sIDPersons);
			
			APILog::Log(0,$aPersonsEarnings);
			
			foreach ($aPersonsMin as $key => $value ) {
				$nEarning = isset($aPersonsEarnings[$value['id_person']]) ? $aPersonsEarnings[$value['id_person']] : 0;
				
				if($value['cost'] > $nEarning) {
					
					$aData = array();
					$aData['id_person'] = $value['id_person'];
					$aData['id_office'] = $value['id_office'];
					$aData['month'] = $nMonth;
					$aData['code'] = '+ДО_МИН';
					$aData['is_earning'] = 1;
					$aData['sum'] = $value['cost'] - $nEarning;
					$aData['description'] = 'допълване до минимална заплата';
					$aData['count'] = 1;
					$aData['total_sum'] = $value['cost'] - $nEarning;
					
					$oDBSalary->update($aData);
					
				}
			}
			
			$oResponse->printResponse();
		}
	}

?>
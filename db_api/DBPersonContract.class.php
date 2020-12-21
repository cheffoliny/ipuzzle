<?php

	class DBPersonContract extends DBBase2
	{
		public function __construct()
		{
			global $db_personnel;
			
			parent::__construct($db_personnel, 'person_contract');
		}
		
		public function getFixCostByIDPerson ($nIDPerson) {
			$sQuery = "
				SELECT
					pc.fix_cost,
					pc.to_arc,
					CONCAT_WS(' ' ,p.fname, p.mname, p.lname) as updated_user,
					DATE_FORMAT(pc.updated_time, '%d.%m.%Y %H:%i') as updated_time_
				FROM person_contract pc
				LEFT JOIN personnel p ON p.id = pc.updated_user
				WHERE 1
					AND pc.id_person = {$nIDPerson}
				ORDER BY pc.id DESC
			";
			return $this->select($sQuery);
			
		}
		
		public function getMinCostByIDPerson ($nIDPerson) {
			$sQuery = "
				SELECT
					pc.min_cost,
					pc.to_arc,
					CONCAT_WS(' ' ,p.fname, p.mname, p.lname) as updated_user,
					DATE_FORMAT(pc.updated_time, '%d.%m.%Y %H:%i') as updated_time_
				FROM person_contract pc
				LEFT JOIN personnel p ON p.id = pc.updated_user
				WHERE 1
					AND pc.id_person = {$nIDPerson}
				ORDER BY pc.id DESC
			";
			return $this->select($sQuery);
			
		}
		
		public function getInsuranceByIDPerson ($nIDPerson) {
			$sQuery = "
				SELECT
					pc.insurance,
					pc.to_arc,
					CONCAT_WS(' ' ,p.fname, p.mname, p.lname) as updated_user,
					DATE_FORMAT(pc.updated_time, '%d.%m.%Y %H:%i') as updated_time_
				FROM person_contract pc
				LEFT JOIN personnel p ON p.id = pc.updated_user
				WHERE 1
					AND pc.id_person = {$nIDPerson}
				ORDER BY pc.id DESC
			";
			return $this->select($sQuery);
			
		}
		
		public function getTechSupportFactorByIDPerson ($nIDPerson) {
			$sQuery = "
				SELECT
					pc.tech_support_factor,
					pc.to_arc,
					CONCAT_WS(' ' ,p.fname, p.mname, p.lname) as updated_user,
					DATE_FORMAT(pc.updated_time, '%d.%m.%Y %H:%i') as updated_time_
				FROM person_contract pc
				LEFT JOIN personnel p ON p.id = pc.updated_user
				WHERE 1
					AND pc.id_person = {$nIDPerson}
				ORDER BY pc.id DESC
			";
			return $this->select($sQuery);
		}
		
		public function getShiftsFactorByIDPerson ($nIDPerson) {
			$sQuery = "
				SELECT
					pc.shifts_factor,
					pc.to_arc,
					CONCAT_WS(' ' ,p.fname, p.mname, p.lname) as updated_user,
					DATE_FORMAT(pc.updated_time, '%d.%m.%Y %H:%i') as updated_time_
				FROM person_contract pc
				LEFT JOIN personnel p ON p.id = pc.updated_user
				WHERE 1
					AND pc.id_person = {$nIDPerson}
				ORDER BY pc.id DESC
			";
			return $this->select($sQuery);
		}
		
		public function getAllByIDPerson ($nIDPerson) {
			$sQuery = "
				SELECT 
					*
				FROM person_contract 
				WHERE 1 
					AND to_arc = 0 
					AND id_person = {$nIDPerson}
			";
			return $this->selectOnce($sQuery);
		}
		
		public function getPersonsWithFix($tMonth,$tMonthNext) {
			
			$sQuery = "
				SELECT 
					pc.id_person,
					p.id_office,
					if(	$tMonth < UNIX_TIMESTAMP(p.date_from) AND UNIX_TIMESTAMP(p.date_from) < $tMonthNext,
						ROUND( ( pc.fix_cost * ($tMonthNext - UNIX_TIMESTAMP(p.date_from) ) ) / ($tMonthNext - $tMonth),2 ),
						pc.fix_cost) AS cost
				FROM person_contract pc
				LEFT JOIN personnel p ON p.id = pc.id_person
				WHERE 1
					AND pc.to_arc = 0
					AND pc.type_salary = 'fix'
					AND pc.fix_cost != 0
					AND p.status = 'active' 
					AND $tMonthNext > UNIX_TIMESTAMP(p.date_from)
			";
			
			return $this->select($sQuery);
		}
		
		public function getPersonsWithMin($tMonth,$tMonthNext) {
			
			$sQuery = "
				SELECT 
					pc.id_person,
					p.id_office,
					if(	$tMonth < UNIX_TIMESTAMP(p.date_from) AND UNIX_TIMESTAMP(p.date_from) < $tMonthNext,
						ROUND( ( pc.min_cost * ($tMonthNext - UNIX_TIMESTAMP(p.date_from) ) ) / ($tMonthNext - $tMonth),2 ),
						pc.min_cost) AS cost
				FROM person_contract pc
				LEFT JOIN personnel p ON p.id = pc.id_person
				WHERE 1
					AND pc.to_arc = 0
					AND pc.type_salary = 'min'
					AND pc.min_cost != 0
					AND p.status = 'active' 
					AND $tMonthNext > UNIX_TIMESTAMP(p.date_from)
			";
			
			return $this->select($sQuery);
		}
	}
?>
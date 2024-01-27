<?php

	class DBPersonContractBasicSalaries extends DBBase2 
	{
		public function __construct()
		{
			global $db_personnel;
			
			parent::__construct( $db_personnel, 'person_contract_basic_salaries' );
		}
		
		public function getBasicSalaries( $nIDPerson, $sDate = "" )
		{
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) ) return array();
			
			$sQuery = "
				SELECT
					id,
					min_cost,
					work_hours,
					DATE_FORMAT( date_from, '%d.%m.%Y' ) AS date_from,
					date_from AS date_from_sorted
				FROM
					person_contract_basic_salaries
				WHERE
					id_person = {$nIDPerson}
					AND " . ( empty( $sDate ) ? "1" : "date_from <= '{$sDate}'" ) . "
				ORDER BY
					" . ( empty( $sDate ) ? "date_from_sorted ASC" : "date_from_sorted DESC, id DESC" ) . "
				" . ( empty( $sDate ) ? "" : "LIMIT 1" ) . "
			";
			
			if( empty( $sDate ) ) return $this->select( $sQuery );
			else return $this->selectOnce( $sQuery );
		}
		
		/* 2010-08-18 : Out of order
		public function checkBasicSalaries()
		{
			$this->oDB->StartTrans();
			
			$oDBSystem = new DBSystem();
			$oDBPersonnel = new DBPersonnel();
			$oDBPersonContract = new DBPersonContract();
			
			$sBasicSalariesCheck = $oDBSystem->getLastBasicSalaries();
			$sToday = date( "Y-m-d" );
			
			if( $sBasicSalariesCheck < $sToday )
			{
				$sQuery = "
					SELECT
						id_person,
						min_cost,
						work_hours
					FROM
						person_contract_basic_salaries
					WHERE
						date_from = '{$sToday}'
					ORDER BY id
				";
				
				$aData = $this->select( $sQuery );
				
				foreach( $aData as $aBasicSalary )
				{
					$aLastContract = $oDBPersonContract->getLastByIDPerson( $aBasicSalary['id_person'] );
					
					//Dispose
					$aLastContract['to_arc'] = 1;
					$oDBPersonContract->update( $aLastContract );
					//End Dispose
					
					//Add New
					$aLastContract['id'] = 0;
					$aLastContract['min_cost'] = $aBasicSalary['min_cost'];
					$aLastContract['work_hours'] = $aBasicSalary['work_hours'];
					$aLastContract['to_arc'] = 0;
					$oDBPersonContract->update( $aLastContract );
					//End Add New
				}
			}
			
			$this->oDB->Execute( "DELETE FROM person_contract_basic_salaries WHERE date_from = '{$sToday}'" );
			
			$bIsItOK = $this->oDB->CompleteTrans();
			if( $bIsItOK )
			{
				$oDBSystem->setLastBasicSalaries( $sToday );
				return DBAPI_ERR_SUCCESS;
			}
			else return DBAPI_ERR_SQL_QUERY;
		}
		*/
	}

?>
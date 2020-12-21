<?php

	class DBWorkContractsEnd extends DBBase2
	{
		public function __construct()
		{
			global $db_personnel;
			
			parent::__construct( $db_personnel, 'work_contracts_end' );
		}
		
		function getWorkContracts( $nIDPerson )
		{
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )return array();
			
			$sQuery = "
					SELECT
						w.id,
						w.num,
						DATE_FORMAT( w.date, '%d.%m.%Y' ) AS date
					FROM work_contracts_end w
					WHERE 1
						AND w.to_arc = 0
						AND w.id_person = {$nIDPerson}
					ORDER BY w.date
			";
			
			return $this->select( $sQuery );
		}
		
		function getWorkContractData( $nID )
		{
			if( empty( $nID ) || !is_numeric( $nID ) )return array();
			
			$sQuery = "
					SELECT
						id,
						id_person,
						num,
						DATE_FORMAT( date, '%d.%m.%Y' ) AS date,
						DATE_FORMAT( date_from, '%d.%m.%Y' ) AS date_from,
						clause_paragraph,
						relation_addition,
						work_place,
						address,
						position,
						position_code,
						reasons,
						compensations
					FROM work_contracts_end
					WHERE 1
						AND to_arc = 0
						AND id = ($nID)
					LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
	}

?>
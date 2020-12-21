<?php

	class DBPayDesksPersons extends DBBase2
	{
		public function __construct()
		{
			global $db_finance;
			
			parent::__construct( $db_finance, 'pay_desks_persons' );
		}
		
		public function getPersonIDsForPayDesk( $nIDPayDesk )
		{
			if( empty( $nIDPayDesk ) || !is_numeric( $nIDPayDesk ) )
			{
				return array();
			}
			
			$sQuery = "
					SELECT
						id_person
					FROM
						pay_desks_persons
					WHERE
						id_pay_desk = {$nIDPayDesk}
			";
			
			$aResult = array();
			$aEnd = array();
			
			$aResult = $this->select( $sQuery );
			
			foreach( $aResult as $aElement )
			{
				$aEnd[] = $aElement['id_person'];
			}
			
			return $aEnd;
		}
		
		public function deletePersonsAtPayDesk( $nIDPayDesk )
		{
			if( empty( $nIDPayDesk ) || !is_numeric( $nIDPayDesk ) )
			{
				return DBAPI_ERR_INVALID_PARAM;
			}
			
			$sQuery = "
					DELETE
					FROM
						pay_desks_persons
					WHERE
						id_pay_desk = {$nIDPayDesk}
			";
			
			$this->select( $sQuery );
			
			return DBAPI_ERR_SUCCESS;
		}
		
		public function getPayDesksForPerson( $nIDPerson )
		{
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				throw new Exception( "Невалиден служител", DBAPI_ERR_INVALID_PARAM );
			}
			
			$sQuery = "
					SELECT
						pd.id,
						pd.name
					FROM
						pay_desks_persons pdp
					LEFT JOIN
						pay_desks pd ON pd.id = pdp.id_pay_desk
					WHERE
						pd.to_arc = 0
						AND pdp.id_person = {$nIDPerson}
					ORDER BY name
			";
			
			return $this->select( $sQuery );
		}
	}

?>
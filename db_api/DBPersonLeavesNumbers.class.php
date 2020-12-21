<?php
	class DBPersonLeavesNumbers extends DBBase2
	{
		public function __construct()
		{
			global $db_personnel;
			
			parent::__construct( $db_personnel, "person_leaves_numbers" );
		}
		
		public function getNumberByFirm( $nIDFirm, $nYear )
		{
			if( empty( $nYear ) || !is_numeric( $nYear ) )$nYear = date( "Y" );
			
			$sQuery = "
				SELECT
					MAX( per_lea.leave_num ) AS num_leave
				FROM
					person_leaves per_lea
				LEFT JOIN
					personnel per ON per.id = per_lea.id_person
				LEFT JOIN
					sod.offices off ON off.id = per.id_office
				LEFT JOIN
					person_leaves_numbers per_lea_num ON CONCAT( ',', per_lea_num.id_firms, ',' ) LIKE CONCAT( '%,', off.id_firm, ',%' )
				WHERE
					per_lea.to_arc = 0
					AND per.to_arc = 0
					AND off.to_arc = 0
					AND per_lea.type = 'application'
					AND per_lea.year = {$nYear}
					AND CONCAT( ',', per_lea_num.id_firms, ',' ) LIKE '%,{$nIDFirm},%'
				GROUP BY per_lea_num.id
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( !empty( $aData ) && isset( $aData['num_leave'] ) )
			{
				if( empty( $aData['num_leave'] ) )$aData['num_leave'] = 0;
				$aData['num_leave']++;
				return $aData['num_leave'];
			}
			else return 1;
		}
		
		public function checkNewYear()
		{
			$nYear = ( int ) date( "Y" );
			
			$sQuery = "
				SELECT
					*
				FROM
					person_leaves_numbers
				WHERE
					last_nulled < $nYear
			";
			
			$aData = $this->select( $sQuery );
			
			foreach( $aData as $nKey => &$aValue )
			{
				$aValue['last_nulled'] = $nYear;
				$aValue['num_leave'] = 1;
				
				$this->update( $aValue );
			}
		}
	}
?>
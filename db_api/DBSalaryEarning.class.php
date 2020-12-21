<?php
	class DBSalaryEarning
		extends DBBase2 {
			
		public function __construct() {
			global $db_personnel;
			//$db_sod->debug=true;
			
			parent::__construct($db_personnel, 'salary_earning_types');
			//$db_personnel->debug()=true;
		}
		
		public function getSourceByID($nID)
		{
			$sQuery = "
				SELECT 
					source
				FROM salary_earning_types 
				WHERE id = {$nID} 				
			";
			return $this->selectOnce( $sQuery );
		}
		
		public function eraseSources( $sSource ) {
			$sQuery = "
				UPDATE 
					salary_earning_types
				SET source = ''
				WHERE source = '{$sSource}'			
			";
			$this->oDB->Execute( $sQuery );
		}
		
		public function getEarnings() {
			$sQuery = "
				SELECT
					code,
					name
				FROM salary_earning_types
				WHERE to_arc = 0		
			
			";
			
			return $this->selectAssoc($sQuery);			
		}
		
		public function getEarningCode() {
			$sQuery = "
				SELECT
					id,
					CONCAT(code, '$$$', name) as code
				FROM salary_earning_types
				WHERE to_arc = 0		
			
			";
			
			return $this->selectAssoc($sQuery);			
		}
		
		public function getCodeTechnics() {
			$sQuery = "
			
				SELECT 
					code
				FROM salary_earning_types
				WHERE 1
					AND to_arc = 0
					AND source = 'limit_card'
				LIMIT 1
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getCodeEarning() {
			$sQuery = "
			
				SELECT 
					code,
					name
				FROM salary_earning_types
				WHERE 1
					AND to_arc = 0
					AND source = 'asset_earning'
				LIMIT 1
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function getCodeAssetOwn() {
			
			$sQuery = "
				SELECT 
					code,
					name
				FROM salary_earning_types
				WHERE 1
					AND to_arc = 0
					AND source = 'asset_own'
				LIMIT 1
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function getEarningsByCode( $code ) {
			$sQuery = "
				SELECT
					id,
					name
				FROM salary_earning_types
				WHERE to_arc = 0
					AND code = '{$code}'		
			";
			
			return $this->select($sQuery);			
		}

		public function getCodeById( $nID ) {
			$sQuery = "
				SELECT
					code
				FROM salary_earning_types
				WHERE to_arc = 0
					AND id = '{$nID}'		
			";
			
			return $this->selectOne($sQuery);			
		}
		
		public function getLeaveEarning( $sType = "due" )
		{
			$sQuery = "
				SELECT
					id,
					code,
					leave_type,
					name
				FROM
					salary_earning_types
				WHERE
					to_arc = 0
			";
			
			if( !empty( $sType ) )
			{
				$sQuery .= "
					AND leave_type = '{$sType}'
				LIMIT 1
				";
				
				return $this->selectOnce( $sQuery );
			}
			else
			{
				$sQuery .= "
					AND ( leave_type = 'due' OR leave_type = 'unpaid' )
				";
				
				return $this->select( $sQuery );
			}
		}
		
		public function getCompensationEarning()
		{
			$sQuery = "
				SELECT
					id,
					code,
					name
				FROM
					salary_earning_types
				WHERE
					to_arc = 0
					AND is_compensation = 1
				LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getHospitalEarning()
		{
			$sQuery = "
				SELECT
					id,
					code,
					name
				FROM
					salary_earning_types
				WHERE
					to_arc = 0
					AND is_hospital = 1
				LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
		
	}
?>
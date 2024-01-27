<?php
	class DBSalaryExpense
		extends DBBase2 {
			
		public function __construct() {
			global $db_personnel;
			//$db_sod->debug=true;
			
			parent::__construct($db_personnel, 'salary_earning_expense');
		}
		
		public function getSourceByID($nID)
		{
			$sQuery = "
				SELECT 
					source
				FROM salary_expense_types 
				WHERE id = {$nID} 				
			";
			return $this->selectOnce( $sQuery );
		}
		
		public function eraseSources( $sSource ) {
			$sQuery = "
				UPDATE 
					salary_expense_types
				SET source = ''
				WHERE source = '{$sSource}'			
			";
			$this->oDB->Execute( $sQuery );
		}
		
		public function getExpence() {
			$sQuery = "
				SELECT
					code,
					name
				FROM salary_expense_types
				WHERE to_arc = 0		
			
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getExpenseCode() {
			$sQuery = "
				SELECT
					id,
					CONCAT(code, '$$$', name) as code
				FROM salary_expense_types
				WHERE to_arc = 0		
			
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getExpenceByCode( $code ) {
			$sQuery = "
				SELECT
					id,
					name
				FROM salary_expense_types
				WHERE to_arc = 0
					AND code = '{$code}'		
			";
			
			return $this->select($sQuery);			
		}		
		
		public function getCodeById( $nID ) {
			$sQuery = "
				SELECT
					code
				FROM salary_expense_types
				WHERE to_arc = 0
					AND id = '{$nID}'		
			";
			
			return $this->selectOne($sQuery);			
		}
		
	}
?>
<?php
	
	class DBSystem
		extends DBBase2 
	{
		public function __construct()
		{
			global $db_system;
			
			parent::__construct($db_system, 'system');
		}
		
		public function initSession()
		{
			$sQuery = "
			 	SELECT *
			 	FROM system
			 	LIMIT 1
			 	";
				
			try {
				$_SESSION['system'] = $this->selectOnce( $sQuery );
			}
			catch( Exception $e ){};
		}
		
		public function getAutoSalaryLastMonth() {
			$sQuery = "
				SELECT 
				auto_salary_last_month
				FROM system
				LIMIT 1
			";
			return $this->selectOne($sQuery);
		}
		
		public function updateAutoSalaryLastMonth($nMonth) {
			$sQuery = "
				UPDATE
 					system 
 				SET auto_salary_last_month = {$nMonth} 
			";
			
			$this->oDB->Execute( $sQuery );
		}
		
		public function getRow() {
			
			$sQuery = "
				SELECT
					* 
				FROM system
				LIMIT 1
				FOR UPDATE
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function setLastNumSaleDoc($nNum) {
			
			$sQuery = "
				UPDATE
					system
				SET
					last_num_sale_doc = {$nNum}
			";
			
			$this->oDB->Execute($sQuery);
		}
		
		public function setLastNumOrder($nNum) {
			
			$sQuery = "
				UPDATE
					system
				SET
					last_num_order = {$nNum}
			";
			
			$this->oDB->Execute($sQuery);
		}
		
		public function setLastNumTransfer( $nNum )
		{
			
			$sQuery = "
				UPDATE
					system
				SET
					last_num_transfer = {$nNum}
			";
			
			$this->oDB->Execute( $sQuery );
		}
	}
	
?>
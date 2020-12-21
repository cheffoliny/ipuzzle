<?php
	class DBBudgetRows extends DBBase2 {
		
		public function __construct() {
			global $db_finance;
			
			parent::__construct($db_finance, "budget_rows");
		}
	}
?>
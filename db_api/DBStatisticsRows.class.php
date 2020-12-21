<?php

	class DBStatisticsRows extends DBBase2 {
		
		function __construct() {
			
			global $db_finance;
			
			parent::__construct($db_finance,'statistics_rows');
		}
		
		public function getByIDStatistic($nIDStatistic) {
			
			$sQuery = "
				SELECT 
					*
				FROM statistics_rows
				WHERE id_statistic = {$nIDStatistic}
			";
			
			return $this->select($sQuery);
		}
		
	}

?>
<?php
	class DBAdminSalaryTotalFilters extends DBBase2 
	{
		public function __construct()
		{
			global $db_personnel;
			
			parent::__construct($db_personnel, 'admin_salary_total_filters');
		}
		
		public function getSchemes() {
			$nID = $_SESSION['userdata']['id_person'];
			
			$sQuery = "
				SELECT
					id,
					name
				FROM admin_salary_total_filters
				WHERE 1
					AND to_arc = 0
				 	AND updated_user = {$nID}
			";
			
			return $this->selectAssoc($sQuery);			
			
		}
	}
?>
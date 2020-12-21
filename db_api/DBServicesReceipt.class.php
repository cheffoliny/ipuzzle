<?php
	class DBServicesReceipt extends DBBase2 {
		
		public function __construct() {
			global $db_finance;
			
			parent::__construct($db_finance, "new_services");
		}
		

		/**
		 * Функцията връща писък с услугите (id, name), които ще  
		 * участват в рецептурника, подредени по азбучен ред;
		 * 
		 * @author Павел Петров
		 * @name getAllServiceReceipts()
		 * 
		 * @return (array) - списък с услугите (id, name), които ще  
		 * участват в рецептурника, подредени по азбучен ред;
		 */		
		public function getAllServiceReceipts() {
			global $db_name_finance;
			
			$sQuery = "
				SELECT
					id,
					name
				FROM {$db_name_finance}.new_services
				WHERE to_arc = 0
				ORDER BY name
			";
			
			return $this->select($sQuery);
		}
	}
?>
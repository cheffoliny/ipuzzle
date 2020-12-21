<?php
	class DBConfig134 extends DBBase2 	{
		public function __construct() {
			global $db_telepol;
			parent::__construct($db_telepol, 'config');
		}

		public function getNum() {
			$sQuery = "
				SELECT 
					contract_free_num
				FROM config			
			";
			return $this->selectOne($sQuery);
		}
		
		public function plusplusNum() {
			$sQuery = "
				UPDATE
					config
				SET contract_free_num = contract_free_num + 1
			";
			
			$this->oDB->Execute($sQuery);
		}
	}
?>
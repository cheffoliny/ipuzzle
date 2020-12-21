<?php

	class DBSingles extends DBBase2 {
		public function __construct() {
			global $db_telepol;
			parent::__construct($db_telepol, "singles");
		}	
	}
?>
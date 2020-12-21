<?php

	class DBFunctions extends DBBase2
	{
		public function __construct()
		{
			global $db_auto;
			
			parent::__construct($db_auto, 'functions');
		}
		public function getFunctions()
		{
			$sQuery = "
				SELECT
					id,
					name
				FROM functions
			";
			return $this->selectAssoc($sQuery);
		}
	}
?>
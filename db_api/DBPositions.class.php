<?php

	class DBPositions 
		extends DBBase2
	{
		public function __construct()
		{
			global $db_personnel;
			//$db_personnel->debug=true;
			parent::__construct($db_personnel, "positions");
		}
		
		public function getPositions()
		{
			$sQuery = "
				SELECT 
					id,
					name
				FROM positions 
				WHERE 
					to_arc = 0
				ORDER BY name
			";
			
			return $this->selectAssoc( $sQuery );
		}
	}
?>
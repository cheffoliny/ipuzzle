<?php
	class DBTemplets extends DBBase2 	{
		public function __construct() {
			global $db_telepol;
			parent::__construct($db_telepol, 'templets');
		}
		
		public function getTemplets()	{

			$sQuery = "
				SELECT
					id_templet,
					name
				FROM templets
			";
			
			return $this->selectAssoc( $sQuery );
		}

	}
?>
<?php
	class DBFaces2 extends DBBase2 	{
		public function __construct() {
			global $db_telepol;
			parent::__construct($db_telepol, 'faces');
		}
		
		public function getFaces( $nID ) {
			
			$sQuery = "
				SELECT 
					f.name,
					f.phone
				FROM faces f
				WHERE 1 
					AND f.id_obj = {$nID}
			";

			return $this->select($sQuery);
		}
	}
?>
<?php

	class DBObjectStatuses extends DBBase2
	{
		public function __construct()
		{
			global $db_sod;
			
			parent::__construct($db_sod, 'object_statuses');
		}
		
		public function getObjectStatus( $nID ) {
			$sQuery = "
				SELECT
					id_status
				FROM object_statuses
				WHERE 1
				AND to_arc = 0
				AND id_obj = {$nID}
				HAVING MAX(updated_time)
				LIMIT 1
			";
			return $this->selectOne($sQuery);
		}
	}

?>
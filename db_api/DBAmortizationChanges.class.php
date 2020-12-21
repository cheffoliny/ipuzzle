<?php
		class DBAmortizationChanges extends DBBase2 
		{
			public function __construct()
			{
				global $db_storage;
				$db_storage->debug=true;
				parent::__construct( $db_storage, 'amortization_changes' );
			}
			
				
			public function getOneByID ($nID) {
				global $db_name_personnel;
				$sQuery = " 
						SELECT
						ac.old_value,
						ac.new_value,
						ac.updated_user,
						DATE_FORMAT(ac.updated_time, '%d.%m.%Y %H:%i:%s') as updated_time,
						CONCAT_WS(' ',p.fname,p.mname,p.lname) as updated_name
						FROM amortization_changes ac
						LEFT JOIN {$db_name_personnel}.personnel p ON p.id = ac.updated_user
						WHERE 1
							AND ac.to_arc = 0
							AND ac.id_asset = {$nID}
						ORDER BY ac.id DESC
						LIMIT 1
				";
			 return $this->selectOnce($sQuery);
			}
			
			public function getAll () {
				$sQuery = "
					SELECT 
					*
					FROM amortization_changes
					WHERE to_arc = 0
					";
				return $this->select($sQuery);
			}
				
		}
?>
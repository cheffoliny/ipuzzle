<?php
	class DBAlarmReasons
		extends DBBase2 
		{
			public function __construct()
			{
				global $db_sod;
				
				parent::__construct($db_sod, 'alarm_reasons');
			}
			
			public function getReport($aParams, DBResponse $oResponse )
			{
					global $db_name_personnel;
					
					$right_edit = false;
					if (!empty($_SESSION['userdata']['access_right_levels']))
						if (in_array('alarm_reasons_edit', $_SESSION['userdata']['access_right_levels']))
						{
							$right_edit = true;
						}
	
					$sQuery = "
							SELECT SQL_CALC_FOUND_ROWS
								ar.id, 
								ar.name,
								IF( 
								p.id, 
								CONCAT(
									CONCAT_WS(' ', p.fname, p.mname, p.lname),
									' (',
									DATE_FORMAT(ar.updated_time, '%d.%m.%Y %H:%i:%s'),
									')'
									),
									''
									) AS updated_user
								FROM alarm_reasons ar
								LEFT JOIN {$db_name_personnel}.personnel p ON ar.updated_user = p.id
								WHERE ar.to_arc=0
							";
					
					$this->getResult($sQuery, 'name', DBAPI_SORT_ASC, $oResponse);
					
					$oResponse->setField("name", "Име", "Сортирай по име");
					$oResponse->setField("updated_user", "Последна редакция", "Сортирай по последна редакция");
					
					if ($right_edit) {
						$oResponse->setField( '', '', '', 'images/cancel.gif', 'deleteReason', '');
						$oResponse->setFieldLink("name", "openReason");
					}
			}
			
			public function getReasons() {

				$sQuery = "
					SELECT 
						ar.id,
						ar.name
					FROM alarm_reasons ar
					WHERE ar.to_arc = 0
					ORDER BY ar.name
				";
				
				return $this->selectAssoc( $sQuery );
			}
			
		}
?>
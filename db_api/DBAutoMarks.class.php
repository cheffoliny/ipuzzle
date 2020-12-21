<?php
	class DBAutoMarks
		extends DBBase2 {
			
		public function __construct() {
			global $db_auto;
			
			parent::__construct($db_auto, 'auto_marks');
		}
		
		
		public function getReport( DBResponse $oResponse )
		{	
			global $db_name_personnel;
			
			$right_edit = false;
			if (!empty($_SESSION['userdata']['access_right_levels']))
				if (in_array('auto_marks_edit', $_SESSION['userdata']['access_right_levels']))
				{
					$right_edit = true;
				}
			
			$sQuery = "
				SELECT
					am.id,
				    am.mark, 
				    CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' [', DATE_FORMAT(am.updated_time, '%d.%m.%Y %H:%i:%s'), ']') AS updated_user
				FROM auto_marks am
				LEFT JOIN {$db_name_personnel}.personnel as up ON am.updated_user = up.id
				WHERE am.to_arc = 0
				";
			
			$this->getResult($sQuery, 'mark', DBAPI_SORT_ASC, $oResponse);
			
			$oResponse->setField("mark", "Марка", "Сортирай по марка");
			$oResponse->setField("updated_user", "Последна редакция", "Сортирай по последна редакция");
			if($right_edit)	{
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'delAutoMark', '');
				$oResponse->setFieldLink("mark", "editAutoMark");
			}
		}
		public function getMarks()
		{
			$sQuery = "
				SELECT
					id,
					mark
				FROM auto_marks
				WHERE to_arc = 0
			";
			return $this -> selectAssoc($sQuery);
		}

	}
	
?>
<?php
	class DBAutoModels
		extends DBBase2 {
			
		public function __construct() {
			global $db_auto;
			
			parent::__construct($db_auto, 'auto_models');
		}
		
		
		public function getReport($nMark, DBResponse $oResponse )
		{	
			global $db_name_personnel,$db_name_auto;
			
			$right_edit = false;
			if (!empty($_SESSION['userdata']['access_right_levels']))
				if (in_array('auto_models_edit', $_SESSION['userdata']['access_right_levels']))
				{
					$right_edit = true;
				}
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					models.id,
					models.model,
					marks.mark, 
				    CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' [', DATE_FORMAT(models.updated_time, '%d.%m.%Y %H:%i:%s'), ']') AS updated_user
				FROM auto_models models
				LEFT JOIN {$db_name_auto}.auto_marks as marks ON models.id_mark = marks.id
				LEFT JOIN {$db_name_personnel}.personnel as up ON models.updated_user = up.id
				WHERE 1
					AND marks.to_arc = 0 
					AND models.to_arc = 0
				";
			
			if (!empty($nMark)) {
				$sQuery .= " AND models.id_mark = {$nMark} ";
			}
			
			$this->getResult($sQuery, 'model', DBAPI_SORT_ASC, $oResponse);
			
			$oResponse->setField("id", "id", "Сортирай по id");
			$oResponse->setField("model", "Модел", "Сортирай по модел");
			$oResponse->setField("mark", "Марка", "Сортирай по марка");
			$oResponse->setField("updated_user", "Последна редакция", "Сортирай по последна редакция");
			
			if ($right_edit) {
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'delAutoModel', '');
				$oResponse->setFieldLink("id", "editAutoModel");
				$oResponse->setFieldLink("model", "editAutoModel");
			}
		}
		
		public function getMarkByIDModel($nID)
		{
			$sQuery = "
				SELECT
					id_mark
				FROM auto_models 
				WHERE {$nID} = id AND to_arc=0
			";
			return $this->select($sQuery);
		}
		public function getModelsByIDMark($nID)
		{
			$sQuery = "
				SELECT
					id,
					model
				FROM auto_models
				WHERE id_mark = {$nID} AND to_arc = 0
			";
			return $this->selectAssoc($sQuery);
		}

	}
	
?>
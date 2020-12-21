<?php
	class DBAuto
		extends DBBase2 {
			
		public function __construct() {
			global $db_auto;
			
			parent::__construct($db_auto, 'auto');
		}
				
		public function getPatrul( $nIDRegion , $sBusyAutos) {

			$nIDRegion = (int) $nIDRegion;

			$sQuery = "
				SELECT 
					a.id,
					CONCAT(m.model, ' [', a.reg_num, ']') AS auto
				FROM auto a
				LEFT JOIN auto_models m ON a.id_model = m.id
				LEFT JOIN functions f ON f.id = a.id_function
				WHERE 1
					AND f.function_type = 'patrul'
					AND a.id_office = {$nIDRegion}
			";
			
			if(!empty($sBusyAutos))
			{
				$sQuery .= "AND a.id NOT IN ($sBusyAutos)";
			}
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getAutoByID( $nID) {

			$sQuery = "
				SELECT 
					CONCAT(m.model, ' [', a.reg_num, ']') AS auto
				FROM auto a
				LEFT JOIN auto_models m ON a.id_model = m.id
				WHERE a.id = {$nID}
			";
			
			return $this->selectOne( $sQuery );
		}
		
		public function getPatrulPosition( ) {

			$sQuery = "
				SELECT 
					p.id
				FROM functions p
				WHERE 1
					AND p.function_type = 'patrul'
				LIMIT 1
			";
		
			return $this->selectOnce( $sQuery );
		}

		public function getReport( DBResponse $oResponse ) {
			$nFirm = Params::get('nIDFirm','0');
			$nOffice = Params::get('nIDOffice','0');
			
			global $db_name_personnel,$db_name_auto,$db_name_sod;
			
			$right_edit = false;
			if (!empty($_SESSION['userdata']['access_right_levels']))
				if (in_array('autos_edit', $_SESSION['userdata']['access_right_levels']))
				{
					$right_edit = true;
				}
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					a.id,
					CONCAT(marks.mark,' ',am.model,' [',a.reg_num,'] ') AS model,
					CONCAT_WS(' ', p.fname, p.mname, p.lname) AS responsible,
				    CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' [', DATE_FORMAT(a.updated_time, '%d.%m.%Y %H:%i:%s'), ']') AS updated_user
				FROM auto a
				LEFT JOIN {$db_name_auto}.auto_models as am ON a.id_model = am.id
				LEFT JOIN {$db_name_auto}.auto_marks as marks ON am.id_mark = marks.id
				LEFT JOIN {$db_name_personnel}.personnel as up ON a.updated_user = up.id
				LEFT JOIN {$db_name_personnel}.personnel as p ON a.id_person = p.id
				LEFT JOIN {$db_name_sod}.offices as of ON a.id_office = of.id
				LEFT JOIN {$db_name_sod}.firms as fm ON of.id_firm = fm.id
				WHERE a.to_arc = 0  AND fm.id = {$nFirm}
			";
			
			if($nOffice)	{
				$sQuery.=" AND a.id_office = {$nOffice} ";
			}
			
			$this->getResult($sQuery, 'model', DBAPI_SORT_ASC, $oResponse);
			
			$oResponse->setField("model", "Автомобил", "Сортирай по автомобил");
			$oResponse->setField("responsible", "Отговорник", "Сортирай по отговорник");
			$oResponse->setField("updated_user", "Последна редакция", "Сортирай по последна редакция");
			
			if ($right_edit) {
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'delAuto', '');
				$oResponse->setFieldLink("model", "editAuto");
			}
		}
				
	}
	
?>
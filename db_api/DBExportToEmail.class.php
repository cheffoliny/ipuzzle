<?php
	class DBExportToEmail extends DBBase2 {
		
		public function __construct() {
			global $db_finance;
			
			parent::__construct($db_finance, "export_to_email");
		}
		
		public function getReport( DBResponse $oResponse ) {
			global $db_name_finance, $db_name_personnel;
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					 e.id,
					 e.email,
					 CONCAT(CONCAT_WS(' ', p.fname, p.mname, p.lname),' [',DATE_FORMAT( e.updated_time, '%d.%m.%Y %H:%i:%s' ),']') as updated
				FROM {$db_name_finance}.export_to_email e
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = e.updated_user
				WHERE e.to_arc = 0
			";
			
			$this->getResult($sQuery, "id", DBAPI_SORT_ASC, $oResponse);
			
			foreach ( $oResponse->oResult->aData as $key => &$aRow ) {			
				$oResponse->setDataAttributes( $key, 'updated', array("style" => "width: 20%; text-align: left;", "nowrap" => "nowrap") );		
			}
						
			$oResponse->setField("email", 	"Имейл", 				"Сортирай по email");
			$oResponse->setField("updated",	"Последно редактирал",	"Сортирай по последно редактирал");
			$oResponse->setField("", 		"", 					"",	"images/cancel.gif", "delEmail", "");
			$oResponse->setFieldLink("email",	"editEmail");
		}
		


		
		/**
		 * Функцията връща всички типове приход
		 * 
		 * @author Павел Петров
		 * @name getEarnings
		 * @return array масив с типовеte
		 */
		public function getEarnings() {
			global $db_name_finance;
			
			$sQuery = "
				SELECT 
					id, 
					code,
					name
				FROM {$db_name_finance}.nomenclatures_earnings
				WHERE to_arc = 0 	
			";
			
			return $this->select($sQuery);
		}	

		
		/**
		 * Функцията връща всички типове разход
		 * 
		 * @author Павел Петров
		 * @name getExpenses
		 * @return array масив с типовеte
		 */
		public function getExpenses() {
			global $db_name_finance;
			
			$sQuery = "
				SELECT 
					id, 
					code,
					name
				FROM {$db_name_finance}.nomenclatures_expenses
				WHERE to_arc = 0 	
			";
			
			return $this->select($sQuery);
		}
	}

?>
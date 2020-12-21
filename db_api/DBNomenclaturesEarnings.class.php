<?php
	class DBNomenclaturesEarnings extends DBBase2 {
		
		public function __construct() {
			global $db_finance;
			parent::__construct($db_finance,'nomenclatures_earnings');
		}
		
		public function getReport(DBResponse $oResponse) {
			
			global $db_name_personnel,$db_finance_backup;
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					 ne.id,
					 ne.code,
					 ne.name,
					 ne.is_system,
					 CONCAT(CONCAT_WS(' ',p.fname,p.mname,p.lname),' [',DATE_FORMAT( ne.updated_time, '%d.%m.%Y %H:%i:%s' ),']') as updated
				FROM nomenclatures_earnings ne
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = ne.updated_user
				WHERE ne.to_arc = 0
			";
			
			$this->getResult($sQuery,'id',DBAPI_SORT_ASC,$oResponse,$db_finance_backup);
			
			$oResponse->setField('code','Код','Сортирай по код');
			$oResponse->setField('name','Име','Сортирай по име');
			$oResponse->setField('is_system','Системен','Сортирай по системен','images/confirm.gif');
			$oResponse->setField('updated','Последно редактирал','Сортирай по последно редактирал');
			$oResponse->setField('','','','images/cancel.gif','delNomenclatureEarning','');
			$oResponse->setFieldLink('name','openNomenclatureEarning');
		}
		
		public function getAllWithCode()
		{
			$sQuery = "
				SELECT
					id,
					CONCAT( '[', code, ']', ' ', name ) AS name
				FROM
					nomenclatures_earnings
				WHERE
					to_arc = 0
				ORDER BY code
			";
			
			return $this->select( $sQuery );
		}
		
		public function getAllAssoc() {
			
			$sQuery = "
				SELECT
					id,
					name
				FROM nomenclatures_earnings
				WHERE to_arc = 0
			";
			
			return $this->selectAssoc($sQuery);
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
		
		public function getNomenclaturesEarnings( $flag = 0 ) {
			global $db_name_finance;
			
			$sQuery = "
				SELECT 
					id,
					IF ( {$flag}, CONCAT('[++] ', code, ' ', name), name ) as name
				FROM {$db_name_finance}.nomenclatures_earnings
				WHERE to_arc = 0
				ORDER BY code
			";
			
			return $this->selectAssoc($sQuery);
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
		
		/**
		 * Функцията връща всички типове приходни номенклатури по групи
		 * 
		 * @author Павел Петров
		 * @name getGroupEarnings
		 * @return array масив с типовеte
		 */
		public function getGroupEarnings() {
			global $db_name_finance;
			
			$sQuery = "
				SELECT 
					ne.id, 
					ne.code,
					ne.name,
					ne.id_group,
					ng.name as group_name
				FROM {$db_name_finance}.nomenclatures_earnings ne
				LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ( ng.id = ne.id_group AND ne.id_group > 0 )
				WHERE ne.to_arc = 0 	
			";
			
			return $this->select($sQuery);
		}	
		
		/**
		 * Функцията връща всички типове разходни номенклатури по групи
		 * 
		 * @author Павел Петров
		 * @name getGroupExpenses()
		 * 
		 * @return array масив с типовеte
		 */
		public function getGroupExpenses() {
			global $db_name_finance;
			
			$sQuery = "
				SELECT 
					ne.id, 
					ne.code,
					ne.name,
					ne.id_group,
					ng.name as group_name
				FROM {$db_name_finance}.nomenclatures_expenses ne
				LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ( ng.id = ne.id_group AND ne.id_group > 0 )
				WHERE ne.to_arc = 0 	
			";
			
			return $this->select($sQuery);
		}			

		public function getByIDService($nIDService) {
			global $db_name_finance;
			
			$sQuery = "
				SELECT 
					ne.* 
				FROM {$db_name_finance}.nomenclatures_services ns
				LEFT JOIN {$db_name_finance}.nomenclatures_earnings ne ON (ne.id = ns.id_nomenclature_earning)
				WHERE ns.id = $nIDService
				LIMIT 1
			";
			
			return $this->select($sQuery);
		}
	}

?>
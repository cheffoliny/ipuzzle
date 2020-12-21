<?php
	class DBNomenclaturesExpenses extends DBBase2 {
		
		public function __construct() {
			global $db_finance;
			parent::__construct($db_finance,'nomenclatures_expenses');
		}
		
		public function getReport(DBResponse $oResponse) {
			
			global $db_name_personnel,$db_finance_backup;
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					 ne.id,
					 ne.code,
					 ne.name,
					 ne.for_salary,
					 ne.for_gsm,
					 ne.for_dds,
					 ne.for_transfer,
					 CONCAT(CONCAT_WS(' ',p.fname,p.mname,p.lname),' [',DATE_FORMAT( ne.updated_time, '%d.%m.%Y %H:%i:%s' ),']') as updated
				FROM nomenclatures_expenses ne
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = ne.updated_user
				WHERE ne.to_arc = 0
			";
			
			$this->getResult($sQuery,'id',DBAPI_SORT_ASC,$oResponse,$db_finance_backup);
			
			$oResponse->setField('code','Код','Сортирай по код');
			$oResponse->setField('name','Име','Сортирай по име');
			$oResponse->setField('for_salary','За заплати','Сортирай по за заплати','images/confirm.gif',NULL,NULL,array('DATA_FORMAT' => DF_CENTER));
			$oResponse->setField('for_gsm','За gsm','Сортирай по за gsm','images/confirm.gif',NULL,NULL,array('DATA_FORMAT' => DF_CENTER));
			$oResponse->setField('for_dds','За ДДС','Сортирай по ДДС','images/confirm.gif',NULL,NULL,array('DATA_FORMAT' => DF_CENTER));
			$oResponse->setField('for_transfer','ТРАНСФЕР','Сортирай по ТРАНСФЕР','images/confirm.gif',NULL,NULL,array('DATA_FORMAT' => DF_CENTER));
			$oResponse->setField('updated','Последно редактирал','Сортирай по последно редактирал');
			$oResponse->setField('','','','images/cancel.gif','delNomenclatureExpense','');
			$oResponse->setFieldLink('name','openNomenclatureExpense');
		}
		
		public function resetField($sFieldName) {
			
			$sQuery = "
				UPDATE
					nomenclatures_expenses
				SET $sFieldName = 0
			";
			
			$this->oDB->Execute($sQuery);
		}
		
		public function getIDSalaryNomenclature() {
			$sQuery = "
				SELECT
					id
				FROM nomenclatures_expenses 
				WHERE to_arc = 0 AND for_salary = 1
				LIMIT 1
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getIDGSMNomenclature() {
			$sQuery = "
				SELECT
					id
				FROM nomenclatures_expenses 
				WHERE to_arc = 0 AND for_gsm = 1
				LIMIT 1
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getIDDDSNomenclature() {
			$sQuery = "
				SELECT
					id
				FROM nomenclatures_expenses 
				WHERE to_arc = 0 AND for_dds = 1
				LIMIT 1
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getNomenclaturesExpenses( $flag = 0 ) {
			$sQuery = "
				SELECT 
					id,
					IF ( {$flag}, CONCAT('[---] ', code, ' ', name), name ) as name
				FROM nomenclatures_expenses
				WHERE to_arc = 0
				ORDER BY code
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getAllWithCode()
		{
			$sQuery = "
				SELECT
					id,
					CONCAT( '[',code, ']', ' ', name ) AS name
				FROM
					nomenclatures_expenses
				WHERE
					to_arc = 0
				ORDER BY code
			";
			
			return $this->select( $sQuery );
		}
		
		public function getAllRecords()
		{
			$sQuery = "
				SELECT 
					id,
					name
				FROM nomenclatures_expenses
				WHERE
					to_arc = 0
				ORDER BY name
			";
			
			return $this->select( $sQuery );
		}
		
		public function checkForTransfer($nIDExpense) {
			global $db_name_finance;
			
			if ( empty($nIDExpense) || !is_numeric($nIDExpense) ) {
				return false;
			}
	
			$sQuery = "
				SELECT 
					for_transfer
				FROM {$dn_name_finance}.nomenclatures_expenses
				WHERE id = {$nIDExpense}
			";
			
			return $this->selectOne( $sQuery );
		}
		
		/**
		 * Функцията връща всички типове номенклатури по групи
		 * 
		 * @author Павел Петров
		 * @name getGroupExpenses
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
	}
?>
<?php
	class DBNomenclaturesEarexpFirms extends DBBase2 {
		
		public function __construct() {
			global $db_finance;
			parent::__construct($db_finance,'nomenclatures_earexp_firms');
		}
		
		public function getReport($nIDFirm,DBResponse $oResponse) {
			
			global $db_finance_backup;
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					nef.id,
					if(nef.nomenclature_type = 'earning',ne.name,nx.name) AS name,
					if(nef.nomenclature_type = 'earning','приход','разход') AS nomenclature_type
				FROM nomenclatures_earexp_firms nef
				LEFT JOIN nomenclatures_earnings ne ON ne.id = nef.id_nomenclature_earexp
				LEFT JOIN nomenclatures_expenses nx ON nx.id = nef.id_nomenclature_earexp
				WHERE nef.id_firm = {$nIDFirm}
			";
			
			$this->getResult($sQuery,'id',DBAPI_SORT_ASC,$oResponse,$db_finance_backup);

			$oResponse->setField('name','Номенклатура приход/разход','Сортирай по номенклатура приход/разход');
			$oResponse->setField('nomenclature_type','Тип','Сортирай по тип');
		}
		
		public function getEarningsByIDFirmAssoc($nIDFirm) {
			$sQuery = "
				SELECT 
					nef.id,
					nef.id_nomenclature_earexp
				FROM nomenclatures_earexp_firms nef
				LEFT JOIN nomenclatures_earnings ne ON ne.id = nef.id_nomenclature_earexp
				WHERE ne.to_arc = 0
					AND nef.nomenclature_type = 'earning'
					AND nef.id_firm = {$nIDFirm}
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getExpensesByIDFirmAssoc($nIDFirm) {
			$sQuery = "
				SELECT 
					nef.id,
					nef.id_nomenclature_earexp
				FROM nomenclatures_earexp_firms nef
				LEFT JOIN nomenclatures_expenses nx ON nx.id = nef.id_nomenclature_earexp
				WHERE nx.to_arc = 0
					AND nef.nomenclature_type = 'expense'
					AND nef.id_firm = {$nIDFirm}
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getExpensesNamesByIDFirmAssoc($nIDFirm) {
			$sQuery = "
				SELECT 
					nx.id,
					nx.name,
					nx.code
				FROM nomenclatures_earexp_firms nef
				LEFT JOIN nomenclatures_expenses nx ON nx.id = nef.id_nomenclature_earexp
				WHERE nx.to_arc = 0
					AND nef.nomenclature_type = 'expense'
					AND nef.id_firm = {$nIDFirm}
				ORDER BY nx.code
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function delByIDFirm($nIDFirm) {
			$sQuery = "
				DELETE
				FROM nomenclatures_earexp_firms
				WHERE id_firm = {$nIDFirm}
			";
			
			$this->oDB->Execute($sQuery);
		}
	}
?>
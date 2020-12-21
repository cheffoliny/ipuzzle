<?php
	class DBNomenclaturesServicesFirms extends DBBase2 {
		
		public function __construct() {
			global $db_finance;
			parent::__construct($db_finance,'nomenclatures_services_firms');
		}
		
		public function getReport($nIDFirm,DBResponse $oResponse) {
			
			global $db_finance_backup;
			
			$sQuery = "
				SELECT
					nsf.id,
					CONCAT('[',ns.code,'] ',ns.name) as name
				FROM nomenclatures_services_firms nsf
				LEFT JOIN nomenclatures_services ns ON ns.id = nsf.id_nomenclature_service
				WHERE ns.to_arc = 0
					AND nsf.id_firm = {$nIDFirm}
			";
			
			$this->getResult($sQuery,'id',DBAPI_SORT_ASC,$oResponse,$db_finance_backup);

			$oResponse->setField('name','Номенклатура услуга','Сортирай по номенклатура услуга' );
		}
		
		public function getItByIDFirm($nIDFirm) {
			
			$sQuery = "
				SELECT 
					nsf.id,
					nsf.id_nomenclature_service
				FROM nomenclatures_services_firms nsf
				LEFT JOIN nomenclatures_services ns ON ns.id = nsf.id_nomenclature_service
				WHERE ns.to_arc = 0
					AND nsf.id_firm = {$nIDFirm}
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function delByIDFirm($nIDFirm) {
			$sQuery = "
				DELETE
				FROM nomenclatures_services_firms
				WHERE id_firm = {$nIDFirm}
			";
			
			$this->oDB->Execute($sQuery);
		}
	}
?>
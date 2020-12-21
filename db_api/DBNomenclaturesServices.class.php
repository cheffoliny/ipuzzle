<?php
	class DBNomenclaturesServices extends DBBase2 {
		
		public function __construct() {
			global $db_finance;
			parent::__construct($db_finance,'nomenclatures_services');
		}
		
		public function getReport(DBResponse $oResponse) {
			
			global $db_name_personnel, $db_name_storage,$db_finance_backup;
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					ns.id,
					ns.code,
					ns.name,
					ns.name_edit,
					ns.quantity_edit,
					ns.price,
					ns.price_edit,
					ns.for_transfer,
					m.description AS measure,
					ne.name AS nomenclature_earning,
					IF(is_month,'месечна','еднократна') AS service_type,
					 CONCAT(CONCAT_WS(' ',p.fname,p.mname,p.lname),' [',DATE_FORMAT( ns.updated_time, '%d.%m.%Y %H:%i:%s' ),']') as updated
				FROM nomenclatures_services ns
				LEFT JOIN nomenclatures_earnings ne ON ne.id = ns.id_nomenclature_earning
				LEFT JOIN {$db_name_storage}.measures m ON m.id = ns.id_measure
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = ns.updated_user
				WHERE ns.to_arc = 0
			";
			
			$this->getResult($sQuery,'id',DBAPI_SORT_ASC,$oResponse,$db_finance_backup);
			
			$oResponse->setField('code','Код','Сортирай по код на услугата');
			$oResponse->setField('name','Име','Сортирай по име на услугата');
			$oResponse->setField('name_edit','Име-ред.','Сортирай по име-редактиране','images/confirm.gif');
			$oResponse->setField('quantity_edit','Количество-ред.','Сортирай по количество-редакция','images/confirm.gif');
			$oResponse->setField('for_transfer', 'ТРАНСФЕР', 'Сортирай по ТРАНСФЕР', 'images/confirm.gif');
			$oResponse->setField('price','Цена','Сортирай по цена',null,null,null,array('DATA_FORMAT'=>DF_CURRENCY));
			$oResponse->setField('price_edit','Цена-ред.','Сортирай по цена-редактиране','images/confirm.gif');
			$oResponse->setField('measure','Мярка','Сортирай по мярка');
			$oResponse->setField('nomenclature_earning','Номенклатура приход','Сортирай по номенклатура приход');
			$oResponse->setField('service_type','Тип','Сортирай по тип');
			$oResponse->setField('updated','Последно редактирал','Сортирай по последно редактирал');
			$oResponse->setField('','','','images/cancel.gif','delService','');
			$oResponse->setFieldLink('name','setService');
			
		}
		
		public function getIt( $nIDFirm )
		{
			$sQuery = "
				SELECT
					ns.id,
					ns.code,
					ns.name
				FROM
					nomenclatures_earexp_firms nef
				LEFT JOIN
					nomenclatures_earnings ne ON ( ne.id = nef.id_nomenclature_earexp AND nef.nomenclature_type = 'earning' )
				LEFT JOIN
					nomenclatures_services ns ON ns.id_nomenclature_earning = ne.id
				WHERE
					ne.to_arc = 0
					AND ns.to_arc = 0
					AND nef.id_firm = {$nIDFirm}
			";
			
			return $this->select($sQuery);
		}
		

		// pavel - vzima def. stoinosti
		public function getDefault( $month ) {
			if ( !empty($month) ) {
				$month = 1;
			} else $month = 0;
			
			$sQuery = "
				SELECT
					ns.id,
					ns.code,
					ns.name,
					ne.name AS nomenclature_earning
				FROM nomenclatures_services ns
				LEFT JOIN nomenclatures_earnings ne ON ne.id = ns.id_nomenclature_earning
				WHERE 
					ns.to_arc = 0
					AND ns.is_month = {$month}
					AND ns.is_default = 1
				LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}

		public function checkForTransfer($nIDService) {
			global $db_name_finance;
			
			if ( empty($nIDService) || !is_numeric($nIDService) ) {
				return false;
			}
	
			$sQuery = "
				SELECT 
					for_transfer
				FROM {$dn_name_finance}.nomenclatures_services
				WHERE id = {$nIDService}
			";
			
			return $this->selectOne( $sQuery );
		}			
	}

?>
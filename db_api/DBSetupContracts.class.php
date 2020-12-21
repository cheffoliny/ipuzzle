<?php
	class DBSetupContracts extends DBBase2 {
		
		public function __construct() {
			global $db_finance;
			parent::__construct( $db_finance, "contracts_services_default_settings" );
		}
		
		public function getReport( DBResponse $oResponse ) {
			
			global $db_name_personnel, $db_finance_backup, $db_name_finance;
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					csds.id,
					csds.service_type as code,
					csds.service_name as name,
					ns.name as nomenclature_service,
					IF ( csds.is_single, 'еднократна', 'месечна' ) AS service_type,
					CONCAT( CONCAT_WS(' ', p.fname, p.mname, p.lname), ' [', DATE_FORMAT( csds.updated_time, '%d.%m.%Y %H:%i:%s' ), ']' ) as updated_user
				FROM {$db_name_finance}.contracts_services_default_settings csds
				LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ns.id = csds.id_nomenclatures_service
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = csds.updated_user
				WHERE 1
			";
			
			$this->getResult( $sQuery, "service_type", DBAPI_SORT_ASC, $oResponse, $db_finance_backup );
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$oResponse->setDataAttributes( $key, "code", 		 array("style" => "text-align: right; width: 120px;") );
				$oResponse->setDataAttributes( $key, "updated_user", array("style" => "text-align: center; width: 30px;") );
			}	
							
			$oResponse->setField( "code", 			"Код", "Сортирай по код на услугата" );
			$oResponse->setField( "name", 			"Име", "Сортирай по име на услугата" );
			$oResponse->setField( "nomenclature_service", "Услуга", "Сортирай по име на услугата" );
			$oResponse->setField( "service_type", 	"Тип", "Сортирай по тип" );
			$oResponse->setField( "updated_user", 	"...", "Сортиране по последно редактирал", "images/dots.gif" );
			$oResponse->setField( "", 				"", "", "images/cancel.gif", "delContract", "");
			
			$oResponse->setFieldLink( "code", "openContract" );
			$oResponse->setFieldLink( "name", "openContract" );
			$oResponse->setFieldLink( "nomenclature_service", "openContract" );
			
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
	}

?>
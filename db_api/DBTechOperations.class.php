<?php

	class DBTechOperations extends DBBase2 {
		
		public function __construct() {
			global $db_sod;
			parent::__construct($db_sod,'tech_operations');
			
		}
		
		public function getOperations() {
			
			$sQuery = "
				SELECT
					id,
					name
				FROM tech_operations
				WHERE to_arc = 0
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getOperationsForContract() {
			
			$sQuery = "
				SELECT 
					id AS id_operation,
					cable_operation
				FROM tech_operations
				WHERE 1
					AND to_arc = 0
					AND to_contract = 1
			";
			
			return $this->select($sQuery);
		}
		
		public function getOperationsForArrange() {
			
			$sQuery = "
				SELECT 
					id,
					name
				FROM tech_operations
				WHERE 1
					AND to_arc = 0
					AND to_arrange = 1
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getReport( DBResponse $oResponse )
		{
			global $db_name_personnel, $db_name_storage;
			
			$sQuery = "
					SELECT
						nom.id,
						nom.name AS nomenclature
					FROM {$db_name_storage}.schemes sch
						LEFT JOIN {$db_name_storage}.scheme_elements sce ON sce.id_scheme = sch.id
						LEFT JOIN {$db_name_storage}.nomenclatures nom ON nom.id = sce.id_nomenclature
					WHERE 1
						AND sce.to_arc = 0
						AND sch.to_arc = 0
						AND sch.id_detector != 0
						AND nom.id IN ( SELECT t.id_nomenclature
										FROM tech_operations_nomenclatures t
											LEFT JOIN tech_operations tp ON tp.id = t.id_operation
										WHERE t.to_arc = 0
											AND tp.to_arc = 0
											AND tp.to_contract = 1 )
			";
			
			$this->getResult( $sQuery, 'nomenclature', DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField( "nomenclature", 	"Номенклатура", 		"Сортирай по номенклатура" );
			$oResponse->setField( "operations", 	"Операции", 			"Сортирай по операции" );
			$oResponse->setField( "price", 			"Цена труд", 			"Сортирай по цена труд" );
			
			$aFinalData = $oResponse->oResult->aData;
			$nPriceClear = 0;
			
			foreach( $oResponse->oResult->aData as $key => $value )
			{
				$sQuery = "
						SELECT
							t.name,
							t.price
						FROM tech_operations_nomenclatures tn
							LEFT JOIN tech_operations t ON t.id = tn.id_operation
							LEFT JOIN {$db_name_storage}.nomenclatures n ON n.id = tn.id_nomenclature
						WHERE 1
							AND t.to_arc = 0
							AND t.to_contract = 1
							AND n.id = {$value['id']}
				";
				
				$aOperations = $this->select( $sQuery );
				
				$nPricePartial = 0;
				foreach( $aOperations as $aOperation )
				{
					$nPricePartial += $aOperation['price'];
					if( !isset( $aFinalData[$key]['operations'] ) )$aFinalData[$key]['operations'] = '';
					$aFinalData[$key]['operations'] .= "{$aOperation['name']}; ";
				}
				
				$aFinalData[$key]['price'] = "{$nPricePartial} лв.";
				$nPriceClear += $nPricePartial;
			}
			
			$oResponse->addTotal( 'price', $this->mround( $nPriceClear ) . " лв." );
			
			$oResponse->setData( $aFinalData );
		}

		public function getReport2( DBResponse $oResponse )
		{
			global $db_name_personnel, $db_name_storage;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
				if( in_array( 'tech_operations_edit', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			
			$sQuery = "
					SELECT
						t.id,
						t.name,
						t.to_contract,
						t.to_arrange,
						t.cable_operation,
						CONCAT( t.price, ' лв.' ) AS price,
						CONCAT(
							CONCAT_WS( ' ', up.fname, up.mname, up.lname ),
							' [',
							DATE_FORMAT( t.updated_time, '%d.%m.%Y %H:%i:%s' ),
							']'
						) AS updated_user
					FROM tech_operations t
						LEFT JOIN personnel.personnel up ON t.updated_user = up.id
					WHERE 1
						AND t.to_arc = 0
					ORDER BY name
			";
			
			$aResult = $this->select( $sQuery );
			
			$oResponse->setField( "name", 				"Наименование", 		"" );
			$oResponse->setField( "to_contract", 		"Ел. договор", 			"", "images/confirm.gif" );
			$oResponse->setField( "to_arrange", 		"При Аранжиране", 		"", "images/confirm.gif" );
			$oResponse->setField( "cable_operation", 	"Окабеляване", 			"", "images/confirm.gif" );
			$oResponse->setField( "price", 				"Цена труд", 			"" );
			$oResponse->setField( "nomenclatures", 		"Номенклатури", 		"" );
			$oResponse->setField( "updated_user", 		"Последна редакция", 	"" );
			
			$aFinalData = array();
			
			foreach( $aResult as $key => $value )
			{
				$sQuery = "
						SELECT
							n.name
						FROM tech_operations_nomenclatures tn
							LEFT JOIN tech_operations t ON t.id = tn.id_operation
							LEFT JOIN {$db_name_storage}.nomenclatures n ON n.id = tn.id_nomenclature
						WHERE 1
							AND t.to_arc = 0
							AND t.id = {$value['id']}
				";
				
				$aNomenclatures = $this->select( $sQuery );
				
				$aFinalData[$key]['id'] = 				$value['id'];
				$aFinalData[$key]['name'] = 			$value['name'];
				$aFinalData[$key]['to_contract'] = 		$value['to_contract'];
				$aFinalData[$key]['to_arrange'] = 		$value['to_arrange'];
				$aFinalData[$key]['cable_operation'] = 	$value['cable_operation'];
				$aFinalData[$key]['price'] = 			$value['price'];
				$aFinalData[$key]['updated_user'] = 	$value['updated_user'];
				
				foreach( $aNomenclatures as $aNomenclature )
				{
					if( !isset( $aFinalData[$key]['nomenclatures'] ) )$aFinalData[$key]['nomenclatures'] = '';
					$aFinalData[$key]['nomenclatures'] .= "{$aNomenclature['name']}; ";
				}
				
				if( empty( $aFinalData[$key]['nomenclatures'] ) )$aFinalData[$key]['nomenclatures'] = "Няма номенклатури!";
			}
			
			if( $right_edit )
			{
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'deleteOperation', '' );
				$oResponse->setFieldLink( "name", 	"editOperation" );
			}
			
			$oResponse->setData( $aFinalData );
		}
		
		function mround( $value )
		{
			return ceil( (string) ( $value * 100 ) ) / 100;
		}
	}

?>
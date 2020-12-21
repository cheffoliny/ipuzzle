<?php
	class DBSaldo extends DBBase2 {
		public function __construct() {
			global $db_finance;
			
			parent::__construct( $db_finance, 'saldo' );
		}		
		
		public function getReport( DBResponse $oResponse, $aParams ) {
			global $db_name_sod, $db_name_finance;
			
			$oOrders 	= new DBOrders();
			$oFirms		= new DBFirms();
			
			$aData		= array();
			$aFirms		= array();
			$aAccess	= array();
			$sAccess	= "";
			
			$nTimeFrom	= jsDateToTimestamp ($aParams['sFromDate']);

			$aData 		= $oOrders->getTotals($aParams);
			$aFirms		= $oFirms->getFirms();
			
			$aAccess[] = 0;
			
			foreach ( $aFirms as $key => $val ) {
				$aAccess[] = $key;
			}
			
			$sAccess 	= implode(",", $aAccess);

			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						s.id, 
						f.name AS name_firm,
						s.name AS name_balance,
						s.sum AS sum,
						s.is_dds AS is_dds
					FROM {$db_name_finance}.saldo s
					LEFT JOIN {$db_name_sod}.firms f ON f.id = s.id_firm
					WHERE f.to_arc = 0
						AND f.id IN ({$sAccess})
			";
			
			$this->getResult( $sQuery, 'name_firm', DBAPI_SORT_ASC, $oResponse );
			
			$nTotalMin = $nTotalMax = $nTotalChange = $nTotalSum = 0;
			
			foreach ( $oResponse->oResult->aData as $nKey => &$aValue ) {
				$min 	= 0;
				$max 	= 0;
				$nID	= $aValue['id'];
				$aSec	= array();
				
				if ( isset($aData[$nID]['min']) ) {
					$min = $aData[$nID]['min'];
					$max = $aData[$nID]['max'];
				} else {
					$aSec = $oOrders->getMomentSaldoBefore($nID, $nTimeFrom);
					
					if ( isset($aSec['sum']) ) {
						$min = $aSec['sum'];
						$max = $aSec['sum'];
					}
				}
				
				$aValue['min'] 		= $min;
				$aValue['max'] 		= $max;
				$aValue['change'] 	= $max - $min;
				
				$nTotalSum		+= $aValue['sum'];
				$nTotalMin 		+= $aValue['min'];
				$nTotalMax 		+= $aValue['max'];
				$nTotalChange 	+= $aValue['change'];
			}
			
			$oResponse->addTotal( "sum", 	$nTotalSum 		);
			$oResponse->addTotal( "min", 	$nTotalMin 		);
			$oResponse->addTotal( "max", 	$nTotalMax 		);
			$oResponse->addTotal( "change", $nTotalChange 	);
			
			$oResponse->setField("name_firm", 		"Фирма", 		"Сортирай по Фирма", 			NULL, NULL, NULL, array("DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField("name_balance", 	"Наименование", "Сортирай по Наименование", 	NULL, NULL, NULL, array("DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField("sum", 			"Текущо", 		"Сортирай по Текущо Салдо", 	NULL, NULL, NULL, array("DATA_FORMAT" => DF_CURRENCY ) );
			$oResponse->setField("min", 			"Начално", 		"Сортирай по Начално Салдо",	NULL, NULL, NULL, array("DATA_FORMAT" => DF_CURRENCY) );
			$oResponse->setField("max", 			"Крайно", 		"Сортирай по Крайно Салдо", 	NULL, NULL, NULL, array("DATA_FORMAT" => DF_CURRENCY) );	
			$oResponse->setField("change", 			"Промяна", 		"Сортирай по Салдо - Промяна", 	NULL, NULL, NULL, array("DATA_FORMAT" => DF_CURRENCY) );		
			$oResponse->setField("is_dds", 			"ДДС", 			"Сортирай", "images/ok.png", 	  NULL, NULL, array("DATA_FORMAT" => DF_NUMBER ) );
		}
		
		/**
		 * Функцията проверява дали дадена фирма има в наличност въведената сума.
		 *
		 * @param int $nIDFirm
		 * @param float $nSum
		 * @return true || false
		 */
		public function checkFirmBalance( $nIDFirm, $nSum )
		{
			if( empty( $nIDFirm ) || !is_numeric( $nIDFirm ) )
			{
				return false;
			}
			
			$sQuery = "
				SELECT
					sum
				FROM
					saldo
				WHERE
					id_firm = {$nIDFirm}
					AND is_dds = 0
				LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( !empty( $aData ) )
			{
				return ( $aData['sum'] >= $nSum ) ? true : false;
			}
			else return false;
		}
		
		/**
		 * Функцията връща данните за сладото по зададена фирма
		 * 
		 * @author Павел Петров
		 * 
		 * @param int $nIDFirm - За коя фирма
		 * @param byte $is_dds - Салдо по ДДС
		 * 
		 * @return array - данните за салдото!
		 */
		public function getSaldoByFirm($nIDFirm, $is_dds = 0) {
			global $db_name_finance;
			
			if ( empty($nIDFirm) || !is_numeric($nIDFirm) ) {
				return array();
			}
			
			$is_dds = !empty($is_dds) ? 1 : 0;
			$aData	= array();
			
			$sQuery = "
				SELECT
					id,
					name,
					sum
				FROM {$db_name_finance}.saldo
				WHERE id_firm = {$nIDFirm}
					AND is_dds = {$is_dds}
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if ( !empty($aData) ) {
				return $aData;
			} else {
				return array();
			}
		}
	}

?>
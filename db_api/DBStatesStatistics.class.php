<?php

	class DBStatesStatistics extends DBBase2 {
		
		public function __construct() {
			
			
			global $db_storage;
			//$db_storage->debug = true;
			parent::__construct( $db_storage, 'states_statistics' );
		}
		
		public function getReport(DBResponse $oResponse,$aData) {
			
			$sQuery = $this->prepareStatesStatisticsQuery( 0, $aData, $oResponse );
			$sQueryTotal = $this->prepareStatesStatisticsQuery( 1, $aData, $oResponse );
			
			
			$this->getResult($sQuery,'id',DBAPI_SORT_ASC,$oResponse);
			
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$val['total_price'] = $val['total_price']." лв.";
				$sMetra = "";
				if(!empty($val['total_broi']))	$sMetra .= $val['total_broi']." бр.  ";
				if(!empty($val['total_metra']) && $val['total_metra'] != '0.00')	$sMetra .= $val['total_metra']." м.";
				$val['total_broi'] = $sMetra;
				
				$oResponse->setDataAttributes($key,'total_broi',array("style" => " text-align:right"));
			}

			
			$aTotals = $this->selectOnce($sQueryTotal);
			
			
			$sMetra = "";
			if(!empty($aTotals['total_broi']))	$sMetra .= $aTotals['total_broi']." бр.  ";
			if(!empty($aTotals['total_metra']) && $val['total_metra'] != '0.00')	$sMetra .= $aTotals['total_metra']." м.";	
			
			
			$oResponse->addTotal('total_broi',$sMetra);
			$oResponse->addTotal('total_price',$aTotals['total_price']);
			
		
			$oDBStatesFilters = new DBStatesFilters();
			$aFilter = $oDBStatesFilters->getRecord($aData['nIDFilter']);
			
			
			$oResponse->setField('date_statistic_','Дата','Сортирай по дата');
			
			if(!empty($aFilter['total_count'])) {
				$oResponse->setfield('total_broi','Количество','Сортирай по количество');
			}
			if(!empty($aFilter['total_price'])) {
				$oResponse->setField('total_price','Цена','Сортирай по цена',NULL,NULL,NULL,array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
			}
			
		}
		
		public function prepareStatesStatisticsQuery($nTotal,$aData,DBResponse $oResponse) {
			
			$nIDPerson	=	$aData['nIDPerson'];
			$nIDFilter	=	$aData['nIDFilter'];
			$nDateFrom	=	$aData['nDateFrom'];
			$nDateTo	=	$aData['nDateTo'];
			
			if(empty($nTotal)) {
			
				$sQuery = "
				
					SELECT SQL_CALC_FOUND_ROWS
						ss.id,
						sf.name,
						DATE_FORMAT( ss.date_statistic, '%d.%m.%Y %H:%i:%s' ) AS date_statistic_,
						ss.total_broi,
						ss.total_metra,
						ss.total_price
					FROM states_statistics ss
					LEFT JOIN states_filters sf ON sf.id = ss.id_filter
					WHERE 1
					 	AND ss.id_person = {$nIDPerson}
					 	AND ss.id_filter = {$nIDFilter}
				";
					
			} else {
				
				$sQuery = "
				
					SELECT 
						SUM(ss.total_broi) AS total_broi,
						SUM(ss.total_metra) AS total_metra,
						SUM(ss.total_price) AS total_price
					FROM states_statistics ss
					LEFT JOIN states_filters sf ON sf.id = ss.id_filter
					WHERE 1
					 	AND ss.id_person = {$nIDPerson}
					 	AND ss.id_filter = {$nIDFilter}
				";
				
			}
			
			
			if(!empty($nDateFrom)) {
				$sQuery .= " AND UNIX_TIMESTAMP(ss.date_statistic) > {$nDateFrom} \n";
			}
			
			if(!empty($nDateTo)) {
				$sQuery .= " AND UNIX_TIMESTAMP(ss.date_statistic) < {$nDateTo} \n";
			}
			
			return $sQuery;
		}
	}
	
?>
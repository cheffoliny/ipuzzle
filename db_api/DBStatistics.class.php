<?php

	class DBStatistics extends DBBase2 {
		
		public function __construct() {
			global $db_finance;
			
			parent::__construct($db_finance,'statistics');
		}
		
		public function getFiltersByIDPerson($nIDPerson) {
			
			global $db_name_system;
			
			$sQuery = "
				SELECT 
					s.id_filter,
					f.name
				FROM statistics s
				LEFT JOIN {$db_name_system}.filters f ON f.id = s.id_filter
				WHERE s.id_person = {$nIDPerson}
				GROUP BY s.id_filter
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getReport($oResponse,$aParams) {
			
			$nIDFilter = $aParams['nIDFilter'];
			$sDateFrom = isset($aParams['sFromDate']) ? $aParams['sFromDate'] : '';
			$sDateTo = isset($aParams['sToDate']) ? $aParams['sToDate'] : '';
			
			$nDateFrom = mysqlDateToTimestamp($sDateFrom);
			$nDateTo = mysqlDateToTimestamp($sDateTo);
			
			if(!empty($nDateTo)) {
				$nDateTo = mktime(0,0,0,date("m",$nDateTo),date("d",$nDateTo)+1,date("Y",$nDateTo));			
			}
			
			$sQuery = "
				SELECT
					id,
					DATE_FORMAT(date_statistic,'%d.%m.%Y %H:%i:%s') AS date_statistic_
				FROM statistics
				WHERE id_filter = {$nIDFilter} 
			";
			
			if(!empty($nDateFrom)) {
				$sQuery .= " AND UNIX_TIMESTAMP(date_statistic) >= {$nDateFrom} ";
			}
			
			if(!empty($nDateTo)) {
				$sQuery .= " AND UNIX_TIMESTAMP(date_statistic) <= {$nDateTo} ";
			}
			
			$this->getResult($sQuery,'id',DBAPI_SORT_DESC,$oResponse);
			
			$oDBStatisticsRows = new DBStatisticsRows();
			
			foreach ($oResponse->oResult->aData as $key => &$value) {
				
				$aStatisticsRows = $oDBStatisticsRows->getByIDStatistic($value['id']);
				
				foreach ($aStatisticsRows as $aStatisticRow) {
					$value[$aStatisticRow['total_name']] = $aStatisticRow['value'];
				}
			}
				
			$oResponse->setField('date_statistic_','дата','сортирай по дата на филтъра');
			
			foreach ($aStatisticsRows as $aStatisticRow) {
				$oResponse->setField($aStatisticRow['total_name'],$aStatisticRow['field_name'],null,null,null,null,array("DATA_FORMAT" => $aStatisticRow['data_format']));
			}
			
		}
	}

?>
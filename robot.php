<?php
$myObj->name = "John";
$myObj->age = 30;
$myObj->city = "New York";

$myJSON = json_encode($myObj);

echo $myJSON;
die();
/*
	Айзък Азимов - Закони на робитиката
	1. Роботът не може да причини вреда на човек или с бездействието си да допусне на човека да бъде причинена вреда.
	2. Роботът е длъжен да се подчинява на човека, ако това не противоречи на Първия закон.
	3. Роботът е длъжен да се грижи за собствената си безопасност, ако това не противоречи на Първия и Втория закон.
*/

	require_once ("config/function.autoload.php");
	require_once ("config/connect.inc.php");
	require_once ("include/general.inc.php");
	require_once ('db_api/include/db_include.inc.php');
	
	// СТАТИСТИКИ
	
	$oDBFilters = new DBFilters();
	$oDBStatistics = new DBStatistics();
	$oDBStatisticsRows = new DBStatisticsRows();
	
	$aAutoFilters = $oDBFilters->getAutoFilters();
	
	foreach ($aAutoFilters as &$aAutoFilter) {
		if(its_time($aAutoFilter['auto_robot_last_date'],$aAutoFilter['auto_period'])) {
			$oReportClass = new $aAutoFilter['report_class'];
			
			$aParams = array();
			$aParams['schemes'] = $aAutoFilter['id'];
			$aParams['robot'] = 1;
			
			$aTotals = $oReportClass->getReport($aParams);
			
			if(!empty($aTotals)) {
				
				$aStatistic = array();
				$aStatistic['id_person'] = $aAutoFilter['id_person'];
				$aStatistic['id_filter'] = $aAutoFilter['id'];
				$aStatistic['date_statistic'] = time();
				
				$oDBStatistics->update($aStatistic);
				
				foreach ($aTotals as $key => $value) {
					
					$aStatisticRow = array();
					$aStatisticRow['id_statistic'] = $aStatistic['id'];
					$aStatisticRow['total_name'] = $key;
					$aStatisticRow['field_name'] = $value['name'];
					$aStatisticRow['value'] = $value['value'];
					$aStatisticRow['data_format'] = $value['data_format'];
					
					$oDBStatisticsRows->update($aStatisticRow);
				}
			}
			
			$aAutoFilter['auto_robot_last_date'] = time();
			$oDBFilters->update($aAutoFilter);
		}
	}
	
	function its_time($sDateLast,$sPeriod) {
		
		$nDateLast = mysqlDateToTimestamp($sDateLast);

		if(empty($nDateLast))return 1;
		
		$nGonedTime = time() - $nDateLast;
		
		switch ($sPeriod) {
			case 'day': if($nGonedTime > ((24 * 60 * 60) - 5*60) ) return 1; break;
			case 'week': if($nGonedTime > ((7 * 24 * 60 * 60) - 5*60) ) return 1; break;
			case 'month': if(date("d") == date("d",$nDateLast) && date("m") != date("m",$nDateLast)) return 1; break;
		}
		return 0;
	}
	
	// анулиране на ЛИМИТНИ КАРТИ с планиран старт от преди два дена, но без реален старт

	$oDBTechLimitCards = new DBTechLimitCards();
	$oDBLimitCardPersons = new DBLimitCardPersons();
	
	$oDBTechLimitCards->cancel2DaysLimitCards();
	$aPersons = $oDBLimitCardPersons->getIDsOfClosedLimitCard();
	
	foreach ($aPersons as $value) {
		$oDBLimitCardPersons->delete($value['id']);
	}
	
	
?>
<?php
	# http://dev.mysql.com/doc/refman/5.0/en/show-table-status.html
	require_once("db_include.inc.php");
	
	
	class DBStatusTables 
	{
	
		/**
		 * Метод, който връща моментното състояние на таблица
		 *
		 * @param obj $oDB		- обект към база данни
		 * @param str $sTable	- име на таблицата
		 * @param arr $aStatus	- масив, в който се връща записа за статуса
		 * @author Boro: 31.08.2006 (
		 */
		function getStatus ($oDB, $sTable, &$aStatus) {
			
			$aStatus = array();
			
			if (!is_object($oDB) || !is_string($sTable))
				return DBAPI_ERR_INVALID_PARAM;
				
			$sQuery = sprintf ("SHOW TABLE STATUS LIKE '%s'", $sTable);
			
			$oRs = $oDB->Execute($sQuery);
			
			if (!$oRs)
				return DBAPI_ERR_SQL_QUERY;
			
			$aStatus = $oRs->fields;
					
			return DBAPI_ERR_SUCCESS;			
			
		}
		
		/**
		 * Метод, който връща масив със статус на последноактуализираната таблица
		 *
		 * @param obj $oDB			- обект към база данни
		 * @param arr $aTablesAssoc	- асоциативен масив от имена на таблици
		 * @param arr $aTable		- масив, в който се връща записа за статуса
		 * @author Boro: 31.08.2006 
		 */
		function getLastChangedTable ($oDB, $aTablesAssoc, &$aTable) {
			
			if (!is_object($oDB) || !is_array($aTablesAssoc))
				return DBAPI_ERR_INVALID_PARAM;

			$aStatusTable = array();	
			
			foreach ($aTablesAssoc AS $value) 
				if (($nResult = $this->getStatus($oDB, $value, $aStatusTable[$value]))!=DBAPI_ERR_SUCCESS)
					return $nResult;
			
			$nMin = -1;		
			foreach ($aStatusTable AS $key=>$value) 
				if (strtotime($value['Update_time']) > $nMin)
					$aTable = $aStatusTable[$key];
				
			return DBAPI_ERR_SUCCESS;			
		}
	}
?>
<?php
	class DBBudget extends DBBase2 {
		
		public function __construct() {
			global $db_finance;
			
			parent::__construct($db_finance, "budget");
		}

		/**
		 * Функцията връща детайлна информация за приходната или 
		 * разходната част на архивен бюджет по зададен месец;
		 * 
		 * @author Павел Петров
		 * @name getBudgetByMonth()
		 *
		 * @param (string) $sMonth	- Месец, (YYYY-MM) за който ще се търси;
		 * @param (string) $type	- (earning, expense) - приход или разход;
		 * 
		 * @return (array) - данните от тъсенето. Ако няма намерени - ще върне
		 * празен масив;
		 */
		public function getBudgetByMonth( $sMonth, $type, $nIDFirm = 0, $nIDOffice = 0 ) {
			global $db_name_finance, $db_name_sod;
			
			if ( empty($sMonth) ) {
				return array();
			} 
			
			if ( ($type == "earning") || ($type == "expense") ) {
				// do nothing...
			} else {
				return array();
			}
			
			$sQuery = "
				SELECT 
					br.id_office,
					br.id_nomenclature,
					ne.name as nomenclature_name,
					ne.id_group,
					ng.name as group_name,
					br.sum
				FROM {$db_name_finance}.budget b
				LEFT JOIN {$db_name_finance}.budget_rows br ON ( b.id = br.id_budget AND br.to_arc = 0 )
				LEFT JOIN {$db_name_sod}.offices o ON ( o.id = br.id_office )
			";
			
			if ( $type == "earning" ) {
				$sQuery .= "
					LEFT JOIN {$db_name_finance}.nomenclatures_earnings ne ON ne.id = br.id_nomenclature
					LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ng.id = ne.id_group
				";
			} else {
				$sQuery .= "
					LEFT JOIN {$db_name_finance}.nomenclatures_expenses ne ON ne.id = br.id_nomenclature
					LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ng.id = ne.id_group
				";				
			}
			
			$sQuery .= "
				WHERE DATE_FORMAT(b.month, '%Y-%m') = '{$sMonth}'
					AND br.type = '{$type}'
			";
			
			if ( !empty($nIDOffice) ) {
				$sQuery .= " AND br.id_office = {$nIDOffice} ";
			} elseif ( !empty($nIDFirm) ) {
				$sQuery .= " AND o.id_firm = {$nIDFirm} ";
			}
			
			return $this->select($sQuery);
		}
		
		public function deleteBudget( $nIDBudget ) {
			global $db_name_finance;
			
			if ( empty($nIDBudget) || !is_numeric($nIDBudget) ) {
				return 0;
			} 
			
			$sQuery = "DELETE FROM {$db_name_finance}.budget WHERE id = {$nIDBudget} ";
			$this->select($sQuery);
			
			$sQuery = "DELETE FROM {$db_name_finance}.budget_rows WHERE id_budget = {$nIDBudget} ";
			$this->select($sQuery);			
			
			return $nIDBudget;
		}
		
		/**
		 * Функцията връща детайлна информация за приходната или 
		 * разходната част на архивен бюджет по зададенo ID;
		 * 
		 * @author Павел Петров
		 * @name getBudgetByID()
		 *
		 * @param (integer) $nIDBudget	- ID на бюджета;
		 * @param (string) 	$type		- (earning, expense) - приход или разход;
		 * 
		 * @return (array) - данните от тъсенето. Ако няма намерени - ще върне
		 * празен масив;
		 */
		public function getBudgetByID( $nIDBudget, $type = "" ) {
			global $db_name_finance, $db_name_sod;
			
			if ( empty($nIDBudget) || !is_numeric($nIDBudget) ) {
				return array();
			} 
			
			if ( ($type == "earning") || ($type == "expense") ) {
				// do nothing...
			} else {
				return array();
			}
			
			$sQuery = "
				SELECT 
					b.month,
					br.id_office,
					br.id_nomenclature,
					ne.name as nomenclature_name,
					ne.id_group,
					ng.name as group_name,
					br.sum
				FROM {$db_name_finance}.budget b
				LEFT JOIN {$db_name_finance}.budget_rows br ON ( b.id = br.id_budget AND br.to_arc = 0 )
				LEFT JOIN {$db_name_sod}.offices o ON ( o.id = br.id_office )
			";
			
			if ( $type == "earning" ) {
				$sQuery .= "
					LEFT JOIN {$db_name_finance}.nomenclatures_earnings ne ON ne.id = br.id_nomenclature
					LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ng.id = ne.id_group
				";
			} else {
				$sQuery .= "
					LEFT JOIN {$db_name_finance}.nomenclatures_expenses ne ON ne.id = br.id_nomenclature
					LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ng.id = ne.id_group
				";				
			}
			
			$sQuery .= "
				WHERE b.id = {$nIDBudget}
					AND br.type = '{$type}'
			";

			return $this->select($sQuery);
		}		
		
		public function checkMonth( $sMonth ) {
			global $db_name_finance;
			
			$nID = 0;
			
			if ( empty($sMonth) ) {
				return 0;
			} 

			$sQuery = "
				SELECT
					id
				FROM {$db_name_finance}.budget
				WHERE DATE_FORMAT(month, '%Y-%m') = '{$sMonth}'
			";	
			
			$nID = $this->selectOne($sQuery);
			
			if ( !empty($nID) ) {
				return $nID;
			} else {
				return 0;
			}
		}
		
		public function getReport( DBResponse $oResponse ) {
			global $db_finance, $db_sod, $db_name_finance, $db_name_sod;
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					b.id as id,
					UNIX_TIMESTAMP(b.month) as month,
					SUM(IF(br.type = 'earning', br.sum, 0)) as earning,
					SUM(IF(br.type = 'expense', br.sum, 0)) as expense,
					SUM(IF(br.type = 'earning', br.sum, br.sum * -1)) as price						
				FROM {$db_name_finance}.budget b
				LEFT JOIN {$db_name_finance}.budget_rows br ON br.id_budget = b.id
				WHERE b.to_arc = 0
				GROUP BY b.id
			";
			
			$this->getResult( $sQuery, "month", DBAPI_SORT_DESC, $oResponse );
			
			$nRowTotal = $oResponse->oResult->oPaging->nRowTotal;
			
			$mname		 = array();
			$mname['01'] = "Януари";
			$mname['02'] = "Февруари";
			$mname['03'] = "Март";
			$mname['04'] = "Април";
			$mname['05'] = "Май";
			$mname['06'] = "Юни";
			$mname['07'] = "Юли";
			$mname['08'] = "Август";
			$mname['09'] = "Септември";
			$mname['10'] = "Октомври";
			$mname['11'] = "Ноември";
			$mname['12'] = "Декември";			
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$val['month'] 	= $mname[date("m", $val['month'])]." ".date("Y", $val['month']);
				$val['earning']	= sprintf("%01.2f лв.", $val['earning']);
				$val['expense']	= sprintf("%01.2f лв.", $val['expense']);
				$val['price']	= sprintf("%01.2f лв.", $val['price']);
				$val['space'] 	= " ";
				
 				$oResponse->setDataAttributes( $key, "month", 		array("style" => "text-align: left; width: 155px;") );
				$oResponse->setDataAttributes( $key, "earning", 	array("style" => "text-align: right; width: 105px;") );
				$oResponse->setDataAttributes( $key, "expense", 	array("style" => "text-align: right; width: 105px;") );
				$oResponse->setDataAttributes( $key, "price", 		array("style" => "text-align: right; width: 105px;") );
				$oResponse->setDataAttributes( $key, "space", 		array("style" => "width: 50%;") );
			}			
			
			$oResponse->setField("month", 		"За месец", 	"Сортирай по Месец");
			$oResponse->setField("earning", 	"Приходи", 		"Сортирай по Приходи");
			$oResponse->setField("expense",		"Разходи.", 	"Сортирай по Разходи");
			$oResponse->setField("price", 		"Резултат",	 	"Сортирай по Резултат");
			$oResponse->setField("space", 		"",	 			NULL);
			$oResponse->setField("", "", "", 	"images/cancel.gif", "deleteBudget", "");
			
			$oResponse->setFieldLink("month", 	"openBudget");
			$oResponse->setFieldLink("earning", "openBudget");			
			$oResponse->setFieldLink("expense", "openBudget");	
			$oResponse->setFieldLink("price", 	"openBudget");			
		}			
	}
?>
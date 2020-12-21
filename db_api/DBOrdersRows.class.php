<?php

	require_once('include/db_include.inc.php');

	class DBOrdersRows extends DBMonthTable {
		
		function __construct() {
			global $db_name_finance, $db_finance;
			
			parent::__construct( $db_name_finance, PREFIX_ORDERS_ROWS, $db_finance );
		}
		
	
		/**
		 * Функцията изтрива записи по ид на ордер
		 * 
		 * @author Павел Петров
		 * @name deleteByOrder
		 * 
		 * @param int $nIDOrder - ID на ордера
		 * 
		 * @return void
		 */			
		public function deleteByOrder( $nIDOrder ) {
			global $db_name_finance, $db_finance;
			
			if ( $this->isValidID($nIDOrder) ) {
				$sTableName = PREFIX_BUY_DOCS_ROWS.substr( $nIDOrder, 0, 6 );
			} else {
				throw new Exception( "Невалидно ID!", DBAPI_ERR_INVALID_PARAM );
			}	
			
			$sQuery = "DELETE FROM {$db_name_finance}.{$sTableName} WHERE id_order = {$nIDOrder} ";
			$db_finance->Execute($sQuery);
		}	

		
		/**
		 * Функцията взима всички записи по дадено ID на ордер
		 * 
		 * @author Павел Петров
		 * @name getByIDOrder
		 * 
		 * @param int $nIDOrder - ID на ордера
		 * 
		 * @return array - Списък с резултата
		 */			
		public function getByIDOrder( $nIDOrder ) {
			global $db_name_finance;
			
			$aData = array();
			
			if ( $this->isValidID($nIDOrder) ) {
				$sTableName = PREFIX_ORDERS_ROWS.substr($nIDOrder, 0, 6);
			} else {
				return array();
			}	
			
			$sQuery = "
				SELECT 
					*
				FROM {$db_name_finance}.{$sTableName}
				WHERE id_order = {$nIDOrder}
			";
			
			$aData = $this->select2($sQuery);

			return $aData;
		}	
		
		
		/**
		 * Функцията връща суми по тотал на базата приходна номенклатура
		 * Ползва се във флекс справката за Финанси/Постъпления
		 * 
		 * @author Павел Петров
		 * @name getEarningTotalsByNom
		 * 
		 * @param (string) $sPeriod - За кой МЕСЕЦ приходи;
		 * @param (int) $nIDFirm 	- ID на фирмата (незадължителен)
		 * @param (int) $nIDOffice 	- ID на офиса (незадължителен)
		 * @param (string) $sFilter	- списък с ID-та на номенклатури, които ще участват в търсенето
		 * 
		 * @return array - Списък с резултата групиран по номенклатури
		 */			
		public function getEarningTotalsByNom( $sPeriod, $nIDFirm = 0, $nIDOffice = 0, $sFilter = "" ) {
			global $db_finance, $db_name_finance, $db_name_sod;
			
			$aData 	= array();
			$sOfce	= "";
			$aPer	= explode("-", $sPeriod);
			$oOfce	= new DBOffices();
			$aTbls	= array();
			
			if ( isset($aPer[1]) ) {
				$ye = $aPer[0];
				$mo = $aPer[1];
				
				if ( checkdate($mo, 1, $ye) ) {
					$sTableName = PREFIX_ORDERS_ROWS.$ye.$mo;
					$sOrderName = PREFIX_ORDERS.$ye.$mo;
				} else {
					return array();
				}
			}
			
			$aTbls	= SQL_get_tables($db_finance, "orders_", "______", "ASC");
			
			if ( !in_array($sOrderName, $aTbls) ) {
				return array();
			}
			
			$sQuery = "
				SELECT 
					IF ( ow.is_dds > 0, 13, IF (na.id_group > 1, na.id_group, -3) ) as id,
					IF ( ow.is_dds > 0, 'Други', IF (na.id > 1, ng.name, 'Невъведена') ) as gname,
					IF ( ow.is_dds > 0, -1, IF (na.id > 1, na.id, -3) ) as id_nomenclature,
					SUM(ow.paid_sum) as price,
					IF ( ow.is_dds > 0, 'ДДС', IF (na.id > 1, na.name, 'Невъведена') ) as nomenclature,
					ow.is_dds
				FROM {$db_name_finance}.{$sTableName} ow
				LEFT JOIN {$db_name_finance}.{$sOrderName} o ON ( o.id = ow.id_order )
				LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ( ns.id = ow.id_service )
				LEFT JOIN {$db_name_finance}.nomenclatures_earnings na ON ( na.id = ns.id_nomenclature_earning )
				LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ( ng.id = na.id_group )
				WHERE o.doc_type = 'sale'		
			";
			
			if ( !empty($nIDOffice) ) {
				$sQuery .= " AND ow.id_office = '{$nIDOffice}' ";
			} elseif ( !empty($nIDFirm) ) {
				$sOfce	.= $oOfce->getIdsByFirm($nIDFirm);
				
				if ( !empty($sOfce) ) {
					$sQuery .= " AND ow.id_office IN ({$sOfce}) ";
				}
			}			
					
			$sQuery .= " GROUP BY ns.id_nomenclature_earning, ow.is_dds ";
			
			if ( !empty($sFilter) ) {
				$aTemp = array();
				$aTemp = explode(",", $sFilter);
				
				// ДДС
				if ( in_array(-1, $aTemp) ) {
					$sQuery .= " HAVING ( is_dds > 0 OR id_nomenclature IN ({$sFilter}) ) ";
				} else {
					$sQuery .= " HAVING id_nomenclature IN ({$sFilter}) ";
				}
			}			
			
			$aData 	= $this->select2($sQuery);

			return $aData;
		}	
		
		/**
		 * Функцията връща суми по тотал на базата разходна номенклатура
		 * Ползва се във флекс справката за Финанси/Постъпления
		 * 
		 * @author Павел Петров
		 * @name getExpenseTotalsByNom
		 * 
		 * @param (string) $sPeriod - За кой МЕСЕЦ приходи;
		 * @param (int) $nIDFirm 	- ID на фирмата (незадължителен)
		 * @param (int) $nIDOffice 	- ID на офиса (незадължителен)
		 * 
		 * @return array - Списък с резултата групиран по номенклатури
		 */			
		public function getExpenseTotalsByNom( $sPeriod, $nIDFirm = 0, $nIDOffice = 0, $sFilter = "" ) {
			global $db_name_finance, $db_finance;
			
			$aData 	= array();
			$aTbls	= array();
			$sOfce	= "";
			$aPer	= explode("-", $sPeriod);
			$oOfce	= new DBOffices();
			
			if ( isset($aPer[1]) ) {
				$ye = $aPer[0];
				$mo = $aPer[1];
				
				if ( checkdate($mo, 1, $ye) ) {
					$sTableName = PREFIX_ORDERS_ROWS.$ye.$mo;
					$sOrderName = PREFIX_ORDERS.$ye.$mo;
				} else {
					return array();
				}
			}
			
			$aTbls	= SQL_get_tables($db_finance, "orders_", "______", "ASC");
			
			if ( !in_array($sOrderName, $aTbls) ) {
				return array();
			}
				
			$sQuery = "
				SELECT 
					IF ( ow.is_dds > 0, 8, IF (ne.id_group > 1, ne.id_group, -3) ) as id,
					IF ( ow.is_dds > 0, 'Други', IF (ne.id > 1, ng.name, 'Невъведена') ) as gname,
					IF ( ow.is_dds > 0, -1, IF (ne.id > 1, ne.id, -3) ) as id_nomenclature,
					SUM(ow.paid_sum) as price,
					IF ( ow.is_dds > 0, 'ДДС', IF (ne.id > 1, ne.name, 'Невъведена') ) as nomenclature,
					ow.is_dds
				FROM {$db_name_finance}.{$sTableName} ow
				LEFT JOIN {$db_name_finance}.{$sOrderName} o ON ( o.id = ow.id_order )
				LEFT JOIN {$db_name_finance}.nomenclatures_expenses ne ON ( ne.id = ow.id_nomenclature_expense )
				LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ( ng.id = ne.id_group )
				WHERE o.doc_type = 'buy'		
			";
			
			if ( !empty($nIDOffice) ) {
				$sQuery .= " AND ow.id_office = '{$nIDOffice}' ";
			} elseif ( !empty($nIDFirm) ) {
				$sOfce	.= $oOfce->getIdsByFirm($nIDFirm);
				
				if ( !empty($sOfce) ) {
					$sQuery .= " AND ow.id_office IN ({$sOfce}) ";
				}
			}			
					
			$sQuery .= " GROUP BY ow.id_nomenclature_expense, ow.is_dds ";
			
			if ( !empty($sFilter) ) {
				$aTemp = array();
				$aTemp = explode(",", $sFilter);
				
				// ДДС
				if ( in_array(-2, $aTemp) ) {
					$sQuery .= " HAVING ( is_dds > 0 OR id_nomenclature IN ({$sFilter}) ) ";
				} else {
					$sQuery .= " HAVING id_nomenclature IN ({$sFilter}) ";
				}
			}	
						
			$aData 	= $this->select2($sQuery);

			return $aData;
		}			
				

		/**
		 * Функцията връща реалната сума по дадено ID на ордер
		 * 
		 * @author Павел Петров
		 * @name getRealSumByIDOrder
		 * 
		 * @param int $nIDOrder - ID на ордера
		 * 
		 * @return int - Сума на ордера
		 */			
		public function getRealSumByIDOrder( $nIDOrder ) {
			global $db_name_finance;
			
			$aData = array();
			
			if ( $this->isValidID($nIDOrder) ) {
				$sTableName = PREFIX_ORDERS_ROWS.substr( $nIDOrder, 0, 6 );
			} else {
				return array();
			}	
			
			$sQuery = "
				SELECT 
					SUM(paid_sum)
				FROM {$db_name_finance}.{$sTableName}
				WHERE id_order = {$nIDOrder}
			";
			
			$aData = $this->selectOne2($sQuery);

			return $aData;
		}	
		
		
		/**
		 * Функцията връща тотали на сумите по номенклатури към определен месец
		 * Ползва се във флекс справката за Финанси/Събираемост
		 * 
		 * @author Павел Петров
		 * @name getTotalsByNom
		 * 
		 * @param (string) $sPeriod 	- За кой МЕСЕЦ приходи;
		 * @param (int) $nIDFirm 		- ID на фирмата (незадължителен)
		 * @param (string) $nIDOffices 	- Числова редица с ID-та на офиса (незадължителен)
		 * 
		 * @return array - Списък с резултата групиран по номенклатури
		 */			
		public function getTotalsByNom( $sPeriod, $nIDFirm = 0, $nIDOffice = 0, $sFilter = "" ) {
			global $db_finance, $db_name_finance;
			
			$aTotal		= array();
			$sOffice	= "";
			$aPeriod	= explode("-", $sPeriod);
			$oOffice	= new DBOffices();
			$aTables	= array();
			$sQuery 	= "";
			$br 		= 0;
			$aTables	= SQL_get_tables($db_finance, "orders_rows_", "______", "ASC");
			
			foreach ( $aTables as $key => $aVal ) {
				if ( $aVal == "orders_rows_origin" ) {
					unset($aTables[$key]);
				}
			}	

			unset($aVal);		

			foreach ( $aTables as $sTableName ) {
				$aData 	= array();
				$sTable = "orders_".substr($sTableName, -6);
				
				$sQuery = "
					( SELECT 
						IF ( srw.is_dds > 0, 13, IF (na.id_group > 1, na.id_group, -3) ) as id,
						IF ( srw.is_dds > 0, 'Други', IF (na.id > 1, ng.name, 'Невъведена') ) as gname,
						IF ( srw.is_dds > 0, -1, IF (na.id > 1, na.id, -3) ) as id_nomenclature,
						SUM( srw.paid_sum ) as price,
						IF ( srw.is_dds > 0, 'ДДС', IF (na.id > 1, na.name, 'Невъведена') ) as nomenclature,
						srw.is_dds
					FROM {$db_name_finance}.{$sTableName} srw
					LEFT JOIN {$db_name_finance}.{$sTable} sr ON ( srw.id_order = sr.id )
					LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ( ns.id = srw.id_service )
					LEFT JOIN {$db_name_finance}.nomenclatures_earnings na ON ( na.id = ns.id_nomenclature_earning )
					LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ( ng.id = na.id_group )
					WHERE srw.id_office > 0
						AND DATE_FORMAT(srw.month, '%Y-%m') = '{$sPeriod}'
						AND srw.paid_sum != 0	
						AND sr.doc_type = 'sale'
						
				";
				//AND sr.order_status = 'active'
				if ( !empty($nIDOffice) ) {
					$sQuery 	.= " AND srw.id_office = {$nIDOffice} ";
				} elseif ( !empty($nIDFirm) ) {
					$sOffice	.= $oOffice->getIdsByFirm($nIDFirm);
					
					if ( !empty($sOffice) ) {
						$sQuery .= " AND srw.id_office IN ({$sOffice}) ";
					}
				}			
				
				$sQuery .= " GROUP BY ns.id_nomenclature_earning, srw.is_dds ";
				
				if ( !empty($sFilter) ) {
					$aTemp = array();
					$aTemp = explode(",", $sFilter);
					
					// ДДС
					if ( in_array(-1, $aTemp) ) {
						$sQuery .= " HAVING ( is_dds > 0 OR id_nomenclature IN ({$sFilter}) ) ";
					} else {
						$sQuery .= " HAVING id_nomenclature IN ({$sFilter}) ";
					}
				}		
				
				$sQuery .= " ) ";			
				
				$br++;
				
				$aData 	= $this->select2($sQuery);
				//return $sQuery;
				foreach ( $aData as $v ) {
					$found_key = -1;
					
					if ( ($found_key = array_search_value($aTotal, "id_nomenclature", $v['id_nomenclature'])) !== FALSE ) {
						$aTotal[$found_key]['price'] += $v['price'];
					} else {
						$aTotal[] = $v;
					}
				}
			}
	
			return $aTotal;
		}					
		
		
		/**
		 * Функцията връща тотали на сумите по номенклатури към определен месец
		 * Ползва се във флекс справката за Финанси/Събираемост
		 * 
		 * @author Павел Петров
		 * @name getTotalsByNom2
		 * 
		 * @param (string) $sPeriod 	- За кой МЕСЕЦ приходи;
		 * @param (int) $nIDFirm 		- ID на фирмата (незадължителен)
		 * @param (string) $nIDOffices 	- Числова редица с ID-та на офиса (незадължителен)
		 * 
		 * @return array - Списък с резултата групиран по номенклатури
		 */			
		public function getTotalsByNom2( $sPeriod, $nIDFirm = 0, $nIDOffice = 0, $sFilter = "" ) {
			global $db_finance, $db_name_finance;
			
			$aTotal		= array();
			$sOffice	= "";
			$aPeriod	= explode("-", $sPeriod);
			$oOffice	= new DBOffices();
			$aTables	= array();
			$sQuery 	= "";
			$br 		= 0;
			$aTables	= SQL_get_tables($db_finance, "orders_rows_", "______", "ASC");
			
			foreach ( $aTables as $key => $aVal ) {
				if ( $aVal == "orders_rows_origin" ) {
					unset($aTables[$key]);
				}
			}	

			unset($aVal);		

			foreach ( $aTables as $sTableName ) {
				$aData 	= array();
				$sTable = "orders_".substr($sTableName, -6);
				
				$sQuery = "
					( SELECT 
						IF ( srw.is_dds > 0, 8, IF (ne.id_group > 1, ne.id_group, -3) ) as id,
						IF ( srw.is_dds > 0, 'Други', IF (ne.id > 1, ng.name, 'Невъведена') ) as gname,
						IF ( srw.is_dds > 0, -1, IF (ne.id > 1, ne.id, -3) ) as id_nomenclature,
						SUM(srw.paid_sum) as price,
						IF ( srw.is_dds > 0, 'ДДС', IF (ne.id > 1, ne.name, 'Невъведена') ) as nomenclature,
						srw.is_dds
					FROM {$db_name_finance}.{$sTableName} srw
					LEFT JOIN {$db_name_finance}.{$sTable} sr ON ( srw.id_order = sr.id )
					LEFT JOIN {$db_name_finance}.nomenclatures_expenses ne ON ( ne.id = srw.id_nomenclature_expense )
					LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ( ng.id = ne.id_group )
					WHERE srw.id_office > 0
						AND DATE_FORMAT(srw.month, '%Y-%m') = '{$sPeriod}'
						AND srw.paid_sum != 0	
						AND sr.doc_type = 'buy'
						
				";
				//AND sr.order_status = 'active'
				if ( !empty($nIDOffice) ) {
					$sQuery 	.= " AND srw.id_office = {$nIDOffice} ";
				} elseif ( !empty($nIDFirm) ) {
					$sOffice	.= $oOffice->getIdsByFirm($nIDFirm);
					
					if ( !empty($sOffice) ) {
						$sQuery .= " AND srw.id_office IN ({$sOffice}) ";
					}
				}			
				
				$sQuery .= " GROUP BY srw.id_nomenclature_expense, srw.is_dds ";
				
				if ( !empty($sFilter) ) {
					$aTemp = array();
					$aTemp = explode(",", $sFilter);
					
					// ДДС
					if ( in_array(-1, $aTemp) ) {
						$sQuery .= " HAVING ( is_dds > 0 OR id_nomenclature IN ({$sFilter}) ) ";
					} else {
						$sQuery .= " HAVING id_nomenclature IN ({$sFilter}) ";
					}
				}		
				
				$sQuery .= " ) ";			
				
				$br++;
				
				$aData 	= $this->select2($sQuery);
				//return $sQuery;
				foreach ( $aData as $v ) {
					$found_key = -1;
					
					if ( ($found_key = array_search_value($aTotal, "id_nomenclature", $v['id_nomenclature'])) !== FALSE ) {
						$aTotal[$found_key]['price'] += $v['price'];
					} else {
						$aTotal[] = $v;
					}
				}
			}
	
			return $aTotal;
		}			

		/**
		 * Връща списък с офисите, участващи в документите според зададените критерии.
		 *
		 * @author Павел Петров 
		 * @name getOfficesForTotals()
		 * 
		 * @param (string) 	- $sPeriod
		 * @param (integer) - $nIDFirm
		 * @param (string) 	- $sFilter
		 * @return (array) 	- Масив от офисите, за които има данни според филтъра
		 */
		public function getOfficesForTotals( $sPeriod, $nIDFirm = 0, $sFilter = "" ) {
			global $db_finance, $db_name_finance, $db_name_sod;
			
			$aTotal		= array();
			$aPeriod	= explode("-", $sPeriod);
			$aTables	= array();
			$sQuery 	= "";
			$br 		= 0;
			$aTables	= SQL_get_tables($db_finance, "orders_rows_", "______", "ASC");
			
			foreach ( $aTables as $key => $aVal ) {
				if ( $aVal == "orders_rows_origin" ) {
					unset($aTables[$key]);
				}
			}	

			unset($aVal);		
			

			foreach ( $aTables as $sTableName ) {
				$aData 	= array();
				$sTable = "orders_".substr($sTableName, -6);
				
				$sQuery = "
					( SELECT 
						DISTINCT srw.id_office, 
						IF ( srw.is_dds > 0, -1, IF (na.id > 1, na.id, -3) ) as id_nomenclature, 
						srw.is_dds
					FROM {$db_name_finance}.{$sTableName} srw
					LEFT JOIN {$db_name_finance}.{$sTable} sr ON ( srw.id_order = sr.id )
					LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ( ns.id = srw.id_service )
					LEFT JOIN {$db_name_finance}.nomenclatures_earnings na ON ( na.id = ns.id_nomenclature_earning )
					LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ( ng.id = na.id_group )
					LEFT JOIN {$db_name_sod}.offices o ON ( o.id = srw.id_office AND srw.id_office > 0 )
					WHERE srw.id_office > 0
						AND DATE_FORMAT(srw.month, '%Y-%m') = '{$sPeriod}'
						AND srw.paid_sum > 0	
						AND sr.doc_type = 'sale'
												
				";
				//AND sr.order_status = 'active'
				if ( !empty($nIDFirm) ) {
					$sQuery .= " AND o.id_firm = {$nIDFirm} ";
				}
				
				if ( !empty($sFilter) ) {
					$aTemp = array();
					$aTemp = explode(",", $sFilter);
					
					// ДДС
					if ( in_array(-1, $aTemp) ) {
						$sQuery .= " HAVING ( is_dds > 0 OR id_nomenclature IN ({$sFilter}) ) ";
					} else {
						$sQuery .= " HAVING id_nomenclature IN ({$sFilter}) ";
					}
				}		
				
				$sQuery .= " ) ";			
				
				$br++;
				
				$aData 	= $this->select2($sQuery);
				
				foreach ( $aData as $v ) {
					$aTotal[$v['id_office']] = $v['id_office'];
				}
			}

			return $aTotal;
		}	

		
		/**
		 * Връща списък с офисите, участващи в документите според зададените критерии.
		 *
		 * @author Павел Петров 
		 * @name getOfficesForTotals()
		 * 
		 * @param (string) 	- $sPeriod
		 * @param (integer) - $nIDFirm
		 * @param (string) 	- $sFilter
		 * @return (array) 	- Масив от офисите, за които има данни според филтъра
		 */
		public function getOfficesForTotals2( $sPeriod, $nIDFirm = 0, $sFilter = "" ) {
			global $db_finance, $db_name_finance, $db_name_sod;
			
			$aTotal		= array();
			$aPeriod	= explode("-", $sPeriod);
			$aTables	= array();
			$sQuery 	= "";
			$br 		= 0;
			$aTables	= SQL_get_tables($db_finance, "orders_rows_", "______", "ASC");
			
			foreach ( $aTables as $key => $aVal ) {
				if ( $aVal == "orders_rows_origin" ) {
					unset($aTables[$key]);
				}
			}	

			unset($aVal);		
			

			foreach ( $aTables as $sTableName ) {
				$aData 	= array();
				$sTable = "orders_".substr($sTableName, -6);
				
				$sQuery = "
					( SELECT 
						DISTINCT brw.id_office, 
						IF ( brw.is_dds > 0, -1, IF (ne.id > 1, ne.id, -3) ) as id_nomenclature, 
						brw.is_dds
					FROM {$db_name_finance}.{$sTableName} brw
					LEFT JOIN {$db_name_finance}.{$sTable} sr ON ( brw.id_order = sr.id )
					LEFT JOIN {$db_name_finance}.nomenclatures_expenses ne ON ( ne.id = brw.id_nomenclature_expense )
					LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ( ng.id = ne.id_group )
					LEFT JOIN {$db_name_sod}.offices o ON ( o.id = brw.id_office AND brw.id_office > 0 )
					WHERE brw.id_office > 0
						AND DATE_FORMAT(brw.month, '%Y-%m') = '{$sPeriod}'
						AND brw.paid_sum > 0
						AND sr.doc_type = 'buy'
										
				";
				//AND sr.order_status = 'active'		
				if ( !empty($nIDFirm) ) {
					$sQuery .= " AND o.id_firm = {$nIDFirm} ";
				}
								
				if ( !empty($sFilter) ) {
					$aTemp = array();
					$aTemp = explode(",", $sFilter);
					
					// ДДС
					if ( in_array(-1, $aTemp) ) {
						$sQuery .= " HAVING ( is_dds > 0 OR id_nomenclature IN ({$sFilter}) ) ";
					} else {
						$sQuery .= " HAVING id_nomenclature IN ({$sFilter}) ";
					}
				}		
				
				$sQuery .= " ) ";			
				
				$br++;
				
				$aData 	= $this->select2($sQuery);
				
				foreach ( $aData as $v ) {
					$aTotal[$v['id_office']] = $v['id_office'];
				}
			}

			return $aTotal;
		}			
	}

?>
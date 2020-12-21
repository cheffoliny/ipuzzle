<?php
	class ApiIncomings {
	
		public function isValidID( $nID ) {
			return preg_match("/^\d{13}$/", $nID);
		}	
				
		public function init( DBResponse $oResponse ) {
			global $db_finance;
			
			$nIDUser 	= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			
			$aMonths	= array();
			$aFirms		= array();
			$aFilter	= array();
			$nFilterDef	= 0;

			$oFirms 	= new DBFirms();
			$oFilter	= new DBFilters();

			// Фирми и офиси
			$aFirms 	= $oFirms->getFirmsByOfficeAll();
			$oResponse->SetFlexVar("firm_regions", $aFirms);
			
			// Филтри по зададен потребител
			$aTmp		= array();
			$aTmp		= $oFilter->getFiltersByReportClass("flex_incomings", $nIDUser);
			
			$aFilter[] = array("id" => 0, "name" => ".:: Изберете филтър ::.");
			
			foreach ( $aTmp as $key => $val ) {
				$aFilter[] = array("id" => $key, "name" => $val['name'], "is_default" => $val['is_default']);
				
				if ( $val['is_default'] == 1 ) {
					$nFilterDef = $key;
				}
			}
			
			$oResponse->SetFlexVar("arr_filter", $aFilter);
			
			// Задаваме филтъра по подразбиране
			if ( !empty($nFilterDef) ) {
				$oResponse->SetFlexControl("cbFilters");
				$oResponse->SetFlexControlDefaultValue("cbFilters", "id", $nFilterDef);
			}
			
			// Зареждане на възмжните месеци
			$aTmp		= array();
			$aTmp		= SQL_get_tables($db_finance, "orders_", "______", "ASC");
			
			foreach ( $aTmp as $key => $aVal ) {
				if ( $aVal == "orders_origin" ) {
					unset($aMonths[$key]);
				} else {
					$aMonths[] = substr($aVal, 7, 4)."-".substr($aVal, -2);
				}
			}
			
			$oResponse->SetFlexVar("arr_months", $aMonths);
			
			$oResponse->printResponse();
		}
		
		
		public function search( DBResponse $oResponse ) {
			global $db_finance;
			$nIDUser 	= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;

			$oOrderRow	= new DBOrdersRows();
			$oFilRows	= new DBFiltersParams();
			$aTotals	= array();
			$aFilRows	= array();
			$aFilterEA	= array();
			$aFilterEX	= array();	
			$aMonths	= array();		
			$sFilter	= "";
			$sFilterEA	= "";
			$sFilterEX	= "";				

			$nIDFirm	= Params::get("id_firm", 	0);	
			$nIDFilter	= Params::get("id_filter", 	0);	
			$nIDOffice	= Params::get("id_office", 	0);	
			$sMonthFrom	= Params::get("month_from", date("Y-m"));	
			$sMonthTo	= Params::get("month_to", 	date("Y-m"));	

			if ( !empty($nIDFilter) ) {
				$aFilRows = $oFilRows->getParamsByIDFilter($nIDFilter);
				
				if ( isset($aFilRows['values']) ) {
					$aTmp 			= array();
					$sFilter 		= $aFilRows['values'];
					$aTmp 			= explode(",", $sFilter);
					$aFilterEA[]	= -5;
					$aFilterEX[]	= -5;
					
					foreach ( $aTmp as $v ) {
						if ( substr($v, 0, 3) == "111" ) {
							$aFilterEA[]	= substr($v, 3, strlen($v) - 1);
						} elseif ( substr($v, 0, 3) == "222" ) { 
							$aFilterEX[]	= substr($v, 3, strlen($v) - 1);
						} else {
							$aFilterEA[]	= $v;
							$aFilterEX[]	= $v;
						}
					}
				}
			}			

			$sFilterEA	= implode(",", $aFilterEA);
			$sFilterEX	= implode(",", $aFilterEX);
		
			// Генериране на месеци по шаблон 
			if ( $sMonthFrom > $sMonthTo ) {
				throw new Exception("Несъответствие между начален и краен месец!", DBAPI_ERR_INVALID_PARAM);
			}

			if ( $sMonthFrom == $sMonthTo ) {
				$aMonths[] = $sMonthFrom;
			} else {
				$aTemp 		= array();
				$aTemp 		= explode("-", $sMonthFrom);
				$m			= isset($aTemp[1]) ? $aTemp[1] : 0;
				$y			= isset($aTemp[0]) ? $aTemp[0] : 0;
				
				$br = 0;
				if ( checkdate($m, 1, $y) ) {
					while ( (date("Y-m", mktime(0, 0, 0, $m++, 1, $y)) <= $sMonthTo)  ) {
						$br++; 
						$aMonths[] 	= date("Y-m", mktime(0, 0, 0, $m -1, 1, $y));
						
						// Гаранция
						if ( $br == 11 ) {
							break;
						}
					}
				}
			}
			
			// Зареждане на приходи по ордери
			foreach ( $aMonths as $aVal ) {
				$aTemp		= array();
				$aTemp		= $oOrderRow->getEarningTotalsByNom($aVal, $nIDFirm, $nIDOffice, $sFilterEA);
				
				if ( !empty($aTemp) ) {
					foreach ( $aTemp as $v ) {
						$v['month']	= $aVal;
						$aTotals[] 	= $v;
					}
				}
			}

			$arr_earnings	= array();
			$total_earning 	= 0;

			foreach ( $aTotals as $aVal ) {
				$total_earning	+= $aVal['price'];
				
				if ( isset($arr_earnings[$aVal['id']]) ) {
					$arr_earnings[$aVal['id']]['sum'] 	+= $aVal['price'];
					
					if ( !empty($aVal['id_nomenclature']) ) {
						if ( isset($arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['sum']) ) {
							$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['id'] 		= $aVal['id_nomenclature'];
							$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['label'] 	= $aVal['nomenclature'];
							$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['sum'] 	+= $aVal['price'];								
						} else {
							$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['id'] 		= $aVal['id_nomenclature'];
							$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['label'] 	= $aVal['nomenclature'];
							$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['sum'] 	= $aVal['price'];							
						}
						
						if ( isset($arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']][$aVal['month']]) ) {
							$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']][$aVal['month']]	+= $aVal['price'];
						} else {
							$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']][$aVal['month']]	= $aVal['price'];
						}
					}
				} else {
					$arr_earnings[$aVal['id']]['id'] 			= $aVal['id'];
					$arr_earnings[$aVal['id']]['label'] 		= $aVal['gname'];
					$arr_earnings[$aVal['id']]['sum'] 			= $aVal['price']; // bash totala
					
					
					if ( !empty($aVal['id_nomenclature']) ) {
						$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['id'] 				= $aVal['id_nomenclature'];
						$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['label'] 			= $aVal['nomenclature'];
						$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['sum'] 			= $aVal['price'];
						$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']][$aVal['month']]	= $aVal['price'];
					}					
				}
				
				if ( isset($arr_earnings[$aVal['id']][$aVal['month']]) ) {
					$arr_earnings[$aVal['id']][$aVal['month']] 	+= $aVal['price'];
				} else {
					$arr_earnings[$aVal['id']][$aVal['month']] 	= $aVal['price'];
				}
			}	

			foreach ( $arr_earnings as &$aPrice ) {
				if ( isset($aPrice['sum']) ) {
					$aPrice['sum'] = sprintf("%01.0f лв.", $aPrice['sum']);
				}
				
				foreach ( $aPrice['children'] as &$aPriceChild ) {
					if ( isset($aPriceChild['sum']) ) {
						$aPriceChild['sum'] = sprintf("%01.0f лв.", $aPriceChild['sum']);
					}					
				}
				
				unset($aPriceChild);
				
				foreach ( $aMonths as $aMo ) {
					if ( !isset($aPrice[$aMo]) ) {
						$aPrice[$aMo] = sprintf("%01.0f лв.", 0);
					} else {
						$aPrice[$aMo] = sprintf("%01.0f лв.", $aPrice[$aMo]);
					}
				}
				
				unset($aMo);
				
				foreach ( $aPrice['children'] as &$aChil ) {
					foreach ( $aMonths as $aMo ) {
						if ( !isset($aChil[$aMo]) ) {
							$aChil[$aMo] = sprintf("%01.0f лв.", 0);
						} else {
							$aChil[$aMo] = sprintf("%01.0f лв.", $aChil[$aMo]);
						}
					}					
				}
				
				unset($aChil);
			}

			unset($aPrice);
			
			$oResponse->SetFlexVar("arr_earnings", $arr_earnings);

			// Зареждане на РАЗХОДИ по ордери
			$aTotals		= array();
			
			foreach ( $aMonths as $aVal ) {
				$aTemp		= array();
				$aTemp		= $oOrderRow->getExpenseTotalsByNom($aVal, $nIDFirm, $nIDOffice, $sFilterEX);
				
				if ( !empty($aTemp) ) {
					foreach ( $aTemp as $v ) {
						$v['month']	= $aVal;
						$aTotals[] 	= $v;
					}
				}
			}
			
			$arr_expenses	= array();
			$total_expense 	= 0;

			foreach ( $aTotals as $aVal ) {
				$total_expense	+= $aVal['price'];
				
				if ( isset($arr_expenses[$aVal['id']]) ) {
					$arr_expenses[$aVal['id']]['sum'] 	+= $aVal['price'];
					
					if ( !empty($aVal['id_nomenclature']) ) {
						if ( isset($arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['sum']) ) {
							$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['id'] 		= $aVal['id_nomenclature'];
							$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['label'] 	= $aVal['nomenclature'];
							$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['sum'] 	+= $aVal['price'];								
						} else {
							$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['id'] 		= $aVal['id_nomenclature'];
							$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['label'] 	= $aVal['nomenclature'];
							$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['sum'] 	= $aVal['price'];							
						}
						
						if ( isset($arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']][$aVal['month']]) ) {
							$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']][$aVal['month']]	+= $aVal['price'];
						} else {
							$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']][$aVal['month']]	= $aVal['price'];
						}						
					}
				} else {
					$arr_expenses[$aVal['id']]['id'] 	= $aVal['id'];
					$arr_expenses[$aVal['id']]['label'] = $aVal['gname'];
					$arr_expenses[$aVal['id']]['sum'] 	= $aVal['price'];
					
					if ( !empty($aVal['id_nomenclature']) ) {
						$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['id'] 				= $aVal['id_nomenclature'];
						$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['label'] 			= $aVal['nomenclature'];
						$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['sum'] 			= $aVal['price'];
						$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']][$aVal['month']]	= $aVal['price'];
					}					
				}
				
				if ( isset($arr_expenses[$aVal['id']][$aVal['month']]) ) {
					$arr_expenses[$aVal['id']][$aVal['month']] 	+= $aVal['price'];
				} else {
					$arr_expenses[$aVal['id']][$aVal['month']] 	= $aVal['price'];
				}				
			}	

			foreach ( $arr_expenses as &$aPrice ) {
				if ( isset($aPrice['sum']) ) {
					$aPrice['sum'] = sprintf("%01.0f лв.", $aPrice['sum']);
				}
				
				foreach ( $aPrice['children'] as &$aPriceChild ) {
					if ( isset($aPriceChild['sum']) ) {
						$aPriceChild['sum'] = sprintf("%01.0f лв.", $aPriceChild['sum']);
					}					
				}
				
				unset($aPriceChild);
				
				foreach ( $aMonths as $aMo ) {
					if ( !isset($aPrice[$aMo]) ) {
						$aPrice[$aMo] = sprintf("%01.0f лв.", 0);
					} else {
						$aPrice[$aMo] = sprintf("%01.0f лв.", $aPrice[$aMo]);
					}
				}
				
				unset($aMo);
				
				foreach ( $aPrice['children'] as &$aChil ) {
					foreach ( $aMonths as $aMo ) {
						if ( !isset($aChil[$aMo]) ) {
							$aChil[$aMo] = sprintf("%01.0f лв.", 0);
						} else {
							$aChil[$aMo] = sprintf("%01.0f лв.", $aChil[$aMo]);
						}
					}					
				}
				
				unset($aChil);				
			}
			
			unset($aPrice);

			$oResponse->SetFlexVar("arr_expenses", $arr_expenses);	
			
			$total_change	= $total_earning - $total_expense;
				
			$oResponse->SetFlexVar("total_earning", sprintf("%01.0f лв.", $total_earning));		
			$oResponse->SetFlexVar("total_expense", sprintf("%01.0f лв.", $total_expense));		
			$oResponse->SetFlexVar("total_change", sprintf("%01.0f лв.", $total_change));		
				
			
			$oResponse->printResponse();
		}		

		public function save_filter( DBResponse $oResponse ) {
			global $db_finance;
			
			$nIDUser 	= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;	
			$nIDFilter	= Params::get("id_filter", 		0);
			$is_default	= Params::get("is_default", 	0);
			$sFilName	= Params::get("filter_name",	"");
			$aData		= Params::get("arr_data",		array());
			
			$oFilParams	= new DBFiltersParams();	
			$oFilter	= new DBFilters();	
			
			$aFilter	= array();
			$nFilterDef	= 0;	
			$sData		= "";		
			
			// Валидация на входните данни!
			if ( empty($sFilName) ) {
				throw new Exception("Въведете име на филтъра!!!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($aData) ) {
				throw new Exception("Изберете поне една номенклатура,\nпо която ще се извършва рестрикцията!!!", DBAPI_ERR_INVALID_PARAM);
			}			
			
			if ( $is_default ) {
				$oFilter->resetDefaults("flex_incomings", $nIDUser);
			}
			
			$aFilterData 							= array();
			$aFilterData['id']						= $nIDFilter;
			$aFilterData['name']					= $sFilName;
			$aFilterData['id_person']				= $nIDUser;
			$aFilterData['is_default']				= $is_default;
			$aFilterData['report_class']			= "flex_incomings";
			$aFilterData['is_auto']					= 0;
			$aFilterData['auto_period']				= "day";
			$aFilterData['auto_start_date']			= "0000-00-00";
			$aFilterData['auto_tobot_last_date']	= "0000-00-00";
			$aFilterData['to_arc']					= 0;
			
			$oFilter->update($aFilterData);
			
			$nIDFilter 	= isset($aFilterData['id']) ? $aFilterData['id'] : 0;
			$sData		= implode(",", $aData);
			
			Params::set("id_filter", $nIDFilter);
			
			// Изтриваме старите настройки на филтъра, ако има такива:
			$oFilParams->delParamsByIDFilter($nIDFilter);
			
			// Попълваме новите настройки на филтъра
			if ( !empty($nIDFilter) ) {
				$aFilterData 				= array();
				$aFilterData['id']			= 0;
				$aFilterData['id_filter']	= $nIDFilter;
				$aFilterData['name']		= "values";
				$aFilterData['value']		= $sData;
				
				$oFilParams->update($aFilterData);
			}
			
			// Презареждаме филтрите наново за да отразим промяната!
			$aTmp		= array();
			$aTmp		= $oFilter->getFiltersByReportClass("flex_incomings", $nIDUser);
			
			$aFilter[] 	= array("id" => 0, "name" => ".:: Изберете филтър ::.");
			
			foreach ( $aTmp as $key => $val ) {
				$aFilter[] = array("id" => $key, "name" => $val['name'], "is_default" => $val['is_default']);
				
				if ( $val['is_default'] == 1 ) {
					$nFilterDef = $key;
				}
			}
			
			$oResponse->SetFlexVar("arr_filter", $aFilter);	
			
			// Задаваме филтъра по подразбиране
			if ( !empty($nFilterDef) ) {
				$oResponse->SetFlexControl("cbFilters");
				$oResponse->SetFlexControlDefaultValue("cbFilters", "id", $nFilterDef);
			}			

			$oResponse->printResponse();
		}
		
		public function delete_filter( DBResponse $oResponse ) {

			$oFilters	= new DBFilters();	
			$oFilRows	= new DBFiltersParams();	

			$nIDFilter	= Params::get("id_filter", 	0);
			$nIDUser 	= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;	
			
			if ( !empty($nIDFilter) ) {
				$oFilRows->delParamsByIDFilter($nIDFilter);
				$oFilters->delete($nIDFilter);
			}
			
			// Презареждаме филтрите наново за да отразим промяната!
			$aTmp		= array();
			$aTmp		= $oFilters->getFiltersByReportClass("flex_incomings", $nIDUser);
			
			$aFilter[] 	= array("id" => 0, "name" => ".:: Изберете филтър ::.");
			
			foreach ( $aTmp as $key => $val ) {
				$aFilter[] = array("id" => $key, "name" => $val['name'], "is_default" => $val['is_default']);
				
				if ( $val['is_default'] == 1 ) {
					$nFilterDef = $key;
				}
			}
			
			$oResponse->SetFlexVar("arr_filter", $aFilter);	
			
			// Задаваме филтъра по подразбиране
			if ( !empty($nFilterDef) ) {
				$oResponse->SetFlexControl("cbFilters");
				$oResponse->SetFlexControlDefaultValue("cbFilters", "id", $nFilterDef);
			}	
						
			$oResponse->printResponse();
		}
		
		public function init_filter( DBResponse $oResponse ) {
			global $db_finance;

			$oEarnings	= new DBNomenclaturesEarnings();
			$oExpenses	= new DBNomenclaturesExpenses();
			$oFilter	= new DBFiltersParams();	

			$nIDFilter	= Params::get("id_filter", 	0);

			$aEarnings	= array();
			$aExpenses	= array();
			$aFilter	= array();
			$aNomChoice	= array();
			$sName		= "";

			$arr_filter_earnings	= array();
			$arr_filter_expenses	= array();
			
			$aEarnings 	= $oEarnings->getGroupEarnings();
			$aExpenses 	= $oExpenses->getGroupExpenses();
			$aFilter	= $oFilter->getParamsByIDFilter($nIDFilter);
			
			if ( isset($aFilter['values']) && !empty($aFilter['values']) ) {
				$aNomChoice = explode(",", $aFilter['values']);
			}
			
			// Приходни номенклатури
			$aTmp	= array();
			$set	= false;
			
			foreach ( $aEarnings as $aVal ) {
				$aVal['id'] = "111".$aVal['id'];
				
				$arr_filter_earnings[$aVal['id_group']]['id'] 			= $aVal['id_group'];
				$arr_filter_earnings[$aVal['id_group']]['label'] 		= $aVal['group_name'];
				
				if ( !empty($aVal['id']) ) {
					$flag = in_array($aVal['id'], $aNomChoice) ? 1 : 0;
					
					if ( ($aVal['group_name'] == "Други") && ($set == false) ) {
						$set 	= true;
						$flag2	= in_array(-1, $aNomChoice) ? 1 : 0;
						$arr_filter_earnings[$aVal['id_group']]['children'][-1]['id'] 			= -1;
						$arr_filter_earnings[$aVal['id_group']]['children'][-1]['id_parent']	= $aVal['id_group'];
						$arr_filter_earnings[$aVal['id_group']]['children'][-1]['label'] 		= ".:: ДДС ::.";
						$arr_filter_earnings[$aVal['id_group']]['children'][-1]['checkState'] 	= $flag2;
						
						if ( isset($aTmp[$aVal['id_group']]) && ($aTmp[$aVal['id_group']] < 2) ) {
							if ( $aTmp[$aVal['id_group']] != $flag2 ) {
								$aTmp[$aVal['id_group']] = 2;
							}
						} elseif ( !isset($aTmp[$aVal['id_group']]) ) {
							$aTmp[$aVal['id_group']] = $flag2;
						}						
					}
					
					$arr_filter_earnings[$aVal['id_group']]['children'][$aVal['id']]['id'] 			= $aVal['id'];
					$arr_filter_earnings[$aVal['id_group']]['children'][$aVal['id']]['id_parent']	= $aVal['id_group'];
					$arr_filter_earnings[$aVal['id_group']]['children'][$aVal['id']]['label'] 		= $aVal['name'];
					$arr_filter_earnings[$aVal['id_group']]['children'][$aVal['id']]['checkState'] 	= $flag;
					
					if ( isset($aTmp[$aVal['id_group']]) && ($aTmp[$aVal['id_group']] < 2) ) {
						if ( $aTmp[$aVal['id_group']] != $flag ) {
							$aTmp[$aVal['id_group']] = 2;
						}
					} elseif ( !isset($aTmp[$aVal['id_group']]) ) {
						$aTmp[$aVal['id_group']] = $flag;
					}
				}					
			}
			
			foreach ( $aTmp as $key => $val ) {
				$arr_filter_earnings[$key]['checkState'] = $val;
			}
			
			unset($aVal);
			
			// Разходни номенклатури
			$aTmp	= array();
			$set	= false;
			
			foreach ( $aExpenses as $aVal ) {
				$aVal['id'] = "222".$aVal['id'];
				
				$arr_filter_expenses[$aVal['id_group']]['id'] 			= $aVal['id_group'];
				$arr_filter_expenses[$aVal['id_group']]['label'] 		= $aVal['group_name'];
				
				if ( !empty($aVal['id']) ) {
					$flag = in_array($aVal['id'], $aNomChoice) ? 1 : 0;
					
					if ( ($aVal['group_name'] == "Други") && ($set == false) ) {
						$set 	= true;
						$flag2	= in_array(-2, $aNomChoice) ? 1 : 0;
						$arr_filter_expenses[$aVal['id_group']]['children'][-2]['id'] 			= -2;
						$arr_filter_expenses[$aVal['id_group']]['children'][-2]['id_parent']	= $aVal['id_group'];
						$arr_filter_expenses[$aVal['id_group']]['children'][-2]['label'] 		= ".:: ДДС ::.";
						$arr_filter_expenses[$aVal['id_group']]['children'][-2]['checkState'] 	= $flag2;
						
						if ( isset($aTmp[$aVal['id_group']]) && ($aTmp[$aVal['id_group']] < 2) ) {
							if ( $aTmp[$aVal['id_group']] != $flag2 ) {
								$aTmp[$aVal['id_group']] = 2;
							}
						} elseif ( !isset($aTmp[$aVal['id_group']]) ) {
							$aTmp[$aVal['id_group']] = $flag2;
						}						
					}
										
					$arr_filter_expenses[$aVal['id_group']]['children'][$aVal['id']]['id'] 			= $aVal['id'];
					$arr_filter_expenses[$aVal['id_group']]['children'][$aVal['id']]['id_parent']	= $aVal['id_group'];
					$arr_filter_expenses[$aVal['id_group']]['children'][$aVal['id']]['label'] 		= $aVal['name'];
					$arr_filter_expenses[$aVal['id_group']]['children'][$aVal['id']]['checkState'] 	= $flag;
					
					if ( isset($aTmp[$aVal['id_group']]) && ($aTmp[$aVal['id_group']] < 2) ) {
						if ( $aTmp[$aVal['id_group']] != $flag ) {
							$aTmp[$aVal['id_group']] = 2;
						}
					} elseif ( !isset($aTmp[$aVal['id_group']]) ) {
						$aTmp[$aVal['id_group']] = $flag;
					}
				}					
			}	

			foreach ( $aTmp as $key => $val ) {
				$arr_filter_expenses[$key]['checkState'] = $val;
			}			
			
			//$oResponse->setAlert(ArrayToString($aFilBase));
			
			$oResponse->SetFlexVar("arr_filter_earnings", $arr_filter_earnings);	
			$oResponse->SetFlexVar("arr_filter_expenses", $arr_filter_expenses);
			
			$oResponse->printResponse();
		}
		

		public function gen_excel() {	
			global $db_finance;
			
			require_once "include/php2excel/class.writeexcel_workbook.inc.php";
			require_once "include/php2excel/class.writeexcel_worksheet.inc.php";
						
			$oOrderRow	= new DBOrdersRows();
			$oFilRows	= new DBFiltersParams();
			$aTotals	= array();
			$aFilRows	= array();
			$aFilterEA	= array();
			$aFilterEX	= array();
			$aMonths	= array();
			$sFilterEA	= "";
			$sFilterEX	= "";
			$sDate		= "";	
			
			$nIDFirm	= Params::get("id_firm", 	0);	
			$nIDOffice	= Params::get("id_office", 	0);	
			$nIDFilter	= Params::get("id_filter", 	0);	
			$sMonthFrom	= Params::get("month_from", date("Y-m"));	
			$sMonthTo	= Params::get("month_to", 	date("Y-m"));	
			$mname		= array();
			
			if ( !empty($nIDFilter) ) {
				$aFilRows = $oFilRows->getParamsByIDFilter($nIDFilter);
				
				if ( isset($aFilRows['values']) ) {
					$aTmp 			= array();
					$sFilter 		= $aFilRows['values'];
					$aTmp 			= explode(",", $sFilter);
					$aFilterEA[]	= -5;
					$aFilterEX[]	= -5;
					
					foreach ( $aTmp as $v ) {
						if ( substr($v, 0, 3) == "111" ) {
							$aFilterEA[]	= substr($v, 3, strlen($v) - 1);
						} elseif ( substr($v, 0, 3) == "222" ) { 
							$aFilterEX[]	= substr($v, 3, strlen($v) - 1);
						} else {
							$aFilterEA[]	= $v;
							$aFilterEX[]	= $v;
						}
					}
				}
			}			

			$sFilterEA	= implode(",", $aFilterEA);
			$sFilterEX	= implode(",", $aFilterEX);
		
			// Генериране на месеци по шаблон 
			if ( $sMonthFrom > $sMonthTo ) {
				throw new Exception("Несъответствие между начален и краен месец!", DBAPI_ERR_INVALID_PARAM);
			}

			if ( $sMonthFrom == $sMonthTo ) {
				$aMonths[] = $sMonthFrom;
			} else {
				$aTemp 		= array();
				$aTemp 		= explode("-", $sMonthFrom);
				$m			= isset($aTemp[1]) ? $aTemp[1] : 0;
				$y			= isset($aTemp[0]) ? $aTemp[0] : 0;
				
				$br = 0;
				if ( checkdate($m, 1, $y) ) {
					while ( (date("Y-m", mktime(0, 0, 0, $m++, 1, $y)) <= $sMonthTo)  ) {
						$br++; 
						$aMonths[] 	= date("Y-m", mktime(0, 0, 0, $m -1, 1, $y));
						
						// Гаранция
						if ( $br == 11 ) {
							break;
						}
					}
				}
			}
			
			// Зареждане на приходи по ордери
			foreach ( $aMonths as $aVal ) {
				$aTemp		= array();
				$aTemp		= $oOrderRow->getEarningTotalsByNom($aVal, $nIDFirm, $nIDOffice, $sFilterEA);
				
				if ( !empty($aTemp) ) {
					foreach ( $aTemp as $v ) {
						$v['month']	= $aVal;
						$aTotals[] 	= $v;
					}
				}
			}			
			
			if ( $sMonthFrom == $sMonthTo ) {
				$aMonParse	= array();
				$aMonParse	= explode("-", $sMonthFrom);
						
				$mname['01'] = 'Януари';
				$mname['02'] = 'Февруари';
				$mname['03'] = 'Март';
				$mname['04'] = 'Април';
				$mname['05'] = 'Май';
				$mname['06'] = 'Юни';
				$mname['07'] = 'Юли';
				$mname['08'] = 'Август';
				$mname['09'] = 'Септември';
				$mname['10'] = 'Октомври';
				$mname['11'] = 'Ноември';
				$mname['12'] = 'Декември';
					
				if ( isset($aMonParse[1]) && (($aMonParse[1] > 0) && ($aMonParse[1] < 13)) ) {
					$sDate 	= $mname[$aMonParse[1]]." ".$aMonParse[0];
				}
			} else {
				$sDate = substr($sMonthFrom, 5, 2)."-".substr($sMonthTo, 5, 2);
			}
			
			$arr_earnings	= array();
			$arr_expenses	= array();
			$total_earning 	= 0;
			$total_expense 	= 0;
			$total_change 	= 0;
			$aDetailTotalEA	= array();
			$aDetailTotalEX	= array();
						
			//$aTotals 	= $oOrderRow->getEarningTotalsByNom($sMonth, $nIDFirm, $nIDOffice);
			
			foreach ( $aTotals as $aVal ) {
				$total_earning += $aVal['price'];
				
				if ( isset($arr_earnings[$aVal['id']]) ) {
					$arr_earnings[$aVal['id']]['sum'] 	+= $aVal['price'];
					
					if ( !empty($aVal['id_nomenclature']) ) {
						if ( isset($arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['sum']) ) {
							$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['id'] 		= $aVal['id_nomenclature'];
							$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['label'] 	= $aVal['nomenclature'];
							$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['sum'] 	+= $aVal['price'];								
						} else {
							$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['id'] 		= $aVal['id_nomenclature'];
							$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['label'] 	= $aVal['nomenclature'];
							$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['sum'] 	= $aVal['price'];							
						}
						
						if ( isset($arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']][$aVal['month']]) ) {
							$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']][$aVal['month']]	+= $aVal['price'];
						} else {
							$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']][$aVal['month']]	= $aVal['price'];
						}						
					}
				} else {
					$arr_earnings[$aVal['id']]['id'] 	= $aVal['id'];
					$arr_earnings[$aVal['id']]['label'] = $aVal['gname'];
					$arr_earnings[$aVal['id']]['sum'] 	= $aVal['price'];
					
					if ( !empty($aVal['id_nomenclature']) ) {
						$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['id'] 				= $aVal['id_nomenclature'];
						$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['label'] 			= $aVal['nomenclature'];
						$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']]['sum'] 			= $aVal['price'];
						$arr_earnings[$aVal['id']]['children'][$aVal['id_nomenclature']][$aVal['month']]	= $aVal['price'];
					}					
				}
				
				if ( isset($arr_earnings[$aVal['id']][$aVal['month']]) ) {
					$arr_earnings[$aVal['id']][$aVal['month']] 	+= $aVal['price'];
				} else {
					$arr_earnings[$aVal['id']][$aVal['month']] 	= $aVal['price'];
				}

				if ( isset($aDetailTotalEA[$aVal['month']]) ) {
					$aDetailTotalEA[$aVal['month']] 	+= $aVal['price'];
				} else {
					$aDetailTotalEA[$aVal['month']] 	= $aVal['price'];
				}				
			}	

			foreach ( $arr_earnings as &$aPrice ) {
				if ( isset($aPrice['sum']) ) {
					//$aPrice['sum'] = sprintf("%01.2f лв.", $aPrice['sum']);
					$aPrice['sum'] = round($aPrice['sum'], 0);
				}
				
				foreach ( $aPrice['children'] as &$aPriceChild ) {
					if ( isset($aPriceChild['sum']) ) {
						//$aPriceChild['sum'] = sprintf("%01.2f лв.", $aPriceChild['sum']);
						$aPriceChild['sum'] = round($aPriceChild['sum'], 0);
					}					
				}
				
				unset($aPriceChild);
				
				foreach ( $aMonths as $aMo ) {
					if ( !isset($aPrice[$aMo]) ) {
						$aPrice[$aMo] = 0;	//sprintf("%01.2f лв.", 0);
					} else {
						$aPrice[$aMo] = round($aPrice[$aMo], 0);	//sprintf("%01.2f лв.", $aPrice[$aMo]);
					}
				}
				
				unset($aMo);
				
				foreach ( $aPrice['children'] as &$aChil ) {
					foreach ( $aMonths as $aMo ) {
						if ( !isset($aChil[$aMo]) ) {
							$aChil[$aMo] = 0;	//sprintf("%01.2f лв.", 0);
						} else {
							$aChil[$aMo] = round($aChil[$aMo]);	//sprintf("%01.2f лв.", $aChil[$aMo]);
						}
					}					
				}
				
				unset($aChil);				
			}
			
			unset($aPrice);
			
			// Зареждане на разходи по ордери
			$aTotals		= array();
			
			foreach ( $aMonths as $aVal ) {
				$aTemp		= array();
				$aTemp		= $oOrderRow->getExpenseTotalsByNom($aVal, $nIDFirm, $nIDOffice, $sFilterEX);
				
				if ( !empty($aTemp) ) {
					foreach ( $aTemp as $v ) {
						$v['month']	= $aVal;
						$aTotals[] 	= $v;
					}
				}
			}				

			foreach ( $aTotals as $aVal ) {
				$total_expense += $aVal['price'];
				
				if ( isset($arr_expenses[$aVal['id']]) ) {
					$arr_expenses[$aVal['id']]['sum'] 	+= $aVal['price'];
					
					if ( !empty($aVal['id_nomenclature']) ) {
						if ( isset($arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['sum']) ) {
							$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['id'] 		= $aVal['id_nomenclature'];
							$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['label'] 	= $aVal['nomenclature'];
							$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['sum'] 	+= $aVal['price'];								
						} else {
							$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['id'] 		= $aVal['id_nomenclature'];
							$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['label'] 	= $aVal['nomenclature'];
							$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['sum'] 	= $aVal['price'];							
						}
						
						if ( isset($arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']][$aVal['month']]) ) {
							$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']][$aVal['month']]	+= $aVal['price'];
						} else {
							$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']][$aVal['month']]	= $aVal['price'];
						}							
					}
				} else {
					$arr_expenses[$aVal['id']]['id'] 	= $aVal['id'];
					$arr_expenses[$aVal['id']]['label'] = $aVal['gname'];
					$arr_expenses[$aVal['id']]['sum'] 	= $aVal['price'];
					
					if ( !empty($aVal['id_nomenclature']) ) {
						$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['id'] 				= $aVal['id_nomenclature'];
						$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['label'] 			= $aVal['nomenclature'];
						$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']]['sum'] 			= $aVal['price'];
						$arr_expenses[$aVal['id']]['children'][$aVal['id_nomenclature']][$aVal['month']]	= $aVal['price'];
					}					
				}
				
				if ( isset($arr_expenses[$aVal['id']][$aVal['month']]) ) {
					$arr_expenses[$aVal['id']][$aVal['month']] 	+= $aVal['price'];
				} else {
					$arr_expenses[$aVal['id']][$aVal['month']] 	= $aVal['price'];
				}	

				if ( isset($aDetailTotalEX[$aVal['month']]) ) {
					$aDetailTotalEX[$aVal['month']] 	+= $aVal['price'];
				} else {
					$aDetailTotalEX[$aVal['month']] 	= $aVal['price'];
				}				
			}	

			foreach ( $arr_expenses as &$aPrice ) {
				if ( isset($aPrice['sum']) ) {
					$aPrice['sum'] = round($aPrice['sum'], 0); //sprintf("%01.2f лв.", $aPrice['sum']);
				}
				
				foreach ( $aPrice['children'] as &$aPriceChild ) {
					if ( isset($aPriceChild['sum']) ) {
						$aPriceChild['sum'] = round($aPriceChild['sum'], 0);	//sprintf("%01.2f лв.", $aPriceChild['sum']);
					}					
				}
				
				unset($aPriceChild);
				
				foreach ( $aMonths as $aMo ) {
					if ( !isset($aPrice[$aMo]) ) {
						$aPrice[$aMo] = 0;		//sprintf("%01.2f лв.", 0);
					} else {
						$aPrice[$aMo] = round($aPrice[$aMo], 0);	//sprintf("%01.2f лв.", $aPrice[$aMo]);
					}
				}
				
				unset($aMo);
				
				foreach ( $aPrice['children'] as &$aChil ) {
					foreach ( $aMonths as $aMo ) {
						if ( !isset($aChil[$aMo]) ) {
							$aChil[$aMo] = 0;	//sprintf("%01.2f лв.", 0);
						} else {
							$aChil[$aMo] = round($aChil[$aMo], 0);	//sprintf("%01.2f лв.", $aChil[$aMo]);
						}
					}					
				}
				
				unset($aChil);					
			}
			
			unset($aPrice);		
			
			$total_change	= $total_earning - $total_expense;
						
			$sFileName	= "buy.xls";
		  	$sFileNameTemp = tempnam("\tmp", $sFileName);
			$oWorkbook = &new writeexcel_workbook($sFileNameTemp);
			 // Създване на worksheet
			$oWorksheet = &$oWorkbook->addworksheet( iconv("UTF-8", "CP1251", "Постъпления") );
			
			//$oWorksheet->set_landscape();
			$oWorksheet->set_paper(9);
			$oWorksheet->set_margins(0.25);
			$oWorksheet->set_margin_right(0.25);
			$oWorksheet->set_margin_bottom(0.5);
			$oWorksheet->set_footer( iconv("UTF-8", "CP1251", "Powered by IntelliSys © 2009."), 0.25);
				
			// Формат за Заглавие
			$formatCaption 	=&$oWorkbook->addformat( array("bold" => 1, "italic" => 0, "size" => 12, "font" => "MS Sans Serif", "color" => "blue", "border" => 0) );
			$formatCaption->set_align("right");
			
			// Формат за заглавията на колоните
			$formatFields 	=&$oWorkbook->addformat( array("bold" => 1, "italic" => 0, "size" => 9, "font" => "MS Sans Serif", "color" => "black", "border" => 1, "pattern" => 1, "fg_color" => "silver") );			
			$formatFields2 	=&$oWorkbook->addformat( array("bold" => 1, "italic" => 0, "size" => 9, "font" => "MS Sans Serif", "color" => "black", "border" => 1, "pattern" => 1, "fg_color" => "silver") );			
			$formatFields2->set_align("center");

			// Формат за информацията
			$formatData 	=&$oWorkbook->addformat( array("size" => 9, "color" => "black", "font" => "MS Sans Serif", "border" => 1) );
			$formatFloat	=&$oWorkbook->addformat( array("size" => 9, "color" => "black", "font" => "MS Sans Serif", "border" => 1) );	
			$formatFloat->set_align("right");
			
			// Формат за тоталите
			$formatCap 		=&$oWorkbook->addformat( array("bold" => 1, "italic" => 0, "size" => 9, "font" => "MS Sans Serif", "color" => "black", "border" => 1, "pattern" => 1, "fg_color" => "silver") );
			$formatCap->set_align("right");
			$formatTotal 	=&$oWorkbook->addformat( array("bold" => 1, "size" => 9, "color" => "black", "font" => "MS Sans Serif", "border" => 1) );
			$formatTotal->set_align("right");
			
			
			// Заглавие
			$y 				= 0;
			$x 				= 0;		
			$next 			= 0;
			$nMonthCount 	= count($aMonths);			
						
			$oWorksheet->merge_cells($y, $x, $y + 1, $x + 1 + $nMonthCount > 1 ? $nMonthCount : 0 );
			// TODO: ZAglavieto!!!
			$oWorksheet->write_string($x, $y, iconv("UTF-8", "CP1251", "Постъпления за ".$sDate), $formatCaption );
						
			// Широчина на колоните
 			$oWorksheet->set_column( $next, $next, 40 );
 			$next++;
 			$oWorksheet->set_column( $next, $next, 12 );
 			$next++;
 			$i = 2;
 			
 			if ( $nMonthCount > 1 ) {
 				foreach ( $aMonths as $aMon ) {
 					$oWorksheet->set_column( $i, $i, 10 );
 					$oWorksheet->write_string( 6, $i, iconv("UTF-8", "CP1251", $aMon ), $formatFields2 );
 					$next++;
 					$i++;
 				}
 			}
			  
			// Тотали :)
			$oWorksheet->write_string(2, 0, iconv("UTF-8", "CP1251", "Приходи"), $formatTotal );	
			$oWorksheet->write_string(2, 1, iconv("UTF-8", "CP1251", sprintf("%01.0f лв.", $total_earning)), $formatCap );
			$oWorksheet->write_string(3, 0, iconv("UTF-8", "CP1251", "Разходи"), $formatTotal );	
			$oWorksheet->write_string(3, 1, iconv("UTF-8", "CP1251", sprintf("%01.0f лв.", $total_expense)), $formatCap );
			$oWorksheet->write_string(4, 0, iconv("UTF-8", "CP1251", "Промяна"), $formatTotal );	
			$oWorksheet->write_string(4, 1, iconv("UTF-8", "CP1251", sprintf("%01.0f лв.", $total_change)), $formatCap );	
			
			// Начални координати ПРИХОДИ
			$y = 7;
			$x = 0;		
		 	
			$oWorksheet->write_string( $y - 1, 0, iconv("UTF-8", "CP1251", "ПРИХОД" ), $formatFields2 );
		 	$oWorksheet->write_string( $y - 1, 1, iconv("UTF-8", "CP1251", "Общо" ), $formatFields2 );
		 	
			foreach ( $arr_earnings as $aVal ) {
				$oWorksheet->write_string( $y, $x, iconv("UTF-8", "CP1251", $aVal['label'] ), $formatFields );
				$oWorksheet->write_number( $y, $x+1, iconv("UTF-8", "CP1251", $aVal['sum'] ), $formatFields );
					
				ksort($aVal);
				reset($aVal);	
								
				$k = 2;
				foreach ( $aVal as $sKey => $sVal ) {
					if ( in_array($sKey, $aMonths) && ($nMonthCount > 1) ) {
						$oWorksheet->write_number( $y, $k, iconv("UTF-8", "CP1251", $sVal ), $formatFields );	
						$k++;
					}
				}
									
				foreach ($aVal['children'] as $aValChild ) {
					$y++;
					$x = 0;		
					
					$oWorksheet->write_string( $y, $x, iconv("UTF-8", "CP1251", $aValChild['label'] ), $formatData );
					$oWorksheet->write_number( $y, $x+1, iconv("UTF-8", "CP1251", $aValChild['sum'] ), $formatFloat );	
					
					$aTest	= array();
					$aTest	= $aValChild;
					
					ksort($aTest);
					reset($aTest);
					
					$k = 2;
					foreach ( $aTest as $key => $val ) {
						if ( in_array($key, $aMonths) && ($nMonthCount > 1) ) {
							$oWorksheet->write_number( $y, $x+$k, iconv("UTF-8", "CP1251", $val), $formatFloat );					
							$k++;
						}
					}
				} 
				
				$y++;
				$x = 0;				
			}
			
			
			// Начални координати РАЗХОДИ
			$y += 3;
			$x = 0;		
		 	$i = 2;
 			
		 	$oWorksheet->write_string( $y - 1, 0, iconv("UTF-8", "CP1251", "РАЗХОД" ), $formatFields2 );
		 	$oWorksheet->write_string( $y - 1, 1, iconv("UTF-8", "CP1251", "Общо" ), $formatFields2 );
		 	
 			if ( $nMonthCount > 1 ) {
 				foreach ( $aMonths as $aMon ) {
 					$oWorksheet->write_string( $y - 1, $i, iconv("UTF-8", "CP1251", $aMon ), $formatFields2 );
 					$i++;
 				}
 			}
 						
			foreach ( $arr_expenses as $aVal ) {
				$oWorksheet->write_string( $y, $x, iconv("UTF-8", "CP1251", $aVal['label'] ), $formatFields );
				$oWorksheet->write_number( $y, $x+1, iconv("UTF-8", "CP1251", $aVal['sum'] ), $formatFields );
				
				ksort($aVal);
				reset($aVal);	
								
				$k = 2;
				foreach ( $aVal as $sKey => $sVal ) {
					if ( in_array($sKey, $aMonths) && ($nMonthCount > 1) ) {
						$oWorksheet->write_number( $y, $k, iconv("UTF-8", "CP1251", $sVal ), $formatFields );	
						$k++;
					}
				}
								
				foreach ($aVal['children'] as $aValChild ) {
					$y++;
					$x = 0;	
					
					$oWorksheet->write_string( $y, $x, iconv("UTF-8", "CP1251", $aValChild['label'] ), $formatData );
					$oWorksheet->write_number( $y, $x+1, iconv("UTF-8", "CP1251", $aValChild['sum'] ), $formatFloat );	

					$aTest	= array();
					$aTest	= $aValChild;
					
					ksort($aTest);
					reset($aTest);
					
					$k = 2;
					foreach ( $aTest as $key => $val ) {
						if ( in_array($key, $aMonths) && ($nMonthCount > 1) ) {
							$oWorksheet->write_number( $y, $x+$k, iconv("UTF-8", "CP1251", $val), $formatFloat );
							$k++;
						}
					}					
				} 
				
				$y++;
				$x = 0;			
			}			
			
			// Затваряне на файла 
			$oWorkbook->close();

			header("Content-type: application/x-msexcel;");
		    header("Content-Disposition: attachment; filename=$sFileName" );
		    header("Expires: 0");
		    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		    header("Pragma: public");
			
		    $fh = fopen($sFileNameTemp, "rb");
			fpassthru($fh);
			
			sleep(2);
			
			unlink($sFileNameTemp);				
		}
	}
?>
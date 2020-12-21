<?php
	class ApiBudget {
		public function isValidID( $nID ) {
			return preg_match("/^\d{13}$/", $nID);
		}	
				
		public function init( DBResponse $oResponse ) {
			global $db_finance;
			$nIDUser 	= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;

			$oFirms 	= new DBFirms();
			$oSaleRows	= new DBSalesDocsRows();
			$oFilter	= new DBFilters();
			$oFilRows	= new DBFiltersParams();

			$aFirms		= array();
			$aTotals	= array();
			$aTables	= array();
			$aMonths	= array();
			$aFilRows	= array();
			$aFilterEA	= array();
			$aFilterEX	= array();	
			$sFilter	= "";
			$sFilterEA	= "";
			$sFilterEX	= "";					
			
			$sMonth		= Params::get("month", 	date("Y-m"));	
						
			// Фирми и офиси
			$aFirms 	= $oFirms->getFirmsByOfficeAll();
			$oResponse->SetFlexVar("firm_regions", $aFirms);
			
			// Зареждане на възмжните месеци 
			$aTables	= SQL_get_tables($db_finance, "sales_docs_rows_", "______", "ASC");
			
			foreach ( $aTables as $key => $aVal ) {
				if ( $aVal == "sales_docs_rows_origin" ) {
					unset($aMonths[$key]);
				} else {
					$aMonths[] = substr($aVal, 16, 4)."-".substr($aVal, -2);
				}
			}
			
			$oResponse->SetFlexVar("arr_months", $aMonths);

			// Филтри по зададен потребител
			$aTmp		= array();
			$aTmp		= $oFilter->getFiltersByReportClass("flex_collections", $nIDUser);
			
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
			
			//$oResponse->SetFlexVar("arr_earnings", $arr_earnings);	
			
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
				$oFilter->resetDefaults("flex_collections", $nIDUser);
			}
			
			$aFilterData 							= array();
			$aFilterData['id']						= $nIDFilter;
			$aFilterData['name']					= $sFilName;
			$aFilterData['id_person']				= $nIDUser;
			$aFilterData['is_default']				= $is_default;
			$aFilterData['report_class']			= "flex_collections";
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
			$aTmp		= $oFilter->getFiltersByReportClass("flex_collections", $nIDUser);
			
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
			$aTmp		= $oFilters->getFiltersByReportClass("flex_collections", $nIDUser);
			
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
		
		public function search( DBResponse $oResponse ) {
			global $db_finance;
			
			$nIDUser 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;

			$oFirms 		= new DBFirms();
			$oSaleRows		= new DBSalesDocsRows();
			$oBuyRows		= new DBBuyDocsRows();
			$oFilRows		= new DBFiltersParams();
			$oOffice		= new DBOffices();
			$oBudget		= new DBBudget();
			$oBudgetRows	= new DBBudgetRows();
			$oOrdersRows	= new DBOrdersRows();

			$aFirms			= array();
			$aTotals		= array();
			$aTables		= array();
			$aFilRows		= array();
			$aFilterEA		= array();
			$aFilterEX		= array();
			$aMonths		= array();	
			$aFinal			= array();
			$aOffice		= array();
			$aStorm			= array();
			$sFilter		= "";
			$sFilterEA		= "";
			$sFilterEX		= "";
			$total_expense	= 0;
			$total_earning	= 0;	
			$total1_save	= 0;
			$total2_save	= 0;
			$aBudgetEA		= array();
			$aBudgetEX		= array();
			
			$nIDFirm		= Params::get("id_firm", 	0);	
			$nIDOffice		= Params::get("id_office", 	0);	
			$nIDFilter		= Params::get("id_filter", 	0);	
			$bView			= Params::get("regions_view", 0);
			$sMonthFrom		= Params::get("month_from", date("Y-m"));	
			$sMonthTo		= Params::get("month_to", 	date("Y-m"));			
			
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
//			if ( $sMonthFrom > $sMonthTo ) {
//				throw new Exception("Несъответствие между начален и краен месец!", DBAPI_ERR_INVALID_PARAM);
//			}

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
						if ( $br == 12 ) {
							break;
						}
					}
				}
			}
			
			$sMonthSearch		= isset($aMonths[0]) ? $aMonths[0] : date("Y-m");		
					
			$aTemp				= array();
			$sOffices			= "";
			$aOfficeEarning		= array();
			$aOfficeExpense		= array();
			$nTotalEarning		= array();
			$nTotalExpense		= array();
			
			$aFinal				= array();
			$aRawData			= array();	
			$aTempFirmEarning	= array();
			$aTempFirmExpense	= array();
			$aMergeFirms		= array();
			
			$aBudgetEA = $oBudget->getBudgetByMonth($sMonthSearch, "earning", $nIDFirm, $nIDOffice);
			$aBudgetEX = $oBudget->getBudgetByMonth($sMonthSearch, "expense", $nIDFirm, $nIDOffice);
			
			//$oResponse->setAlert(ArrayToString($aBudgetEA));
									
			if ( $bView ) {		
				$aTempFirmEarning 		= $oOrdersRows->getOfficesForTotals($sMonthSearch, $nIDFirm, $sFilterEA);
				$aTempFirmExpense		= $oOrdersRows->getOfficesForTotals2($sMonthSearch, $nIDFirm, $sFilterEX);
				
				//$aTempFirmEarning 		= $oSaleRows->getOfficesForTotals($sMonthSearch, $nIDFirm, $sFilterEA);
				//$aTempFirmExpense		= $oBuyRows->getOfficesForTotals($sMonthSearch, $nIDFirm, $sFilterEX);				
				
				$aMergeFirms 			= array_merge($aTempFirmEarning, $aTempFirmExpense);
		
				foreach ( $aMergeFirms as $aVal2 ) {
					$aOfficeEarning[$aVal2] 	= $aVal2;
					$nTotalEarning[$aVal2]		= 0;
				}
					
				foreach ( $aMergeFirms as $aVal2 ) {
					$aOfficeExpense[$aVal2] 	= $aVal2;
					$nTotalExpense[$aVal2]		= 0;
				}					
					
				$arr_earnings[-100]['id'] 				= 0;
				$arr_earnings[-100]['label'] 			= "Всичко: ";
				$arr_earnings[-100]['sum'] 				= 0;
				$arr_earnings[-100][$sMonthSearch] 		= 0;
				$arr_earnings[-100]['result_saved'] 	= 0;
				$arr_earnings[-100]['sum_saved'] 		= 0;	
				$arr_earnings[-100]['is_total'] 		= 1;					
					
				foreach ( $aOfficeEarning as $aVal2 ) {	
					$aRawData	= array();
					$aOffice	= array();
					$aFinal		= array();
					$sOffice	= $oOffice->getRecord($aVal2);
					$aRawData 	= $oOrdersRows->getTotalsByNom($sMonthSearch, $nIDFirm, $aVal2, $sFilterEA);
					//$aRawData 	= $oSaleRows->getTotalsByNom($sMonthSearch, $nIDFirm, $aVal2, $sFilterEA);
					$aDataSort	= array();
					$t_save1 	= 0;
						
					if ( !empty($aRawData) ) {
						foreach ( $aRawData as $v ) {
							$v['month']		= $sMonthSearch;
							$v['id_office']	= $aVal2;
							$aFinal[] 		= $v;
						}
					}						
					
					$aDataSort							= $this->dataMutate($aFinal, $aMonths, $sMonthSearch);

					$nSum 								= isset($aDataSort['sum']) ? $aDataSort['sum'] : 0;
						
					$arr_earnings[-100][$sMonthSearch] 	+= $nSum;	
					$arr_earnings[-100]['sum']			+= $nSum;
					
					if ( !isset($arr_earnings[$aVal2]) ) {
						$arr_earnings[$aVal2]['id']				= $aVal2;
						$arr_earnings[$aVal2]['label']			= isset($sOffice['name']) ? $sOffice['name'] : "";
						$arr_earnings[$aVal2]['sum']			= $nSum;
					//	$arr_earnings[$aVal2][$sMonthSearch] 	= $nTotal[$aVal2];
						$arr_earnings[$aVal2]['children']		= $aDataSort;
						
					} else {
						$arr_earnings[$aVal2][$sMonthSearch] 	+= $nTotal[$aVal2];
							
						foreach ( $aDataSort as $nKey => $aVal3 ) {
							if ( $nKey == "sum" ) {
								$arr_earnings[$aVal2]['sum']	+= $aVal3;
							}
						
							if ( !isset($arr_earnings[$aVal2]['children'][$nKey]) ) {
								$arr_earnings[$aVal2]['children'][$nKey] = $aVal3;
								
							} else {
								foreach ( $aVal3 as $nKey4 => $aVal4 ) {
									if ( $nKey4 == "sum" ) {
										$arr_earnings[$aVal2]['children'][$nKey]['sum']		+= $aVal4;
									}												
																		
									if ( !isset($arr_earnings[$aVal2]['children'][$nKey]['children'][$nKey4]) ) {
										if ( $sMonthSearch == $nKey4 ) {
											$arr_earnings[$aVal2]['children'][$nKey][$nKey4] = $aVal4;
										}
											
										if ( $nKey4 == "children" ) {
											foreach ( $aVal4 as $nkeyNum => $nKeyVal ) {
												if ( isset($arr_earnings[$aVal2]['children'][$nKey]['children'][$nkeyNum]) ) {
													$arr_earnings[$aVal2]['children'][$nKey]['children'][$nkeyNum][$sMonthSearch] 	+= $nKeyVal[$sMonthSearch];
													$arr_earnings[$aVal2]['children'][$nKey]['children'][$nkeyNum]['sum']			+= $nKeyVal['sum'];
												} else {												
													$arr_earnings[$aVal2]['children'][$nKey]['children'][$nkeyNum]					= $nKeyVal;
												}
											}
										}
									} 
								}
							}
						}
					}
					
					// Запазени стойности
					foreach ( $arr_earnings[$aVal2]['children'] as $nFormatKey => $aFormatVal ) {
						
						foreach ( $aBudgetEA as $aEar ) {
							if ( isset($aEar['id_group']) && ($aEar['id_group'] == $nFormatKey) && ($aEar['id_office'] == $aVal2) ) {
								
								if ( isset($arr_earnings[$aVal2]['children'][$nFormatKey]['sum_saved'])) {
									$arr_earnings[$aVal2]['children'][$nFormatKey]['sum_saved'] = sprintf("%01.0f лв.", floatval($arr_earnings[$aVal2]['children'][$nFormatKey]['sum_saved']) + $aEar['sum']);
								} else {
									//$oResponse->setAlert(ArrayToString($aEar));
									$arr_earnings[$aVal2]['children'][$nFormatKey]['sum_saved'] = sprintf("%01.0f лв.", $aEar['sum']);
								}
							}
						}
						
						if ( !isset($arr_earnings[$aVal2]['children'][$nFormatKey]['sum_saved']) ) {
							//$arr_earnings[$aVal2]['children'][$nFormatKey]['sum_saved'] = sprintf("%01.0f лв.", 0);						
						}
						
						if ( is_array($aFormatVal) ) {
							foreach ( $aFormatVal as $sKey => $aChild ) {
								if ( $sKey == "children" ) {
									foreach ( $aChild as $k => $v ) {
										foreach ( $aBudgetEA as $aEar ) {
											if ( isset($aEar['id_nomenclature']) && ($aEar['id_nomenclature'] == $k)  && ($aEar['id_office'] == $aVal2) ) {
												if ( isset($arr_earnings[$aVal2]['children'][$nFormatKey]['children'][$k]['sum_saved'])) {
													$arr_earnings[$aVal2]['children'][$nFormatKey]['children'][$k]['sum_saved'] = sprintf("%01.0f лв.", floatval($arr_earnings[$aVal2]['children'][$nFormatKey]['children'][$k]['sum_saved']) + $aEar['sum']);
													$t_save1 += $aEar['sum'];
												} else {
													$arr_earnings[$aVal2]['children'][$nFormatKey]['children'][$k]['sum_saved'] = sprintf("%01.0f лв.", $aEar['sum']);
													$t_save1 += $aEar['sum'];
												}
											}
										}
		
										if ( !isset($arr_earnings[$aVal2]['children'][$nFormatKey]['children'][$k]['sum_saved']) ) {
											$arr_earnings[$aVal2]['children'][$nFormatKey]['children'][$k]['sum_saved'] = sprintf("%01.0f лв.", 0);						
										}															
									}
								}
							}
						}
						
						if ( isset($arr_earnings[$aVal2]['sum_saved']) ) {
							$arr_earnings[$aVal2]['sum_saved'] =  sprintf("%01.0f лв.", floatval($arr_earnings[$aVal2]['sum_saved']) + $t_save1);
						} else {
							$arr_earnings[$aVal2]['sum_saved'] =  sprintf("%01.0f лв.", $t_save1);
						}	
						
						$total1_save	+= $t_save1;
						$t_save1 		= 0;			
					}					
				}
					
				
				// TODO: loop za razhodi
				$arr_expenses[-100]['id'] 			= 0;
				$arr_expenses[-100]['label'] 		= "Всичко: ";
				$arr_expenses[-100]['sum'] 			= 0;
				$arr_expenses[-100][$sMonthSearch] 	= 0;					
				$arr_expenses[-100]['is_total'] 	= 1;

				foreach ( $aOfficeExpense as $aVal2 ) {		
					$aRawData	= array();
					$aOffice	= array();
					$aFinal		= array();
					$aDataSort	= array();
					$sOffice	= $oOffice->getRecord($aVal2);
					$aRawData 	= $oOrdersRows->getTotalsByNom2($sMonthSearch, $nIDFirm, $aVal2, $sFilterEX);
					//$aRawData 	= $oBuyRows->getTotalsByNom($sMonthSearch, $nIDFirm, $aVal2, $sFilterEX);
										
					if ( !empty($aRawData) ) {
						foreach ( $aRawData as $v ) {
							$v['month']		= $sMonthSearch;
							$v['id_office']	= $aVal2;
							$aFinal[] 		= $v;
						}
					}						
						
					$aDataSort							= $this->dataMutate($aFinal, $aMonths, $sMonthSearch);

					$nSum 								= isset($aDataSort['sum']) ? $aDataSort['sum'] : 0;
						
					$arr_expenses[-100][$sMonthSearch] 	+= $nSum;	
					$arr_expenses[-100]['sum']			+= $nSum;
					
					if ( !isset($arr_expenses[$aVal2]) ) {
						$arr_expenses[$aVal2]['id']				= $aVal2;
						$arr_expenses[$aVal2]['label']			= isset($sOffice['name']) ? $sOffice['name'] : "";
						$arr_expenses[$aVal2]['sum']			= $nSum;
					//	$arr_expenses[$aVal2][$sMonthSearch] 	= $nTotal[$aVal2];
						$arr_expenses[$aVal2]['children']		= $aDataSort;
					} else {
						$arr_expenses[$aVal2][$sMonthSearch] 	+= $nTotal[$aVal2];
							
						foreach ( $aDataSort as $nKey => $aVal3 ) {
							if ( $nKey == "sum" ) {
								$arr_expenses[$aVal2]['sum']	+= $aVal3;
							}
								
							if ( !isset($arr_expenses[$aVal2]['children'][$nKey]) ) {
								$arr_expenses[$aVal2]['children'][$nKey] = $aVal3;			
							} else {
								foreach ( $aVal3 as $nKey4 => $aVal4 ) {
									if ( $nKey4 == "sum" ) {
										$arr_expenses[$aVal2]['children'][$nKey]['sum']		+= $aVal4;
									}
																		
									if ( !isset($arr_expenses[$aVal2]['children'][$nKey]['children'][$nKey4]) ) {
										if ( $sMonthSearch == $nKey4 ) {
											$arr_expenses[$aVal2]['children'][$nKey][$nKey4] = $aVal4;
										}
											
										if ( $nKey4 == "children" ) {
											foreach ( $aVal4 as $nkeyNum => $nKeyVal ) {
												if ( isset($arr_expenses[$aVal2]['children'][$nKey]['children'][$nkeyNum]) ) {
													$arr_expenses[$aVal2]['children'][$nKey]['children'][$nkeyNum][$sMonthSearch] 	+= $nKeyVal[$sMonthSearch];
													$arr_expenses[$aVal2]['children'][$nKey]['children'][$nkeyNum]['sum']	+= $nKeyVal['sum'];
												} else {												
													$arr_expenses[$aVal2]['children'][$nKey]['children'][$nkeyNum]			= $nKeyVal;
												}
											}
										}
									} 
								}
							}
						}
					}
					
					
					// Запазени стойности
					foreach ( $arr_expenses[$aVal2]['children'] as $nFormatKey => $aFormatVal ) {
						
						foreach ( $aBudgetEX as $aEar ) {
							if ( isset($aEar['id_group']) && ($aEar['id_group'] == $nFormatKey) && ($aEar['id_office'] == $aVal2) ) {
								
								if ( isset($arr_expenses[$aVal2]['children'][$nFormatKey]['sum_saved'])) {
									$arr_expenses[$aVal2]['children'][$nFormatKey]['sum_saved'] = sprintf("%01.0f лв.", floatval($arr_expenses[$aVal2]['children'][$nFormatKey]['sum_saved']) + $aEar['sum']);
								} else {
									//$oResponse->setAlert(ArrayToString($aEar));
									$arr_expenses[$aVal2]['children'][$nFormatKey]['sum_saved'] = sprintf("%01.0f лв.", $aEar['sum']);
								}
							}
						}
						
						if ( !isset($arr_expenses[$aVal2]['children'][$nFormatKey]['sum_saved']) ) {
							//$arr_expenses[$aVal2]['children'][$nFormatKey]['sum_saved'] = sprintf("%01.0f лв.", 0);						
						}
						
						if ( is_array($aFormatVal) ) {
							foreach ( $aFormatVal as $sKey => $aChild ) {
								if ( $sKey == "children" ) {
									foreach ( $aChild as $k => $v ) {
										foreach ( $aBudgetEX as $aEar ) {
											if ( isset($aEar['id_nomenclature']) && ($aEar['id_nomenclature'] == $k)  && ($aEar['id_office'] == $aVal2) ) {
												if ( isset($arr_expenses[$aVal2]['children'][$nFormatKey]['children'][$k]['sum_saved'])) {
													$arr_expenses[$aVal2]['children'][$nFormatKey]['children'][$k]['sum_saved'] = sprintf("%01.0f лв.", floatval($arr_expenses[$aVal2]['children'][$nFormatKey]['children'][$k]['sum_saved']) + $aEar['sum']);
													$t_save2 += $aEar['sum'];
												} else {
													$arr_expenses[$aVal2]['children'][$nFormatKey]['children'][$k]['sum_saved'] = sprintf("%01.0f лв.", $aEar['sum']);
													$t_save2 += $aEar['sum'];
												}
											}
										}
		
										if ( !isset($arr_expenses[$aVal2]['children'][$nFormatKey]['children'][$k]['sum_saved']) ) {
											$arr_expenses[$aVal2]['children'][$nFormatKey]['children'][$k]['sum_saved'] = sprintf("%01.0f лв.", 0);						
										}															
									}
								}
							}
						}
						
						if ( isset($arr_expenses[$aVal2]['sum_saved']) ) {
							$arr_expenses[$aVal2]['sum_saved'] =  sprintf("%01.0f лв.", floatval($arr_expenses[$aVal2]['sum_saved']) + $t_save2);
						} else {
							$arr_expenses[$aVal2]['sum_saved'] =  sprintf("%01.0f лв.", $t_save2);
						}	
						
						$total2_save	+= $t_save2;
						$t_save2 		= 0;			
					}									
				}
				
				$arr_earnings[-100]['sum_saved'] =  sprintf("%01.0f лв.", $total1_save);
				$arr_expenses[-100]['sum_saved'] =  sprintf("%01.0f лв.", $total2_save);
				
				foreach ( $arr_earnings as $k => $v ) {
					$arr_earnings[$k]['result'] =  sprintf("%01.0f лв.", floatval($arr_earnings[$k]['sum']) - floatval($arr_expenses[$k]['sum']));
					
					$arr_earnings[$k]['result_saved'] =  sprintf("%01.0f лв.", $arr_earnings[$k]['sum_saved'] - $arr_expenses[$k]['sum_saved']);
//					if ( isset($arr_expenses[$k]['result_saved']) ) {
//						$arr_earnings[$k]['result_saved'] =  sprintf("%01.0f лв.", $arr_earnings[$k]['sum_saved'] - $arr_expenses[$k]['sum_saved']);
//					} else {
//						$arr_earnings[$k]['result_saved'] =  sprintf("%01.0f лв.", $arr_earnings[$k]['sum_saved']);
//					}					
				}				
				
			} else {
				$aTemp		= array();
				$aTotals	= array();
				$aTemp 		= $oOrdersRows->getTotalsByNom($sMonthSearch, $nIDFirm, $nIDOffice, $sFilterEA);
				//$aTemp 		= $oSaleRows->getTotalsByNom($sMonthSearch, $nIDFirm, $nIDOffice, $sFilterEA);
					
				if ( !empty($aTemp) ) {
					foreach ( $aTemp as $v ) {
						$v['month']		= $sMonthSearch;
						$aTotals[] 		= $v;
							
						$total_earning += $v['price'];
					}
				}
					
				$arr_earnings = $this->dataMerge( $aTotals, $aMonths, $sMonthSearch );
				
				// Запазени стойности
				$t_save = 0;
				foreach ( $arr_earnings as $nIDKey => $aVal ) {
					foreach ( $aBudgetEA as $aEar ) {
						if ( isset($aEar['id_group']) && ($aEar['id_group'] == $nIDKey) ) {
							if ( isset($arr_earnings[$nIDKey]['sum_saved'])) {
								$arr_earnings[$nIDKey]['sum_saved'] = sprintf("%01.0f лв.", floatval($arr_earnings[$nIDKey]['sum_saved']) + $aEar['sum']);
							} else {
								$arr_earnings[$nIDKey]['sum_saved'] = sprintf("%01.0f лв.", $aEar['sum']);
							}
						}
					}
					
					if ( !isset($arr_earnings[$nIDKey]['sum_saved']) ) {
						$arr_earnings[$nIDKey]['sum_saved'] = sprintf("%01.0f лв.", 0);						
					}
					
					
					foreach ( $aVal as $sKey => $aChild ) {
						if ( $sKey == "children" ) {
							foreach ( $aChild as $k => $v ) {
								foreach ( $aBudgetEA as $aEar ) {
									if ( isset($aEar['id_nomenclature']) && ($aEar['id_nomenclature'] == $k) ) {
										if ( isset($arr_earnings[$nIDKey]['children'][$k]['sum_saved'])) {
											$arr_earnings[$nIDKey]['children'][$k]['sum_saved'] = sprintf("%01.0f лв.", floatval($arr_earnings[$nIDKey]['children'][$k]['sum_saved']) + $aEar['sum']);
											$t_save += $aEar['sum'];
										} else {
											$arr_earnings[$nIDKey]['children'][$k]['sum_saved'] = sprintf("%01.0f лв.", $aEar['sum']);
											$t_save += $aEar['sum'];
										}
									}
								}

								if ( !isset($arr_earnings[$nIDKey]['children'][$k]['sum_saved']) ) {
									$arr_earnings[$nIDKey]['children'][$k]['sum_saved'] = sprintf("%01.0f лв.", 0);						
								}															
							}
						}
					}
				}
								
				$arr_earnings[-100]['id'] 			= 0;
				$arr_earnings[-100]['label'] 		= "Всичко: ";
				$arr_earnings[-100]['is_total'] 	= 1;
				$arr_earnings[-100]['sum'] 			= sprintf("%01.0f лв.", $total_earning);
				$arr_earnings[-100]['sum_saved']	= sprintf("%01.0f лв.", $t_save);
				
				$aTemp		= array();
				$aTotals	= array();
				$aTemp 		= $oOrdersRows->getTotalsByNom2($sMonthSearch, $nIDFirm, $nIDOffice, $sFilterEX);
				//$aTemp 		= $oBuyRows->getTotalsByNom($sMonthSearch, $nIDFirm, $nIDOffice, $sFilterEX);

				if ( !empty($aTemp) ) {
					foreach ( $aTemp as $v ) {
						$v['month']		= $sMonthSearch;
						$aTotals[] 		= $v;
							
						$total_expense += $v['price'];
					}
				}				
	
				$arr_expenses = $this->dataMerge( $aTotals, $aMonths, $sMonthSearch );	
				
				// Запазени стойности
				$t_save2	= 0;
				
				foreach ( $arr_expenses as $nIDKey => $aVal ) {
					foreach ( $aBudgetEX as $aEar ) {
						if ( isset($aEar['id_group']) && ($aEar['id_group'] == $nIDKey) ) {
							if ( isset($arr_expenses[$nIDKey]['sum_saved'])) {
								$arr_expenses[$nIDKey]['sum_saved'] = sprintf("%01.0f лв.", floatval($arr_expenses[$nIDKey]['sum_saved']) + $aEar['sum']);
							} else {
								$arr_expenses[$nIDKey]['sum_saved'] = sprintf("%01.0f лв.", $aEar['sum']);
							}
						}
					}
					
					if ( !isset($arr_expenses[$nIDKey]['sum_saved']) ) {
						$arr_expenses[$nIDKey]['sum_saved'] = sprintf("%01.0f лв.", 0);						
					}
					
					
					foreach ( $aVal as $sKey => $aChild ) {
						if ( $sKey == "children" ) {
							foreach ( $aChild as $k => $v ) {
								foreach ( $aBudgetEX as $aEar ) {
									if ( isset($aEar['id_nomenclature']) && ($aEar['id_nomenclature'] == $k) ) {
										if ( isset($arr_expenses[$nIDKey]['children'][$k]['sum_saved'])) {
											$arr_expenses[$nIDKey]['children'][$k]['sum_saved'] = sprintf("%01.0f лв.", floatval($arr_expenses[$nIDKey]['children'][$k]['sum_saved']) + $aEar['sum']);
											$t_save2 += $aEar['sum'];
										} else {
											$arr_expenses[$nIDKey]['children'][$k]['sum_saved'] = sprintf("%01.0f лв.", $aEar['sum']);
											$t_save2 += $aEar['sum'];
										}
									}
								}

								if ( !isset($arr_expenses[$nIDKey]['children'][$k]['sum_saved']) ) {
									$arr_expenses[$nIDKey]['children'][$k]['sum_saved'] = sprintf("%01.0f лв.", 0);						
								}															
							}
						}
					}
				}							
				
				$arr_expenses[-100]['id'] 				= 0;
				$arr_expenses[-100]['label'] 			= "Всичко: ";
				$arr_expenses[-100]['is_total'] 		= 1;
				$arr_expenses[-100]['sum'] 				= sprintf("%01.0f лв.", $total_expense);
				$arr_expenses[-100]['sum_saved']		= sprintf("%01.0f лв.", $t_save2);
				$arr_earnings[-100]['result_saved']		= sprintf("%01.0f лв.", $t_save - $t_save2);
								
				ksort($arr_earnings);
				reset($arr_earnings);					
					
				ksort($arr_expenses);
				reset($arr_expenses);					
			}
			
			$arr_earnings[-100]['result'] 				=  sprintf("%01.0f лв.", $arr_earnings[-100]['sum'] - $arr_expenses[-100]['sum']);

			$arr_earnings = $this->formatData($arr_earnings, $aMonths);
			$arr_expenses = $this->formatData($arr_expenses, $aMonths);
			
			$oResponse->SetFlexVar("arr_earnings", $arr_earnings);
			$oResponse->SetFlexVar("arr_expenses", $arr_expenses);								
			
			$oResponse->printResponse();	
		}
		
		
		public function formatData( $aData, $aMonths ) {

			foreach ( $aData as $nRootIndex => $aRootThread ) {
				$aData[$nRootIndex]['sum'] = sprintf("%01.0f лв.", $aRootThread['sum']);
				
				foreach ( $aRootThread as $sRootkey => $aRootValues ) {
					foreach ( $aMonths as $sPopulateMonth ) {
						if ( !isset($aRootThread[$sPopulateMonth]) ) {
							$aData[$nRootIndex][$sPopulateMonth] = sprintf("%01.0f лв.", 0);
						}
					}
														
					if ( in_array($sRootkey, $aMonths) ) {
						$aData[$nRootIndex][$sRootkey] = sprintf("%01.0f лв.", $aRootValues);
					}
					
					// Преминаване на второ ниво в дървото
					if ( $sRootkey == "children" ) {
						foreach ( $aRootValues as $sChildOneKey => $aChildOneValues ) {
							if ( $sChildOneKey == "sum" ) {
								$aData[$nRootIndex]['children']['sum'] = sprintf("%01.0f лв.", $aChildOneValues);
							} else {
								// По номенклатури - първо ниво
								foreach ( $aChildOneValues as $nChildNomenclatureOne => $aChildNomenclatureOne ) {
									if ( $nChildNomenclatureOne == "sum" ) {
										$aData[$nRootIndex]['children'][$sChildOneKey]['sum'] = sprintf("%01.0f лв.", $aChildNomenclatureOne);
									}									

									if ( in_array($nChildNomenclatureOne, $aMonths) ) {
										$aData[$nRootIndex]['children'][$sChildOneKey][$nChildNomenclatureOne] = sprintf("%01.0f лв.", $aChildNomenclatureOne);
									}	

									// Преминаване на трето ниво в дървото
									if ( $nChildNomenclatureOne == "children" ) {
										foreach ( $aChildNomenclatureOne as $sChildTwoKey => $aChildTwoValues ) {
											foreach ( $aChildTwoValues as $sChildTwoIndex => $aChildTwoStore ) {
												if ( $sChildTwoIndex == "sum" ) {
													$aData[$nRootIndex]['children'][$sChildOneKey]['children'][$sChildTwoKey]['sum'] = sprintf("%01.0f лв.", $aChildTwoStore);
												}
												
												if ( in_array($sChildTwoIndex, $aMonths) ) {
													$aData[$nRootIndex]['children'][$sChildOneKey]['children'][$sChildTwoKey][$sChildTwoIndex] = sprintf("%01.0f лв.", $aChildTwoStore);
												}												
											}

																				
										}
										
									}
								}
							}
						}
					}
				}
			}	
			
			return $aData;		
		}
		
		/**
		 * Приема за параметър масив със сурови данни, извлечени от БД 
		 * и ги трансформира в точно определена каскадна структура, попълваща
		 * дървовидната структура във флекс справката за събираемост.
		 *
		 * @author 	Павел Петров
		 * 
		 * @name 	dataMerge()
		 * @param 	(array) - $aTotals - данните от БД в суров вид
		 * @param 	(array) - $aMonths - масив с месеците за обработка
		 * @param 	(string) - $sMonth - за кой месец се отнася
		 * 
		 * @return 	(array) - данните в разгънат каскаден вид с тотали
		 */		
		public function dataMerge( $aTotals, $aMonths, $sMonth ) {
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
					$aPrice['sum'] = sprintf("%01.0f лв.", $aPrice['sum'])." лв.";
				}
						
				foreach ( $aPrice['children'] as &$aPriceChild ) {
					if ( isset($aPriceChild['sum']) ) {
						$aPriceChild['sum'] = sprintf("%01.0f лв.", $aPriceChild['sum'])." лв.";
					}					
				}
						
				unset($aPriceChild);
						
				foreach ( $aMonths as $aMo ) {
					if ( !isset($aPrice[$aMo]) ) {
						$aPrice[$aMo] = sprintf("%01.0f лв.", 0);
					} else {
						$aPrice[$aMo] = sprintf("%01.0f лв.", $aPrice[$aMo])." лв.";
					}
				}
						
				unset($aMo);
						
				foreach ( $aPrice['children'] as &$aChil ) {
					foreach ( $aMonths as $aMo ) {
						if ( !isset($aChil[$aMo]) ) {
							$aChil[$aMo] = sprintf("%01.0f лв.", 0);
						} else {
							$aChil[$aMo] = sprintf("%01.0f лв.", $aChil[$aMo])." лв.";
						}
					}					
				}
					
				unset($aChil);
			}
	
			unset($aPrice);	
			
			return $arr_earnings;
		}
		
		/**
		 * Приема за параметър масив със сурови данни, извлечени от БД 
		 * и ги трансформира в точно определена каскадна структура, попълваща
		 * дървовидната структура във флекс справката за събираемост.
		 *
		 * @author 	Павел Петров
		 * 
		 * @name 	dataMutate()
		 * @param 	(array) - $aTotals - данните от БД в суров вид
		 * @param 	(array) - $aMonths - масив с месеците за обработка
		 * @param 	(string) - $sMonth - за кой месец се отнася
		 * 
		 * @return 	(array) - данните в разгънат каскаден вид с тотали
		 */
		private function dataMutate( $aTotals, $aMonths, $sMonth ) {
			if ( !is_array($aTotals) || empty($aTotals) ) {
				return array();
			}
			
			$arr_earnings	= array();
			$total_earning 	= 0;	

			foreach ( $aTotals as $aVal ) {
				if ( $aVal['month'] != $sMonth ) {
					continue;
				}			
				
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
			
			$arr_earnings['sum'] = $total_earning;
			
			return $arr_earnings;		
		}
		

		public function init_filter( DBResponse $oResponse ) {
			global $db_finance;

			$oEarnings				= new DBNomenclaturesEarnings();
			$oExpenses				= new DBNomenclaturesExpenses();
			$oFilter				= new DBFiltersParams();	

			$nIDFilter				= Params::get("id_filter", 	0);

			$aEarnings				= array();
			$aExpenses				= array();
			$aFilter				= array();
			$aNomChoice				= array();
			$sName					= "";

			$arr_filter_earnings	= array();
			$arr_filter_expenses	= array();
			
			$aEarnings 				= $oEarnings->getGroupEarnings();
			$aExpenses 				= $oEarnings->getGroupExpenses();
			$aFilter				= $oFilter->getParamsByIDFilter($nIDFilter);
			
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
						$flag2	= in_array(-1, $aNomChoice) ? 1 : 0;
						$arr_filter_expenses[$aVal['id_group']]['children'][-1]['id'] 			= -1;
						$arr_filter_expenses[$aVal['id_group']]['children'][-1]['id_parent']	= $aVal['id_group'];
						$arr_filter_expenses[$aVal['id_group']]['children'][-1]['label'] 		= ".:: ДДС ::.";
						$arr_filter_expenses[$aVal['id_group']]['children'][-1]['checkState'] 	= $flag2;
						
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
			
			unset($aVal);			
			
			$oResponse->SetFlexVar("arr_filter_earnings", $arr_filter_earnings);
			
			$oResponse->SetFlexVar("arr_filter_expenses", $arr_filter_expenses);	
			
			$oResponse->printResponse();
		}
		
		
		public function saveBudget( DBResponse $oResponse ) {			
			global $db_finance;
			
			$nIDUser 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			
			$nIDFirm		= Params::get("id_firm", 	0);	
			$nIDOffice		= Params::get("id_office", 	0);	
			$nIDFilter		= Params::get("id_filter", 	0);	
			$bView			= Params::get("regions_view", 0);
			$sMonth			= Params::get("month_from", date("Y-m"));			

			$oFirms 		= new DBFirms();
			$oSaleRows		= new DBSalesDocsRows();
			$oBuyRows		= new DBBuyDocsRows();
			$oFilRows		= new DBFiltersParams();
			$oOffice		= new DBOffices();
			$oBudget		= new DBBudget();
			$oBudgetRows	= new DBBudgetRows();
			$oOrdersRows	= new DBOrdersRows();

			$aFilRows		= array();
			$aFilterEA		= array();
			$aFilterEX		= array();
			$sFilter		= "";
			$sFilterEA		= "";
			$sFilterEX		= "";
			$nIDBudget		= 0;
			
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
			
			$sMonthTo 	= date("Y-m", mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
			
			// Проверка за краен месец 
			if ( $sMonth > $sMonthTo ) {
				throw new Exception("Бюджета трябва да бъде за предходен месец!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$nHaveBudget 		= 0;
			$nHaveBudget		= $oBudget->checkMonth($sMonth);
			
			if ( !empty($nHaveBudget) ) {
				throw new Exception("Бюджета за избрания месец вече е генериран!", DBAPI_ERR_INVALID_PARAM);
			}

			$aOfficeEarning		= array();
			$aOfficeExpense		= array();
			$aTempFirmEarning	= array();
			$aTempFirmExpense	= array();
			$aMergeFirms		= array();
			
			//$aTempFirmEarning 		= $oSaleRows->getOfficesForTotals($sMonth, 0, $sFilterEA);
			//$aTempFirmEarning 		= $oSaleRows->getOfficesForTotals($sMonth, 0, "");
			//$aTempFirmExpense		= $oBuyRows->getOfficesForTotals($sMonth, 0, "");
			
			$aTempFirmEarning 		= $oOrdersRows->getOfficesForTotals($sMonth, 0, "");
			$aTempFirmExpense		= $oOrdersRows->getOfficesForTotals2($sMonth, 0, "");			
				
			$aMergeFirms 			= array_merge($aTempFirmEarning, $aTempFirmExpense);
		
			foreach ( $aMergeFirms as $aVal2 ) {
				$aOfficeEarning[$aVal2] 	= $aVal2;
			}
					
			foreach ( $aMergeFirms as $aVal2 ) {
				$aOfficeExpense[$aVal2] 	= $aVal2;
			}									
			
			if ( !empty($aMergeFirms) ) {
				$aData = array();
				$aData['id'] 	= 0;
				$aData['month'] = $sMonth."-01";
				
				$oBudget->update($aData);
				
				$nIDBudget = isset($aData['id']) ? $aData['id'] : 0;
			}
			
			foreach ( $aOfficeEarning as $aVal2 ) {	
				$aRawData	= array();
				$aOffice	= array();
				
				$sOffice	= $oOffice->getRecord($aVal2);
				//$aRawData 	= $oSaleRows->getTotalsByNom($sMonth, $nIDFirm, $aVal2, $sFilterEA);		
				//$aRawData 	= $oSaleRows->getTotalsByNom($sMonth, 0, $aVal2, "");			
				$aRawData 	= $oOrdersRows->getTotalsByNom($sMonth, 0, $aVal2, "");			
				
				if ( !empty($aRawData) ) {
					foreach ( $aRawData as $aExp ) {
						$aData = array();
						$aData['id'] 				= 0;
						$aData['id_budget'] 		= $nIDBudget;
						$aData['type'] 				= "earning";
						$aData['id_nomenclature'] 	= isset($aExp['id_nomenclature']) 	? $aExp['id_nomenclature'] 	: 0;
						$aData['id_office'] 		= $aVal2;
						$aData['sum'] 				= isset($aExp['price']) 			? $aExp['price'] 			: 0;
						
						$oBudgetRows->update($aData);
					}	
				}			
			}
					
			foreach ( $aOfficeExpense as $aVal2 ) {		
				$aRawData	= array();
				$aOffice	= array();

				$sOffice	= $oOffice->getRecord($aVal2);
				//$aRawData 	= $oBuyRows->getTotalsByNom($sMonth, $nIDFirm, $aVal2, $sFilterEX);
				//$aRawData 	= $oBuyRows->getTotalsByNom($sMonth, 0, $aVal2, "");
				$aRawData 	= $oOrdersRows->getTotalsByNom2($sMonth, 0, $aVal2, "");

				if ( !empty($aRawData) ) {
					foreach ( $aRawData as $aExp ) {
						$aData = array();
						$aData['id'] 				= 0;
						$aData['id_budget'] 		= $nIDBudget;
						$aData['type'] 				= "expense";
						$aData['id_nomenclature'] 	= isset($aExp['id_nomenclature']) 	? $aExp['id_nomenclature'] 	: 0;
						$aData['id_office'] 		= $aVal2;
						$aData['sum'] 				= isset($aExp['price'])				? $aExp['price'] 			: 0;
						
						$oBudgetRows->update($aData);	
					}
				}					
			}
			
			$oResponse->printResponse();
		}

			
		public function gen_excel() {	
			global $db_finance;
			
			require_once "include/php2excel/class.writeexcel_workbook.inc.php";
			require_once "include/php2excel/class.writeexcel_worksheet.inc.php";
							
			$oSaleRows		= new DBSalesDocsRows();
			$oBuyRows		= new DBBuyDocsRows();
			$oOrdersRows	= new DBOrdersRows();
			$oFilRows		= new DBFiltersParams();
			$aTotalEarnings	= array();
			$aTotalExpenses	= array();
			$aFilRows		= array();
			$aFilterEA		= array();
			$aFilterEX		= array();
			$aMonths		= array();
			$sFilterEA		= "";
			$sFilterEX		= "";
			$sDate			= "";	
			$arr_earnings	= array();
			$arr_expenses	= array();
			$total_earning 	= 0;
			$total_expense 	= 0;
			$aDetailTotalEA	= array();
			$aDetailTotalEX	= array();			
			
			$nIDFirm		= Params::get("id_firm", 	0);	
			$nIDOffice		= Params::get("id_office", 	0);	
			$nIDFilter		= Params::get("id_filter", 	0);	
			$sMonthFrom		= Params::get("month_from", date("Y-m"));		
			$mname			= array();
			
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

			$aMonths[] 	= $sMonthFrom;
			
			$aMonParse	= array();
			$aMonParse	= explode("-", $sMonthFrom);
						
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
					
			if ( isset($aMonParse[1]) && (($aMonParse[1] > 0) && ($aMonParse[1] < 13)) ) {
				$sDate 	= $mname[$aMonParse[1]]." ".$aMonParse[0];
			}			
			
			// Зареждане на приходи по документи
			$aTemp1		= array();
			$aTemp2		= array();
			//$aTemp1 		= $oSaleRows->getTotalsByNom($sMonthFrom, $nIDFirm, $nIDOffice, $sFilterEA);
			//$aTemp2 		= $oBuyRows->getTotalsByNom($sMonthFrom, $nIDFirm, $nIDOffice, $sFilterEX);
			
			$aTemp1 		= $oOrdersRows->getTotalsByNom($sMonthFrom, $nIDFirm, $nIDOffice, $sFilterEA);
			$aTemp2 		= $oOrdersRows->getTotalsByNom2($sMonthFrom, $nIDFirm, $nIDOffice, $sFilterEX);			
			//debug($aTemp2);
			if ( !empty($aTemp1) ) {
				foreach ( $aTemp1 as $v ) {
					$v['month']			= $sMonthFrom;
					$aTotalEarnings[] 	= $v;
				}
			}
			
			if ( !empty($aTemp2) ) {
				foreach ( $aTemp2 as $v ) {
					$v['month']			= $sMonthFrom;
					$aTotalExpenses[] 	= $v;
				}
			}			

						
			foreach ( $aTotalEarnings as $aVal ) {
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
			
			
			foreach ( $aTotalExpenses as $aVal ) {
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
						
			$sFileName	= "budget.xls";
		  	$sFileNameTemp = tempnam("\tmp", $sFileName);
			$oWorkbook = &new writeexcel_workbook($sFileNameTemp);
			 // Създване на worksheet
			$oWorksheet = &$oWorkbook->addworksheet( iconv("UTF-8", "CP1251", "Бюджет") );
			
			//$oWorksheet->set_landscape();
			$oWorksheet->set_paper(9);
			$oWorksheet->set_margins(0.25);
			$oWorksheet->set_margin_right(0.25);
			$oWorksheet->set_margin_bottom(0.5);
			$oWorksheet->set_footer( iconv("UTF-8", "CP1251", "Powered by IntelliSys © 2013."), 0.25);
				
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
			$oWorksheet->write_string($x, $y, iconv("UTF-8", "CP1251", "Бюджет за ".$sDate), $formatCaption );
						
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
			$oWorksheet->write_string(2, 0, iconv("UTF-8", "CP1251", "Общо приход: "), $formatTotal );	
			$oWorksheet->write_string(2, 1, iconv("UTF-8", "CP1251", sprintf("%01.0f лв.", $total_earning)), $formatCap );

			$oWorksheet->write_string(3, 0, iconv("UTF-8", "CP1251", "Общо разход: "), $formatTotal );	
			$oWorksheet->write_string(3, 1, iconv("UTF-8", "CP1251", sprintf("%01.0f лв.", $total_expense)), $formatCap );			
			
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
				
			$y	+= 2;
			$x 	= 0;	
			
			$oWorksheet->write_string( $y, 0, iconv("UTF-8", "CP1251", "РАЗХОД" ), $formatFields2 );
		 	$oWorksheet->write_string( $y, 1, iconv("UTF-8", "CP1251", "Общо" ), $formatFields2 );
		 	
		 	$y++;

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
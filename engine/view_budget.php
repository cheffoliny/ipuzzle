<?php
	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
	set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../');
		
	require_once "config/function.autoload.php";
	require_once "config/connect.inc.php";
	
	require_once "../include/php2excel/class.writeexcel_workbook.inc.php";
	require_once "../include/php2excel/class.writeexcel_worksheet.inc.php";	
	
	global $db_finance, $db_name_finance;
			
	$nIDBudget = isset($_GET['id']) ? $_GET['id'] : 0;

	$oBudget 		= new DBBudget();
	$oBudgetRows	= new DBBudgetRows();
	
	$sMonthFrom		= "";
	$aTotalEarnings	= array();
	$aTotalExpenses	= array();
	$aMonths		= array();
	$sDate			= "";	
	$arr_earnings	= array();
	$arr_expenses	= array();
	$total_earning 	= 0;
	$total_expense 	= 0;
	$aDetailTotalEA	= array();
	$aDetailTotalEX	= array();			
	$mname			= array();
	$aMonParse		= array();
			
	$aBudgetEA		= array();
	$aBudgetEX		= array();	
	
	$aBudgetEA		= $oBudget->getBudgetByID($nIDBudget, "earning");
	$aBudgetEX		= $oBudget->getBudgetByID($nIDBudget, "expense");
	
	if ( isset($aBudgetEA[0]['month']) && !empty($aBudgetEA[0]['month']) ) {
		$sMonthFrom	= substr($aBudgetEA[0]['month'], 0, 7);
	} elseif ( isset($aBudgetEX[0]['month']) && !empty($aBudgetEX[0]['month']) ) {
		$sMonthFrom	= substr($aBudgetEX[0]['month'], 0, 7);
	}

	$aMonths[] 		= $sMonthFrom;
				
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
								
	foreach ( $aBudgetEA as &$aVal ) {
		$total_earning += $aVal['sum'];
		
		if ( isset($aVal['id_nomenclature']) && ($aVal['id_nomenclature'] == -1) ) {
			$aVal['id_nomenclature'] 	= 12;
			$aVal['id_group'] 			= 13;
			$aVal['group_name'] 		= "Други";
			$aVal['nomenclature_name'] 	= "ДДС";
		}
				
		if ( isset($arr_earnings[$aVal['id_group']]) ) {
			$arr_earnings[$aVal['id_group']]['sum'] 	+= $aVal['sum'];
					
			if ( !empty($aVal['id_nomenclature']) ) {
				if ( isset($arr_earnings[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['sum']) ) {
					$arr_earnings[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['id'] 		= $aVal['id_nomenclature'];
					$arr_earnings[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['label'] 	= $aVal['nomenclature_name'];
					$arr_earnings[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['sum'] 		+= $aVal['sum'];								
				} else {
					$arr_earnings[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['id'] 		= $aVal['id_nomenclature'];
					$arr_earnings[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['label'] 	= $aVal['nomenclature_name'];
					$arr_earnings[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['sum'] 		= $aVal['sum'];							
				}
						
				if ( isset($arr_earnings[$aVal['id_group']]['children'][$aVal['id_nomenclature']][$sMonthFrom]) ) {
					$arr_earnings[$aVal['id_group']]['children'][$aVal['id_nomenclature']][$sMonthFrom]	+= $aVal['sum'];
				} else {
					$arr_earnings[$aVal['id_group']]['children'][$aVal['id_nomenclature']][$sMonthFrom]	= $aVal['sum'];
				}						
			}
		} else {
			$arr_earnings[$aVal['id_group']]['id'] 			= $aVal['id_group'];
			$arr_earnings[$aVal['id_group']]['label'] 		= $aVal['group_name'];
			$arr_earnings[$aVal['id_group']]['sum'] 		= $aVal['sum'];
					
			if ( !empty($aVal['id_nomenclature']) ) {
				$arr_earnings[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['id'] 			= $aVal['id_nomenclature'];
				$arr_earnings[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['label'] 		= $aVal['nomenclature_name'];
				$arr_earnings[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['sum'] 			= $aVal['sum'];
				$arr_earnings[$aVal['id_group']]['children'][$aVal['id_nomenclature']][$sMonthFrom]		= $aVal['sum'];
			}					
		}
				
		if ( isset($arr_earnings[$aVal['id_group']][$sMonthFrom]) ) {
			$arr_earnings[$aVal['id_group']][$sMonthFrom] 	+= $aVal['sum'];
		} else {
			$arr_earnings[$aVal['id_group']][$sMonthFrom] 	= $aVal['sum'];
		}

		if ( isset($aDetailTotalEA[$sMonthFrom]) ) {
			$aDetailTotalEA[$sMonthFrom] 	+= $aVal['sum'];
		} else {
			$aDetailTotalEA[$sMonthFrom] 	= $aVal['sum'];
		}				
	}
		
	unset($aVal);
	
	foreach ( $aBudgetEX as &$aVal ) {
		$total_expense += $aVal['sum'];
				
		if ( isset($aVal['id_nomenclature']) && ($aVal['id_nomenclature'] == -1) ) {
			$aVal['id_nomenclature'] 	= 45;
			$aVal['id_group'] 			= 8;
			$aVal['group_name'] 		= "Други";
			$aVal['nomenclature_name'] 	= "ДДС";
		}				
				
		if ( isset($arr_expenses[$aVal['id_group']]) ) {
			$arr_expenses[$aVal['id_group']]['sum'] 	+= $aVal['sum'];
					
			if ( !empty($aVal['id_nomenclature']) ) {
				if ( isset($arr_expenses[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['sum']) ) {
					$arr_expenses[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['id'] 		= $aVal['id_nomenclature'];
					$arr_expenses[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['label'] 	= $aVal['nomenclature_name'];
					$arr_expenses[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['sum'] 		+= $aVal['sum'];								
				} else {
					$arr_expenses[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['id'] 		= $aVal['id_nomenclature'];
					$arr_expenses[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['label'] 	= $aVal['nomenclature_name'];
					$arr_expenses[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['sum'] 		= $aVal['sum'];							
				}
						
				if ( isset($arr_expenses[$aVal['id_group']]['children'][$aVal['id_nomenclature']][$sMonthFrom]) ) {
					$arr_expenses[$aVal['id_group']]['children'][$aVal['id_nomenclature']][$sMonthFrom]	+= $aVal['sum'];
				} else {
					$arr_expenses[$aVal['id_group']]['children'][$aVal['id_nomenclature']][$sMonthFrom]	= $aVal['sum'];
				}						
			}
		} else {
			$arr_expenses[$aVal['id_group']]['id'] 		= $aVal['id_group'];
			$arr_expenses[$aVal['id_group']]['label'] 	= $aVal['group_name'];
			$arr_expenses[$aVal['id_group']]['sum'] 	= $aVal['sum'];
					
			if ( !empty($aVal['id_nomenclature']) ) {
				$arr_expenses[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['id'] 			= $aVal['id_nomenclature'];
				$arr_expenses[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['label'] 		= $aVal['nomenclature_name'];
				$arr_expenses[$aVal['id_group']]['children'][$aVal['id_nomenclature']]['sum'] 			= $aVal['sum'];
				$arr_expenses[$aVal['id_group']]['children'][$aVal['id_nomenclature']][$sMonthFrom]		= $aVal['sum'];
			}					
		}
				
				
		if ( isset($arr_expenses[$aVal['id_group']][$sMonthFrom]) ) {
			$arr_expenses[$aVal['id_group']][$sMonthFrom] 	+= $aVal['sum'];
		} else {
			$arr_expenses[$aVal['id_group']][$sMonthFrom] 	= $aVal['sum'];
		}

		if ( isset($aDetailTotalEX[$sMonthFrom]) ) {
			$aDetailTotalEX[$sMonthFrom] 	+= $aVal['sum'];
		} else {
			$aDetailTotalEX[$sMonthFrom] 	= $aVal['sum'];
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
	
	$sFileName	= "budgets.xls";
	$sFileNameTemp = tempnam("/tmp", $sFileName);
	$oWorkbook = &new writeexcel_workbook($sFileNameTemp);

	// Създване на worksheet
	$oWorksheet = &$oWorkbook->addworksheet( iconv("UTF-8", "CP1251", "Budget") );
			
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
						
	$oWorksheet->merge_cells($y, $x, $y + 1, $x + 1 );
	$oWorksheet->write_string($x, $y, iconv("UTF-8", "CP1251", "Бюджет за ".$sDate), $formatCaption );
						
	// Широчина на колоните
	$oWorksheet->set_column( $next, $next, 40 );
	$next++;
	$oWorksheet->set_column( $next, $next, 12 );
	$next++;
	$i = 2;
			  
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
									
		foreach ($aVal['children'] as $aValChild ) {
			$y++;
			$x = 0;		
					
			$oWorksheet->write_string( $y, $x, iconv("UTF-8", "CP1251", $aValChild['label'] ), $formatData );
			$oWorksheet->write_number( $y, $x+1, iconv("UTF-8", "CP1251", $aValChild['sum'] ), $formatFloat );	
					
			$aTest	= array();
			$aTest	= $aValChild;
			
			ksort($aTest);
			reset($aTest);
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
				
		foreach ($aVal['children'] as $aValChild ) {
			$y++;
			$x = 0;		
					
			$oWorksheet->write_string( $y, $x, iconv("UTF-8", "CP1251", $aValChild['label'] ), $formatData );
			$oWorksheet->write_number( $y, $x+1, iconv("UTF-8", "CP1251", $aValChild['sum'] ), $formatFloat );	
				
			$aTest	= array();
			$aTest	= $aValChild;
					
			ksort($aTest);
			reset($aTest);
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
			
    $fh = @fopen($sFileNameTemp, "rb");
	@fpassthru($fh);
			
	sleep(2);
		
	@unlink($sFileNameTemp);							
?>
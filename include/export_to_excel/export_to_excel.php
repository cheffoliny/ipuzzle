<?php
  /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
   *	Export_XLS - Функция, която експортва масив към ексел-ски файл.										 *
   *	Автор : Аспарух Костадинов (AI-Killer)																 *
   *																										 *
   *	Параметри:																							 *
   *	$data = array ('caption' => string, 'fields' => array(), 'totals' => array(), 'data' => array());	 *
   *																										 *
   *		$data['fields'][$row_num][$cell_num] = array ('caption' => 'текст за заглавие на колоната',		 *
   *													  'cоlspan' => 'брой колони, които заема',			 *
   *													  'rowspan' => 'брой редове, които заема');			 *
   *																										 *
   *		$data['totals'] = array(0 => 'total 1', 1 => 'total 2', ...);									 *
   *																										 *
   *		$data['data'] = array (																			 *
   *								array(0 => 'данни за първото поле', 1 => 'данни за второто поле', ...),  *
   *								array(0 => 'данни за първото поле', 1 => 'данни за второто поле', ...),  *
   *								array(0 => 'данни за първото поле', 1 => 'данни за второто поле', ...),  *
   *								....																	 *
   *							  )																			 *
   *																										 *
   *	$name = 'име на файла, в който ще се запише информацията (примерно 'export.xls')'					 *
   * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
  	
	require_once "include/php2excel/class.writeexcel_workbook.inc.php";
	require_once "include/php2excel/class.writeexcel_worksheet.inc.php";
		
	function get_col_width( $data, $col ) {
	  $width = 0;

	  foreach ($data['fields'] as $row){
		$width = strlen( $row[$col]['caption'] ) > $width ? strlen( $row[$col]['caption'] ) : $width;
	  }

	  foreach ($data['data'] as $row)
		$width = strlen( $row[$col] ) > $width ? strlen( $row[$col] ) : $width;
	  
	  return $width;
  }


  function Export_XLS ( $data, $sFileName, $caption )
  { 
	//print_r($data); 
  	$sFileNameTemp = tempnam("/tmp", $sFileName);
	$oWorkbook = new writeexcel_workbook($sFileNameTemp);
	 // Създване на worksheet
	$oWorksheet =  $oWorkbook->addworksheet( iconv("UTF-8", "CP1251", $caption) );
	
	$oWorksheet->set_landscape();
	$oWorksheet->set_paper(9);
	$oWorksheet->set_margins(0.25);
	$oWorksheet->set_margin_right(0.25);
	$oWorksheet->set_margin_bottom(0.5);
	$oWorksheet->set_footer("Powered by TELEPOL Net © 2007. http://www.telepol.net",0.25);
	
	
	// Формат за Заглавие
	$formatCaption = $oWorkbook->addformat(array(
										bold    => 1,
										italic  => 0,
										size    => 12,
										font    => 'MS Sans Serif',
										color 	=> 'blue' 
									));


	// Формат за заглавията на колоните
	$formatFields = $oWorkbook->addformat(array(
										bold    => 1,
										italic  => 0,
										size    => 9,
										font    => 'MS Sans Serif',
										color 	=> 'black', 
										border 	=> 1,
										pattern => 1, 
										fg_color=> 'silver'
									));

	
	// Формат за сборовете
	$formatTotals = $oWorkbook->addformat(array(
										color 	=> 'red', 
										font    => 'MS Sans Serif',
										border 	=> 1,
									));
									
	// Формат за информацията
	$formatData = $oWorkbook->addformat(array(
										size 	=> 9,
										color 	=> 'black', 
										font    => 'MS Sans Serif',
										border 	=> 1,
									));
	//Формати
	$formatNum = $oWorkbook->addformat(array(
										size 	=> 9,
										color 	=> 'black', 
										font    => 'MS Sans Serif',
										border 	=> 1,
									));
	$formatNum->set_align('right');
	$formatNum->set_num_format('0');
	
	$formatFloat = $oWorkbook->addformat(array(
										size 	=> 9,
										color 	=> 'black', 
										font    => 'MS Sans Serif',
										border 	=> 1,
									));
	$formatFloat->set_align('right');
	$formatFloat->set_num_format('0.000');
	
	$formatDigit = $oWorkbook->addformat(array(
										size 	=> 9,
										color 	=> 'black', 
										font    => 'MS Sans Serif',
										border 	=> 1,
									));
	$formatDigit->set_align('right');
	$formatDigit->set_num_format('0.00');
	
	$formatCurrency = $oWorkbook->addformat(array(
										size 	=> 9,
										color 	=> 'black', 
										font    => 'MS Sans Serif',
										border 	=> 1,
									));
	$formatCurrency->set_align('right');
	$formatCurrency->set_num_format('0.00 '.iconv( "UTF-8", "CP1251", $_SESSION['system']['currency']) );
	
	// Задаване ширината на колоните според информацията
	$c=0;
	foreach ( end( $data['fields'] ) as $col){
		$oWorksheet->set_column( $c,$c, get_col_width($data,$c) );
		$c++;
	}

	$oWorksheet->write_string(0, 0, iconv( "UTF-8", "CP1251", $data['caption'] ), $formatCaption);
	  
	// Начални координати 
	$y = 2;
	$x = 0;
	  
	// Записване на fields
	foreach ($data['fields'] as $k1 => $v1) {
		foreach ($data['fields'][$k1] as $k2 => $v2) {
			$oWorksheet->merge_cells($y, $x, $y + $data['fields'][$k1][$k2]['rowspan'] - 1, $x + $data['fields'][$k1][$k2]['colspan'] - 1);
			$oWorksheet->write_string($y, $x, iconv( "UTF-8", "CP1251", $data['fields'][$k1][$k2]['caption'] ), $formatFields);
			$x = $x + $data['fields'][$k1][$k2]['colspan'];
		}
		$y++;
		$x = 0;
	}
	//Записване на totals
	if (isset($data['totals'])) {
		foreach ($data['totals'] as $k => $v)
			$oWorksheet->write_string($y, $k, iconv( "UTF-8", "CP1251", $data['totals'][$k] ), $formatTotals);
		$y++;
	}
		  
	// Записване на data
	foreach ($data['data'] as $k1 => $v1) {	
		foreach ($data['data'][$k1] as $k2 => $v2)
		{
			$nDataFormat 	= DF_STRING;
			$format 		= $formatData;
			$sContent 		= $data['data'][$k1][$k2];

			if( !empty($data['fields'][0][$k2]['attributes']['DATA_FORMAT']) )
				$nDataFormat = $data['fields'][0][$k2]['attributes']['DATA_FORMAT'];
				
			switch( $nDataFormat ) 
			{
				case DF_DIGIT : 
								$format = $formatDigit;
								break;
				case DF_FLOAT : 
								$format = $formatFloat;
								break;
				case DF_NUMBER : 
								$format = $formatNum;
								break;
				case DF_CURRENCY : 
								$format = $formatCurrency;
								break;
				default : 
								$format = $formatData;
			}
			
			$oWorksheet->write($y, $k2, !empty($data['data'][$k1][$k2]) ? iconv( "UTF-8", "CP1251", $sContent ) : ' ', $format);
		}
		$y++;
	} 
		
	// Затваряне на файла 
	$oWorkbook->close();
	
	header("Content-type: application/x-msexcel;");
    header("Content-Disposition: attachment; filename=$sFileName" );
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    header("Pragma: public");
	
    $fh=fopen($sFileNameTemp, "rb");
	fpassthru($fh);
	unlink($sFileNameTemp);
  }
?>
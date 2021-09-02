<?php
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

	define( "ZERO_PADDING",8);
	
	/**
	 * @desc Функция която рекурсивно премахва обратна наклонена (backslash) от ескейпнати символи
	 *
	 * @param mixed $data
	 * @return mixed
	 */
	function stripslashes_deep($data) {
		return is_array($data) ? array_map('stripslashes_deep', $data) : stripslashes($data);
	}

	/**
	 * @desc Функция която рекурсивно ескейпва единични кавички, двойни капички и обратна наклонена (backslash) с обратни наклонени (backslashes)
	 *
	 * @param mixed $data
	 * @return mixed
	 */
	function addslashes_deep($data) {
		return is_array($data) ? array_map('addslashes_deep', $data) : addslashes($data);
	}

	function htmlspecialchars_deep($data) {
		return is_array($data) ? array_map('htmlspecialchars_deep', $data) : htmlspecialchars($data);
	}

	/**
	 * @desc Функция която рекурсивно обработва подаден масив (стринг) за нуждите на XML конвертирате - UTF-8, specialchars, slashes
	 *
	 * @param mixed $data
	 * @return mixed
	 */
	function xml_convert_chars_deep ($data) {
		return is_array($data) ? array_map('xml_convert_chars_deep', $data) : addslashes(iconv("CP1251","UTF-8",htmlspecialchars($data)));
	}
	
	/**
	 * @desc Функция която разцепва низ на части с избрана дължина и ги слепва с посочен низ
	 *
	 * @param string $string
	 * @param string $glue
	 * @param int $rowsize
	 * @return string
	 */
	function textwrap($string, $glue = "<br />", $rowsize = 80) {
		return strlen($string) > $rowsize ? substr($string, 0, $rowsize) . $glue . textwrap(substr($string, $rowsize), $glue, $rowsize) : $string;
	}
	
	function utf8decode_deep($value) {
		return is_array($value) ? array_map('utf8decode_deep', $value) : iconv("UTF-8","CP1251",$value);
	}
	
	function utf8encode_deep($value) {
		return is_array($value) ? array_map('utf8encode_deep', $value) : iconv("CP1251","UTF-8",$value);
	}

	function array_multi_csort() {
		$args = func_get_args();
		$marray = array_shift($args);
		$i = 0;

  		$msortline = "return(array_multisort(";
		foreach ($args as $arg) {
			$i++;
			if (is_string($arg)) {
				foreach ($marray as $row) {
					$sortarr[$i][] = $row[$arg];
				}
			}
			else {
				$sortarr[$i] = $arg;
			}
			$msortline .= "\$sortarr[".$i."],";
		}
	
		$msortline .= "\$marray));";
		eval($msortline);
		return $marray;
	}
	
	function SQL_get_tables(&$db_eol, $basename, $pattern, $order = "DESC") {
		$sbase = preg_replace("/(_)/","\\\\$1",$basename,-1);
		$order = strtoupper($order) == "ASC" ? "ASC" : "DESC";
		$rs = $db_eol->Execute("SHOW TABLES LIKE '{$basename}{$pattern}'");
		$tables = array();
		while(!$rs->EOF) {
			$dat = $rs->fields;
			sort($dat);
			$dat = $dat[0];
			array_push($tables,$dat);
			$rs->MoveNext();
		}
		if (strtoupper($order) == "ASC") sort($tables);
		else rsort($tables);

		return count($tables) ? $tables : false;
	}

	function SQL_union_search(&$db_eol, $sql, $basename, $pattern, $ord_field, $order, $limit = 0) {
		$tables = SQL_get_tables($db_eol,$basename,$pattern,"ASC");
		$result = array();
		$rsv = $db_eol->Execute("SELECT SUBSTRING(version(),1,1) >= 4 AS ver");
		$version = $rsv && !$rsv->EOF ? $rsv->fields['ver'] : 0;
		if ($version) {
			if ($tables) {
				if (count($tables) > 1) {
					$datasql = "";
					for($i=0;$i<count($tables)-1;$i++)
						$datasql .= preg_replace("/<%tablename%>/",$tables[$i],$sql)." UNION ";
					$datasql .= preg_replace("/<%tablename%>/",$tables[count($tables)-1],$sql).($ord_field != "" ? " ORDER BY $ord_field $order" : "");
				}
				else {
					$datasql = preg_replace("/<%tablename%>/",$tables[0],$sql).($ord_field != "" ? " ORDER BY $ord_field $order" : "");
				}
			}
			else return false;
			$rs = $db_eol->Execute($datasql);
			return $rs && !$rs->EOF ? $rs->GetArray() : false;
		}
		else {
			$tmp_data = array();
			if ($tables) {
				for($i=0;$i<count($tables);$i++) {
					$rs = $db_eol->Execute(preg_replace("/<%tablename%>/",$tables[$i],$sql));
					$tmp_data = array_merge($tmp_data,$rs && !$rs->EOF ? $rs->GetArray() : array());
				}
			}
			else return false;
			return count($tmp_data) ? ($ord_field ? array_multi_csort($tmp_data,$ord_field,strtoupper($order) == "ASC" ? SORT_ASC : SORT_DESC, SORT_NUMERIC) : $tmp_data) : false;
		}
	}


	function u_search(&$db_eol, $sql, $basename, $pattern) {
		$tables = SQL_get_tables($db_eol, $basename, $pattern, "ASC");
		$result = array();
		$rsv = $db_eol->Execute("SELECT SUBSTRING(version(), 1, 1) >= 4 AS ver");
		$version = $rsv && !$rsv->EOF ? $rsv->fields['ver'] : 0;
		if ($version) {
			if ($tables) {
				if (count($tables) > 1) {
					$datasql = "";
					for($i = 0; $i < count($tables) - 1; $i++)
						$datasql .= preg_replace("/<%tablename%>/", $tables[$i], $sql)." UNION ";
					$datasql .= preg_replace("/<%tablename%>/", $tables[count($tables) - 1], $sql);
				}
				else {
					$datasql = preg_replace("/<%tablename%>/", $tables[0], $sql);
				}
			}
			else return false;
			$rs = $db_eol->Execute($datasql);
			if (!$rs) {
				return false;
			} else {
				$cnt = 0;
				while(!$rs->EOF) {
					$cnt++;
					$rs->MoveNext();
				}
				return $cnt;
			}

		}
		else {
			$tmp_data = array();
			if ($tables) {
				for($i = 0; $i < count($tables); $i++) {
					$rs = $db_eol->Execute(preg_replace("/<%tablename%>/", $tables[$i], $sql));
					$tmp_data = array_merge($tmp_data, $rs && !$rs->EOF ? $rs->GetArray() : array());
				}
			}
			else return false;
			return count($tmp_data) ? $tmp_data : false;
		}
	}

function debug($value,$play=true){
	if($play){
		echo "<pre>"; print_r($value); echo "</pre>"; 
	} else {
		return "<pre>".print_r($value,true)."</pre>";
	}
}

# funkciq za syzdavane na nova tablica po original s ili bez nachalna stojnost za AUTOINCREMENT
	#
	function copy_table(&$db_eol, $origin, $destination, $increment = 0) {
		$sorigin = preg_replace("/(_)/","\\\\$1",$origin,-1);
		$rs = $db_eol->Execute("SHOW TABLES LIKE '$sorigin'");
		if (!$rs || $rs->EOF) return false;

		$sdest = preg_replace("/(_)/","\\\\$1",$destination,-1);
		$rs = $db_eol->Execute("SHOW TABLES LIKE '$sdest'");
		if ($rs && !$rs->EOF) return true;

		$rs_fields = $db_eol->Execute("SHOW FIELDS FROM $origin");
		$rs_keys = $db_eol->Execute("SHOW KEYS FROM $origin");
		$sql = "CREATE TABLE {$destination} (";
		while(!$rs_fields->EOF) {
			$fld = $rs_fields->fields;
			$sql .= "{$fld['Field']} {$fld['Type']}".($fld['Default'] != '' ? " default '{$fld['Default']}'" : "").(!$fld['Null'] ? " NOT NULL" : "").($fld['Extra'] != '' ? " {$fld['Extra']}" : "").",";
			$rs_fields->MoveNext();
		}
		while(!$rs_keys->EOF) {
			$fld = $rs_keys->fields;
			$sql .= ($fld['Key_name'] == 'PRIMARY' ? "{$fld['Key_name']} KEY " : ($fld['Non_unique'] ? "KEY {$fld['Key_name']} " : "UNIQUE KEY {$fld['Key_name']} "))."({$fld['Column_name']}),";
			$rs_keys->MoveNext();
		}
		$sql = substr($sql,0,-1).")";
		if ($increment) $sql .= " AUTO_INCREMENT={$increment}";
		$rs = $db_eol->Execute($sql);
		return $rs ? true : false;
	}

	# proverka i syzdavane na periodichni tablici ot vida table_name_(?)
	#
	function SQL_check_and_create(&$db_eol, $basename, $date, $increment = false, $field = "id", $interval = 1000000) {
		$sbase = preg_replace("/(_)/","\\\\$1",$basename,-1);
		$rs = $db_eol->Execute("SHOW TABLES LIKE '{$sbase}\_{$date}'");
		if ($rs && !$rs->EOF) return true;
		// novosyzdadenite tablici shte prodyljavat autoindex-a
		if ($increment) {
			$rs = $db_eol->Execute("SHOW TABLES LIKE '{$sbase}\_".str_repeat("_",strlen($date))."'");
			// ima li tablici ot tozi vid ?
			if ($rs && !$rs->EOF) {
				// ima, vzimame gi i gi sortirame v namalqvasht red
				$tables = array();
				while(!$rs->EOF) {
					$dat = $rs->fields;
					sort($dat);
					array_push($tables,$dat[0]);
					$rs->MoveNext();
				}
				rsort($tables);
				$rtbl = $basename."_".$date;
				if($tables[0] < $rtbl) {
					// poslednata tablica e predi tazi po porednost
					$tbl = $tables[0];
					$rs = $db_eol->Execute("SELECT min({$field}) as field FROM {$tbl}");
					$afield = $rs && !$rs->EOF ? $rs->fields['field'] + $interval : 0;
				}
				else {
					for($i=1;$i<count($tables);$i++) {
						// tyrsim mqstoto na tablicata
            if($tables[$i] < $rtbl) break;
					}
					if($i >= count($tables)) $i--;
					if($tables[$i] > $rtbl) {
						// tablicata e kato porednost predi vsi4ki ostanali ! problem.
            $rs = $db_eol->Execute("SELECT min({$field}) as field FROM {$tables[$i]}");
						$fres = $rs && !$rs->EOF ? $rs->fields['field'] : 0;
						$afield = $fres ? floor($fres/2) : 0;
						// ako nachalniq index na pyrvata tablica > 0 to nachalniqt na novata e /2
					}
					else {
						$rs = $db_eol->Execute("SELECT min({$field}) as field_min, max({$field}) as field_max FROM {$tables[$i]}");
						// rezultata ot tablicata koqto shte se namira predi novata
						$i--;
            $rs2 = $db_eol->Execute("SELECT min({$field}) as field_min FROM {$tables[$i]}");
						// rezultata ot tablicata koqto shte se namira sled novata
            $distance = floor(($rs2->fields['field_min'] - $rs->fields['field_min'])/2);
						if($distance > ($rs->fields['field_max'] - $rs->fields['field_min'])*3/2) $afield = $rs2->fields['field_min'] - $distance;
						// sredata e po-golqma ot razlkata ot maximalniq i minimalniq element + 50%
						else $afield = $rs2->fields['field'] - floor(($rs2->fields['field'] - $rs->fields['field_min'])/2);
						// sredata se opredelq kato polovinata ot razlikata ot nachaloto na sledvashtata i kraq na predhodnata tablica
					}
				}
			}
			// nqma - zapochva ot nachalo
			else $afield = $interval;
		}
		else $afield = 0;
		copy_table($db_eol,"{$basename}_origin","{$basename}_{$date}",$afield);
	}

		/* Function Count Sub Priority */
		function count_level($arr,$id)
		{
			if ($arr[$id]['id_parent'] > 0)
			{				
				$GLOBALS['c']++;
				count_level($arr,$arr[$id]['id_parent']);
			} 
			else 
				$GLOBALS['c']++;
			return $GLOBALS['c'];
		};

		/* Function Sort By Priority */
		function sort_data_by_levels($source,&$destination,$id)
		{
			$check = false;
			$num = count($destination);
			for ($i=0;$i++;$i==$num)
				foreach ($source as $k=>$v)
					if ($source[$k] == $destination[$i])
						$check = true;
			if (!$check)
				$destination[] = $source[$id];
			$num = count($source);
			foreach ($source as $k=>$v)
			{
				if ($source[$k]['id_parent'] == $id)
				{
					$destinaton[] = $source[$k];
					sort_data_by_levels($source,$destination,$source[$k]['id']);
				}
			}
			unset($source[$id]);
		};

	/* Sort Data By Levels */
	function sort_data(&$data)
	{
		if( !is_array( $data ) ) 
			return; 
		
		$id_data = array();
		$new_data = array();
		$new_data = $data;

		foreach ($data as $key => $item)
			$id_data[$item['id']] = $item;

		/* Проверка за Администратор */
/*		foreach ($new_data as $k => $v)
			if (!isset($new_data[$id_data[$k]['id_parent']]))
		{
				$new_data[$k]['id_parent'] = 0;				
		}
		foreach ($id_data as $k => $v)
		{
			if (!isset($id_data[$id_data[$k]['id_parent']]))
				$id_data[$k]['id_parent'] = 0;
		}*/

		/* Set Sub Priority */
		foreach ($new_data as $k=>$v)
		{
			$new_data[$k]['level'] = 0;
			if ($v['id_parent'] <> 0)
			{			
				$GLOBALS['c'] = 0;
				$new_data[$k]['level'] = count_level($id_data,$v['id_parent']);
				unset($GLOBALS['c']);
			}	
		}

		/* Set Sort Data */
		foreach ($new_data as $key => $item)
			$new_data[$item['id']] = $item;

		$d = array();

		foreach ($new_data as $k=>$v)
		{
			if ($v['id_parent'] == 0)
			{
				sort_data_by_levels($new_data,$d,$v['id']);
			}
		}
		$data = $d;		
	}
	/* подготвя масив за показване в падащо меню */
	function set_menu(&$data)
	{
		if( !is_array( $data ) )
			return;
			
		foreach ($data as $k=>$v)
		{
			$space = '';
			$num = $data[$k]['level'];			
			for ($i=0;$i<$num;$i++)
				$space .='&nbsp;&nbsp;';			
			$data[$k]['menu_name'] = $space.$data[$k]['name'];		
		}
	}

	/* изчисляване на сумата на под-разходите */
	function calculate_sum($razhodi,$id_office)
	{
		foreach ($razhodi as $k => $v)
		{
		}
	}

	/* проверка дали прихода е главен */
	function ismaster($razhod,$id)
	{
		$is_master = false;
		foreach ($razhod as $k => $v)
			if ($razhod[$k]['id_parent'] == $id)
				$is_master = true;
		return $is_master;
	}	
	
	
	/**
	 * @desc Функция escape-ва JavaScript
	 *
	 * @param string $content
	 * @return string
	 *
	 * author: dido2k
	 */
	 
	function javascriptescape_deep( $content )
	{
		// escape javascript characters
		if( is_array( $content ) )
			return array_map('javascriptescape_deep', $content);
		
		$content = preg_replace("/('|\"|\\\)/s", "\\\\$1", $content);
		$content = preg_replace("/(\r\n|\r|\n)/s", "\\n", $content);

		return $content;
	}
	
	
	/**
	 * @desc Функцията преобразува дата от формат ДД/ММ/ГГГГ във unix timestamp формат
	 *
	 * @param string $sDate дата във формат ДД/ММ/ГГГГ
	 * @return int unix timestamp при успех, или 0 при неуспех
	 * @author: dido2k
	 */
	 
	function jsDateToTimestamp( $sDate )
	{	
		if( !empty( $sDate ) )
		{
			@list($d, $m, $y) = explode(".", $sDate);
			
			if( @checkdate($m, $d, $y) )
				return mktime(0, 0, 0, $m, $d, $y);
		}
		
		return 0;
	}
	
	function jsDateEndToTimestamp( $sDate )
	{	
		if( !empty( $sDate ) )
		{
			@list($d, $m, $y) = explode(".", $sDate);
			
			if( @checkdate($m, $d, $y) )
				return mktime(23, 59, 59, $m, $d, $y);
		}
		
		return 0;
	}
	
	/**
	 * Функцията преобразува дата от формат ДД.ММ.ГГГГ в SQL ГГГГ-ММ-ДД формат.
	 *
	 * @param string $sDate
	 * @return string
	 */
	
	function jsDateToMySQLDate( $sDate )
	{
		if( !empty( $sDate ) && strlen( $sDate ) == 10 )
		{
			$nDate = jsDateToTimestamp( $sDate );
			
			return date( "Y-m-d", $nDate );
		}
		
		return "0000-00-00";
	}
	
	/**
	 * @desc Функцията преобразува datetime от MySQL формат във unix timestamp формат
	 *
	 * @param string $sDateTime datetime във MySQL формат
	 * @return int unix timestamp при успех, или 0 при неуспех
	 * @author: dido2k
	 */
	 
	function mysqlDateToTimestamp( $sDateTime )
	{
		if( !empty( $sDateTime ) && !in_array($sDateTime, array('0000-00-00 00:00:00', '0000-00-00')) )
		{
			if( ($nRes = strtotime( $sDateTime ) ) != -1 )
				return $nRes;
		}
		
		return 0;
	}
	
	
	/**
	 * @desc Функцията преобразува datetime от MySQL формат във формат ДД/ММ/ГГГГ
	 *
	 * @param string $sDateTime datetime във MySQL формат
	 * @return string date формат ДД/ММ/ГГГГ при успех, или празен стринг при неуспех
	 * @author: dido2k
	 */
	
	function mysqlDateToJsDate( $sDateTime )
	{
		if( !empty( $sDateTime ) )
		{
			if( ($nTime = mysqlDateToTimestamp( $sDateTime )) != 0 )
				return date("d.m.Y", $nTime);
		}
		
		return "";
	}
	
	/**
	 * @desc Функцията преобразува datetime от MySQL формат във формат ЧЧ:ММ:СС
	 *
	 * @param string $sDateTime datetime във MySQL формат
	 * @return string date формат ЧЧ:ММ:СС при успех, или празен стринг при неуспех
	 * @author: dido2k
	 */
	 
	function mysqlDateToJsTime( $sDateTime )
	{
		if( ($nTime = mysqlDateToTimestamp( $sDateTime )) != 0 )
			return date("H:i:s", $nTime);
		
		return "";
	}
	
	/**
	 * @desc Функцията преобразува timstamp MySQL формат ( YYYY-MM-DD HH:II:SS )
	 *
	 * @param string $nTimestamp
	 * 		- Ако се подаде NULL се взима текшутото време
	 * 		- Ако се подаде 0 се връща нулево време "0000-00-00 00:00:00";
	 * 		- Ако се подаде друг timestamp се връща време
	 * @return string mysql datetime формат YYYY-MM-DD HH:II:SS при успех
	 * @author: dido2k
	 */
	
	function timestampToMysqlDateTime( $nTimestamp = NULL )
	{
		if( $nTimestamp === NULL )
			$nTimestamp = time();
		else if( $nTimestamp === 0 )
			return "0000-00-00 00:00:00";
		
		return date("Y-m-d H:i:s", $nTimestamp);
	}
	
	
	/**
	 * @desc Функцията изчислява разликата в календарни дни между 2 timestamp-a
	 *
	 */
	
	function dateDiffCalendarDays($nTimestamp1, $nTimestamp2)
	{
		$nTime1 = mktime(0, 0, 0, date('m', $nTimestamp1), date('d', $nTimestamp1), date('Y', $nTimestamp1));
		$nTime2 = mktime(0, 0, 0, date('m', $nTimestamp2), date('d', $nTimestamp2), date('Y', $nTimestamp2));
		
		return  ( ( ( $nTime1 - $nTime2 ) / ( 24 * 60 * 60 ) ) );
	}
	
	/**
	 * @desc Функцията добавя водещи нули към число или стринг
	 *
	 * @param number $nZeros - общ брой на цифрите, заедно с водещите нули
	 * @param number $nNumber - числото, което ще се форматира
	 * @return string - стринг с водещи нули ("000012345");
	 * @author: boro
	 */
	function zero_padding ($nNumber, $nZeros=ZEROPADDING) {
		return sprintf("%0".$nZeros."s", $nNumber);
	}
	
	/**
	 * @desc Функция, която конвертира MySQL формата на подобен на date()
	 *
	 * @param string 	$sParam - подавана дата във вида: yyyy-mm-dd
	 * @param string 	$sFormat, формат на датата, съобразно date()
	 * @param boolean 	$bCheckNullDate - дали да връща NULL при дата 01.01.1970
	 * @return string - дата 
	 * @author: boro
	 */
	function ConvertMySQLDate ($sFormat, $sParam, $bCheckNullDate=false) {
		if (!$sFormat || !$sParam)
			return null;
			
		$aDataTime = explode (" ", $sParam);
		$aDate = explode ("-", $aDataTime[0]);
		if (isset ($aDataTime[1])){
			$aTime = explode (":", $aDataTime[1]);
			if (count(@$aTime)==3)
			$sResult = mktime($aTime[0], $aTime[1], $aTime[2], $aDate[1], $aDate[2], $aDate[0]);
		}
		else 
			$sResult = mktime(0, 0, 0, $aDate[1], $aDate[2], $aDate[0]);
				
		if ($bCheckNullDate && $sResult<=943912800)
			return null;	
		return date($sFormat, $sResult);
	}
	
	/**
	 * @desc Функция, която порверява валидността на дата
	 *
	 * @param string 	$sParam - проверявана дата, задължително от вида dd.mm.yyyy (с произвилен сепаратор)
	 * @param string 	$sSeparator - ".", "/".....
	 * @return boolean  - true-датата е валидна 
	 * @author: boro
	 */
	function checkValidDate ($sParam, $sSeparator=".") {
		$aDate = explode($sSeparator, $sParam);
		if (count ($aDate)!=3)
			return false;
			
			
		if(is_numeric($aDate[0]) && is_numeric($aDate[1]) && is_numeric($aDate[2])) {	
			return checkdate($aDate[1], $aDate[0], $aDate[2]);
		}
		
		return false;
	}
	
	/**
	 * @desc Функция, която конвертира дата от вида dd.mm.yyyy в timestamp, с произволен сепаратор
	 *
	 * @param string 	$sParam - проверявана дата, задължително от вида dd.mm.yyyy (с произвилен сепаратор)
	 * @param string 	$sSeparator - ".", "/".....
	 * @return int  	timestamp
	 * @author: boro
	 */
	function convertDateInToTimestamp ($sParam, $sSeparator=".") {
		$aDate = explode($sSeparator, $sParam);
		if (count ($aDate)!=3)
			return null; 
		return mktime(0,0,0,$aDate[1], $aDate[0], $aDate[2]);
	}
	
	
	/**
	 * Функция, отчислява ДДС от сума и връща сумата без ДДС
	 *
	 * @param double 	$dSum - проверявана дата, задължително от вида dd.mm.yyyy (с произвилен сепаратор)
	 * @param double 	$dDDS - ДДС като процент
	 * @return int  	сума без ДДС
	 * @author: dido2k
	 */
	 
	function discountDDS($dSum, $dDDS) 
	{
		$dSum = (double)$dSum;
		$dDDS = (double)$dDDS;
		
		return (double)($dSum / (1 + $dDDS / 100 ));
	}
	
	/**
	 * Функция, която връща броя на работните дни от даден месец и година.
	 * Ако годината и месеца са текущите връща работните дни до днешния ден включително
	 *
	 * @param int $nYear Година
	 * @param int $nMonth Месец
	 * @return int Брой работни дни
	 * @author: dido2k
	 */
	function getWorkDaysUntilToday($nYear, $nMonth)
	{
		$nTimestamp = mktime(0, 0, 0, $nMonth, 1, $nYear);
		
		$nDay = ( date("mY") == sprintf("%02u%02u", $nMonth, $nYear)) ? date("j") : date("t", $nTimestamp);
		
		$nTimestamp = mktime(0, 0, 0, $nMonth, $nDay, $nYear);
        
        $nWorkDays = 0;
        
        while( $nDay )
        {
            switch( date("w", mktime(0, 0, 0, $nMonth, $nDay, $nYear) ) )
            {
                case 0:
                case 6:
                    break;
                default:
                    $nWorkDays++;
            }
            
            $nDay--;
        }    
        
        return $nWorkDays;
	}
	
	/**
	 * Функция за парсване номер на консуматив от вида ABC00123
	 *
	 * @param string 	$sDocNumber - Вход: 	Номер на документа 		пр. ABC1234567
	 * @param string 	$sPrefix	- Резултат: Префикс-Главни букви 	пр. ABC
	 * @param string 	$sSuffix	- Резултат: Суфикс без водещи нули	пр. 123 (
	 * @param string	$sType		- Резултат: Тип на док. ако е извесетн пр. PACK - главни букви
	 * @param string	$sErrMsg	- Резултат: Съобщение, ако парването е неуспешно
	 * @return bool		- true при успешен парсинг
	 * @author Boro
	 */
	function document_parse ($sDocNumber, &$sPrefix, &$sSuffix, &$sType, &$sErrMsg) {
		$sErrMsg = '';
	
		// Проверка на целия израз
		$nGlobalResult  = preg_match ("@^[a-zA-Z]*[0-9]+$@", $sDocNumber, $aGlobalResult);	
	
		if (!isset($aGlobalResult[0]) || empty($aGlobalResult[0])) {
			$sErrMsg = "Некоректен номер на документ!";
			return false; 
		}
		
		// Парсване на префикса
		$nPrefixCheck 	= preg_match ("@^[a-zA-Z]*@", $sDocNumber, $aPrefix);
		
		// Парсване на суфикса
		$nSuffixCheck	= preg_match ("@[0-9]+$@", $sDocNumber, $aSuffix);
		
		if (!count ($aSuffix) || isset($aSuffix[0]) && $aSuffix[0]<=0) { // некоректно число на кода
			$sErrMsg = "Числото на кода е некоректно!";
			return false; 
		}
	
		$sPrefix 	= isset($aPrefix[0]) ? strtoupper($aPrefix[0]) : '';
		$sSuffix 	= isset($aSuffix[0]) ? $aSuffix[0] : '';
		
		$oConsumeTypes = new DBConsumeTypes();
		$aConsumeTypes = array();
	
		if (($nResult=$oConsumeTypes->getAllConsumeTypesAssocType($aConsumeTypes, $aConsumeTypesId))!=DBAPI_ERR_SUCCESS) {
			$sErrMsg = "Грешка при зареждане типовете консумативи!";
			return false;
		}
		
		if (!isset($aConsumeTypes[$sPrefix])) { // непознат тип в базата данни
			$sErrMsg = "Типът на консуматива е непознат за системата!";
			return false; 
		}
	
		$sType 		= strtoupper($aConsumeTypes[$sPrefix]);
	
		if (!$nPrefixCheck*$nSuffixCheck) {
			$sErrMsg = "Форматът на кода на документа е некоректен!";
			return false;
		}
		return true;
	}
	
	
	/*
		Преброява почивните и работните дни в дадения период;
		
		$start  - начално време ( mysql datetime или timestamp )
		$stop   - крайно  време ( mysql datetime или timestamp )
		$result - какъв резултат да върне ( 'weekends', 'workdays', 'both' -> Връща масив ['weekends'], ['workdays'] )
		
	*/
	function getWorkDays( $start, $stop , $return = 'workdays', $nWithHolidays = false ){
		
		global $db_eol;
		
		
		if( is_numeric($start))
		{
			$start = date('Y-m-d H-i-s', $start);
		}
		
		if( is_numeric($stop) )
		{
			$stop  = date('Y-m-d H-i-s', $stop );	
		}
		
		
		$start = split('[- :]', $start);
		
		$start = mktime( 0,	0, 0, $start[1], $start[2], $start[0] );
					  

		$stop = split('[- :]', $stop);
		
		$stop = mktime( 0, 0, 0, $stop[1], $stop[2], $stop[0] );
		
		
		$tmp 	  = array();
		$tmp 	  = getdate($start); // Вземаме инфо за датата ден месец година и т.н.
		$StartDay = $tmp['wday']; // Вземаме номера на деня 0 - Неделя, 6 - Събота
		
		$nHolidays = 0;
		if( $nWithHolidays !== false )
		{
			$rs = $db_eol->Execute("SELECT date_reception as date FROM holidays WHERE ( date_reception BETWEEN DATE(FROM_UNIXTIME($start)) AND DATE(FROM_UNIXTIME($stop)) )");
			
			$aHolidays = array();
			$aHolidays = $rs->GetArray();
			
			if( !empty($aHolidays))
			{
				
				foreach($aHolidays as $key => $val)
				{
					$nISWeekend = getWorkDays( $aHolidays[$key]['date'].' 00:00:00', $aHolidays[$key]['date'].' 00:00:00', 'weekends');
					
					if( $nISWeekend == 0 )
						$nHolidays++;
				}
				
			}
		
		}
		
		$nWeekends = 0 + $nHolidays;
		$nWorkDays = 0 - $nHolidays;
		
		while( $start <= $stop )
		{
			
			if( $StartDay > 6 )
				$StartDay = 0;
			
			if( in_array($StartDay, array(0,6)) )
				$nWeekends++;
			else
				$nWorkDays++;
			
			$start += 86400;
			$StartDay++;
			
		}
		
		switch ( $return ){
			
			case 'workdays':
				return $nWorkDays;
				break;
							
			case 'weekends':
				return $nWeekends;
				break;
				
			case 'both':
				return array( 'weekends' => $nWeekends,
							  'workdays' => $nWorkDays  ); 
				break;
				
			default:
				return $nWorkDays;
		}
	}
	
	function sql_not_empty( $aWhere, $bHasBefore, $bAddWhere = false )
	{
		if( !empty($aWhere) )
		{
			
			$sWhere = "";
			$sWhere = implode(' AND ', $aWhere );
			
			if( $bHasBefore )
				return ' AND ' . $sWhere . ' ';
			elseif( $bAddWhere == false )
				return $sWhere . ' ';
			else 
				return "WHERE \n " . $sWhere . " ";
				
		}else{
			return '';
		}
	}
	
	// Функция за конвертиране на кирилица в фонетичен формат
	function convertCyr2Pho( $sString ) 
	{
		$aConv = array();
		$aConv['А'] = 'A';
		$aConv['Б'] = 'B';
		$aConv['В'] = 'V';
		$aConv['Г'] = 'G';
		$aConv['Д'] = 'D';
		$aConv['Е'] = 'E';
		$aConv['Ж'] = 'ZH';
		$aConv['З'] = 'Z';
		$aConv['И'] = 'I';
		$aConv['Й'] = 'J';
		$aConv['К'] = 'K';
		$aConv['Л'] = 'L';
		$aConv['М'] = 'M';
		$aConv['Н'] = 'N';
		$aConv['О'] = 'O';
		$aConv['П'] = 'P';
		$aConv['Р'] = 'R';
		$aConv['С'] = 'S';
		$aConv['Т'] = 'T';
		$aConv['У'] = 'U';
		$aConv['Ф'] = 'F';
		$aConv['Х'] = 'H';
		$aConv['Ц'] = 'C';
		$aConv['Ч'] = 'TCH';
		$aConv['Ш'] = 'SH';
		$aConv['Щ'] = 'ST';
		$aConv['Ь'] = 'A';
		$aConv['Ъ'] = 'A';
		$aConv['Ю'] = 'JU';
		$aConv['Я'] = 'IA';
		$aConv['а'] = 'a';
		$aConv['б'] = 'b';
		$aConv['в'] = 'v';
		$aConv['г'] = 'g';
		$aConv['д'] = 'd';
		$aConv['е'] = 'e';
		$aConv['ж'] = 'zh';
		$aConv['з'] = 'z';
		$aConv['и'] = 'i';
		$aConv['й'] = 'j';
		$aConv['к'] = 'k';
		$aConv['л'] = 'l';
		$aConv['м'] = 'm';
		$aConv['н'] = 'n';
		$aConv['о'] = 'o';
		$aConv['п'] = 'p';
		$aConv['р'] = 'r';
		$aConv['с'] = 's';
		$aConv['т'] = 't';
		$aConv['у'] = 'u';
		$aConv['ф'] = 'f';
		$aConv['х'] = 'h';
		$aConv['ц'] = 'c';
		$aConv['ч'] = 'tch';
		$aConv['ш'] = 'sh';
		$aConv['щ'] = 'st';
		$aConv['ь'] = 'a';
		$aConv['ъ'] = 'a';
		$aConv['ю'] = 'ju';
		$aConv['я'] = 'ia';
		
		return str_replace( array_keys( $aConv ), array_values( $aConv ), $sString);
	}
	
	function in( $mInput, $mValues )
	{
		if( !is_array($mValues) )
			$mValues = explode( ',', $mValues );
		
		if( in_array($mInput, $mValues) )
			return TRUE;
			
		return FALSE;
	}
	
	
/**
 * Изтриване на файлове преди определена дата в директория
 *
 * @param string $dirName					- относителен или абсолютен път до директорията
 * @param int $nBeforeTimeStamp				- TIMESTAMP време, до което файловете ще се изтриват
 * @author Boro: 04.02.2007
 */
function deleteOldFiles($dirName, $nBeforeTimeStamp=0) {
   $d = dir($dirName);

  if ($nBeforeTimeStamp<0)
  	$nBeforeTimeStamp = time();
   
  while($entry = $d->read())
  	if (!in_array($entry, array('.', '..')) && filemtime($dirName."/".$entry) <= $nBeforeTimeStamp)
   		unlink($dirName."/".$entry);
   		
   $d->close();
}

/**
 * 	Форматиране на сума
 * 	@author dido2k	
 */

function formatMoney( $mSum )
{
	return sprintf("%.2f", round( doubleval( $mSum ), 2));
}

/**
 *  Функцията връща текущото време като timestmap + милисекунци
 */

function miliseconds()
{
	return round( microtime( true ), 3 ) * 1000;
}


/**
 * конвертира стринг в главни букви
 *
 * @param str $sString
 * @return str
 * @author Boro
 */
function strtoupper_utf8($sString){
	$sString = str_replace('я', '~%1%~', $sString);
	$sString = str_replace('ч', '~%2%~', $sString);
	
	$sString=iconv("UTF-8", "CP1251", $sString);
	$sString=strtoupper($sString);
	$sString=iconv("CP1251", "UTF-8", $sString);

	$sString = str_replace('~%1%~', 'Я', $sString);
	$sString = str_replace('~%2%~', 'Ч', $sString);
	
	return $sString;
} 

/**
 * Превръща число с максимална дължина 4 цифри, в стринг с числото в думи.
 *
 * @param int $nDigit
 * @return string
 * @author Mihail Dimitrov
 */
function convertDigitToText( $nDigit )
{
	$bAndAdded = false;
	$sResponse = "";
	
	$nDigit = strrev( $nDigit );
	
	if( isset( $nDigit[3] ) )
	{
		switch( $nDigit[3] )
		{
			case 1: $sResponse = "хиляда "; break;
			case 2: $sResponse = "две хиляди "; break;
			case 3: $sResponse = "три хиляди "; break;
			case 4: $sResponse = "четири хиляди "; break;
			case 5: $sResponse = "пет хиляди "; break;
			case 6: $sResponse = "шест хиляди "; break;
			case 7: $sResponse = "седем хиляди "; break;
			case 8: $sResponse = "осем хиляди "; break;
			case 9: $sResponse = "девет хиляди "; break;
		}
		
		if( $nDigit[0] == 0 && $nDigit[1] == 0 && $nDigit[2] != 0 && !$bAndAdded )
		{
			$sResponse .= "и ";
			$bAndAdded = true;
		}
	}
	
	if( isset( $nDigit[2] ) )
	{
		switch( $nDigit[2] )
		{
			case 1: $sResponse .= "сто "; break;
			case 2: $sResponse .= "двеста "; break;
			case 3: $sResponse .= "триста "; break;
			case 4: $sResponse .= "четиристотин "; break;
			case 5: $sResponse .= "петстотин "; break;
			case 6: $sResponse .= "шестстотин "; break;
			case 7: $sResponse .= "седемстотин "; break;
			case 8: $sResponse .= "осемстотин "; break;
			case 9: $sResponse .= "деветстотин "; break;
		}
		
		if( $nDigit[0] == 0 && $nDigit[1] != 0 && !$bAndAdded )
		{
			$sResponse .= "и ";
			$bAndAdded = true;
		}
	}
	
	if( isset( $nDigit[1] ) )
	{
		if( $nDigit[1] != 1 )
		{
			switch( $nDigit[1] )
			{
				case 2: $sResponse .= "двадесет"; break;
				case 3: $sResponse .= "тридесет"; break;
				case 4: $sResponse .= "четиридесет"; break;
				case 5: $sResponse .= "петдесет"; break;
				case 6: $sResponse .= "шестдесет"; break;
				case 7: $sResponse .= "седемдесет"; break;
				case 8: $sResponse .= "осемдесет"; break;
				case 9: $sResponse .= "деветдесет"; break;
			}
			if( $nDigit[0] != 0 )
			{
				switch( $nDigit[0] )
				{
					case 1: $sResponse .= " и един"; break;
					case 2: $sResponse .= " и два"; break;
					case 3: $sResponse .= " и три"; break;
					case 4: $sResponse .= " и четири"; break;
					case 5: $sResponse .= " и пет"; break;
					case 6: $sResponse .= " и шест"; break;
					case 7: $sResponse .= " и седем"; break;
					case 8: $sResponse .= " и осем"; break;
					case 9: $sResponse .= " и девет"; break;
				}
			}
		}
		else
		{
			if( $nDigit[0] != 0 )$sResponse .= "и ";
			switch( $nDigit[0] )
			{
				case 0: $sResponse .= "десет"; break;
				case 1: $sResponse .= "единадесет"; break;
				case 2: $sResponse .= "дванадесет"; break;
				case 3: $sResponse .= "тринадесет"; break;
				case 4: $sResponse .= "четиринадесет"; break;
				case 5: $sResponse .= "петнадесет"; break;
				case 6: $sResponse .= "шестнадесет"; break;
				case 7: $sResponse .= "седемнадесет"; break;
				case 8: $sResponse .= "осемнадесет"; break;
				case 9: $sResponse .= "деветнадесет"; break;
			}
		}
	}
	
	return $sResponse;
}

/**
 * Еквивалент на LPAD в MySQL.
 *
 * @param string $sNum
 * @param int $nSpaces
 * @param int $nFillout
 * @return string
 */
function LPAD( $sNum, $nSpaces, $nFillout )
{
	if( strlen( $sNum ) > $nSpaces )return $sNum;
	
	$nIterations = $nSpaces - strlen( $sNum );
	$sPrefix = "";
	for( $i = 0; $i < $nIterations; $i++ )
	{
		$sPrefix .= $nFillout;
	}
	
	return $sPrefix . $sNum;
}

function utf8_substr( $str, $start )
{
	// BUG pri UTF-8 - funkciata substr ne bachka pravilno!
	// da se izpolzva kogato nqma instaliran modul mb_ 
	preg_match_all( "/./su", $str, $ar );
	if( func_num_args() >= 3 )
	{
		$end = func_get_arg( 2 );
		return join( "", array_slice( $ar[0], $start, $end ) );
	}
	else
	{
		return join( "", array_slice( $ar[0], $start ) );
	}
}

function utf8_strlen( $str )
{
	// BUG pri UTF-8 - funkciata strlen ne bachka pravilno! 
	// da se izpolzva kogato nqma instaliran modul mb_  
	preg_match_all( "/./su", $str, $ar );
	return count( $ar[0] );
} 

/**
 * Разрежда символите от стринга с интервали.
 *
 * @param string $sText
 * @return string
 * @author Misho
 */
function addSpaces( $sText )
{
	$sResult = "";
	
	for( $i = 0; $i < utf8_strlen( $sText ); $i++ )
	{
		$sResult .= utf8_substr( $sText, $i, 1 ) . " ";
	}
	
	return utf8_substr( $sResult, 0, utf8_strlen( $sResult ) - 1 );
}

/**
 * Функцията извършва елементарни операции с време ( HH:mm ).
 *
 * @param string $sTime1 ( HH:mm )
 * @param string $sTime2 ( HH:mm )
 * @param bool $bNegate ( TRUE = изваждане ; FALSE = събиране ) ( OPTIONAL, DEFAULT : FALSE )
 * @return string ( HH:mm )
 */
function    getTimeSum( $sTime1, $sTime2, $bNegate = false )
{
	$aTime1 = explode( ":", $sTime1 );
	$aTime2 = explode( ":", $sTime2 );
	
	if( !isset( $aTime1[0] ) )$aTime1[0] = 0; else $aTime1[0] = ( int ) $aTime1[0];
	if( !isset( $aTime1[1] ) )$aTime1[1] = 0; else $aTime1[1] = ( int ) $aTime1[1];
	if( !isset( $aTime2[0] ) )$aTime2[0] = 0; else $aTime2[0] = ( int ) $aTime2[0];
	if( !isset( $aTime2[1] ) )$aTime2[1] = 0; else $aTime2[1] = ( int ) $aTime2[1];
	
	if( $aTime1[0] < 0 )
	{
		$aTime1[1] = -$aTime1[1];
	}
	
	$aTime1[1] += $aTime1[0] * 60;
	$aTime2[1] += $aTime2[0] * 60;
	
	if( $bNegate )$aTime1[1] -= $aTime2[1];
	else $aTime1[1] += $aTime2[1];
	
	$aTime1[0] = ( int ) ( $aTime1[1] / 60 );
	$aTime1[1] = $aTime1[1] % 60;
	
	if( $aTime1[1] < 0 )$aTime1[1] = -$aTime1[1];
	
	$sTime = ( ( strlen( $aTime1[0] ) < 2 ) ? "0" . $aTime1[0] : $aTime1[0] ) . ":" . ( ( strlen( $aTime1[1] ) < 2 ) ? "0" . $aTime1[1] : $aTime1[1] );
	
	return $sTime;
}

/**
 * Закръгляне на час, формат HH:mm
 *
 * @param string $sTime ( HH:mm )
 * 
 * @return int ( Брой часове )
 */
function getRoundTime( $sTime )
{
	$aTime = explode( ":", $sTime );
	
	if( !isset( $aTime[0] ) )$aTime[0] = 0; else $aTime[0] = ( int ) $aTime[0];
	if( !isset( $aTime[1] ) )$aTime[1] = 0; else $aTime[1] = ( int ) $aTime[1];
	
	if( $aTime[1] > 30 )$aTime[0] = ( $aTime[0] < 0 ) ? $aTime[0] - 1 : $aTime[0] + 1;
	
	return $aTime[0];
}

/**
 * Преобразува масив/обект в стринг
 *
 * @author Pavel Petrov
 * 
 * @param array $aData - масива, който трябва да се преобразува
 * @param int $type (незадължителен) - флаг за смяна на изгледа
 * @return string данните от масива като текст
 */
function ArrayToString( $aData, $type = 0 ) {
	$content = "";
	
	ob_start();
	
	if ( $type == 1 ) {
		var_dump( $aData );
	} else {
		print_r( $aData );
	}
	
	$content = ob_get_contents();
	
	ob_end_clean();		
	
	return $content;
}

function parseObjectToArray( $object )
{
	$array = array();
	if( is_object( $object ) )
	{
		$array = get_object_vars( $object );
		return $array;
	}
	else return $object;
}

function ob_toFile($aParams, $filename = "test.txt") {
	$filename = "test.txt";

	$content = ArrayToString( $aParams, 1 );
	
	if ( !$handle = fopen($filename, "w+") ) {
		exit;
	}		

	if ( fwrite($handle, $content) === FALSE ) {
		exit;
	}		
}

function getID( $nID, $bAllowNegative = FALSE )
{
	if( !is_numeric( $nID ) || ( $bAllowNegative == FALSE && $nID < 0 ) )
		return 0;
	
	return $nID;
}


/**
 * Функцията връща ИНДЕКСА от идексен масив във вид:
 * 
 * array(
 *   [0] => array([key1] => [value1], [key2] => [value2], [key3] => [value3] ... ),
 *   [1] => array([key1] => [value1], [key2] => [value2], [key3] => [value3] ... ),
 *   ...
 *  );
 *  по зададен асоциативен кей и съответстваща му стойност или FALSE при несъответсвие;
 *  
 * @author Павел Петров
 *  
 * @param array 	$aData 		- Масив,  от който ще се търси;
 * @param string 	$sKey		- асоциативен кей, по който ще се търси
 * @param mixed 	$sValue		- търсена стойност, която да има кейя
 * 
 * @return int - индекс от масива при открито съвпадения (първия, ако има няколко), false при липса;
 */
function array_search_value($aData, $sKey, $sValue) {
	foreach ( $aData as $key => $val ) {
		$current_key = $key;
		
		if ( isset($val[$sKey]) && ($val[$sKey] == $sValue) ) {
			return $current_key;
		}
	}
	
	return false;
}

/**
 * Функцията връща в масив относителни стойности от масива $aValues, понижени в интервал [0 - $nResolution].
 *
 * @author Михаил Димитров
 * @param int $nResolution
 * @param int $aValues
 * 
 * @return array
 */
function calcReduction( $nResolution, $aValues )
{
	$aResult = array();
	
	$nMaxValue = 0;
	foreach( $aValues as $nKey => $nValue )
	{
		if( ( $nValue >= 0 ? $nValue : -$nValue ) > $nMaxValue )$nMaxValue = ( $nValue >= 0 ? $nValue : -$nValue );
	}
	
	if( $nResolution != 0 )$nCoeficient = $nMaxValue / $nResolution;
	else $nCoeficient = 1;
	
	foreach( $aValues as $nKey => $nValue )
	{
		$aResult[$nKey] = empty( $nCoeficient ) ? round( $nValue, 0 ) : round( ( $nValue / $nCoeficient ), 0 );
	}
	
	return $aResult;
}

function debug_to_file( $sOutput, $sFile = "D:\Output.txt", $sMode = "w" )
{
	$oFile = fopen( $sFile, $sMode );
	fwrite( $oFile, $sOutput );
	fclose( $oFile );
}

function SecToTime( $nSecs )
{
	$nMinutes = ( int ) ( $nSecs / 60 );
	$nSecs = $nSecs % 60;
	$nHours = ( int ) ( $nMinutes / 60 );
	$nMinutes = $nMinutes % 60;
	
	return $nHours . ":" . LPAD( $nMinutes, 2, 0 ) . ":" . LPAD( $nSecs, 2, 0 );
}

/**
 * Изважда или събира брой месеци към текущия. ( заобикаляне на проблема от strtotime при пълнене на селекти )
 *
 * @param int $nOffset
 * @return string "Y-m"
 */
function offsetMonth( $nOffset )
{
	$nYear = date( "Y" );
	$nMonth = date( "m" );
	
	for( $i = 0; $i < abs( $nOffset ); $i++ )
	{
		if( $nOffset >= 0 ) $nMonth++;
		if( $nOffset < 0 ) $nMonth--;
		
		if( $nMonth > 12 ) { $nMonth = 1; $nYear++; }
		if( $nMonth < 1 ) { $nMonth = 12; $nYear--; }
	}
	
	return $nYear . "-" . LPAD( $nMonth, 2, 0 );
}

?>
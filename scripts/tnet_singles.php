<?php
	function debug( $array ) {
		echo "<pre>"; print_r($array); echo "<pre>";
	}
	
	$telenet_system = "telenet_system";
	$finance		= "finance";
	$sod			= "sod";
	$telepol		= "telepol_net";

	//$link = mysql_connect('213.91.252.134', 'lamerko', 'Olig0fren');
	//$link = mysql_connect('localhost', 'root', '');
	if ( !$link ) {
	   die('Не мога да се конектна: ' . mysql_error());
	}

	$db_selected = mysql_select_db($telepol, $link);
	if ( !$db_selected ) {
	   die ('Не мога да селектна: ' . mysql_error());
	}	


	//$link2 = mysql_connect('213.91.252.129', 'lamerko', 'Olig0fren', 1);
	//$link2 = mysql_connect('172.16.10.254:3307', 'lamerko', 'Olig0fren', 1);
	if ( !$link2 ) {
	   die('Не мога да се конектна: ' . mysql_error());
	}

	$db_selected2 = mysql_select_db($sod, $link2);
	if ( !$db_selected2 ) {
	   die ('Не мога да селектна: ' . mysql_error());
	}	
	
	$sQuery = "
		SELECT 
			s.id_obj as id,
			s.id_single as id_schet,
			s.data,
			s.`sum` as tsum,
			s.`sum_p` as tsum_p,
			s.info,
			o.id_master_service,
			CASE currency
				WHEN 'BGL' THEN s.info
				WHEN 'USD' THEN CONCAT('[USD]', s.info) 
				WHEN 'EUR' THEN CONCAT('[EUR]', s.info) 
			END as info		
		FROM singles s
		LEFT JOIN objects o ON o.id_obj = s.id_obj
		WHERE s.`sum` > 0
			AND s.sum != s.sum_p
	";
	
	$result = mysql_query( $sQuery, $link );
	
	$aData = array();
	
	while ($row = mysql_fetch_assoc($result)) {
		
		$result4 = mysql_query( "SET NAMES UTF8", $link2 );
		
		$nID		= is_numeric($row['id'])		? $row['id']							: 0;
		$nIDSchet	= is_numeric($row['id_schet'])	? $row['id_schet']						: 0;
		$start 		= isset($row['data'])			? $row['data']							: "0000-00-00";
		$price 		= isset($row['tsum'])	&& is_numeric($row['tsum']) ? $row['tsum']		: 0;
		$price_p	= isset($row['tsum_p']) && is_numeric($row['tsum_p']) ? $row['tsum_p']	: 0;
		$service 	= isset($row['info'])			? mysql_real_escape_string(iconv("CP1251", "UTF-8", $row['info'])) : "";
		//$nIDSer		= isset($row['id_master_service'])	? $row['id_master_service']			: 0;
		$nIDSer		= 1001; // Абонаментна поддръжка
				
		if ( $price_p < $price ) {
			$price 	= ($price - $price_p) * 1.2;
			$paid_date = "0000-00-00";
			$doc_num = "";
		} else {
			$price *= 1.2;
			$paid_date = $start;
			$doc_num = 1;
		}
			
		$insQuery = "
			INSERT INTO {$sod}.objects_singles
				( id_object, id_office, id_service, id_schet, service_name, single_price, quantity, total_sum, start_date, paid_date, id_sale_doc, updated_user, updated_time, to_arc )
			SELECT o.id, 67, {$nIDSer}, {$nIDSchet}, '{$service}', '{$price}', 1, '{$price}', '{$start}', '{$paid_date}', '{$doc_num}', 35, NOW(), 0
			FROM {$sod}.objects o
			LEFT JOIN {$finance}.nomenclatures_earnings ne ON (ne.id_schet = {$nIDSer} AND ne.to_arc = 0)
			LEFT JOIN {$finance}.nomenclatures_services ns ON (ne.id = ns.id_nomenclature_earning AND ns.is_schet = 1 AND ns.to_arc = 0) 
			WHERE o.id_oldobj = {$nID}
				AND ne.id_schet = {$nIDSer}

		";
				
		//debug($insQuery);
		$result4 = mysql_query( $insQuery, $link2 );
		
		if ( !$result4 ) {
		    echo 'Не мога да изпълня: ' . mysql_error() . $insQuery;
		    exit;
		}
	}	
	
	//debug($aData);
	echo "OK";
?>
<?php
	function debug( $array ) {
		echo "<pre>"; print_r($array); echo "<pre>";
	}

	$telenet_system = "telenet_system";
	$finance		= "finance";
	$sod			= "sod";
	$telepol		= "sod";

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
	
	function zero_padding ($nNumber, $nZeros=ZEROPADDING) {
		return sprintf("%0".$nZeros."s", $nNumber);
	}	
	
	function getPayment( $nID, $nIDSer ) {
		global $link, $link2, $telenet_system, $finance, $sod;
		
		$table1 = date("Ym");
		$table2 = date("Ym", mktime(0, 0, 0, date("m") -1, 1, date("Y")));
		$table3 = date("Ym", mktime(0, 0, 0, date("m") -2, 1, date("Y")));
		
		$sQuery = "
			( 	
				SELECT 
					CONCAT('200901', '_', id) as id,
					id_obj,
					bank,
					sum,
					valid_sum as order_sum,
					taxes,
					tax_num,
					paid_month,
					faktura,
					data,
					info,
					direction_id as id_direction
				FROM mp200901
				WHERE id_obj = {$nID}
					AND confirm = 0
					AND (sum - valid_sum) > 0
					AND zero = 0
			) UNION (
				SELECT 
					CONCAT('200902', '_', id) as id,
					id_obj,
					bank,
					sum,
					valid_sum as order_sum,
					taxes,
					tax_num,
					paid_month,
					faktura,
					data,
					info,
					direction_id as id_direction
				FROM mp200902
				WHERE id_obj = {$nID}
					AND confirm = 0
					AND (sum - valid_sum) > 0
					AND zero = 0
			) UNION (
				SELECT 
					CONCAT('200903', '_', id) as id,
					id_obj,
					bank,
					sum,
					valid_sum as order_sum,
					taxes,
					tax_num,
					paid_month,
					faktura,
					data,
					info,
					direction_id as id_direction
				FROM mp200903
				WHERE id_obj = {$nID}
					AND confirm = 0
					AND (sum - valid_sum) > 0
					AND zero = 0
			) UNION (
				SELECT 
					CONCAT('200904', '_', id) as id,
					id_obj,
					bank,
					sum,
					valid_sum as order_sum,
					taxes,
					tax_num,
					paid_month,
					faktura,
					data,
					info,
					direction_id as id_direction
				FROM mp200904
				WHERE id_obj = {$nID}
					AND confirm = 0
					AND (sum - valid_sum) > 0
					AND zero = 0
			) UNION (
				SELECT 
					CONCAT('200905', '_', id) as id,
					id_obj,
					bank,
					sum,
					valid_sum as order_sum,
					taxes,
					tax_num,
					paid_month,
					faktura,
					data,
					info,
					direction_id as id_direction
				FROM mp200905
				WHERE id_obj = {$nID}
					AND confirm = 0
					AND (sum - valid_sum) > 0
					AND zero = 0
			) UNION (
				SELECT 
					CONCAT('200906', '_', id) as id,
					id_obj,
					bank,
					sum,
					valid_sum as order_sum,
					taxes,
					tax_num,
					paid_month,
					faktura,
					data,
					info,
					direction_id as id_direction
				FROM mp200906
				WHERE id_obj = {$nID}
					AND confirm = 0
					AND (sum - valid_sum) > 0
					AND zero = 0
			) UNION (
				SELECT 
					CONCAT('200907', '_', id) as id,
					id_obj,
					bank,
					sum,
					valid_sum as order_sum,
					taxes,
					tax_num,
					paid_month,
					faktura,
					data,
					info,
					direction_id as id_direction
				FROM mp200907
				WHERE id_obj = {$nID}
					AND confirm = 0
					AND (sum - valid_sum) > 0
					AND zero = 0
			) UNION (
				SELECT 
					CONCAT('200908', '_', id) as id,
					id_obj,
					bank,
					sum,
					valid_sum as order_sum,
					taxes,
					tax_num,
					paid_month,
					faktura,
					data,
					info,
					direction_id as id_direction
				FROM mp200908
				WHERE id_obj = {$nID}
					AND confirm = 0
					AND (sum - valid_sum) > 0
					AND zero = 0
			) UNION (
				SELECT 
					CONCAT('200909', '_', id) as id,
					id_obj,
					bank,
					sum,
					valid_sum as order_sum,
					taxes,
					tax_num,
					paid_month,
					faktura,
					data,
					info,
					direction_id as id_direction
				FROM mp200909
				WHERE id_obj = {$nID}
					AND confirm = 0
					AND (sum - valid_sum) > 0
					AND zero = 0
			) UNION (
				SELECT 
					CONCAT('200910', '_', id) as id,
					id_obj,
					bank,
					sum,
					valid_sum as order_sum,
					taxes,
					tax_num,
					paid_month,
					faktura,
					data,
					info,
					direction_id as id_direction
				FROM mp200910
				WHERE id_obj = {$nID}
					AND confirm = 0
					AND (sum - valid_sum) > 0
					AND zero = 0
			) UNION (
				SELECT 
					CONCAT('200911', '_', id) as id,
					id_obj,
					bank,
					sum,
					valid_sum as order_sum,
					taxes,
					tax_num,
					paid_month,
					faktura,
					data,
					info,
					direction_id as id_direction
				FROM mp200911
				WHERE id_obj = {$nID}
					AND confirm = 0
					AND (sum - valid_sum) > 0
					AND zero = 0
			)
		";			
		//echo $sQuery;
		$resPay = mysql_query( $sQuery, $link );
		
		if ( !$resPay ) {
		    echo $sQuery.mysql_error();
		    exit;
		}
	
		while ( $rowPay = @mysql_fetch_assoc($resPay) ) {
			if ( isset($rowPay['id']) ) {
				$ids = array();
				$ids = explode("_", $rowPay['id']);
				$id = $ids[0].zero_padding($ids[1], 7);	
			} else $id = 0;
			
			$sum 		= isset($rowPay['sum']) && is_numeric($rowPay['sum']) 	? ($rowPay['sum']) : 0;
			$order_sum 	= isset($rowPay['order_sum']) && is_numeric($rowPay['order_sum']) 	? ($rowPay['order_sum']) : 0;
			$paid_month = isset($rowPay['paid_month']) 	? $rowPay['paid_month'] : "0000-00-00";
			$data		= isset($rowPay['data']) 		? $rowPay['data'] : "0000-00-00";
			$doc_type 	= isset($rowPay['faktura']) && $rowPay['faktura'] == 1 	? "faktura" : "kvitanciq";
			$paid_type 	= isset($rowPay['bank']) && $rowPay['bank'] == 1 		? "bank" : "cash";
			$nDocNum	= isset($rowPay['tax_num'])		? $rowPay['tax_num']	: 0;
			$nIDSaleDoc	= 0;
			$info		= isset($rowPay['info'])		? iconv("CP1251", "UTF-8", $rowPay['info'])	: "";
			
			//$resNum 	= mysql_query( "SELECT last_num_sale_doc FROM {$telenet_system}.system", $link2 );
			//$num 		= mysql_fetch_row($resNum);
			//$nDocNum 	= isset($num[0]) ? $num[0] + 1 : 0;
			
			//mysql_query( "UPDATE {$telenet_system}.system SET last_num_sale_doc = last_num_sale_doc + 1 ", $link2 );
			
			$result2 = mysql_query( "SET NAMES UTF8", $link2 );

			$insQuery = "
				INSERT INTO {$finance}.sales_docs_200911
					( doc_num, doc_date, doc_type, doc_status, id_credit_master, id_client, client_name, client_ein, client_ein_dds, client_address, client_mol, 
					client_recipient, deliverer_name, deliverer_address, deliverer_ein, deliverer_ein_dds, deliverer_mol, total_sum, single_view_name, orders_sum, last_order_id, 
					last_order_time, paid_type, id_bank_account, view_type, is_auto, note, gen_pdf, created_user, created_time, updated_user, updated_time, to_arc )  
				SELECT 
					{$nDocNum}, '{$data}', '{$doc_type}', 'final', 0, c.id, c.name, c.invoice_ein, c.invoice_ein_dds, c.invoice_address, c.invoice_mol,
					invoice_recipient, 'Инфра ЕООД', 'гр. Димитровград, ул. Хр. Г. Данов 10-Б3', '111111111', 'BG 111111111', 'Любомир Ненчев Гочев', '{$sum}', 'yслуга', '{$order_sum}', 0, 
					'0000-00-00 00:00:00', '{$paid_type}', 0, 'detail', 0, '', '', 35, NOW(), 35, NOW(), 0 
				FROM {$sod}.objects o
				LEFT JOIN {$sod}.clients_objects co ON co.id_object = o.id
				LEFT JOIN {$sod}.clients c ON c.id = co.id_client
				LEFT JOIN {$sod}.offices of ON of.id = o.id_office
				LEFT JOIN {$sod}.firms f ON f.id = of.id_firm
				LEFT JOIN {$sod}.offices odds ON odds.id = f.id_office_dds
				LEFT JOIN {$sod}.firms fdds ON fdds.id = odds.id_firm
				WHERE o.id_oldobj = {$nID}
			";
			//debug($insQuery);	
			// {$id} - id_schet - da se alabala sled kato sichko e OK!!!

			$resPay2 	= mysql_query( $insQuery, $link2 );
			if ( !$resPay2 ) {
			   die('Грешка в query: ' . mysql_error());
			}

			$nIDSaleDoc = mysql_result( mysql_query( "SELECT LAST_INSERT_ID()", $link2), 0 );

			//$sum		= $sum / 1.2;
			//$order_sum	= $order_sum / 1.2;

			if ( strlen($nIDSaleDoc) != 13 ) {
				debug($insQuery);
			} else {
				$nIDSaleDocRow	= 0;
				$nIDSer			= 1001;
					
				$insQuery = "
					INSERT INTO {$finance}.sales_docs_rows_200911
						( id_sale_doc, id_office, id_object, month, id_service, id_duty_row, id_schet_row, service_name, object_name, quantity, measure, single_price, total_sum, paid_sum, paid_date, is_dds, updated_user, updated_time )
					SELECT
						{$nIDSaleDoc}, o.id_office, o.id, '{$paid_month}', ns.id, 0, '{$id}', '{$info}', CONCAT('[', o.num, '] ', o.invoice_name), 1, 'бр.', '{$sum}' / 1.2, '{$sum}' / 1.2, '{$order_sum}' / 1.2, NOW(), 0, 35, NOW()
					FROM {$sod}.objects o
					LEFT JOIN {$finance}.nomenclatures_earnings ne ON (ne.id_schet = {$nIDSer} AND ne.to_arc = 0)
					LEFT JOIN {$finance}.nomenclatures_services ns ON (ne.id = ns.id_nomenclature_earning AND ns.to_arc = 0 AND ns.is_schet = 1) 
					WHERE o.id_oldobj = {$nID}
						AND ne.id_schet = {$nIDSer}
				";	
				
				$resPay3 	= mysql_query( $insQuery, $link2 );
				if ( !$resPay3 ) {
				   die('Грешка в query: ' . mysql_error());
				}
				
				$nIDSaleDocRow = mysql_result( mysql_query( "SELECT LAST_INSERT_ID()", $link2), 0 );

				$sum_dds	= $sum / 6;			// 6;
				$osum_dds	= $order_sum / 6;		// 6;
				
				if ( strlen($nIDSaleDoc) != 13 ) {
					debug($insQuery);
				}

				$insQuery = "
					INSERT INTO {$finance}.sales_docs_rows_200911
						( id_sale_doc, id_office, id_object, month, id_service, id_duty_row, id_schet_row, service_name, object_name, quantity, measure, single_price, total_sum, paid_sum, paid_date, is_dds, updated_user, updated_time )
					VALUES 
						(
							{$nIDSaleDoc}, 67, 0, '{$paid_month}', 0, 0, '{$id}', 'ДДС', 'ДДС', 1, 'бр.', '{$sum_dds}', '{$sum_dds}', '{$osum_dds}', NOW(), 1, 35, NOW()
						)
				";	
				
				$resPay4 	= mysql_query( $insQuery, $link2 );
				if ( !$resPay4 ) {
				   die('Грешка в query: ' . mysql_error());
				}
			}
		}
	}
			
	$sQuery = "
		SELECT 
			o.id_obj as id,
			o.start as start_date,
			(o.price * 1.2) as price,
			IF ( UNIX_TIMESTAMP(oo.paid_month) > 0, oo.paid_month, o.paid_month ) as paid_month,
			o.id_master_service as id_service
		FROM objects o 
		LEFT JOIN objects oo ON ( o.id_master_obj = oo.id_obj AND o.id_master_obj > 0 )
		WHERE o.price > 0 
			AND o.id_status != 4
	";
	//o.paid_month as paid_month,
	$result = mysql_query( $sQuery, $link );
	
	$aData = array();
	
	while ($row = mysql_fetch_assoc($result)) {
		$nID		= is_numeric($row['id']) ? $row['id'] : 0;
		$nIDSer		= 1001;		//is_numeric($row['id_service']) ? $row['id_service'] : 0;
		$start 		= isset($row['start_date']) ? $row['start_date'] : 0;
		$price 		= isset($row['price']) && is_numeric($row['price']) ? $row['price'] : 0;
		$paid 		= isset($row['paid_month']) ? $row['paid_month'] : "0000-00-00";
		
		$result2 = mysql_query( "SET NAMES UTF8", $link2 );

		$insQuery = "
			INSERT INTO {$sod}.objects_services
				( id_object, id_office, id_service, service_name, single_price, quantity, total_sum, start_date, last_paid, updated_user, updated_time, to_arc )
			SELECT o.id, 67, 8, ns.name, '{$price}', 1, '{$price}', '{$start}', '{$paid}', 35, NOW(), 0 
			FROM {$sod}.objects o, {$finance}.nomenclatures_earnings n 
			LEFT JOIN {$finance}.nomenclatures_services ns ON ns.id_nomenclature_earning = n.id AND ns.is_schet = 1
			WHERE o.id_oldobj = {$nID}
				AND n.id_schet = {$nIDSer}

		";
		//debug($insQuery);	
		$result4 = mysql_query( $insQuery, $link2 );

		getPayment( $nID, $nIDSer );
	}	

	$sQuery = "
		SELECT 
			s.id_obj as id, 
			s.id_service as ids,
			o.start as start_date,
			(s.price * 1.2) as price,
			IF ( UNIX_TIMESTAMP(oo.paid_month) > 0, oo.paid_month, o.paid_month ) as paid_month,
			s.id_type as id_service
		FROM services s
		LEFT JOIN objects o ON s.id_obj = o.id_obj
		LEFT JOIN objects oo ON ( o.id_master_obj = oo.id_obj AND o.id_master_obj > 0 AND oo.id_status != 4 )
		WHERE s.active = 1 
			AND s.price > 0
			AND o.id_status != 4
	";

	$result12 = mysql_query( $sQuery, $link );
	
	$aData = array();
	
	while ($row = mysql_fetch_assoc($result12)) {
		$nID		= is_numeric($row['id']) ? $row['id'] : 0;
		$nIDSer		= 1001;		// is_numeric($row['id_service']) ? $row['id_service'] : 0;
		$start 		= isset($row['start_date']) ? $row['start_date'] : 0;
		$price 		= isset($row['price']) && is_numeric($row['price']) ? $row['price'] : 0;
		$paid 		= isset($row['paid_month']) ? $row['paid_month'] : "0000-00-00";
		$nIDService	=  isset($row['ids']) && is_numeric($row['ids']) ? $row['ids'] : 0;
			
		$result2 = mysql_query( "SET NAMES UTF8", $link2 );

		$insQuery = "
			INSERT INTO {$sod}.objects_services
				( id_object, id_office, id_service, id_schet, service_name, single_price, quantity, total_sum, start_date, last_paid, updated_user, updated_time, to_arc )
			SELECT o.id, 67, ns.id, 8, ns.name, '{$price}', 1, '{$price}', '{$start}', '{$paid}', 35, NOW(), 0 
			FROM {$sod}.objects o, {$finance}.nomenclatures_earnings n 
			LEFT JOIN {$finance}.nomenclatures_services ns ON ns.id_nomenclature_earning = n.id AND ns.is_schet = 1
			WHERE o.id_oldobj = {$nID}
				AND n.id_schet = {$nIDSer}
		";

		//debug($insQuery);	
		$result14 = mysql_query( $insQuery, $link2 );
		
		$upQuery = "
			UPDATE {$sod}.objects_services SET single_price = single_price - '{$price}', total_sum = total_sum - '{$price}' WHERE id_object = ( SELECT id FROM {$sod}.objects WHERE id_oldobj = {$nID}) AND id_schet = 0
		";

		$result15 = mysql_query( $upQuery, $link2 );

		//getPayment( $nID, $nIDSer );
	}

	
	//debug($aData);
	echo "OK";
?>
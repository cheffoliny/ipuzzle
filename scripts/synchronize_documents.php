<?php
	function debug( $array ) {
		echo "<pre>"; print_r($array); echo "<pre>";
	}

	$finance	= "finance";
	$sod		= "sod";

	//$link = mysql_connect('213.91.252.134', 'lamerko', 'Olig0fren');
	//$link = mysql_connect('localhost', 'root', '');
	if ( !$link ) {
	   die('Не мога да се конектна: ' . mysql_error());
	}

	$db_selected = mysql_select_db('telepol', $link);
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
	
	function getClient( $ein, &$nID ) {
		global $link2, $sod;

		if ( !empty($ein) ) {
			$rClient2 	= mysql_query( "
				SELECT 
					id, 
					name, 
					invoice_ein as ein, 
					invoice_ein_dds as ein_dds, 
					invoice_address as address, 
					invoice_mol as mol, 
					invoice_recipient as recipient  
				FROM {$sod}.clients 
				WHERE invoice_ein = '{$ein}' 
				LIMIT 1", 
			$link2 );

			$rowCL 		= mysql_fetch_row($rClient2);
		}
		
		if ( isset($rowCL[0]) && !empty($rowCL[0]) ) {
			$nID['id']		= $rowCL[0];
			$nID['name']	= $rowCL[1];
			$nID['ein']		= $rowCL[2];
			$nID['ein_dds'] = $rowCL[3];
			$nID['address'] = $rowCL[4];
			$nID['mol']		= $rowCL[5];
			$nID['recipient'] = $rowCL[6];
		} else {
			$nID = 0;
		}
	}

	function getPayment() {
		global $link, $link2, $telenet_system, $finance, $sod;
		
		$sQuery = "
			( 	
				SELECT 
					CONCAT('200901', '_', m.id) as id,
					m.id_obj,
					m.bank,
					m.sum,
					m.valid_sum as order_sum,
					m.taxes,
					m.tax_num,
					m.paid_month,
					m.faktura,
					m.data,
					m.f_bulstat as ein,
					m.direction_id as id_direction,
					d.account as code
				FROM mp200901 m
				LEFT JOIN directions d ON ( d.id_direction = m.direction_id )
				WHERE 1
					AND m.confirm = 0
					AND (m.sum - m.valid_sum) > 0
					AND m.zero = 0
					AND m.id_obj = 0		
			) UNION (
				SELECT
					CONCAT('200902', '_', m.id) as id,
					m.id_obj,
					m.bank,
					m.sum,
					m.valid_sum as order_sum,
					m.taxes,
					m.tax_num,
					m.paid_month,
					m.faktura,
					m.data,
					m.f_bulstat as ein,
					m.direction_id as id_direction,
					d.account as code
				FROM mp200902 m
				LEFT JOIN directions d ON ( d.id_direction = m.direction_id )
				WHERE 1
					AND m.confirm = 0
					AND (m.sum - m.valid_sum) > 0
					AND m.zero = 0
					AND m.id_obj = 0			
			) UNION (
				SELECT
					CONCAT('200903', '_', m.id) as id,
					m.id_obj,
					m.bank,
					m.sum,
					m.valid_sum as order_sum,
					m.taxes,
					m.tax_num,
					m.paid_month,
					m.faktura,
					m.data,
					m.f_bulstat as ein,
					m.direction_id as id_direction,
					d.account as code
				FROM mp200903 m
				LEFT JOIN directions d ON ( d.id_direction = m.direction_id )
				WHERE 1
					AND m.confirm = 0
					AND (m.sum - m.valid_sum) > 0
					AND m.zero = 0
					AND m.id_obj = 0				
			) UNION (
				SELECT
					CONCAT('200904', '_', m.id) as id,
					m.id_obj,
					m.bank,
					m.sum,
					m.valid_sum as order_sum,
					m.taxes,
					m.tax_num,
					m.paid_month,
					m.faktura,
					m.data,
					m.f_bulstat as ein,
					m.direction_id as id_direction,
					d.account as code
				FROM mp200904 m
				LEFT JOIN directions d ON ( d.id_direction = m.direction_id )
				WHERE 1
					AND m.confirm = 0
					AND (m.sum - m.valid_sum) > 0
					AND m.zero = 0
					AND m.id_obj = 0			
			) UNION (
				SELECT
					CONCAT('200905', '_', m.id) as id,
					m.id_obj,
					m.bank,
					m.sum,
					m.valid_sum as order_sum,
					m.taxes,
					m.tax_num,
					m.paid_month,
					m.faktura,
					m.data,
					m.f_bulstat as ein,
					m.direction_id as id_direction,
					d.account as code
				FROM mp200905 m
				LEFT JOIN directions d ON ( d.id_direction = m.direction_id )
				WHERE 1
					AND m.confirm = 0
					AND (m.sum - m.valid_sum) > 0
					AND m.zero = 0
					AND m.id_obj = 0			
			) UNION (
				SELECT
					CONCAT('200906', '_', m.id) as id,
					m.id_obj,
					m.bank,
					m.sum,
					m.valid_sum as order_sum,
					m.taxes,
					m.tax_num,
					m.paid_month,
					m.faktura,
					m.data,
					m.f_bulstat as ein,
					m.direction_id as id_direction,
					d.account as code
				FROM mp200906 m
				LEFT JOIN directions d ON ( d.id_direction = m.direction_id )
				WHERE 1
					AND m.confirm = 0
					AND (m.sum - m.valid_sum) > 0
					AND m.zero = 0
					AND m.id_obj = 0				
			) UNION (
				SELECT
					CONCAT('200907', '_', m.id) as id,
					m.id_obj,
					m.bank,
					m.sum,
					m.valid_sum as order_sum,
					m.taxes,
					m.tax_num,
					m.paid_month,
					m.faktura,
					m.data,
					m.f_bulstat as ein,
					m.direction_id as id_direction,
					d.account as code
				FROM mp200907 m
				LEFT JOIN directions d ON ( d.id_direction = m.direction_id )
				WHERE 1
					AND m.confirm = 0
					AND (m.sum - m.valid_sum) > 0
					AND m.zero = 0
					AND m.id_obj = 0
			)						
		";			
		//echo $sQuery;
		$resPay = mysql_query( $sQuery, $link );
		
		if ( !$resPay ) {
		    echo $sQuery.mysql_error();
		    exit;
		}
	
		$nIDClient	= 0;
		$nIDSaleDoc	= 0;
		$nIDOffice	= 0;	
		$aClient	= array();

		while ( $rowPay = mysql_fetch_assoc($resPay) ) {
			$nIDClient	= 0;
			$nIDSaleDoc	= 0;
			$nIDOffice	= 0;
			$aClient	= array();
			

			if ( isset($rowPay['id']) ) {
				$ids = array();
				$ids = explode("_", $rowPay['id']);
				$id = $ids[0].zero_padding($ids[1], 7);	
			} else $id = 0;
			
			if ( !empty($id) ) {
				$nID		= 0;	//isset($rowPay['id_obj']) 	&& is_numeric($rowPay['id_obj']) 	? ($rowPay['id_obj']) 		: 0;
				$sum 		= isset($rowPay['sum']) && is_numeric($rowPay['sum']) 			? ($rowPay['sum']) 			: 0;
				$order_sum 	= isset($rowPay['order_sum']) && is_numeric($rowPay['order_sum']) ? ($rowPay['order_sum']) 	: 0;
				$paid_month = isset($rowPay['paid_month']) 									? $rowPay['paid_month'] 	: "0000-00-00";
				$data		= isset($rowPay['data']) 										? $rowPay['data'] 			: "0000-00-00";
				$doc_type 	= isset($rowPay['faktura']) && $rowPay['faktura'] == 1 			? "faktura" 				: "kvitanciq";
				$paid_type 	= isset($rowPay['bank']) && $rowPay['bank'] == 1 				? "bank" 					: "cash";
				$nDocNum	= isset($rowPay['tax_num'])										? $rowPay['tax_num']		: 0;
				$ein		= isset($rowPay['ein'])											? $rowPay['ein']			: "";
				$code		= isset($rowPay['code'])										? $rowPay['code']			: 0;
				$nIDSaleDoc	= 0;	
				//$nIDSer		= isset($rowPay['id_service']) && is_numeric($rowPay['id_service']) ? ($rowPay['id_service']) : 0;		

				getClient($ein, &$aClient);

				$nIDClient	= isset($aClient['id'])			? $aClient['id']		: 0;

				if ( !empty($nIDClient) ) {
					$ein		= isset($aClient['ein'])		? $aClient['ein']		: "";
					$ein_dds	= isset($aClient['ein_dds'])	? $aClient['ein_dds']	: "";
					$cl_name	= isset($aClient['name'])		? $aClient['name']		: "";
					$cl_mol		= isset($aClient['mol'])		? $aClient['mol']		: "";
					$cl_addr	= isset($aClient['address'])	? $aClient['address']	: "";
					$cl_recp	= isset($aClient['recipient'])	? $aClient['recipient'] : "";

					switch ( $code ) {
						case "703-1-1": $nIDOffice = 20;
						break;

						case "703-2-1": $nIDOffice = 19;
						break;

						case "703-3-1": $nIDOffice = 69;
						break;

						case "703-4-1": $nIDOffice = 68;
						break;

						case "703-6-1": $nIDOffice = 1;
						break;

						default: $nIDOffice = 0;
						break;
					}

					$result2 = mysql_query( "SET NAMES UTF8", $link2 );

					$insQuery = "
						INSERT INTO {$finance}.sales_docs_200908
							( doc_num, doc_date, doc_type, doc_status, id_credit_master, id_client, client_name, client_ein, client_ein_dds, client_address, client_mol, 
							client_recipient, deliverer_name, deliverer_address, deliverer_ein, deliverer_ein_dds, deliverer_mol, total_sum, single_view_name, orders_sum, last_order_id, 
							last_order_time, paid_type, id_bank_account, view_type, is_auto, note, gen_pdf, created_user, created_time, updated_user, updated_time, to_arc )  
						VALUES 
							( {$nDocNum}, '{$data}', '{$doc_type}', 'final', 0, {$nIDClient}, '{$cl_name}', '{$ein}', '{$ein_dds}', '{$cl_addr}', '{$cl_mol}',
							'{$cl_recp}', 'Инфра ЕООД', 'гр. Димитровград, ул. Хр. Г. Данов 10-Б3', '111111111', 'BG1111111', 'Любомир Ненчев Гочев', '{$sum}', 'yслуга', '{$order_sum}', 0, 
							'0000-00-00 00:00:00', '{$paid_type}', 0, 'detail', 0, '', '', 35, NOW(), 35, NOW(), 0 )
					";
				
					if ( !empty($nIDClient) ) {
							$resPay2 	= mysql_query( $insQuery, $link2 );
					}

					if ( !$resPay2 ) {
						 die('Грешка в query: ' . mysql_error());
					}

					$nIDSaleDoc = mysql_result( mysql_query( "SELECT LAST_INSERT_ID()", $link2), 0 );

					if ( strlen($nIDSaleDoc) != 13 ) {
						debug($insQuery);
					} else {
						$nIDSaleDocRow = 0;

						$insQuery = "
							INSERT INTO {$finance}.sales_docs_rows_200908	
								( id_sale_doc, id_office, id_object, month, id_service, id_duty_row, id_schet_row, service_name, object_name, quantity, measure, single_price, total_sum, paid_sum, paid_date, is_dds, updated_user, updated_time )
							SELECT
								{$nIDSaleDoc}, {$nIDOffice}, 0, '{$paid_month}', ns.id, 0, '{$id}', ns.name, '', 1, 'бр.', '{$sum}' / 1.2, '{$sum}' / 1.2, '{$order_sum}' / 1.2, NOW(), 0, 35, NOW()
							FROM {$finance}.nomenclatures_earnings ne
							LEFT JOIN {$finance}.nomenclatures_services ns ON (ne.id = ns.id_nomenclature_earning AND ns.is_schet = 1 AND ns.to_arc = 0) 
							WHERE  ne.to_arc = 0
								AND ne.id_schet = 2001
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
							INSERT INTO {$finance}.sales_docs_rows_200908	
								( id_sale_doc, id_office, id_object, month, id_service, id_duty_row, id_schet_row, service_name, object_name, quantity, measure, single_price, total_sum, paid_sum, paid_date, is_dds, updated_user, updated_time )
							VALUES 
								(
									{$nIDSaleDoc}, 1, 0, '{$paid_month}', 0, 0, '{$id}', 'ДДС', 'ДДС', 1, 'бр.', '{$sum_dds}', '{$sum_dds}', '{$osum_dds}', NOW(), 1, 35, NOW()
								)
						";	

						$resPay4 	= mysql_query( $insQuery, $link2 );
						
						if ( !$resPay4 ) {
							die('Грешка в query: ' . mysql_error());
						}
					}

				}

			}
			



		}
	}
			
	getPayment();
	//debug($aData);
	echo "OK";
?>
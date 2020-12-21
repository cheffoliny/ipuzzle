<?php
	class DBInvoicement
		extends DBBase2 {
			
		public function __construct() {
			global $db_sod;
			//$db_sod->debug=true;
			
			parent::__construct($db_sod, 'objects_services');
		}
		
		function makeInvoice( DBResponse $oResponse ) {
			global $db_sod, $db_finance, $db_name_sod, $db_name_finance;

			$aData = array();
			$aMonth = array();
			
			$oSalesDocs = new DBSalesDocs();
			$aSalesDocs = array();
				
			$sQuery = "
				SELECT 
					os.id as id,
					c.id as id_client,
					IF ( UNIX_TIMESTAMP( os.last_paid ) = 0, 0, UNIX_TIMESTAMP(os.last_paid) ) as payment,
					IF ( UNIX_TIMESTAMP( os.last_paid ) > 0, 0, UNIX_TIMESTAMP(os.start_date) ) as start_date,
					CONCAT_WS('-', YEAR(NOW()), MONTH(NOW()), '01' ) as first_day
				FROM {$db_name_sod}.objects_services os
				LEFT JOIN {$db_name_sod}.clients_objects co ON ( co.id_object = os.id_object AND co.to_arc = 0 )
				LEFT JOIN {$db_name_sod}.clients c ON co.id_client = c.id
				WHERE os.to_arc = 0
					AND c.invoice_payment = 'bank'
					AND c.invoice_email != ''
					AND c.invoice_email is not null
					AND os.last_paid <= NOW()
					AND os.start_date <= NOW()
				HAVING DATE(FROM_UNIXTIME(payment)) != first_day	
			";	
			
			$aData = $this->select( $sQuery );
			
			foreach ( $aData as $key => $val ) {
				
				$nIDClient = isset($val['id_client']) && is_numeric($val['id_client']) ? $val['id_client'] : 0;
 				
				if ( isset($val['payment']) && is_numeric($val['payment']) && $val['payment'] > 0 ) {
					$date = strtotime('+1 month', $val['payment']);
				} elseif ( isset($val['start_date']) && is_numeric($val['start_date']) && $val['start_date'] > 0 ) {
					$date = $val['start_date'];
				} else $date = 0;
				
				//$val['date'] = date('Y-m-d', $date);
				
				$br = 1;
				while ( date('Y-m-d', $date) <= date('Y-m-d') ) {
					$tmp = $br == 1 ? date('Y-m-d', $date) : date('Y-m', $date)."-01";
					$aMonth[$nIDClient][] = $val['id'].",".$tmp;

					$br++;
					$date = strtotime('+1 month', $date);
				}
		
			}
			
			$sQuery2 = "
				SELECT 
					os.id as id,
					c.id as id_client
				FROM {$db_name_sod}.objects_singles os
				LEFT JOIN {$db_name_sod}.clients_objects co ON ( co.id_object = os.id_object AND co.to_arc = 0 )
				LEFT JOIN {$db_name_sod}.clients c ON co.id_client = c.id
				WHERE os.to_arc = 0
					AND c.invoice_payment = 'bank'
					AND c.invoice_email != ''
					AND c.invoice_email is not null
					AND os.id_sale_doc = 0
			";	
			
			$aData2 = $this->select( $sQuery2 );
			
			foreach ( $aData2 as $key => $val ) {
				$nIDClient = isset($val['id_client']) && is_numeric($val['id_client']) ? $val['id_client'] : 0;
				$aMonth[$nIDClient][] = $val['id'].",single";
			}			
			
			//APILog::Log(0, $aMonth);
			
			foreach ( $aMonth as $value ) {
				$aSalesDocs[] = $oSalesDocs->makeDocs( $nIDClient, $value, "faktura", "final", 1 );
				//APILog::Log(0, $aSalesDocs);
			}
			
			$sIDDocs = "-1";
			
			foreach ( $aSalesDocs as $vdocs ) {
				foreach ( $vdocs as $ids ) {
					$sIDDocs .= !empty($sIDDocs) ? ",".$ids : $ids;
				}
			}
			
			$table = "sales_docs_".date("Ym");
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					id,
					doc_num,
					DATE_FORMAT(doc_date, '%d.%m.%Y') as doc_date,
					deliverer_name,
					client_name,
					total_sum
				FROM {$db_name_finance}.{$table}
				WHERE id IN ({$sIDDocs})
			";
			
			//APILog::Log(0, $sQuery);
			
			$nPage 		= Params::get("current_page", 1);
			$sSortField = Params::get("sfield", "doc_num");
			$nSortType	= Params::get("stype", "DBAPI_SORT_ASC");
					
			if ( empty($sSortField) ) {
				$sSortField = "doc_num";
			}
			
			$nRowCount  = 1000;		//$_SESSION['userdata']['row_limit'];
			$nRowOffset = ($nPage-1) * $nRowCount;
			
			$sSortType = ($nSortType == DBAPI_SORT_DESC) ? "DESC" : "ASC";
			
			$bLimited = !empty( $nPage ) && !preg_match( PATERN_QUERY_LIMIT, $sQuery );
			
			$sQuery .= sprintf("ORDER BY %s %s\n", trim($sSortField,"_"), $sSortType);
			
			$oResponse->setSort($sSortField, $nSortType);
			
			if ( $bLimited ) {
				$sQuery .= sprintf("LIMIT %d, %d\n", $nRowOffset, $nRowCount);
			}
				
							
			$oRS = $db_finance->execute( $sQuery );	
			
			if ( $oRS ) {
				$oData = $oRS->getArray();
			} else $oData = array();
			
			$oRes = $db_finance->Execute("SELECT FOUND_ROWS()");

			$nRowTotal = current( $oRes->FetchRow() );
	
			$oResponse->setPaging(
				$nRowCount,
				$nRowTotal,
				ceil($nRowOffset / $nRowCount) + 1
			);
				
			$oResponse->setData( $oData );
			
			if ( $bLimited ) {
				$oResponse->setPaging($nRowCount, $nRowTotal, ceil($nRowOffset / $nRowCount) + 1);
			}
			
			$oResponse->setField("doc_num", 		"Номер", 	NULL);
			$oResponse->setField("doc_date", 		"Време", 	NULL);
			$oResponse->setField("deliverer_name", 	"Доставчик", NULL);
			$oResponse->setField("client_name", 	"Клиент", NULL);
			$oResponse->setField("total_sum", 		"Сума", NULL, NULL, NULL, NULL, array('DATA_FORMAT' => DF_CURRENCY) );
			
			$oResponse->setFieldLink('doc_num',			'openSaleDoc');
			$oResponse->setFieldLink('doc_date',		'openSaleDoc');
			$oResponse->setFieldLink('deliverer_name',	'openSaleDoc');
			$oResponse->setFieldLink('client_name',		'openSaleDoc');
			$oResponse->setFieldLink('total_sum',		'openSaleDoc');
	
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$val['doc_num'] = zero_padding( $val['doc_num'], 10);
				
				$oResponse->setDataAttributes( $key, 'doc_num', array('style' => 'text-align: right; width: 120px;') );	
				$oResponse->setDataAttributes( $key, 'doc_date', array('style' => 'text-align: center; width: 120px;') );	
				$oResponse->setDataAttributes( $key, 'total_sum', array('style' => 'text-align: right; width: 120px;') );	
			}			
			
		}			
	}
?>
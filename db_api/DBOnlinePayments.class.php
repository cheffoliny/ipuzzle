<?php
class DBOnlinePayments extends DBBase2 {
	private $oDBSalesDocs;
	
	public function __construct() {
		global $db_finance, $db_name_finance;
		
		$this->oDBSalesDocs = new DBMonthTable($db_name_finance, 'sales_docs_', $db_finance);
		
		parent::__construct($db_finance, 'easypay_notifications');
	}
	
//	public function getReport($oResponse, $aWhere = array()) {
//		global $db_name_my_telepol, $db_name_personnel;
//
//		$sWhere = '';
//		if (is_array($aWhere) && !empty($aWhere)) $sWhere = 'WHERE ' . implode(' AND ', $aWhere);
//
//		$sQuery = "
//			SELECT SQL_CALC_FOUND_ROWS
//				CONCAT(
//					pt.id,
//					'|',
//					IF (pt.status = 'paid' && pt.is_checked = 0, TRUE, FALSE)
//				)											AS id,
//				pt.id										AS transaction_num,
//				CASE pt.merchant
//					WHEN 'epay' THEN 'ePay'
//					ELSE 'N/A'
//				END											AS merchant,
//				CASE pt.status
//					WHEN 'created'	THEN 'Създаден'
//					WHEN 'failed'	THEN 'Изтекло време за плащане'
//					WHEN 'paid'		THEN 'Платен'
//					WHEN 'canceled'	THEN 'Отказано от платец'
//					WHEN 'pending'	THEN 'В процес на плащане'
//					ELSE 'N/A'
//				END											AS status,
//				pt.amount									AS amount,
//				pt.currency									AS currency,
//				pt.error_description						AS error,
//				IF (pt.status = 'paid',
//					IF (is_checked, 'Потвърден', 'Непотвърден'),
//					'---'
//				)											AS is_checked,
//				CONCAT_WS('\n',
//					CONCAT (
//						'Създал: ',
//						CONCAT_WS(' ',
//							IF (c_pmt.id,
//								CONCAT('Клиентската система >> ', c_pmt.name, ' - ', c_pmt.username),
//								'N/A'
//							),
//							IF (pt.created_time != '0000-00-00 00:00:00',
//								CONCAT('(', DATE_FORMAT(pt.created_time, '%d.%m.%Y %H:%i:%s'), ')'),
//								'(N/A)'
//							)
//						)
//					),
//					CONCAT (
//						'Редактирал: ',
//						CONCAT_WS(' ',
//							IF (pt.is_checked,
//								IF (u_p.id,
//									CONCAT_WS(' ', IF (u_p.fname != '', u_p.fname, NULL), IF (u_p.mname != '', u_p.mname, NULL), IF (u_p.lname != '', u_p.lname, NULL)),
//									'N/A'
//								),
//								IF (u_pmt.id,
//									CONCAT('Клиентската система >> ', u_pmt.name, ' - ', u_pmt.username),
//									'ePay'
//								)
//							),
//							IF (pt.updated_time != '0000-00-00 00:00:00',
//								CONCAT('(', DATE_FORMAT(pt.updated_time, '%d.%m.%Y %H:%i:%s'), ')'),
//								'(N/A)'
//							)
//						)
//					)
//				)											AS created_updated_user,
//
//				DATE_FORMAT(pt.created_time, '%d.%m.%Y %H:%i:%s') AS created_date_time,
//				IF (c_pmt.id,
//					CONCAT(c_pmt.name, ' - ', c_pmt.username),
//					'N/A'
//				)											AS user
//			FROM {$db_name_my_telepol}.payment_transactions AS pt
//			LEFT JOIN {$db_name_my_telepol}.account_users	AS c_pmt	ON c_pmt.id	= pt.created_user
//
//			LEFT JOIN {$db_name_personnel}.personnel		AS u_p		ON u_p.id	= pt.updated_user
//			LEFT JOIN {$db_name_my_telepol}.account_users	AS u_pmt	ON u_pmt.id = pt.updated_user
//			{$sWhere}
//		";
////		echo $sQuery;
//		$this->getResult($sQuery, 'id', DBAPI_SORT_DESC, $oResponse);
//
//		foreach ($oResponse->oResult->aData as $nRowNum => $aRowData) {
//			$oResponse->setDataAttributes($nRowNum, 'transaction_num'		, array('class' => 'center'									));
//			$oResponse->setDataAttributes($nRowNum, 'merchant'				, array('class' => 'center'									));
//			$oResponse->setDataAttributes($nRowNum, 'status'				, array('class' => 'center'									));
//			$oResponse->setDataAttributes($nRowNum, 'currency'				, array('class' => 'center'									));
//			$oResponse->setDataAttributes($nRowNum, 'amount'				, array('class' => 'right'									));
//			$oResponse->setDataAttributes($nRowNum, 'is_checked'			, array('class' => 'center'									));
//			$oResponse->setDataAttributes($nRowNum, 'user'					, array('class' => 'center'									));
//			$oResponse->setDataAttributes($nRowNum, 'created_updated_user'	, array('class' => 'center'									));
//			$oResponse->setDataAttributes($nRowNum, 'created_date_time'		, array('class' => 'center'									));
//			$oResponse->setDataAttributes($nRowNum, 'is_checked'			, array('class' => 'center'									));
//			$oResponse->setDataAttributes($nRowNum, 'show_items'			, array('class' => 'show_items', 'title' => 'Покажи редове'	));
//		}
//
//		$oResponse->setField('transaction_num'		,	'Транзакция №'																									);
//		$oResponse->setField('merchant'				,	'Тип разплащане'																								);
//		$oResponse->setField('status'				,	'Статус'																										);
//		$oResponse->setField('amount'				,	'Стойност'																										);
//		$oResponse->setField('currency'				,	'Валута'																										);
//		$oResponse->setField('user'					,	'Потребител'																									);
//		$oResponse->setField('created_date_time'	,	'Дата'																											);
//		$oResponse->setField('error'				,	'Възникнали грешки'																								);
//		$oResponse->setField('is_checked'			,	'Потвърден/Непотвърден'																							);
//		$oResponse->setField('created_updated_user'	,	''								, 'Създал/Редактирал'	,	'images/info.png'									);
//		$oResponse->setField('show_items'			,	''								, 'Редактиране'			,	'images/cash-register.gif',		'showItems',	''	);
//	}
	
//	public function getReportItem($oResponse) {
//		global $db_name_my_telepol, $db_name_personnel;
//
//		$nIDTransaction = intval(Params::get('id_transaction'));
//		if (!$nIDTransaction) throw new Exception('Невалиден параметър');
//
//		$sQuery = "
//			SELECT SQL_CALC_FOUND_ROWS
//				pti.id_item											AS id,
//				CASE pti.item_type
//					WHEN 'invoice' THEN 'Фактура'
//					ELSE 'N/A'
//				END													AS item_type,
//				pti.item_num										AS item_num,
//				pti.original_amount									AS original_amount,
//				pti.original_currency								AS original_currency,
//				pti.transaction_amount								AS transaction_amount,
//				pt.currency											AS transaction_currency,
//				#pti.description									AS description, #JIRA: MYTLP-5#
//				''													AS description,
//				CONCAT_WS('\n',
//					CONCAT (
//						'Създал: ',
//						CONCAT_WS(' ',
//							IF (c_pmt.id,
//								CONCAT('Клиентската система >> ', c_pmt.name, ' - ', c_pmt.username),
//								'N/A'
//							),
//							IF (
//								pti.created_time != '0000-00-00 00:00:00',
//								CONCAT('(', DATE_FORMAT(pti.created_time, '%d.%m.%Y %H:%i:%s'), ')'),
//								'(N/A)'
//							)
//						)
//					),
//					CONCAT (
//						'Редактирал: ',
//						CONCAT_WS(' ',
//							IF (u_pmt.id,
//								CONCAT('Клиентската система >> ', u_pmt.name, ' - ', u_pmt.username),
//								'N/A'
//							),
//							IF (pti.updated_time != '0000-00-00 00:00:00',
//								CONCAT('(', DATE_FORMAT(pti.updated_time, '%d.%m.%Y %H:%i:%s'), ')'),
//								'(N/A)'
//							)
//						)
//					)
//				)													AS created_updated_user
//			FROM {$db_name_my_telepol}.payment_transactions_items	AS pti
//			LEFT JOIN {$db_name_my_telepol}.payment_transactions	AS pt		ON pt.id	= pti.id_transaction
//			LEFT JOIN {$db_name_my_telepol}.account_users			AS c_pmt	ON c_pmt.id	= pt.created_user
//			LEFT JOIN {$db_name_my_telepol}.account_users			AS u_pmt	ON u_pmt.id = pt.updated_user
//			WHERE 1
//				AND pti.id_transaction = {$nIDTransaction}
//		";
//		$this->getResult($sQuery, 'id', DBAPI_SORT_DESC, $oResponse);
//
//		$aSaleDocNums = array();
//		foreach ($oResponse->oResult->aData as $nRowNum => $aRowData) {
//			$aSaleDocNums[$aRowData['item_num']] = $aRowData['item_num'];
//
//			$oResponse->setDataAttributes($nRowNum, 'item_type'				, array('class' => 'center'	));
//			$oResponse->setDataAttributes($nRowNum, 'item_num'				, array('class' => 'center'	));
//			$oResponse->setDataAttributes($nRowNum, 'original_amount'		, array('class' => 'right'	));
//			$oResponse->setDataAttributes($nRowNum, 'original_currency'		, array('class' => 'center'	));
//			$oResponse->setDataAttributes($nRowNum, 'transaction_amount'	, array('class' => 'right'	));
//			$oResponse->setDataAttributes($nRowNum, 'transaction_currency'	, array('class' => 'center'	));
//			$oResponse->setDataAttributes($nRowNum, 'created_updated_user'	, array('class' => 'center'	));
//		}
//
//		#JIRA: MYTLP-5#
//		if (!empty($aSaleDocNums)) {
//			$aEscapedSaleDocNums = array();
//			foreach ($aSaleDocNums as $mDocNum) {
//				$nEscapedDocNum = intval($mDocNum);
//				if ($nEscapedDocNum) $aEscapedSaleDocNums[] = $nEscapedDocNum;
//			}
//			if (!empty($aEscapedSaleDocNums)) {
//				$sEscapedSaleDocNums = implode(',', $aEscapedSaleDocNums);
//
//				global $db_finance, $db_name_finance;
//				$oDBMonthTable = new DBMonthTable($db_name_finance, 'sales_docs_', $db_finance);
//
//				$aClientData = array();
//				$oDBMonthTable->selectAssoc("
//					SELECT
//						sd.doc_num		AS id,
//						sd.client_name	AS client_name
//					FROM <table>		AS sd
//					WHERE 1
//						AND sd.doc_num IN ({$sEscapedSaleDocNums})
//				", $aClientData);
//
//				if (!empty($aClientData)) {
//					foreach ($oResponse->oResult->aData as $nRowNum => $aRowData) {
//						if (!array_key_exists($aRowData['item_num'], $aClientData)) continue;
//
//						$oResponse->oResult->aData[$nRowNum]['description'] = $aClientData[$aRowData['item_num']];
//					}
//				}
//			}
//		}
//
//		$oResponse->setField('item_type'			, 'Тип документ за разплащане'											);
//		$oResponse->setField('item_num'				, 'Номер на документ', null, null, 'printInvoice'						);
//		$oResponse->setField('original_amount'		, 'Стойност'															);
//		$oResponse->setField('original_currency'	, 'Валута'																);
//		$oResponse->setField('transaction_amount'	, 'Стойност за превод'													);
//		$oResponse->setField('transaction_currency'	, 'Валута за превод'													);
////		$oResponse->setField('description'			, 'Допълнителна информация'												);
//		$oResponse->setField('description'			, 'Клиент'																); #JIRA: MYTLP-5#
//		$oResponse->setField('created_updated_user'	, ''							, 'Създал/Редактирал', 'images/info.png');
//	}
//
//	public function checkTransaction($nTransactionID) {
//		$nTransactionID = intval($nTransactionID);
//		if (!$nTransactionID) throw new Exception('Невалидно ID на транзакция');
//
//		global $db_name_my_telepol, $db_name_finance;
//
//		$aTransactionData = $this->select("
//			SELECT
//				pti.item_type											AS `type`,
//				pti.id_item												AS id_item,
//				pti.original_amount										AS paid_sum
//			FROM {$db_name_my_telepol}.payment_transactions				AS pt
//			LEFT JOIN {$db_name_my_telepol}.payment_transactions_items	AS pti ON pti.id_transaction = pt.id
//			WHERE 1
//				AND pt.id			= {$nTransactionID}
//				AND pt.is_checked	= 0
//				AND pt.status		= 'paid'
//		");
//		if (empty($aTransactionData)) throw new Exception('Не е намерена редове на транзакция');
//		foreach ($aTransactionData as $aItemData) {
//			// финиши на платените елементи
//			switch ($aItemData['type']) {
//				case 'invoice':
//					$this->finishInvoice($aItemData['id_item'], $aItemData['paid_sum']);
//					break;
//				// ...
//				default: throw new Exception('Не е намерен такъв "finish"');
//			}
//		}
//
//		$aUpdateData = array(
//			'id'			=> $nTransactionID,
//			'is_checked'	=> 1
//		);
//		$this->update($aUpdateData);
//		return true;
//	}
	
	private function finishInvoice($nInvoiceID, $dPaidSum) {
		return true;
//		if (!$this->oDBSalesDocs->isValidID($nInvoiceID)) throw new Exception('Невалидно ID на фактура');
//		$dPaidSum = floatval($dPaidSum);
//		if ($dPaidSum <= 0) throw new Exception('Невалиднa заплатена сума към фактура');
//		
//		$aUpdateData = array(
//			'id'		=> $nInvoiceID,
//			'order_sum' => $dPaidSum
//		);
//		$this->oDBSalesDocs->update($aUpdateData);
	}

    /**
     * За api_online_payments
     *
     * @param DBResponse $oResponse
     * @param $aParams
     *
     * @throws Exception
     */
    public function getReportNew(DBResponse $oResponse, $aParams) {
        global $db_name_finance;

        $nDateFrom = (!isset($aParams['sFromDate'])) ? date('Y-m-d') : jsDateToMySQLDate($aParams['sFromDate']);
        $nDateTo   = (!isset($aParams['sToDate']))   ? date('Y-m-d') : jsDateToMySQLDate($aParams['sToDate']);

        $sQ = "
			SELECT SQL_CALC_FOUND_ROWS
				e.id 									AS __key,
				e.id									AS transaction_num,
				e.requestTDate							AS t_date,
				#CASE e.provider
				#	WHEN 0 THEN 0
				#	WHEN 1 THEN 'EasyPay'
				#	WHEN 2 THEN 'FastPay'
				#	WHEN 3 THEN 'CashTerminal'
				#	WHEN 4 THEN 'TransCard'
				#END 									AS provider,
				ep.name AS provider,
				CASE e.returnStatus
					WHEN '00' THEN 'OK'
					WHEN '94' THEN 'Повторение'
					WHEN '96' THEN 'Неизвестно'
					WHEN '01' THEN 'CT Грешка'
					WHEN '02' THEN 'Клиента не е намерен'
					WHEN '03' THEN 'Няма задължение'
					WHEN '04' THEN 'Некоректна сума'
				END 									AS `status`,
				e.requestAmount		 					AS amount,
				er.returnClientName						AS `name`,
				er.returnInvoices,
				er.requestIDClient						AS 'client_id'

			FROM {$db_name_finance}.easypay_notifications e
				JOIN {$db_name_finance}.easypay_request_duty er ON er.id = e.id_request
				JOIN {$db_name_finance}.easypay_provider ep ON ep.id = e.provider

			WHERE 1
				AND DATE(e.requestTDate) >= '{$nDateFrom}'
				AND DATE(e.requestTDate) <= '{$nDateTo}'
		";

        if(isset($aParams['payment_type']) && $aParams['payment_type'] === 'error') {
            $sQ .= " AND e.returnStatus IN('04') ";
        }
        if(isset($aParams['payment_provider']) && $aParams['payment_provider'] !== 'all') {
            $prov = (int) $aParams['payment_provider'];
            $sQ .= " AND e.provider = {$prov} ";
        }

        APILog::Log(111,$sQ);

        $this->getResult($sQ, 'e.id', DBAPI_SORT_DESC, $oResponse);

        $oResponse->setField('transaction_num'  , 'Транзакция №'    , 'Номер на транзакцията'   , null, null, null, array('style' => 'width:100px;')						    );
        $oResponse->setField('t_date'           , 'Дата'            , 'Дата на транзакцията'    , null, null, null, array("DATA_FORMAT" => DF_DATE, 'style'=>'width:120px;')    );
        $oResponse->setField('returnInvoices'	, 'Документи'       , 'Редакция на платежни документи', null, null, null                                                        );
        $oResponse->setField('provider'         , 'Провайдър'		, 'Провайдър'               , null, null, null																);
        $oResponse->setField('status'			, 'Статус'          , 'Статус на транзакцията'  , null, null, null, array('style' => 'width:100px;')							);
        $oResponse->setField('amount'			, 'Стойност'        , 'Стойност на транзакцията', null, null, null, array("DATA_FORMAT" => DF_CURRENCY )                        );
        $oResponse->setField('name'			    , 'Клиент'          , 'Клиент'                  , null																			);

        $nTotal = 0;

		$totalTmpData = $this->selectAssoc($sQ);

		foreach ( $totalTmpData as $key => $data ) {
			$nTotal += $data['amount'];

		}



        foreach ( $oResponse->oResult->aData as $key => &$aRes ) {

            $oResponse->setDataAttributes($key, 'returnInvoices', array('data-provider' => $aRes['provider'])   );
            $oResponse->setDataAttributes($key, 'name'          , array('data-client' => $aRes['client_id'])    );

//            $nTotal += $aRes['amount'];
            $aRes['amount'] = ($aRes['amount'] / 100);
        }

        $oResponse->addTotal('amount', ($nTotal / 100) );
    }
}
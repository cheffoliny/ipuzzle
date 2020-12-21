<?php
//
//	require_once('include/db_include.inc.php');
//
//	class DBTransfers extends DBMonthTable
//	{
//
//		function __construct()
//		{
//			global $db_name_finance, $db_finance;
//
//			parent::__construct( $db_name_finance, PREFIX_TRANSFERS, $db_finance );
//		}
//
//		function jsDateEndToTimestamp( $sDate )
//		{
//			if( !empty( $sDate ) )
//			{
//				@list( $d, $m, $y ) = explode( ".", $sDate );
//
//				if( @checkdate( $m, $d, $y ) )
//				{
//					return mktime( 23, 59, 59, $m, $d, $y );
//				}
//			}
//
//			return 0;
//		}
//
//		public function getReport( DBResponse $oResponse, $aParams )
//		{
//			global $db_name_personnel, $db_name_sod;
//
//			//Initialize
//			$oCashier = new DBCashiers();
//
//			$nTimeFrom		= jsDateToTimestamp ( $aParams['sFromDate'] );
//			$nTimeTo		= jsDateToTimestamp ( $aParams['sToDate'] );
//			$nFullTimeTo 	= $this->jsDateEndToTimestamp( $aParams['sToDate'] );
//
//			$nAvailablePerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
//			$sAvailableAccounts = $oCashier->getBankAccountsOpperate( $nAvailablePerson );
//			if( empty( $sAvailableAccounts ) )$sAvailableAccounts = "-1";
//
//			$nSelf = ( isset( $aParams['nSelf'] ) && !empty( $aParams['nSelf'] ) ) ? $aParams['nSelf'] : 0;
//			$nToMe = ( isset( $aParams['nToMe'] ) && !empty( $aParams['nToMe'] ) ) ? $aParams['nToMe'] : 0;
//			//End Initialize
//
//			//Form Basic Query
//			$sQuery = "
//					SELECT SQL_CALC_FOUND_ROWS
//						tra.id AS id,
//						LPAD( tra.num, 10, 0 ) AS transfer_num,
//						DATE_FORMAT( tra.transfer_date, '%d.%m.%Y %H:%i:%s' ) AS transfer_date,
//						CONCAT( tra.total_sum, ' лв.' ) AS transfer_sum,
//						CASE tra.expense_account_type
//							WHEN 'person' THEN CONCAT_WS( ' ', per_exp.fname, per_exp.mname, per_exp.lname )
//							WHEN 'bank' THEN ban_exp.name_account
//						END AS transfer_expenditure_account,
//						CASE tra.earning_account_type
//							WHEN 'person' THEN CONCAT_WS( ' ', per_ear.fname, per_ear.mname, per_ear.lname )
//							WHEN 'bank' THEN ban_ear.name_account
//						END AS transfer_income_account,
//						IF
//						(
//							tra.created_user,
//							CONCAT( CONCAT_WS( ' ', per_cre.fname, per_cre.mname, per_cre.lname ), ' ' , DATE_FORMAT( tra.created_time, '%d.%m.%Y %H:%i:%s' ) ),
//							''
//						) AS transfer_created,
//						IF
//						(
//							tra.confirm_user,
//							CONCAT( CONCAT_WS( ' ', per_con.fname, per_con.mname, per_con.lname ), ' ' , DATE_FORMAT( tra.confirm_time, '%d.%m.%Y %H:%i:%s' ) ),
//							''
//						) AS transfer_confirm,
//						tra.is_confirm AS is_confirm,
//						tra.is_canceled AS is_canceled,
//						IF( tra.is_confirm, 'Потвърден', 'Не потвърден' ) AS transfer_confirmed,
//						IF
//						(
//							(
//								CASE tra.expense_account_type
//									WHEN 'person' THEN tra.expense_id_person = {$nAvailablePerson}
//									WHEN 'bank' THEN tra.expense_id_bank_account IN ( {$sAvailableAccounts} )
//								END
//								AND
//								CASE tra.earning_account_type
//									WHEN 'person' THEN tra.earning_id_person = {$nAvailablePerson}
//									WHEN 'bank' THEN tra.earning_id_bank_account IN ( {$sAvailableAccounts} )
//								END
//							),
//							'sbtntgПотвърдиebtntg',
//							''
//						) AS is_to_confirm
//					FROM
//						<table> tra
//					LEFT JOIN
//						{$db_name_personnel}.personnel per_exp ON ( per_exp.id = tra.expense_id_person AND tra.expense_account_type = 'person' )
//					LEFT JOIN
//						{$db_name_sod}.offices off_exp ON ( off_exp.id = per_exp.id_office AND tra.expense_account_type = 'person' )
//					LEFT JOIN
//						bank_accounts ban_exp ON ( ban_exp.id = tra.expense_id_bank_account AND tra.expense_account_type = 'bank' )
//					LEFT JOIN
//						{$db_name_personnel}.personnel per_ear ON ( per_ear.id = tra.earning_id_person AND tra.earning_account_type = 'person' )
//					LEFT JOIN
//						{$db_name_sod}.offices off_ear ON ( off_ear.id = per_ear.id_office AND tra.earning_account_type = 'person' )
//					LEFT JOIN
//						bank_accounts ban_ear ON ( ban_ear.id = tra.earning_id_bank_account AND tra.earning_account_type = 'bank' )
//					LEFT JOIN
//						{$db_name_personnel}.personnel per_cre ON per_cre.id = tra.created_user
//					LEFT JOIN
//						{$db_name_personnel}.personnel per_con ON per_con.id = tra.confirm_user
//					WHERE
//						( UNIX_TIMESTAMP( tra.transfer_date ) >= {$nTimeFrom} AND UNIX_TIMESTAMP( tra.transfer_date ) <= {$nFullTimeTo} )
//						AND tra.to_arc = 0
//						AND CASE tra.expense_account_type
//							WHEN 'person' THEN per_exp.to_arc = 0
//							WHEN 'bank' THEN ban_exp.to_arc = 0
//						END
//						AND CASE tra.earning_account_type
//							WHEN 'person' THEN per_ear.to_arc = 0
//							WHEN 'bank' THEN ban_ear.to_arc = 0
//						END
//			";
//
//			if( $nSelf )
//			{
//				$sQuery .= "
//						AND tra.is_canceled = 0
//				";
//			}
//			//End Form Basic Query
//
//			//Additional Filtering
//			if( isset( $aParams['nIDBankAccount'] ) && !empty( $aParams['nIDBankAccount'] ) ||
//				isset( $aParams['nIDFirm'] ) 		&& !empty( $aParams['nIDFirm'] )			)
//			{
//				$sQuery .= "
//						AND
//						(
//				";
//			}
//
//			if( isset( $aParams['nIDBankAccount'] ) && !empty( $aParams['nIDBankAccount'] ) )
//			{
//				$sQuery .= "
//							(
//								( tra.expense_account_type = 'bank' AND tra.expense_id_bank_account = {$aParams['nIDBankAccount']} )
//								OR
//								( tra.earning_account_type = 'bank' AND tra.earning_id_bank_account = {$aParams['nIDBankAccount']} )
//							)
//				";
//
//				if( isset( $aParams['nIDFirm'] ) && !empty( $aParams['nIDFirm'] ) )
//				{
//					$sQuery .= " OR ";
//				}
//			}
//
//			if( isset( $aParams['nIDFirm'] ) && !empty( $aParams['nIDFirm'] ) )
//			{
//				$sQuery .= "
//							(
//				";
//			}
//
//			if( isset( $aParams['nIDFirm'] ) && !empty( $aParams['nIDFirm'] ) )
//			{
//				if( !$nSelf )
//				{
//					$sQuery .= "
//								(
//									( tra.expense_account_type = 'person' AND off_exp.id_firm = {$aParams['nIDFirm']} )
//									OR
//									( tra.earning_account_type = 'person' AND off_ear.id_firm = {$aParams['nIDFirm']} )
//								)
//					";
//				}
//				else
//				{
//					if( $nToMe )
//					{
//						$sQuery .= "
//								( tra.earning_account_type = 'person' AND off_ear.id_firm = {$aParams['nIDFirm']} )
//						";
//					}
//					else
//					{
//						$sQuery .= "
//								( tra.expense_account_type = 'person' AND off_exp.id_firm = {$aParams['nIDFirm']} )
//						";
//					}
//				}
//			}
//
//			if( isset( $aParams['nIDOffice'] ) && !empty( $aParams['nIDOffice'] ) )
//			{
//				if( !$nSelf )
//				{
//					$sQuery .= "
//								AND
//								(
//									( tra.expense_account_type = 'person' AND off_exp.id = {$aParams['nIDOffice']} )
//									OR
//									( tra.earning_account_type = 'person' AND off_ear.id = {$aParams['nIDOffice']} )
//								)
//					";
//				}
//				else
//				{
//					if( $nToMe )
//					{
//						$sQuery .= "
//								AND ( tra.earning_account_type = 'person' AND off_ear.id = {$aParams['nIDOffice']} )
//						";
//					}
//					else
//					{
//						$sQuery .= "
//								AND ( tra.expense_account_type = 'person' AND off_exp.id = {$aParams['nIDOffice']} )
//						";
//					}
//				}
//			}
//
//			if( isset( $aParams['nIDPerson'] ) && !empty( $aParams['nIDPerson'] ) )
//			{
//				if( !$nSelf )
//				{
//					$sQuery .= "
//								AND
//								(
//									( tra.expense_account_type = 'person' AND tra.expense_id_person = {$aParams['nIDPerson']} )
//									OR
//									( tra.earning_account_type = 'person' AND tra.earning_id_person = {$aParams['nIDPerson']} )
//								)
//					";
//				}
//				else
//				{
//					if( $nToMe )
//					{
//						$sQuery .= "
//								AND ( tra.earning_account_type = 'person' AND tra.earning_id_person = {$aParams['nIDPerson']} )
//						";
//					}
//					else
//					{
//						$sQuery .= "
//								AND ( tra.expense_account_type = 'person' AND tra.expense_id_person = {$aParams['nIDPerson']} )
//						";
//					}
//				}
//			}
//
//			if( isset( $aParams['nIDFirm'] ) && !empty( $aParams['nIDFirm'] ) )
//			{
//				$sQuery .= "
//							)
//				";
//			}
//
//			if( isset( $aParams['nIDBankAccount'] ) && !empty( $aParams['nIDBankAccount'] ) ||
//				isset( $aParams['nIDFirm'] ) 		&& !empty( $aParams['nIDFirm'] )			)
//			{
//				$sQuery .= "
//						)
//				";
//			}
//
//			if( isset( $aParams['nIsConfirm'] ) && $aParams['nIsConfirm'] != 2 )
//			{
//				$sQuery .= "
//						AND tra.is_confirm = {$aParams['nIsConfirm']}
//				";
//			}
//			//End Additional Filtering
//
//			$this->makeUnionSelect( $sQuery, $nTimeFrom, $nTimeTo );
//
//			$this->getResult( $sQuery, 'transfer_num', DBAPI_SORT_DESC, $oResponse );
//
//			foreach( $oResponse->oResult->aData as $key => &$aRow )
//			{
//				$aRow['chk'] = 0;
//
//				if( empty( $aRow['is_confirm'] ) )
//				{
//					$oResponse->setRowAttributes( $aRow['id'], array( "style" => "font-weight: bold;" ) );
//				}
//				if( !empty( $aRow['is_canceled'] ) )
//				{
//					$oResponse->setRowAttributes( $aRow['id'], array( "style" => "font-style: italic; color: #969696;" ) );
//				}
//
//				if( $aRow['is_to_confirm'] != '' )
//				{
//					$oResponse->setDataAttributes( $key, 'is_to_confirm', array( "onclick" => "confirmTransfer( " . $aRow['id'] . " )" ) );
//					$oResponse->setFormElement( "form1", "chk[" . $aRow['id'] . "]", array( 'value' => '0' ) );
//				}
//				else
//				{
//					$oResponse->setFormElement( "form1", "chk[" . $aRow['id'] . "]", array( 'visibility' => 'hidden' ) );
//					$oResponse->setFormElement( "form1", "chk[" . $aRow['id'] . "]", array( 'value' => '1' ) );
//				}
//			}
//
//			$oResponse->setField( 'chk', '', NULL, NULL, NULL, NULL, array( 'type' => 'checkbox' ) );
//			$oResponse->setFieldData( 'chk', 'input', array( 'type' => 'checkbox', 'exception' => 'false' ) );
//			$oResponse->setFieldAttributes( 'chk', array( 'style' => 'width: 25px;' ) );
//
//			$oResponse->setFormElement( 'form1', 'sel', array(), '' );
//			$oResponse->setFormElementChild( 'form1', 'sel', array( 'value' => '1' ), "--- Маркирай всички ---" );
//			$oResponse->setFormElementChild( 'form1', 'sel', array( 'value' => '2' ), "--- Отмаркирай всички ---" );
//			$oResponse->setFormElementChild( 'form1', 'sel', array( 'value' => '0' ), "------" );
//			$oResponse->setFormElementChild( 'form1', 'sel', array( 'value' => '3' ), "--- Потвърди ---" );
//
//			$oResponse->setField( 'transfer_num', 					'Номер', 				'Сортирай по Номер', 				NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_ZEROLEADNUM ) );
//			$oResponse->setField( 'transfer_date', 					'Дата',					'Сортирай по Дата', 				NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_DATETIME ) );
//			$oResponse->setField( 'transfer_sum', 					'Сума', 				'Сортирай по Сума', 				NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_CURRENCY ) );
//			$oResponse->setField( 'transfer_expenditure_account', 	'Сметка - Разход', 		'Сортирай по Сметка - Разход', 		NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
//			$oResponse->setField( 'transfer_income_account', 		'Сметка - Приход', 		'Сортирай по Сметка - Приход', 		NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
//			$oResponse->setField( 'transfer_created', 				'Създал Трансфера', 	'Сортирай по Създал Трансфера', 	NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
//			$oResponse->setField( 'transfer_confirm', 				'Потвърдил Трансфера', 	'Сортирай по Потвърдил Трансфера', 	NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
//			$oResponse->setField( 'transfer_confirmed', 			'Статус', 				'Сортирай по Статус', 				NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
//			$oResponse->setField( 'is_to_confirm', 					'', 					'' );
//
//			$oResponse->setFieldLink( 'transfer_num', 'openTransfer' );
//		}
//
//		/**
//		 * Функцията проверява, дали от / към потребител са налични трансфери, за период от време.
//		 *
//		 * @param int $nIDPerson
//		 * @param string $sFromDate 		( "d.m.Y" )
//		 * @param string $sToDate			( "d.m.Y" )
//		 * @param string $sSendOrReceive 	( "s" или "r", по подразбиране "r" )
//		 * @return true / false
//		 */
//		public function isPersonOperatingTransfers( $nIDPerson, $sFromDate, $sToDate, $sSendOrReceive )
//		{
//			//Validation
//			if( empty( $nIDPerson ) && !is_numeric( $nIDPerson ) )
//			{
//				return false;
//			}
//
//			$sSendOrReceive = ( $sSendOrReceive == "s" ) ? "s" : "r";
//			//End Validation
//
//			$nTimeFrom		= jsDateToTimestamp ( $sFromDate );
//			$nTimeTo		= jsDateToTimestamp ( $sToDate );
//			$nFullTimeTo 	= $this->jsDateEndToTimestamp( $sToDate );
//
//			$aData 			= array();
//
//			$sQuery = "
//					SELECT
//						t.id
//					FROM
//						<table> t
//					WHERE
//						( UNIX_TIMESTAMP( t.transfer_date ) >= {$nTimeFrom} AND UNIX_TIMESTAMP( t.transfer_date ) <= {$nFullTimeTo} )
//						AND t.to_arc = 0
//						AND t.is_confirm = 0
//						AND t.is_canceled = 0
//			";
//
//			if( $sSendOrReceive == "s" )
//			{
//				$sQuery .= "
//						AND t.expense_account_type = 'person'
//						AND t.expense_id_person = {$nIDPerson}
//				";
//			}
//
//			if( $sSendOrReceive == "r" )
//			{
//				$sQuery .= "
//						AND t.earning_account_type = 'person'
//						AND t.earning_id_person = {$nIDPerson}
//				";
//			}
//
//			$this->select( $sQuery, $aData, $nTimeFrom, $nTimeTo );
//
//			if( !empty( $aData ) )return true;
//			else return false;
//		}
//	}
//
//?>
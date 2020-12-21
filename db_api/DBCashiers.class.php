<?php
	class DBCashiers extends DBBase2
	{
		public function __construct()
		{
			global $db_finance;
			
			parent::__construct( $db_finance, 'cashier' );
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$oNomenclaturesExpenses = new DBNomenclaturesExpenses();
			$oBankAccounts = new DBBankAccounts();
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						c.id,
						CONCAT_WS( ' ', pe.fname, pe.mname, pe.lname ) AS person_name,
						c.nomenclatures_expenses_create,
						c.bank_accounts_operate,
						c.bank_accounts_watch,
						IF(
							p.id,
							CONCAT(
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
								' (',
								DATE_FORMAT( c.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
								),
								''
								) AS updated_user
						FROM cashier c
						LEFT JOIN {$db_name_personnel}.personnel p ON c.updated_user = p.id
						LEFT JOIN {$db_name_personnel}.personnel pe ON pe.id = c.id_person
						WHERE
							c.to_arc = 0
			";
			
			$this->getResult( $sQuery, "person_name", DBAPI_SORT_ASC, $oResponse );
			$aData = & $oResponse->oResult->aData;
			
			$oResponse->setField( "person_name", 				"Име на Касиер", 				"", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "nomenclatures_expenses", 	"Разходи по номенклатури", 		"", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "bank_account_opperate", 		"Банкови сметки - опериране", 	"", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "bank_account_track", 		"Банкови сметки - следене", 	"", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "updated_user", 				"Последна редакция", 			"" );
			$oResponse->setField( '', 							'', 							'', 'images/cancel.gif', 'deleteCashier', '' );
			
			$oResponse->setFieldLink( "person_name", "openCashier" );
			
			foreach( $aData as $key => $value )
			{
				$aNomenclaturesToList = array();
				$aBankAccountsOpperateToList = array();
				$aBankAccountsTrackToList = array();
				
				$aNomenclaturesToList = explode( ",", $value['nomenclatures_expenses_create'] );
				$aData[$key]['nomenclatures_expenses'] = "";
				foreach( $aNomenclaturesToList as $nIDNomenclatureExpense )
				{
					if( !empty( $nIDNomenclatureExpense ) )
					{
						$aNomenclatureExpense = $oNomenclaturesExpenses->getRecord( $nIDNomenclatureExpense );
						if( isset( $aNomenclatureExpense['name'] ) )$aData[$key]['nomenclatures_expenses'] .= $aNomenclatureExpense['name'] . "; ";
					}
				}
				
				if( !empty( $aData[$key]['nomenclatures_expenses'] ) )
				{
					$aData[$key]['nomenclatures_expenses'] = substr( $aData[$key]['nomenclatures_expenses'], 0, strlen( $aData[$key]['nomenclatures_expenses'] ) - 2 );
				}
				
				$aBankAccountsOpperateToList = explode( ",", $value['bank_accounts_operate'] );
				$aData[$key]['bank_account_opperate'] = "";
				foreach( $aBankAccountsOpperateToList as $nIDBankAccountOpperate )
				{
					if( !empty( $nIDBankAccountOpperate ) )
					{
						$aBankAccountOperate = $oBankAccounts->getRecord( $nIDBankAccountOpperate );
						if( isset( $aBankAccountOperate['name_account'] ) )$aData[$key]['bank_account_opperate'] .= $aBankAccountOperate['name_account'] . "; ";
					}
				}
				
				if( !empty( $aData[$key]['bank_account_opperate'] ) )
				{
					$aData[$key]['bank_account_opperate'] = substr( $aData[$key]['bank_account_opperate'], 0, strlen( $aData[$key]['bank_account_opperate'] ) - 2 );
				}
				
				$aBankAccountsTrackToList = explode( ",", $value['bank_accounts_watch'] );
				$aData[$key]['bank_account_track'] = "";
				foreach( $aBankAccountsTrackToList as $nIDBankAccountTrack )
				{
					if( !empty( $nIDBankAccountTrack ) )
					{
						$aBankAccountTrack = $oBankAccounts->getRecord( $nIDBankAccountTrack );
						if( isset( $aBankAccountTrack['name_account'] ) )$aData[$key]['bank_account_track'] .= $aBankAccountTrack['name_account'] . "; ";
					}
				}
				
				if( !empty( $aData[$key]['bank_account_track'] ) )
				{
					$aData[$key]['bank_account_track'] = substr( $aData[$key]['bank_account_track'], 0, strlen( $aData[$key]['bank_account_track'] ) - 2 );
				}
			}
			
			//$oResponse->setData( $aData );
		}
		
		public function isCashierUnique( $nIDPerson )
		{
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				return true;
			}
			
			$sQuery = "
					SELECT
						*
					FROM
						cashier
					WHERE
						to_arc = 0
						AND id_person = {$nIDPerson}
			";
			
			$aData = $this->select( $sQuery );
			
			if( empty( $aData ) )return true;
			else return false;
		}
		
		public function getBankAccountsWatch( $nIDPerson )
		{
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				return '';
			}
			
			$sQuery = "
					SELECT
						bank_accounts_watch
					FROM
						cashier
					WHERE
						to_arc = 0
						AND id_person = {$nIDPerson}
					LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( !empty( $aData ) )
			{
				return $aData['bank_accounts_watch'];
			}
			else
			{
				return '';
			}
		}
		
		public function getBankAccountsOpperate( $nIDPerson )
		{
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				return '';
			}
			
			$sQuery = "
					SELECT
						bank_accounts_operate
					FROM
						cashier
					WHERE
						to_arc = 0
						AND id_person = {$nIDPerson}
					LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( !empty( $aData ) )
			{
				return $aData['bank_accounts_operate'];
			}
			else
			{
				return '';
			}
		}
		
		/**
		 * Функцията връща данните за касиер по зададено ID на потребител 
		 * 
		 * @author Павел Петров
		 * @name getByIDPerson
		 * 
		 * @param int $nIDPerson - ID на потребител
		 * @return array масив с данните за касиера
		 */			
		public function getByIDPerson($nIDPerson) {
			global $db_name_finance;
			
			$aData = array();
			
			if ( empty($nIDPerson) || !is_numeric($nIDPerson) ) {
				return array();
			}
			
			$sQuery = "
					SELECT
						*
					FROM {$db_name_finance}.cashier
					WHERE to_arc = 0
						AND id_person = {$nIDPerson}
					LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if ( !empty($aData) ) {
				return $aData;
			} else {
				return array();
			}
		}		
			
	}
?>
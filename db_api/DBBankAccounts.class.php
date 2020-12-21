<?php
	class DBBankAccounts extends DBBase2
	{
		public function __construct()
		{
			global $db_finance;
			
			parent::__construct( $db_finance, 'bank_accounts' );
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						ba.id,
						IF ( ba.cash, CONCAT(ba.name_account, ' [каса]'), CONCAT(ba.name_account, ' [банка]') ) AS name_account,
						IF ( ba.cash, 'Касова сметка', ba.name_bank ) as name_bank,
						ba.iban,
						ba.bic,
						IF(
							p.id,
							CONCAT(
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
								' (',
								DATE_FORMAT( ba.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
								),
								''
								) AS updated_user
						FROM bank_accounts ba
						LEFT JOIN {$db_name_personnel}.personnel p ON ba.updated_user = p.id
						WHERE ba.to_arc = 0
			";
			
			$this->getResult( $sQuery, 'name_account', DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField( "name_account", 	"Наименование на сметката", "Сортирай по наименование на сметката", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "name_bank", 		"Име на банката", 			"Сортирай по име на банката", 			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "iban", 			"IBAN", 					"Сортирай по IBAN", 					NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "bic", 			"BIC", 						"Сортирай по BIC", 						NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "updated_user", 	"Последна редакция", 		"Сортирай по последна редакция" );
			$oResponse->setField( '', 				'', 						'', 'images/cancel.gif', 'deleteAccount', '' );
			
			$oResponse->setFieldLink( "name_account", "openAccount" );
		}
		
		public function getAllRecords()
		{
			$sQuery = "
				SELECT 
					id,
					IF ( cash, CONCAT(name_account, ' [каса]'), CONCAT(name_account, ' [банка]') ) AS name,
					iban AS iban
				FROM bank_accounts
				WHERE
					to_arc = 0
				ORDER BY name
			";
			
			return $this->select( $sQuery );
		}
		
		public function getAllRecords2( $cash = 1 ) {
			
			$cash = !empty($cash) ? 1 : 0;
			
			$sQuery = "
				SELECT 
					id,
					IF ( cash, CONCAT(name_account, ' [каса]'), CONCAT(name_account, ' [банка]') ) AS name,
					iban AS iban
				FROM bank_accounts
				WHERE to_arc = 0
					AND cash = {$cash}
				ORDER BY name
			";
			
			return $this->select( $sQuery );
		}		
		
		public function getByPersonForOperate($nIDPerson) {
			
			$sQuery = "
				SELECT
					id,
					IF ( cash, CONCAT(name_account, ' [каса]'), CONCAT(name_account, ' [банка]') ) AS name_account
				FROM bank_accounts 
				WHERE find_in_set(id, (	SELECT 
											bank_accounts_operate
										FROM cashier
										WHERE id_person = {$nIDPerson}
											AND to_arc = 0
										)
									)
					AND to_arc = 0
				ORDER BY name_account
			";
			
			return $this->selectAssoc($sQuery);
			
		}
		
		public function getByPersonForOperate2($nIDPerson) {
			
			$sQuery = "
				SELECT
					id,
					IF ( cash, CONCAT(name_account, ' [каса]'), CONCAT(name_account, ' [банка]') ) AS name_account,
					IF ( cash, 'cash', 'bank' ) as type
				FROM bank_accounts 
				WHERE find_in_set(id, (	SELECT 
											bank_accounts_operate
										FROM cashier
										WHERE id_person = {$nIDPerson}
											AND to_arc = 0
										)
									)
					AND to_arc = 0
				ORDER BY name_account
			";
			
			return $this->selectAssoc($sQuery);
			
		}		
		
		public function getBankAccountsForPerson( $nIDPerson )
		{
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				throw new Exception( "Невалиден служител!", DBAPI_ERR_INVALID_PARAM );
			}
			
			$aData = array();
			
			$sQuery = "
					SELECT
						ca.bank_accounts_watch AS accounts
					FROM
						cashier ca
					WHERE
						ca.to_arc = 0
						AND ca.id_person = {$nIDPerson}
					LIMIT 1
			";
			
			$aAllData = array();
			$aAllData = $this->selectOnce( $sQuery );
			
			if( isset( $aAllData['accounts'] ) )
			{
				$aCycleData = explode( ",", $aAllData['accounts'] );
				foreach( $aCycleData as $key => $value )
				{
					$sQuery = "
							SELECT
								id,
								IF ( cash, CONCAT(name_account, ' [каса]'), CONCAT(name_account, ' [банка]') ) as name_account
							FROM
								bank_accounts
							WHERE
								to_arc = 0
								AND id = {$value}
							LIMIT 1
					";
					
					$aTemp = array();
					$aTemp = $this->selectOnce( $sQuery );
					if( !empty( $aTemp ) )
					{
						$aData[$key]['id'] = $aTemp['id'];
						$aData[$key]['name'] = $aTemp['name_account'];
					}
				}
			}
			
			return $aData;
		}
		
		public function getBankAccountsOperate( $nIDPerson )
		{
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				throw new Exception( "Невалиден служител!", DBAPI_ERR_INVALID_PARAM );
			}
			
			$sQuery = "
				SELECT
					id AS id,
					IF ( cash, CONCAT(name_account, ' [каса]'), CONCAT(name_account, ' [банка]') ) AS name,
					iban AS iban
				FROM
					bank_accounts
				WHERE
					FIND_IN_SET(
						id,
						(
							SELECT 
								bank_accounts_operate
							FROM
								cashier
							WHERE
								id_person = {$nIDPerson}
								AND to_arc = 0
						)
					)
					AND to_arc = 0
				ORDER BY name
			";
			
			return $this->select( $sQuery );
		}
		
		public function getBankAccoutsForFirm( $nIDFirm )
		{
			if( empty( $nIDFirm ) && !is_numeric( $nIDFirm ) )
			{
				return array();
			}
			
			$sQuery = "
					SELECT
						id AS id,
						IF ( cash, CONCAT(name_account, ' [каса]'), CONCAT(name_account, ' [банка]') ) AS name
					FROM
						bank_accounts
					WHERE
						to_arc = 0
						AND FIND_IN_SET( {$nIDFirm}, ids_typical_firms )
					ORDER BY
						name
			";
			
			return $this->select( $sQuery );
		}
		
		// Pavel
		public function getBankAccoutById( $nID ) {
			
			$nID = is_numeric($nID) ? $nID : 0;
			
			$sQuery = "
				SELECT
					id,
					IF ( cash, CONCAT(name_account, ' [каса]'), CONCAT(name_account, ' [банка]') ) AS name_account,
					name_bank,
					iban,
					bic,
					ids_typical_firms
				FROM bank_accounts
				WHERE id = {$nID}
			";
			
			return $this->selectOnce( $sQuery );
		}			
		
		/**
		 * Функцията връща всички банкови сметки според правата на достъп
		 * 
		 * @author Павел Петров
		 * @name getAllAccounts
		 * 
		 * @param enum $cash {0, 1, 2} - 0: Само по банка; 1: Само в каса; 2: Всички;
		 * @param enum $title {0, 1} - 0: Без пояснение за тип на сметка; 1: С пояснение за тип;
		 * @param enum $user_restrict {0, 1, 2} - 0: Без рестрикция; 1: Само сметките, за които може да оперира; 2: Само сметките, за които може да вижда;
		 * @return array масив с банковите сметки според оказания вид и рестрикция
		 */		
		public function getAllAccounts( $cash = 2, $title = 0, $user_restrict = 1 ) {
			global $db_name_finance;
			
			$nIDUser = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
			
			$sQuery = "
				SELECT 
					ba.id,
					IF ( {$title} = 1, 
						IF ( ba.cash, CONCAT(ba.name_account, ' [каса]'), CONCAT(ba.name_account, ' [банка]') ), 
						ba.name_account
					) AS name,
					ba.iban AS iban,
					IF ( ba.cash, 'cash', 'bank' ) as type
				FROM {$db_name_finance}.bank_accounts ba
			";
			
			if ( $user_restrict == 1 ) {
				$sQuery .= "
					LEFT JOIN {$db_name_finance}.cashier c ON (FIND_IN_SET(ba.id, c.bank_accounts_operate) AND c.to_arc = 0)
				";
			}
			
			if ( $user_restrict == 2 ) {
				$sQuery .= "
					LEFT JOIN {$db_name_finance}.cashier c ON (FIND_IN_SET(ba.id, c.bank_accounts_watch) AND c.to_arc = 0)
				";
			}		
			
			$sQuery .= "
				WHERE ba.to_arc = 0
			";
			
			if ( !empty($user_restrict) ) {
				$sQuery .= "
					AND c.id_person = '{$nIDUser}'
				";
			}
				
			if ( $cash == 1 || $cash == 0 ) {
				$sQuery .= "\n\t AND ba.cash = ".$cash;
			}
				
			$sQuery .= "\n ORDER BY ba.name_account ";
			
			return $this->select( $sQuery );
		}

        public function getTypeAccoutById( $nID ) {
            global $db_name_finance;

            if ( !is_numeric($nID) || empty($nID) ) {
                return "person";
            }

            $sQuery = "
				SELECT
					IF ( cash = 1, 'cash', 'bank' ) AS type
				FROM {$db_name_finance}.bank_accounts
				WHERE id = {$nID}
			";

            return $this->selectOne( $sQuery );
        }

    }
?>
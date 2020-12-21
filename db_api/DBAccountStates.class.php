<?php

	class DBAccountStates extends DBBase2
	{
		public function __construct()
		{
			global $db_finance;
			parent::__construct( $db_finance, 'account_states' );
		}
		
		public function getRow( $sAccoutType = "", $nIDPerson = 0, $nIDBankAccount = 0 )
		{
			global $db_finance;
			
			$sQuery = "
				SELECT
					*
				FROM account_states
				WHERE 1
			";
			
//			if ( !empty($sAccoutType) ) {
//				$sQuery .= " AND account_type = {$db_finance->Quote( $sAccoutType )} ";
//			}
			
			if( !empty( $nIDPerson ) )
			{
				$sQuery .= " AND id_person = {$nIDPerson} \n";
			}
			
			if( !empty( $nIDBankAccount ) )
			{
				$sQuery .= " AND id_bank_account = {$nIDBankAccount} \n";
			}
			
			$sQuery .= " LIMIT 1";
			
			return $this->selectOnce($sQuery);			
		}
		
		public function getAllPermited( DBResponse $oResponse ) {
			global $db_name_finance;
			
			$nIDPerson 		= isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			
			if ( empty($nIDPerson) ) {
				return array();
			}
						
			$oDBCashiers 	= new DBCashiers();
			$sAccounts 		= $oDBCashiers->getBankAccountsWatch($nIDPerson);
			
			if(!empty($sAccounts))
			{
				$sQuery = "
					SELECT
						ban_acc.id as id,
						ban_acc.name_account AS name,
						acc_sta.current_sum AS account_sum
					FROM {$db_name_finance}.account_states acc_sta
					LEFT JOIN {$db_name_finance}.bank_accounts ban_acc ON ban_acc.id = acc_sta.id_bank_account
					WHERE
						ban_acc.to_arc = 0
						AND acc_sta.id_bank_account IN ( {$sAccounts} )
				";
				
				//Sorting
				$oParams = Params::getInstance();
				
				$sSortField = $oParams->get( "sfield", "name" );
				$nSortType	= $oParams->get( "stype", DBAPI_SORT_ASC );
				
				if( empty( $sSortField ) )$sSortField = "name";
				if( empty( $nSortType ) )$nSortType = DBAPI_SORT_ASC;
				$sSortType = ( $nSortType == DBAPI_SORT_ASC ) ? "ASC" : "DESC";
				
				$sQuery .= "
					ORDER BY
						{$sSortField} {$sSortType}
				";
				
			
			$oResponse->setSort( $sSortField, $nSortType );
			//End Sorting
			
			return $this->select( $sQuery );
			}
		}
		
	}


?>
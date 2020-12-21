<?php

	class ApiSetSetupCashier
	{
		public function get( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			$oDBFirms = new DBFirms();
			$oDBOffices = new DBOffices();
			$oDBPersonnel = new DBPersonnel();
			
			$oDBNomenclaturesExpenses = new DBNomenclaturesExpenses();
			$oDBBankAccounts = new DBBankAccounts();
			
			$oCashiers = new DBCashiers();
			
			//Load Firms
			$aFirms = $oDBFirms->getFirms4();
			
			$oResponse->setFormElement( 'form1', 'nIDFirm', array(), '' );
			$oResponse->setFormElementChild( 'form1', 'nIDFirm', array_merge( array( "value" => '0' ) ), "--Изберете--" );
			
			foreach( $aFirms as $key => $value )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDFirm', array_merge( array( "value" => $key ) ), $value );
			}
			//End Load Firms
			
			//Load Nomenclatures and Bank Accounts
			$aNomenclaturesExpenses = $oDBNomenclaturesExpenses->getAllRecords();
			$aBankAccounts = $oDBBankAccounts->getAllRecords();
			//End Load Nomenclatures and Bank Accounts
			
			if( !empty( $nID ) )
			{
				$aCashier = $oCashiers->getRecord( $nID );
				
				if( !empty( $aCashier ) )
				{
					//Get Firm, Office and Person
					$nIDPerson = $aCashier['id_person'];
					
					$aPersonOffice = $oDBPersonnel->getPersonnelOffice( $nIDPerson );
					$nIDOffice = isset( $aPersonOffice['id_office'] ) ? $aPersonOffice['id_office'] : 0;
					
					if( !empty( $nIDOffice ) )
					{
						$aOfficeFirm = $oDBOffices->getFirmByIDOffice( $nIDOffice );
						
						$nIDFirm = isset( $aOfficeFirm['id_firm'] ) ? $aOfficeFirm['id_firm'] : 0;
					}
					else $nIDFirm = 0;
					//End Get Firm, Office and Person
					
					//Set Firm, Office and Person
					$oResponse->setFormElementAttribute( "form1", "nIDFirm", "value", $nIDFirm );
					
					$oResponse->setFormElement( 'form1', 'nIDOffice', array(), '' );
					$oResponse->setFormElement( 'form1', 'nIDPerson', array(), '' );
					
					if( !empty( $nIDFirm ) )
					{
						$aOffices = $oDBOffices->getOfficesByIDFirm( $nIDFirm );
						
						$oResponse->setFormElementChild( 'form1', 'nIDOffice', array_merge( array( "value" => '0' ) ), "--Изберете--" );
						foreach( $aOffices as $key => $value )
						{
							$oResponse->setFormElementChild( 'form1', 'nIDOffice', array_merge( array( "value" => $key ) ), $value );
						}
						
						$oResponse->setFormElementAttribute( "form1", "nIDOffice", "value", $nIDOffice );
					}
					else
					{
						$oResponse->setFormElementChild( 'form1', 'nIDOffice', array_merge( array( "value" => '0' ) ), "Първо изберете фирма" );
					}
					
					if( !empty( $nIDOffice ) )
					{
						$aPersons = $oDBPersonnel->getPersonnelsByIDOffice3( $nIDOffice );
						
						$oResponse->setFormElementChild( 'form1', 'nIDPerson', array_merge( array( "value" => '0' ) ), "--Изберете--" );
						foreach( $aPersons as $key => $value )
						{
							$oResponse->setFormElementChild( 'form1', 'nIDPerson', array_merge( array( "value" => $key ) ), $value );
						}
						
						$oResponse->setFormElementAttribute( "form1", "nIDPerson", "value", $nIDPerson );
					}
					else
					{
						$oResponse->setFormElementChild( 'form1', 'nIDPerson', array_merge( array( "value" => '0' ) ), "Първо изберете регион" );
					}
					//End Set Firm, Office and Person
					
					//Get Nomenclatures and Bank Accounts
					$aIDsNomenclaturesExpenses = explode( ",", $aCashier['nomenclatures_expenses_create'] );
					$aIDsBankAccountsOpperate = explode( ",", $aCashier['bank_accounts_operate'] );
					$aIDsBankAccountsWatch = explode( ",", $aCashier['bank_accounts_watch'] );
					
					$oResponse->setFormElement( "form1", "nomenclatures_all", array(), "" );
					$oResponse->setFormElement( "form1", "account_opperate_all", array(), "" );
					$oResponse->setFormElement( "form1", "account_watch_all", array(), "" );
					$oResponse->setFormElement( "form1", "nomenclatures_current", array(), "" );
					$oResponse->setFormElement( "form1", "account_opperate_current", array(), "" );
					$oResponse->setFormElement( "form1", "account_watch_current", array(), "" );
					
					foreach( $aNomenclaturesExpenses as $aNomenclatureExpense )
					{
						if( in_array( $aNomenclatureExpense['id'], $aIDsNomenclaturesExpenses ) )
						{
							$oResponse->setFormElementChild( "form1", "nomenclatures_current", array( "value" => $aNomenclatureExpense['id'] ), $aNomenclatureExpense['name'] );
						}
						else
						{
							$oResponse->setFormElementChild( "form1", "nomenclatures_all", array( "value" => $aNomenclatureExpense['id'] ), $aNomenclatureExpense['name'] );
						}
					}
					foreach( $aBankAccounts as $aBankAccount )
					{
						if( in_array( $aBankAccount['id'], $aIDsBankAccountsOpperate ) )
						{
							$oResponse->setFormElementChild( "form1", "account_opperate_current", array( "value" => $aBankAccount['id'] ), $aBankAccount['name'] );
						}
						else
						{
							$oResponse->setFormElementChild( "form1", "account_opperate_all", array( "value" => $aBankAccount['id'] ), $aBankAccount['name'] );
						}
						if( in_array( $aBankAccount['id'], $aIDsBankAccountsWatch ) )
						{
							$oResponse->setFormElementChild( "form1", "account_watch_current", array( "value" => $aBankAccount['id'] ), $aBankAccount['name'] );
						}
						else
						{
							$oResponse->setFormElementChild( "form1", "account_watch_all", array( "value" => $aBankAccount['id'] ), $aBankAccount['name'] );
						}
					}
					
					//End Get Nomenclatures and Bank Accounts
				}
			}
			else
			{
				//Initialize Offices and Persons
				$oResponse->setFormElement( 'form1', 'nIDOffice', array(), '' );
				$oResponse->setFormElementChild( 'form1', 'nIDOffice', array_merge( array( "value" => '0' ) ), "Първо изберете фирма" );
				
				$oResponse->setFormElement( 'form1', 'nIDPerson', array(), '' );
				$oResponse->setFormElementChild( 'form1', 'nIDPerson', array_merge( array( "value" => '0' ) ), "Първо изберете регион" );
				//End Initialize Offices and Persons
				
				//Initialize Nomenclatures and Bank Accounts
				$oResponse->setFormElement( "form1", "nomenclatures_all", array(), "" );
				$oResponse->setFormElement( "form1", "account_opperate_all", array(), "" );
				$oResponse->setFormElement( "form1", "account_watch_all", array(), "" );
				
				foreach( $aNomenclaturesExpenses as $aNomenclatureExpense )
				{
					$oResponse->setFormElementChild( "form1", "nomenclatures_all", array( "value" => $aNomenclatureExpense['id'] ), $aNomenclatureExpense['name'] );
				}
				foreach( $aBankAccounts as $aBankAccount )
				{
					$oResponse->setFormElementChild( "form1", "account_opperate_all", array( "value" => $aBankAccount['id'] ), $aBankAccount['name'] );
					$oResponse->setFormElementChild( "form1", "account_watch_all", array( "value" => $aBankAccount['id'] ), $aBankAccount['name'] );
				}
				//End Initialize Nomenclatures and Bank Accounts
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$nID 		= Params::get( 'nID', 0 );
			$nIDPerson 	= Params::get( "nIDPerson" );
			
			$oCashiers = new DBCashiers();
			
			$aNomenclaturesExpenses = Params::get( "nomenclatures_current", array() );
			$aAccountsOpperate = Params::get( "account_opperate_current", array() );
			$aAccountsWatch = Params::get( "account_watch_current", array() );
			
			if( empty( $nIDPerson ) )
			{
				throw new Exception( "Въведете служител - касиер!", DBAPI_ERR_INVALID_PARAM );
			}
			
			if( empty( $nID ) )
			{
				if( !$oCashiers->isCashierUnique( $nIDPerson ) )
				{
					throw new Exception( "Посоченият служител вече е въведен!", DBAPI_ERR_INVALID_PARAM );
				}
			}
			
			$aData = array();
			$aData['id'] = Params::get( 'nID', 0 );
			$aData['id_person'] = $nIDPerson;
			$aData['nomenclatures_expenses_create'] = implode( ",", $aNomenclaturesExpenses );
			$aData['bank_accounts_operate'] = implode( ",", $aAccountsOpperate );
			$aData['bank_accounts_watch'] = implode( ",", $aAccountsWatch );
			
			$oCashiers->update( $aData );
		}
		
		public function loadOffices( DBResponse $oResponse )
		{
			$nFirm = Params::get( 'nIDFirm', 0 );
			
			$oResponse->setFormElement( 'form1', 'nIDOffice', array(), '' );
			
			if( !empty( $nFirm ) )
			{
				$oDBOffices = new DBOffices();
				$aOffices = $oDBOffices->getOfficesByIDFirm( $nFirm );
				
				$oResponse->setFormElementChild( 'form1', 'nIDOffice', array_merge( array( "value" => '0' ) ), "--Изберете--" );
				foreach( $aOffices as $key => $value )
				{
					$oResponse->setFormElementChild( 'form1', 'nIDOffice', array_merge( array( "value" => $key ) ), $value );
				}
			}
			else
			{
				$oResponse->setFormElementChild( 'form1', 'nIDOffice', array_merge( array( "value" => '0' ) ), "Първо изберете фирма" );
			}
			
			$oResponse->setFormElement( 'form1', 'nIDPerson', array(), '' );
			$oResponse->setFormElementChild( 'form1', 'nIDPerson', array_merge( array( "value" => '0' ) ), "Първо изберете регион" );
			
			$oResponse->printResponse();
		}
		
		public function loadPersons( DBResponse $oResponse )
		{
			$nOffice = Params::get( 'nIDOffice' );
			
			$oResponse->setFormElement( 'form1', 'nIDPerson', array(), '' );
			
			if( !empty( $nOffice ) )
			{
				$oDBPersonnel = new DBPersonnel();
				$aResponsible = $oDBPersonnel->getPersonnelsByIDOffice3( $nOffice );
				
				$oResponse->setFormElementChild( 'form1', 'nIDPerson', array_merge( array( "value" => '0' ) ), "--Изберете--" );
				foreach( $aResponsible as $key => $value )
				{
					$oResponse->setFormElementChild( 'form1', 'nIDPerson', array_merge( array( "value" => $key ) ), $value );
				}
			}
			else
			{
				$oResponse->setFormElementChild( 'form1', 'nIDPerson', array_merge( array( "value" => '0' ) ), "Първо изберете регион" );
			}
			
			$oResponse->printResponse();
		}
	}
	
?>
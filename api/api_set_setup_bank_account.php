<?php

	class ApiSetSetupBankAccount
	{
		public function get( DBResponse $oResponse ) {
			//Params
			$nID = Params::get( "nID", 0 );
			//End Params
			
			//Object Instances
			$oBankAccounts 	= new DBBankAccounts();
			$oFirms			= new DBFirms();
			//End Object Instances
			
			//Variable Initializations
			$aBankAccount 	= array();
			$aFirms 		= array();
			//End Variable Initializations
			
			if( !empty( $nID ) ) {
				$aBankAccount = $oBankAccounts->getRecord( $nID );
				
				$oResponse->setFormElement( 'form1', 'sNameAccount', 	array( 'value' => $aBankAccount['name_account'] ) 	);
				$oResponse->setFormElement( 'form1', 'sNameBank', 		array( 'value' => $aBankAccount['name_bank'] ) 		);
				$oResponse->setFormElement( 'form1', 'sIBAN', 			array( 'value' => $aBankAccount['iban'] ) 			);
				$oResponse->setFormElement( 'form1', 'sBIC', 			array( 'value' => $aBankAccount['bic'] ) 			);
				
				//Set Firms
				$aIDs = explode( ",", $aBankAccount['ids_typical_firms'] );
				$aFirms = $oFirms->getFirms4();
				
				$oResponse->setFormElement( "form1", "firms_all", array(), "" );
				$oResponse->setFormElement( "form1", "firms_current", array(), "" );
				
				foreach( $aFirms as $nID => $sName )
				{
					if( in_array( $nID, $aIDs ) )
					{
						$oResponse->setFormElementChild( "form1", "firms_current", array( "value" => $nID ), $sName );
					}
					else
					{
						$oResponse->setFormElementChild( "form1", "firms_all", array( "value" => $nID ), $sName );
					}
				}
				//End Set Firms
				
				if ( isset($aBankAccount['cash']) && ($aBankAccount['cash'] == 1) ) {
					$oResponse->setFormElement( "form1", "cash", array("value" => 1), "" );
				} else {
					$oResponse->setFormElement( "form1", "cash", array("value" => 0), "" );
				}
			}
			else
			{
				//Set Firms
				$aFirms = $oFirms->getFirms4();
				
				$oResponse->setFormElement( "form1", "firms_all", array(), "" );
				
				foreach( $aFirms as $nID => $sName )
				{
					$oResponse->setFormElementChild( "form1", "firms_all", array( "value" => $nID ), $sName );
				}
				//End Set Firms
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse ) {
			$oBankAccounts = new DBBankAccounts();
			
			$nID			= Params::get( "nID", 0 );
			$sNameAccount 	= Params::get( "sNameAccount" );
			$sNameBank 		= Params::get( "sNameBank" );
			$sIBAN 			= Params::get( "sIBAN" );
			$sBIC 			= Params::get( "sBIC" );
			$aFirms 		= Params::get( "firms_current", array() );
			$sBank	 		= Params::get( "bank", "" );
			APILog::Log(0, "Bla: ".$sBank);
			//Validate
			if ( empty($sNameAccount) ) {
				throw new Exception( "Въведете наименование на сметката!", DBAPI_ERR_INVALID_PARAM );
			}
			
			if ( $sBank == 1 ) {
				if ( empty($sBIC) ) {
					throw new Exception( "Въведете банков идентификационен код!", DBAPI_ERR_INVALID_PARAM );
				}
				
				if ( empty($sIBAN) ) {
					throw new Exception( "Полето IBAN не може да е празно!", DBAPI_ERR_INVALID_PARAM );
				}
				
				$oValidator = new Validate();
				
				$oValidator->variable = $sIBAN;
				$oValidator->checkIBAN();
				if ( !$oValidator->result ) {
					throw new Exception( sprintf( "Грешен IBAN!\n%s.", $oValidator->errResult ), DBAPI_ERR_INVALID_PARAM );
				}
				//End Validate
			}
			
			$aData = array();
			$aData['id'] 				= $nID;
			$aData['name_account'] 		= $sNameAccount;
			$aData['name_bank'] 		= $sBank ? $sNameBank 	: "";
			$aData['cash'] 				= $sBank ? 0 			: 1;
			$aData['iban'] 				= $sBank ? $sIBAN 		: "";
			$aData['bic'] 				= $sBank ? $sBIC 		: "";
			$aData['ids_typical_firms'] = $sBank ? implode( ",", $aFirms ) : "";
			
			$oBankAccounts->update( $aData );
		}
	}
	
?>
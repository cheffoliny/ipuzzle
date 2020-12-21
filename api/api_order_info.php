<?php
	class ApiOrderInfo {
		public function load( DBResponse $oResponse ) {
			$nID 		= Params::get( "nID", 0 );
			$nIDDoc 	= Params::get( "nIDDoc", 0 );
			$sDocType 	= Params::get( "sDocType", "" );
			$aParams 	= Params::getAll();
			$aDocument 	= array();
			$nTotal		= 0;
			
			$oOrders 		= new DBOrders();
			$oOrderRows		= new DBOrdersRows();
			$oSalesDocs 	= new DBSalesDocs();
			$oBuyDocs 		= new DBBuyDocs();
			$oPersonnel 	= new DBPersonnel();
			$oBankAccounts 	= new DBBankAccounts();
			
			if ( !empty($nID) ) {
				//Fill Bank Accounts
				$aBankAccounts = $oBankAccounts->getAllRecords();
				$oResponse->setFormElement( "form1", "nIDBankAccount", array(), "" );
				
				foreach( $aBankAccounts as $aBankAccount ) {
					$name = $aBankAccount['name'];
					
					if ( $aBankAccount['iban'] ) {
						$name .= " [".$aBankAccount['iban']."]";
					}
					
					$oResponse->setFormElementChild( "form1", "nIDBankAccount", array( "value" => $aBankAccount['id'] ), $name );
				}
				//End Fill Bank Accounts
				
				$aOrder = array();
				$nResult = $oOrders->getRecord( $nID, $aOrder );
				
				if ( $nResult != DBAPI_ERR_SUCCESS ) {
					throw new Exception( "Грешка при изпълнение на операцията!", $nResult );
				}
				
				if ( !empty($aOrder) ) {
					$nTotal = $oOrderRows->getRealSumByIDOrder($nID);
					$nTotal	= sprintf("%01.2f", $nTotal);
					
					$oResponse->setFormElement( "form1", "nTotalSum", 		array( "value" => $nTotal ), 					$nTotal );
					$oResponse->setFormElement( "form1", "sNote", 			array( "value" => $aOrder['note'] ), 			$aOrder['note'] );
					$oResponse->setFormElement( "form1", "sAccountType", 	array( "value" => $aOrder['account_type'] ), 	$aOrder['account_type'] );
					$oResponse->setFormElement( "form1", "nPaidSum", 		array( "value" => "" ), 						"" );
					$oResponse->setFormElement( "form1", "nRestSum", 		array( "value" => "" ), 						"" );					
					
					// Избор на сметката
					$oResponse->setFormElementAttribute( "form1", "nIDBankAccount", "value", $aOrder['bank_account_id'] );
					
					switch( $aOrder['doc_type'] ) {
						case 'sale':
							$nResult = $oSalesDocs->getRecord( $aOrder['doc_id'], $aDocument );
						break;
						
						case 'buy':
							$nResult = $oBuyDocs->getRecord( $aOrder['doc_id'], $aDocument );
						break;
					}
					
					if ( $nResult != DBAPI_ERR_SUCCESS ) {
						throw new Exception( "Грешка при изпълнение на операцията!", $nResult );
					}
					
					if ( !empty($aDocument) ) {
						$oResponse->setFormElement( "form1", "nDocNum", array( "value" => $aDocument['doc_num'] ), $aDocument['doc_num'] );
					}
		
					if ( isset( $aParams['bAllowView'] ) && $aParams['bAllowView'] ) {
						$oOrders->getReportFirmBalances( $oResponse, $nID );
					}
				}
			}
	
			$oResponse->printResponse();
		}
		
			public function result( DBResponse $oResponse ) {
			$nID 		= Params::get( 'nID', 0 );
			$nIDDoc 	= Params::get( "nIDDoc", 0 );
			$sDocType 	= Params::get( "sDocType", "" );
			$aParams 	= Params::getAll();
			$aOrder 	= array();
			
			$oOrders 	= new DBOrders();
			
			if ( !empty($nID) ) {
				$nResult = $oOrders->getRecord( $nID, $aOrder );
				
				if(  $nResult != DBAPI_ERR_SUCCESS ) {
					throw new Exception( "Грешка при изпълнение на операцията!", $nResult );
				}
				
				if ( !empty($aOrder) ) {
					if ( isset($aParams['bAllowView']) && $aParams['bAllowView'] ) {
						$oOrders->getReportFirmBalances( $oResponse, $nID );
					}
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function isValidID( $nID ) {
			return preg_match("/^\d{13}$/", $nID);
		}		
		
		public function annulment( DBResponse $oResponse ) {
			global $db_name_system, $db_name_finance, $db_system, $db_finance;
			
			$nID 		= Params::get("nID", 0);
			$oOrders 	= new DBOrders();
			
			if ( $this->isValidID($nID) ) {
				$oOrders->annulment($oResponse, $nID);
			}			

			$oResponse->printResponse();
		}		
	}
?>
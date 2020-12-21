<?php

	class ApiCurrencyMovement
	{
		public function load( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			//$oDBTransfers 		= new DBTransfers();
			$oDBBankAccounts 	= new DBBankAccounts();
			$nIDPerson 			= isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			
			//Set Bank Accounts
			$oResponse->setFormElement( "form1", "nIDBankAccount", array(), "" );
			
			if( !empty( $_SESSION['userdata']['id_person'] ) )
			{
				$aBankAccounts = $oDBBankAccounts->getBankAccountsForPerson( $_SESSION['userdata']['id_person'] );
				
				foreach( $aBankAccounts as $aBankAccount )
				{
					$oResponse->setFormElementChild( "form1", "nIDBankAccount", array( "value" => $aBankAccount['id'] ), $aBankAccount['name'] );
				}
				
				if( isset( $aParams['nIDBankAccount'] ) && !empty( $aParams['nIDBankAccount'] ) )
				{
					$oResponse->setFormElementAttribute( "form1", "nIDBankAccount", "value", $aParams['nIDBankAccount'] );
				}
				if( isset( $_SESSION['last_bank_account'] ) && !empty( $_SESSION['last_bank_account'] ) )
				{
					$oResponse->setFormElementAttribute( "form1", "nIDBankAccount", "value", $_SESSION['last_bank_account'] );
				}
			}
			//End Set Bank Accounts
			
			//Default Date and Time
			$oResponse->setFormElement( 'form1', 'sFromDate', array( 'value' => date( 'd.m.Y' ) ) );
			$oResponse->setFormElement( 'form1', 'sToDate', array( 'value' => date( 'd.m.Y' ) ) );
			//End Default Date and Time
			
			//Setup Transfer Buttons
			$sInitDate 		= date( 'd.m.Y' );
			
			//$nTransfersTo 	= ( $oDBTransfers->isPersonOperatingTransfers( $nIDPerson, $sInitDate, $sInitDate, "r" ) ) ? 1 : 0;
			//$nTransfersFrom = ( $oDBTransfers->isPersonOperatingTransfers( $nIDPerson, $sInitDate, $sInitDate, "s" ) ) ? 1 : 0;
			
			//$oResponse->setFormElement( "form1", "nTransfersTo", 	array( "value" => $nTransfersTo ), 		$nTransfersTo 	);
			//$oResponse->setFormElement( "form1", "nTransfersFrom", 	array( "value" => $nTransfersFrom ), 	$nTransfersFrom );
			//End Setup Transfer Buttons
			
			$oResponse->printResponse( "Отчети", "currency_movement" );
		}
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oOrders 		= new DBOrders();
			//$oDBTransfers 	= new DBTransfers();
			
			//Filter Validation
			if( !isset( $aParams['nIDBankAccount'] ) || empty( $aParams['nIDBankAccount'] ) )
			{
				throw new Exception( "Моля, посочете сметка!", DBAPI_ERR_INVALID_PARAM );
			}
			//End Filter Validation
			
			$_SESSION['last_bank_account'] = $aParams['nIDBankAccount'];
			
			$nIDPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			
			//$nTransfersTo 	= ( $oDBTransfers->isPersonOperatingTransfers( $nIDPerson, $aParams['sFromDate'], $aParams['sToDate'], "r" ) ) ? 1 : 0;
			//$nTransfersFrom = ( $oDBTransfers->isPersonOperatingTransfers( $nIDPerson, $aParams['sFromDate'], $aParams['sToDate'], "s" ) ) ? 1 : 0;
			
			//$oResponse->setFormElement( "form1", "nTransfersTo", 	array( "value" => $nTransfersTo ), 		$nTransfersTo 	);
			//$oResponse->setFormElement( "form1", "nTransfersFrom", 	array( "value" => $nTransfersFrom ), 	$nTransfersFrom );
			
			$oOrders->getReportCurrencyMovement( $oResponse, $aParams );
			
			$oResponse->printResponse( "Отчети", "currency_movement" );
		}
		
//		public function loadTransfers( DBResponse $oResponse )
//		{
//			$aParams = Params::getAll();
//
//			$oDBTransfers = new DBTransfers();
//
//			$nIDPerson 		= isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
//
//			$nTransfersTo 	= ( $oDBTransfers->isPersonOperatingTransfers( $nIDPerson, $aParams['sFromDate'], $aParams['sToDate'], "r" ) ) ? 1 : 0;
//			$nTransfersFrom = ( $oDBTransfers->isPersonOperatingTransfers( $nIDPerson, $aParams['sFromDate'], $aParams['sToDate'], "s" ) ) ? 1 : 0;
//
//			$oResponse->setFormElement( "form1", "nTransfersTo", 	array( "value" => $nTransfersTo ), 		$nTransfersTo 	);
//			$oResponse->setFormElement( "form1", "nTransfersFrom", 	array( "value" => $nTransfersFrom ), 	$nTransfersFrom );
//
//			$oResponse->printResponse();
//		}
		
		public function checkDoc( DBResponse $oResponse ) {
			$nNum 			= Params::get("nDocNum", 0);
			$oDBSalesDocs 	= new DBSalesDocs();
			$nIDSaleDoc		= 0;
			
			$nIDSaleDoc 	= $oDBSalesDocs->searchSaleDocsByNum(floatval($nNum));
			APILog::Log(0, $nIDSaleDoc);

			if ( !empty($nIDSaleDoc) ) {
				$oResponse->setFormElement("form1", "nDocExists", array("value" => $nIDSaleDoc), $nIDSaleDoc);
			} else {
				$oResponse->setFormElement("form1", "nDocExists", array("value" => 0), 0);
			}
			
			$oResponse->printResponse();
		}
	}
?>
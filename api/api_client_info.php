<?php

	class ApiClientInfo
	{
		
		public function load( DBResponse $oResponse )
		{
			$nID = Params::get( 'nID', 0 );
			
			$oClients = new DBClients();
			
			if( !empty( $nID ) )
			{
				$aClient = $oClients->getRecord( $nID );
				
				if( !empty( $aClient ) )
				{
					$oResponse->setFormElement( "form1", "nID", array( "value" => $aClient['id'] ), $aClient['id'] );

					$oResponse->setFormElement( "form1", "sName", array( "value" => $aClient['name'] ), $aClient['name'] );
					$oResponse->setFormElement( "form1", "sAddress", array( "value" => $aClient['address'] ), $aClient['address'] );
					$oResponse->setFormElement( "form1", "sEmail", array( "value" => $aClient['email'] ), $aClient['email'] );
					$oResponse->setFormElement( "form1", "sPhone", array( "value" => $aClient['phone'] ), $aClient['phone'] );
					
					$oResponse->setFormElement( "form1", "sInvoiceAddress", array( "value" => $aClient['invoice_address'] ), $aClient['invoice_address'] );
					$oResponse->setFormElement( "form1", "sInvoiceEIN", array( "value" => $aClient['invoice_ein'] ), $aClient['invoice_ein'] );
					$oResponse->setFormElement( "form1", "sInvoiceEINDDS", array( "value" => $aClient['invoice_ein_dds'] ), $aClient['invoice_ein_dds'] );
					$oResponse->setFormElement( "form1", "sInvoiceMOL", array( "value" => $aClient['invoice_mol'] ), $aClient['invoice_mol'] );
					$oResponse->setFormElement( "form1", "sInvoiceRecipient", array( "value" => $aClient['invoice_recipient'] ), $aClient['invoice_recipient'] );
					if( $aClient['invoice_bring_to_object'] )
					{
						$oResponse->setFormElement( "form1", "nInvoiceBringToObject", array( "checked" => "checked" ) );
					}
					else
					{
						$oResponse->setFormElement( "form1", "nInvoiceBringToObject", array( "checked" => "" ) );
					}
					$oResponse->setFormElement( "form1", "sInvoiceLayout", array( "value" => $aClient['invoice_layout'] ), $aClient['invoice_layout'] );
					$oResponse->setFormElement( "form1", "sInvoicePayment", array( "value" => $aClient['invoice_payment'] ), $aClient['invoice_payment'] );
					if( !empty( $aClient['invoice_email'] ) )
					{
						$oResponse->setFormElement( "form1", "nSendByEmail", array( "checked" => "checked" ) );
						$oResponse->setFormElement( "form1", "sInvoiceEmail", array( "value" => $aClient['invoice_email'] ), $aClient['invoice_email'] );
					}
					else
					{
						$oResponse->setFormElement( "form1", "nSendByEmail", array( "checked" => "" ) );
					}
					
					$oResponse->setFormElement( "form1", "sNote", array( "value" => $aClient['note'] ) );
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$oClients = new DBClients();
			$oValidator = new Validate();
			
			$nID 		= Params::get( 'nID', 0 );
			
			$sName 		= Params::get( 'sName', "" );
			$sAddress 	= Params::get( 'sAddress', "" );
			$sEmail 	= Params::get( 'sEmail', "" );
			$sPhone 	= Params::get( 'sPhone', "" );
			$sNote		= Params::get( "sNote", "" );
			
			$sInvoiceAddress 		= Params::get( 'sInvoiceAddress', "" );
			$sInvoiceEIN 			= Params::get( 'sInvoiceEIN', "" );
			$sInvoiceEINDDS 		= Params::get( 'sInvoiceEINDDS', "" );
			$sInvoiceMOL 			= Params::get( 'sInvoiceMOL', "" );
			$sInvoiceRecipient 		= Params::get( 'sInvoiceRecipient', "" );
			$nInvoiceBringToObject 	= Params::get( 'nInvoiceBringToObject', 0 );
			$sInvoiceLayout 		= Params::get( 'sInvoiceLayout', "" );
			$sInvoicePayment 		= Params::get( 'sInvoicePayment', "" );
			$nSendByEmail			= Params::get( 'nSendByEmail', 0 );
			if( $nSendByEmail )
			{
				$sInvoiceEmail		= Params::get( 'sInvoiceEmail', "" );
			}
			else
			{
				$sInvoiceEmail		= "";
			}
			
			//Validate
			if( !empty( $sInvoiceEIN ) && empty( $nID ) )
			{
				$bIsEINUnique = ($sInvoiceEIN == "999999999999999") || ($oClients->isEINUnique( $sInvoiceEIN ));
				
				if( !$bIsEINUnique )
				{
					throw new Exception( "Вече съществува клиент с въведения ЕИН!", DBAPI_ERR_INVALID_PARAM );
				}
			}
			
			if( empty( $sName ) )
			{
				throw new Exception( "Въведете име на клиент!", DBAPI_ERR_INVALID_PARAM );
			}
			
			if( !empty( $sEmail ) )
			{
				$oValidator->variable = $sEmail;
				$oValidator->checkEMAIL();
				if( !$oValidator->result )
				{
					throw new Exception( sprintf( "Невалиден E-Mail адрес!\n%s.", $oValidator->errResult ), DBAPI_ERR_INVALID_PARAM );
				}
			}
			
			$oValidator->variable = $sInvoiceEIN;
			$oValidator->trimAndCheck_EinEGN();
			if( !$oValidator->result ) {
				throw new Exception( sprintf( "Грешен ЕИН / ЕГН!\n%s.", $oValidator->errResult ), DBAPI_ERR_INVALID_PARAM );
			}
			
			$oValidator->variable = $sInvoiceEINDDS;
			$oValidator->checkEIN();
			if( !$oValidator->result )
			{
				throw new Exception( sprintf( "Грешен ЕИН по ДДС!\n%s.", $oValidator->errResult ), DBAPI_ERR_INVALID_PARAM );
			}
			
			if( !empty( $sInvoiceEmail ) )
			{
				$oValidator->variable = $sInvoiceEmail;
				$oValidator->checkEMAIL();
				if( !$oValidator->result )
				{
					throw new Exception( sprintf( "Невалиден E-Mail адрес за фактура!\n%s.", $oValidator->errResult ), DBAPI_ERR_INVALID_PARAM );
				}
			}
			//Еnd Validate
			
			$aClient = array();
			$aClient['id'] = $nID;
			
			$aClient['name'] = $sName;
			$aClient['address'] = $sAddress;
			$aClient['email'] = $sEmail;
			$aClient['phone'] = $sPhone;
			$aClient['note'] = $sNote;
			
			$aClient['invoice_address'] = $sInvoiceAddress;
			$aClient['invoice_ein'] = $sInvoiceEIN;
			$aClient['invoice_ein_dds'] = $sInvoiceEINDDS;
			$aClient['invoice_mol'] = $sInvoiceMOL;
			$aClient['invoice_recipient'] = $sInvoiceRecipient;
			$aClient['invoice_bring_to_object'] = $nInvoiceBringToObject;
			$aClient['invoice_layout'] = $sInvoiceLayout;
			$aClient['invoice_payment'] = $sInvoicePayment;
			$aClient['invoice_email'] = $sInvoiceEmail;
			
			$oClients->update( $aClient );
			
			$oResponse->setFormElement( "form1", "nID", array( "value" => $aClient['id'] ), $aClient['id'] );
			
			$oResponse->printResponse();
		}
	}

?>
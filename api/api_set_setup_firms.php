<?php

	$oFirm = new DBBase( $db_sod, 'firms' );

	switch( $aParams['api_action'])
	{
		case "save" : 
			SaveFirm( $aParams );
		break;
		
		case "getoffices" :
			getOffices( $aParams['nIDFirmDDS'] );
		break;
		
		default : 
			loadFirm( $aParams['id'] );
		break;
	}
	
	function getOffices( $nIDFirm )
	{
		global $oResponse;
		
		//Initialize DDS Firms
		$oDBOffices = new DBOffices();
		
		$oResponse->setFormElement( 'form1', 'nIDOfficeDDS' );
		$oResponse->setFormElementChild( 'form1', 'nIDOfficeDDS', array( "value" => 0 ), "-- Избери --" );
		
		if( !empty( $nIDFirm ) )
		{
			$aOffices = $oDBOffices->getOfficesByFirm( $nIDFirm );
			
			foreach( $aOffices as $aOffice )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDOfficeDDS', array( "value" => $aOffice['id'] ), $aOffice['name'] );
			}
		}
		//End Initialize DDS Firms
		
		return DBAPI_ERR_SUCCESS;
	}
	
	function SaveFirm( $aParams )
	{
		global $oFirm, $oResponse;
		
		$nCode = !empty( $aParams['code'] ) && is_numeric( $aParams['code'] ) ? $aParams['code'] : 0;
		
		if( empty( $nCode ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Некоректна стойност на полето код!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}

		//Проверка за повторение в кода
		$aDuplicate = array();
		$aWhere = array();
		$aWhere[] = " id != {$aParams['id']} ";
		$aWhere[] = " code = $nCode ";
		
		if( $nResult = $oFirm->getResult( $aDuplicate, NULL, $aWhere ) != DBAPI_ERR_SUCCESS )
		{
			$oResponse->setError( DBAPI_ERR_SQL_QUERY, "Проблем при комуникацията!", __FILE__, __LINE__ );
			return DBAPI_ERR_SQL_QUERY;
		}
		if( !empty( $aDuplicate ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Вече съществува запис с този код!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		
		if( empty( $aParams['name'] ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето наименование!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		if( empty( $aParams['mol'] ) )
		{
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето МОЛ!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}
		
		$aFirm = array();
		$aFirm['id'] 						= $aParams['id'];
		$aFirm['code'] 						= $aParams['code'];
		$aFirm['name'] 						= $aParams['name'];
		$aFirm['mol'] 						= $aParams['mol'];
		$aFirm['jur_name'] 					= $aParams['jur_name'];
		$aFirm['address'] 					= $aParams['address'];
		$aFirm['idn'] 						= $aParams['idn'];
		$aFirm['idn_dds'] 					= $aParams['idn_dds'];
		$aFirm['jur_mol'] 					= $aParams['jur_mol'];
		$aFirm['id_office_dds'] 			= $aParams['nIDOfficeDDS'];
		$aFirm['id_bank_account_default'] 	= $aParams['nIDBankAccountDefault'];
		
		if( $nResult = $oFirm->update( $aFirm ) != DBAPI_ERR_SUCCESS )
		{
			$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
			return $nResult;
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	function loadFirm( $nID )
	{
		global $oFirm, $oResponse;
		
		//Initialize DDS Firms
		$oDBFirms 	= new DBFirms();
		$oDBOffices = new DBOffices();
		
		$aFirms = $oDBFirms->getFirms2();
		
		$oResponse->setFormElement( 'form1', 'nIDFirmDDS' );
		$oResponse->setFormElementChild( 'form1', 'nIDFirmDDS', array( "value" => 0 ), "-- Избери --" );
		
		foreach( $aFirms as $aFirm )
		{
			$oResponse->setFormElementChild( 'form1', 'nIDFirmDDS', array( "value" => $aFirm['id'] ), $aFirm['name'] );
		}
		//End Initialize DDS Firms
		
		//Get Bank Accounts
		$oBankAccounts = new DBBankAccounts();
		
		$oResponse->setFormElement( "form1", "nIDBankAccountDefault", array(), "" );
		$oResponse->setFormElementChild( "form1", "nIDBankAccountDefault", array( "value" => 0 ), "-- Избери --" );
		$aBankAccounts = array();
		$aBankAccountsIDs = array();
		if( !empty( $nID ) )
		{
			$aBankAccounts = $oBankAccounts->getBankAccoutsForFirm( $nID );
			
			foreach( $aBankAccounts as $aBankAccount )
			{
				$oResponse->setFormElementChild( "form1", "nIDBankAccountDefault", array( "value" => $aBankAccount['id'] ), $aBankAccount['name'] );
				$aBankAccountsIDs[] = $aBankAccount['id'];
			}
		}
		//End Get Bank Accounts
		
		$id = (int) $nID;
		
		if ( $id > 0 )
		{
			// Редакция
			$aData = array();
			
			if( $nResult = $oFirm->getRecord( $id, $aData ) != DBAPI_ERR_SUCCESS )
			{
				$oResponse->setError( $nResult, "Проблем при комуникацията!", __FILE__, __LINE__ );
				return $nResult;
			}
			
			$oResponse->setFormElement('form1', 'code', array(), $aData['code']);
			$oResponse->setFormElement('form1', 'name', array(), $aData['name']);
			$oResponse->setFormElement('form1', 'mol', array(), $aData['mol']);

			$oResponse->setFormElement('form1', 'jur_name', array(), $aData['jur_name']);
			$oResponse->setFormElement('form1', 'address', array(), $aData['address']);
			$oResponse->setFormElement('form1', 'idn', array(), $aData['idn']);
			$oResponse->setFormElement('form1', 'idn_dds', array(), $aData['idn_dds']);
			$oResponse->setFormElement('form1', 'jur_mol', array(), $aData['jur_mol']);
			
			if( isset( $aData['id_office_dds'] ) && is_numeric( $aData['id_office_dds'] ) )
			{
				$aOfficeDDS = $oDBOffices->getRecord( $aData['id_office_dds'] );
				if( isset( $aOfficeDDS['id_firm'] ) )
				{
					if( !empty( $aOfficeDDS['id_firm'] ) )
					{
						$oResponse->setFormElementAttribute( 'form1', 'nIDFirmDDS', "value", $aOfficeDDS['id_firm'] );
					}
					getOffices( $aOfficeDDS['id_firm'] );
					if( !empty( $aData['id_office_dds'] ) )
					{
						$oResponse->setFormElementAttribute( 'form1', 'nIDOfficeDDS', "value", $aData['id_office_dds'] );
					}
				}
				else
				{
					$oResponse->setFormElement( 'form1', 'nIDOfficeDDS' );
					$oResponse->setFormElementChild( 'form1', 'nIDOfficeDDS', array( "value" => 0 ), "-- Избери --" );
				}
			}
		}
		else
		{
			$oResponse->setFormElement( 'form1', 'nIDOfficeDDS' );
			$oResponse->setFormElementChild( 'form1', 'nIDOfficeDDS', array( "value" => 0 ), "-- Избери --" );
		}
		
		if( in_array( $aData['id_bank_account_default'], $aBankAccountsIDs ) )
		{
			$oResponse->setFormElementAttribute( "form1", "nIDBankAccountDefault", "value", $aData['id_bank_account_default'] );
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	print( $oResponse->toXML() );

?>
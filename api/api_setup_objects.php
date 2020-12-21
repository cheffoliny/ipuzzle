<?php
	$oDBFirms 				= new DBFirms();
	$oDBOffices 			= new DBOffices();
	$oObjects 				= new DBBase( $db_sod, 'objects' );
	$oObjectServices 		= new DBObjectServices();
	$oFirms 				= new DBBase( $db_sod, 'firms' );
	$oDBFirms 				= new DBFirms();
	$oRegions 				= new DBBase( $db_sod, 'offices' );
	$oFilters 				= new DBFilters();
	$oFiltersVisibleFields 	= new DBFiltersVisibleFields();
	
	$right_edit = false;
	if (!empty($_SESSION['userdata']['access_right_levels']))
		if (in_array('objects_edit', $_SESSION['userdata']['access_right_levels']))
		{
			$right_edit = true;
		}
		
	switch($aParams['api_action'])
	{
		case 'delete' : 
				$nID = (int) $aParams['id'];
				if( $nReseul = $oObjects->delete( $nID ) != DBAPI_ERR_SUCCESS )
					$oResponse->setError( $nReseul, "Проблем при премахването на записа!", __FILE__, __LINE__ );
				
				$aParams['api_action'] = 'result';
			break;
		
		case 'deleteFilter':
				$nIDFilter = $aParams['schemes'];
				
				$oFiltersVisibleFields->delByIDFilter( $nIDFilter );
				$oFilters->delete( $nIDFilter );
			break;
		
		case 'generate' : 
				//Extract Filters
				$nIDPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
				
				$aFilters = array();
				$aFilters = $oFilters->getFiltersByReportClass( "DBObjects", $nIDPerson );
				
				$oResponse->setFormElement( 'form1', 'schemes' );
				$oResponse->setFormElementChild( 'form1', 'schemes', array( "value" => "0" ), "---Изберете---" );
				
				foreach( $aFilters as $key => $value )
				{
					if( $value['is_default'] == '1' )
					{
						$oResponse->setFormElementChild( 'form1', 'schemes', array( "value" => $key, "selected" => "selected" ), $value['name'] );
					}
					else
					{
						$oResponse->setFormElementChild( 'form1', 'schemes', array( "value" => $key ), $value['name'] );
					}
				}
				//End Extract Filters
				
				$nOpenWindow = 0;
				if( !empty( $aParams['nOpenWin'] ) )
					$nOpenWindow = (int) $aParams['nOpenWin'];
				
				//Extract Firms and Offices
				$aFirms = $aRegions = array();
				
				if( !empty( $aParams['nDefReg'] ) || $nOpenWindow == 1 )
				{
					$aParams['nDefReg'] = (int) $aParams['nDefReg'];
					$nIDOffice = $aParams['nDefReg'];
				}
				else $nIDOffice = $_SESSION['userdata']['id_office'];
				
				$oFirms->getResult( $aFirms, NULL, array(" to_arc=0 "), "name" );
				
				$aFirms = $oDBFirms->getFirms2();
				$oResponse->setFormElement( 'form1', 'id_firm' );
				
				//$sel = false;
				//$def = 0;
				$oResponse->setFormElementChild( 'form1', 'id_firm', array( 'value' => 0 ), 'Фирма администратор' );

				if ( empty($aParams['id_firm']) ) {
					$oOffices = new DBOffices();
					if( !empty( $aParams['nIDFirm'] ) )
					{
						$aParams['id_firm'] = $aParams['nIDFirm'];
					}
					else $aParams['id_firm'] = $oOffices->getFirmByIDOffice( $nIDOffice );
				}
				
				foreach ( $aFirms as $aFirm ) {

					$arr = array();
					if ( !empty($aParams['id_firm']) ) {
						if( $aFirm['id'] == $aParams['id_firm'] ) {
							$arr = array('selected' => 'selected');
						}
					}
					
					$oResponse->setFormElementChild( 'form1', 'id_firm', array_merge(array('value' => $aFirm['id']), $arr), sprintf ("%s",$aFirm['name'] ) );
				}
				
				$firm_match = empty($aParams['id_firm']) ? 0 : (int) $aParams['id_firm'];
				
				if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
				{
					$sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
					$sCondition = " id IN ({$sAccessable}) ";
				}
				else $sCondition = " 1 ";
				
				if( $firm_match )$oRegions->getResult( $aRegions, NULL, array( " to_arc = 0 ", " id_firm = $firm_match ", $sCondition ), "name" );
				else $oRegions->getResult( $aRegions, NULL, array( " to_arc = 0 ", $sCondition ), "name" );
				
				$oResponse->setFormElement( 'form1', 'id_reg' );
				
				$oResponse->setFormElementChild( 'form1', 'id_reg', array( 'value' => 0 ), 'Административен офис' );
				foreach ( $aRegions as $aRegion ) {
					$arr = array();
					if ( !empty($aParams['id_reg']) ) {
						if( $aRegion['id'] == $aParams['id_reg'] ) {
							$arr = array('selected'=>'selected');
						}
					} else {
						if( $aRegion['id'] == $nIDOffice ) {
							$arr = array('selected'=>'selected');
						}
					}
					
					$oResponse->setFormElementChild( 'form1', 'id_reg', array_merge(array('value' => $aRegion['id']), $arr), sprintf("%s [%s]", $aRegion['name'], $aRegion['code']) );
				}
				
				if( !isset( $aParams['id_firm'] ) && !isset( $aParams['id_reg'] ) )
				{
					$oOffices = new DBOffices();
					$oOffices->retrieveLoggedUserOffice( 'id_firm', 'id_reg', $oResponse );
				}
				
				$aFirms = array();
				$aFirms = $oDBFirms->getFirms2();
				
				$oResponse->setFormElement( "form1", "nIDReactionFirm", array(), "" );
				$oResponse->setFormElementChild( "form1", "nIDReactionFirm", array( "value" => 0 ), "Реагираща фирма" );
				
				$oResponse->setFormElement( "form1", "nIDTechFirm", array(), "" );
				$oResponse->setFormElementChild( "form1", "nIDTechFirm", array( "value" => 0 ), "Сервизна фирма" );
				
				foreach( $aFirms as $aFirm )
				{
					$oResponse->setFormElementChild( "form1", "nIDReactionFirm", array( "value" => $aFirm['id'] ), $aFirm['name'] );
					$oResponse->setFormElementChild( "form1", "nIDTechFirm", array( "value" => $aFirm['id'] ), $aFirm['name'] );
				}
				
				if( !empty( $aParams['nIDReactionFirm'] ) )
				{
					$oResponse->setFormElementAttribute( "form1", "nIDReactionFirm", "value", $aParams['nIDReactionFirm'] );
					
					$oResponse->setFormElement( "form1", "nIDReactionOffice", array(), "" );
					$oResponse->setFormElementChild( "form1", "nIDReactionOffice", array( "value" => 0 ), "Реагиращ офис" );
					
					$aOffices = array();
					$aOffices = $oDBOffices->getOfficesByFirm( $aParams['nIDReactionFirm'] );
					
					foreach( $aOffices as $aOffice )
					{
						$oResponse->setFormElementChild( "form1", "nIDReactionOffice", array( "value" => $aOffice['id'] ), $aOffice['name'] . "[{$aOffice['code']}]" );
					}
					
					if( !empty( $aParams['nIDReactionOffice'] ) )
					{
						$oResponse->setFormElementAttribute( "form1", "nIDReactionOffice", "value", $aParams['nIDReactionOffice'] );
					}
				}
				else
				{
					$oResponse->setFormElement( "form1", "nIDReactionOffice", array(), "" );
					$oResponse->setFormElementChild( "form1", "nIDReactionOffice", array( "value" => 0 ), "Реагиращ офис" );
				}
				
				if( !empty( $aParams['nIDTechFirm'] ) )
				{
					$oResponse->setFormElementAttribute( "form1", "nIDTechFirm", "value", $aParams['nIDTechFirm'] );
					
					$oResponse->setFormElement( "form1", "nIDTechOffice", array(), "" );
					$oResponse->setFormElementChild( "form1", "nIDTechOffice", array( "value" => 0 ), "Сервизен офис" );
					
					$aOffices = array();
					$aOffices = $oDBOffices->getOfficesByFirm( $aParams['nIDTechFirm'] );
					
					foreach( $aOffices as $aOffice )
					{
						$oResponse->setFormElementChild( "form1", "nIDTechOffice", array( "value" => $aOffice['id'] ), $aOffice['name'] . "[{$aOffice['code']}]" );
					}
					
					if( !empty( $aParams['nIDTechOffice'] ) )
					{
						$oResponse->setFormElementAttribute( "form1", "nIDTechOffice", "value", $aParams['nIDTechOffice'] );
					}
				}
				else
				{
					$oResponse->setFormElement( "form1", "nIDTechOffice", array(), "" );
					$oResponse->setFormElementChild( "form1", "nIDTechOffice", array( "value" => 0 ), "Сервизен офис" );
				}
				//End Extract Firms and Offices
				
				//Set Object Statuses Combo
				$oStatuses = new DBStatuses();
				$aStatuses = $oStatuses->getStatuses2();
				
				
				//get firm statuses
				$oFirmStatuses = new DBStatuses();
				$aFirmStatuses = $oFirmStatuses->getFirmStatuses( $aParams['id_firm'] ); 
				
				
				if ( !isset($aParams['aStatus']) ) $aParams['aStatus'] = 0;
				if ( !isset($aParams['nFunction']) ) $aParams['nFunction'] = 0;
				
				$oResponse->setFormElement( 'form1', 'aStatus' );
				$oResponse->setFormElementChild( 'form1', 'aStatus', array( 'value' => 0 ), '-- Всички състояния --' );

                $nIDActive = 0;
                $flag_have_statuses = 0;
                $flag=0;
                foreach( $aStatuses as $aStatus )
                {
                    if( 1==1 ) //$nOpenWindow
                    {
                        foreach ($aFirmStatuses as $aFrimStatus)
                        {
                            if($aStatus['id'] == $aFrimStatus['id_status'])
                            {
                                $flag=1;
                                $flag_have_statuses = 1;
                                $oResponse->setFormElementChild( 'form1', 'aStatus', array( 'value' => $aStatus['id'] ,"selected" => "selected" ),$aStatus['name'] );
                            }
                        }
                        if( $aStatus['name'] == 'активен' )
                            $nIDActive = $aStatus['id'];
                        if(!$flag)
                            $oResponse->setFormElementChild( 'form1', 'aStatus', array( 'value' => $aStatus['id'] ), $aStatus['name'] );
                        $flag=0;
                    }
					else 
					{
                        if ( $aStatus['name'] == 'активен' ) {
                            $nIDActive = $aStatus['id'];
                        }

                        $oResponse->setFormElementChild( 'form1', 'aStatus', array( 'value' => $aStatus['id'] ), $aStatus['name'] );
					}
				}

				//Set Object Functions Combo
				$oObjectFunctions = new DBObjectFunctions();
				$aFunctions = $oObjectFunctions->getFunctions2();
				
				$oResponse->setFormElement( 'form1', 'nFunction' );
				$oResponse->setFormElementChild( 'form1', 'nFunction', array( 'value' => 0 ), " Всички дейности " );
				foreach( $aFunctions as $aFunction )
				{
					$oResponse->setFormElementChild( 'form1', 'nFunction', array( 'value' => $aFunction['id'] ), $aFunction['name'] );
				}
				if( $aParams['nFunction'] != 0 )
				{
					$oResponse->setFormElementAttribute( 'form1', 'nFunction', 'value', $aParams['nFunction'] );
				}
				//End Set Object Functions Combo
				
				//Set Dates
				$oResponse->setFormElement( "form1", "sUnpaid" );
				for( $i = -6; $i <= 3; $i++ )
				{
					$aSelected = ( !isset( $aParams['sPaidTo'] ) ) ? array( "selected" => "selected" ) : array();
					if( $i == 0 )
					{
						$oResponse->setFormElementChild( "form1", "sUnpaid", array_merge( array( "value" => 0 ), $aSelected ), "-- Изберете --" );
					}
					
					$sDateToFill = offsetMonth( $i );
					
					$oResponse->setFormElementChild( "form1", "sUnpaid", array( "value" => "{$sDateToFill}-01" ), $sDateToFill );
				}
				
				if( isset( $aParams['sPaidTo'] ) )
				{
					$oResponse->setFormElementAttribute( "form1", "sUnpaid", "value", $aParams['sPaidTo'] . "-01" );
				}
				//End Set Dates
				
				print( $oResponse->toXML() );
			break;
		
		case 'genregions' : 
				$aRegions = array();
				
				$firm_match = empty($aParams['id_firm']) ? 0 : (int) $aParams['id_firm'];
				
				if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
				{
					$sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
					$sCondition = " id IN ({$sAccessable}) ";
				}
				else $sCondition = " 1 ";
				
				if( $firm_match )$oRegions->getResult( $aRegions, NULL, array( " to_arc = 0 ", " id_firm = $firm_match ", $sCondition ), "name" );
				else $oRegions->getResult( $aRegions, NULL, array( " to_arc=0 ", $sCondition ), "name" );
				
				$oResponse->setFormElement( 'form1', 'id_reg' );
				
				$oResponse->setFormElementChild( 'form1', 'id_reg', array('value'=>0), 'Всички Региони');
				foreach( $aRegions as $aRegion )
				{
					$oResponse->setFormElementChild( 'form1', 'id_reg', array( 'value' => $aRegion['id'] ), sprintf( "%s [%s]", $aRegion['name'], $aRegion['code'] ) );
				}
				
				print( $oResponse->toXML() );
			break;
		
		case 'genReactionOffices':
				if( !empty( $aParams['nIDReactionFirm'] ) )
				{
					$oResponse->setFormElementAttribute( "form1", "nIDReactionFirm", "value", $aParams['nIDReactionFirm'] );
					
					$oResponse->setFormElement( "form1", "nIDReactionOffice", array(), "" );
					$oResponse->setFormElementChild( "form1", "nIDReactionOffice", array( "value" => 0 ), "Реагиращ офис" );
					
					$aOffices = array();
					$aOffices = $oDBOffices->getOfficesByFirm( $aParams['nIDReactionFirm'] );
					
					foreach( $aOffices as $aOffice )
					{
						$oResponse->setFormElementChild( "form1", "nIDReactionOffice", array( "value" => $aOffice['id'] ), $aOffice['name'] . "[{$aOffice['code']}]" );
					}
					
					if( !empty( $aParams['nIDReactionOffice'] ) )
					{
						$oResponse->setFormElementAttribute( "form1", "nIDReactionOffice", "value", $aParams['nIDReactionOffice'] );
					}
				}
				else
				{
					$oResponse->setFormElement( "form1", "nIDReactionOffice", array(), "" );
					$oResponse->setFormElementChild( "form1", "nIDReactionOffice", array( "value" => 0 ), "Реагиращ офис" );
				}
				
				print( $oResponse->toXML() );
			break;
		
		case 'genTechOffices':
				if( !empty( $aParams['nIDTechFirm'] ) )
				{
					$oResponse->setFormElementAttribute( "form1", "nIDTechFirm", "value", $aParams['nIDTechFirm'] );
					
					$oResponse->setFormElement( "form1", "nIDTechOffice", array(), "" );
					$oResponse->setFormElementChild( "form1", "nIDTechOffice", array( "value" => 0 ), "Сервизен офис" );
					
					$aOffices = array();
					$aOffices = $oDBOffices->getOfficesByFirm( $aParams['nIDTechFirm'] );
					
					foreach( $aOffices as $aOffice )
					{
						$oResponse->setFormElementChild( "form1", "nIDTechOffice", array( "value" => $aOffice['id'] ), $aOffice['name'] . "[{$aOffice['code']}]" );
					}
					
					if( !empty( $aParams['nIDTechOffice'] ) )
					{
						$oResponse->setFormElementAttribute( "form1", "nIDTechOffice", "value", $aParams['nIDTechOffice'] );
					}
				}
				else
				{
					$oResponse->setFormElement( "form1", "nIDTechOffice", array(), "" );
					$oResponse->setFormElementChild( "form1", "nIDTechOffice", array( "value" => 0 ), "Сервизен офис" );
				}
				
				print( $oResponse->toXML() );
			break;
		
		default: 
			break;
	}
	
	class MyHandler extends APIHandler
	{
		function setFields( $aParams )
		{
			global $oResponse, $right_edit, $oFilters, $oFiltersVisibleFields;
			
			$nIDFilter 	= (int) ( isset( $aParams['schemes'] ) && !empty( $aParams['schemes'] ) ) ? $aParams['schemes'] : 0;
			
			
			$oResponse->setField( 'status' , 		'статус', 			'Сортирай по статус', 	NULL, "openObjectMessages",NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'num' , 			'номер', 			'Сортирай по номер', 	NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_NUMBER ) );
			$oResponse->setField( 'name', 			'име на обект', 	'Сортирай по име', 		NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
							
			
			if( !empty( $nIDFilter ) )
			{
				$aFilterVisibleFields = $oFiltersVisibleFields->getFieldsByIDFilter( $nIDFilter );
				foreach( $aFilterVisibleFields as $value )
				{
					if( $value == "nTech" )
					{
						$oResponse->setField( 'total_broi',	'брой техника', 	'сортирай по брой техника', NULL,"openObjectStore",NULL,	array( 'DATA_FORMAT' => DF_NUMBER ));
						$oResponse->setField( 'cp',			'номенклатура', 	'сортирай по номенклатура', NULL,"openObjectStore",NULL,	array( 'DATA_FORMAT' => DF_STRING ));
					}
					
					if( $value == "nMonthTax" )
					{
						
						$oResponse->setField( '&month_tax', 'Абонамент', NULL, NULL, "openObjectTaxes", NULL, array( 'DATA_FORMAT' => DF_CURRENCY ) );
					}
					
					if( $value == "nUnpaidSingle" )
					{
						$oResponse->setField( '&unpaid_singles', 'Неплат. други задължения', NULL, NULL, "openObjectTaxes", NULL, array( 'DATA_FORMAT' => DF_CURRENCY ) );
					}
					
					if( $value == "nLastPaid" )
					{
						$oResponse->setField( 'last_paid', 'Последно платен месец', NULL, NULL, "openObjectTaxes", NULL, NULL );
					}
					
					if( $value == "nObjectType" )
					{
						$oResponse->setField( 'object_type', 'Тип', 'Сортирай по тип', NULL, "openObjectTaxes", NULL, array( 'DATA_FORMAT' => DF_STRING ) );
					}
					
					if( $value == "nObjectFunction" )
					{
						$oResponse->setField( 'object_function', 'Назначение', 'Сортирай по назначение', NULL, "openObject", NULL, array( 'DATA_FORMAT' => DF_STRING ) );
					}
					
					if( $value == "nObjectPhone" )
					{
						$oResponse->setField( 'object_phone', 'Телефон', 'Сортирай по телефон на обекта', NULL, "openObject", NULL, array( 'DATA_FORMAT' => DF_STRING ) );
					}
					
					if( $value == "nStartDate" )
					{
						$oResponse->setField( 'start_date', 'Въведен', 'Сортирай по дата на стартиране / въвеждане', NULL, "openObjectContract", NULL, array( 'DATA_FORMAT' => DF_DATE ) );
					}
					
					if( $value == "nAddress" )
					{
						$oResponse->setField( 'address', 'Улица', 'Сортирай по улица', NULL, "openObject", NULL, array( 'DATA_FORMAT' => DF_STRING ) );
					}
					
					if( $value == "nDistance" )
					{
						$oResponse->setField( 'distance', 'Дистанция', 'Сортирай по дистанция', NULL, "openObject", NULL, array( 'DATA_FORMAT' => DF_NUMBER ) );
					}
					
					if( $value == "nOperativeInfo" )
					{
						$oResponse->setField( 'operative_info', 'Оперативна информация', 'Сортирай по оперативна информация' );
					}
										
					if( $value == "nAdminReg")
					{
						$oResponse->setField( 'admin_reg', 'Регион за администрация', NULL, NULL,"openObject",NULL, array('DATA_FORMAT' => DF_STRING));
					}
					
					if( $value == "nTechReg")
					{
						$oResponse->setField( 'tech_reg', 'Регион за техн.поддръжка', NULL, NULL,"openObject",NULL, array('DATA_FORMAT' => DF_STRING));
					}
					
					if( $value == "nReactReg")
					{
						$oResponse->setField( 'reaction_reg', 'Регион за реакция', NULL, NULL,"openObject",NULL, array('DATA_FORMAT' => DF_STRING));
					}

                    if( $value == "nWorkTime")
                    {
                        $oResponse->setField( 'work_time', 'Раб.време', NULL, NULL,"openObject",NULL, array('DATA_FORMAT' => DF_TIME));
                    }
					
//					if( $value == "nRS" )
//					{
//						$oResponse->setField( 'rs_name', 'Рекламен сътрудник', 'Сортирай по рекламен сътрудник', NULL, "openObjectContract", NULL, array( 'DATA_FORMAT' => DF_STRING ) );
//					}
					
//					if( $value == "nLiabilities" )
//					{
//						$oResponse->setField( 'single_liability', 	'Еднокр. отговорност', 	'Сортирай по еднократна отговорност', 	NULL, "openObjectContract", NULL, array( 'DATA_FORMAT' => DF_FLOAT ) );
//						$oResponse->setField( 'year_liability', 	'Год. отговорност', 	'Сортирай по годишна отговорност', 		NULL, "openObjectContract", NULL, array( 'DATA_FORMAT' => DF_FLOAT ) );
//					}
					
//					if( $value == "nEndContract" )
//					{
//						$oResponse->setField( '&end_contract', 'Край на договора', NULL, NULL, "openObjectContract", NULL, array( 'DATA_FORMAT' => DF_DATE ) );
//					}
						
					
				}
			}
			else
			{
 
				
				
				$oResponse->setField( 'name_firm', 		'име на фирма', 	'Сортирай по име на фирма', 	NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
				$oResponse->setField( 'name_region', 	'име на регион', 	'Сортирай по име на регион', 	NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			}
			
			$oResponse->setField( "name_client", "клиент", "Сортирай по Клиент", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			
			$oResponse->setFieldLink( "name", 			"openObject" );
			$oResponse->setFieldLink( "name_client", 	"viewClient" );
			//$oResponse->setFieldLink( "operative_info", "openObject");
		}
		
		function getReport( $aParams )
		{
			global $oResponse, $oObjectServices, $db_name_finance, $db_name_sod, $db_name_storage, $db_sod, $oFiltersVisibleFields;
			
			$nIDFilter 	= (int) ( isset( $aParams['schemes'] ) && !empty( $aParams['schemes'] ) ) ? $aParams['schemes'] : 0;
			
			$aWhere	= array();
			$aJoin = array();
			$aGroup = array();
			$aColumn = array();
			$aContract = array();
		 	
			//проверява дали е чекнато показването с ДДС
			$nDDS = 0;
			if( isset( $aParams['nDDS'] ) )
			{
				if( isset( $aParams['api_action'] ) && substr( $aParams['api_action'], 0, 9 ) == "export_to" )$nDDS = 1;
				else
				{
					if( !empty( $aParams['nDDS'] ) )$nDDS = 1;
				}
			}
			
			if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
			{
				$sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
				$sCondition = " t.id_office IN ({$sAccessable}) ";
			}
			else $sCondition = "";
			
			if( !empty( $sCondition ) )
				$aWhere[] = $sCondition;

			$searchID = empty( $aParams['id_firm'] ) && is_numeric( $aParams['id_firm'] ) ? 0 : $aParams['id_firm'];
			if( $searchID != 0 )$aWhere[] = sprintf( " f.id = '%s' ", $searchID );
			$searchID = empty( $aParams['id_reg'] ) && is_numeric( $aParams['id_reg'] ) ? 0 : $aParams['id_reg'];
			if( $searchID != 0 )$aWhere[] = sprintf( " r.id = '%s' ", $searchID );
			
			
			
			if( isset( $aParams['nIsPhysical'] ) )
			{
				if( isset( $aParams['api_action'] ) && substr( $aParams['api_action'], 0, 9 ) == "export_to" )
				{
					$aWhere[] = " t.is_fo = '1' ";
				}
				else
				{
					if( $aParams['nIsPhysical'] )$aWhere[] = sprintf( " t.is_fo = '%s' ", $aParams['nIsPhysical'] );
				}
			}
			
			if( isset( $aParams['nIsSOD'] ) )
			{
				if( isset( $aParams['api_action'] ) && substr( $aParams['api_action'], 0, 9 ) == "export_to" )
				{
					$aWhere[] = " t.is_sod = '1' ";
				}
				else
				{
					if( $aParams['nIsSOD'] )$aWhere[] = sprintf( " t.is_sod = '%s' ", $aParams['nIsSOD'] );
				}
			}
				
			if( isset( $aParams['nIsTech'] ) )
			{
				if( isset( $aParams['api_action'] ) && substr( $aParams['api_action'], 0, 9 ) == "export_to" )
				{
					$aWhere[] = " t.is_tech = '1' ";
				}
				else
				{
					if( $aParams['nIsTech'] )$aWhere[] = sprintf( " t.is_tech = '%s' ", $aParams['nIsTech'] );
				}
			}

            //
            if( isset( $aParams['nIsServiceMode'] ) )
            {
                if( isset( $aParams['api_action'] ) && substr( $aParams['api_action'], 0, 9 ) == "export_to" )
                {
                    $aWhere[] = " t.service_status = '1' ";
                }
                else
                {
                    if( $aParams['nIsServiceMode'] )$aWhere[] = sprintf( " t.service_status = '%s' ", $aParams['nIsServiceMode'] );
                }
            }

            if( isset( $aParams['nIsWorkTime'] ) )
            {
                if( isset( $aParams['api_action'] ) && substr( $aParams['api_action'], 0, 10 ) == "export_to" )
                {
                    $aWhere[] = " t.work_time_alert = '00:00:00' ";
                }
                else
                {
                    if( $aParams['nIsWorkTime'] )$aWhere[] = sprintf( " t.work_time_alert != '00:00:00' ", $aParams['nIsWorkTime'] );
                }
            }
			
			if( !empty( $aParams['nNum'] ) && is_numeric( $aParams['nNum'] ) )
				$aWhere[] = sprintf( " t.num = %s ", $aParams['nNum'] );
			
			if( !empty( $aParams['nNum1'] ) && is_numeric( $aParams['nNum1'] ) )
				$aWhere[] = sprintf( " t.num = %s ", $aParams['nNum1'] );
		
				if( !empty( $aParams['sName'] ) )
			{
				$aParams['sName'] = addslashes( $aParams['sName'] );
				$aWhere[] = sprintf( " t.name LIKE '%%%s%%' ", $aParams['sName'] );
			}
            $nStatus = $_POST['aStatus'];
            APILog::Log(0,$aWhere);
            $aStatus = array();
            if(!($nStatus[0]=='0') && !empty($nStatus) )
            {
                foreach ($nStatus as $k=> $v)
                {
                    if($v)
                        $aStatus[] = intval($v);
                }

                $aWhere[] = " t.id_status IN (".implode(",", $aStatus).") ";
            }

			if( !empty( $aParams['nFunction'] ) && is_numeric( $aParams['nFunction'] ) )
				$aWhere[] = sprintf(" t.id_function = %s ", $aParams['nFunction'] );
			if( !empty( $aParams['nType'] ) && is_numeric( $aParams['nType'] ) )
				$aWhere[] = sprintf(" t.id_objtype = %s ", $aParams['nType'] );
			if( !empty( $aParams['sAddress'] ) )
				$aWhere[] = sprintf(" t.address LIKE '%%%s%%' ", $aParams['sAddress'] );
				
			$sMolColumn = "";
			$sMolJoin = "";
			if( !empty( $aParams['sMol'] ) ) {
				$aWhere[] = sprintf(" fa.name LIKE '%%%s%%' ", $aParams['sMol'] );
				$sMolColumn = "fa.name AS mol,";
				$sMolJoin = " LEFT JOIN (select f.id_obj,f.name from faces as f where f.name like '%%".$aParams['sMol']."%%' group by f.id_obj) as fa ON fa.id_obj = t.id ";
			}
			
			$sPhoneColumn = "";
			$sPhoneJoin = "";
			if( !empty( $aParams['sPhone'] ) ) {
				$aWhere[] = sprintf(" ( trim(ft.phone) LIKE '%%%s%%' ) ", $aParams['sPhone'] );
				$sPhoneColumn = " replace(ft.phone, ' ','') AS phone,";
				$sPhoneJoin = " LEFT JOIN (select ff.id_obj, ff.phone from faces as ff where ff.to_arc = 0 AND replace(ff.phone,' ','') like '%%".$aParams['sPhone']."%%' group by ff.id_obj) as ft ON ft.id_obj = t.id ";
			}
			
			//if( !empty( $aParams['sPhone'] ) ) {
			//	$aWhere[] = sprintf(" t.phone LIKE '%%%s%%' ", $aParams['sPhone'] );
			//}
			
			if( !empty( $aParams['sIDN'] ) )
				$aWhere[] = sprintf(" f.idn = '%s' ", $aParams['sIDN'] );
			
			$searchID = empty( $aParams['nIDReactionFirm'] ) && is_numeric( $aParams['nIDReactionFirm'] ) ? 0 : $aParams['nIDReactionFirm'];
			if( $searchID != 0 )$aWhere[] = sprintf( " rf.id = '%s' ", $searchID );
			$searchID = empty( $aParams['nIDReactionOffice'] ) && is_numeric( $aParams['nIDReactionOffice'] ) ? 0 : $aParams['nIDReactionOffice'];
			if( $searchID != 0 )$aWhere[] = sprintf( " ro.id = '%s' ", $searchID );
			$searchID = empty( $aParams['nIDTechFirm'] ) && is_numeric( $aParams['nIDTechFirm'] ) ? 0 : $aParams['nIDTechFirm'];
			if( $searchID != 0 )$aWhere[] = sprintf( " tf.id = '%s' ", $searchID );
			$searchID = empty( $aParams['nIDTechOffice'] ) && is_numeric( $aParams['nIDTechOffice'] ) ? 0 : $aParams['nIDTechOffice'];
			if( $searchID != 0 )$aWhere[] = sprintf( " tof.id = '%s' ", $searchID );
			
			if( isset( $aParams['sFromDate'] ) && !empty( $aParams['sFromDate'] ) )
			{
				$nTimeFrom = jsDateToTimestamp( $aParams['sFromDate'] );
				$aWhere[] = " UNIX_TIMESTAMP( t.start ) >= {$nTimeFrom} ";
			}
			if( isset( $aParams['sToDate'] ) && !empty( $aParams['sToDate'] ) )
			{
				$nTimeTo = jsDateEndToTimestamp( $aParams['sToDate'] );
				$aWhere[] = " UNIX_TIMESTAMP( t.start ) <= {$nTimeTo} ";
			}
			
			if( isset( $aParams['nHasNoClient'] ) )
			{
				if( isset( $aParams['api_action'] ) && substr( $aParams['api_action'], 0, 9 ) == "export_to" )
				{
					$aWhere[] = " ISNULL( co.id ) ";
				}
				else
				{
					if( !empty( $aParams['nHasNoClient'] ) )
					{
						$aWhere[] = " ISNULL( co.id ) ";
					}
				}
			}
			
			if( isset( $aParams['sUnpaid'] ) && !empty( $aParams['sUnpaid'] ) )
			{
				$aWhere[] = "
					IF
					(
						os.real_paid != '0000-00-00',
						UNIX_TIMESTAMP( os.real_paid ) < UNIX_TIMESTAMP( '{$aParams['sUnpaid']}' ),
						UNIX_TIMESTAMP( os.start_date - INTERVAL 1 MONTH ) < UNIX_TIMESTAMP( '{$aParams['sUnpaid']}' )
					)
				";
			}
			
//			 if( ( $aParams['sfield'] == 'total_broi' ) || ($aParams['sfield'] == 'cp' )) {
//			 	$aParams['sfield'] = "num";
//			 	$aParams['stype'] = 0;
//			}
			if( !empty( $nIDFilter ) )
			{
				$aFilterVisibleFields = $oFiltersVisibleFields->getFieldsByIDFilter( $nIDFilter );
				
				foreach( $aFilterVisibleFields as $value )
				{
					if( $value == "nTech" )
					{
						$aWhere[] = "(nom.unit is null OR nom.unit in ('бр.'))
						";
						$aJoin[] = "
						LEFT JOIN {$db_name_storage}.states sta ON ( sta.id_storage = t.id AND sta.storage_type = 'object' and sta.count > 0 )
						LEFT JOIN {$db_name_storage}.nomenclatures nom ON nom.id = sta.id_nomenclature
						LEFT JOIN {$db_name_storage}.nomenclature_types nom_t ON nom.id_type = nom_t.id
						";
						$aGroup[] = "sta.id, nom.id";
							
						$aColumn[] =  "

						SUM( sta.count ) / if(count(distinct os.id) < 1, 1, count(distinct os.id))  AS total_broi,
						group_concat(distinct lpad(cast(round(nom.id,0) as char),15,'0'),'|',concat(cast(round(sta.count,0) as char),'бр. ',nom.name) SEPARATOR '\n')  as total_broi_hint,
						group_concat(distinct if((nom_t.is_control_panel > 0) and (sta.count > 0), nom.name,NULL ) SEPARATOR '|') as cp
						
						";					;
					}
					
					if( $value == "nLiabilities" )
					{
						$aContract[]="
						con.single_liability AS single_liability,
						con.year_liability AS year_liability";
					}
					if( $value == "nEndContract" )
					{
						$aContract[]="
						DATE_FORMAT( con.contract_date, '%%d' ) AS con_day,
						DATE_FORMAT( con.contract_date, '%%m' ) AS con_month,
						DATE_FORMAT( con.contract_date, '%%Y' ) AS con_year,
						con.period_in_month AS period_in_month";
					}
					if( $value == "nRS" )
					{
						$aContract[]="CONCAT( con.rs_name, ' [ ', con.rs_code, ' ]' ) AS rs_name ";
					}
					if( $value == "nObjectFunction" )
					{
						$aJoin[] ="
						LEFT JOIN {$db_name_sod}.object_functions obj_fun ON obj_fun.id = t.id_function";
						$aGroup[] ="obj_fun.id";
						$aColumn[] = "
						obj_fun.name AS object_function";
					}
					if( $value == "nObjectType" )
					{
						$aJoin[] ="
						LEFT JOIN {$db_name_sod}.object_types obj_typ ON obj_typ.id = t.id_objtype";
						$aGroup[] ="obj_typ.id";
						$aColumn[] = "obj_typ.name AS object_type";
					}
				}
				
				if ((!in_array("nTech", $aFilterVisibleFields)) && (( $aParams['sfield'] == 'total_broi' ) || ($aParams['sfield'] == 'cp' )) ) {
					$aParams['sfield'] = "num";
					$aParams['stype'] = 0;
				}
				if ((!in_array("nLiabilities", $aFilterVisibleFields)) && (( $aParams['sfield'] == 'single_liability' ) || ($aParams['sfield'] == 'year_liability' )) ) {
					$aParams['sfield'] = "num";
					$aParams['stype'] = 0;
				}
				if ((!in_array("nObjectFunction", $aFilterVisibleFields)) && ($aParams['sfield'] == 'object_function' ) ) {
					$aParams['sfield'] = "num";
					$aParams['stype'] = 0;
				}
				if ((!in_array("nObjectType", $aFilterVisibleFields)) && ($aParams['sfield'] == 'object_type' ) ) {
					$aParams['sfield'] = "num";
					$aParams['stype'] = 0;
				}
				
				//ako e ne e otbelqzano nito edno ot trite da ne dobavq join
				if(!empty($aContract))
				{
					$aJoin[] ="
					LEFT JOIN {$db_name_finance}.contracts con ON ( con.id_obj = t.id AND con.contract_status = 'validated' AND con.to_arc = 0 )";
					$aGroup[] ="con.id";	
				}			
			}

			//left join
			$sTotalJoin		= implode("", $aJoin);
			
			//contract columns
			$sTotalContract	= implode(", ", $aContract);
			$sTotalContract 	.= empty($sTotalContract) ? "" : ",";
			
			//group by
			$sTotalGroup 	= implode(", ", $aGroup);
			$sTotalGroup 	= empty($sTotalGroup) ? "" : "," .$sTotalGroup;
			
			//total column
			$sTotalColumn	= implode(", ", $aColumn);
			$sTotalColumn = empty($sTotalColumn) ?  "" : $sTotalColumn.",";
			
			$sTotalWhere 	= implode(" AND ", $aWhere);
			$sTotalWhere 	.= empty($aWhere) ? " 1 " : "";
			$aTotal			= array();
			$nMonthTotal	= 0;
			$nSingleTotal	= 0;
			$nIDFilter 		= (int) ( isset( $aParams['schemes'] ) && !empty( $aParams['schemes'] ) ) ? $aParams['schemes'] : 0;
			$aFilterv 		= $oFiltersVisibleFields->getFieldsByIDFilter( $nIDFilter );
			
			

						
			// query za totalite
			if ( in_array("nMonthTax", $aFilterv) || in_array("nUnpaidSingle", $aFilterv) ) {
				$sQuery = "
					SELECT 
						DISTINCT t.id,
						IF( 
						{$nDDS}, 
						SUM(os.total_sum) , 
						SUM(os.total_sum) / 1.2
						)  as month_sum,
						IF( 
						{$nDDS}, 
						SUM(osi.total_sum) , 
						SUM(osi.total_sum) / 1.2
						)  as single_sum						
						
					FROM {$db_name_sod}.objects t
					LEFT JOIN {$db_name_sod}.clients_objects co ON ( co.id_object = t.id AND co.to_arc = 0 )
					LEFT JOIN {$db_name_sod}.clients cl ON cl.id = co.id_client
					LEFT JOIN {$db_name_sod}.offices r ON r.id = t.id_office
					LEFT JOIN {$db_name_sod}.statuses s ON s.id = t.id_status
					LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
					LEFT JOIN {$db_name_sod}.offices ro ON ro.id = t.id_reaction_office
					LEFT JOIN {$db_name_sod}.firms rf ON rf.id = ro.id_firm
					LEFT JOIN {$db_name_sod}.offices tof ON tof.id = t.id_tech_office
					LEFT JOIN {$db_name_sod}.firms tf ON tf.id = tof.id_firm
					LEFT JOIN {$db_name_sod}.faces fa ON fa.id_obj = t.id
					LEFT JOIN {$db_name_sod}.objects_services os ON ( os.id_object = t.id AND os.to_arc = 0 )
					LEFT JOIN {$db_name_sod}.objects_singles osi ON ( osi.id_object = t.id AND osi.to_arc = 0 AND osi.id_sale_doc < 1 AND UNIX_TIMESTAMP(osi.paid_date) = 0 )
					{$sTotalJoin}
					WHERE {$sTotalWhere}
					GROUP BY t.id, co.id, cl.id, r.id, s.id, f.id, ro.id, rf.id, tof.id,tf.id, fa.id{$sTotalGroup}
				";
				
				$aTotal = $db_sod->getArray($sQuery);
				
				foreach ( $aTotal as $nTotal ) {
					$nMonthTotal 	+= $nTotal['month_sum'];
					$nSingleTotal 	+= $nTotal['single_sum'];
				}
				
				$oResponse->addTotal("&month_tax", $nMonthTotal);
				$oResponse->addTotal("&unpaid_singles", $nSingleTotal);			
			} else {
				$oResponse->addTotal("&month_tax", 0);
				$oResponse->addTotal("&unpaid_singles", 0);
			}
			
			$sQuery = sprintf(" 
				SELECT 
					SQL_CALC_FOUND_ROWS 
					t.id AS _id, 
					IF
					(
						cl.id,
						CONCAT( t.id,',', cl.id ),
						CONCAT( t.id,',0' )
					) AS id,
					t.num, 
					s.name as status,
					t.name,
					t.is_sod as is_sod,
					CONCAT( f.name, ' [', f.code, ']' ) AS name_firm,
					CONCAT( r.name, ' [', r.code, ']' ) AS name_region,
					{$sMolColumn}
					{$sPhoneColumn}
					r.name admin_reg,
					tof.name tech_reg,
					ro.name as reaction_reg,
					t.phone AS object_phone,
					t.start AS start_date,
					t.work_time_alert as work_time,
					t.address AS address,
					t.distance AS distance,
					t.operativ_info AS operative_info,
					{$sTotalContract}
					cl.name AS name_client,
					{$sTotalColumn}
					IF
					(
						os.real_paid != '0000-00-00',
						MIN(UNIX_TIMESTAMP( os.real_paid )),
						UNIX_TIMESTAMP( os.start_date - INTERVAL 1 MONTH )
					) as last_paid
				FROM
					%s t
					LEFT JOIN clients_objects co ON ( co.id_object = t.id AND co.to_arc = 0 )
					LEFT JOIN clients cl ON cl.id = co.id_client
					LEFT JOIN offices r ON r.id = t.id_office
					LEFT JOIN statuses s ON s.id = t.id_status
					LEFT JOIN firms f ON f.id = r.id_firm
					LEFT JOIN offices ro ON ro.id = t.id_reaction_office
					LEFT JOIN firms rf ON rf.id = ro.id_firm
					LEFT JOIN offices tof ON tof.id = t.id_tech_office
					LEFT JOIN firms tf ON tf.id = tof.id_firm
					#LEFT JOIN faces fa ON fa.id_obj = t.id
					{$sMolJoin}
					{$sPhoneJoin}
					LEFT JOIN objects_services os ON ( os.id_object = t.id AND os.to_arc = 0 )
					{$sTotalJoin}
				", 
				$this->_oBase->_sTableName
			);
			APILog::Log(0,$sQuery);
//					DATE_FORMAT(
//						MIN(
//							IF
//							(
//								os.last_paid != '0000-00-00',
//								IF
//								(
//									UNIX_TIMESTAMP( ( os.start_date - INTERVAL 1 MONTH ) ) > UNIX_TIMESTAMP( os.last_paid ),
//									( os.start_date - INTERVAL 1 MONTH ),
//									os.last_paid
//								),
//								os.start_date
//							)
//						),
//						'%%m.%%Y'
//					) AS last_paida			
			
			global $db_sod_backup;
			
			$aData = array();
			
			
			$nResult = $this->_oBase->getReport( $aParams, $sQuery, $aWhere, "_id" , $aData, $db_sod_backup );
			
			foreach( $oResponse->oResult->aData as $key => &$value )
			{
				$aID = explode( ",", $value['id'] );
				$nID = isset( $aID[0] ) ? $aID[0] : 0;
				
 				
				 
				
				// omazva DDS-to
				$value['&month_tax'] = $oObjectServices->getSumPriceByObject( $nID )  ;

				
//				APILog::Log( "DDS->>>".$nDDS);
				if( !$nDDS && ($value['&month_tax'] > 0)) 
				{
					$value['&month_tax'] /= 1.2;
				}
				
				// omazva DDS-to
				$value['&unpaid_singles'] = $oObjectServices->getSinglePriceByObject( $nID );
				if( !$nDDS && ($value['&unpaid_singles'] > 0)) 
				{
					$value['&unpaid_singles'] /= 1.2;
				}
				
				
					
				
				if ( !empty($oResponse->oResult->aData[$key]['last_paid']) ) {
					$oResponse->oResult->aData[$key]['last_paid'] 		= date("m.Y", $oResponse->oResult->aData[$key]['last_paid']);
				} else {
					$oResponse->oResult->aData[$key]['last_paid'] 		= "";
				}
				$oResponse->setDataAttributes( $key, "last_paid", array( "style" => "text-align: center" ) );
				//$oResponse->oResult->aData[$key]['last_paid'] = $oObjectServices->getLastPaidMonth( $nID );
				if( !empty($value['con_day']) & !empty($value['con_month']) & !empty($value['con_year']) &!empty($value['period_in_month']) )
				{
					$nContractDay 		= $value['con_day'];
					$nContractMonth 	= $value['con_month'];
					$nContractYear 		= $value['con_year'];
					$nContractPeriod 	= $value['period_in_month'];
					$oResponse->oResult->aData[$key]['&end_contract'] = date( "Y-m-d", mktime( 0, 0, 0, $nContractMonth + $nContractPeriod, $nContractDay, $nContractYear ) );
				}
				else $oResponse->oResult->aData[$key]['&end_contract'] = "";
				
				
				if(!empty ($value['operative_info']))
					$oResponse->setDataAttributes($key, "operative_info", array("style" => "background-image: url( 'images/dots.gif' ); background-repeat: no-repeat; cursor: pointer;", "onclick" => "openObject( '{$value['id']}' );", "title" => $value['operative_info']));
				$value['operative_info'] = "";
				
				if(!empty ($value['total_broi'])) {
					$aTotal_broi_hint = explode("\n", $value['total_broi_hint']);
					$aTotal_broi_hint_final = "";
					foreach ($aTotal_broi_hint as &$val) {
						$val = substr($val, 16);
					}
					$aTotal_broi_hint_final = implode("\n", $aTotal_broi_hint);
					
					$oResponse->setDataAttributes( $key, "total_broi", array("width" => "100px", "title" => $aTotal_broi_hint_final));
				}
				
				if(!empty ($value['cp']))
					if (utf8_strlen($value['cp']) > 10)
					{	
						$nom = explode("|", $value['cp']);
						$oResponse->setDataAttributes( $key, "cp", array("width" => "100px", "title" => $nom[0]));
						if(utf8_strlen($nom[0]) > 10)
							$value['cp'] = utf8_substr($nom[0], 0, 10) . '...';
						else $value['cp'] =$nom[0];
					}
				
				if($value['is_sod'] == 1)
				    $oResponse->setDataAttributes($key, "num", array("style" => "cursor: pointer;", "onclick" => "openObjectArchiv( '{$value['id']}' );"));
				
		}
			
			return $nResult;
		}
	}
	
	if( $aParams['api_action'] != 'generate' && $aParams['api_action'] != 'genregions' )
	{
		$oHandler = new MyHandler( $oObjects, 'num', 'objects', 'Обекти' );
		
		$oHandler->Handler( $aParams );
	}

?>
<?php
	require_once ('./vo/telenet/api/FlexResponse.php');
	require_once ('./vo/telenet/api/FlexVar.php');
	require_once ('./vo/telenet/api/FlexControl.php');
	
//conection info
// define( "DATABASE_SERVER", "telepol.net:3307");
// define( "DATABASE_USERNAME", "rumen");
// define( "DATABASE_PASSWORD", "rumen");
// define( "DATABASE_NAME", "sod");	
	
	if ( !isset($_SESSION) ) {
		session_start();
	}
	
	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
	set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname']."/../".PATH_SEPARATOR.$aPath['dirname']."/../../");

	require_once ("config/function.autoload.php");
	require_once ("include/adodb/adodb-exceptions.inc.php");
	require_once ("config/connect.inc.php");
	require_once ("include/general.inc.php");

class buy {
//	private $aFlexVars = array();
//	private $aFlexControls = array();
//	private $oHiddenParams = array();

	public function __construct() {
		global $oResponse;
		
		$oResponse = new DBResponse();
	}
	
	public function init( $nID ) {
		global $oResponse;
		
		$oFirms 	= new DBFirms();
		$oEarnings 	= new DBNomenclaturesEarnings();
		$oBuyDoc 	= new DBBuyDocs();
		$oBankAcc 	= new DBBankAccounts();
		$oDirection	= new DBDirections();
		$oBuyDocRows= new DBBuyDocsRows();
		
		//$oResponse	= new DBResponse();
		
		$aDirection = array();
		$aOrders	= array();
		$aRows 		= array();
		$aBuyDocRows= array();
		$aBuyDoc	= array();
		$aBankAcc	= array();
		$aFirms 	= array();
		$aFirms2 	= array();
		$aEarnings	= array();
		$aData		= array();  		// Масив с данните за фактурата, ако има такива
		$nID		= strval($nID); 	// "2009040000063"; 	// Твърдо
		
		$oResponse->SetHiddenParam( "nID", $nID );
			
		
		// Инициализираме масива сис сесията, ако не съществува
		if ( !isset($_SESSION['userdata']['access_right_levels']) ) {
			$_SESSION['userdata']['access_right_levels'] = array();
		}
		
		$aFirms = $oFirms->getFirmsAsClient();
		
		if ( !empty($nID) ) {
			$aBuyDoc 	 = $oBuyDoc->getDoc( $nID );
			
			// ДДС
			$aDDS		= array();
			$aDDS 		= $oBuyDocRows->getDDSByDoc( $nID );	

			// Списък с описа
			$aBuyDocRows = $oBuyDocRows->getRowsByDoc( $nID );	
			
			// Списък с ордерите
			$aOrders 	 = $oBuyDocRows->getOrdersByDoc( $nID );
			
			if ( isset($aBuyDoc['client_name']) && !empty($aBuyDoc['client_name']) ) {
				$aTemp = array();
				$aTemp[0]['name'] 		= $aBuyDoc['client_name'];
				$aTemp[0]['title'] 		= "---";
				$aTemp[0]['address'] 	= isset($aBuyDoc['client_address']) ? $aBuyDoc['client_address'] : "";
				$aTemp[0]['idn'] 		= isset($aBuyDoc['client_ein']) 	? $aBuyDoc['client_ein'] 	 : "";
				$aTemp[0]['idn_dds'] 	= isset($aBuyDoc['client_ein_dds']) ? $aBuyDoc['client_ein_dds'] : "";
				$aTemp[0]['jur_mol'] 	= isset($aBuyDoc['client_mol']) 	? $aBuyDoc['client_mol'] 	 : "";
				
				$aFirms = array_merge($aTemp, $aFirms);
			}
			
			$sDate = isset($aBuyDoc['doc_date']) ? $aBuyDoc['doc_date'] : date("Y-m-d");
			
			$aData['id']				= $nID;
			$aData['doc_num'] 			= isset($aBuyDoc['doc_num']) 				? $aBuyDoc['doc_num'] 					: "";
			$aData['doc_date'] 			= $sDate;
			$aData['doc_type'] 			= isset($aBuyDoc['doc_type']) 				? $aBuyDoc['doc_type'] 					: "faktura";
			$aData['client_recipient'] 	= isset($aBuyDoc['client_recipient']) 		? $aBuyDoc['client_recipient'] 			: "";
			$aData['id_deliverer'] 		= isset($aBuyDoc['id_deliverer']) 			? $aBuyDoc['id_deliverer'] 				: 0;
			$aData['deliverer_name'] 	= isset($aBuyDoc['deliverer_name']) 		? $aBuyDoc['deliverer_name'] 			: "";
			$aData['deliverer_address'] = isset($aBuyDoc['deliverer_address']) 		? $aBuyDoc['deliverer_address'] 		: "";
			$aData['deliverer_ein'] 	= isset($aBuyDoc['deliverer_ein']) 			? $aBuyDoc['deliverer_ein'] 			: "";
			$aData['deliverer_ein_dds'] = isset($aBuyDoc['deliverer_ein_dds']) 		? $aBuyDoc['deliverer_ein_dds'] 		: "";
			$aData['deliverer_mol'] 	= isset($aBuyDoc['deliverer_mol']) 			? $aBuyDoc['deliverer_mol'] 			: "";
			$aData['total_sum'] 		= isset($aBuyDoc['total_sum']) 				? $aBuyDoc['total_sum'] 				: 0;
			$aData['paid_type'] 		= isset($aBuyDoc['paid_type']) 				? $aBuyDoc['paid_type'] 				: "cash";
			
			if ( isset($aDDS[0]['payed']) ) {
				$aData['dds_payed'] 	= $aDDS[0]['payed'] == 1 ? true : false;
			} else {
				$aData['dds_payed'] 	= false;
			}
		} else {
			$aData['id'] 				= 0;
			$aData['doc_date'] 			= date("Y-m-d");
			$aData['doc_type'] 			= "faktura";
			$aData['paid_type'] 		= "cash";
			$aData['dds_sum'] 			= 0;
			$aData['dds_payed'] 		= false;
			$aData['dds_for_payment'] 	= true;
		}
		
		$aData['buy_doc_view'] 		= in_array('buy_doc_view', $_SESSION['userdata']['access_right_levels']) ? true : false;
		
		// При право за редакция - добавяме и право за преглед
		if ( in_array('buy_doc_edit', $_SESSION['userdata']['access_right_levels']) ) {
			$aData['buy_doc_view']	= true;
			$aData['buy_doc_edit'] 	= true;
		} else {
			$aData['buy_doc_edit'] 	= false;
		}
		
		// При пълно право за редакция - добавяме и право за преглед и редакция
		if ( in_array('buy_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
			$aData['buy_doc_view']	= true;
			$aData['buy_doc_edit'] 	= true;
			$aData['buy_doc_grant'] = true;
		} else {
			$aData['buy_doc_grant']	= false;
		}

		$aData['buy_doc_order_view'] = in_array('buy_doc_order_view', $_SESSION['userdata']['access_right_levels']) && $aData['buy_doc_view'] ? true : false;
		
		// При право за редакция - добавяме и право за преглед
		if ( in_array('buy_doc_order_edit', $_SESSION['userdata']['access_right_levels']) && $aData['buy_doc_view'] ) {
			$aData['buy_doc_order_view'] = true;
			$aData['buy_doc_order_edit'] = true;
		} else {
			$aData['buy_doc_order_edit'] = false;
		}
			
		if ( !$aData['buy_doc_view'] ) {
			$aData 						= array();
			
			$aData['id'] 				= 0;
			$aData['doc_date'] 			= date("Y-m-d");
			$aData['doc_type'] 			= "faktura";
			$aData['paid_type'] 		= "cash";
			$aData['dds_sum'] 			= 0;
			$aData['dds_payed'] 		= false;
			$aData['dds_for_payment'] 	= true;
						
			$aData['buy_doc_view']		 = false;
			$aData['buy_doc_edit'] 		 = false;
			$aData['buy_doc_grant'] 	 = false;	
			$aData['buy_doc_order_view'] = false;
			$aData['buy_doc_order_edit'] = false;
		}
		
		$aData['user_id'] 			= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
		$aData['user_name'] 		= isset($_SESSION['userdata']['name']) 		? $_SESSION['userdata']['name']  		: "";
		$aData['user_office_id'] 	= isset($_SESSION['userdata']['id_office']) ? $_SESSION['userdata']['id_office']  	: 0;
		$aData['user_office_name'] 	= isset($_SESSION['userdata']['region']) 	? $_SESSION['userdata']['region']  		: "";
		$aData['user_uname'] 		= isset($_SESSION['userdata']['username']) 	? $_SESSION['userdata']['username']  	: "";
		$aData['user_row_limit'] 	= isset($_SESSION['userdata']['row_limit']) ? $_SESSION['userdata']['row_limit']  	: 0;
		$aData['user_has_debug'] 	= isset($_SESSION['userdata']['has_debug']) ? $_SESSION['userdata']['has_debug']  	: 0;		
		//$oResponse->setAlert(ArrayToString($aData));			
		// Общи данни
		$oResponse->SetFlexVar("aData", $aData);
		
		// Получател
		$oResponse->SetFlexVar("arr_poluchateli", $aFirms);	
		
		// Типове разход
		$aEarnings 	= $oEarnings->getExpenses();
		$oResponse->SetFlexVar("nomenklatures", $aEarnings);	
		
		// Фирми и офиси
		$aFirms2 	= $oFirms->getFirmsByOffice();
		$oResponse->SetFlexVar("firm_regions", $aFirms2);	

		// Списък с банкови/касови сметки
		$aBankAcc	= $oBankAcc->getAllAccounts( 2, 0 );
		$oResponse->SetFlexVar("arr_smetki", $aBankAcc);	
		
		// Списък с направленията
		$aDirection	= $oDirection->getRegionDirections();
		$oResponse->SetFlexVar("arr_directions", $aDirection);	
		
		// Списък с описа
		foreach ( $aBuyDocRows as $key => $val ) {
			$aTemp = array();
			
			$aTemp['id'] 					= (string) $val['id'];
			$aTemp['nomenclature']['id'] 	= $val['id_nomenclature'];
			$aTemp['nomenclature']['code']	= $val['nomenclature_code'];
			$aTemp['nomenclature']['name'] 	= $val['nomenclature_name'];
			$aTemp['firm_region']['fcode'] 	= $val['id_firm'];
			$aTemp['firm_region']['firm'] 	= $val['firm_name'];
			$aTemp['firm_region']['rcode'] 	= $val['id_office'];
			$aTemp['firm_region']['region'] = $val['office_name'];
			$aTemp['direction']['id'] 		= $val['id_direction'];
			$aTemp['direction']['name'] 	= $val['direction_name'];
			$aTemp['month'] 				= $val['month'];
			$aTemp['sum'] 					= $val['sum'];
			//$aTemp['addRow'] 				= false;
			$aTemp['payed'] 				= $val['payed'] == 1 ? true : false;
			$aTemp['for_payment'] 			= true;
			
			$aRows[] 						= $aTemp;
 		}
		
		//return $aRows;
		$oResponse->SetFlexVar("arr_rows", $aRows);
		
		// Списък с ордерите
		$oResponse->SetFlexVar("arr_orders", $aOrders);
		
		// Стойности по подразбиране
		$oResponse->SetFlexControl("cbPoluchatel");
		
		if ( isset($nID) && isset($aBuyDoc['client_name']) && !empty($aBuyDoc['client_name']) ) {
			$oResponse->SetFlexControlDefaultValue("cbPoluchatel", "title", "---");
		} else {
			$oResponse->SetFlexControlDefaultValue("cbPoluchatel", "title", "ИНФРА ЕООД");
		}
		
		//$oResponse->setAlert("wsfwerfew");
		return $oResponse->toAMF();
	}
	
	public function save( $aParams ) {
		global $oResponse;
		
		$oBuyDocRows	= new DBBuyDocsRows();
		$oBuyDoc		= new DBBuyDocs();
		$oFirms 		= new DBFirms();
		
		$aFirms 		= array();
		$aorders		= array();

		// Права за достъп
		$edit_right 	= in_array('buy_doc_edit', $_SESSION['userdata']['access_right_levels']) ? true : false;
		$grant_right 	= false;
		

		if ( in_array('buy_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
			$edit_right		= true;
			$grant_right 	= true;
		}		
		
		$filename = "test.txt";
		
			
		
//		$content = ArrayToString( $aParams, 1 );
//		
//		if ( !$handle = fopen($filename, "w+") ) {
//			exit;
//		}		
//	
//		if ( fwrite($handle, $content) === FALSE ) {
//			exit;
//		}		
		

//		if ( empty($content) ) {
//			$oResponse->setAlert("Nqma nikoi tuka");
//		} else $oResponse->setAlert($content);

		
		$aData 	= array();
		$nID 	= isset($aParams['hiddenParams']['nID']) ? $aParams['hiddenParams']['nID'] : 0;	//"0"; 	// Твърдо
		
		//$oResponse->setAlert($nID);
		//throw new Exception( $nID, DBAPI_ERR_INVALID_PARAM );
		
		if ( !isset($aParams['doc_num']) || empty($aParams['doc_num']) ) {
			throw new Exception( "Въведете номер на документа!", DBAPI_ERR_INVALID_PARAM );
		}
		
		if ( !isset($aParams['cbPoluchatel']['name']) || !isset($aParams['cbPoluchatel']['idn']) || empty($aParams['cbPoluchatel']['name']) || empty($aParams['cbPoluchatel']['idn']) ) {
			throw new Exception( "Изберете валиден получател", DBAPI_ERR_INVALID_PARAM );
		}
		
		if ( !isset($aParams['deliverer_name']) || !isset($aParams['deliverer_ein']) || empty($aParams['deliverer_name']) || empty($aParams['deliverer_ein']) ) {
			throw new Exception( "Изберете валиден доставчик", DBAPI_ERR_INVALID_PARAM );
		}		

		if ( !isset($aParams['doc_type']) || empty($aParams['doc_type']) ) {
			throw new Exception( "Въведете вид на документа!", DBAPI_ERR_INVALID_PARAM );
		}
		

		if ( !isset($aParams['paid_type']) || empty($aParams['paid_type']) ) {
			throw new Exception( "Въведете начин на плащане!", DBAPI_ERR_INVALID_PARAM );
		}		
		
		if ( !isset($aParams['doc_date']) || empty($aParams['doc_date']) ) {
			throw new Exception( "Изберете дата на получаване на документа!", DBAPI_ERR_INVALID_PARAM );
		} else {
			$aTemp = array();
			$aTemp = explode("-", $aParams['doc_date']);
			
			if ( !checkdate($aTemp[1], $aTemp[2], $aTemp[0]) ) {
				throw new Exception( "Изберете валидна дата на получаване на документа!", DBAPI_ERR_INVALID_PARAM );
			}
			
			$tDate = mktime(0, 0, 0, $aTemp[1], $aTemp[2], $aTemp[0]);
		}
		
		
		if ( empty($nID) ) {
			$aData 						= array();	

			$aData['id'] 				= $nID;
			$aData['id_schet'] 			= 0;
			$aData['doc_num'] 			= $aParams['doc_num'];
			$aData['doc_date'] 			= $tDate;
			$aData['doc_type'] 			= $aParams['doc_type'];
			$aData['for_fuel'] 			= 0;
			$aData['for_gsm'] 			= 0;
			$aData['doc_status'] 		= "final";
			
			$aData['client_name'] 		= isset($aParams['cbPoluchatel']['name']) 	 ? $aParams['cbPoluchatel']['name'] 	: "";
			$aData['client_ein'] 		= isset($aParams['cbPoluchatel']['idn']) 	 ? $aParams['cbPoluchatel']['idn'] 		: "";
			$aData['client_ein_dds'] 	= isset($aParams['cbPoluchatel']['idn_dds']) ? $aParams['cbPoluchatel']['idn_dds']  : "";
			$aData['client_address'] 	= isset($aParams['cbPoluchatel']['address']) ? $aParams['cbPoluchatel']['address']  : "";
			$aData['client_mol'] 		= isset($aParams['cbPoluchatel']['jur_mol']) ? $aParams['cbPoluchatel']['jur_mol']  : "";
			$aData['client_recipient'] 	= isset($_SESSION['userdata']['name']) 		 ? $_SESSION['userdata']['name']  		: "";
			
			$aData['deliverer_name'] 	= isset($aParams['deliverer_name']) 		? $aParams['deliverer_name'] 			: "";
			$aData['deliverer_ein'] 	= isset($aParams['deliverer_ein']) 			? $aParams['deliverer_ein'] 			: "";
			$aData['deliverer_ein_dds'] = isset($aParams['deliverer_ein_dds']) 		? $aParams['deliverer_ein_dds'] 		: "";
			$aData['deliverer_address'] = isset($aParams['deliverer_address']) 		? $aParams['deliverer_address'] 		: "";
			$aData['deliverer_mol'] 	= isset($aParams['deliverer_mol']) 			? $aParams['deliverer_mol'] 			: "";
			
			$aData['total_sum'] 		= isset($aParams['eTotal']) 				? $aParams['eTotal'] 					: 0;
			$aData['orders_sum'] 		= 0;
			$aData['last_order_id'] 	= 0;
			$aData['last_order_time'] 	= "0000-00-00 00:00:00";
			$aData['paid_type'] 		= $aParams['paid_type'];
			$aData['view_type'] 		= "single";
			$aData['note'] 				= "";
			$aData['created_user'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
			$aData['created_time'] 		= time();
			$aData['updated_user'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
			$aData['updated_time'] 		= time();			
			
			$oBuyDoc->update($aData);
			
			$nID 		= $aData['id'];	
			$aDataRows 	= array();
			$nTotal		= 0;
			
			foreach ( $aParams['grid'] as $val ) {	
				if ( !empty($val['sum']) ) {
					$aDataRows['id'] 				= 0;
					$aDataRows['id_buy_doc'] 		= $nID;	
					$aDataRows['id_office'] 		= $val['firm_region']['rcode'];	
					$aDataRows['id_object'] 		= 0;
					$aDataRows['id_person'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
					$aDataRows['id_direction'] 		= $val['direction']['id'];	
					$aDataRows['id_nomenclature_expense'] = !empty($val['nomenclature']['id']) ? $val['nomenclature']['id'] : 0;	
					$aDataRows['id_salary_row'] 	= 0;	
					$aDataRows['id_order'] 			= 0;	
					$aDataRows['month'] 			= $val['month'] / 1000;	
					$aDataRows['quantity']			= 1;
					$aDataRows['measure']			= "бр.";
					$aDataRows['single_price']		= $val['sum'];
					$aDataRows['total_sum']			= $val['sum'];
					$aDataRows['paid_sum']			= 0;
					$aDataRows['paid_date'] 		= "0000-00-00 00:00:00";
					$aDataRows['is_dds']	 		= 0;
					$aDataRows['note'] 				= "";
					
					$oBuyDocRows->update($aDataRows);
					
					$nTotal 					+= $val['sum'];
				}
			}
			
			$aData = array();
			$aData['id']		= strval($nID);
			
			if ( $aParams['doc_type'] == "faktura" ) {
				$nIDN = isset($aParams['cbPoluchatel']['idn']) ? $aParams['cbPoluchatel']['idn'] : 0;
				
				$aFirms = $oFirms->getDDSFirmByEIN( $nIDN );
				
				$aDataRows['id'] 				= 0;
				$aDataRows['id_buy_doc'] 		= $nID;	
				$aDataRows['id_office'] 		= isset($aFirms['id']) ? $aFirms['id'] : 0;	
				$aDataRows['id_object'] 		= 0;
				$aDataRows['id_person'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
				$aDataRows['id_direction'] 		= 0;	
				$aDataRows['id_nomenclature_expense'] = 0;	
				$aDataRows['id_salary_row'] 	= 0;	
				$aDataRows['id_order'] 			= 0;	
				$aDataRows['month'] 			= $tDate;	
				$aDataRows['quantity']			= 1;
				$aDataRows['measure']			= "бр.";
				$aDataRows['single_price']		= $nTotal * 0.2;
				$aDataRows['total_sum']			= $nTotal * 0.2;
				$aDataRows['paid_sum']			= 0;
				$aDataRows['paid_date'] 		= "0000-00-00 00:00:00";
				$aDataRows['is_dds']	 		= 1;
				$aDataRows['note'] 				= "";
				
				$oBuyDocRows->update($aDataRows);	
				
				$aData['total_sum'] = $nTotal * 1.2;			
			} else {
				$aData['total_sum'] = $nTotal;
			}
			
			$oBuyDoc->update($aData);
			
		} else {		// РЕДАКЦИЯ
			// 1. Проверка за ордери към документ-а
			$aOrders = $oBuyDocRows->getOrdersByDoc( $nID );
			
			// ДДС
			$aDDS		= array();
			$aDDS 		= $oBuyDocRows->getDDSByDoc( $nID );	
			$nIDDDS 	= isset($aDDS[0]['id']) ? $aDDS[0]['id'] : 0;
			
			// Списък от ИД-тата
			$sIDs 		= "0";		
			
			// 1.1. Нямаме плащания и имаме право за промяна
			if ( empty($aOrders) ) {
				if ( !$edit_right ) {
					throw new Exception("Нямате право за редакция!!!", DBAPI_ERR_INVALID_PARAM);
				}
				
				$aData 						= array();	
	
				$aData['id'] 				= strval($nID);
				$aData['id_schet'] 			= 0;
				$aData['doc_num'] 			= $aParams['doc_num'];
				$aData['doc_date'] 			= $tDate;
				$aData['doc_type'] 			= $aParams['doc_type'];
				$aData['for_fuel'] 			= 0;
				$aData['for_gsm'] 			= 0;
				$aData['doc_status'] 		= "final";
				
				$aData['client_name'] 		= isset($aParams['cbPoluchatel']['name']) 	 ? $aParams['cbPoluchatel']['name'] 	: "";
				$aData['client_ein'] 		= isset($aParams['cbPoluchatel']['idn']) 	 ? $aParams['cbPoluchatel']['idn'] 		: "";
				$aData['client_ein_dds'] 	= isset($aParams['cbPoluchatel']['idn_dds']) ? $aParams['cbPoluchatel']['idn_dds']  : "";
				$aData['client_address'] 	= isset($aParams['cbPoluchatel']['address']) ? $aParams['cbPoluchatel']['address']  : "";
				$aData['client_mol'] 		= isset($aParams['cbPoluchatel']['jur_mol']) ? $aParams['cbPoluchatel']['jur_mol']  : "";
				$aData['client_recipient'] 	= isset($_SESSION['userdata']['name']) 		 ? $_SESSION['userdata']['name']  		: "";				
				
				$aData['deliverer_name'] 	= isset($aParams['deliverer_name']) 		? $aParams['deliverer_name'] 			: "";
				$aData['deliverer_ein'] 	= isset($aParams['deliverer_ein']) 			? $aParams['deliverer_ein'] 			: "";
				$aData['deliverer_ein_dds'] = isset($aParams['deliverer_ein_dds']) 		? $aParams['deliverer_ein_dds'] 		: "";
				$aData['deliverer_address'] = isset($aParams['deliverer_address']) 		? $aParams['deliverer_address'] 		: "";
				$aData['deliverer_mol'] 	= isset($aParams['deliverer_mol']) 			? $aParams['deliverer_mol'] 			: "";
				
				$aData['total_sum'] 		= isset($aParams['eTotal']) 				? $aParams['eTotal'] 					: 0;
				$aData['orders_sum'] 		= 0;
				$aData['last_order_id'] 	= 0;
				$aData['last_order_time'] 	= "0000-00-00 00:00:00";
				$aData['paid_type'] 		= $aParams['paid_type'];
				$aData['view_type'] 		= "single";
				$aData['note'] 				= "";
				$aData['created_user'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
				$aData['created_time'] 		= time();
				$aData['updated_user'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
				$aData['updated_time'] 		= time();			
				
				$oBuyDoc->update($aData);				
				//$oResponse->setAlert(ArrayToString($aData));		
				$aDataRows 	= array();
				$nTotal		= 0;
				
				foreach ( $aParams['grid'] as $val ) {	
					
					if ( !empty($val['sum']) ) {
						$aDataRows['id'] 				= isset($val['id']) ? $val['id'] : 0;
						$aDataRows['id_buy_doc'] 		= $nID;	
						$aDataRows['id_office'] 		= $val['firm_region']['rcode'];	
						$aDataRows['id_object'] 		= 0;
						$aDataRows['id_person'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
						$aDataRows['id_direction'] 		= $val['direction']['id'];	
						$aDataRows['id_nomenclature_expense'] = !empty($val['nomenclature']['id']) ? $val['nomenclature']['id'] : 0;	
						$aDataRows['id_salary_row'] 	= 0;	
						$aDataRows['id_order'] 			= 0;	
						$aDataRows['month'] 			= $val['month'] / 1000;	
						$aDataRows['quantity']			= 1;
						$aDataRows['measure']			= "бр.";
						$aDataRows['single_price']		= $val['sum'];
						$aDataRows['total_sum']			= $val['sum'];
						$aDataRows['paid_sum']			= 0;
						$aDataRows['paid_date'] 		= "0000-00-00 00:00:00";
						$aDataRows['is_dds']	 		= 0;
						$aDataRows['note'] 				= "";
						
						$oBuyDocRows->update($aDataRows);
						
						$nTotal += $val['sum'];
						$sIDs	.= ",".$aDataRows['id'];
					}
				}

				$aData 			= array();
				$aData['id']	= strval($nID);
				
				if ( $aParams['doc_type'] == "faktura" ) {
					$nIDN = isset($aParams['cbPoluchatel']['idn']) ? $aParams['cbPoluchatel']['idn'] : 0;
					
					$aFirms = $oFirms->getDDSFirmByEIN( $nIDN );
					
					$aDataRows['id'] 				= $nIDDDS;
					$aDataRows['id_buy_doc'] 		= $nID;	
					$aDataRows['id_office'] 		= isset($aFirms['id']) ? $aFirms['id'] : 0;	
					$aDataRows['id_object'] 		= 0;
					$aDataRows['id_person'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
					$aDataRows['id_direction'] 		= 0;	
					$aDataRows['id_nomenclature_expense'] = 0;	
					$aDataRows['id_salary_row'] 	= 0;	
					$aDataRows['id_order'] 			= 0;	
					$aDataRows['month'] 			= $tDate;	
					$aDataRows['quantity']			= 1;
					$aDataRows['measure']			= "бр.";
					$aDataRows['single_price']		= $nTotal * 0.2;
					$aDataRows['total_sum']			= $nTotal * 0.2;
					$aDataRows['paid_sum']			= 0;
					$aDataRows['paid_date'] 		= "0000-00-00 00:00:00";
					$aDataRows['is_dds']	 		= 1;
					$aDataRows['note'] 				= "";
					//
					$oBuyDocRows->update($aDataRows);	

					$aData['total_sum'] = $nTotal * 1.2;	
					$sIDs				.= ",".$aDataRows['id'];	
				} else {
					$aData['total_sum'] = $nTotal;
					
					$oBuyDocRows->delete($nIDDDS);
				}
				
				$oBuyDoc->update($aData);
				
				$oBuyDocRows->deleteRows( $nID, $sIDs );					
			} else {
				// Имаме ордери, но променяме!!!
				
				if ( !$grant_right ) {
					throw new Exception("Нямате право за редакция на платени документи!!!", DBAPI_ERR_INVALID_PARAM);
				}

				// 1. Тип на фактурата - особеност - ДДС! 
				if ( $nIDDDS > 0 ) {
					//$oResponse->setAlert(ArrayToString($aDDS[0]));
					$payed 		= isset($aDDS[0]['payed']) 	? $aDDS[0]['payed'] : 0;
					$nDDSSum 	=  isset($aDDS[0]['sum']) 	? $aDDS[0]['sum'] 	: 0;
					
					// Премахваме ДДС-то
					if ( $aParams['doc_type'] != "faktura" ) {
						$this->makeOrderFromDeleteRows( $nIDDDS );
					}
					
					$this->makeOrderFromChangedRows($aParams);
				}
				
						
			}
			
//			$filename = "orders.txt";
//			
//			$content = ArrayToString( $aOrders );
//			
//			if ( !$handle = fopen($filename, "w+") ) {
//				exit;
//			}		
//		
//			if ( fwrite($handle, $content) === FALSE ) {
//				exit;
//			}			
		}
		
		return $this->init( $nID );
	}

	public function isValidID( $nID ) {
		return preg_match("/^\d{13}$/", $nID);
	}
			
	public function delRows( $sRows ) {
		global $oResponse, $db_finance, $db_system, $db_name_finance, $db_name_system, $db_name_sod; 
		
		$nIDUser		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
		
		$aData 			= array();
		$aData 			= explode(",", $sRows);
		$sErrMessage	= "";
		$flag 			= true;
		
		$oOrders		= new DBOrders();
		$oBuyDocRows	= new DBBuyDocsRows();
		$oFirms			= new DBFirms();
		
		$db_finance->StartTrans();
		$db_system->StartTrans();			
		
		try {
			foreach ( $aData as $nID ) {
				$aBuyRows		= array();		
				
				if ( $this->isValidID($nID) ) {
					$sTableName  = PREFIX_BUY_DOCS_ROWS.substr( $nID, 0, 6 );
					$sBuyTable	 = PREFIX_BUY_DOCS.substr( $nID, 0, 6 );
				} else {
					//$sErrMessage .= "Невалидно ID на запис в описа!\n";
					continue;
				}	
				
				$oBuyDocRows->getRecord($nID, $aBuyRows);
				
				$nIDOrder 		= isset($aBuyRows['id_order'])		? $aBuyRows['id_order'] 		: 0;
				$nIDBuyDoc 		= isset($aBuyRows['id_buy_doc']) 	? $aBuyRows['id_buy_doc'] 		: 0;
				$nSum			= isset($aBuyRows['total_sum']) 	&& isset($aBuyRows['paid_sum']) && ($aBuyRows['total_sum'] == $aBuyRows['paid_sum']) ? $aBuyRows['total_sum'] : 0;
				$nTotalSum		= isset($aBuyRows['total_sum']) 	? $aBuyRows['total_sum'] 		: 0;
				$isDDS			= isset($aBuyRows['is_dds']) 		? $aBuyRows['is_dds'] 			: 0;
				$nIDOffice		= isset($aBuyRows['id_office']) 	? $aBuyRows['id_office'] 		: 0;
				
				$nIDFirm 		= $oFirms->getFirmByOffice($nIDOffice);
		
				if ( !empty($nIDOrder) && !empty($nSum) ) {
					// Имаме реализирано плащане за конкретния запис, създаваме обратено плащане на него!
					$aRow			= array();
					$aOrder			= array();
					
					$oOrders->getRecord($nIDOrder, $aRow);					
						
					$nIDAccount		= isset($aRow['bank_account_id']) 	? $aRow['bank_account_id'] 	: 0;
					$sOrderType		= isset($aRow['order_type']) 		? $aRow['order_type'] 		: "expense";
					$sAccType		= isset($aRow['account_type']) 		? $aRow['account_type'] 	: "cash";
					$sDocType		= isset($aRow['doc_type']) 			? $aRow['doc_type'] 		: "sale";
					
					// Пореден номер
					$oRes 			= $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
					$nLastOrder 	= !empty($oRes->fields['last_num_order']) ? $oRes->fields['last_num_order'] + 1 : 0;						
					
					// Наличност по сметка
					$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} FOR UPDATE");
					$nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;			
					
					// Салдо		
					$oRes 			= $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id_firm = {$nIDFirm} AND is_dds = {$isDDS} LIMIT 1 FOR UPDATE");
					$nSaldo		 	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;	
					
					if ( $nTotalSum > 0 ) {
						// Сумата е положителна, проверяваме за тип на документа
						if ( $sDocType == "buy" ) {
							// Типа на документа е разход. Прибавяме сумата към сметките!
							$sOrderType 	= "earning";
							$paid_account	= $nAccountState + $nSum;
							$nSaldo			= $nSaldo + $nSum;
						} else {
							// Типа е приход - вадим сумата от сметките (обратно действие)
							$sOrderType 	= "expense";
							$paid_account	= $nAccountState - $nSum;
							$nSaldo			= $nSaldo - $nSum;							
						}
					} else {
						// Сумата е отрицателна, връщаме стойностите!
						if ( $sDocType == "buy" ) {
							// Типа на документа е разход. Премахваме сумата към сметките!
							$sOrderType 	= "expense";
							$paid_account	= $nAccountState - $nSum;
							$nSaldo			= $nSaldo - $nSum;
						} else {
							// Типа е приход - прибавяме сумата от сметките (обратно действие)
							$sOrderType 	= "earning";
							$paid_account	= $nAccountState + $nSum;
							$nSaldo			= $nSaldo + $nSum;							
						}						
					}
					
					if ( $nAccountState < 0 ) {
						$sErrMessage .= "Недостатъчна наличност в сметката!\n";		
					}
					
					if ( $nSaldo < 0 ) {
						$sErrMessage .= "Недостатъчна наличност по салдо на фирмата!\n";		
					}	

					$aOrderData						= array();
					$aOrderData['id']				= 0;
					$aOrderData['num']				= $nLastOrder;
					$aOrderData['order_type'] 		= $sOrderType;
					$aOrderData['id_transfer']		= 0;
					$aOrderData['order_date']		= time();
					$aOrderData['order_sum']		= $nSum;
					$aOrderData['account_type']		= $sAccType;
					$aOrderData['id_person']		= $nIDUser;
					$aOrderData['account_sum']		= $paid_account;
					$aOrderData['bank_account_id']	= $nIDAccount;
					$aOrderData['doc_id']			= $nIDBuyDoc;
					$aOrderData['doc_type']			= $sDocType;
					$aOrderData['note']				= "";
					$aOrderData['created_user']		= $nIDUser;
					$aOrderData['created_time']		= time();
					$aOrderData['updated_user']		= $nIDUser;
					$aOrderData['updated_time']		= time();
					
					$oOrders->update($aOrderData);	
				}
				
				$oBuyDocRows->delete( $nID );
				
				$db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");
				
				$db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = '{$paid_account}' WHERE id_bank_account = {$nIDAccount} ");		
					
				$db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = '{$nSaldo}' WHERE id_firm = {$nIDFirm} AND is_dds = {$isDDS} LIMIT 1");						
				
			}
			
			if ( !empty($sErrMessage) ) {
				$oResponse->setAlert($sErrMessage);
				
				$db_finance->FailTrans();
				$db_system->FailTrans();	
				
				$flag = false;		
			}
				
			$db_finance->CompleteTrans();
			$db_system->CompleteTrans();			
		} catch (Exception $e) {
			//$oResponse->setAlert("Грешка при Задача!!!");
			$oResponse->setAlert(ArrayToString($sErrMessage));	
			
			$db_finance->FailTrans();
			$db_system->FailTrans();
			
			$flag = false;		
		}		
		
		return $flag;
	}
	
	
	
	
		public function addRow( $aRow ) {
		global $oResponse, $db_finance, $db_system, $db_name_finance, $db_name_system, $db_name_sod; 
		
		$nIDUser		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
		
//		$aData 			= array();
//		$aData 			= explode(",", $sRows);
		$sErrMessage	= "";
		$flag 			= true;
		
		$oOrders		= new DBOrders();
		$oBuyDocRows	= new DBBuyDocsRows();
		$oFirms			= new DBFirms();
		
		$db_finance->StartTrans();
		$db_system->StartTrans();			
		
		try {
			foreach ( $aRow as $val ) {
				$aBuyRows		= array();
				
				$nID			= isset($val['id'])						? $val['id'] 							: 0;
				$makeOrder		= isset($val['payed']) 					? $val['payed'] 						: 0;
				$nIDOffice		= isset($val['firm_region']['rcode']) 	? $val['firm_region']['rcode'] 			: 0;
				$nIDFirm		= isset($val['firm_region']['fcode']) 	? $val['firm_region']['fcode'] 			: 0;
				$nIDDirection	= isset($val['direction']['id']) 		? $val['direction']['id'] 				: 0;
				$nIDNomenclat	= isset($val['nomenclature']['id']) 	? $val['nomenclature']['id'] 			: 0;
				$month			= isset($val['month']) 					? date("Y-m-d", $val['month'] / 1000) 	: "0000-00-00";
				
				if ( $this->isValidID($nID) ) {
					$sTableName  = PREFIX_BUY_DOCS_ROWS.substr( $nID, 0, 6 );
					$sBuyTable	 = PREFIX_BUY_DOCS.substr( $nID, 0, 6 );
				} else {
					//$sErrMessage .= "Невалидно ID на запис в описа!\n";
					continue;
				}	
				
				$oBuyDocRows->getRecord($nID, $aBuyRows);
				
				$nIDOrder 		= isset($aBuyRows['id_order'])		? $aBuyRows['id_order'] 		: 0;
				$nIDBuyDoc 		= isset($aBuyRows['id_buy_doc']) 	? $aBuyRows['id_buy_doc'] 		: 0;
				$nSum			= isset($aBuyRows['total_sum']) 	&& isset($aBuyRows['paid_sum']) && ($aBuyRows['total_sum'] == $aBuyRows['paid_sum']) ? $aBuyRows['total_sum'] : 0;
				$nTotalSum		= isset($aBuyRows['total_sum']) 	? $aBuyRows['total_sum'] 		: 0;
				$isDDS			= isset($aBuyRows['is_dds']) 		? $aBuyRows['is_dds'] 			: 0;
				$nIDOffice		= isset($aBuyRows['id_office']) 	? $aBuyRows['id_office'] 		: 0;
				
				$nIDFirm 		= $oFirms->getFirmByOffice($nIDOffice);
		
				if ( !empty($nIDOrder) && !empty($nSum) ) {
					// Имаме реализирано плащане за конкретния запис, създаваме обратено плащане на него!
					$aRow			= array();
					$aOrder			= array();
					
					$oOrders->getRecord($nIDOrder, $aRow);					
						
					$nIDAccount		= isset($aRow['bank_account_id']) 	? $aRow['bank_account_id'] 	: 0;
					$sOrderType		= isset($aRow['order_type']) 		? $aRow['order_type'] 		: "expense";
					$sAccType		= isset($aRow['account_type']) 		? $aRow['account_type'] 	: "cash";
					$sDocType		= isset($aRow['doc_type']) 			? $aRow['doc_type'] 		: "sale";
					
					// Пореден номер
					$oRes 			= $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
					$nLastOrder 	= !empty($oRes->fields['last_num_order']) ? $oRes->fields['last_num_order'] + 1 : 0;						
					
					// Наличност по сметка
					$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} FOR UPDATE");
					$nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;			
					
					// Салдо		
					$oRes 			= $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id_firm = {$nIDFirm} AND is_dds = {$isDDS} LIMIT 1 FOR UPDATE");
					$nSaldo		 	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;	
					
					if ( $nTotalSum > 0 ) {
						// Сумата е положителна, проверяваме за тип на документа
						if ( $sDocType == "buy" ) {
							// Типа на документа е разход. Прибавяме сумата към сметките!
							$sOrderType 	= "earning";
							$paid_account	= $nAccountState + $nSum;
							$nSaldo			= $nSaldo + $nSum;
						} else {
							// Типа е приход - вадим сумата от сметките (обратно действие)
							$sOrderType 	= "expense";
							$paid_account	= $nAccountState - $nSum;
							$nSaldo			= $nSaldo - $nSum;							
						}
					} else {
						// Сумата е отрицателна, връщаме стойностите!
						if ( $sDocType == "buy" ) {
							// Типа на документа е разход. Премахваме сумата към сметките!
							$sOrderType 	= "expense";
							$paid_account	= $nAccountState - $nSum;
							$nSaldo			= $nSaldo - $nSum;
						} else {
							// Типа е приход - прибавяме сумата от сметките (обратно действие)
							$sOrderType 	= "earning";
							$paid_account	= $nAccountState + $nSum;
							$nSaldo			= $nSaldo + $nSum;							
						}						
					}
					
					if ( $nAccountState < 0 ) {
						$sErrMessage .= "Недостатъчна наличност в сметката!\n";		
					}
					
					if ( $nSaldo < 0 ) {
						$sErrMessage .= "Недостатъчна наличност по салдо на фирмата!\n";		
					}	

					$aOrderData						= array();
					$aOrderData['id']				= 0;
					$aOrderData['num']				= $nLastOrder;
					$aOrderData['order_type'] 		= $sOrderType;
					$aOrderData['id_transfer']		= 0;
					$aOrderData['order_date']		= time();
					$aOrderData['order_sum']		= $nSum;
					$aOrderData['account_type']		= $sAccType;
					$aOrderData['id_person']		= $nIDUser;
					$aOrderData['account_sum']		= $paid_account;
					$aOrderData['bank_account_id']	= $nIDAccount;
					$aOrderData['doc_id']			= $nIDBuyDoc;
					$aOrderData['doc_type']			= $sDocType;
					$aOrderData['note']				= "";
					$aOrderData['created_user']		= $nIDUser;
					$aOrderData['created_time']		= time();
					$aOrderData['updated_user']		= $nIDUser;
					$aOrderData['updated_time']		= time();
					
					$oOrders->update($aOrderData);	
				}
				
				$oBuyDocRows->delete( $nID );
				
				$db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");
				
				$db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = '{$paid_account}' WHERE id_bank_account = {$nIDAccount} ");		
					
				$db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = '{$nSaldo}' WHERE id_firm = {$nIDFirm} AND is_dds = {$isDDS} LIMIT 1");						
				
			}
			
			if ( !empty($sErrMessage) ) {
				$oResponse->setAlert($sErrMessage);
				
				$db_finance->FailTrans();
				$db_system->FailTrans();	
				
				$flag = false;		
			}
				
			$db_finance->CompleteTrans();
			$db_system->CompleteTrans();			
		} catch (Exception $e) {
			//$oResponse->setAlert("Грешка при Задача!!!");
			$oResponse->setAlert(ArrayToString($sErrMessage));	
			
			$db_finance->FailTrans();
			$db_system->FailTrans();
			
			$flag = false;		
		}		
		
		return $flag;
	}
	
	
	
	
	
	public function makeOrderFromChangedRows( $aParams ) {
		global $oResponse, $db_finance, $db_system, $db_name_finance, $db_name_system, $db_name_sod; 
		
		$nIDUser		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
		$nID 			= isset($aParams['hiddenParams']['nID']) ? $aParams['hiddenParams']['nID'] : 0;		
		
		$aData 			= array();
		$sErrMessage	= "";
		$flag 			= true;
		
		$oOrders		= new DBOrders();
		$oBuyDocRows	= new DBBuyDocsRows();
		$oBuyDoc		= new DBBuyDocs();
		$oFirms			= new DBFirms();

		$aBuyRows		= array();	
		$aFirms			= array();	
		
		$db_finance->StartTrans();
		$db_system->StartTrans();			
		
		try {
			foreach ( $aParams['grid'] as $val ) {	
				$nIDFirm 	= isset($val['firm_region']['fcode']) 	? $val['firm_region']['fcode'] 	: 0;
				$sFirmName	= isset($val['firm_region']['firm']) 	? $val['firm_region']['firm'] 	: "";
				
				$nIDRow		= isset($val['id']) 					? $val['id'] 					: 0;
						
				if ( !empty($nIDRow) && ($val['payed'] == 1) ) {
					$this->delRows($nIDRow);
					$this->addRow($val);
					$oResponse->setAlert(ArrayToString($val));
					
				}
				
//				if ( !empty($val['id']) && ($val['payed'] == 1) ) {
//					$aBuyRows	= array();
//					//$oResponse->setAlert(ArrayToString($val));	
//					$oBuyDocRows->getRecord($val['id'], $aBuyRows);
//					
//					
//					if ( isset($aBuyRows['id_order']) && !empty($aBuyRows['id_order']) ) {
//						$aOrder		= array();
//						$oOrders->getRecord($aBuyRows['id_order'], $aOrder);
//						//$oResponse->setAlert(ArrayToString($aOrder));	
//						
//						$nIDAccount = isset($aOrder['bank_account_id']) 	? $aOrder['bank_account_id'] 			: 0;
//						//$sOrderType = isset($aOrder['order_type']) 			? $aOrder['order_type'] 				: "";	
//						
//						$n1 = isset($aBuyRows['id_office']) 				? $aBuyRows['id_office'] 				: 0;	//1
//						$n2 = isset($aBuyRows['id_direction']) 				? $aBuyRows['id_direction'] 			: 0;	//112
//						$n3 = isset($aBuyRows['id_nomenclature_expense']) 	? $aBuyRows['id_nomenclature_expense'] 	: 0;	//39
//						$n4 = isset($aBuyRows['month']) 					? $aBuyRows['month'] 					: "0000-00-00";	//2009-04-01
//						$n5 = isset($aBuyRows['total_sum']) 				? floatval($aBuyRows['total_sum'])		: 0;	//10.0000
//						$n6 = $oFirms->getFirmByOffice($n1);
//						
//						$c1	= isset($val['firm_region']['rcode']) 			? $val['firm_region']['rcode'] 			: 0;	//1
//						$c2	= isset($val['direction']['id']) 				? $val['direction']['id'] 				: 0;	//112
//						$c3	= isset($val['nomenclature']['id']) 			? $val['nomenclature']['id'] 			: 0;	//39
//						$c4	= isset($val['month']) 							? $val['month'] / 1000					: "0000-00-00";	//2009-04-01
//						$c5	= isset($val['sum']) 							? floatval($val['sum'])					: 0;	//10
//						$c6	= isset($val['firm_region']['fcode']) 			? $val['firm_region']['fcode'] 			: 0;	//1
//						
//						//$oResponse->setAlert(ArrayToString($c4));	
//						// Фирмата не е променена - компенсираме разликите
//						if ( $n6 == $c6 ) {
//							// Сумите не са променени - няма да се създава нов ордер!!! Актуализация на текущия запис!
//							if ( $n5 == $c5 ) {
//								$aDataRows  					= array();
//								$aDataRows['id'] 				= $val['id'];
//								$aDataRows['id_office'] 		= $c1;	
//								$aDataRows['id_direction'] 		= $c2;	
//								$aDataRows['id_nomenclature_expense'] = $c3;	
//								$aDataRows['month'] 			= $c4;	
//							
//								$oBuyDocRows->update($aDataRows);									
//							} else {
//								// Сумите са променени - актуализация + ордер за компенсация за разликата
//								$oRes 			= $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
//								$nLastOrder 	= !empty($oRes->fields['last_num_order']) ? $oRes->fields['last_num_order'] + 1 : 0;
//								
//								// Наличност по сметка
//								$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} FOR UPDATE");
//								$nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;								
//								
//								// Наличност по фирма
//								$oRes 			= $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id_firm = {$c6} AND is_dds = 0 LIMIT 1 FOR UPDATE");
//								$nFirmSaldo 	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;	
//								
//								$nSum = $c5 - $n5;
//								
//								if ( $nSum > 0 ) {
//									$sType 			= "expense";
//									$paid_account 	= $nAccountState - $nSum;
//									$firm_saldo		= $nFirmSaldo - $nSum;
//								} else {
//									$sType 			= "earning";
//									$nSum 			= abs($nSum);
//									$paid_account 	= $nAccountState + $nSum;
//									$firm_saldo		= $nFirmSaldo + $nSum;
//								}	
//																
//								if ( $paid_account < 0 ) {
//									$sErrMessage = "Нямате достатъчно наличност по сметката!!!\n";
//								}	
//
//								if ( $firm_saldo < 0 ) {
//									$sErrMessage = "Нямате достатъчно наличност по фирма!!!\n";
//								}	
//								
//								$aData						= array();
//								$aData['id']				= 0;
//								$aData['num']				= $nLastOrder;
//								$aData['order_type'] 		= $sType;
//								$aData['id_transfer']		= 0;
//								$aData['order_date']		= time();
//								$aData['order_sum']			= $nSum;
//								$aData['account_type']		= isset($aParams['paid_type']) ? $aParams['paid_type'] : "cash";
//								$aData['id_person']			= $nIDUser;
//								$aData['account_sum']		= $paid_account;
//								$aData['bank_account_id']	= $nIDAccount;
//								$aData['doc_id']			= $nID;
//								$aData['doc_type']			= "buy";
//								$aData['note']				= "";
//								$aData['created_user']		= $nIDUser;
//								$aData['created_time']		= time();
//								$aData['updated_user']		= $nIDUser;
//								$aData['updated_time']		= time();
//					
//								$oOrders->update($aData);								
//								
//								$aDataRows  					= array();
//								$aDataRows['id'] 				= $val['id'];
//								$aDataRows['id_office'] 		= $c1;	
//								$aDataRows['id_direction'] 		= $c2;	
//								$aDataRows['id_order'] 			= $aData['id'];
//								$aDataRows['id_person'] 		= $nIDUser;	
//								$aDataRows['id_nomenclature_expense'] = $c3;	
//								$aDataRows['month'] 			= $c4;
//								$aDataRows['single_price'] 		= $c5;	
//								$aDataRows['total_sum'] 		= $c5;	
//								$aDataRows['paid_sum'] 			= $c5;
//								$aDataRows['paid_date'] 		= time();	
//							
//								$oBuyDocRows->update($aDataRows);	
//
//								$oRes = $db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");
//								
//								$oRes = $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = '{$paid_account}' WHERE id_bank_account = {$nIDAccount} ");								
//															
//								$oRes = $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = '{$firm_saldo}' WHERE id_firm = {$c6} AND is_dds = 0 LIMIT 1");
//							}
//							
//							
//						}
//					}
//				}
				
				//$oResponse->setAlert($val['id']);
//				if ( !empty($val['sum']) && $val['for_payment'] == 1 ) {		
//					$nPaidSum += $val['sum'];
//					
//					if ( isset($val['id']) ) {
//						$aIDs[]	= strval($val['id']);
//					}
//					
//					// Салда на фирмите
//					$oRes = $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id_firm = {$nIDFirm} AND is_dds = 0 LIMIT 1 FOR UPDATE");
//    				
//					if ( $oRes && !$oRes->EOF ) {
//						$nSaldo	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;	
//						$nSum	= isset($val['sum']) ? $val['sum'] : 0;
//						//$oResponse->setAlert($val['sum']);
//						if ( $nSaldo < $nSum ) {
//							$fall = true;
//						} else {
//							$oRes = $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum - '{$nSum}' WHERE id_firm = {$nIDFirm} AND is_dds = 0 LIMIT 1");
//						}
//					} else {
//						$db_finance->Execute("INSERT INTO {$db_name_finance}.saldo (id_firm, name, sum, is_dds) SELECT {$nIDFirm}, name, 0, 0 FROM {$db_name_sod}.firms WHERE id = {$nIDFirm} ");
//						$nSaldo = 0;
//						$fall	= true;
//					}
//				}
				
//				$nTotalSum += $val['sum'];
			}				
			
			
			
			
//			foreach ( $aData as $nID ) {
//				$oRes 			= $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
//				$nLastOrder 	= !empty($oRes->fields['last_num_order']) ? $oRes->fields['last_num_order'] + 1 : 0;			
//				
//				if ( $this->isValidID($nID) ) {
//					$sTableName  = PREFIX_BUY_DOCS_ROWS.substr( $nID, 0, 6 );
//					$sBuyTable	 = PREFIX_BUY_DOCS.substr( $nID, 0, 6 );
//				} else {
//					$sErrMessage .= "Невалидно ID на запис в описа!\n";
//				}	
//	
//				$sQuery = "
//					SELECT 
//						b.id_buy_doc, 
//						b.id_order, 
//						b.total_sum,
//						b.paid_sum,
//						b.id_office,
//						o.id_firm,
//						b.is_dds
//					FROM {$db_name_finance}.$sTableName b
//					LEFT JOIN {$db_name_sod}.offices o ON o.id = b.id_office
//					WHERE b.id = '{$nID}'
//					LIMIT 1
//				";
//					
//				$aRow 			= $db_finance->getArray($sQuery);
//				
//				$aOrdertables 	= SQL_get_tables( $db_finance, "orders_20", "____" );	// Всички налични периодични таблици за ордери
//				
//				$nIDOrder 		= isset($aRow[0]['id_order']) 	? $aRow[0]['id_order'] 		: 0;
//				$nIDBuyDoc 		= isset($aRow[0]['id_buy_doc']) ? $aRow[0]['id_buy_doc'] 	: 0;
//				$nSum			= isset($aRow[0]['total_sum']) && isset($aRow[0]['paid_sum']) && ($aRow[0]['total_sum'] == $aRow[0]['paid_sum']) ? $aRow[0]['total_sum'] : 0;
//				$isDDS			= isset($aRow[0]['is_dds']) 	? $aRow[0]['is_dds'] 		: 0;
//				$nIDFirm 		= isset($aRow[0]['id_firm']) 	? $aRow[0]['id_firm'] 		: 0;
//
//				$sOrderTable	= "";
//				
//				$db_finance->Execute("UPDATE {$db_name_finance}.{$sTableName} SET id_order = 0, paid_sum = 0, updated_user = '{$nIDUser}', updated_time = NOW() WHERE id = '{$nID}' LIMIT 1");
//				
//				$db_finance->Execute("UPDATE {$db_name_finance}.{$sBuyTable} SET orders_sum = orders_sum - '{$nSum}', updated_user = '{$nIDUser}', updated_time = NOW() WHERE id = '{$nIDBuyDoc}' LIMIT 1");				
//
//				// Попълваме масив с имена на периодични таблици според ID на ордерите
//				if ( in_array(PREFIX_ORDERS.substr($nIDOrder, 0, 6), $aOrdertables) ) {
//					$sOrderTable = PREFIX_ORDERS.substr($nIDOrder, 0, 6);
//				} else {
//					break;
//				}
//	
//				$sQuery = "
//					SELECT 
//						bank_account_id as id_account,
//						order_type,
//						doc_id as id_doc,
//						account_type,
//						doc_type
//					FROM {$db_name_finance}.$sOrderTable 
//					WHERE id = '{$nIDOrder}'
//						AND doc_type = 'buy'
//					LIMIT 1
//				";		
//
//				$aRow 			= $db_finance->getArray($sQuery);	
//				$nIDAccount		= isset($aRow[0]['id_account']) ? $aRow[0]['id_account'] : 0;
//				$sOrderType		= isset($aRow[0]['order_type']) ? $aRow[0]['order_type'] : "earning";
//				$sAccType		= isset($aRow[0]['account_type']) ? $aRow[0]['account_type'] : "cash";
//				$sDocType		= isset($aRow[0]['doc_type']) 	? $aRow[0]['doc_type'] : "sale";
//				
//				
//				// Наличност по сметка
//				$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} FOR UPDATE");
//				$nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;			
//				
//				// Салдо		
//				$oRes 			= $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id_firm = {$nIDFirm} AND is_dds = {$isDDS} LIMIT 1 FOR UPDATE");
//				$nSaldo		 	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;	
//				
//				// Приход - вадиме сумата
//				if ( $sOrderType == "earning" ) {
//					$sOrderType 	= "expense";
//					$paid_account	= $nAccountState - $nSum;
//					$nSaldo			= $nSaldo - $nSum;
//				} else {
//					$sOrderType 	= "earning";
//					$paid_account	= $nAccountState + $nSum;
//					$nSaldo			= $nSaldo + $nSum;
//				}
//				
//				if ( $nAccountState < 0 ) {
//					$sErrMessage .= "Недостатъчна наличност в сметката!\n";		
//				}
//				
//				if ( $nSaldo < 0 ) {
//					$sErrMessage .= "Недостатъчна наличност по салдо на фирмата!\n";		
//				}				
//				
//				if ( $nSum != 0 ) {
//					
//					$aOrderData 	= array();
//					$aOrderData['id']				= 0;
//					$aOrderData['num'] 				= $nLastOrder;
//					$aOrderData['order_type'] 		= $sOrderType;
//					$aOrderData['id_transfer'] 		= 0;
//					$aOrderData['order_date'] 		= time();
//					$aOrderData['order_sum'] 		= $nSum;
//					$aOrderData['account_type'] 	= $sAccType;
//					$aOrderData['id_person'] 		= $nIDUser;
//					$aOrderData['account_sum'] 		= $paid_account;
//					$aOrderData['bank_account_id'] 	= $nIDAccount;
//					$aOrderData['doc_id'] 			= $nIDBuyDoc;
//					$aOrderData['doc_type'] 		= $sDocType;
//					$aOrderData['note'] 			= "";
//					$aOrderData['created_user'] 	= $nIDUser;
//					$aOrderData['created_time'] 	= time();
//					$aOrderData['updated_user'] 	= $nIDUser;
//					$aOrderData['updated_time'] 	= time();	
//
//					$oOrders->update($aOrderData);	
//					//$oResponse->setAlert(ArrayToString($aOrderData));	
//					
//					$db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");
//				
//					$db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = '{$paid_account}' WHERE id_bank_account = {$nIDAccount} ");		
//					
//					$db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = '{$nSaldo}' WHERE id_firm = {$nIDFirm} AND is_dds = {$isDDS} LIMIT 1");		
//				}
				
				
//			}
			
			if ( !empty($sErrMessage) ) {
				$oResponse->setAlert($sErrMessage);
				
				$db_finance->FailTrans();
				$db_system->FailTrans();	
				
				$flag = false;		
			}
				
			$db_finance->CompleteTrans();
			$db_system->CompleteTrans();			
		} catch (Exception $e) {
			$oResponse->setAlert("Грешка при Задача!!!");
			
			$db_finance->FailTrans();
			$db_system->FailTrans();
			
			$flag = false;		
		}		
		
		return $this->init( $nID, $oResponse );	
	}	
	
	
	

 	public function makeOrder( $aParams ) {
		global $oResponse, $db_finance, $db_system, $db_name_finance, $db_name_system, $db_name_sod;
		
		$oBuyDocRows	= new DBBuyDocsRows();
		$oBuyDoc		= new DBBuyDocs();
		$oFirms 		= new DBFirms();
		$oOrders		= new DBOrders();
		
		$aFirms 		= array();
		$aOrders		= array();
		
		// Право за редакция
		$edit_right = in_array('buy_doc_order_edit', $_SESSION['userdata']['access_right_levels']) ? true : false;		
		
		$aData 	= array();
		$nID 	= isset($aParams['hiddenParams']['nID']) ? $aParams['hiddenParams']['nID'] : 0;		

		// ДДС
		$aDDS		= array();
		$aDDS 		= $oBuyDocRows->getDDSByDoc( $nID );	
		$nDDS 		= isset($aDDS[0]['id']) ? $aDDS[0]['id'] : 0;
		//$oResponse->setAlert("bla".$nDDS);
		$nTotalSum 	= 0;	
		$nPaidSum	= 0;
		$nDDSSum	= 0;
		
		$nIDAccount = isset($aParams['cbAccount']) ? $aParams['cbAccount'] : 0;
		
		$db_finance->StartTrans();
		//$db_sod->StartTrans();
		$db_system->StartTrans();	
		
		try {
			$oRes 			= $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
			$nLastOrder 	= !empty($oRes->fields['last_num_order']) ? $oRes->fields['last_num_order'] + 1 : 0;
			
			// Наличност по сметка
			$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} FOR UPDATE");
			$nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;
			
			//$oResponse->setAlert("state".$nIDAccount);
			
			$nDDSSum 		= 0;
			$fall			= false;
			$aIDs			= array();
			
			foreach ( $aParams['grid'] as $val ) {	
				$nIDFirm 	= isset($val['firm_region']['fcode']) ? $val['firm_region']['fcode'] : 0;
				$sFirmName	= isset($val['firm_region']['firm']) ? $val['firm_region']['firm'] : "";
				//$oResponse->setAlert($val['id']);
				if ( !empty($val['sum']) && $val['for_payment'] == 1 ) {		
					$nPaidSum += $val['sum'];
					
					if ( isset($val['id']) ) {
						$aIDs[]	= strval($val['id']);
					}
					
					// Салда на фирмите
					$oRes = $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id_firm = {$nIDFirm} AND is_dds = 0 LIMIT 1 FOR UPDATE");
    				
					if ( $oRes && !$oRes->EOF ) {
						$nSaldo	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;	
						$nSum	= isset($val['sum']) ? $val['sum'] : 0;
						//$oResponse->setAlert($val['sum']);
						if ( $nSaldo < $nSum ) {
							$fall = true;
						} else {
							$oRes = $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum - '{$nSum}' WHERE id_firm = {$nIDFirm} AND is_dds = 0 LIMIT 1");
						}
					} else {
						$db_finance->Execute("INSERT INTO {$db_name_finance}.saldo (id_firm, name, sum, is_dds) SELECT {$nIDFirm}, name, 0, 0 FROM {$db_name_sod}.firms WHERE id = {$nIDFirm} ");
						$nSaldo = 0;
						$fall	= true;
					}
				}
				
				$nTotalSum += $val['sum'];
			}	
			
			if ( $aParams['dds_for_payment'] == 1 ) {
				$nDDSSum 	= $nTotalSum * 0.2;
				
				$aIDs[]   	= $nDDS;
				
				$nIDN 		= isset($aParams['cbPoluchatel']['idn']) ? $aParams['cbPoluchatel']['idn'] : 0;
				$aFirms 	= $oFirms->getDDSFirmByEIN( $nIDN );
				$nIDFirm	= isset($aFirms['id']) ? $aFirms['id'] : 0;
					
				// Салда на фирмите
				$oRes = $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id_firm = {$nIDFirm} AND is_dds = 1 LIMIT 1 FOR UPDATE");
    				
				if ( $oRes && !$oRes->EOF ) {
					$nSaldo	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;	

					if ( $nSaldo < $nDDSSum ) {
						$fall = true;
					} else {
						$oRes = $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum - '{$nDDSSum}' WHERE id_firm = {$nIDFirm} AND is_dds = 1 LIMIT 1");
					}
				} else {
					$db_finance->Execute("INSERT INTO {$db_name_finance}.saldo (id_firm, name, sum, is_dds) SELECT {$nIDFirm}, CONCAT(name, ' ДДС'), 0, 1 FROM {$db_name_sod}.firms WHERE id = {$nIDFirm} ");
					$nSaldo = 0;
					$fall	= true;
				}							
			}
			
			if ( $nPaidSum > $nAccountState ) {
				//$oResponse->setAlert($nPaidSum."-".$nAccountState);
				throw new Exception("Нямате достатъчно наличност по сметката!!!", DBAPI_ERR_INVALID_PARAM);
			}			
			
			$paid_account				= $nAccountState - ($nPaidSum + $nDDSSum);
			$sum_dds					= $nPaidSum + $nDDSSum;
			
			$aData['id']				= 0;
			$aData['num']				= $nLastOrder;
			$aData['order_type'] 		= "expense";
			$aData['id_transfer']		= 0;
			$aData['order_date']		= time();
			$aData['order_sum']			= $sum_dds;
			$aData['account_type']		= isset($aParams['paid_type']) ? $aParams['paid_type'] : "cash";
			$aData['id_person']			= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			$aData['account_sum']		= $paid_account;
			$aData['bank_account_id']	= isset($aParams['cbAccount']) ? $aParams['cbAccount'] : 0;
			$aData['doc_id']			= $nID;
			$aData['doc_type']			= "buy";
			$aData['note']				= "";
			$aData['created_user']		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			$aData['created_time']		= time();
			$aData['updated_user']		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			$aData['updated_time']		= time();

			$oOrders->update($aData);
			
			$nIDOrder 		= $aData['id'];
			$sBuyName 		= "buy_docs_".substr($nID, 0, 6);
			$sBuyRowsName 	= "buy_docs_rows_".substr($nID, 0, 6);
			//$oResponse->setAlert(ArrayToString($aIDs));
			
			$oRes = $db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");
			
			$oRes = $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = '{$paid_account}' WHERE id_bank_account = {$nIDAccount} ");
			$sIDs = !empty($aIDs) ? implode(",", $aIDs) : -1;
			
			//$oResponse->setAlert(ArrayToString($sIDs));
			$oRes = $db_finance->Execute("UPDATE {$db_name_finance}.{$sBuyRowsName} SET id_order = '{$nIDOrder}', paid_sum = total_sum WHERE id IN ({$sIDs})");
			
			$oRes = $db_finance->Execute("UPDATE {$db_name_finance}.{$sBuyName} SET last_order_id = '{$nIDOrder}', orders_sum = orders_sum + '{$sum_dds}' WHERE id = '{$nID}'");
			//$oResponse->setAlert($sIDs);
			//$oResponse->setAlert("UPDATE {$db_name_finance}.{$sBuyName} SET id_order = '{$nIDOrder}' WHERE id IN ({$sIDs})");
			
			if ( $fall ) {
				$oResponse->setAlert("Нямате достатъчно наличност по фирма!!!");
				
				$db_finance->FailTrans();
				//$db_sod->FailTrans();
				$db_system->FailTrans();				
			}			
			
			$db_finance->CompleteTrans();
			//$db_sod->CompleteTrans();
			$db_system->CompleteTrans();			
		} catch (Exception $e) {
			throw $e;
			//$oResponse->setAlert($e);
			$db_finance->FailTrans();
			//$db_sod->FailTrans();
			$db_system->FailTrans();
		}
		
		// дебъг информация
		$filename = "orders.txt";
			
//		$content = ArrayToString( $aParams );
//			
//		if ( !$handle = fopen($filename, "w+") ) {
//			exit;
//		}		
//		
//		if ( fwrite($handle, $content) === FALSE ) {
//			exit;
//		}	
				
		return $this->init( $nID, $oResponse );	
 	}
	
	public function suggest($field, $info) {
  		global $oResponse, $db_sod, $db_name_sod;
  		
  		//$oResponse->setAlert(ArrayToString($oResponse));
  		
//  		if ( utf8_strlen($info) < 3 ) {
//  			//die();
//  		}
  		
  		$arr_objects = array();
  		
  		$sQuery = "
  			SELECT 
  				id,
  				name,
  				address,
  				invoice_ein,
  				invoice_ein_dds,
  				invoice_mol
  			FROM {$db_name_sod}.clients
  			WHERE UPPER({$field}) LIKE UPPER('%$info%')
  			LIMIT 10
  		";
  		
  		$arr_objects = $db_sod->getArray( $sQuery );
  		
  		$oResponse->SetFlexVar("arr_clients", $arr_objects);

  		return $oResponse->toAMF();
 }
	//----------------------------------------------
//	private function SetFlexVar($sName, $oVar) {
//		$var = new FlexVar();
//		$var->name = $sName;
//		$var->value = $oVar;
//		$this->aFlexVars[$sName] = $var;
//	}
//	private function SetFlexControl($sName, $aAttributes = array(), $aMethods = array()) {
//		$control = new FlexControl();
//		$control->name = $sName;
//		$control->attributes = $aAttributes;
//		//$control->methods = $aMethods;
//		$this->aFlexControls[$sName] = $control;
//	}
//	private function SetFlexControlAttr($sName, $sAttrName, $oAttrValue) {
//		if (array_key_exists($sName, $this->aFlexControls)) {
//			$this->aFlexControls[$sName]->attributes[] = array('name'=>$sAttrName, 'value'=>$oAttrValue);
//		}
//	}
//	private function SetFlexControlMethod($sName, $sMethodName) {
//	}
//	private function SetFlexControlDefaultValue($sName, $sDefaultField, $oValue) {
//		if (array_key_exists($sName, $this->aFlexControls)) {
//			$this->aFlexControls[$sName]->defaultField = $sDefaultField;
//			$this->aFlexControls[$sName]->defaultValue = $oValue;
//		}
//	}
//	private function SetHiddenParam($sName, $oParam) {
//		$this->oHiddenParams[$sName] = $oParam;
//	}
//	private function toAMF() {
//		$res = new FlexResponse();
//		foreach($this->aFlexVars as $value) {
//			$res->variables[] = $value;
//		}
//		foreach($this->aFlexControls as $value) {
//			$res->controls[] = $value;
//		}
//		$res->hiddenParams = $this->oHiddenParams;
//
//		return $res;
//	}
}

?>
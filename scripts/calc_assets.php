<?php
	if ( !isset($_SESSION) ) {
		session_start();
	}
	
	if ( isset($_SERVER['SCRIPT_FILENAME']) && !empty($_SERVER['SCRIPT_FILENAME']) ) {
		$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
		set_include_path( get_include_path().PATH_SEPARATOR.'/usr/local/apache2/htdocs/telenet/'.PATH_SEPARATOR.$aPath['dirname'].'/../' );
		//print(get_include_path().PATH_SEPARATOR.'/usr/local/apache2/htdocs/telenet/'.PATH_SEPARATOR.$aPath['dirname'].'/../');
	} else {
		//print(get_include_path().PATH_SEPARATOR.'/usr/local/apache2/htdocs/telenet/' );
		set_include_path( get_include_path().PATH_SEPARATOR.'/usr/local/apache2/htdocs/telenet/' );
	}
		
	require_once ("../config/function.autoload.php");
	require_once ("../include/adodb/adodb-exceptions.inc.php");
	require_once ("../config/connect.inc.php");
	require_once ("../include/general.inc.php");
	
	$oAssets = new DBAssets();
	$assets = array();
	
	$assets = $oAssets->getSingleAssets();
	//APILog::Log(0, $assets);
	$total = array();
	
	foreach ( $assets as $key => $val ) {
		//debug($val);
		if ( $val['storage_type'] == "person" ) {
			$office = $oAssets->getOfficeByPerson( $key );
		} elseif ( $val['storage_type'] == "storagehouse" ) {
			$office = $oAssets->getOfficeByStoragehouse( $key );
		}

		$nIDOffice = isset($office[0]['id_office']) ? $office[0]['id_office'] : 0;
		$nPrice = $oAssets->getPrice( $key );

		if ( $val['status'] == "wasted" ) {  // brakuvani 
			$total[$nIDOffice]['wasted'] = isset($total[$nIDOffice]['wasted']) ? $total[$nIDOffice]['wasted'] + $nPrice : $nPrice;
		}
		
		if ( $val['status'] == "entered" ) {  // fsdas 
			$total[$nIDOffice]['entered'] = isset($total[$nIDOffice]['entered']) ? $total[$nIDOffice]['entered'] + $nPrice : $nPrice;
		}
		
		if ( $val['status'] == "attached" ) {  // privyrzani 
			$total[$nIDOffice]['attached'] = isset($total[$nIDOffice]['attached']) ? $total[$nIDOffice]['attached'] + $nPrice : $nPrice;
		}		
	}
	
	//debug($total);
	$tmpArr = array();
	
	foreach ( $total as $k => $v ) {
		$tmpArr['id_office'] = $k;
		$tmpArr['date_in'] = time();
		$tmpArr['sum_wasted'] = isset($v['wasted']) ? $v['wasted'] : 0;	
		$tmpArr['sum_entered'] = isset($v['entered']) ? $v['entered'] : 0;	
		$tmpArr['sum_attached'] = isset($v['attached']) ? $v['attached'] : 0;	
		
		$check = $oAssets->checkAssets( $tmpArr );
		
		if ( $check === false ) {
			$sql = "SELECT * FROM assets_totals WHERE id = -1";
			$rs = $db_storage->Execute($sql); 
			
			$insertSQL = $db_storage->GetInsertSQL($rs, $tmpArr); 
			$db_storage->Execute($insertSQL);
		} elseif ( is_numeric($check) ) {
			$sql = "SELECT * FROM assets_totals WHERE id = {$check}";
			$rs = $db_storage->Execute($sql); 
			
			$tmpArr['id'] = $check;
			$updateSQL = $db_storage->GetUpdateSQL($rs, $tmpArr); 
			$db_storage->Execute($updateSQL);
			//debug($updateSQL);
		}
		 
	}
	
?>
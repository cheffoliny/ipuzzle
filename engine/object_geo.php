<?php
	global $template;
	
	$nID    = isset($_GET['nID']) ? (int) $_GET['nID'] : 0;
	$mobile = !empty($_GET['mobile']) ? $_GET['mobile'] : 0;
	$aObject = array();
	$edit = array();
	$view = array();
	
	if(!empty($nID)) {
		$oDBObjects = new DBObjects();
		
		$aObject = $oDBObjects->getRecord($nID);
	}

	$isSOD  = isset($aObject['is_sod']) ? $aObject['is_sod'] : 0;
	$isFO   = isset($aObject['is_fo']) ? $aObject['is_fo'] : 0;

	$isValidGeo = static function ($lat, $lng) {
		return is_numeric($lat)
			&& is_numeric($lng)
			&& (float) $lat >= -90
			&& (float) $lat <= 90
			&& (float) $lng >= -180
			&& (float) $lng <= 180
			&& ((float) $lat != 0.0 || (float) $lng != 0.0);
	};

	// Geographic centre of Bulgaria. It is used only when the object, its city
	// and its office do not have usable coordinates yet.
	$mapCenter = array('lat' => 42.7339, 'lng' => 25.4858, 'zoom' => 7);
	$hasMapCenter = false;

	if ($isValidGeo($aObject['geo_lat'] ?? null, $aObject['geo_lan'] ?? null)) {
		$mapCenter = array(
			'lat' => (float) $aObject['geo_lat'],
			'lng' => (float) $aObject['geo_lan'],
			'zoom' => 14,
		);
		$hasMapCenter = true;
	}

	if (!$hasMapCenter && !empty($aObject['address_city'])) {
		$oCities = new DBCities();
		$aCity = $oCities->getRecord((int) $aObject['address_city']);

		if ($isValidGeo($aCity['geo_lat'] ?? null, $aCity['geo_lan'] ?? null)) {
			$mapCenter = array(
				'lat' => (float) $aCity['geo_lat'],
				'lng' => (float) $aCity['geo_lan'],
				'zoom' => 14,
			);
			$hasMapCenter = true;
		}
	}

	if (!$hasMapCenter && !empty($aObject['id_office'])) {
		$oOffices = new DBOffices();
		$aOffice = $oOffices->getRecord((int) $aObject['id_office']);

		if ($isValidGeo($aOffice['geo_lat'] ?? null, $aOffice['geo_lan'] ?? null)) {
			$mapCenter = array(
				'lat' => (float) $aOffice['geo_lat'],
				'lng' => (float) $aOffice['geo_lan'],
				'zoom' => 14,
			);
		}
	}
	
    $pov    = isset($aObject['geo_pov']) && !empty($aObject['geo_pov']) ? $aObject['geo_pov'] : json_encode(array());
    $pov    = json_encode(json_decode($pov, true));
    //var_dump($pov);
    //	$pov    = json_encode(json_decode($pov));
	//var_dump(json_encode(json_decode($pov)));
	require_once('engine/object_tabs_rights.php');

	$template->assign("edit", $edit);
	$template->assign("view", $view);
	$template->assign("cnt", count($view));	
	$template->assign("mobile", $mobile);

    $template->assign("nID", $nID);

	$template->assign( "object",    $aObject['object'] ?? '' );
	$template->assign( "num",       $aObject['num'] ?? '' );

	$template->assign('pov',$pov);
	$template->assign('aObject',$aObject);
	$template->assign('mapCenter', $mapCenter);
	/* added by Me 25.09.2013 */
	$template->assign( "isSOD", $isSOD );
	$template->assign( "isFO", $isFO );
?>

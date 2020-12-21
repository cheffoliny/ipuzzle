<?php
	//debug($_GET);
	$tmpArr = array();
	$assets = array();
	$search = isset($_GET['search']) ? $_GET['search'] : 0;
	$max = 0; $min = 0;
	$max2 = 0; $min2 = 0;
	$max3 = 0; $min3 = 0;
	
	$tmpArr['nIDFirm'] = isset($_GET['firm']) ? $_GET['firm'] : 0;
	$tmpArr['period'] = isset($_GET['period']) ? $_GET['period'] : 0;

	if ( $search ) {
		$tmpArr2 = array();
		$oAssets = new DBAssets();
		$tmpArr2 = $oAssets->getAssetTotals( $tmpArr );
		$wasted = array();
		$entered = array();
		$attached = array();
		$offset = array();
		$offset2 = array();
		$offset3 = array();
		
		foreach ( $tmpArr2 as $key => $val ) {
			$mon = isset($val['date_in']) && !empty($val['date_in']) ? substr($val['date_in'], 0, 2) : 0;
			$ye = isset($val['date_in']) && !empty($val['date_in']) ? substr($val['date_in'], -4) : 0;
			$date = isset($mname[$mon]) ? $mname[$mon]." ".$ye : "";
			
			if ( isset($wasted[$val['id_firm']]) ) {
				//echo "firm: ".$val['firm']." => sum: ".$val['sum_wasted']." => offset: ".$offset[$val['id_firm']]."<br>";
				$wasted[$val['id_firm']] = $val['sum_wasted'] - $offset[$val['id_firm']];
				$offset[$val['id_firm']] = $val['sum_wasted'];
				
				if ( $max < $wasted[$val['id_firm']] ) $max = $wasted[$val['id_firm']];
				if ( $min > $wasted[$val['id_firm']] ) $min = $wasted[$val['id_firm']];
			} else {
				$offset[$val['id_firm']] = $val['sum_wasted'];				
				$wasted[$val['id_firm']] = 0;
			}

			if ( isset($entered[$val['id_firm']]) ) {
				$entered[$val['id_firm']] = $val['sum_entered'] - $offset2[$val['id_firm']];
				$offset2[$val['id_firm']] = $val['sum_entered'];

				if ( $max2 < $entered[$val['id_firm']] ) $max2 = $entered[$val['id_firm']];
				if ( $min2 > $entered[$val['id_firm']] ) $min2 = $entered[$val['id_firm']];
			} else {
				$offset2[$val['id_firm']] = $val['sum_entered'];
				$entered[$val['id_firm']] = 0;
			}

			if ( isset($attached[$val['id_firm']]) ) {
				$attached[$val['id_firm']] = $val['sum_attached'] - $offset3[$val['id_firm']];
				$offset3[$val['id_firm']] = $val['sum_attached'];

				if ( $max3 < $attached[$val['id_firm']] ) $max3 = $attached[$val['id_firm']];
				if ( $min3 > $attached[$val['id_firm']] ) $min3 = $attached[$val['id_firm']];
			} else {
				$offset3[$val['id_firm']] = $val['sum_attached'];
				$attached[$val['id_firm']] = 0;
			}

			$val['date'] = $date;
			$val['wasted'] = $wasted[$val['id_firm']];
			$val['entered'] = $entered[$val['id_firm']];
			$val['attached'] = $attached[$val['id_firm']];
			
			$assets[$val['id_firm']][] = $val;
		}
		//debug($assets);
	}

	$max = $max >= abs($min) ? $max : abs($min);
	$max2 = $max2 >= abs($min2) ? $max2 : abs($min2);
	$max3 = $max3 >= abs($min3) ? $max3 : abs($min3);
	//echo $max." ".$max2;
	$template->assign('search', $tmpArr);
	$template->assign('assets', $assets);
	$template->assign('max', $max);
	$template->assign('max2', $max2);
	$template->assign('max3', $max3);
?>
<?php

    $oDBTechTiming = new DBTechTiming();

    $nIDRequest = isset($_GET['id_request']) ? $_GET['id_request'] : '0';
    $right_edit = false;

    if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
        if ( in_array( 'tech_planning_edit', $_SESSION['userdata']['access_right_levels']) ) {
            $right_edit = true;
        }
    }

    $aTechTiming = $oDBTechTiming->getAll();

    if(!empty($nIDRequest)) {
		
		$oDBTechRequests = new DBTechRequests();
		$oDBContracts = new DBContracts();
		$oDBTechTiming = new DBTechTiming();
		
		$aRequest = $oDBTechRequests->getRecord($nIDRequest);
		
		if($aRequest['tech_type'] == 'contract') {
		
			$aContract = $oDBContracts->getRecord($aRequest['id_contract']);
			
			$aTechTiming = $oDBTechTiming->getInfoByName('create');
		
			$nMinute = $aTechTiming['minute'] + ($aContract['count_detectors'] - 1)*$aTechTiming['step_detector'];
			
			if( $aContract['obj_distance'] > 15 ) {
				$nMinute += round(($aContract['obj_distance'] - 15) / 30) * 30;
			}
			$nPicNum = $nMinute/30;
			
			if( $nPicNum > 16 ) $nPicNum = 16;
			
			$nHours = floor($nMinute/60);
			$nMinute = $nMinute % ($nHours*60);
			
			$template->assign('nHours',$nHours);
			$template->assign('nMinute',$nMinute);
			$template->assign('nPicNum',$nPicNum);

        } else {

            $oDBObjects = new DBObjects();


            $sTechTimingName = $oDBTechTiming->getType((int)$aRequest['id_tech_timing'],1);
            // за да взема типа и ако е изграждане да не търси обекта

            if($sTechTimingName != 'create')
            {
                // ако заявката е за изграждане да не търси обект защото при изграждане обект не се подава
                $aObject = $oDBObjects->getRecord($aRequest['id_object']);
            }

            $sTechType = '';
            switch ($aRequest['type']) {
                case 'create': $sTechType = 'Изграждане';break;
                case 'destroy': $sTechType = 'Сваляне';break;
                case 'holdup': $sTechType = 'Профилактика';break;
                case 'arrange': $sTechType = 'Аранжиране';break;
            }

            $template->assign('sObjectName',$aObject['name']);
            $template->assign('sTechType',$sTechType);
        }
    }

$template->assign('nIDRequest',	$nIDRequest);
$template->assign('right_edit', $right_edit);
$template->assign('aTechTiming', $aTechTiming);
?>
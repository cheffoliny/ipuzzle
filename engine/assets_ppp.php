<?php
	$nID = !empty( $_GET['id'] ) ? $_GET['id'] : 0;
	
	if(!empty($nID)) {
		
		$oDBAssetsPPPs = new DBAssetsPPPs();
		
		$aPPP = $oDBAssetsPPPs -> getRecord($nID);
		
		$sPPPTypeName = '';
		switch ($aPPP['ppp_type']) {
			case 'enter': $sPPPTypeName = 'Придобиване';break;
			case 'attach': $sPPPTypeName = 'Въвеждане';break;
			case 'waste': $sPPPTypeName = 'Бракуване';break;
		}
		
		$sNum = zero_padding($nID).'/'.date('d-m-Y',strtotime($aPPP['created_time'])).' '.$sPPPTypeName;
		
		$template->assign("sPPPType",$aPPP['ppp_type']);
		$template->assign("nIDConfirmUser",$aPPP['confirm_user']);
		$template->assign("sNum",$sNum);
	} else {
		$sPPPType = !empty( $_GET['type']) ? $_GET['type'] : 0;
		$template->assign("sPPPType",$sPPPType);
	}
	
	$template->assign("nID", $nID);
?>
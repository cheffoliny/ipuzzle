<?php
	//$nID = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
	
	$nID = isset($_GET['nID']) ? $_GET['nID'] : $_SESSION['userdata']['id_person'];
	$nIDLimitCard = isset($_GET['id_limit_card']) ? $_GET['id_limit_card'] : '0';
	
	$template->assign("nID", $nID);	
	$template->assign('nIDLimitCard',$nIDLimitCard);
?>
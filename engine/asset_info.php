<?php
    // Проверка на правата за достъп
	$right_edit 		= false; // Право на редактиране на информацията за служител
	$right_view			= false; // Право за показване на служителите
	$right_edit_change	= false; // Право за редактиране на амортизационен период чрез бутона Промени
	
	if (!empty($_SESSION['userdata']['access_right_levels'] )) {
		if ( in_array('asset_info_edit', $_SESSION['userdata']['access_right_levels']) ) {
			$right_view = true;
			$right_edit = true;
		}
        if (in_array('asset_info_view', $_SESSION['userdata']['access_right_levels'])) 
			$right_view = true;		
			
		if (in_array('set_asset_info', $_SESSION['userdata']['access_right_levels'])) {
			$right_edit_change = true;
		} 
		 else $right_edit_change	= false;
	}
	
	$nID = isset( $_GET['id'] )? $_GET['id'] : $_GET['nID'];
	
	$template->assign("nID",$nID);
//	throw new Exception($nID);
	$template->assign("right_edit", $right_edit);
	$template->assign("right_view", $right_view);
	$template->assign("right_edit_change", $right_edit_change);
?>
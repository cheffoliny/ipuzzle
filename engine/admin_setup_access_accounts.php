<?php
//	$template -> assign("def_office", $_SESSION["userdata"]["id_office"]);

	$right_edit = false;
	if (!empty($_SESSION['userdata']['access_right_levels']))
		if (in_array('access_levels_edit', $_SESSION['userdata']['access_right_levels']))
		{
			$right_edit = true;
		}

	$id_profile = isset( $_GET['id_profile'] ) && $_GET['id_profile'] ? $_GET['id_profile'] : 0;
	$template->assign( "id_profile", $id_profile );
	$template->assign( 'right_edit', $right_edit );

?>
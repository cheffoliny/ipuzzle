<?php
	$nID = !empty( $_GET['id'] ) ? $_GET['id'] : 0;
	$id_obj = !empty( $_GET['id_obj'] ) ? $_GET['id_obj'] : 0;
	$template->assign("nID", $nID);
	$template->assign("id_obj", $id_obj);
?>
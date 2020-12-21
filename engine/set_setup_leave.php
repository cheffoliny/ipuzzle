<?php
	$id_person = ! empty( $_GET['person'] ) ? $_GET['person'] : 0;
	$id = ! empty( $_GET['id'] ) ? $_GET['id'] : 0;
	$nDate = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
	
	$template->assign("year", date('Y', $nDate) );
	$template->assign('id_person', $id_person );
	$template->assign('id', $id );
?>
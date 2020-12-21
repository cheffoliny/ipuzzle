<?php
	$id_person = ! empty( $_GET['person'] ) ? $_GET['person'] : 0;
	$id = ! empty( $_GET['id'] ) ? $_GET['id'] : 0;

	$template->assign('id_person', $id_person );
	$template->assign('id', $id );
?>
<?
	$id = isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : 0;
	$template->assign("id",$id);
?>
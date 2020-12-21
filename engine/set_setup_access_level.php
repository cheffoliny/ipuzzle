<?
	$id = isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : 0;
	$group = isset($_GET['group']) && $_GET['group'] ? $_GET['group'] : 0;

	$template->assign("id",$id);
	$template->assign("group",$group);
?>
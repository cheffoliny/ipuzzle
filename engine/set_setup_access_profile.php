<?php

	$oAccess = new DBAccess();
	$oAccess->getLevelGroups($level_groups);
	$oAccess->getLevels($levels);

	$id = 		isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : 0;
	$selall = 	isset($_GET['selall']) && $_GET['selall'] ? $_GET['selall'] : 0;

	$template->assign("levels",$levels);
	$template->assign("level_groups",$level_groups);
	$template->assign("id",$id);
	$template->assign("selall", $selall);
?>
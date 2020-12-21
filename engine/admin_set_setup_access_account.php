<?php
	$id = isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : 0;
	$selall = isset($_GET['selall']) && $_GET['selall'] ? $_GET['selall'] : 0;
	
	$template -> assign("id", $id);
	$template -> assign("selall", $selall);
?>
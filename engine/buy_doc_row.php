<?php

	$nID = isset($_GET['id']) ? $_GET['id'] : '0';
	$nIDBuyDoc = isset($_GET['id_buy_doc']) ? $_GET['id_buy_doc'] : '';
	
	$template->assign('nID',$nID);
	$template->assign('nIDBuyDoc',$nIDBuyDoc);

?>
<?php

	$nIDOffice = isset($_GET['id_office']) ? $_GET['id_office'] : '';
	
	$template->assign('nIDOffice',$nIDOffice);

?>
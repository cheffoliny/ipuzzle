<?php
	$nIDFirm 	= isset($_GET['nIDFirm']) 	&& !empty($_GET['nIDFirm']) ? $_GET['nIDFirm'] 	: 0;
	$nIDOffice 	= isset($_GET['nIDOffice']) && !empty($_GET['nIDOffice']) ? $_GET['nIDOffice'] 	: 0;
	$dFrom		= isset($_GET['dFrom']) 	&& !empty($_GET['dFrom']) 	? $_GET['dFrom'] 	: date("d.m.Y");
	$dTo		= isset($_GET['dTo']) 		&& !empty($_GET['dTo']) 	? $_GET['dTo'] 		: date("d.m.Y");
	
	$template->assign("nIDFirm", 	$nIDFirm);
	$template->assign("nIDOffice", 	$nIDOffice);
	$template->assign("dFrom", 		$dFrom);
	$template->assign("dTo", 		$dTo);
?>
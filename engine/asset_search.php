<?php
	$sTypeTo = !empty( $_GET['type'] ) ? $_GET['type'] : '';	
	
	$nIDFirm = !empty( $_GET['nIDFirm']) ? $_GET['nIDFirm'] : '0';
	$nIDOffice = !empty( $_GET['nIDOffice']) ? $_GET['nIDOffice'] : '0';
	$nIDPerson = !empty( $_GET['nIDPerson']) ? $_GET['nIDPerson'] : '0';
	$nIDGroup = !empty( $_GET['nIDGroup']) ? $_GET['nIDGroup'] : '0';
	
	$template->assign('nIDFirm',$nIDFirm);
	$template->assign('nIDOffice',$nIDOffice);
	$template->assign('nIDPerson',$nIDPerson);
	$template->assign('nIDGroup',$nIDGroup);
	
	$template->assign("sTypeTo", $sTypeTo);
?>
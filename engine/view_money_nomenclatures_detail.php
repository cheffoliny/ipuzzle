<?php
	$nIDNom		= isset($_GET['nIDNomenclature']) 	? $_GET['nIDNomenclature'] 	: 0;
	$nIDFirm	= isset($_GET['nIDFirm']) 			? $_GET['nIDFirm'] 	 		: 0;
	$nIDOffice	= isset($_GET['nIDOffice']) 		? $_GET['nIDOffice'] 		: 0;
	$nIDObject	= isset($_GET['nIDObject']) 		? $_GET['nIDObject'] 		: 0;
	$sFromDate	= isset($_GET['sFromDate']) 		? $_GET['sFromDate'] 		: 0;
	$sToDate	= isset($_GET['sToDate']) 			? $_GET['sToDate'] 	 		: 0;
	$sMonth		= isset($_GET['sMonth']) 			? $_GET['sMonth'] 	 		: 0;
	$nIDBankAccount = isset($_GET['nIDBankAccount']) 			? $_GET['nIDBankAccount'] 	 		: 0;
	$button		= (!empty($nIDNom) || $nIDBankAccount )				? false						: true;
	
	
	$template->assign("button", 			$button);
	$template->assign("nIDNomenclature", 	$nIDNom);
	$template->assign("nIDFirm", 			$nIDFirm);
	$template->assign("nIDOffice", 			$nIDOffice);
	$template->assign("nIDObject", 			$nIDObject);
	$template->assign("sFromDate", 			$sFromDate);
	$template->assign("sToDate", 			$sToDate);
	$template->assign("sMonth", 			$sMonth);
	$template->assign("nIDBankAccount",		$nIDBankAccount);
?>
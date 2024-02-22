<?php
$oDBPersonnel = new DBPersonnel();
$oDBOffices = new DBOffices();

$nIDPerson	= isset($_GET['id_person'	]) ? $_GET['id_person'	] : '0';
$nIDOffice	= isset($_GET['id_office'	]) ? $_GET['id_office'	] : '0';
$sType		= isset($_GET['type'		]) ? $_GET['type'		] : '';


$aPersonNames = $oDBPersonnel->getPersonnelNames($nIDPerson);
$aOffices = $oDBOffices->getAll();

$template->assign('nIDPerson',	$nIDPerson);
$template->assign('nIDOffice', 	$nIDOffice);
$template->assign('sPerson',		$aPersonNames['names']);
$template->assign('sType',		$sType);
$template->assign('nIDOffice', 	$nIDOffice);

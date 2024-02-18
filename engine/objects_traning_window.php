<?php
global $db_personnel, $db_name_personnel, $db_sod, $db_name_sod;

$nIDPerson = $_GET['id_person'];
$nIDOffice = $_GET['id_office'];
$nDateFrom = $_GET['dateFrom'];
$nDateTo   = $_GET['dateTo'];

switch ($_GET['type']) {
	case "known":
		$sType = "Познати";
	break;
	case "unknown":
		$sType = "Непознати";		
	break;
	case "reacted":
		$sType = "Реакция за";
	break;
	case "visited":
		$sType = "Посетени";
	break;
}

$oBase = new DBBase2($db_personnel,'personnel');
$aPerson = $oBase->select("SELECT CONCAT(fname,' ',mname,' ',lname) AS name FROM $db_name_personnel.personnel WHERE id = $nIDPerson");

$template->assign('nIDPerson', (int) $nIDPerson);
$template->assign('nIDOffice', (int) $nIDOffice);
$template->assign('dateFrom',		 $_GET['dateFrom']);
$template->assign('dateTo',			 $_GET['dateTo']);
$template->assign('tType',			 $_GET['type']);
$template->assign('sType',			 $sType);
$template->assign('sPerson',		 $aPerson[0]['name']);
?>

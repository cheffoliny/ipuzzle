<?php
	
	require_once('config/function.autoload.php');
	
	if (!isset($_GET['do']) || strtolower($_GET['do']) == "login") {
		include_once("config/login.inc.php");
		die();
	}
	
	
	require_once ("config/header.inc.php");	
	require_once ("config/session.inc.php");
	require_once ("include/smarty/Smarty.class.php");
	require_once ("config/connect.inc.php");
	require_once ("include/get_menu_structure.inc.php");
	
	
	$template = new Smarty;
	$template->assign("userdata",$_SESSION['userdata']);
	//$template->assign("system", $_SESSION['system']);
	$template->assign("menu_main",$menu);
    $template->display("index.tpl");

?>

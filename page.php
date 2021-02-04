<?php
require_once ("config/session.inc.php");  // установява includ_path = на директорията на проекта
require_once ("config/function.autoload.php");
require_once ("config/header.inc.php");
require_once ("config/config.inc.php");
require_once ("include/smarty/Smarty.class.php");
require_once ("config/connect.inc.php");
require_once ("include/general.inc.php");
require_once ("include/validate.inc.php");

DEFINE("FLEX_SWF_DIR", "flex/bin/");


$oEvents = new DBSystemEvents();

if(isset($_SESSION['userdata']['need_pass_change']) && $_SESSION['userdata']['need_pass_change'] === true) {
	header("Location:./?do=changepass");
	die();
}

$template = new Smarty;
$template->assign("currency",@$_SESSION['system']['currency']);
$template->assign("local_temp_dir",@$_SESSION['system']['local_temp_dir']);

$page = isset($_GET['page']) ? $_GET['page'] : "";

if(empty($page)) $page="blank_page";

// Ако страницата не съществува
if( file_exists(FLEX_SWF_DIR.$page.".swf") )
{
	$aParams = array_merge($_GET, $_POST);
	$aFlexParams = array();

	foreach( $aParams as $sKey => $sValue )
		$aFlexParams[] = sprintf( "%s=%s", $sKey, $sValue );

	$template->assign("play_flex_file",FLEX_SWF_DIR.$page.".swf");
	$template->assign("flex_name", $page);
	$template->assign("flex_params", implode( "&", $aFlexParams ) );

}
elseif( !file_exists("templates/{$page}.tpl") )
	$template->assign("error","missingFile");

//Ако страницата не е разрешена за достъп
if(!in_array($page,$_SESSION['userdata']['access_right_files']))
	$template->assign("error","missingReject");

if(file_exists("engine/{$page}.php"))
	include_once("engine/{$page}.php");

$_SESSION['last_selected_page']=$page;
$template->assign("page",$page);
$template->assign("eol_debug",defined('EOL_DEBUG') ? (EOL_DEBUG ? 1 : 0) : 0);
$template->assign("is_save_file", !empty($_SESSION['userdata']['is_save_file']) ? $_SESSION['userdata']['is_save_file'] : 0 );
$aAccount = array();
$oAccess = new DBAccess();
$oAccess->getAccountOnce($_SESSION['userdata']['id'], $aAccount);


if( !defined('EOL_DEBUG') || ( !EOL_DEBUG ) )
{
//		if( !empty($aAccount['last_session']) && ( $aAccount['last_session'] != session_id() ) )
//		{
//			$template->assign("ip",$aAccount['last_ip']);
//			$template->display("rejoin.tpl");
//			die();
//		}
}

$oEvents->InsertSystemEvent($page, false);

// Browser type recognition based on useragent's string
$sUserAgent = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? strtolower( $_SERVER['HTTP_USER_AGENT'] ) : '';

if ( preg_match("/msie/i", $sUserAgent) ) {
	$sUserAgent = "msie";
} elseif (preg_match("/firefox/i", $sUserAgent) ) {
	$sUserAgent = "firefox";
} else $sUserAgent = "unknown";

/*
if (eregi("msie",$sUserAgent)) {
    $sUserAgent = "msie";
} elseif (eregi("firefox",$sUserAgent)) {
    $sUserAgent = "firefox";
} else $sUserAgent = "unknown";
*/

$_SESSION['userdata']['user_agent'] = $sUserAgent;
$template->assign("agent",$sUserAgent);
$template->assign("GOOGLE_MAP_KEY",GOOGLE_MAP_KEY);

if ( in_array($page, ["sale_new", "buy_new"]) ) {
	die();
}

$template->display("page.tpl");
?>

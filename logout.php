<?
	require_once('config/function.autoload.php');
	require_once('config/connect.inc.php');

	session_start();
	if (isset($_SESSION['userdata']['id'])) {
		$oAccess = new DBAccess();
		$aData=array("id"=>$_SESSION['userdata']['id'], "last_logout"=>time());
		$oAccess ->updateOnlineStatusAccount( $aData );
	}
	session_destroy();
	Header("Location: index.php");
	exit();
?>
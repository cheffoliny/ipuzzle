<?php


session_name('systemLogin');
// Starting the session

session_set_cookie_params(2*7*24*60*60);
// Making the cookie live for 2 weeks

session_start();

if( isset($_SESSION['id']) && !isset($_COOKIE['loginRemember']) && !$_SESSION['rememberMe'])
{
	// If you are logged in, but you don't have the loginRemember cookie (browser restart)
	// and you have not checked the rememberMe checkbox:

	$_SESSION = array();
	session_destroy();
	
	// Destroy the session
}


if( isset($_GET['logoff']) )
{
	$_SESSION = array();
	session_destroy();
	
	header("Location: index.php");
	exit;
}



?>
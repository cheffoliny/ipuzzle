<?php
	$nDebug = isset($_SESSION['userdata']['has_debug']) ? $_SESSION['userdata']['has_debug'] : 0 ;
	define('EOL_DEBUG', $nDebug);
	
	$aHosts = array();
	$aHosts[] = "http://213.91.252.137/telenet";
	$aHosts[] = "http://213.91.252.135/telenet";
?>
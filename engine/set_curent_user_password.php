<?php

	$sRequestUser = $_SESSION['userdata']['username'];
	
	$template->assign( "name", $sRequestUser );
	
?>
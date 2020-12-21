<?php

	$nIDLimitCard = isset( $_GET['id_limit_card'] ) ? $_GET['id_limit_card'] : 0;
	
	$nIDLogPerson = !empty($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : '0';
	
	$template->assign( 'nIDLimitCard', $nIDLimitCard );
	$template->assign('nIDLogPerson',$nIDLogPerson);

?>
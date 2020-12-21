<?
	$session_init = session_start();
	if (!$session_init) {
	// не може да се инициализира сесия, работата не може да продължи
		die();
	}
	
	if (!isset($_SESSION['valid_session'])) {
	// няма логнат потребител
		die();
	}
	
	// всичко е наред, работата продължава нормално
?>
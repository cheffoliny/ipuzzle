<?
	$session_init = session_start();
	if (!$session_init) {
		Die("Session cannot be initiated. System cannot be started");
	}

	if (!isset($_SESSION['telenet_valid_session'])) {
		header("Location: ./index.php?do=login");
		exit();
	}

	set_include_path( get_include_path().PATH_SEPARATOR.$_SESSION['BASE_DIR'] );
	set_include_path( get_include_path().PATH_SEPARATOR.$_SESSION['BASE_DIR'].'/include' );
?>
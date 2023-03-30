<?php
//	session_start();

	//require_once('./inc/session.inc.php');
	//require_once('general.inc.php');
	
// 	header("Content-type: text/html; charset=UTF-8");
// 	header("Pragma: no-cache");
		
//			
//	define('ERROR', '<center><a class="error">Грешка. Моля опитайте пак.</a></center>' );	

	
//	sleep(2);
	

	if( isset($_GET['action']))
	{
		$action = $_GET['action'];
		$_SESSION['action'] = $action;
		
	}
	elseif( isset($_SESSION['action']) )
	{
		$action = $_SESSION['action'];
		
	}else{
		$action = 'home';		
	}
			
		
	switch ( $action ){
		
		case 'home':
			if( file_exists('./content/alarms_info.php'))
				include('./content/alarms_info.php');
			else 
				echo ERROR ;
				
			break;
									
		case 'contacts':
			if( file_exists('contacts.php'))
				include('contacts.php');
			else 
				echo ERROR;
			
			break;

		case 'opened':
			if( file_exists('./content/opened.php'))
				include('./content/opened.php');
			else
				echo ERROR;

			break;
							
		default:
			if( file_exists('./content/alarms_info.php'))
				include('./content/alarms_info.php');
			else
				echo ERROR ;
				
	}

?>
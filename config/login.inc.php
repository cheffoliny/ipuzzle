<?php
	require_once ("config/config.inc.php");
	
	$nTime = date('Ymd', time());
	$nTS = (isset($_GET['ts']))?$_GET['ts']:0;
/*	
	// Баланс на натоварването
	if ( isset($_GET['f']) && $_GET['f'] == "auto" ) {
		// Do nothing... (Pavel)
	} elseif ( empty($_GET['f']) && !empty($aHosts) ) {
		$sHost = $aHosts[ rand(0, count($aHosts)-1) ];
		//die("Location:{$sHost}/index.php?forwarding=1");
		header("Location:{$sHost}/.?f=1&ts={$nTime}");
	} elseif ( ($nTS != $nTime) && !empty($aHosts) ) {
		$sHost = $aHosts[ rand(0, count($aHosts)-1) ];
		header("Location:{$sHost}/.?f=1&ts={$nTime}");
	}
*/	
	require_once ("config/function.autoload.php");	
	require_once ("config/header.inc.php");
	require_once ("config/connect.inc.php");
	require_once ("include/smarty/Smarty.class.php");
	require_once ("include/general.inc.php");
	
	$oUser = new DBAccess();
	$oEvents = new DBSystemEvents();
	$template = new Smarty;

	session_start();
	
	$post = addslashes_deep($_POST);
	$username = !empty($post['username']) ? $post['username'] : '';
	$userpass = !empty($post['password']) ? $post['password'] : '';
	$usercode = !empty($post['code']) ? $post['code'] : '';
	$userdata = array();
	$system = array();

	
	if( !empty($username) && !empty($userpass) )
	{
		if ($usercode != $_SESSION['login_img_num'] && FALSE) 
		{
			$template->assign("message","Невалиден защитен код!");
		}
		elseif( $oUser->login( $username, $userpass, $userdata ) == DBAPI_ERR_SUCCESS)
		{
		  
			if( count($userdata['access_right_regions']) <= 0 || $userdata['vacate'] )
			{
				$template->assign("message","Нямате достъп до системата!");
			} 
			else 
			{
				// Оторозиран достъп
				$base_dir = pathinfo( $_SERVER['SCRIPT_FILENAME'] );
				
				$_SESSION['BASE_DIR'] = $base_dir['dirname'];
				
				set_include_path( get_include_path().PATH_SEPARATOR.$_SESSION['BASE_DIR']);

				$_SESSION['telenet_valid_session'] = TRUE;
								
				$userdata['access_right_files']['set_curent_user_password'] = 'set_curent_user_password';
				$userdata['access_right_files']['set_curent_user_setup'] = 'set_curent_user_setup';
				$userdata['access_right_files']['blank_page'] = 'blank_page';
				$_SESSION['userdata'] = $userdata; 
				
				// Pavel - avtomatichni...
				/*
				if ( date("d") == 1 ) { 
					$lastDate = $oUser->getLastServiceDate();
					
					$ndate = new DateTime(date("Y-m-d"));
					$ndate->modify("-1 month");
					$mon = $ndate->format("Ym");
					
					if ( $mon > $lastDate ) {
						$mdate = new DateTime(date("Y-m-d H:i:s"));
						$mdate->modify("-1 day");
						$mm = $mdate->format("Y-m-d H:i:s");
						
						$arr = array();
						$arr['user'] = 1;
						$arr['time'] = $mm;
						
						$oUser->createService( $arr );
						//debug($oUser->createService( $arr ));
					}
				}
				*/
				// pavel
				
				$oSystem = new DBSystem();
				$oSystem->initSession();
				
				//$_POST['password']='********';
				//$oEvents->InsertSystemEvent('login.inc.php', false);
				//header("Location:./?do=main");
				//die();
				$_POST['password']='********';
				$oEvents->InsertSystemEvent('login.inc.php', false);
				header("Location:./?do=main".(!empty($_POST['target']) ? '&target='.$_POST['target'] : ''));
				die();
			}
		} 
		else 
		{
			$oEvents->InsertSystemEvent('login.inc.php', false);
			$template->assign("message","<h5 class='mb-3 text-danger'>Невалидно потребителско име или парола!</h5>");
		}
		

	}
	
	$template->assign("target", empty($_GET['target']) ? '' : $_GET['target']);
	$template->display("login.tpl");
	//$template->display("login.tpl");
?>
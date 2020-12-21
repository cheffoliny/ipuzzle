<?php
	require_once ("../../config/session.inc.php");
	if (empty($_SESSION['telenet_valid_session']) || empty($_SESSION['userdata'])) {
		echo "Неоторизиран достъп!";
		die();
	}
	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
	set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../');
	require_once ("../../config/function.autoload.php");
     require_once ("../../config/config.inc.php");
	require_once ("../../config/connect.inc.php");
	require_once ("../../include/general.inc.php");
	
	class cMD extends MonitoringDaemon {
		public function initCanvas() {
			$oChild = new MonitoringDaemon();
			$oChild->processCommands();
			$oChild->oWSSEvents->sendEvents();
		}
	}
	$oMD = new cMD();	
	$oMD->initCanvas();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<style>
			html, body {
				width:      100%;
				height:     100%;
				margin: 0;
				padding: 0;				
			}
		</style>
		<script type="text/javascript" src="../js/jquery/jquery-1.5.1.min.js"></script>		
		<script type="text/javascript">
			$(document).ready(function(){ init(); });
			var init = function() {
				$("body > a").click(
					function(evt){
						$("#result").html('');						
						$.post('test_canvas.php',
							{	sess:'<?=session_id(); ?>', 
								id_person:'<?=$_SESSION["userdata"]["id_person"]; ?>',
								simid:evt.currentTarget.id
							},
							function(data){
								$("#result").append(data);
							}						
						);
						
					}
				);
					
					
					
					
				
			}			
		</script>
	</head>
	<body>
		<a href="#" value="1" id="sim1">ШУМЕН</a>
		<a href="#" value="2" id="sim2">ГАБРОВО</a>
		<a href="#" value="3" id="sim3">SIM 3</a>
		<a href="#" value="4" id="sim4">SIM 4</a>
		<div id="result" style="margin: 0; border: 1px solid; padding: 5px; height: 80%; overflow-y:auto; "></div>
	</body>
</html>
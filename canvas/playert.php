<?php
	require_once ("../config/session.inc.php");
	if (empty($_SESSION['telenet_valid_session']) || empty($_SESSION['userdata'])) {
		echo "Неоторизиран достъп!";
		die();
	}
	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );		
	set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../');
	require_once ("../config/function.autoload.php");
    require_once ("../config/config.inc.php");
	require_once ("../config/connect.inc.php");
	require_once ("../include/general.inc.php");	
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Плеър</title>
    <script type="text/javascript" src="js/include/playert.js"></script>
    <script type="text/javascript" src="js/jquery/jquery-1.5.1.min.js"></script>
    <script type="text/javascript" src="js/jquery/jquery.svg.js"></script>
    <script type="text/javascript" src="js/jquery/jquery.svganim.js"></script>
    <script type="text/javascript" src="js/jquery/jquery.svgdom.js"></script>   
    <script type="text/javascript" src="js/jquery/jquery.svgfilter.js"></script>
    <script type="text/javascript" src="js/jquery/jquery-ui-1.8.12.custom.min.js"></script>
    <script type="text/javascript" src="js/jquery/jquery.ui.core.js"></script>
    <script type="text/javascript" src="js/jquery/jquery.ui.button.js"></script>
    <script type="text/javascript" src="js/jquery/jquery.ui.widget.js"></script>    
    <script type="text/javascript" src="js/jquery/jquery.ui.dialog.js"></script>
	<script type="text/javascript" src="js/jquery/jquery.ui.draggable.js"></script>
   <!-- <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script> -->
    
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="js/jquery/themes/base/jquery.ui.all.css">
    <style>
        @import "js/jquery/jquery.svg.css";        
        @import "css/main.css";
    </style>

    <script>    	
		var ID_ALARM = parseInt('<?=$_GET['id_alarm']?>');
		var ID_ALARM_PATRUL = parseInt('<?=$_GET['id_alarm_patrul']?>');
		var LAYER_TYPE = '<?=$_GET['layer_type']?>';
		var ID_CONTRACT = parseInt('<?=$_GET['id_contract']?>');
	    $(document).ready(function(){ pl.init(); });	    
    </script>
    
  </head>
  <body>
	  <table width="100%" cellpadding="0" cellspacing="0" border="0">
		  <tr>
			  <td id="activeRegion" colspan="3">
				  <div class="wcontent" style="background-color: rgba(207,239,253,0.5); position: absolute; z-index: 100;">                   					 
                      <div id="joker" class="wrapper">
						  <span id="clock"/>
					  </div>
                  </div>
				  <div id="map_canvas" style="height: 100%; width: 100%; visibility: visible;" />
			  </td>
		  </tr>			  		  
		  <tr>
			  <td height="30" width="20"><div id="btnScrollLeft"></div></td>
			  <td style="position: relative;" colspan="2">
					<div id="timeLineWrapper" >		  
						<div id="timeLine" style="height: 100%;margin:0;padding:0; position: absolute; top:0;"></div>		  
					</div>
				</td>
			  <td height="30" width="20"><div id="btnScrollRight"></div></td>
		  </tr>
		  <tr style="background-color: rgba(46, 131, 255, 0.5);">
			  <td><img src="img/wp_grey.png" width="20" height="20" name="btn_wp"/></td>
			  <td id="subtitles" style="padding-left:15px; width:450px;"></td>
			  <td height="40" align="center" nowrap="1">
				  <table border="0" cellpadding="0" cellspacing="0" height="100%">
					  <tr>
						  <td><img src="img/reverse_grey.png" width="24" height="24" name="btn_reverse"/></td>
						  <td><img src="img/play_grey.png" width="30" height="30" name="btn_play"/></td>
				          <td><img src="img/forward_grey.png" width="24" height="24" name="btn_forward"/></td>
					</tr>
				  </table>
			  </td>
			  <td></td>
		  </tr>
	  </table>	 	 	  
  </body>
</html>
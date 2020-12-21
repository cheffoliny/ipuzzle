<?php
	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
	set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../');
	require_once ("../config/function.autoload.php");
	require_once ("../config/config.inc.php");
	require_once ("../config/connect.inc.php");
	require_once ("../include/general.inc.php");
	$oBase = new DBBase2($db_sod,'offices');
	$sQuery = "
		SELECT 
			o.id, 
			o.name,
			f.name AS firm
		FROM offices o
		LEFT JOIN firms f ON o.id_firm = f.id		
		ORDER BY o.name
	";
	$aOffices = $oBase->select($sQuery);
	
?>
<!DOCTYPE html>
<html>
  <head>
    <title>СИМУЛАТОР</title>
    <script type="text/javascript" src="js/include/simulator.js"></script>
    <script type="text/javascript" src="js/jquery/jquery-1.5.1.min.js"></script>
    <script type="text/javascript" src="js/jquery/jquery.hyjack.select.js"></script>
	<script type="text/javascript" src="js/jquery/jquery.ui.core.js"></script>
	<script type="text/javascript" src="js/jquery/jquery.ui.widget.js"></script>   
    <!--<script type="text/javascript" src="js/jquery/jquery.svg.js"></script>
    <script type="text/javascript" src="js/jquery/jquery.svganim.js"></script>
    <script type="text/javascript" src="js/jquery/jquery.svgdom.js"></script>   
    <script type="text/javascript" src="js/jquery/jquery.svgfilter.js"></script>
    <script type="text/javascript" src="js/jquery/jquery-ui-1.8.12.custom.min.js"></script>
    <script type="text/javascript" src="js/jquery/jquery.ui.core.js"></script>
    <script type="text/javascript" src="js/jquery/jquery.ui.button.js"></script>
    <script type="text/javascript" src="js/jquery/jquery.ui.widget.js"></script>    
    <script type="text/javascript" src="js/jquery/jquery.ui.dialog.js"></script>-->
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?key=AIzaSyAgMeepfn7LP4uw75BUQ8Q79tfuBs4ouKw&sensor=false&region=BG"></script>
    
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="js/jquery/themes/base/jquery.ui.all.css">
    <style>
        @import "js/jquery/jquery.svg.css";
        @import "js/jquery/hyjack.css";
        @import "css/main.css";
		.tblMenu {
			border: 1px solid darkgrey; 
			-webkit-border-radius: 0 0 5px 5px; 
			-webkit-box-shadow: 0px 0px 10px #555;
			padding: 3px; 
			background-color: rgba(207, 239, 253, 0.496094);
			position: relative;
			z-index: 1000;
		}		
    </style>
    <script>$(document).ready(function(){ sim.init(); });
	</script>
  </head>
  <body>
	  <div id="tblMenu" class="tblMenu">
		  <table width="100%" height="60" cellpadding="1" cellspacing="1" border="0">
			 <tr style="height: 30px;">
				 <td width="40"><label for="objId">Регион:</label>
					 <select id="selRegion" style="width:250px;">		
						 <?php 
							foreach ($aOffices as $ofice) {
								echo "<option value='".$ofice['id']."'>".$ofice['name']." - ".$ofice['firm']."</option>";
							}
						?>					 
					 </select>	
				 </td>
				 <td width="100">
					 <label for="objId">Обект:</label>&nbsp;
					 <input type="text" id="txtObjNum" style="width:245px"/>
				 </td>			 
				 <td width="100" ><label for="selMsg">Състояние:</label>
					 <select id="selMsg" style="width:250px;"></select>	
				 </td>				 
				 <td width="100">
					 <button id="btnAddObjectToMap">Добави обект</button>
					 <button id="btnRefresh" style="float:right;"><img src="img/refresh.png" width="20" height="12"/></button>
				 </td>
			 </tr>
			 <tr style="height: 30px;">			 
				 <td width="100" align="left">
					 <label for="carId">Патрул:</label>
					 <select id="selPatrol" style="width:250px;"></select>	
				 </td>
				 <td width="100"> 
					<label for="selCar">Кола:</label>&nbsp;&nbsp;&nbsp;
					 <select id="selCar" style="width:250px;"></select>	
				 </td>
				 <td width="100">
					 <button id="btnAddCarRoadList">Създай пътен лист</button>		
					 <button id="btnDelCarRoadList">Закрий пътен лист</button>		
				 </td>
				 <td width="100" ></td>				
			 </tr>		 				 
		  </table>
	  </div>
	  <div id="map_canvas" style=" width: 100%;"></div>
  </body>
</html>

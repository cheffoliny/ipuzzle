<?php
	require_once ("../config/session.inc.php");
	if (empty($_SESSION['telenet_valid_session']) || empty($_SESSION['userdata'])) {
		echo "Неоторизиран достъп!";
		die();
	}
	error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );		
	set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../');
	require_once ("../config/function.autoload.php");
    require_once ("../config/config.inc.php");
	require_once ("../config/connect.inc.php");
	require_once ("../include/general.inc.php");
	$oAuth = new WSSEvents();
	$oAuth->sendAuth(array('session_id'=>session_id(),'id_person'=>$_SESSION['userdata']['id_person']));
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Диспечерски Модул</title>
    
	<script type="text/javascript" src="js/include/Stats.js"></script>
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
	<script type="text/javascript" src="js/jquery/jquery.mousewheel.js"></script>
	<script type="text/javascript" src="js/include/framework.js?version=2"></script>
   <!-- <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script> -->
    
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="js/jquery/themes/base/jquery.ui.all.css">
    <style>
        @import "js/jquery/jquery.svg.css";        
        @import "css/main.css";		
		@import "js/jquery/themes/custom-theme/jquery-ui-1.8.16.custom.css";
    </style>

    <script>
        <?php
        $rem_ip = explode('.', $_SERVER['REMOTE_ADDR']);
        if ((int)$rem_ip[0] == 10 && (int)$rem_ip[1] == 10) {
            $WS_URL = "ws://10.10.1.2:7001/";
        } else {
            $WS_URL =  "ws://213.91.252.143:7001/";
        }
        ?>
                var WS_URL	= '<?=empty($aHosts)? "ws://213.91.252.138:7001/" : $WS_URL ?>';
    	//var WS_URL		= 'ws://127.0.0.1:7000/';
                var SESSION		= '<?=session_id()?>';
		var BASE_URL	= '<?="http://".$_SERVER['HTTP_HOST']?>';
		var ID_PERSON	= '<?=$_SESSION['userdata']['id_person']?>';
		var PERSON		= '<?=$_SESSION['userdata']['name']?>';
		var USER		= '<?=$_SESSION['userdata']['username']?>';
	    $(document).ready(function(){ fw.init(); });	    
    </script>
    
  </head>
  <body style="opacity:0;">	
      <table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0" styel="table-layout:fixed;">
          <tr><td height="30" style="padding-left:1px" nowrap="nowrap"><div id="regionTop" class="selector"></div></td></tr>
          <tr>
              <td id="activeRegion" style="background-color: rgba(207,239,253,0.5);padding-right: 2px;">                                                  
			<div id="bunchSelector" class="bunchLoupe"></div>
				<div id="divArchive">					  
					<span id="btnArchive" style="position: relative;float:right;right:5px;cursor: pointer;">
						<img id="imgArchive" width="12" height="12" src="../images/add_more.gif" style="position: relative;cursor: pointer;"/>
						Архив						  
					</span>	
					<div class="tblArchive"></div>					  
				</div>				  
				<div class="wcontent" style="background-color: rgba(207,239,253,0.5); position: absolute; top:30px; z-index: 100;">                   					  
						<div id="joker" class="wrapper"></div>
				</div>
				  <div id="divServiceBypass">
					  <span id="btnServiceBypass" style="position: relative;float:right;right:5px;cursor: pointer;">
						  <img id="imgServiceBypass" width="12" height="12" src="../images/add_more.gif" style="position: relative;cursor: pointer;"/>
						  Сервиз<span id="nService" style="font-size:12px;"></span>/Байпас<span id="nBypass" style="font-size:12px;"></span>						  
					  </span>	
					  <div class="tblServiceBypass"></div>
				  </div>
			<div id="divNotifications">
				<span id="btnNotifications" style="position: relative;float:left;left:5px;cursor: pointer;">
					<img id="imgNotifications" width="12" height="12" src="../images/add_more.gif" style="position: relative;cursor: pointer;" onclick="fw.clickBtnNotifications($(this))"/>
					За известяване (<span id="notifications_count"></span>)
					<div class="tblNotifications" style="display:none;"></div>
				</span>	
			</div>
			<div id="divAlarmPanics">
				<span id="btnAlarmPanics" style="position: relative;float:left;left:5px;cursor: pointer;">
					<img id="imgAlarmPanics" width="12" height="12" src="../images/add_more.gif" style="position: relative;cursor: pointer;" onclick="fw.clickBtnAlarmPanics($(this))"/>
					Непотвърдени паники (<span id="alarm_panics_count"></span>)
					<div class="tblAlarmPanics" style="display:none;"></div>
				</span>	
			</div>
			
                  <div id="map_canvas" style="height: 100%; width: 100%; visibility: hidden;" />				  
              </td>
          </tr>
          <tr><td height="30" style="padding-left:1px" nowrap="nowrap"><div id="regionBottom" class="selector"></div></td></tr>
          <tr><td height="30" id="menuBottomWrapper" style="overflow:hidden; padding: 0; background-color: #cfeffd;">
                  <div id="menuBottom">                                                                  
                      <table style="margin:0; width: 100%;" cellpadding="0" cellspacing="0" id="menuBottom11"><tr>
                      <td valign="top" width="1%" name="mbb0"><div class="menuBottomButton">Настройки</div></td>
                      <!--<td valign="top" width="1%" name="mbb1">
						  <div class="menuBottomButton">
							  <div style="top:-103px;height:100px; overflow-y:scroll;" class="submenu submenu-hide"></div>
							  Архив
						  </div>
					  </td>-->
                      <td valign="top" width="1%" name="mbb2">
                          <div class="menuBottomButton">
                            <div style="top:-111px;" class="submenu submenu-hide">								
                                <li style="width: 85px;" value="0" name="cars" class="submenu-unselect">
									<image name="cars" src="img/cars.png" width="24" height="24"/>
									<input type="checkbox" name="chbCarVisActive" ident="cars" title="Текущ регион (c)"/>
									<input type="checkbox" name="chbCarVisAll" title="Всички региони (Sift+c)"/>
								</li>
                                <li style="width: 85px;" value="0" name="wp" class="submenu-unselect">
									<image name="wp" src="img/wp.png" width="24" height="24"/>
									<input type="checkbox" name="chbWpVisActive" ident="wp" title="Текущ регион (a)"/>
									<input type="checkbox" name="chbWpVisAll" title="Всички региони (Shift+a)"/>
								</li>                              
								<li style="width: 85px;" value="0" name="map" class="submenu-unselect">
									<image name="map" src="img/map.png" width="24" height="24"/>
									<input type="checkbox" name="chbMapVisActive" ident="map" title="Текущ регион (m)"/>
									<input type="checkbox" name="chbMapVisAll" title="Всички региони (Shift+m)"/>
								</li>
								<li style="width: 85px;" value="0" name="tablet" class="submenu-unselect">
									<image name="map" src="img/tablet.png" width="24" height="24"/>
									<input type="checkbox" name="chbTGPSActive" ident="map" title="Текущ регион (q)"/>
									<input type="checkbox" name="chbTGPSVisAll" title="Всички региони (Shift+q)"/>
								</li>   
                            </div>
                            Визия                          
                          </div>
                      </td>                      
                      <td nowrap="nowrap"  valign="top" width="1%" name="mbb3">
						  <div class="menuBottomButton">Обект</div>
					  </td>
                      <td valign="top" width="1%" name="mbb4">
                          <div class="menuBottomButton">
							  <div style="top:-51px;" class="submenu submenu-hide">
								  <li style="width: 85px;" value="0" name="carsFromRegion" class="submenu-unselect">
										<image name="carsFromRegion" src="img/carsFromRegion_grey.png" width="24" height="24" style="vertical-align:text-bottom;"/>
										<input type="checkbox" name="chbFromRegVisActive" ident="carsFromRegion" title="Текущ регион (f)" check="false"/>
										<input type="checkbox" name="chbFromRegVisAll" title="Всички региони (Shift+f)"/>
								  </li>
								  <li style="width: 85px;" value="0" name="carsInRegion" class="submenu-unselect">
										<image name="carsInRegion" src="img/carsInRegion_grey.png" width="24" height="24" style="vertical-align:text-bottom;"/>
										<input type="checkbox" name="chbInRegVisActive" ident="carsInRegion" title="Текущ регион (i)"/>
										<input type="checkbox" name="chbInRegVisAll" title="Всички региони (Shift+i)"/>
								  </li>
							  </div>
                              Автомобили
                          </div>
                      </td>
                      <td valign="top" width="1%" name="mbb5">                      
                          <div class="menuBottomButton">
                              <div style="top:-63px;" class="submenu submenu-hide">
                                  <li value="16" style="width: 85px; z-index: 900;" class="submenu-unselect">
									  <image src="img/house.png" width="16" height="16" style="vertical-align:text-bottom;"/>
									  <input type="radio" name="rbSize" value="16" style="float:right;" />									  
								  </li>
                                  <li value="20" style="width: 85px; z-index: 900;" class="submenu-unselect">
									  <image src="img/house.png" width="20" height="20" style="vertical-align:text-bottom;""/>
									  <input type="radio" name="rbSize" value="20" style="float:right;" checked />									
								  </li>
                                  <li value="24" style="width: 85px; z-index: 900;" class="submenu-unselect">
									  <image src="img/house.png" width="24" height="24" style="vertical-align:text-bottom;"/>
									  <input type="radio" name="rbSize" value="24" style="float:right;" />									 
								  </li>
                              </div>
                              Размер
                          </div>
                      </td>
					  <?php if ($_SERVER['SERVER_ADDR']=="213.91.252.138") {?>
					  <td valign="top" width="1%" name="mbb6">                      
                          <div class="menuBottomButton">  					
                              Симулация
                          </div>
                      </td> 
					  <?php } ?>
                      <td valign="top" id="statusBar">
                          
                          <!--<input type="checkbox" id="btnPin" style="float:right; clear: right;" />                      
                          <label for="btnPin"></label>   -->
                          <div name="conStatus" style="float:right; margin: 5px; color: #023b90; font-size: 12px;">
                            <img src="img/plug-disconnect-slash.png"/>
                          </div>
                          <div name="clock" style="float:right; margin: 8px; color: #023b90; font-size: 12px; cursor:default;">&nbsp;</div>
                      </td>
                      <tr></table>
                      
                  </div>
          </td></tr>
      </table>	  
  </body>
</html>

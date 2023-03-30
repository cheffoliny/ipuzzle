<?php

define('INCLUDE_CHECK',true);
require 'includes.php';


if( !isset($_SESSION['id']) ):

    require_once './include/login_form.inc.php';
    $strOnLoad = '';

else:
    $strOnLoad = 'onload="get_alarms(); play_alarms(); connection_check(0);"';
?>
<body <?php echo $strOnLoad; ?> >

    <div id="wrapper" class="row">
        <div id="sidebar-wrapper" class="col-sm-2 col-md-2">
            <div id="sidebar" >
                <ul id="play_alarms" class="nav list-group"></ul>
                <ul id="alarmPanel"  class="nav list-group"></ul>
                <ul id="sendGeo"></ul>
                <ul class="logout" onclick="IntelliSOD.stopSound();"><a href="?logoff"><i class="fa fa-power-off"></i> &nbsp; ИЗХОД </a></ul>
            </div>
        </div>
        <div id="main-wrapper" class="col-sm-10 col-md-10 pull-right">
            <div id="main"></div>
        </div>
    </div>

<?php

endif;

?>

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"    ></script>
    <script src="./js/bootstrap-modal.js"   ></script>
    <script src="./js/bootstrap-tooltip.js" ></script>
    <script src="./js/bootstrap-popover.js" ></script>
    <script src="./js/bootstrap.min.js"     ></script>

</body>
</html>
	            

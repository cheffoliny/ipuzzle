<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>iMonitor | Monitoring, Reports and Analytics</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- bootstrap 3.0.2 -->
    <!-- Bootstrap 3.3.4 -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- font Awesome -->
    <!-- FontAwesome 4.3.0 -->
    <link href="bootstrap/fa5/css/all.css" rel="stylesheet" type="text/css" />
<!--    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />-->
    <!-- Ionicons 2.0.0 -->
    <link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />
<!--    <!-- fullCalendar -->
<!--    <link href="css/fullcalendar/fullcalendar.css" rel="stylesheet" type="text/css" />-->
    <!-- Theme style -->
    <link href="dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link href="dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
    <!-- iCheck -->
    <link href="plugins/iCheck/flat/blue.css" rel="stylesheet" type="text/css" />
    <!-- jvectormap -->
    <link href="plugins/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
    <!-- Date Picker -->
    <link href="plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker -->
    <link href="plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
    <!-- bootstrap wysihtml5 - text editor -->
    <link href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />
    <!-- Morris chart -->
    <link href="plugins/morris/morris.css" rel="stylesheet" type="text/css" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesnt work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link href="imon/imonitor.css" rel="stylesheet" type="text/css" />
    <!-- jQuery 2.1.4 -->
    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <script src="imon/ajax_monitor.js"></script>

</head>

<?php

    ob_start();

    function handle_drop($errno, $errstr, $errfile, $errline){
        if( $errno == E_WARNING ){
            echo 'Проблем с връзката! Проверете всички връзки и опитайте да обновите страницата!';
            $ob = ob_get_clean();
            header("HTTP/1.0 500 Internal server error");
            echo $ob;

        }
    }


    if(!isset( $_SESSION['mid'] )):

        $strOnLoad = '';
        $strBodyBG = 'class="bg-black"';
        require_once './include/login_form.inc.php';

    else:

        if( isset( $_GET['action'] )) {
            $action = $_GET['action'];
            $_SESSION['action'] = $action;
        }
        elseif( isset($_SESSION['action']) ) {
            $action = $_SESSION['action'];
        }else{
            $action = 'itech';
        }

        if( isset( $action ) && $action == 'ipatrol' ):
            $strOnLoad = 'onload="monitoring(); in_service_mod(); in_temp_bypass(); no_closed_objects(); stop_alarms(0);"';
            $strBodyBG = 'class="skin-blue fixed"';
        elseif( isset( $action ) && $action == 'ireport' ):
            $strOnLoad = 'onload="alarms_by_day(); alarms_by_type(); alarms_by_object(); alarms_by_signal(); in_service_mod(); in_temp_bypass(); drawDocSparklines(); alarms_by_delay(); no_closed_objects(); stop_alarms(0);"';
            $strBodyBG = 'class="skin-blue fixed"';
        elseif( isset( $action ) && $action == 'ifinance' ):
            $strOnLoad = 'onload="no_client_no_taxes(); prepaid_taxes(); no_closed_objects(); stop_alarms(0); "';
            $strBodyBG = 'class="skin-blue fixed"';
        else:
            $strOnLoad = 'onload="no_test(); no_220(); low_level(); in_service_mod(); stuck_in_objects(); in_temp_bypass(); selectSignals(0); no_closed_objects(); stop_alarms(0);"';
            $strBodyBG = 'class="skin-blue fixed"';
        endif;

    endif;

echo "<body ".$strBodyBG." ".$strOnLoad." >";

?>
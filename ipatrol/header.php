<?php

if( !defined('INCLUDE_CHECK') ) die( 'Операцията не е позволена' );

?>
<!DOCTYPE>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!--<meta name="viewport" content="target-densitydpi=device-dpi, width=device-width" />-->
<!--<meta name="viewport" content="target-densitydpi=device-dpi, maximum-scale=2,user-scalable=1" />-->
<title>iPatrol | Monitoring System</title>
    
    <link rel="stylesheet" type="text/css" href="./css/jquery-ui.css"                   />
    <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Righteous"                      />

    <link rel="stylesheet" type="text/css" href="./css/main.css" media="screen" />
    <link rel="stylesheet" media="screen" href="css/fa5/css/all.css" />
    <link rel="stylesheet" type="text/css" href="./css/bootstrap.min.css"       />
    <link rel="stylesheet" type="text/css" href="./css/styles.css"              />
    <link rel="stylesheet" type="text/css" href="./css/style_predeff.css"       />

    <script type="text/javascript" src="./js/jquery-1.9.1.js"                   ></script>

<?php

if( isset($_SESSION['id']) ) {

?>
<!--    <script type="text/javascript" src="./js/jquery-ui.js"                      ></script>-->
<!--    <script type="text/javascript" src="./js/jquery-migrate-1.1.1.js"           ></script>-->

    <script type="text/javascript" src="./js/general.js"                        ></script>
    <script type="text/javascript" src="./js/get_alarms.js"                     ></script>
<!--    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>-->
<?php
}
?>
    <!--[if lte IE 6]>
    <script type="text/javascript" src="./js/pngfix/supersleight-min.js"        ></script>
    <![endif]-->

    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"    ></script>
    <script src="http://getbootstrap.com/docs-assets/js/html5shiv.js"   ></script>
    <script src="http://getbootstrap.com/docs-assets/js/respond.min.js" ></script>
    <![endif]-->

</head>

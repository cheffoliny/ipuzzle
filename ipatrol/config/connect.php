<?php

if(!defined('INCLUDE_CHECK')) die('You are not allowed to execute this file directly');

define("DB_SERVER"  , "localhost"  );
define("DB_USER"    , "root"		);
define("DB_PASS"    , ""   	);

define("DB_AUTO"       , "auto"				);
define("DB_PERSONNEL"  , "personnel"		);
define("DB_SOD"        , "sod"				);
define("DB_SYSTEM"	   , "intelli_system"	);

$db_auto = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, "", 3306 ) or die( mysqli_connect_error() );
mysqli_select_db( $db_auto, DB_AUTO		        );
$db_auto->set_charset("utf8");

$db_sod = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, "", 3306 ) or die( mysqli_connect_error()  );
mysqli_select_db( $db_sod, DB_SOD		        );
$db_sod->set_charset("utf8");

$db_personnel = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, "", 3306 ) or die( mysqli_connect_error()  );
mysqli_select_db( $db_personnel, DB_PERSONNEL   );
$db_personnel->set_charset("utf8");

$db_system =	mysqli_connect( DB_SERVER, DB_USER, DB_PASS, "", 3306	) or die( mysqli_connect_error() );
mysqli_select_db( $db_system, DB_SYSTEM		    );
$db_system->set_charset("utf8");
		
?>

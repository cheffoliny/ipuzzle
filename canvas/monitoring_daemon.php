<?php
try {
set_include_path( get_include_path().PATH_SEPARATOR.dirname( __FILE__ ).'/../');

require_once ("../config/function.autoload.php");
require_once ("../config/config.inc.php");
require_once ("../include/adodb/adodb-exceptions.inc.php"); 
$ADODB_EXCEPTION = 'DBException';
require_once ("../config/connect.inc.php");

require_once ("../include/general.inc.php");

	$pidFile = "monitoring_daemon.pid";
        $fh = fopen($pidFile, 'w') or die("can't open file");
        $myPID = getmypid();
        fwrite($fh, $myPID);
        fclose($fh);
	set_time_limit(0);
	$oBot = new MonitoringDaemon();
	$oBot->run();

} catch (Exception $e) {
	var_dump($e);
}
echo 'exited '.date('Y-m-d H:i:s');

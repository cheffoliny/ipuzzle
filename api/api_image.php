<?php
	if ( !isset($_SESSION) ) {
		session_start();
	}
	
	$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
	set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../');
		
	require_once ("../config/function.autoload.php");
	require_once ("../include/adodb/adodb-exceptions.inc.php");
	require_once ("../config/connect.inc.php");
	require_once ("../include/general.inc.php");
	
	$image = "";
	//$db_personnel->debug=true;
     
	$nID = isset($_GET["img"]) ? $_GET["img"] : 0;

	if ( !empty($nID) && is_numeric($nID) ) {
		$rs = $db_personnel->Execute("SELECT image, ext FROM person_images WHERE id_person = '{$nID}'");

		if( $rs ) {
			if ( !$rs->EOF ) {
				$image = $rs->getArray();
			}
		}
	}
	
	if ( isset($image[0]['image']) ) {
		$img = base64_decode($image[0]['image']);
		$ext = $image[0]['ext'];

		header("Content-Type: image/{$ext}", true);
		header("Content-Length: ".strlen($img), true);
		header("Last-Modified: ".date('r'), true);
		//header("Content-Transfer-Encoding: binary\n");		
		header("Content-Disposition: inline; filename='{$nID}.{$ext}'", true);
		header("Cache-Control: no-cache, must-revalidate", true);
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT", true);
		
		echo $img;
		exit();
		//echo $image[0]['image'];
	}
?>
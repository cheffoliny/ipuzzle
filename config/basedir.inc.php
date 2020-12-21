<?php
	
	function set_basedir()
	{
		$aPath = pathinfo( $_SERVER['SCRIPT_FILENAME'] );	
		set_include_path( get_include_path().PATH_SEPARATOR.$aPath['dirname'].'/../');
	}
	
?>
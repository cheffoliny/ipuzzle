<?php

	function puzzle_autoload( $sClassName )
	{

		if( file_exists('../db_api/'.$sClassName.'.class.php') )
			require_once('../db_api/'.$sClassName.'.class.php');

		elseif( file_exists('db_api/'.$sClassName.'.class.php') )
			require_once('db_api/'.$sClassName.'.class.php');

		elseif(	file_exists('../db_api/include/'.$sClassName.'.class.php')	)
			require_once('../db_api/include/'.$sClassName.'.class.php');

		elseif(	file_exists('db_api/include/'.$sClassName.'.class.php')	)
			require_once('db_api/include/'.$sClassName.'.class.php');

		elseif(	file_exists('../classes/'.$sClassName.'.class.php') )
			require_once('../classes/'.$sClassName.'.class.php');

		elseif(	file_exists('classes/'.$sClassName.'.class.php') )
			require_once('classes/'.$sClassName.'.class.php');

		elseif( file_exists('../../db_api/'.$sClassName.'.class.php') )
			require_once('../../db_api/'.$sClassName.'.class.php');

		elseif(	file_exists('../../db_api/include/'.$sClassName.'.class.php')	)
			require_once('../../db_api/include/'.$sClassName.'.class.php');

		elseif(	file_exists('../../classes/'.$sClassName.'.class.php') )
			require_once('../../classes/'.$sClassName.'.class.php');
	}

    spl_autoload_register("puzzle_autoload");
?>
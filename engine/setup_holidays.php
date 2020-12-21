<?php

	//Начало: Право за редакция
	$right_edit = false;
	if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
	{
		if( in_array( 'holidays_edit', $_SESSION['userdata']['access_right_levels'] ) )
		{
			$right_edit = true;
		}
	}
	//Край: Право за редакция
	
	$template->assign('right_edit', $right_edit );
	$template->assign( "nYear", date( "Y" ) );

?>
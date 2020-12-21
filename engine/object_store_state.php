<?php

	$edit = false;
	
	if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
	{
		if( in_array( 'object_store_edit', $_SESSION['userdata']['access_right_levels'] ) )
		{
			$edit = true;
		}
	}
	
	$template->assign( "edit", $edit );

?>
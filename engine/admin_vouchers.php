<?php

	$template->assign( 'year', date( "Y", strtotime( "-1 MONTHS" ) ) );
	$template->assign( 'month', date( "m", strtotime( "-1 MONTHS" ) ) );

?>
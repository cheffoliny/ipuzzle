<?php
	$oImage = New DBBase( $db_personnel, 'person_images' );
	
	function uploadImage( $aParams ) {
		global $oImage, $oResponse;
		//debug($aParams);
	}
	

	print( $oResponse->toXML() );	
?>

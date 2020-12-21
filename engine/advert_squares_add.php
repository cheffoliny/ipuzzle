<?php

	if( isset( $_FILES["image"]["error"] ) && ( $_FILES["image"]["error"] == 0 ) && ( ( $_FILES["image"]["type"] == "image/pjpeg" ) || ( ( $_FILES["image"]["type"] == "image/jpeg" ) ) ) )
	{
		isset( $_FILES["image"]["tmp_name"] ) ? $sFileTemp = $_FILES["image"]["tmp_name"] : $sFileTemp = "";
		isset( $_FILES['image']['name'] ) ? $sFileOrig = $_FILES['image']['name'] : $sFileOrig = "";
		
		//Get Extension
		$aFileOrig = explode( ".", $sFileOrig );
		$sFileExt = "." . ( !empty( $aFileOrig ) ? end( $aFileOrig ) : "jpg" );
		//End Get Extension
		
		$sFileName = date( "YmdHis" ) . $sFileExt;
		
		$sFileName = $_SESSION["BASE_DIR"] . "/images_adverts/" . $sFileName;
		
		if( @move_uploaded_file( $sFileTemp, $sFileName ) )
		{
			$sPrint = "
				<script>
					window.opener.loadXMLDoc2( 'result' );
					window.close();
				</script>
			";
			
			print( $sPrint );
		}
	}

?>
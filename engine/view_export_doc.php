<?php
	$file = isset($_GET['id']) ? $_GET['id'] : "";

	$content = "";

	if ( !file_exists($file) ) {
		print("Error open file!!!");
	} else {
	    if ( $handle = @fopen($file, 'r') ) {
			while (!feof($handle)) {
				$content .= @fread($handle, 8192);
			}			   
	    }	
			    
	    @fclose($handle);
		    
	    header("Cache-Control: public, must-revalidate");
		header("Pragma: hack"); 
		header("Content-Type: text/plain");
		header("Content-Length: ".(string)(filesize($file)) );
		header('Content-Disposition: attachment; filename="'.$file.'"');
		header("Content-Transfer-Encoding: binary\n");
		echo $content;
	}
?>
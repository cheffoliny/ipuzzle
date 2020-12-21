<?php
	$nID = ! empty( $_GET['id'] ) ? $_GET['id'] : 0;
	
	if ( isset($_FILES['image']['error']) && ($_FILES['image']['error'] == 0) && (($_FILES['image']['type'] == 'image/pjpeg') || (($_FILES['image']['type'] == 'image/jpeg')))  ) {
		isset($_FILES['image']['tmp_name']) ? $tmp_file = $_FILES['image']['tmp_name'] : $tmp_file = "";
		isset($_FILES['image']['name']) ? $file_name = $_FILES['image']['name'] : $file_name = "";
		$fname = $_SESSION['BASE_DIR'].'/upload/'.$file_name;
		//echo $fname;
		if( @move_uploaded_file($tmp_file, $fname) ) {
			
			if ( !empty($nID) && is_numeric($nID) ) {
				try {
					//set_error_handler(create_function('oko', "throw new Exception(); return true;"));
					//$db_personnel->debug=true;
					$handle = fopen($fname, "r");
					$tmp_file = '';
							
					while (!feof( $handle )) {
						$tmp_file .= fread( $handle, 8192 );
					}
						
					fclose($handle);
					
					$tmp_file = mysql_real_escape_string($tmp_file);

					if ( $rs = $db_personnel->Execute("DELETE FROM person_images WHERE id_person = '{$nID}'") !== FALSE ) {
						$rs = $db_personnel->Execute("INSERT INTO person_images (id_person, image) VALUES ('{$nID}', '{$tmp_file}')");
					}
					
				} catch (Exception $err) {
					// Meow
				}	
			}
			
			$path = $_SESSION['BASE_DIR']."/upload/";
			
			if ( is_dir($path) ) {
				if ( $dh = opendir($path) ) {
					while ( ( $file = readdir($dh) ) !== false) {
						if ( filetype($path.$file) == 'file' ) {
							@unlink($path.$file);
						}
					}
				   closedir($dh);
				}
			}
		//rpc_on_exit = function() {
			print("
				<script>
					window.opener.loadXMLDoc('save');
					window.opener.location.reload();
					window.close();\n
				</script>
			");
			
		}	
	}
	
	
	$template->assign('id', $nID );
?>